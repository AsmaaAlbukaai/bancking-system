<?php
namespace App\Modules\Account\States;

use App\Modules\Account\Account;

class ClosedState implements AccountStateInterface
{
    public function name(): string { return 'closed'; }

    public function canWithdraw(Account $account): bool { return false; }
    public function canDeposit(Account $account): bool { return false; }
    public function canTransfer(Account $account): bool { return false; }
}
