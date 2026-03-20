<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('budgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('family_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->decimal('amount', 15, 2);
            $table->decimal('spent', 15, 2)->default(0);
            $table->enum('period', ['weekly', 'monthly', 'quarterly', 'yearly'])->default('monthly');
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('alert_threshold', 5, 2)->default(80.00);
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['family_id', 'period', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budgets');
    }
};
