<?php

namespace App\Models;

use App\Modules\Transaction\Transaction;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionApproval extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'approver_id',
        'action',
        'comments',
        'level',
        'approval_order',
        'is_required',
        'action_taken_at',
        'conditions'
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'action_taken_at' => 'datetime',
        'conditions' => 'array'
    ];

    // Relationships
    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

}

