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
        Schema::create('investments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->foreignId('opprtunty_id');
            $table->decimal('amount');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('investments');
         Schema::table('investments', function (Blueprint $table) {
        $table->dropForeign(['opprtunty_id']);
        $table->dropColumn('opprtunty_id');

        $table->dropForeign(['user_id']);
        $table->dropColumn('user_id');
    });
    }
};
