<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Setting;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Kita tidak mengubah struktur tabel, hanya insert data default
        Setting::firstOrCreate(['key' => 'logo_left'], ['value' => null]);
        Setting::firstOrCreate(['key' => 'logo_right'], ['value' => null]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Tidak perlu drop kolom, cukup hapus barisnya
        Setting::whereIn('key', ['logo_left', 'logo_right'])->delete();
    }
};
