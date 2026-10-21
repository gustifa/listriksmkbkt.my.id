<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids; // Import HasUuids

class Setting extends Model
{
    use HasFactory, HasUuids; // Tambahkan HasUuids

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = ['key', 'value'];
    // protected $guarded = [];

    // Helper static untuk mengambil nilai setting dengan cepat
    // Cara pakai: Setting::value('school_name')
    public static function value($key, $default = null)
    {
        $setting = self::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }
}
