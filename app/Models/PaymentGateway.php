<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentGateway extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'type',
        'provider',
        'base_url',
        'credentials',
        'config',
        'is_active',
        'is_test_mode',
        'transaction_fee',
        'percentage_fee',
        'min_amount',
        'max_amount',
        'supported_currencies',
        'supported_countries',
        'timeout_seconds',
        'retry_attempts',
        'webhook_config'
    ];

    protected $casts = [
        'credentials' => 'array',
        'config' => 'array',
        'is_active' => 'boolean',
        'is_test_mode' => 'boolean',
        'transaction_fee' => 'decimal:2',
        'percentage_fee' => 'decimal:2',
        'min_amount' => 'decimal:2',
        'max_amount' => 'decimal:2',
        'supported_currencies' => 'array',
        'supported_countries' => 'array',
        'webhook_config' => 'array'
    ];

    // Relationships
    public function gatewayTransactions()
    {
        return $this->hasMany(GatewayTransaction::class);
    }

}