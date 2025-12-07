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
    public function loadTree(Account $account, $depth = 5): Account
    {
        // مثال بسيط: preload children up to depth
        $account->load('children');
        if ($depth > 0) {
            foreach ($account->children as $child) {
                $this->loadTree($child, $depth - 1);
            }
        }
        return $account;
    }
}
