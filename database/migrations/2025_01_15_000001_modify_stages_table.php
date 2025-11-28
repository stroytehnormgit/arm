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
        $columnsToDrop = [];
        
        if (Schema::hasColumn('stages', 'start_date')) {
            $columnsToDrop[] = 'start_date';
        }
        if (Schema::hasColumn('stages', 'end_date')) {
            $columnsToDrop[] = 'end_date';
        }
        if (Schema::hasColumn('stages', 'amount')) {
            $columnsToDrop[] = 'amount';
        }
        
        if (!empty($columnsToDrop)) {
            Schema::table('stages', function (Blueprint $table) use ($columnsToDrop) {
                $table->dropColumn($columnsToDrop);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stages', function (Blueprint $table) {
            $table->string('start_date')->nullable()->comment('Начало этапа');
            $table->string('end_date')->nullable()->comment('Окончание этапа');
            $table->decimal('amount', 15, 2)->nullable()->comment('Сумма');
        });
    }
};

