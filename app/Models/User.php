<?php

namespace App\Models;
use App\Modules\Account\Account;
use App\Modules\Transaction\Transaction;
use App\Modules\Notification\Notification;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;


class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role',
        'email_verification_code',
        'email_verification_expires_at',

    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'email_verification_expires_at' => 'datetime',
        'password' => 'hashed',
    ];

    // Relationships
    public function accounts()
    {
        return $this->hasMany(Account::class);
    }

    public function accountGroups()
    {
        return $this->hasMany(AccountGroup::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function notificationPreference()
    {
        return $this->hasOne(NotificationPreference::class);
    }

    public function transactionApprovals()
    {
        return $this->hasMany(TransactionApproval::class, 'approver_id');
    }

    public function approvedTransactions()
    {
        return $this->hasMany(Transaction::class, 'approved_by');
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }
    public function approvedStatusChanges()
{
    return $this->hasMany(AccountStatusChangeRequest::class, 'approved_by');
}
public function requestedStatusChanges()
{
    return $this->hasMany(AccountStatusChangeRequest::class, 'requested_by');
}
}


