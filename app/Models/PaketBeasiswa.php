<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class PaketBeasiswa extends Model
{
    protected $connection = 'pjblNextgen';

    protected $collection = 'paket_beasiswas';

    protected $fillable = [
        'nama_beasiswa',
        'fase_checkpoint',
        'persyaratan',
        'benefit',
        'url',
        'gambar',
        'deadline',
        'harga',
    ];

    protected $casts = [
        'fase_checkpoint' => 'array',
        'persyaratan' => 'array',
        'benefit' => 'array',
        'harga' => 'integer',
        'deadline' => 'date',
    ];
}
