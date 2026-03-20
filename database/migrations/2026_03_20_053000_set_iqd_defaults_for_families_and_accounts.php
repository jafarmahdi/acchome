<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE families MODIFY currency VARCHAR(10) NOT NULL DEFAULT 'IQD'");
        DB::statement("ALTER TABLE families MODIFY currency_symbol VARCHAR(5) NOT NULL DEFAULT 'د.ع'");
        DB::statement("ALTER TABLE families MODIFY locale VARCHAR(5) NOT NULL DEFAULT 'ar'");
        DB::statement("ALTER TABLE families MODIFY direction VARCHAR(3) NOT NULL DEFAULT 'rtl'");

        DB::statement("ALTER TABLE accounts MODIFY currency VARCHAR(10) NOT NULL DEFAULT 'IQD'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE families MODIFY currency VARCHAR(10) NOT NULL DEFAULT 'USD'");
        DB::statement("ALTER TABLE families MODIFY currency_symbol VARCHAR(5) NOT NULL DEFAULT '$'");
        DB::statement("ALTER TABLE families MODIFY locale VARCHAR(5) NOT NULL DEFAULT 'en'");
        DB::statement("ALTER TABLE families MODIFY direction VARCHAR(3) NOT NULL DEFAULT 'ltr'");

        DB::statement("ALTER TABLE accounts MODIFY currency VARCHAR(10) NOT NULL DEFAULT 'USD'");
    }
};
