<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('family_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('type', [
                'over_budget', 'low_balance', 'loan_due', 'goal_milestone',
                'unusual_spending', 'recurring_due', 'bill_reminder', 'custom'
            ]);
            $table->string('title');
            $table->text('message');
            $table->enum('severity', ['info', 'warning', 'danger', 'success'])->default('info');
            $table->string('icon', 50)->default('bell');
            $table->string('action_url')->nullable();
            $table->morphs('alertable');
            $table->boolean('is_read')->default(false);
            $table->boolean('is_dismissed')->default(false);
            $table->boolean('email_sent')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['family_id', 'is_read', 'created_at']);
            $table->index(['user_id', 'is_read']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alerts');
    }
};
