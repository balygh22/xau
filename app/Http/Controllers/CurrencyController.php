<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CurrencyController extends Controller
{
    /**
     * عرض قائمة العملات مرتبة حسب الرمز.
     */
    public function index()
    {
        // تم التحديث: الترتيب باستخدام اسم العمود الصحيح 'CurrencyCode'
        $currencies = Currency::orderBy('CurrencyCode')->get();
        return view('settings.currencies.index', compact('currencies'));
    }

    /**
     * عرض نموذج إنشاء عملة جديدة.
     */
    public function create()
    {
        return view('settings.currencies.create');
    }

    /**
     * تخزين عملة جديدة في قاعدة البيانات.
     */
    public function store(Request $request)
    {
        // تم التحديث: استخدام أسماء الحقول الصحيحة من قاعدة البيانات
        $data = $request->validate([
            'CurrencyCode' => ['required', 'string', 'max:5', 'unique:currencies,CurrencyCode'],
            'CurrencyName' => ['required', 'string', 'max:50'],
            'IsDefault' => ['nullable', 'boolean'],
        ], [], [
            'CurrencyCode' => 'رمز العملة',
            'CurrencyName' => 'اسم العملة',
        ]);

        DB::transaction(function () use ($data) {
            $isDefault = (bool)($data['IsDefault'] ?? false);

            // إذا تم تحديدها كافتراضية، قم بإلغاء التحديد عن العملات الأخرى
            if ($isDefault) {
                Currency::query()->update(['IsDefault' => 0]);
            }

            // إنشاء العملة باستخدام أسماء الأعمدة الصحيحة
            Currency::create([
                'CurrencyCode' => strtoupper($data['CurrencyCode']),
                'CurrencyName' => $data['CurrencyName'],
                'IsDefault' => $isDefault,
            ]);
        });

        return redirect()->route('settings.currencies.index')->with('success', 'تمت إضافة العملة بنجاح');
    }

    /**
     * عرض نموذج تعديل العملة.
     * تم تفعيل Route Model Binding بشكل صحيح في نموذج Currency.
     */
    public function edit(Currency $currency)
    {
        return view('settings.currencies.edit', compact('currency'));
    }

    /**
     * تحديث بيانات العملة.
     */
    public function update(Request $request, Currency $currency)
    {
        // تم التحديث: استخدام أسماء الحقول الصحيحة وقاعدة التحقق الفريدة
        $data = $request->validate([
            'CurrencyCode' => ['required', 'string', 'max:5', Rule::unique('currencies', 'CurrencyCode')->ignore($currency->CurrencyID, 'CurrencyID')],
            'CurrencyName' => ['required', 'string', 'max:50'],
            'IsDefault' => ['nullable', 'boolean'],
        ], [], [
            'CurrencyCode' => 'رمز العملة',
            'CurrencyName' => 'اسم العملة',
        ]);

        DB::transaction(function () use ($data, $currency) {
            $isDefault = (bool)($data['IsDefault'] ?? false);

            // إذا تم تحديدها كافتراضية، قم بإلغاء التحديد عن العملات الأخرى
            if ($isDefault) {
                Currency::where('CurrencyID', '!=', $currency->CurrencyID)->update(['IsDefault' => 0]);
            }

            // تحديث العملة باستخدام أسماء الأعمدة الصحيحة
            $currency->update([
                'CurrencyCode' => strtoupper($data['CurrencyCode']),
                'CurrencyName' => $data['CurrencyName'],
                'IsDefault' => $isDefault,
            ]);
        });

        return redirect()->route('settings.currencies.index')->with('success', 'تم تعديل العملة بنجاح');
    }

    /**
     * حذف عملة من قاعدة البيانات.
     */
    public function destroy(Currency $currency)
    {
        // منع حذف العملة الافتراضية
        if ($currency->IsDefault) {
            return back()->withErrors('لا يمكن حذف العملة الافتراضية.');
        }

        // التحقق مما إذا كانت العملة مستخدمة في جداول أخرى
        $isUsedInTransactions = DB::table('transactions')->where('CurrencyID', $currency->CurrencyID)->exists();
        $isUsedInPayments = DB::table('payments')->where('CurrencyID', $currency->CurrencyID)->exists();
        $isUsedInBalances = DB::table('account_balances')->where('CurrencyID', $currency->CurrencyID)->exists();

        if ($isUsedInTransactions || $isUsedInPayments || $isUsedInBalances) {
            return back()->withErrors('لا يمكن حذف العملة لأنها مستخدمة في معاملات أو أرصدة. يجب حذف السجلات المرتبطة أولاً.');
        }

        $currency->delete();
        return redirect()->route('settings.currencies.index')->with('success', 'تم حذف العملة بنجاح');
    }
}
