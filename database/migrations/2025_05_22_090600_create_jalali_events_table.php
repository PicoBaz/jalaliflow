<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jalali_events', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('frequency', ['daily', 'weekly', 'monthly', 'yearly']);
            $table->string('start_date');
            $table->string('next_run');
            $table->text('action');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jalali_events');
    }
};