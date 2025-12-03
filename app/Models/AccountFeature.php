<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountFeature extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'monthly_fee',
        'setup_fee',
        'fee_type',
        'fee_config',
        'type',
        'requirements',
        'is_active',
        'sort_order'
    ];

    protected $casts = [
        'monthly_fee' => 'decimal:2',
        'setup_fee' => 'decimal:2',
        'fee_config' => 'array',
        'requirements' => 'array',
        'is_active' => 'boolean'
    ];

    // Relationships
    public function accounts()
    {
        return $this->belongsToMany(Account::class, 'account_feature_pivot')
            ->withPivot([
                'is_active',
                'custom_fee',
                'activated_at',
                'deactivated_at',
                'next_billing_date',
                'settings'
            ])
            ->withTimestamps();
    }

    public function activeAccounts()
    {
        return $this->accounts()->wherePivot('is_active', true);
    }
}