<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Order extends Model
{
    protected $connection = 'pjblNextgen';
    protected $collection = 'orders';

    protected $fillable = [
        'order_id',       // "ORD-{userId}-{timestamp}"
        'user_id',
        'user_name',
        'user_email',
        'package_id',
        'package_name',
        'amount',         // integer, in IDR
        'status',         // pending | paid | failed | expired | cancelled
        'snap_token',
        'redirect_url',
        'midtrans_response', // raw webhook payload
        'paid_at',
    ];

    protected $casts = [
        'paid_at'    => 'datetime',
        'created_at' => 'datetime',
        'amount'     => 'integer',
    ];

    public function scopePending($q)   { return $q->where('status', 'pending'); }
    public function scopePaid($q)      { return $q->where('status', 'paid'); }
}
