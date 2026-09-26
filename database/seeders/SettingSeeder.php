<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingSeeder extends Seeder
{
    public function run()
    {
        $data = [
            'school_name'    => 'SMK NEGERI 1 BUKITTINGGI',
            'school_address' => 'Jl. Iskandar Teja Sukmana - Padang Gamuak, Kecamatan Guguk Panjang, Kota Bukittinggi',
            'school_phone'   => '085274817886',
            'school_web'     => 'www.smkn1bukittinggi.sch.id',
            'school_email'   => 'info@smkn1bukittinggi.sch.id',
        ];

        foreach ($data as $key => $val) {
            Setting::updateOrCreate(['key' => $key], ['value' => $val]);
        }
    }
}
