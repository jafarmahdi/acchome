<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('account_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('family_id')->constrained()->cascadeOnDelete();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('adjustment_type', ['set', 'add', 'subtract']);
            $table->decimal('entered_amount', 15, 2);
            $table->decimal('previous_balance', 15, 2);
            $table->decimal('new_balance', 15, 2);
            $table->decimal('difference', 15, 2);
            $table->string('reason');
            $table->date('adjustment_date');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['family_id', 'account_id', 'adjustment_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_adjustments');
    }
};
