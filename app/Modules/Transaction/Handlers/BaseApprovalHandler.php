<?php

namespace App\Modules\Transaction\Handlers;

use App\Modules\Transaction\Transaction;

abstract class BaseApprovalHandler
{
    protected ?BaseApprovalHandler $next = null;

    public function setNext(BaseApprovalHandler $handler): BaseApprovalHandler
    {
        $this->next = $handler;
        return $handler;
    }

    /**
     * حاول معالجة المعاملة، إن لم تنجح مررها للمعالج التالي
     */
    public function handle(Transaction $transaction): bool
    {
        if ($this->canHandle($transaction)) {
            return $this->process($transaction);
        }

        if ($this->next) {
            return $this->next->handle($transaction);
        }

        // إذا لا أحد استلم المعاملة => فشل بالموافقة
        return false;
    }

    abstract protected function canHandle(Transaction $transaction): bool;
    abstract protected function process(Transaction $transaction): bool;
}
