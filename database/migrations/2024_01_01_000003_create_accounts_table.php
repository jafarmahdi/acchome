<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('family_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->enum('type', ['cash', 'bank', 'savings', 'credit_card', 'loan', 'rewards', 'other'])->default('cash');
            $table->decimal('balance', 15, 2)->default(0);
            $table->decimal('initial_balance', 15, 2)->default(0);
            $table->string('currency', 10)->default('USD');
            $table->string('bank_name')->nullable();
            $table->string('account_number')->nullable();
            $table->string('color', 7)->default('#3B82F6');
            $table->string('icon', 50)->default('wallet');
            $table->decimal('low_balance_threshold', 15, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('include_in_total')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['family_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
