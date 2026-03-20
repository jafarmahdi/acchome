<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('savings_goals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('family_id')->constrained()->cascadeOnDelete();
            $table->foreignId('account_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('target_amount', 15, 2);
            $table->decimal('current_amount', 15, 2)->default(0);
            $table->date('target_date')->nullable();
            $table->string('icon', 50)->default('piggy-bank');
            $table->string('color', 7)->default('#10B981');
            $table->enum('status', ['active', 'completed', 'cancelled'])->default('active');
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium');
            $table->timestamps();
        });

        Schema::create('savings_contributions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('savings_goal_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 15, 2);
            $table->text('notes')->nullable();
            $table->date('contribution_date');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('savings_contributions');
        Schema::dropIfExists('savings_goals');
    }
};
