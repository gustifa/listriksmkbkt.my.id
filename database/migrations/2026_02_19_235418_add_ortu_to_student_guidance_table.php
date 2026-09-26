<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_guidances', function (Blueprint $table) {
            $table->boolean('is_summoned')->default(false)->after('status')->comment('Apakah orang tua dipanggil?');
            $table->date('summon_date')->nullable()->after('is_summoned');
            $table->time('summon_time')->nullable()->after('summon_date');
            $table->string('summon_file')->nullable()->after('summon_time')->comment('File PDF Surat Panggilan');
        });
    }

    public function down(): void
    {
        Schema::table('student_guidances', function (Blueprint $table) {
            $table->dropColumn(['is_summoned', 'summon_date', 'summon_time', 'summon_file']);
        });
    }
};