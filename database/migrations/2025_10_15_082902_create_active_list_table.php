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
        Schema::create('active_list', function (Blueprint $table) {
            $table->id();
            $table->string('code')->comment('№ п/п (шифр)');
            $table->string('development_name')->comment('Наименование разработки');
            $table->decimal('total_cost', 15, 2)->comment('Стоимость всего');
            $table->decimal('cost_2025', 15, 2)->comment('Стоимость на 2025');
            $table->decimal('cost_2026', 15, 2)->comment('Стоимость на 2026');
            $table->string('start_date')->comment('Срок начала');
            $table->string('end_date')->comment('Срок окончания');
            $table->string('organizations')->comment('Организации');
            $table->string('development_type')->comment('Тип разработки');
            $table->string('current_stage')->comment('Текущий этап');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('active_list');
    }
};
