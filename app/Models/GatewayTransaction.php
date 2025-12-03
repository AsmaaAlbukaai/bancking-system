<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GatewayTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'gateway_id',
        'gateway_reference',
        'gateway_status',
        'gateway_response',
        'gateway_request',
        'gateway_fee',
        'currency',
        'initiated_at',
        'processed_at',
        'settled_at',
        'failure_reason',
        'retry_count'
    ];

    protected $casts = [
        'gateway_response' => 'array',
        'gateway_request' => 'array',
        'gateway_fee' => 'decimal:2',
        'initiated_at' => 'datetime',
        'processed_at' => 'datetime',
        'settled_at' => 'datetime'
    ];

    // Relationships
    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function gateway()
    {
        return $this->belongsTo(PaymentGateway::class);
    }

}