<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PaymentFlowTest extends TestCase
{
    // ملاحظة: إن كنت تستخدم SQLite في الاختبارات قد تحتاج تهيئة بيانات أولية.

    public function test_receipt_payment_updates_balances_and_paidamount(): void
    {
        // تهيئة: إنشاء عميل، صندوق، عملة، فاتورة
        $customerId = DB::table('accounts')->insertGetId([
            'AccountName' => 'Test Customer', 'AccountType'=>'Customer', 'IsActive'=>1,
        ]);
        $cashId = DB::table('accounts')->insertGetId([
            'AccountName' => 'Main Cash', 'AccountType'=>'Cashbox', 'IsActive'=>1,
        ]);
        $currencyId = DB::table('currencies')->insertGetId([
            'CurrencyCode'=>'TST','CurrencyName'=>'Test', 'IsDefault'=>0,
        ]);
        DB::table('account_balances')->insert([
            'AccountID'=>$cashId, 'CurrencyID'=>$currencyId, 'CurrentBalance'=>0,
        ]);

        $trxId = DB::table('transactions')->insertGetId([
            'TransactionNumber'=>'INV-T-1','TransactionType'=>'Sale','AccountID'=>$customerId,
            'TransactionDate'=>now(),'CurrencyID'=>$currencyId,'TotalAmount'=>1000,'PaidAmount'=>0,
        ]);

        // تنفيذ: سند قبض من العميل إلى الصندوق بقيمة 400
        $userId = DB::table('users')->insertGetId([
            'FullName'=>'Tester','UserName'=>'tester','PasswordHash'=>'x','Email'=>null,
        ]);
        $this->be(\App\Models\User::where('UserID',$userId)->first());

        $resp = $this->post(route('payments.store'), [
            'type' => 'receipt',
            'from_account_id' => $customerId,
            'to_account_id' => $cashId,
            'payment_date' => now()->toDateString(),
            'amount' => 400,
            'currency_id' => $currencyId,
            'description' => 'test',
            'transaction_id' => $trxId,
        ]);
        $resp->assertRedirect(route('payments.index'));

        // تحقق: رصيد الصندوق +400، العميل نقص 400، PaidAmount = 400
        $cashBal = DB::table('account_balances')->where('AccountID',$cashId)->where('CurrencyID',$currencyId)->value('CurrentBalance');
        $this->assertEquals(400.0, (float)$cashBal, 'Cash balance should increase by 400');
        $paid = DB::table('transactions')->where('TransactionID',$trxId)->value('PaidAmount');
        $this->assertEquals(400.0, (float)$paid, 'PaidAmount should be 400');
    }

    public function test_disallow_disabled_accounts_in_payments(): void
    {
        $cust = DB::table('accounts')->insertGetId(['AccountName'=>'C','AccountType'=>'Customer','IsActive'=>0]);
        $cash = DB::table('accounts')->insertGetId(['AccountName'=>'Cash','AccountType'=>'Cashbox','IsActive'=>1]);
        $cur  = DB::table('currencies')->insertGetId(['CurrencyCode'=>'X','CurrencyName'=>'X','IsDefault'=>0]);
        DB::table('account_balances')->insert(['AccountID'=>$cash,'CurrencyID'=>$cur,'CurrentBalance'=>0]);

        $userId = DB::table('users')->insertGetId(['FullName'=>'Tester','UserName'=>'tester','PasswordHash'=>'x']);
        $this->be(\App\Models\User::where('UserID',$userId)->first());

        $resp = $this->post(route('payments.store'), [
            'type' => 'receipt',
            'from_account_id' => $cust,
            'to_account_id' => $cash,
            'payment_date' => now()->toDateString(),
            'amount' => 10,
            'currency_id' => $cur,
        ]);
        $resp->assertSessionHasErrors();
    }
}