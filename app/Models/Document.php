<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Document extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'judul', 
        'nomor_surat', 
        'kategori', 
        'tanggal_dokumen', 
        'file_path', 
        'keterangan'
    ];
}
