<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_guidances', function (Blueprint $table) {
            $table->string('agreement_file')->nullable()->after('photo_evidence')->comment('File surat perjanjian hasil upload siswa');
        });
    }

    public function down(): void
    {
        Schema::table('student_guidances', function (Blueprint $table) {
            $table->dropColumn('agreement_file');
        });
    }
};