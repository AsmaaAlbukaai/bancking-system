<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'action',
        'model_type',
        'model_id',
        'old_values',
        'new_values',
        'changed_values',
        'ip_address',
        'user_agent',
        'url',
        'method',
        'request_data',
        'event',
        'browser',
        'platform',
        'device',
        'country',
        'city'
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'changed_values' => 'array',
        'request_data' => 'array'
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function model()
    {
        return $this->morphTo();
    }

}