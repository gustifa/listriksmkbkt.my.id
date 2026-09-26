<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Student;
use App\Models\Schedule;
use App\Models\Classroom;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    //use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run()
{
        $this->call(ClassroomSeeder::class);
        $this->call(SettingSeeder::class);
        $this->call(RoleSeeder::class);
        $this->call(UserSeeder::class);
        // $this->call(MapelSeeder::class);
}
}
