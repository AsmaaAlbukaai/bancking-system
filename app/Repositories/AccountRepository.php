<?php
namespace App\Repositories;

use App\Modules\Account\Account;

class AccountRepository
{
    public function create(array $data)
    {
        return Account::create($data);
    }
}
