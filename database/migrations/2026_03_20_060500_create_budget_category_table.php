<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('budget_category')) {
            Schema::create('budget_category', function (Blueprint $table) {
                $table->id();
                $table->foreignId('budget_id')->constrained()->cascadeOnDelete();
                $table->foreignId('category_id')->constrained()->cascadeOnDelete();
                $table->timestamps();

                $table->unique(['budget_id', 'category_id']);
            });
        }

        DB::statement("
            INSERT IGNORE INTO budget_category (budget_id, category_id, created_at, updated_at)
            SELECT id, category_id, NOW(), NOW()
            FROM budgets
            WHERE category_id IS NOT NULL
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('budget_category');
    }
};
