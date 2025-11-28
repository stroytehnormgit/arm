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
        Schema::create('site_files', function (Blueprint $table) {
            $table->id();
            $table->date('placement_date')->comment('Дата размещения');
            $table->string('file_type')->comment('Тип файла');
            $table->string('development_name')->comment('Наименование разработки');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('site_files');
    }
};
