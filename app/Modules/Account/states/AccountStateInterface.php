<?php
namespace App\Modules\Account\States;

use App\Modules\Account\Account;

interface AccountStateInterface
{
    public function name(): string;

    public function canWithdraw(Account $account): bool;

    public function canDeposit(Account $account): bool;

    public function canTransfer(Account $account): bool;
}
