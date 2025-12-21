<?php

namespace App\Modules\Account;

class AccountCompositeService
{
    /**
     * احسب مجموع الرصيد لحساب (بما في ذلك الحسابات الفرعية) بصورة تكرارية
     */
    public function getTotalBalance(Account $account): float
    {
        $total = (float) $account->balance;

        // تأكد من تحميل children (يمكن eager load)
        foreach ($account->children as $child) {
            $total += $this->getTotalBalance($child);
        }

        return $total;
    }

    /**
     * جلب شجرة الحسابات (يمكن تحسين الأداء بإستخدام eager loading)
     */
    public function loadTree(Account $account, int $depth = 5): Account
{
    if ($depth <= 0) {
        return $account;
    }

     $account->load([
        'children' => function ($q) use ($depth) {
            $q->with('children');
        }
    ]);

    foreach ($account->children as $child) {
        $this->loadTree($child, $depth - 1);
    }

    return $account;
}

}
