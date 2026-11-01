<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
// use Illuminate\Support\Facades\Crypt;

class ApiKey extends Model
{
    use HasFactory;

    protected $keyType = 'string';
    public $incrementing = true;

    protected $fillable = [
        'provider',
        'label_name',
        'api_key',
        'priority',
        'is_active',
    ];

    // protected static function boot()
    // {
    //     parent::boot();
    //     static::creating(function ($model) {
    //         if (empty($model->id)) {
    //             $model->id = (string) Str::uuid();
    //         }
    //     });
    // }

    // Mutator: Otomatis enkripsi API Key saat disimpan ke database
    // public function setApiKeyAttribute($value)
    // {
    //     $this->attributes['api_key'] = Crypt::encryptString($value);
    // }

    // Accessor: Otomatis dekripsi API Key saat dipanggil oleh sistem
    // public function getApiKeyAttribute($value)
    // {
    //     try {
    //         return Crypt::decryptString($value);
    //     } catch (\Exception $e) {
    //         return $value;
    //     }
    // }
}