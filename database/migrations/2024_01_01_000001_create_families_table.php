<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('families', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('currency', 10)->default('USD');
            $table->string('currency_symbol', 5)->default('$');
            $table->string('timezone', 50)->default('Asia/Baghdad');
            $table->string('locale', 5)->default('en');
            $table->string('direction', 3)->default('ltr');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('families');
    }
};
