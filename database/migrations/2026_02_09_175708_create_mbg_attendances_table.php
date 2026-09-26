<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mbg_attendances', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('student_id')->constrained('students')->onDelete('cascade');
            $table->date('date');
            $table->dateTime('taken_at')->nullable();
            $table->string('taken_method')->nullable();
            $table->string('taken_image')->nullable();
            $table->dateTime('returned_at')->nullable();
            $table->string('returned_method')->nullable();
            $table->string('returned_image')->nullable();
            $table->timestamps();
            // Cek satu per satu untuk menghindari error jika kolom sudah ada sebagian
            // if (!Schema::hasColumn('mbg_attendances', 'taken_at')) {
            //     $table->dateTime('taken_at')->nullable()->after('date');
            // }
            // if (!Schema::hasColumn('mbg_attendances', 'taken_method')) {
            //     $table->string('taken_method')->nullable()->after('taken_at');
            // }
            // if (!Schema::hasColumn('mbg_attendances', 'taken_image')) {
            //     $table->string('taken_image')->nullable()->after('taken_method');
            // }

            // if (!Schema::hasColumn('mbg_attendances', 'returned_at')) {
            //     $table->dateTime('returned_at')->nullable()->after('taken_image');
            // }
            // if (!Schema::hasColumn('mbg_attendances', 'returned_method')) {
            //     $table->string('returned_method')->nullable()->after('returned_at');
            // }
            // if (!Schema::hasColumn('mbg_attendances', 'returned_image')) {
            //     $table->string('returned_image')->nullable()->after('returned_method');
            // }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mbg_attendances');
        // Schema::table('mbg_attendances', function (Blueprint $table) {
        //     $table->dropColumn([
        //         'taken_at',
        //         'taken_method',
        //         'taken_image',
        //         'returned_at',
        //         'returned_method',
        //         'returned_image'
        //     ]);
        // });
    }
};
