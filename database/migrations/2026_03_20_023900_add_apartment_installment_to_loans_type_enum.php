<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE loans
            MODIFY COLUMN type ENUM(
                'borrowed',
                'lent',
                'installment',
                'salary_advance',
                'mortgage',
                'apartment_installment'
            ) NOT NULL DEFAULT 'borrowed'
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE loans
            MODIFY COLUMN type ENUM(
                'borrowed',
                'lent',
                'installment',
                'salary_advance',
                'mortgage'
            ) NOT NULL DEFAULT 'borrowed'
        ");
    }
};
