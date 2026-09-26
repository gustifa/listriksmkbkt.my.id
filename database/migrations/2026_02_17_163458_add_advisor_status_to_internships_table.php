<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('internships', function (Blueprint $table) {
            // null = belum ada, pending = request siswa, approved = disetujui/ditentukan admin
            $table->enum('advisor_status', ['pending', 'approved'])->nullable()->after('advisor_id');
        });
    }

    public function down(): void
    {
        Schema::table('internships', function (Blueprint $table) {
            $table->dropColumn('advisor_status');
        });
    }
};