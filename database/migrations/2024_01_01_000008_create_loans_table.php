<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('family_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('account_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('type', ['borrowed', 'lent', 'installment', 'salary_advance', 'mortgage'])->default('borrowed');
            $table->string('lender_borrower_name')->nullable();
            $table->decimal('original_amount', 15, 2);
            $table->decimal('remaining_amount', 15, 2);
            $table->decimal('interest_rate', 5, 2)->default(0);
            $table->decimal('monthly_payment', 15, 2)->default(0);
            $table->integer('total_installments')->default(1);
            $table->integer('paid_installments')->default(0);
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->integer('due_day')->nullable();
            $table->enum('status', ['active', 'paid_off', 'defaulted', 'cancelled'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['family_id', 'status']);
        });

        Schema::create('loan_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('account_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('amount', 15, 2);
            $table->decimal('principal', 15, 2)->default(0);
            $table->decimal('interest', 15, 2)->default(0);
            $table->date('payment_date');
            $table->date('due_date')->nullable();
            $table->integer('installment_number')->nullable();
            $table->enum('status', ['paid', 'pending', 'overdue', 'skipped'])->default('paid');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['loan_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loan_payments');
        Schema::dropIfExists('loans');
    }
};
