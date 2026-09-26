<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ramadan_journals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('student_id')->constrained('students')->onDelete('cascade');
            $table->date('date');
            
            // Puasa
            $table->enum('fasting_status', ['full', 'half', 'none'])->default('full'); // Penuh, Setengah, Tidak
            
            // Sholat Wajib (Boolean: 1=Ya, 0=Tidak)
            $table->boolean('prayer_subuh')->default(false);
            $table->boolean('prayer_dzuhur')->default(false);
            $table->boolean('prayer_ashar')->default(false);
            $table->boolean('prayer_maghrib')->default(false);
            $table->boolean('prayer_isya')->default(false);
            
            // Ibadah Sunnah
            $table->boolean('prayer_tarawih')->default(false);
            $table->boolean('prayer_witir')->default(false);
            $table->boolean('prayer_dhuha')->default(false);
            $table->boolean('prayer_tahajud')->default(false);
            
            // Al-Quran
            $table->boolean('read_quran')->default(false);
            $table->string('surah_name')->nullable(); // Nama Surat / Juz
            $table->string('ayat_range')->nullable(); // Ayat berapa

            // Catatan / Ceramah
            $table->text('notes')->nullable(); // Ringkasan Ceramah / Kegiatan Lain

            $table->timestamps();
            
            // Mencegah input ganda di hari yang sama
            $table->unique(['student_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ramadan_journals');
    }
};