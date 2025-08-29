<?php
// Migrate legacy `accountbalances` data into new `account_balances`, then drop the legacy table
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

if (!Schema::hasTable('accountbalances')) {
    echo json_encode(['status' => 'no_legacy_table'], JSON_PRETTY_PRINT), "\n";
    exit(0);
}

$report = [
    'before' => [
        'accountbalances' => Schema::hasTable('accountbalances') ? DB::table('accountbalances')->count() : 0,
        'account_balances' => Schema::hasTable('account_balances') ? DB::table('account_balances')->count() : 0,
    ],
    'migrated' => 0,
    'merged' => 0,
    'fixed_currency_ids' => 0,
    'after' => [],
];

try {
    // Ensure new table exists
    if (!Schema::hasTable('account_balances')) {
        throw new RuntimeException('account_balances table not found');
    }

    $rows = DB::table('accountbalances')->get(['AccountID','CurrencyID','CurrentBalance']);
    foreach ($rows as $row) {
        $legacyCurrencyId = (int)$row->CurrencyID;
        // Map to standard id; if null, try to set id = CurrencyID
        $stdId = DB::table('currencies')->where('CurrencyID', $legacyCurrencyId)->value('id');
        if (!$stdId) {
            $affected = DB::table('currencies')->where('CurrencyID', $legacyCurrencyId)->whereNull('id')->update(['id' => $legacyCurrencyId]);
            if ($affected) { $report['fixed_currency_ids'] += $affected; }
            $stdId = DB::table('currencies')->where('CurrencyID', $legacyCurrencyId)->value('id');
            if (!$stdId) {
                throw new RuntimeException('Cannot resolve currencies.id for CurrencyID='.$legacyCurrencyId);
            }
        }

        $exists = DB::table('account_balances')->where(['account_id' => $row->AccountID, 'currency_id' => $stdId])->first();
        if ($exists) {
            DB::table('account_balances')->where(['account_id' => $row->AccountID, 'currency_id' => $stdId])
                ->update(['current_balance' => DB::raw('current_balance + '.((float)$row->CurrentBalance))]);
            $report['merged']++;
        } else {
            DB::table('account_balances')->insert([
                'account_id' => $row->AccountID,
                'currency_id' => $stdId,
                'current_balance' => $row->CurrentBalance,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $report['migrated']++;
        }
    }

    // Drop legacy table (DDL auto-commits in MySQL)
    DB::statement('DROP TABLE `accountbalances`');
} catch (\Throwable $e) {
    fwrite(STDERR, 'Failed: '.$e->getMessage()."\n");
    exit(1);
}

$report['after'] = [
    'account_balances' => DB::table('account_balances')->count(),
];

echo json_encode($report, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE), "\n";