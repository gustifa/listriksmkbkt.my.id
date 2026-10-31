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
        Schema::table('izin_siswas', function (Blueprint $table) {
            // 1. Tambah Kolom 'type' (sakit/izin)
            if (!Schema::hasColumn('izin_siswas', 'type')) {
                $table->string('type', 50)->default('izin')->nullable()->after('student_id');
            }

            // 2. Tambah Kolom 'end_date'
            if (!Schema::hasColumn('izin_siswas', 'end_date')) {
                $table->date('end_date')->nullable()->after('date');
            }

            // 3. Tambah Kolom 'attachment_path'
            if (!Schema::hasColumn('izin_siswas', 'attachment_path')) {
                $table->string('attachment_path', 255)->nullable()->after('reason');
            }

            // 4. Tambah Kolom 'telegram_chat_id' & 'approved_by'
            if (!Schema::hasColumn('izin_siswas', 'telegram_chat_id')) {
                $table->string('telegram_chat_id', 255)->nullable()->after('attachment_path');
            }

            if (!Schema::hasColumn('izin_siswas', 'approved_by')) {
                $table->string('approved_by', 255)->nullable()->after('status');
            }
        });

        // 5. Tambahkan Foreign Key Relationship secara terpisah
        try {
            Schema::table('izin_siswas', function (Blueprint $table) {
                $table->foreign('student_id')
                      ->references('id')
                      ->on('students')
                      ->onDelete('cascade')
                      ->onUpdate('cascade');
            });
        } catch (\Exception $e) {
            // Abaikan jika foreign key sudah dipasang di PostgreSQL
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('izin_siswas', function (Blueprint $table) {
            try {
                $table->dropForeign(['student_id']);
            } catch (\Exception $e) {}

            // Hapus kolom hanya jika kolom tersebut terdeteksi ada
            $columnsToDrop = [];
            $cols = ['type', 'end_date', 'attachment_path', 'telegram_chat_id', 'approved_by'];

            foreach ($cols as $col) {
                if (Schema::hasColumn('izin_siswas', $col)) {
                    $columnsToDrop[] = $col;
                }
            }

            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};