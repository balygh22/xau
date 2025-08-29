<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\AccountBalance;
use App\Models\Currency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AccountController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->query('type');
        $query = Account::orderBy('AccountName');

        if (in_array($type, ['Customer', 'Supplier', 'Cashbox', 'Bank'])) {
            $query->where('AccountType', $type);
        }

        $accounts = $query->paginate(15)->withQueryString();
        return view('accounts.index', compact('accounts', 'type'));
    }

    public function create()
    {
        $types = ['Customer', 'Supplier', 'Cashbox', 'Bank'];
        return view('accounts.create', compact('types'));
    }

    public function store(Request $request)
    {
        // استخدام الأسماء الحديثة للتحقق من الصحة
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'account_type' => ['required', Rule::in(['Customer', 'Supplier', 'Cashbox', 'Bank'])],
            'identifier' => ['nullable', 'string', 'max:50'],
            'is_active' => ['nullable', 'boolean'],
        ], [], [
            'name' => 'اسم الحساب',
            'account_type' => 'نوع الحساب',
        ]);

        DB::transaction(function () use ($data) {
            // Eloquent سيستخدم الـ Mutators في نموذج Account لترجمة الحقول
            $account = Account::create($data);

            // إذا كان الحساب صندوقًا أو بنكًا، قم بإنشاء أرصدة صفرية لجميع العملات
            if (in_array($data['account_type'], ['Cashbox', 'Bank'])) {
                $currencies = Currency::all();
                foreach ($currencies as $currency) {
                    AccountBalance::create([
                        'AccountID' => $account->AccountID,
                        'CurrencyID' => $currency->CurrencyID,
                        'CurrentBalance' => 0,
                    ]);
                }
            }
        });

        return redirect()->route('accounts.index')->with('success', 'تم إنشاء الحساب بنجاح');
    }

    public function activate(Account $account)
    {
        $account->is_active = true; // يترجم إلى IsActive
        $account->save();
        return back()->with('success', 'تم تفعيل الحساب');
    }

    public function deactivate(Account $account)
    {
        $account->is_active = false; // يترجم إلى IsActive
        $account->save();
        return back()->with('success', 'تم تعطيل الحساب');
    }

    public function statement(Account $account)
    {
        // أرصدة العملات
        $account->load('balances.currency');

        // احسب رصيد الحركات لكل عملة (من واقع الأرصدة + حركات المدفوعات) لعرضه بوضوح
        $balancesMap = $account->balances->mapWithKeys(function($b){
            $curId = $b->currency->id ?? $b->currency->CurrencyID;
            $curCode = $b->currency->code ?? $b->currency->CurrencyCode ?? '#';
            $bal = $b->current_balance ?? $b->CurrentBalance ?? 0;
            return [$curId => ['code'=>$curCode,'balance'=>(float)$bal]];
        });

        // معاملات مرتبطة بالحساب (فواتير)
        $transactions = \App\Models\Transaction::where('AccountID', $account->AccountID)
            ->orderByDesc('TransactionDate')
            ->get();

        // المدفوعات المرتبطة بالحساب (منه أو إليه)
        $payments = \App\Models\Payment::with(['fromAccount','toAccount','currency','user'])
            ->where('FromAccountID', $account->AccountID)
            ->orWhere('ToAccountID', $account->AccountID)
            ->orderByDesc('PaymentDate')
            ->get();

        // دمج النتائج في قائمة موحدة
        $entries = collect();

        foreach ($transactions as $t) {
            $entries->push([
                'date' => optional($t->TransactionDate),
                'kind' => 'transaction',
                'type' => $t->TransactionType,
                'label' => $t->type_label,
                'number' => $t->TransactionNumber,
                'amount' => (float)$t->TotalAmount,
                'link' => route('transactions.show', $t->TransactionID),
            ]);
        }

        foreach ($payments as $p) {
            // توصيف مبسط
            $op = $p->type_label;
            $desc = $p->description ?: ("حركة من [".($p->fromAccount->AccountName ?? '')."] إلى [".($p->toAccount->AccountName ?? '')."]");
            $entries->push([
                'date' => optional($p->payment_date),
                'kind' => 'payment',
                'type' => $p->type_key,
                'label' => $op,
                'number' => $p->payment_number,
                'amount' => (float)$p->amount,
                'description' => $desc,
                'link' => route('payments.index'), // لا يوجد صفحة عرض مفصلة حالياً
            ]);
        }

        $entries = $entries->sortByDesc('date')->values();

        return view('accounts.statement', compact('account','entries'));
    }
}
