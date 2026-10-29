<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('announcements', function (Blueprint $table) {
            $table->uuid('id')->primary(); // Menggunakan UUID sebagai Primary Key
            $table->string('title');
            $table->text('content');
            $table->string('target_role')->default('all'); // all, parent, student
            $table->unsignedBigInteger('classroom_id')->nullable(); // null jika untuk semua kelas
            $table->string('attachment')->nullable(); // file/gambar lampiran (opsional)
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};