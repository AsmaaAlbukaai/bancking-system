<?php

namespace App\Modules\Account\Contracts;

use App\Modules\Account\Account;

/**
 * واجهة مجردة للوصول إلى بيانات الحسابات
 * تطبق مبدأ Dependency Inversion بحيث تعتمد الخدمات على هذا العقد
 * وليس على تنفيذ Eloquent معين.
 */
interface AccountRepositoryInterface
{
    public function create(array $data): Account;

    public function find(int $id): ?Account;

    public function update(int $id, array $data): Account;

    public function delete(int $id): bool;
}


