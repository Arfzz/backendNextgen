<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Document extends Model
{
    protected $connection = 'pjblNextgen';

    protected $collection = 'documents';

    public $timestamps = false;

    protected $fillable = [
        'class_id',
        'title',
        'file_url',
        'uploaded_by',
        'uploaded_at',
    ];

    protected $casts = [
        'uploaded_at' => 'datetime',
    ];
}
