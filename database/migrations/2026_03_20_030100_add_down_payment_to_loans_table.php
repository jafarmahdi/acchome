<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('loans', 'down_payment')) {
            Schema::table('loans', function (Blueprint $table) {
                $table->decimal('down_payment', 15, 2)->default(0)->after('original_amount');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('loans', 'down_payment')) {
            Schema::table('loans', function (Blueprint $table) {
                $table->dropColumn('down_payment');
            });
        }
    }
};
