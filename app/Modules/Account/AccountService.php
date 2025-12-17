<?php

namespace App\Modules\Account;

use App\Models\User;
use App\Modules\Account\Contracts\AccountRepositoryInterface;
use App\Modules\Account\States\AccountStateFactory;
use App\Modules\Account\AccountNumberGeneratorService;

class AccountService
{
    /**
     * نعتمد على عقد مجرد بدلاً من تنفيذ محدد للـ Repository
     * تطبيقاً لمبدأ Dependency Inversion.
     */
    protected AccountRepositoryInterface $repo;
    protected AccountNumberGeneratorService $numberGenerator;
    protected AccountStateFactory $stateFactory;

    public function __construct(
        AccountRepositoryInterface $repo,
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

    public function getAccountById(int $id): ?Account
    {
        return $this->repo->find($id);
    }

    public function createAccount(array $data): Account
    {
        $data['account_number'] = $this->numberGenerator->generate();
        return $this->repo->create($data);
    }

    public function updateAccount(int $id, array $data): Account
    {
        return $this->repo->update($id, $data);
    }

    public function deleteAccount(int $id): bool
    {
        return $this->repo->delete($id);
    }

    public function getCustomerAccountsByUserId(int $userId): array
    {
        $user = User::where('role', 'customer')->findOrFail($userId);

        return [
            'user' => $user,
            'accounts' => $user->accounts()->with('transactions')->latest()->get(),
        ];
    }

    public function closeAccount(int $id, ?string $reason = null): array
    {
        $acc = $this->repo->find($id);

        if (! $acc) {
            return ['error' => 'Account not found'];
        }

        $state = $this->stateFactory->make($acc);

        if ($state->name() === 'closed') {
            return ['error' => 'Account already closed'];
        }

        $acc->update([
            'status' => 'closed',
            'closed_at' => now(),
            'closure_reason' => $reason ?? 'Closed by admin',
        ]);

        return ['message' => 'Account closed successfully'];
    }

    public function activateAccount(int $id): array
    {
        $acc = $this->repo->find($id);

        if (! $acc) {
            return ['error' => 'Account not found'];
        }

        $state = $this->stateFactory->make($acc);

        if ($state->name() === 'active') {
            return ['error' => 'Account already active'];
        }

        $acc->update([
            'status' => 'active',
            'closed_at' => null,
            'closure_reason' => null,
        ]);

        return ['message' => 'Account activated successfully'];
    }
}

