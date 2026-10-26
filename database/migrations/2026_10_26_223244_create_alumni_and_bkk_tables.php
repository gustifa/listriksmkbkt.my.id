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
        // 1. Tabel Profil Alumni (Detail Pelacak Alumni / Tracer Study)
        Schema::create('alumni_profiles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            // Menghubungkan ke tabel students (UUID)
            $table->uuid('student_id');
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            
            $table->year('graduation_year');
            $table->enum('current_status', ['working', 'studying', 'entrepreneur', 'searching'])
                  ->default('searching')
                  ->comment('Status: Bekerja, Kuliah, Wirausaha, Mencari Kerja');
            
            $table->string('current_company')->nullable()->comment('Nama Perusahaan/Kampus/Usaha');
            $table->string('position')->nullable()->comment('Jabatan/Program Studi');
            $table->string('phone_number', 20)->nullable();
            $table->string('linkedin_url')->nullable();
            $table->string('cv_file')->nullable()->comment('Path file CV (PDF)');
            $table->timestamps();
        });

        // 2. Tabel Kompetensi & Sertifikasi Alumni
        Schema::create('alumni_skills', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('student_id');
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            
            $table->string('skill_name')->comment('Contoh: PLC Programming, Instalasi Otomasi');
            $table->string('certificate_name')->nullable()->comment('Nama Sertifikat (BNSP/LSP)');
            $table->string('certificate_file')->nullable()->comment('File Bukti Sertifikat');
            $table->enum('level', ['basic', 'intermediate', 'advanced'])->default('basic');
            $table->timestamps();
        });

        // 3. Tabel Perusahaan / Mitra Industri (BKK)
        Schema::create('companies', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('industry_sector')->comment('Bidang Usaha (Manufaktur, Listrik, IT)');
            $table->string('email')->unique();
            $table->string('phone', 20);
            $table->text('address')->nullable();
            $table->string('logo')->nullable();
            $table->boolean('is_verified')->default(false)->comment('Verifikasi oleh Admin BKK');
            $table->timestamps();
        });

        // 4. Tabel Lowongan Kerja (Job Postings)
        Schema::create('job_postings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('company_id');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            
            $table->string('title')->comment('Contoh: Teknisi Listrik Industri');
            $table->text('description');
            $table->text('requirements')->nullable();
            $table->string('major_target')->nullable()->comment('Jurusan yang dicari, misal: TITL');
            $table->date('deadline')->nullable();
            $table->enum('status', ['open', 'closed'])->default('open');
            $table->timestamps();
        });

        // 5. Tabel Lamaran Kerja Alumni (Job Applications)
        Schema::create('job_applications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('job_posting_id');
            $table->foreign('job_posting_id')->references('id')->on('job_postings')->onDelete('cascade');
            
            $table->uuid('student_id');
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            
            $table->enum('status', ['pending', 'reviewed', 'shortlisted', 'accepted', 'rejected'])->default('pending');
            $table->timestamp('applied_at')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_applications');
        Schema::dropIfExists('job_postings');
        Schema::dropIfExists('companies');
        Schema::dropIfExists('alumni_skills');
        Schema::dropIfExists('alumni_profiles');
    }
};