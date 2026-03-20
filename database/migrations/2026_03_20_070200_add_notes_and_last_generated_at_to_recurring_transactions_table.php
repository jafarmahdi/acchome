<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('recurring_transactions', function (Blueprint $table) {
            $table->text('notes')->nullable()->after('description');
            $table->timestamp('last_generated_at')->nullable()->after('auto_create');
        });
    }

    public function down(): void
    {
        Schema::table('recurring_transactions', function (Blueprint $table) {
            $table->dropColumn(['notes', 'last_generated_at']);
        });
    }
};
