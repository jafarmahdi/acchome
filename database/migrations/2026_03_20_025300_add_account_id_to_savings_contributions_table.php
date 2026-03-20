<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('savings_contributions', 'account_id')) {
            Schema::table('savings_contributions', function (Blueprint $table) {
                $table->foreignId('account_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('savings_contributions', 'account_id')) {
            Schema::table('savings_contributions', function (Blueprint $table) {
                $table->dropConstrainedForeignId('account_id');
            });
        }
    }
};
