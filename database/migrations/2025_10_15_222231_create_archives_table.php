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
        Schema::create('archives', function (Blueprint $table) {
            $table->id();
            $table->string('code')->comment('№ п/п (шифр)');
            $table->string('name')->comment('Наименование разработки');
            $table->decimal('total_cost', 15, 2)->comment('Стоимость всего');
            $table->decimal('cost_2023', 15, 2)->comment('Стоимость на 2023');
            $table->decimal('cost_2024', 15, 2)->comment('Стоимость на 2024');
            $table->string('start_date')->comment('Срок начала разработки');
            $table->string('end_date')->comment('Срок окончания разработки');
            $table->string('organization')->comment('Наименование организаций, выполняющих работу');
            $table->string('type')->comment('Разрабатывается впервые или взамен');
            $table->string('year_period')->comment('Период (год)');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('archives');
    }
};