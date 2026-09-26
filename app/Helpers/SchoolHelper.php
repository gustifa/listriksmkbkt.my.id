<?php

use App\Models\Setting;

if (!function_exists('get_school_data')) {
    function get_school_data()
    {
        return [
            // Identitas Sekolah
            'name'       => Setting::value('school_name', 'SMK DEFAULT'),
            'address'    => Setting::value('school_address', 'Alamat Sekolah'),
            'phone'      => Setting::value('school_phone', '-'),
            'web'        => Setting::value('school_web', '-'),
            'email'      => Setting::value('school_email', '-'),
            'logo_left'  => Setting::value('logo_left'),
            'logo_right' => Setting::value('logo_right'),

            // Pengaturan Kertas
            'paper_size'        => Setting::value('paper_size', 'a4'),
            'paper_orientation' => Setting::value('paper_orientation', 'portrait'),

            // Pengaturan Margin (Tambahkan satuan cm/mm untuk CSS)
            'margin_top'    => Setting::value('margin_top', '2.5') . 'cm',
            'margin_right'  => Setting::value('margin_right', '2.5') . 'cm',
            'margin_bottom' => Setting::value('margin_bottom', '2.5') . 'cm',
            'margin_left'   => Setting::value('margin_left', '2.5') . 'cm',

            // Tanda Tangan
            'sign_city'  => Setting::value('signature_city', 'Jakarta'),
            'sign_title' => Setting::value('signature_title', 'Kepala Sekolah'),
            'sign_name'  => Setting::value('signature_name', 'Administrator'),
            'sign_nip'   => Setting::value('signature_nip', '-'),
        ];
    }
}