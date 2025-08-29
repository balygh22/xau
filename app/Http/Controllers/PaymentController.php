<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\AccountBalance;
use App\Models\Currency;
use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function index(): View
    {
        // Order by correct date column depending on schema
        $dateCol = Schema::hasColumn('payments', 'payment_date') ? 'payment_date' : 'PaymentDate';

        $payments = Payment::with(['fromAccount', 'toAccount', 'currency', 'user'])
            ->orderByDesc($dateCol)
            ->paginate(15);

        return view('payments.index', compact('payments'));
    }

    public function destroy(int $id): RedirectResponse
    {
        // Soft business rule: reverse effects before delete
        $p = Payment::with(['fromAccount','toAccount'])->where('PaymentID', $id)->first();
        if (!$p) return redirect()->route('payments.index')->with('error', 'لم يتم العثور على السند');

        DB::transaction(function () use ($p) {
            // Reverse balances
            $fromId = $p->FromAccountID ?? $p->from_account_id;
            $toId   = $p->ToAccountID   ?? $p->to_account_id;
            $curId  = $p->CurrencyID    ?? $p->currency_id;
            $amt    = (float)($p->Amount ?? $p->amount ?? 0);

            // Detect columns for account_balances
            $acctCol = Schema::hasColumn('account_balances','account_id') ? 'account_id' : 'AccountID';
            $currCol = Schema::hasColumn('account_balances','currency_id') ? 'currency_id' : 'CurrencyID';
            $balCol  = Schema::hasColumn('account_balances','current_balance') ? 'current_balance' : 'CurrentBalance';

            DB::table('account_balances')->where($acctCol,$toId)->where($currCol,$curId)->update([$balCol=>DB::raw("$balCol - ".$amt)]);
            DB::table('account_balances')->where($acctCol,$fromId)->where($currCol,$curId)->update([$balCol=>DB::raw("$balCol + ".$amt)]);

            // If linked to transaction, decrease PaidAmount
            $txnId = $p->TransactionID ?? $p->transaction_id;
            if ($txnId && Schema::hasColumn('transactions','PaidAmount')) {
                DB::table('transactions')->where('TransactionID',$txnId)->update([
                    'PaidAmount' => DB::raw('PaidAmount - '.($amt+0)),
                ]);
            }

            // Delete row using query builder to handle legacy keys
            $idCol = Schema::hasColumn('payments','id') ? 'id' : 'PaymentID';
            $val = $p->PaymentID ?? $p->id ?? null;
            if ($val !== null) {
                DB::table('payments')->where($idCol, $val)->delete();
            }
        });

        return redirect()->route('payments.index')->with('success','تم حذف السند وعكس تأثيره بنجاح');
    }

    public function createReceipt(): View
    {
        return $this->createCommon('receipt');
    }

    public function createDisbursement(): View
    {
        return $this->createCommon('disbursement');
    }

    public function createTransfer(): View
    {
        return $this->createCommon('transfer');
    }

    private function createCommon(string $type): View
    {
        // Fetch accounts by type for selects, only active
        $cashAndBanks = Account::whereIn('AccountType', ['Cashbox', 'Bank'])->active()->orderBy('AccountName')->get();
        $customers    = Account::where('AccountType', 'Customer')->active()->orderBy('AccountName')->get();
        $suppliers    = Account::where('AccountType', 'Supplier')->active()->orderBy('AccountName')->get();
        $currencies   = Currency::orderBy('CurrencyName')->get();

        // If a transaction is referenced in query, resolve and lock currency in the form
        $linkedTransaction = null;
        $txParam = request('transaction_id');
        if (!empty($txParam)) {
            $typed = (string) $txParam;
            $resolvedId = DB::table('transactions')->where('TransactionID', $typed)->value('TransactionID');
            if (!$resolvedId) {
                $resolvedId = DB::table('transactions')->where('TransactionNumber', $typed)->value('TransactionID');
            }
            if ($resolvedId) {
                $trx = DB::table('transactions')->where('TransactionID', $resolvedId)->first(['TransactionID','CurrencyID']);
                if ($trx) {
                    $cur = DB::table('currencies')->where('CurrencyID', $trx->CurrencyID)->first(['CurrencyID','CurrencyName','CurrencyCode']);
                    $linkedTransaction = [
                        'id' => (int)$trx->TransactionID,
                        'currency_id' => (int)$trx->CurrencyID,
                        'currency_label' => ($cur->CurrencyName ?? $cur->CurrencyCode ?? ('#'.$trx->CurrencyID)),
                    ];
                }
            }
        }

        return view('payments.create', compact('type', 'cashAndBanks', 'customers', 'suppliers', 'currencies', 'linkedTransaction'));
    }

    public function store(Request $request): RedirectResponse
    {
        // Common validation
        $data = $request->validate([
            'type' => 'required|in:receipt,disbursement,transfer',
            'from_account_id' => 'required|integer',
            'to_account_id' => 'required|integer|different:from_account_id',
            'payment_date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'currency_id' => 'required|integer',
            'description' => 'nullable|string|max:255',
            'transaction_id' => 'nullable|integer',
        ]);

        // Business rules depending on type
        if ($data['type'] === 'receipt') {
            // from: Customer, to: Cash/Bank
            $this->ensureAccountTypes($data['from_account_id'], ['Customer']);
            $this->ensureAccountTypes($data['to_account_id'], ['Cashbox', 'Bank']);
        } elseif ($data['type'] === 'disbursement') {
            // from: Cash/Bank, to: Supplier/Employee/Expenses (we have Supplier only now)
            $this->ensureAccountTypes($data['from_account_id'], ['Cashbox', 'Bank']);
            $this->ensureAccountTypes($data['to_account_id'], ['Supplier']);
        } else { // transfer
            // from: Cash/Bank, to: Cash/Bank
            $this->ensureAccountTypes($data['from_account_id'], ['Cashbox', 'Bank']);
            $this->ensureAccountTypes($data['to_account_id'], ['Cashbox', 'Bank']);
        }

        $userId = Auth::id();

        // If user entered an invoice number instead of internal ID, resolve it
        if (!empty($data['transaction_id'])) {
            $typed = (string)$data['transaction_id'];
            $resolvedId = DB::table('transactions')->where('TransactionID', $typed)->value('TransactionID');
            if (!$resolvedId) {
                $resolvedId = DB::table('transactions')->where('TransactionNumber', $typed)->value('TransactionID');
            }
            $data['transaction_id'] = $resolvedId ?: null;
        }

        // Business validation: prevent paying fully-paid invoices and enforce currency match
        if (!empty($data['transaction_id'])) {
            $trx = DB::table('transactions')->where('TransactionID', $data['transaction_id'])->first(['TotalAmount','PaidAmount','CurrencyID']);
            if ($trx) {
                // Enforce currency consistency
                if ((int)$data['currency_id'] !== (int)$trx->CurrencyID) {
                    return back()->withErrors(['currency_id' => 'عملة السند لا تطابق عملة الفاتورة المرتبطة.'])->withInput();
                }

                $remaining = (float)$trx->TotalAmount - (float)$trx->PaidAmount;
                if ($remaining <= 0.00001) {
                    return back()->withErrors(['transaction_id' => 'لا يمكن إضافة دفعة: الفاتورة مدفوعة بالكامل'])->withInput();
                }
                if ((float)$data['amount'] > $remaining + 0.00001) {
                    return back()->withErrors(['amount' => 'المبلغ يتجاوز المتبقي على الفاتورة (' . number_format($remaining,2) . ')'])->withInput();
                }
            }
        }

        // Currency sanity with cash/bank accounts (avoid cross-currency mistakes without FX)
        $acctCol = Schema::hasColumn('account_balances', 'account_id') ? 'account_id' : 'AccountID';
        $currCol = Schema::hasColumn('account_balances', 'currency_id') ? 'currency_id' : 'CurrencyID';

        $fromAcc = Account::where('AccountID', $data['from_account_id'])->first(['AccountID','AccountType']);
        $toAcc   = Account::where('AccountID', $data['to_account_id'])->first(['AccountID','AccountType']);

        $isCashOrBank = fn($t) => in_array($t, ['Cashbox','Bank'], true);
        $fetchCurrencies = function(int $accountId) use ($acctCol, $currCol) {
            return DB::table('account_balances')->where($acctCol, $accountId)->pluck($currCol)->unique()->filter()->values()->all();
        };

        if ($fromAcc && $isCashOrBank($fromAcc->AccountType)) {
            $fromCurrencies = $fetchCurrencies($fromAcc->AccountID);
            if (count($fromCurrencies) === 1 && (int)$fromCurrencies[0] !== (int)$data['currency_id']) {
                return back()->withErrors(['currency_id' => 'العملة المختارة لا تطابق عملة الصندوق/البنك (من).'])->withInput();
            }
        }
        if ($toAcc && $isCashOrBank($toAcc->AccountType)) {
            $toCurrencies = $fetchCurrencies($toAcc->AccountID);
            if (count($toCurrencies) === 1 && (int)$toCurrencies[0] !== (int)$data['currency_id']) {
                return back()->withErrors(['currency_id' => 'العملة المختارة لا تطابق عملة الصندوق/البنك (إلى).'])->withInput();
            }
        }
        if ($data['type'] === 'transfer' && $fromAcc && $toAcc && $isCashOrBank($fromAcc->AccountType) && $isCashOrBank($toAcc->AccountType)) {
            // For transfer ensure both have the selected currency (or at least not uniquely different)
            $fromCurrencies = $fromCurrencies ?? $fetchCurrencies($fromAcc->AccountID);
            $toCurrencies   = $toCurrencies   ?? $fetchCurrencies($toAcc->AccountID);
            $fromHas = in_array((int)$data['currency_id'], array_map('intval', $fromCurrencies), true);
            $toHas   = in_array((int)$data['currency_id'], array_map('intval', $toCurrencies), true);
            if (!$fromHas || !$toHas) {
                return back()->withErrors(['currency_id' => 'لا يمكن التحويل: العملة غير متوفرة في أحد الصندوقين/البنكين.'])->withInput();
            }
        }

        DB::transaction(function () use ($data, $userId) {
            // Detect column names for payments table (legacy vs new)
            $idCol = Schema::hasColumn('payments', 'id') ? 'id' : 'PaymentID';
            $txnCol = Schema::hasColumn('payments', 'transaction_id') ? 'transaction_id' : 'TransactionID';
            $fromCol = Schema::hasColumn('payments', 'from_account_id') ? 'from_account_id' : 'FromAccountID';
            $toCol = Schema::hasColumn('payments', 'to_account_id') ? 'to_account_id' : 'ToAccountID';
            $dateCol = Schema::hasColumn('payments', 'payment_date') ? 'payment_date' : 'PaymentDate';
            $amtCol = Schema::hasColumn('payments', 'amount') ? 'amount' : 'Amount';
            $curCol = Schema::hasColumn('payments', 'currency_id') ? 'currency_id' : 'CurrencyID';
            $descCol = Schema::hasColumn('payments', 'description') ? 'description' : 'Description';
            $userCol = Schema::hasColumn('payments', 'user_id') ? 'user_id' : 'UserID';

            // Determine if a payment number column exists
            $hasSnakeNum = Schema::hasColumn('payments', 'payment_number');
            $hasPascalNum = Schema::hasColumn('payments', 'PaymentNumber');

            // Build insert payload (base fields)
            $payload = [
                $txnCol => $data['transaction_id'] ?? null,
                $fromCol => $data['from_account_id'],
                $toCol => $data['to_account_id'],
                $dateCol => $data['payment_date'],
                $amtCol => $data['amount'],
                $curCol => $data['currency_id'],
                $descCol => $data['description'] ?? null,
                $userCol => $userId,
            ];

            // Conditionally include payment number if the column exists
            if ($hasSnakeNum || $hasPascalNum) {
                $nextSeq = (int) (DB::table('payments')->max($idCol)) + 1;
                $paymentNumber = 'PAY-' . str_pad((string)$nextSeq, 6, '0', STR_PAD_LEFT);
                $payload[$hasSnakeNum ? 'payment_number' : 'PaymentNumber'] = $paymentNumber;
            }

            // Insert payment (use query builder to avoid fillable/name coupling)
            DB::table('payments')->insert($payload);

            // Update balances atomically
            $this->adjustBalance($data['to_account_id'], $data['currency_id'], $data['amount']);   // credit to
            $this->adjustBalance($data['from_account_id'], $data['currency_id'], -$data['amount']); // debit from

            // If linked to transaction, bump PaidAmount
            if (!empty($data['transaction_id']) && Schema::hasColumn('transactions', 'PaidAmount')) {
                DB::table('transactions')
                    ->where('TransactionID', $data['transaction_id'])
                    ->update([
                        'PaidAmount' => DB::raw('PaidAmount + ' . ((float)$data['amount'])),
                    ]);
            }
        });

        return redirect()->route('payments.index')->with('success', 'تم حفظ سند الدفع بنجاح');
    }

    private function ensureAccountTypes(int $accountId, array $allowedTypes): void
    {
        $acc = Account::where('AccountID', $accountId)->first();
        abort_if(!$acc, 422, 'الحساب غير موجود');
        abort_if(!in_array($acc->AccountType, $allowedTypes, true), 422, 'نوع الحساب غير مسموح لهذه العملية');
        // Active status check (legacy/new columns)
        $isActive = (bool)($acc->is_active ?? $acc->IsActive ?? false);
        abort_if(!$isActive, 422, 'الحساب غير فعال');
    }

    private function adjustBalance(int $accountId, int $currencyId, float $delta): void
    {
        // Detect schema columns
        $acctCol = Schema::hasColumn('account_balances', 'account_id') ? 'account_id' : 'AccountID';
        $currCol = Schema::hasColumn('account_balances', 'currency_id') ? 'currency_id' : 'CurrencyID';
        $balCol  = Schema::hasColumn('account_balances', 'current_balance') ? 'current_balance' : 'CurrentBalance';

        // Ensure row exists
        $exists = DB::table('account_balances')
            ->where($acctCol, $accountId)
            ->where($currCol, $currencyId)
            ->exists();
        if (!$exists) {
            $payload = [
                $acctCol => $accountId,
                $currCol => $currencyId,
                $balCol => 0,
            ];
            // Only include timestamps if columns exist
            if (Schema::hasColumn('account_balances', 'created_at')) {
                $payload['created_at'] = now();
            }
            if (Schema::hasColumn('account_balances', 'updated_at')) {
                $payload['updated_at'] = now();
            }
            DB::table('account_balances')->insert($payload);
        }

        // Apply delta
        $update = [
            $balCol => DB::raw($balCol . ' + ' . ($delta + 0)),
        ];
        if (Schema::hasColumn('account_balances', 'updated_at')) {
            $update['updated_at'] = now();
        }
        DB::table('account_balances')
            ->where($acctCol, $accountId)
            ->where($currCol, $currencyId)
            ->update($update);
    }
}