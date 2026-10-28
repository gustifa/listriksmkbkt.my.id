<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tabel Subscriber (Orang Tua)
        Schema::create('telegram_subscribers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            // Mengacu ke tabel students yang juga menggunakan UUID
            $table->foreignUuid('student_id')->nullable()->constrained('students')->onDelete('cascade');
            
            $table->string('parent_name');
            $table->string('phone_number')->nullable();
            $table->string('chat_id')->unique();
            $table->string('username')->nullable();
            $table->timestamp('subscribed_at')->useCurrent();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Tabel Log Pengiriman Notifikasi
        Schema::create('telegram_notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            // Relasi ke telegram_subscribers via UUID
            $table->foreignUuid('telegram_subscriber_id')->nullable()->constrained('telegram_subscribers')->onDelete('set null');
            
            $table->string('recipient_name');
            $table->string('chat_id');
            $table->string('title');
            $table->text('message');
            $table->enum('type', ['absensi', 'ujian', 'pengumuman', 'pelanggaran'])->default('pengumuman');
            $table->enum('status', ['pending', 'success', 'failed'])->default('pending');
            $table->text('error_log')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('telegram_notifications');
        Schema::dropIfExists('telegram_subscribers');
    }
};