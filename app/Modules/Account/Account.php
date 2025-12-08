<?php
namespace App\Modules\Account;

use App\Models\AccountFeature;
use App\Models\AccountGroup;
use App\Models\AccountStatusChangeRequest;
use App\Models\InterestCalculation;
use App\Models\User;
use App\Modules\Account\States\AccountStateFactory;
use App\Modules\Account\States\AccountStateInterface;
use App\Modules\Transaction\Transaction;
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
    return $this->hasMany(Transaction::class, 'from_account_id')
        ->orWhere('to_account_id', $this->id);
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
    public function statusChangeRequests()
{
    return $this->hasMany(AccountStatusChangeRequest::class, 'account_id');
}
   public function getState(): AccountStateInterface
   {
    return (new AccountStateFactory())->make($this);
   }
}