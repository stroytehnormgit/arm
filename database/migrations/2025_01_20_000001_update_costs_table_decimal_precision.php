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
        Schema::table('costs', function (Blueprint $table) {
            $table->decimal('average_monthly_salary', 15, 4)->nullable()->change();
            $table->decimal('document_volume_coefficient', 15, 4)->nullable()->change();
            $table->decimal('mandatory_payments_qn', 15, 4)->nullable()->change();
            $table->decimal('overhead_costs_qnr', 15, 4)->nullable()->change();
            $table->decimal('profit_qp', 15, 4)->nullable()->change();
            $table->decimal('other_expenses_qpr', 15, 4)->nullable()->change();
            $table->decimal('review_cost_sp', 15, 4)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('costs', function (Blueprint $table) {
            $table->decimal('average_monthly_salary', 15, 2)->nullable()->change();
            $table->decimal('document_volume_coefficient', 15, 2)->nullable()->change();
            $table->decimal('mandatory_payments_qn', 15, 2)->nullable()->change();
            $table->decimal('overhead_costs_qnr', 15, 2)->nullable()->change();
            $table->decimal('profit_qp', 15, 2)->nullable()->change();
            $table->decimal('other_expenses_qpr', 15, 2)->nullable()->change();
            $table->decimal('review_cost_sp', 15, 2)->nullable()->change();
        });
    }
};

