<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Account extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'account_number',
        'type',
        'account_name',
        'balance',
        'interest_rate',
        'credit_limit',
        'minimum_balance',
        'status',
        'parent_account_id',
        'group_id',
        'opened_at',
        'closed_at',
        'closure_reason'
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'interest_rate' => 'decimal:2',
        'credit_limit' => 'decimal:2',
        'minimum_balance' => 'decimal:2',
        'opened_at' => 'date',
        'closed_at' => 'date',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function parent()
    {
        return $this->belongsTo(Account::class, 'parent_account_id');
    }

    public function children()
    {
        return $this->hasMany(Account::class, 'parent_account_id');
    }

    public function group()
    {
        return $this->belongsTo(AccountGroup::class);
    }

    public function fromTransactions()
    {
        return $this->hasMany(Transaction::class, 'from_account_id');
    }

    public function toTransactions()
    {
        return $this->hasMany(Transaction::class, 'to_account_id');
    }

    public function transactions()
    {
        return Transaction::where(function($query) {
            $query->where('from_account_id', $this->id)
                  ->orWhere('to_account_id', $this->id);
        });
    }

    public function features()
    {
        return $this->belongsToMany(AccountFeature::class, 'account_feature_pivot')
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

    public function activeFeatures()
    {
        return $this->features()->wherePivot('is_active', true);
    }

    public function interestCalculations()
    {
        return $this->hasMany(InterestCalculation::class);
    }
}