<?php

namespace App\Repositories;

use App\Modules\Account\Account;
use App\Modules\Account\Contracts\AccountRepositoryInterface;

/**
 * تنفيذ فعلي لعقد AccountRepositoryInterface باستخدام Eloquent
 * هذه الطبقة مسؤولة فقط عن التعامل مع قاعدة البيانات،
 * بينما تبقى قواعد العمل (Business Logic) في الخدمات.
 */
class AccountRepository implements AccountRepositoryInterface
{
    public function create(array $data): Account
    {
        return Account::create($data);
    }

    public function find(int $id): ?Account
    {
        return Account::find($id);
    }

    public function update(int $id, array $data): Account
    {
        $account = $this->find($id);

        if (! $account) {
            throw new \RuntimeException('Account not found');
        }

        $account->update($data);

        return $account;
    }

    public function delete(int $id): bool
    {
        $account = $this->find($id);

        if (! $account) {
            throw new \RuntimeException('Account not found');
        }

        return (bool) $account->delete();
    }
}

