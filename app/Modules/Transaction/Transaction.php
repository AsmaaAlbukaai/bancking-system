<?php

namespace App\Modules\Transaction;

use App\Models\GatewayTransaction;
use App\Models\TransactionApproval;
use App\Models\User;
use App\Modules\Account\Account;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Modules\Transaction\Recurring\RecurringStrategyFactory;


class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference',
        'from_account_id',
        'to_account_id',
        'amount',
        'fee',
        'tax',
        'net_amount',
        'type',
        'status',
        'description',
        'notes',
        'category',
        'metadata',
        'processed_at',
        'approved_at',
        'approved_by',
        'is_recurring',
        'recurring_frequency',
        'next_recurring_at'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'fee' => 'decimal:2',
        'tax' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'metadata' => 'array',
        'processed_at' => 'datetime',
        'approved_at' => 'datetime',
        'is_recurring' => 'boolean',
        'next_recurring_at' => 'datetime'
    ];

    // Relationships
    public function fromAccount()
    {
        return $this->belongsTo(Account::class, 'from_account_id');
    }

    public function toAccount()
    {
        return $this->belongsTo(Account::class, 'to_account_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function approvals()
    {
        return $this->hasMany(TransactionApproval::class);
    }

    public function gatewayTransactions()
    {
        return $this->hasMany(GatewayTransaction::class);
    }

public function getRecurringStrategy()
{
     return RecurringStrategyFactory::make($this->recurring_frequency);
}
}