<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('loan_payments', function (Blueprint $table) {
            if (!Schema::hasColumn('loan_payments', 'reference_number')) {
                $table->string('reference_number', 100)->nullable()->after('installment_number');
            }

            if (!Schema::hasColumn('loan_payments', 'affects_totals')) {
                $table->boolean('affects_totals')->default(true)->after('reference_number');
            }
        });
    }

    public function down(): void
    {
        Schema::table('loan_payments', function (Blueprint $table) {
            $columns = [];

            if (Schema::hasColumn('loan_payments', 'reference_number')) {
                $columns[] = 'reference_number';
            }

            if (Schema::hasColumn('loan_payments', 'affects_totals')) {
                $columns[] = 'affects_totals';
            }

            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
