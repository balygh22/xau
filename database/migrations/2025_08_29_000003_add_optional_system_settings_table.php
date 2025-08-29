<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('system_settings')) {
            Schema::create('system_settings', function (Blueprint $table) {
                $table->string('key')->primary();
                $table->string('value')->nullable();
                $table->timestamps();
            });
            DB::table('system_settings')->insert([
                ['key' => 'allow_negative_stock', 'value' => '0', 'created_at'=>now(), 'updated_at'=>now()],
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};