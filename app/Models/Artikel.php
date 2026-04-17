<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Artikel extends Model
{
    protected $connection = 'pjblNextgen';

    protected $collection = 'artikels';

    protected $fillable = [
        'judul_artikel',
        'url',
        'thumbnail',
    ];
}
