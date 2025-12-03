<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InterestCalculation extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'principal',
        'interest_rate',
        'calculation_method',
        'period',
        'days',
        'interest_amount',
        'total_amount',
        'tax_amount',
        'net_interest',
        'calculation_date',
        'applicable_from',
        'applicable_to',
        'is_applied',
        'applied_at',
        'applied_by',
        'calculation_details'
    ];

    protected $casts = [
        'principal' => 'decimal:2',
        'interest_rate' => 'decimal:2',
        'interest_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'net_interest' => 'decimal:2',
        'calculation_date' => 'date',
        'applicable_from' => 'date',
        'applicable_to' => 'date',
        'is_applied' => 'boolean',
        'applied_at' => 'datetime',
        'calculation_details' => 'array'
    ];

    // Relationships
    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function applier()
    {
        return $this->belongsTo(User::class, 'applied_by');
    }

}