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
        Schema::table('planned_list', function (Blueprint $table) {
            $table->decimal('total_cost', 15, 2)->nullable()->change();
            $table->decimal('cost_2025', 15, 2)->nullable()->change();
            $table->decimal('cost_2026', 15, 2)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('planned_list', function (Blueprint $table) {
            $table->decimal('total_cost', 15, 2)->nullable(false)->change();
            $table->decimal('cost_2025', 15, 2)->nullable(false)->change();
            $table->decimal('cost_2026', 15, 2)->nullable(false)->change();
        });
    }
};

