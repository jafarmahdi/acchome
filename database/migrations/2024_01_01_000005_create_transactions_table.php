<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('family_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('type', ['expense', 'income', 'transfer']);
            $table->decimal('amount', 15, 2);
            $table->string('description');
            $table->text('notes')->nullable();
            $table->date('transaction_date');
            $table->time('transaction_time')->nullable();
            $table->string('reference_number')->nullable();
            $table->string('receipt_image')->nullable();
            $table->enum('payment_method', ['cash', 'card', 'bank_transfer', 'cheque', 'online', 'other'])->default('cash');
            $table->string('location')->nullable();
            $table->json('tags')->nullable();
            $table->boolean('is_recurring')->default(false);
            $table->string('recurring_frequency')->nullable();
            $table->foreignId('transfer_to_account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->foreignId('loan_id')->nullable();
            $table->boolean('is_approved')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['family_id', 'type', 'transaction_date']);
            $table->index(['account_id', 'transaction_date']);
            $table->index(['user_id', 'type']);
            $table->index(['category_id', 'transaction_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
