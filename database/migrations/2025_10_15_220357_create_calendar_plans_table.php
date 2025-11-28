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
        Schema::create('calendar_plans', function (Blueprint $table) {
            $table->id();
            $table->string('number')->comment('№');
            $table->string('stage')->comment('Стадии и виды работ');
            $table->string('deadline')->comment('Сроки выполнения');
            $table->decimal('amount', 15, 2)->comment('Сумма, руб');
            $table->string('result')->comment('Чем заканчивается стадия');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calendar_plans');
    }
};