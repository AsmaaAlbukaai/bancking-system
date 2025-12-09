<?php

namespace App\Modules\Account;

use App\Models\User;
use App\Repositories\AccountRepository;
use App\Modules\Account\States\AccountStateFactory;
use App\Modules\Account\AccountNumberGeneratorService;

class AccountService
{
    protected AccountRepository $repo;
    protected AccountNumberGeneratorService $numberGenerator;
    protected AccountStateFactory $stateFactory;

    public function __construct(
        AccountRepository $repo,
        AccountNumberGeneratorService $generator,
        AccountStateFactory $stateFactory
    ) {
        $this->repo = $repo;
        $this->numberGenerator = $generator;
        $this->stateFactory = $stateFactory;
    }

    public function getUserAccounts(User $user)
    {
        return $user->accounts()->with('children')->get();
    }

    public function getAccountById($id)
    {
        return $this->repo->find($id);
    }

    public function createAccount(array $data)
    {
        $data['account_number'] = $this->numberGenerator->generate();
        return $this->repo->create($data);
    }

    public function updateAccount($id, array $data)
    {
        return $this->repo->update($id, $data);
    }

    public function deleteAccount($id)
    {
        return $this->repo->delete($id);
    }

    public function getCustomerAccountsByUserId($userId)
    {
        $user = User::where('role', 'customer')->findOrFail($userId);

        return [
            'user' => $user,
            'accounts' => $user->accounts()->with('transactions')->latest()->get()
        ];
    }

    public function closeAccount($id, $reason = null)
    {
        $acc = $this->repo->find($id);

        $state = $this->stateFactory->make($acc);

        if ($state->name() === 'closed') {
            return ['error' => 'Account already closed'];
        }

        $acc->update([
            'status' => 'closed',
            'closed_at' => now(),
            'closure_reason' => $reason ?? 'Closed by admin'
        ]);

        return ['message' => 'Account closed successfully'];
    }

    public function activateAccount($id)
    {
        $acc = $this->repo->find($id);

        $state = $this->stateFactory->make($acc);

        if ($state->name() === 'active') {
            return ['error' => 'Account already active'];
        }

        $acc->update([
            'status' => 'active',
            'closed_at' => null,
            'closure_reason' => null
        ]);

        return ['message' => 'Account activated successfully'];
    }
}
