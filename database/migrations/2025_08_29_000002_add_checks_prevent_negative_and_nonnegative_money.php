<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // ملاحظات: CHECK في MySQL 8 مدعوم. سنضيف إن لم تكن موجودة تقريباً.
        // transactions
        DB::statement("ALTER TABLE transactions ADD CONSTRAINT chk_transactions_totals CHECK (TotalAmount >= 0 AND PaidAmount >= 0)");
        // payments
        DB::statement("ALTER TABLE payments ADD CONSTRAINT chk_payments_amount CHECK (Amount >= 0)");
        // products (الأوزان والعدد لا تقل عن الصفر - إن أردت السماح بالسالب أزل هذا)
        DB::statement("ALTER TABLE products ADD CONSTRAINT chk_products_stock_nonneg CHECK (StockByUnit >= 0 AND StockByWeight >= 0)");
        // account_balances
        DB::statement("ALTER TABLE account_balances ADD CONSTRAINT chk_acc_bal_nonneg CHECK (CurrentBalance >= -999999999999.9999)");
    }

    public function down(): void
    {
        foreach ([
            ['transactions','chk_transactions_totals'],
            ['payments','chk_payments_amount'],
            ['products','chk_products_stock_nonneg'],
            ['account_balances','chk_acc_bal_nonneg'],
        ] as [$table,$name]) {
            try { DB::statement("ALTER TABLE $table DROP CHECK $name"); } catch (Throwable $e) {}
        }
    }
};