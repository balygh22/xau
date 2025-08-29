<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$report = [
    'source_table' => 'currencies_new',
    'exists' => false,
    'merged' => 0,
    'inserted' => 0,
    'updated' => 0,
    'dropped' => false,
];

if (!Schema::hasTable('currencies_new')) {
    echo json_encode($report, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE), "\n";
    exit(0);
}

$report['exists'] = true;

$rows = DB::table('currencies_new')->get(['id','code','name','is_default','created_at','updated_at']);

DB::beginTransaction();
try {
    foreach ($rows as $r) {
        $code = strtoupper(trim($r->code));
        // Try find existing record in currencies by standard or legacy code columns
        $existing = DB::table('currencies')
            ->whereRaw('UPPER(code) = ?', [$code])
            ->orWhereRaw('UPPER(CurrencyCode) = ?', [$code])
            ->first();

        if ($existing) {
            // Update name/default if empty or prefer incoming when different
            $update = [];
            if (empty($existing->name) && !empty($r->name)) { $update['name'] = $r->name; }
            if (property_exists($existing, 'CurrencyName') && empty($existing->CurrencyName) && !empty($r->name)) { $update['CurrencyName'] = $r->name; }
            if ($r->is_default) { $update['is_default'] = 1; if (Schema::hasColumn('currencies','IsDefault')) { $update['IsDefault'] = 1; } }
            if (!empty($update)) {
                DB::table('currencies')->where('CurrencyID', $existing->CurrencyID)->update($update);
                $report['updated']++;
            }
            $report['merged']++;
        } else {
            // Insert into currencies with both standard and legacy columns populated
            $data = [
                'code' => $code,
                'name' => $r->name,
                'is_default' => (int)$r->is_default,
                'created_at' => $r->created_at ?? now(),
                'updated_at' => $r->updated_at ?? now(),
            ];
            if (Schema::hasColumn('currencies','CurrencyCode')) { $data['CurrencyCode'] = $code; }
            if (Schema::hasColumn('currencies','CurrencyName')) { $data['CurrencyName'] = $r->name; }
            if (Schema::hasColumn('currencies','IsDefault'))   { $data['IsDefault'] = (int)$r->is_default; }

            DB::table('currencies')->insert($data);
            $report['inserted']++;
        }
    }

    // If any record is set to default, ensure only one default remains
    $default = DB::table('currencies')->where('is_default', 1)->orderBy('CurrencyID')->first();
    if ($default) {
        DB::table('currencies')->where('CurrencyID', '!=', $default->CurrencyID)->update(['is_default' => 0]);
        if (Schema::hasColumn('currencies','IsDefault')) {
            DB::table('currencies')->where('CurrencyID', '!=', $default->CurrencyID)->update(['IsDefault' => 0]);
            DB::table('currencies')->where('CurrencyID', $default->CurrencyID)->update(['IsDefault' => 1]);
        }
    }

    DB::commit();
} catch (\Throwable $e) {
    DB::rollBack();
    fwrite(STDERR, 'Failed merging: '.$e->getMessage()."\n");
    exit(1);
}

// Drop the duplicate table (DDL auto-commit)
try {
    DB::statement('DROP TABLE `currencies_new`');
    $report['dropped'] = true;
} catch (\Throwable $e) {
    $report['dropped'] = false;
}

echo json_encode($report, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE), "\n";