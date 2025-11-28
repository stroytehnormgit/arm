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
        Schema::create('planned_list', function (Blueprint $table) {
            $table->id();
            $table->string('code')->comment('№ п/п (шифр)');
            $table->string('development_name')->comment('Наименование разработки');
            $table->decimal('total_cost', 15, 2)->comment('Стоимость всего');
            $table->decimal('cost_2025', 15, 2)->comment('Стоимость на 2025');
            $table->decimal('cost_2026', 15, 2)->comment('Стоимость на 2026');
            $table->string('start_date')->comment('Срок начала разработки');
            $table->string('end_date')->comment('Срок окончания разработки');
            $table->string('organizations')->comment('Наименование организаций, выполняющих работу');
            $table->string('development_type')->comment('Разрабатывается впервые или взамен');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('planned_list');
    }
};
