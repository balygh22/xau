<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // استخدم SQL خام لأن الجداول legacy بأسماء PascalCase
        // accounts: فهرس لنوع الحساب + التفعيل
        DB::statement('CREATE INDEX IF NOT EXISTS idx_accounts_type_active ON accounts (AccountType, IsActive)');

        // payments: فهارس للتاريخ، من/إلى، العملة
        DB::statement('CREATE INDEX IF NOT EXISTS idx_payments_date ON payments (PaymentDate)');
        DB::statement('CREATE INDEX IF NOT EXISTS idx_payments_from ON payments (FromAccountID)');
        DB::statement('CREATE INDEX IF NOT EXISTS idx_payments_to ON payments (ToAccountID)');
        DB::statement('CREATE INDEX IF NOT EXISTS idx_payments_currency ON payments (CurrencyID)');

        // transactions: التاريخ، الحساب، النوع
        DB::statement('CREATE INDEX IF NOT EXISTS idx_transactions_date ON transactions (TransactionDate)');
        DB::statement('CREATE INDEX IF NOT EXISTS idx_transactions_account ON transactions (AccountID)');
        DB::statement('CREATE INDEX IF NOT EXISTS idx_transactions_account_type ON transactions (AccountID, TransactionType)');

        // products: الصنف
        if (Schema::hasTable('products') && Schema::hasColumn('products','CategoryID')) {
            DB::statement('CREATE INDEX IF NOT EXISTS idx_products_category ON products (CategoryID)');
        }

        // account_balances: فهرس إضافي على التاريخ للتقارير إن لزم
        if (Schema::hasTable('account_balances') && Schema::hasColumn('account_balances','updated_at')) {
            DB::statement('CREATE INDEX IF NOT EXISTS idx_acc_bal_updated ON account_balances (updated_at)');
        }
    }

    public function down(): void
    {
        // إسقاط الفهارس (تجاهل الخطأ إن لم توجد)
        foreach ([
            'idx_accounts_type_active',
            'idx_payments_date','idx_payments_from','idx_payments_to','idx_payments_currency',
            'idx_transactions_date','idx_transactions_account','idx_transactions_account_type',
            'idx_products_category','idx_acc_bal_updated',
        ] as $idx) {
            try { DB::statement("DROP INDEX $idx ON accounts"); } catch (Throwable $e) {}
            try { DB::statement("DROP INDEX $idx ON payments"); } catch (Throwable $e) {}
            try { DB::statement("DROP INDEX $idx ON transactions"); } catch (Throwable $e) {}
            try { DB::statement("DROP INDEX $idx ON products"); } catch (Throwable $e) {}
            try { DB::statement("DROP INDEX $idx ON account_balances"); } catch (Throwable $e) {}
        }
    }
};