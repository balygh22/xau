<?php
// Standalone check for currency usage across tables
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$target = (int)($argv[1] ?? 0);
if ($target <= 0) {
    fwrite(STDERR, "Usage: php artisan-currency-usage-check.php <CurrencyID>\n");
    exit(1);
}

$results = [];
// products (legacy FK points to CurrencyID)
if (Schema::hasTable('products') && Schema::hasColumn('products','currency_id')) {
    $results['products.currency_id'] = DB::table('products')->where('currency_id', $target)->count();
}
// accountbalances (legacy)
foreach (['accountbalances','AccountBalances'] as $t) {
    if (Schema::hasTable($t) && Schema::hasColumn($t, 'CurrencyID')) {
        $results["$t.CurrencyID"] = DB::table($t)->where('CurrencyID', $target)->count();
    }
}
// account_balances (new) -> currencies.id
if (Schema::hasTable('account_balances') && Schema::hasColumn('account_balances','currency_id')) {
    // find standard id mapped to this legacy CurrencyID if present
    $stdId = DB::table('currencies')->where('CurrencyID', $target)->value('id');
    if ($stdId) {
        $results['account_balances.currency_id'] = DB::table('account_balances')->where('currency_id', $stdId)->count();
    } else {
        $results['account_balances.currency_id'] = 0;
    }
}
// payments, transactions (legacy)
foreach ([['table'=>'payments','col'=>'CurrencyID'],['table'=>'transactions','col'=>'CurrencyID']] as $x) {
    if (Schema::hasTable($x['table']) && Schema::hasColumn($x['table'],$x['col'])) {
        $results[$x['table'].'.'.$x['col']] = DB::table($x['table'])->where($x['col'], $target)->count();
    }
}

echo json_encode($results, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT), "\n";