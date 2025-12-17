<?php

namespace App\Modules\Support;

use Illuminate\Database\Eloquent\Model;

class SupportTicket extends Model
{
    protected $table = 'support_tickets';

    protected $fillable = [
        'user_id',
        'type',
        'subject',
        'message',
        'status',
        'assigned_to_role',
        'last_reply_by',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function replies()
    {
        return $this->hasMany(TicketReply::class, 'ticket_id');
    }
}
