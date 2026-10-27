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
        Schema::table('internship_grades', function (Blueprint $table) {
            // 1. Hapus kolom-kolom penilaian lama yang kaku
            if (Schema::hasColumn('internship_grades', 'discipline')) {
                $table->dropColumn([
                    'discipline',
                    'teamwork',
                    'initiative',
                    'responsibility',
                    'technical_mastery',
                    'work_quality'
                ]);
            }

            // 2. Gunakan ->json() agar universal dan aman di seluruh versi database
            if (!Schema::hasColumn('internship_grades', 'grade_details')) {
                $table->json('grade_details')->nullable()->after('teacher_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('internship_grades', function (Blueprint $table) {
            $table->dropColumn('grade_details');

            $table->integer('discipline')->nullable();
            $table->integer('teamwork')->nullable();
            $table->integer('initiative')->nullable();
            $table->integer('responsibility')->nullable();
            $table->integer('technical_mastery')->nullable();
            $table->integer('work_quality')->nullable();
        });
    }
};