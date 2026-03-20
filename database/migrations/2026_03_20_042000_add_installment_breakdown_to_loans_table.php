<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            if (!Schema::hasColumn('loans', 'installment_interest')) {
                $table->decimal('installment_interest', 15, 2)->default(0)->after('monthly_payment');
            }

            if (!Schema::hasColumn('loans', 'installment_insurance')) {
                $table->decimal('installment_insurance', 15, 2)->default(0)->after('installment_interest');
            }

            if (!Schema::hasColumn('loans', 'installment_bank_fee')) {
                $table->decimal('installment_bank_fee', 15, 2)->default(0)->after('installment_insurance');
            }
        });
    }

    public function down(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            $columns = [];

            foreach (['installment_interest', 'installment_insurance', 'installment_bank_fee'] as $column) {
                if (Schema::hasColumn('loans', $column)) {
                    $columns[] = $column;
                }
            }

            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
