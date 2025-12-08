<?php 
namespace App\Modules\Account\States;

use App\Modules\Account\Account;

class ActiveState implements AccountStateInterface
{
    public function name(): string { return 'active'; }

    public function canWithdraw(Account $account): bool { return true; }
    public function canDeposit(Account $account): bool { return true; }
    public function canTransfer(Account $account): bool { return true; }
}
