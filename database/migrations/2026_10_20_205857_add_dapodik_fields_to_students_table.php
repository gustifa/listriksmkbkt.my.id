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
        Schema::table('students', function (Blueprint $table) {
            // Identitas Tambahan Siswa
            $table->string('nisn')->nullable()->after('nis');
            $table->string('nik')->nullable()->after('nisn');
            $table->string('pob')->nullable()->comment('Place of Birth / Tempat Lahir')->after('gender');
            $table->date('dob')->nullable()->comment('Date of Birth / Tanggal Lahir')->after('pob');
            $table->string('religion')->nullable()->after('dob');
            
            // Detail Alamat
            $table->string('rt', 10)->nullable()->after('address');
            $table->string('rw', 10)->nullable()->after('rt');
            $table->string('dusun')->nullable()->after('rw');
            $table->string('subdistrict')->nullable()->comment('Kelurahan')->after('dusun');
            $table->string('district')->nullable()->comment('Kecamatan')->after('subdistrict');
            $table->string('postal_code', 10)->nullable()->after('district');
            
            // Kontak & Informasi Sekolah Asal
            $table->string('email')->nullable()->after('phone');
            $table->string('previous_school')->nullable()->comment('Sekolah Asal')->after('email');

            // Data Ayah
            $table->string('father_name')->nullable()->after('previous_school');
            $table->string('father_nik')->nullable()->after('father_name');
            $table->string('father_education')->nullable()->after('father_nik');
            $table->string('father_job')->nullable()->after('father_education');
            $table->string('father_income')->nullable()->after('father_job');

            // Data Ibu
            $table->string('mother_name')->nullable()->after('father_income');
            $table->string('mother_nik')->nullable()->after('mother_name');
            $table->string('mother_education')->nullable()->after('mother_nik');
            $table->string('mother_job')->nullable()->after('mother_education');
            $table->string('mother_income')->nullable()->after('mother_job');

            // Data Wali
            $table->string('guardian_name')->nullable()->after('mother_income');
            $table->string('guardian_nik')->nullable()->after('guardian_name');
            $table->string('guardian_education')->nullable()->after('guardian_nik');
            $table->string('guardian_job')->nullable()->after('guardian_education');
            $table->string('guardian_income')->nullable()->after('guardian_job');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn([
                'nisn', 'nik', 'pob', 'dob', 'religion',
                'rt', 'rw', 'dusun', 'subdistrict', 'district', 'postal_code',
                'email', 'previous_school',
                'father_name', 'father_nik', 'father_education', 'father_job', 'father_income',
                'mother_name', 'mother_nik', 'mother_education', 'mother_job', 'mother_income',
                'guardian_name', 'guardian_nik', 'guardian_education', 'guardian_job', 'guardian_income',
            ]);
        });
    }
};