<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
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
