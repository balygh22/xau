<?php
// Reassign references from one legacy CurrencyID to target (by code or default), then delete old currency
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$old = (int)($argv[1] ?? 0);
$targetCode = strtoupper(trim($argv[2] ?? 'YER'));
if ($old <= 0) {
    fwrite(STDERR, "Usage: php artisan-currency-reassign-delete.php <OldCurrencyID> [TARGET_CODE=YER]\n");
    exit(1);
}

// Determine target currency
$target = DB::table('currencies')->whereRaw('UPPER(code) = ?', [$targetCode])->first();
if (!$target) {
    $target = DB::table('currencies')->where('is_default', 1)->first();
}
if (!$target) {
    fwrite(STDERR, "Target currency not found (code={$targetCode} or is_default=1).\n");
    exit(2);
}

$targetLegacyId = property_exists($target, 'CurrencyID') ? $target->CurrencyID : null;
$targetStdId    = property_exists($target, 'id') ? $target->id : null;

$oldStdId = DB::table('currencies')->where('CurrencyID', $old)->value('id');

$changes = [
    'products.currency_id' => 0,
    'accountbalances.CurrencyID' => 0,
    'AccountBalances.CurrencyID' => 0,
    'payments.CurrencyID' => 0,
    'transactions.CurrencyID' => 0,
    'account_balances.currency_id' => 0,
];

DB::beginTransaction();
try {
    if ($targetLegacyId) {
        if (Schema::hasTable('products') && Schema::hasColumn('products','currency_id')) {
            $changes['products.currency_id'] = DB::table('products')->where('currency_id', $old)->update(['currency_id' => $targetLegacyId]);
        }
        // Merge-aware reassign for legacy account balances
        foreach (['accountbalances','AccountBalances'] as $t) {
            if (Schema::hasTable($t) && Schema::hasColumn($t, 'CurrencyID') && Schema::hasColumn($t, 'AccountID') && Schema::hasColumn($t, 'CurrentBalance')) {
                $rows = DB::table($t)->where('CurrencyID', $old)->get(['AccountID','CurrencyID','CurrentBalance']);
                $merged = 0; $updated = 0; $deleted = 0;
                foreach ($rows as $row) {
                    $exists = DB::table($t)->where(['AccountID' => $row->AccountID, 'CurrencyID' => $targetLegacyId])->first();
                    if ($exists) {
                        // Merge balance then delete source
                        DB::table($t)->where(['AccountID' => $row->AccountID, 'CurrencyID' => $targetLegacyId])
                            ->update(['CurrentBalance' => DB::raw('CurrentBalance + '.((float)$row->CurrentBalance))]);
                        DB::table($t)->where(['AccountID' => $row->AccountID, 'CurrencyID' => $old])->delete();
                        $merged++; $deleted++;
                    } else {
                        DB::table($t)->where(['AccountID' => $row->AccountID, 'CurrencyID' => $old])
                            ->update(['CurrencyID' => $targetLegacyId]);
                        $updated++;
                    }
                }
                $changes[$t.'.CurrencyID'] = ['merged'=>$merged,'updated'=>$updated,'deleted'=>$deleted];
            } elseif (Schema::hasTable($t) && Schema::hasColumn($t, 'CurrencyID')) {
                // Fallback simple update
                $changes[$t.'.CurrencyID'] = DB::table($t)->where('CurrencyID', $old)->update(['CurrencyID' => $targetLegacyId]);
            }
        }
        foreach ([['table'=>'payments','col'=>'CurrencyID'],['table'=>'transactions','col'=>'CurrencyID']] as $x) {
            if (Schema::hasTable($x['table']) && Schema::hasColumn($x['table'],$x['col'])) {
                $changes[$x['table'].'.'.$x['col']] = DB::table($x['table'])->where($x['col'], $old)->update([$x['col'] => $targetLegacyId]);
            }
        }
    }
    if ($oldStdId && $targetStdId && Schema::hasTable('account_balances') && Schema::hasColumn('account_balances','currency_id')) {
        // Merge-aware reassign for new account_balances
        $rows = DB::table('account_balances')->where('currency_id', $oldStdId)->get(['account_id','currency_id','current_balance']);
        $merged = 0; $updated = 0; $deleted = 0;
        foreach ($rows as $row) {
            $exists = DB::table('account_balances')->where(['account_id' => $row->account_id, 'currency_id' => $targetStdId])->first();
            if ($exists) {
                DB::table('account_balances')->where(['account_id' => $row->account_id, 'currency_id' => $targetStdId])
                    ->update(['current_balance' => DB::raw('current_balance + '.((float)$row->current_balance))]);
                DB::table('account_balances')->where(['account_id' => $row->account_id, 'currency_id' => $oldStdId])->delete();
                $merged++; $deleted++;
            } else {
                DB::table('account_balances')->where(['account_id' => $row->account_id, 'currency_id' => $oldStdId])
                    ->update(['currency_id' => $targetStdId]);
                $updated++;
            }
        }
        $changes['account_balances.currency_id'] = ['merged'=>$merged,'updated'=>$updated,'deleted'=>$deleted];
    }

    // Delete old currency (legacy PK)
    DB::table('currencies')->where('CurrencyID', $old)->delete();

    DB::commit();
} catch (\Throwable $e) {
    DB::rollBack();
    fwrite(STDERR, "Failed: ".$e->getMessage()."\n");
    exit(3);
}

echo json_encode([
    'target' => ['code' => $targetCode, 'CurrencyID' => $targetLegacyId, 'id' => $targetStdId],
    'changes' => $changes,
    'deletedCurrencyID' => $old,
], JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT), "\n";