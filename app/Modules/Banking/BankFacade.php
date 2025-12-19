<?php

namespace App\Modules\Banking;

use App\Modules\Account\Account;
use App\Modules\Account\AccountCompositeService;
use App\Modules\Interest\InterestCalculatorService;
use App\Modules\Transaction\Transaction;
use App\Modules\Transaction\TransactionService;

class BankFacade
{
    protected TransactionService $txService;
    protected InterestCalculatorService $interestService;
    protected AccountCompositeService $composite;

    public function __construct(
        TransactionService $txService,
        InterestCalculatorService $interestService,
        AccountCompositeService $composite
    ) {
        $this->txService = $txService;
        $this->interestService = $interestService;
        $this->composite = $composite;
    }

    public function transfer(Account $from, Account $to, float $amount, array $meta = [])
    {
        return $this->txService->transfer($from, $to, $amount, $meta);
    }

    public function customerTransaction($acc, $amount, $type, $meta = [])
    {
        return $this->txService->customerTransaction($acc, $amount, $type, $meta);
    }

    public function approveTransaction(Transaction $txn, $user)
    {
        return $this->txService->approveTransaction($txn, $user);
    }

    public function rejectTransaction(Transaction $txn, $user)
    {
        return $this->txService->rejectTransaction($txn, $user);
    }

    public function getPendingCustomerTransactions()
    {
        return $this->txService->customerPendingTransactions();
    }
    public function approveByManager(Transaction $txn, $manager)
   {
    return $this->txService->approveByManager($txn, $manager);
   }
   public function rejectByManager(Transaction $txn, $manager)
   {
    return $this->txService->rejectByManager($txn, $manager);
   }

    /**
     * تطبيق واضح لـ Composite Pattern على شجرة الحسابات:
     * نحسب الرصيد الكلي لحساب أب وجميع حساباته الفرعية (children)
     * عن طريق خدمة AccountCompositeService التي تتعامل مع الشجرة بالكامل.
     */
    public function getTotalBalance(Account $account): float
    {
        // تحميل الشجرة كاملة لتحسين الأداء وتقليل عدد الاستعلامات
        $this->composite->loadTree($account);

        return $this->composite->getTotalBalance($account);
    }
    public function getAccountTree(Account $account, int $depth = 5): Account
   {
    return $this->composite->loadTree($account, $depth);
    }
    public function calculateInterest(Account $account, int $days, ?string $method = null)
    {
        return $this->interestService->calculateForAccount($account, $days, $method);
    }
    /**
 * 🔹 سحب مبلغ من حساب
 */
     public function withdraw(Account $account, float $amount, array $meta = [])
{
    return $this->txService->customerTransaction(
        $account,
        $amount,
        'withdrawal',
        $meta
    );
}

/**
 * 🔹 إيداع مبلغ في حساب
 */
    public function deposit(Account $account, float $amount, array $meta = [])
{
    return $this->txService->customerTransaction(
        $account,
        $amount,
        'deposit',
        $meta
    );
}

}

