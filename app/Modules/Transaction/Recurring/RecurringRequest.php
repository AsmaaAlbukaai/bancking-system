<?php
namespace App\Modules\Transaction\Recurring;

use App\Modules\Account\Account;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class RecurringRequest extends Model
{
    protected $fillable = [
        'user_id',
        'from_account_id',
        'to_account_id',
        'type',
        'amount',
        'frequency',
        'status',
        'approved_by'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function fromAccount()
    {
        return $this->belongsTo(Account::class, 'from_account_id');
    }

    public function toAccount()
    {
        return $this->belongsTo(Account::class, 'to_account_id');
    }
}
