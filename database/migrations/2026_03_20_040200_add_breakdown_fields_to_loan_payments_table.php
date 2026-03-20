<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('loan_payments', function (Blueprint $table) {
            if (!Schema::hasColumn('loan_payments', 'insurance_amount')) {
                $table->decimal('insurance_amount', 15, 2)->default(0)->after('interest');
            }

            if (!Schema::hasColumn('loan_payments', 'bank_fee')) {
                $table->decimal('bank_fee', 15, 2)->default(0)->after('insurance_amount');
            }
        });
    }

    public function down(): void
    {
        Schema::table('loan_payments', function (Blueprint $table) {
            $columns = [];

            if (Schema::hasColumn('loan_payments', 'insurance_amount')) {
                $columns[] = 'insurance_amount';
            }

            if (Schema::hasColumn('loan_payments', 'bank_fee')) {
                $columns[] = 'bank_fee';
            }

            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
