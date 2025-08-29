<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$tables = DB::select('SHOW TABLES');
$dbName = DB::getDatabaseName();
$key = 'Tables_in_'.$dbName;
$list = array_map(fn($r)=>$r->$key, $tables);

$check = [];
foreach ($list as $t) {
    if (preg_match('/^(currenc|account_?balances?|products)$/i', $t)) { // include currencies, balances, products
        $check[] = $t;
    }
}

$columns = [];
foreach ($check as $t) {
    $cols = DB::select('SHOW COLUMNS FROM `'.$t.'`');
    $columns[$t] = array_map(fn($c)=>$c->Field, $cols);
}

echo json_encode([
    'database' => $dbName,
    'all_tables' => $list,
    'matched_tables' => $check,
    'columns' => $columns,
], JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE), "\n";