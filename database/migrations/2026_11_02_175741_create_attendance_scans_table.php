<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_scans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->enum('scan_type', ['daily', 'learning', 'prayer']);
            $table->string('image_path');
            $table->foreignUuid('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('scan_date');
            $table->enum('status', ['pending', 'processed', 'failed'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_scans');
    }
};