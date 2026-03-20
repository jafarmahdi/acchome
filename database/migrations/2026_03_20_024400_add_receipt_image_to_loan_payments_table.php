<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('loan_payments', 'receipt_image')) {
            Schema::table('loan_payments', function (Blueprint $table) {
                $table->string('receipt_image')->nullable()->after('notes');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('loan_payments', 'receipt_image')) {
            Schema::table('loan_payments', function (Blueprint $table) {
                $table->dropColumn('receipt_image');
            });
        }
    }
};
