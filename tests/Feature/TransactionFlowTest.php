<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TransactionFlowTest extends TestCase
{
    public function test_create_sale_with_upfront_updates_balances_and_paidamount(): void
    {
        $cust = DB::table('accounts')->insertGetId(['AccountName'=>'Cust','AccountType'=>'Customer','IsActive'=>1]);
        $cash = DB::table('accounts')->insertGetId(['AccountName'=>'Cash','AccountType'=>'Cashbox','IsActive'=>1]);
        $cur  = DB::table('currencies')->insertGetId(['CurrencyCode'=>'T','CurrencyName'=>'T','IsDefault'=>0]);
        DB::table('account_balances')->insert(['AccountID'=>$cash,'CurrencyID'=>$cur,'CurrentBalance'=>0]);

        // منتج وكميات متاحة
        $cat = DB::table('categories')->insertGetId(['CategoryName'=>'Cat']);
        $prod = DB::table('products')->insertGetId([
            'ProductName'=>'P','CategoryID'=>$cat,'GoldWeight'=>0,'StoneWeight'=>0,'LaborCost'=>0,'StockByWeight'=>100,'StockByUnit'=>100
        ]);

        $userId = DB::table('users')->insertGetId(['FullName'=>'Tester','UserName'=>'tester','PasswordHash'=>'x']);
        $this->be(\App\Models\User::where('UserID',$userId)->first());

        $resp = $this->post(route('transactions.store'), [
            'type'=>'Sale',
            'AccountID'=>$cust,
            'CurrencyID'=>$cur,
            'TransactionDate'=>now()->toDateString(),
            'items'=>[
                ['ProductID'=>$prod,'Quantity'=>2,'UnitPrice'=>100,'GoldWeight'=>0]
            ],
            'upfront'=>['amount'=>50,'cash_account_id'=>$cash],
        ]);
        $resp->assertRedirect(route('transactions.index'));

        // تحقق
        $tx = DB::table('transactions')->orderByDesc('TransactionID')->first();
        $this->assertEquals(200.00, (float)$tx->TotalAmount);
        $this->assertEquals(50.00, (float)$tx->PaidAmount);

        $cashBal = DB::table('account_balances')->where('AccountID',$cash)->where('CurrencyID',$cur)->value('CurrentBalance');
        $this->assertEquals(50.0, (float)$cashBal);
    }

    public function test_block_disabled_account_in_transaction(): void
    {
        $cust = DB::table('accounts')->insertGetId(['AccountName'=>'Cust','AccountType'=>'Customer','IsActive'=>0]);
        $cur  = DB::table('currencies')->insertGetId(['CurrencyCode'=>'T','CurrencyName'=>'T','IsDefault'=>0]);
        $cat = DB::table('categories')->insertGetId(['CategoryName'=>'Cat']);
        $prod = DB::table('products')->insertGetId([
            'ProductName'=>'P','CategoryID'=>$cat,'GoldWeight'=>0,'StoneWeight'=>0,'LaborCost'=>0,'StockByWeight'=>100,'StockByUnit'=>100
        ]);
        $userId = DB::table('users')->insertGetId(['FullName'=>'Tester','UserName'=>'tester','PasswordHash'=>'x']);
        $this->be(\App\Models\User::where('UserID',$userId)->first());

        $resp = $this->post(route('transactions.store'), [
            'type'=>'Sale',
            'AccountID'=>$cust,
            'CurrencyID'=>$cur,
            'TransactionDate'=>now()->toDateString(),
            'items'=>[
                ['ProductID'=>$prod,'Quantity'=>1,'UnitPrice'=>10,'GoldWeight'=>0]
            ],
        ]);
        $resp->assertStatus(302); // متوقع وجود أخطاء
        $resp->assertSessionHasErrors();
    }
}