<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PermitReason;

class PermitReasonSeeder extends Seeder
{
    public function run(): void
    {
        $reasons = [
            ['name' => 'Toilet', 'icon' => 'fas fa-restroom', 'color' => 'primary'],
            ['name' => 'UKS', 'icon' => 'fas fa-briefcase-medical', 'color' => 'danger'],
            ['name' => 'BK/Guru', 'icon' => 'fas fa-user-tie', 'color' => 'warning'],
            ['name' => 'Lainnya', 'icon' => 'fas fa-door-open', 'color' => 'secondary'],
        ];

        foreach ($reasons as $reason) {
            PermitReason::updateOrCreate(['name' => $reason['name']], $reason);
        }
    }
}
