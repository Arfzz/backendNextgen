<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

/**
 * Graduation record — one document per student per beasiswa.
 * Collection: graduations
 *
 * @property string      $student_id
 * @property string      $beasiswa_name
 * @property string      $mentor_id
 * @property string      $status          pending | lulus | gagal
 * @property string|null $proof_url
 * @property bool        $notified
 * @property string|null $testimonial_id
 */
class Graduation extends Model
{
    protected $connection = 'pjblNextgen';
    protected $collection = 'graduations';

    protected $fillable = [
        'student_id',
        'beasiswa_name',
        'mentor_id',
        'status',         // 'pending' | 'lulus' | 'gagal'
        'proof_url',
        'notified',       // false = belum popup, true = sudah popup
        'testimonial_id',
    ];

    protected $casts = [
        'notified' => 'boolean',
    ];
}
