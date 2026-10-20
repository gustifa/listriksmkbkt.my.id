<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('external_examiners', function (Blueprint $table) {
            $table->uuid('id')->primary();               // Primary Key UUID
            $table->string('name');                      // Nama Penguji Eksternal
            $table->string('company_name');              // Nama Instansi/DUDI
            $table->string('position')->nullable();       // Jabatan
            $table->string('logo')->nullable();           // Path file gambar logo DUDI
            $table->string('phone')->nullable();          // Kontak
            $table->text('address')->nullable();          // Alamat
            $table->timestamps();
        });

        // Relasi UUID di tabel ukk_assessments
        if (Schema::hasTable('ukk_assessments')) {
            Schema::table('ukk_assessments', function (Blueprint $table) {
                if (!Schema::hasColumn('ukk_assessments', 'external_examiner_id')) {
                    $table->foreignUuid('external_examiner_id')->nullable()->constrained('external_examiners')->onDelete('set null');
                }
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('external_examiners');
    }
};