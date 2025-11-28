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
            $table->string('document_type')->nullable();
            $table->string('designation')->nullable();
            $table->date('development_end')->nullable();
            $table->integer('page_count')->nullable();
            $table->date('development_start')->nullable();
            $table->string('block')->nullable();
            $table->string('author')->nullable();
            $table->decimal('cost', 15, 2)->nullable();
            $table->string('department')->nullable();
            $table->text('regulatory_documents')->nullable();
            $table->text('first_year_stages')->nullable();
            $table->text('subsequent_years_stages')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('planned_list', function (Blueprint $table) {
            $table->dropColumn([
                'document_type',
                'designation',
                'development_end',
                'page_count',
                'development_start',
                'block',
                'author',
                'cost',
                'department',
                'regulatory_documents',
                'first_year_stages',
                'subsequent_years_stages',
            ]);
        });
    }
};
