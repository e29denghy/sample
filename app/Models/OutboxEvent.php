<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OutboxEvent extends Model
{
    protected $fillable = [
        'event_type', 'aggregate_type', 'aggregate_id', 'aggregate_version', 'payload',
        'idempotency_key', 'status', 'attempts', 'available_at', 'locked_at', 'last_error', 'processed_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'available_at' => 'datetime',
        'locked_at' => 'datetime',
        'processed_at' => 'datetime',
    ];
}
