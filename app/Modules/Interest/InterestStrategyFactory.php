<?php

namespace App\Modules\Interest;

use App\Modules\Account\Account;
use App\Modules\Interest\Strategies\CompoundInterestStrategy;
use App\Modules\Interest\Strategies\InterestStrategyInterface;
use App\Modules\Interest\Strategies\SimpleInterestStrategy;

/**
 * يحدد طريقة حساب الفائدة بناءً على نوع الحساب أو طريقة مطلوبة صراحةً.
 * - method: "compound" أو "simple" (افتراضي).
 * - يمكن توسيعها لاحقاً لدعم أنواع حسابات إضافية أو معدلات مركبة مختلفة.
 */
class InterestStrategyFactory
{
    public function make(?string $method = null, ?Account $account = null): InterestStrategyInterface
    {
        $method = strtolower($method ?? $this->defaultMethodForAccount($account));

        return match ($method) {
            'compound' => new CompoundInterestStrategy(), // افتراضي: شهري (12)
            default     => new SimpleInterestStrategy(),
        };
    }

    /**
     * اختيار افتراضي بسيط: نجعل حسابات الادخار/التمويل المركّب تستخدم compound،
     * والبقية simple. يمكن تحسينها مستقبلاً بحقل مخصص في الحساب.
     */
    protected function defaultMethodForAccount(?Account $account): string
    {
        if (! $account) {
            return 'simple';
        }

        return match ($account->type) {
            'savings', 'loan' => 'compound',
            default           => 'simple',
        };
    }
}


