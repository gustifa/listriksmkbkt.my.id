<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\AttendanceSetting;

class AttendanceSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        AttendanceSetting::create([
        'late_limit_time' => '07:00:00',
        'early_departure_time' => '10:00:00',
    ]);
    }
}
