<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    // {
    //     Schema::create('attendance_settings', function (Blueprint $table) {
    //         $table->id();
    //         $table->time('late_limit_time')->default('07:00:00'); // Batas jam masuk (lewat ini = terlambat)
    //         $table->time('early_departure_time')->default('10:00:00'); // Batas awal boleh pulang
    //         $table->timestamps();
    //     });
    // }

    {
        Schema::create('attendance_settings', function (Blueprint $table) {
            $table->id();

            // JAM MASUK (CHECK IN)
            $table->time('start_check_in_time')->default('06:00:00'); // Awal scan dibuka
            $table->time('late_limit_time')->default('07:00:00');     // Batas terlambat
            $table->time('end_check_in_time')->default('12:00:00');   // Batas akhir scan masuk (opsional)

            // JAM PULANG (CHECK OUT)
            $table->time('early_departure_time')->default('14:00:00'); // Awal boleh scan pulang

            $table->timestamps();
        });

        // Insert Default Data agar tidak error saat pertama kali run
        DB::table('attendance_settings')->insert([
            'start_check_in_time' => '06:00:00',
            'late_limit_time' => '07:15:00',
            'early_departure_time' => '15:00:00',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_settings');
    }
};
