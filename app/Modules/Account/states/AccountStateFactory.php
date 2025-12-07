<?php
namespace App\Modules\Account\States;

use App\Modules\Account\Account;

class AccountStateFactory
{
    public function make(Account $account): AccountStateInterface
    {
        return match ($account->status) {
            'active' => new ActiveState(),
            'suspended' => new SuspendedState(),
            'frozen' => new FrozenState(),
            'closed' => new ClosedState(),
            default => new ActiveState()
        };
    }
}
