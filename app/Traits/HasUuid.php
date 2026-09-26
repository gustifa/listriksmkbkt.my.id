<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait HasUuid
{
    protected static function bootHasUuid()
    {
        // Saat data baru dibuat (creating), otomatis isi ID dengan UUID
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    // Beri tahu Laravel bahwa Primary Key kita BUKAN auto-increment
    public function getIncrementing()
    {
        return false;
    }

    // Beri tahu Laravel bahwa tipe data Primary Key adalah STRING
    public function getKeyType()
    {
        return 'string';
    }
}