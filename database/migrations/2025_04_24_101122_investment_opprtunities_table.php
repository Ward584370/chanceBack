<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('investment_opprtunities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('target_amount', 15, 2);
            $table->decimal('collected_amount', 15, 2);
            $table->foreignId('factory_id')->constrained()->cascadeOnDelete();
            $table->decimal('minimum_target', 15, 2)->nullable();
            $table->date('strtup')->nullable();
            $table->integer('payout_frequency')->default(1); // عدد الشهور
            $table->decimal('profit_percentage', 5, 2);
            $table->string('descrption')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('investment_opprtunities');
    }
};