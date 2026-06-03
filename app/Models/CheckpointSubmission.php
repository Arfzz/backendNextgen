<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

/**
 * Stores files uploaded by students as proof for each fase checkpoint.
 *
 * Fields:
 *  - paket_beasiswa  : nama beasiswa (relasi utama)
 *  - class_id        : PaketBeasiswa._id
 *  - student_id      : User._id
 *  - checkpoint_title: nama fase (e.g. "Seleksi Wawancara")
 *  - file_url        : stored file path
 *  - submitted_at    : timestamp
 */
class CheckpointSubmission extends Model
{
    protected $connection = 'pjblNextgen';
    protected $collection = 'checkpoint_submissions';

    protected $fillable = [
        'paket_beasiswa',
        'class_id',
        'student_id',
        'checkpoint_title',
        'file_url',
        'submitted_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
    ];
}
