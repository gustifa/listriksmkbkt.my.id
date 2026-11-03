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
        Schema::table('assignments', function (Blueprint $table) {
            // Menambahkan kolom relasi TP dan Academic Year bertipe UUID
            if (!Schema::hasColumn('assignments', 'learning_objective_id')) {
                $table->uuid('learning_objective_id')->nullable()->after('subject_id');
                
                $table->foreign('learning_objective_id')
                      ->references('id')
                      ->on('learning_objectives')
                      ->onDelete('set null');
            }

            if (!Schema::hasColumn('assignments', 'academic_year_id')) {
                $table->uuid('academic_year_id')->nullable()->after('classroom_id');
                
                $table->foreign('academic_year_id')
                      ->references('id')
                      ->on('academic_years')
                      ->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assignments', function (Blueprint $table) {
            if (Schema::hasColumn('assignments', 'learning_objective_id')) {
                $table->dropForeign(['learning_objective_id']);
                $table->dropColumn('learning_objective_id');
            }

            if (Schema::hasColumn('assignments', 'academic_year_id')) {
                $table->dropForeign(['academic_year_id']);
                $table->dropColumn('academic_year_id');
            }
        });
    }
};