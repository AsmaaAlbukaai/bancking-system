<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Account\Account;
use App\Models\User;

class AccountStatusChangeRequest extends Model
{
    protected $table = 'account_status_change_requests';

    protected $fillable = [
        'account_id',
        'requested_status',
        'current_status',
        'approval_level', // teller | manager
        'status',         // pending | approved | rejected
        'approved_by',
        'requested_by',
    ];

    protected $casts = [
        'approved_by' => 'integer',
    ];

    // علاقة مع الحساب
    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    // علاقة مع الموظف (الشخص الذي وافق)
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
    public function requester()
{
    return $this->belongsTo(User::class, 'requested_by');
}
}
