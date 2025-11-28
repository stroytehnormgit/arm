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
        Schema::create('costs', function (Blueprint $table) {
            $table->id();
            $table->integer('year')->comment('Год');
            $table->decimal('average_monthly_salary', 15, 4)->nullable()->comment('Среднемесячная заработная плата');
            $table->decimal('document_volume_coefficient', 15, 4)->nullable()->default(1)->comment('Коэффициент, учитывающий объем документа');
            $table->decimal('mandatory_payments_qn', 15, 4)->nullable()->comment('Обязательные платежи (Qн)');
            $table->decimal('overhead_costs_qnr', 15, 4)->nullable()->comment('Накладные расходы организации-исполнителя(Qнр)');
            $table->decimal('profit_qp', 15, 4)->nullable()->comment('Прибыль Q(п)');
            $table->decimal('other_expenses_qpr', 15, 4)->nullable()->comment('Прочие расходы организации-исполнителя(Qпр)');
            $table->decimal('review_cost_sp', 15, 4)->nullable()->comment('Стоимость одного отзыва (Сп)');
            $table->timestamps();
            
            $table->unique('year');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('costs');
    }
};

