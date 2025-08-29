<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\Account;
use App\Models\Currency;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class TransactionController extends Controller
{
    public function index(): View
    {
        $transactions = Transaction::with(['account', 'currency', 'user'])
            ->orderByDesc('TransactionDate')
            ->paginate(15);

        return view('transactions.index', compact('transactions'));
    }

    public function createSale(): View
    {
        return $this->createCommon('Sale');
    }

    public function createPurchase(): View
    {
        return $this->createCommon('Purchase');
    }

    private function createCommon(string $type): View
    {
        // حسابات (نشطة فقط)
        $accounts = Account::when($type === 'Sale', fn($q) => $q->where('AccountType', 'Customer'))
                           ->when($type === 'Purchase', fn($q) => $q->where('AccountType', 'Supplier'))
                           ->active()
                           ->orderBy('AccountName')->get();

        // الصناديق والبنوك للدفعة المقدّمة (نشطة فقط)
        $cashAndBanks = Account::whereIn('AccountType', ['Cashbox','Bank'])
                               ->active()
                               ->orderBy('AccountName')->get();

        $currencies = Currency::orderBy('CurrencyName')->get();
        $products   = Product::orderBy('ProductName')->limit(200)->get(); // بداءة بسيطة

        return view('transactions.create', compact('type','accounts','cashAndBanks','currencies','products'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'type'          => 'required|in:Sale,Purchase',
            'AccountID'     => 'required|integer',
            'CurrencyID'    => 'required|integer',
            'TransactionDate'=> 'required|date',
            'Notes'         => 'nullable|string|max:255',

            // بنية العناصر
            'items'                     => 'required|array|min:1',
            'items.*.ProductID'         => 'required|integer',
            'items.*.Quantity'          => 'required|numeric|min:0.0001',
            'items.*.UnitPrice'         => 'required|numeric|min:0',
            'items.*.GoldWeight'        => 'nullable|numeric|min:0',

            // الدفعة المقدّمة
            'upfront.amount'            => 'nullable|numeric|min:0',
            'upfront.cash_account_id'   => 'nullable|integer',
        ]);

        // تحقق حالة الحساب الخاص بالدفعة المقدّمة (الصندوق/البنك) إن تم تمريره
        if (!empty($data['upfront']['cash_account_id'])) {
            $cashAcc = Account::where('AccountID', $data['upfront']['cash_account_id'])->first();
            abort_if(!$cashAcc, 422, 'حساب الصندوق/البنك غير موجود');
            $isActiveCash = (bool)($cashAcc->is_active ?? $cashAcc->IsActive ?? false);
            abort_if(!$isActiveCash, 422, 'حساب الصندوق/البنك غير فعال');
        }

        $userId = Auth::id();

        // تحقق أن الحساب المحدد نشط
        $acc = Account::where('AccountID', $data['AccountID'])->first();
        abort_if(!$acc, 422, 'الحساب غير موجود');
        $isActive = (bool)($acc->is_active ?? $acc->IsActive ?? false);
        abort_if(!$isActive, 422, 'الحساب غير فعال');

        DB::transaction(function () use ($data, $userId) {
            // اجمالي الفاتورة
            $total = 0;
            foreach ($data['items'] as &$it) {
                $line = (float)$it['Quantity'] * (float)$it['UnitPrice'];
                $it['LineTotal'] = round($line, 2);
                $total += $it['LineTotal'];
            }

            // رقم فاتورة فريد وفق القاعدة: INV-YYYY-SEQ
            $year = date('Y', strtotime($data['TransactionDate']));
            $nextSeq = (int) (DB::table('transactions')->max('TransactionID')) + 1;
            $trxNumber = 'INV-' . $year . '-' . str_pad((string)$nextSeq, 3, '0', STR_PAD_LEFT);

            // إنشاء المعاملة
            $trx = Transaction::create([
                'TransactionNumber'=> $trxNumber,
                'TransactionType'  => $data['type'],
                'AccountID'        => $data['AccountID'],
                'CurrencyID'       => $data['CurrencyID'],
                'TotalAmount'      => $total,
                'PaidAmount'       => 0,
                'TransactionDate'  => $data['TransactionDate'],
                'Notes'            => $data['Notes'] ?? null,
                'UserID'           => $userId,
            ]);

            // تفاصيل الأصناف + تحديث المخزون
            // تطبيق سياسة عدم السماح بالمخزون السالب إذا كان الإعداد معطلاً
            $allowNegative = false;
            if (Schema::hasTable('system_settings')) {
                $allowNegative = (bool) ((int) (DB::table('system_settings')->where('key','allow_negative_stock')->value('value') ?? 0));
            }

            foreach ($data['items'] as $it) {
                // تحقق مسبق للمخزون عند البيع إذا كان غير مسموح بالسالب
                if (!$allowNegative && $data['type'] === 'Sale') {
                    $qtyColPre = Schema::hasColumn('products','StockByUnit') ? 'StockByUnit' : 'stock_by_unit';
                    $wColPre   = Schema::hasColumn('products','StockByWeight') ? 'StockByWeight' : 'stock_by_weight';
                    $p = DB::table('products')->where('ProductID', $it['ProductID'])->first([$qtyColPre, $wColPre]);
                    $curQty = (float) ($p->$qtyColPre ?? 0);
                    $curW   = (float) ($p->$wColPre   ?? 0);
                    $needQty = (float) $it['Quantity'];
                    $needW   = (float) ($it['GoldWeight'] ?? 0);
                    abort_if($curQty - $needQty < 0, 422, 'الكمية المطلوبة غير متوفرة في المخزون');
                    if (!empty($it['GoldWeight']) && Schema::hasColumn('products',$wColPre)) {
                        abort_if($curW - $needW < 0, 422, 'الوزن المطلوب غير متوفر في المخزون');
                    }
                }

                TransactionDetail::create([
                    'TransactionID' => $trx->TransactionID,
                    'ProductID'     => $it['ProductID'],
                    'Quantity'      => $it['Quantity'],
                    'UnitPrice'     => $it['UnitPrice'],
                    'LineTotal'     => $it['LineTotal'],
                    'Weight'        => $it['GoldWeight'] ?? 0,
                ]);

                // تحديث المخزون (بسيط: بالكمية، ويمكن إضافة الوزن)
                $qtyCol   = Schema::hasColumn('products','StockByUnit') ? 'StockByUnit' : 'stock_by_unit';
                $wCol     = Schema::hasColumn('products','StockByWeight') ? 'StockByWeight' : 'stock_by_weight';

                $qtyDelta = ($data['type'] === 'Sale') ? -$it['Quantity'] : +$it['Quantity'];
                DB::table('products')->where('ProductID', $it['ProductID'])
                    ->update([$qtyCol => DB::raw("$qtyCol + (" . ($qtyDelta + 0) . ")")]);

                if (!empty($it['GoldWeight']) && Schema::hasColumn('products', $wCol)) {
                    $wDelta = ($data['type'] === 'Sale') ? -$it['GoldWeight'] : +$it['GoldWeight'];
                    DB::table('products')->where('ProductID', $it['ProductID'])
                        ->update([$wCol => DB::raw("$wCol + (" . ($wDelta + 0) . ")")]);
                }

                // سجل مخزون (متوافق مع جدول inventorylog في SQL)
                if (Schema::hasTable('inventorylog')) {
                    DB::table('inventorylog')->insert([
                        'ProductID'     => $it['ProductID'],
                        'TransactionID' => $trx->TransactionID,
                        'MovementType'  => $data['type'],
                        'WeightChange'  => $it['GoldWeight'] ?? 0,
                        'UnitChange'    => (int)($it['Quantity']),
                        'UserID'        => $userId,
                        'LogDate'       => now(),
                    ]);
                }
            }

            // تحديث رصيد العميل/المورد (الدين يرتفع بقيمة الفاتورة)
            $this->adjustBalance($data['AccountID'], $data['CurrencyID'], +$total);

            // دفعة مقدّمة (إن وجدت): إنشاء Payment وتحديث الأرصدة
            $upfront = $data['upfront']['amount'] ?? 0;
            $cashAcc = $data['upfront']['cash_account_id'] ?? null;

            if ($upfront > 0 && $cashAcc) {
                // نوع الحركة للدفعة المقدّمة يختلف حسب النوع:
                // - Sale: من العميل -> إلى الصندوق (سند قبض)
                // - Purchase: من الصندوق -> إلى المورد (سند صرف)

                if ($data['type'] === 'Sale') {
                    // تخفيض دين العميل
                    $this->adjustBalance($data['AccountID'], $data['CurrencyID'], -$upfront);
                    // زيادة رصيد الصندوق
                    $this->adjustBalance($cashAcc,           $data['CurrencyID'], +$upfront);
                    // تسجيل دفع
                    $this->insertPayment($data['AccountID'], $cashAcc, $data['CurrencyID'], $upfront, $userId, $trx->TransactionID, 'دفعة مقدّمة على فاتورة بيع');
                } else { // Purchase
                    // زيادة رصيد الصندوق ينقص (من الصندوق إلى المورد)
                    $this->adjustBalance($cashAcc,           $data['CurrencyID'], -$upfront);
                    // تخفيض دين المورد
                    $this->adjustBalance($data['AccountID'], $data['CurrencyID'], -$upfront);
                    // تسجيل دفع
                    $this->insertPayment($cashAcc, $data['AccountID'], $data['CurrencyID'], $upfront, $userId, $trx->TransactionID, 'دفعة مقدّمة على فاتورة شراء');
                }

                // تحديث PaidAmount في الفاتورة
                DB::table('transactions')->where('TransactionID', $trx->TransactionID)
                    ->update(['PaidAmount' => DB::raw('PaidAmount + '.($upfront + 0))]);
            }
        });

        return redirect()->route('transactions.index')->with('success', 'تم حفظ الفاتورة بنجاح');
    }

    public function show(int $id): View
    {
        $trx = Transaction::with(['details.product','account','currency','user'])->findOrFail($id);
        return view('transactions.show', compact('trx'));
    }

    public function destroy(int $id): RedirectResponse
    {
        $trx = Transaction::with('details')->findOrFail($id);

        // حدد عامل العكس بحسب نوع العملية
        $reverseFactorMap = [
            'Sale'           => +1,  // حذف بيع => يزيد المخزون
            'Purchase'       => -1,  // حذف شراء => ينقص المخزون
            'SaleReturn'     => -1,  // حذف مرتجع بيع => ينقص المخزون
            'PurchaseReturn' => +1,  // حذف مرتجع شراء => يزيد المخزون
        ];
        $factor = $reverseFactorMap[$trx->TransactionType] ?? 0;

        DB::transaction(function () use ($trx, $factor) {
            // 1) عكس المخزون بشكل مبسط
            $qtyCol = Schema::hasColumn('products','StockByUnit') ? 'StockByUnit' : 'stock_by_unit';
            $wCol   = Schema::hasColumn('products','StockByWeight') ? 'StockByWeight' : 'stock_by_weight';

            // تطبيق سياسة عدم السماح بالمخزون السالب عند عكس الحركة
            $allowNegative = false;
            if (Schema::hasTable('system_settings')) {
                $allowNegative = (bool) ((int) (DB::table('system_settings')->where('key','allow_negative_stock')->value('value') ?? 0));
            }

            foreach ($trx->details as $d) {
                // إذا كان العامل يُنقص المخزون (مثلاً حذف شراء أو حذف مرتجع بيع)
                if (!$allowNegative && $factor < 0) {
                    $p = DB::table('products')->where('ProductID', $d->ProductID)->first([$qtyCol, $wCol]);
                    if ($d->Quantity) {
                        $newQty = (float)($p->$qtyCol ?? 0) + ($factor * (float)$d->Quantity);
                        abort_if($newQty < 0, 422, 'لا يمكن عكس الحركة: سيؤدي ذلك إلى مخزون سالب');
                    }
                    if ($d->Weight && Schema::hasColumn('products',$wCol)) {
                        $newW = (float)($p->$wCol ?? 0) + ($factor * (float)$d->Weight);
                        abort_if($newW < 0, 422, 'لا يمكن عكس الحركة: سيؤدي ذلك إلى وزن سالب');
                    }
                }

                if ($d->Quantity) {
                    DB::table('products')->where('ProductID',$d->ProductID)->update([$qtyCol=>DB::raw("$qtyCol + ".($factor * (float)$d->Quantity))]);
                }
                if ($d->Weight && Schema::hasColumn('products',$wCol)) {
                    DB::table('products')->where('ProductID',$d->ProductID)->update([$wCol=>DB::raw("$wCol + ".($factor * (float)$d->Weight))]);
                }
            }

            // 2) عكس المدفوعات المرتبطة وحذفها
            $payIdCol = Schema::hasColumn('payments','id') ? 'id' : 'PaymentID';
            $txnCol   = Schema::hasColumn('payments','transaction_id') ? 'transaction_id' : 'TransactionID';
            $amtCol   = Schema::hasColumn('payments','amount') ? 'amount' : 'Amount';
            $fromCol  = Schema::hasColumn('payments','from_account_id') ? 'from_account_id' : 'FromAccountID';
            $toCol    = Schema::hasColumn('payments','to_account_id') ? 'to_account_id' : 'ToAccountID';
            $curCol   = Schema::hasColumn('payments','currency_id') ? 'currency_id' : 'CurrencyID';

            // اختر الأعمدة بأسماء موحّدة لتفادي اختلاف حالة الأحرف بين الأنظمة
            $payments = DB::table('payments')
                ->where($txnCol, $trx->TransactionID)
                ->select([
                    "$amtCol as amount",
                    "$fromCol as from_id",
                    "$toCol as to_id",
                    "$curCol as currency_id",
                ])->get();
            // أعكس الأثر على الأرصدة و PaidAmount ثم احذف السندات
            $acctCol = Schema::hasColumn('account_balances','account_id') ? 'account_id' : 'AccountID';
            $currCol = Schema::hasColumn('account_balances','currency_id') ? 'currency_id' : 'CurrencyID';
            $balCol  = Schema::hasColumn('account_balances','current_balance') ? 'current_balance' : 'CurrentBalance';

            foreach ($payments as $p) {
                $amt = (float)$p->amount;
                DB::table('account_balances')->where($acctCol, $p->to_id)->where($currCol, $p->currency_id)
                    ->update([$balCol => DB::raw("$balCol - ".$amt)]);
                DB::table('account_balances')->where($acctCol, $p->from_id)->where($currCol, $p->currency_id)
                    ->update([$balCol => DB::raw("$balCol + ".$amt)]);
            }
            if (Schema::hasColumn('transactions','PaidAmount')) {
                $sum = (float) DB::table('payments')->where($txnCol, $trx->TransactionID)->sum($amtCol);
                if ($sum > 0) {
                    DB::table('transactions')->where('TransactionID', $trx->TransactionID)
                      ->update(['PaidAmount' => DB::raw('PaidAmount - '.($sum+0))]);
                }
            }
            DB::table('payments')->where($txnCol, $trx->TransactionID)->delete();

            // 3) حذف تفاصيل ثم الفاتورة
            DB::table('transactiondetails')->where('TransactionID',$trx->TransactionID)->delete();
            DB::table('transactions')->where('TransactionID',$trx->TransactionID)->delete();
        });

        return redirect()->route('transactions.index')->with('success','تم حذف العملية وعكس أثر المخزون والمدفوعات');
    }

    // ------- Returns ---------
    public function createReturn(int $id): View
    {
        $orig = Transaction::with(['details.product','account','currency','user'])->findOrFail($id);
        abort_unless(in_array($orig->TransactionType, ['Sale','Purchase']), 404);

        $type = $orig->TransactionType === 'Sale' ? 'SaleReturn' : 'PurchaseReturn';

        return view('transactions.return', [
            'original'   => $orig,
            'type'       => $type,
            'canRefund'  => true, // خيار إنشاء سند صرف/قبض اختياري في الواجهة
        ]);
    }

    public function storeReturn(Request $request, int $id): RedirectResponse
    {
        $orig = Transaction::with(['details','account','currency'])->findOrFail($id);
        abort_unless(in_array($orig->TransactionType, ['Sale','Purchase']), 404);
        $type = $orig->TransactionType === 'Sale' ? 'SaleReturn' : 'PurchaseReturn';

        $data = $request->validate([
            'lines'                 => 'required|array|min:1',
            'lines.*.DetailID'      => 'required|integer',
            'lines.*.Quantity'      => 'nullable|numeric|min:0',
            'lines.*.Weight'        => 'nullable|numeric|min:0',
            'lines.*.UnitPrice'     => 'required|numeric|min:0',
            'notes'                 => 'nullable|string|max:500',
            'create_cash_voucher'   => 'nullable|boolean',
        ]);

        $userId = auth()->id();

        DB::transaction(function () use ($data, $orig, $type, $userId) {
            // 1) تحقق الكميات/الأوزان وعدم تجاوز الأصل
            $origDetails = $orig->details->keyBy('DetailID');

            $total = 0.0;
            $prepared = [];
            foreach ($data['lines'] as $line) {
                $d = $origDetails[$line['DetailID']] ?? null;
                if (!$d) continue; // تجاهل أسطر غير صالحة

                $qty    = (float)($line['Quantity'] ?? 0);
                $weight = (float)($line['Weight'] ?? 0);
                $price  = (float)$line['UnitPrice'];

                // حدود عدم التجاوز (هنا نفترض عدم وجود جدول returns details، فنقارن فقط بالأصل > 0)
                if ($qty > (float)$d->Quantity) $qty = (float)$d->Quantity;
                if ($weight > (float)$d->Weight) $weight = (float)$d->Weight;

                if ($qty <= 0 && $weight <= 0) continue;

                $lineTotal = round(($qty > 0 ? $qty : 0) * $price + ($weight > 0 ? $weight * $price : 0), 2);
                $total += $lineTotal;

                $prepared[] = [
                    'ProductID' => $d->ProductID,
                    'Quantity'  => $qty,
                    'Weight'    => $weight,
                    'UnitPrice' => $price,
                    'LineTotal' => $lineTotal,
                ];
            }

            if (empty($prepared)) {
                abort(422, 'لا توجد أسطر صالحة للمرتجع');
            }

            // 2) رقم مرتجع فريد SR/PR-YYYY-SEQ
            $year = date('Y');
            $nextSeq = (int) (DB::table('transactions')->max('TransactionID')) + 1;
            $prefix = $type === 'SaleReturn' ? 'SR' : 'PR';
            $trxNumber = $prefix . '-' . $year . '-' . str_pad((string)$nextSeq, 3, '0', STR_PAD_LEFT);

            // 3) إنشاء معاملة المرتجع
            $ret = Transaction::create([
                'TransactionNumber'    => $trxNumber,
                'TransactionType'      => $type,
                'AccountID'            => $orig->AccountID,
                'TransactionDate'      => now(),
                'CurrencyID'           => $orig->CurrencyID, // نفس عملة الأصل
                'TotalAmount'          => $total,
                'PaidAmount'           => 0,
                'Notes'                => $data['notes'] ?? null,
                'UserID'               => $userId,
                'OriginalTransactionID'=> $orig->TransactionID,
            ]);

            // 4) تفاصيل + مخزون + لوق
            foreach ($prepared as $it) {
                TransactionDetail::create([
                    'TransactionID' => $ret->TransactionID,
                    'ProductID'     => $it['ProductID'],
                    'Quantity'      => $it['Quantity'],
                    'Weight'        => $it['Weight'],
                    'UnitPrice'     => $it['UnitPrice'],
                    'LineTotal'     => $it['LineTotal'],
                ]);

                // تحديث مخزون: SaleReturn يزيد، PurchaseReturn ينقص
                $qtyCol = Schema::hasColumn('products','StockByUnit') ? 'StockByUnit' : 'stock_by_unit';
                $wCol   = Schema::hasColumn('products','StockByWeight') ? 'StockByWeight' : 'stock_by_weight';

                $factor = $type === 'SaleReturn' ? +1 : -1;
                if ($it['Quantity'] > 0) {
                    DB::table('products')->where('ProductID', $it['ProductID'])
                        ->update([$qtyCol => DB::raw("$qtyCol + " . ($factor * $it['Quantity']))]);
                }
                if ($it['Weight'] > 0 && Schema::hasColumn('products',$wCol)) {
                    DB::table('products')->where('ProductID', $it['ProductID'])
                        ->update([$wCol => DB::raw("$wCol + " . ($factor * $it['Weight']))]);
                }

                // inventorylog
                if (Schema::hasTable('inventorylog')) {
                    DB::table('inventorylog')->insert([
                        'ProductID'     => $it['ProductID'],
                        'TransactionID' => $ret->TransactionID,
                        'MovementType'  => $type,
                        'WeightChange'  => ($factor * ($it['Weight'] ?? 0)),
                        'UnitChange'    => (int)($factor * ($it['Quantity'] ?? 0)),
                        'UserID'        => $userId,
                        'LogDate'       => now(),
                    ]);
                }
            }

            // 5) الأثر المالي: تخفيض الدين فقط افتراضياً
            // العميل في SaleReturn: دينه ينقص بقيمة المرتجع
            // المورد في PurchaseReturn: دينه ينقص أيضاً (نرد البضاعة، يقل ما علينا له)
            $this->adjustBalance($orig->AccountID, $orig->CurrencyID, -$total);

            // 6) اختيارياً إنشاء سند صرف/قبض
            if (!empty($data['create_cash_voucher'])) {
                if ($type === 'SaleReturn') {
                    // سند صرف: من الصندوق إلى العميل
                    $cash = Account::whereIn('AccountType',[ 'Cashbox','Bank'])
                                   ->active()
                                   ->orderBy('AccountID')->first();
                    if ($cash) {
                        // نقص من الصندوق وزيادة للعميل (من منظور الأرصدة: العميل -total تم فعلاً بالخطوة السابقة)
                        $this->adjustBalance($cash->AccountID, $orig->CurrencyID, -$total);
                        // Payment: From cash to customer
                        $this->insertPayment($cash->AccountID, $orig->AccountID, $orig->CurrencyID, $total, $userId, $ret->TransactionID, 'صرف مقابل مرتجع بيع');
                        DB::table('transactions')->where('TransactionID', $ret->TransactionID)->update([
                            'PaidAmount' => DB::raw('PaidAmount + '.($total+0)),
                        ]);
                    }
                } else { // PurchaseReturn
                    // سند قبض: من المورد إلى الصندوق
                    $cash = Account::whereIn('AccountType',[ 'Cashbox','Bank'])
                                   ->active()
                                   ->orderBy('AccountID')->first();
                    if ($cash) {
                        // زيادة رصيد الصندوق
                        $this->adjustBalance($cash->AccountID, $orig->CurrencyID, +$total);
                        // Payment: From supplier to cash
                        $this->insertPayment($orig->AccountID, $cash->AccountID, $orig->CurrencyID, $total, $userId, $ret->TransactionID, 'قبض مقابل مرتجع شراء');
                        DB::table('transactions')->where('TransactionID', $ret->TransactionID)->update([
                            'PaidAmount' => DB::raw('PaidAmount + '.($total+0)),
                        ]);
                    }
                }
            }
        });

        return redirect()->route('transactions.index')->with('success', 'تم إنشاء المرتجع بنجاح');
    }

    // ===== Helpers =====

    private function adjustBalance(int $accountId, int $currencyId, float $delta): void
    {
        $acctCol = Schema::hasColumn('account_balances','account_id') ? 'account_id' : 'AccountID';
        $currCol = Schema::hasColumn('account_balances','currency_id') ? 'currency_id' : 'CurrencyID';
        $balCol  = Schema::hasColumn('account_balances','current_balance') ? 'current_balance' : 'CurrentBalance';

        $exists = DB::table('account_balances')->where($acctCol,$accountId)->where($currCol,$currencyId)->exists();
        if (!$exists) {
            $payload = [$acctCol=>$accountId, $currCol=>$currencyId, $balCol=>0];
            if (Schema::hasColumn('account_balances','created_at')) $payload['created_at'] = now();
            if (Schema::hasColumn('account_balances','updated_at')) $payload['updated_at'] = now();
            DB::table('account_balances')->insert($payload);
        }

        $update = [ $balCol => DB::raw("$balCol + ".($delta+0)) ];
        if (Schema::hasColumn('account_balances','updated_at')) $update['updated_at'] = now();

        DB::table('account_balances')->where($acctCol,$accountId)->where($currCol,$currencyId)->update($update);
    }

    private function insertPayment(int $fromAccountId, int $toAccountId, int $currencyId, float $amount, ?int $userId, int $transactionId, ?string $desc): void
    {
        // أعمدة legacy في جدول payments
        $idCol   = Schema::hasColumn('payments','PaymentID') ? 'PaymentID' : 'id';
        $txnCol  = Schema::hasColumn('payments','TransactionID') ? 'TransactionID' : 'transaction_id';
        $fromCol = Schema::hasColumn('payments','FromAccountID') ? 'FromAccountID' : 'from_account_id';
        $toCol   = Schema::hasColumn('payments','ToAccountID')   ? 'ToAccountID'   : 'to_account_id';
        $dateCol = Schema::hasColumn('payments','PaymentDate') ? 'PaymentDate' : 'payment_date';
        $amtCol  = Schema::hasColumn('payments','Amount') ? 'Amount' : 'amount';
        $curCol  = Schema::hasColumn('payments','CurrencyID') ? 'CurrencyID' : 'currency_id';
        $descCol = Schema::hasColumn('payments','Description') ? 'Description' : 'description';
        $userCol = Schema::hasColumn('payments','UserID') ? 'UserID' : 'user_id';

        // تحضير الحقول الأساسية
        $payload = [
            $txnCol  => $transactionId,
            $fromCol => $fromAccountId,
            $toCol   => $toAccountId,
            $dateCol => now(),
            $amtCol  => $amount,
            $curCol  => $currencyId,
            $descCol => $desc,
            $userCol => $userId,
        ];

        // إذا كان هناك عمود لرقم السند نولّده ونضيفه
        $hasSnakeNum = Schema::hasColumn('payments','payment_number');
        $hasPascalNum = Schema::hasColumn('payments','PaymentNumber');
        if ($hasSnakeNum || $hasPascalNum) {
            $nextSeq = (int) (DB::table('payments')->max($idCol)) + 1;
            $paymentNumber = 'PAY-' . str_pad((string)$nextSeq, 6, '0', STR_PAD_LEFT);
            $payload[$hasSnakeNum ? 'payment_number' : 'PaymentNumber'] = $paymentNumber;
        }

        DB::table('payments')->insert($payload);
    }
}
