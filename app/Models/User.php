<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role',
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
}