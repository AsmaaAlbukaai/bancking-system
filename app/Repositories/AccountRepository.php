<?php
namespace App\Repositories;

use App\Modules\Account\Account;

class AccountRepository
{
    public function create(array $data)
    {
        return Account::create($data);
    }
    public function find($id)
{
    return \App\Modules\Account\Account::find($id);
}
    public function update($id, array $data)
{
    $account = $this->find($id); // أو getById()
    if (!$account) {
        throw new \Exception("Account not found");
    }

    $account->update($data);

    return $account;
}
    public function delete($id)
    {
        $account = $this->find($id);
        if (!$account) {
            throw new \Exception("Account not found");
        }

        return $account->delete();
    }
}
