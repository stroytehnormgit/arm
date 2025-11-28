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
        Schema::create('planned_list_stage', function (Blueprint $table) {
            $table->id();
            $table->foreignId('planned_list_id')->constrained('planned_list')->onDelete('cascade');
            $table->foreignId('stage_id')->constrained('stages')->onDelete('cascade');
            $table->string('start_date')->nullable()->comment('Начало этапа');
            $table->string('end_date')->nullable()->comment('Окончание этапа');
            $table->decimal('amount', 15, 2)->nullable()->comment('Сумма');
            $table->timestamps();
            
            $table->unique(['planned_list_id', 'stage_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('planned_list_stage');
    }
};

