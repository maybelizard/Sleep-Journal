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
        Schema::create('habit_sleep_entry', function (Blueprint $table) {
            $table->foreignId('habit_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sleep_entry_id')->constrained()->cascadeOnDelete();
            $table->primary(['habit_id', 'sleep_entry_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('habit_sleep_entry');
    }
};
