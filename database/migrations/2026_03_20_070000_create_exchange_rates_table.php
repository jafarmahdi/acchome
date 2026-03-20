<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exchange_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('family_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('from_currency', 10);
            $table->string('to_currency', 10);
            $table->decimal('rate', 18, 6);
            $table->date('effective_date');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['family_id', 'from_currency', 'to_currency', 'effective_date'], 'exchange_rates_lookup_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exchange_rates');
    }
};
