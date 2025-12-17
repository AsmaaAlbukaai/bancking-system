<?php

namespace App\Modules\Interest;

use App\Models\InterestCalculation;
use App\Modules\Account\Account;
use App\Modules\Interest\Strategies\InterestStrategyInterface;
use Illuminate\Support\Facades\Cache;


class InterestCalculatorService
{
    protected InterestStrategyInterface $strategy;

    public function __construct(InterestStrategyInterface $strategy)
    {
        $this->strategy = $strategy;
    }

    public function calculateForAccount(Account $account, int $days): InterestCalculation
    {
        // 1. إنشاء مفتاح كاش فريد
        $cacheKey = "interest_calc:account_{$account->id}:days_{$days}:rate_{$account->interest_rate}";
        
        // 2. محاولة جلب النتيجة من الكاش أولاً
        $cachedResult = Cache::get($cacheKey);
        
        if ($cachedResult) {
            // إذا كان هناك نتيجة مخزنة مؤقتًا، أرجعها
            return InterestCalculation::find($cachedResult['calculation_id']);
        }
        
        // 3. إذا لم تكن في الكاش، قم بالحساب
        $principal = (float) $account->balance;
        $rate = (float) $account->interest_rate;

        $interestAmount = $this->strategy->calculate($principal, $rate, $days);

        // 4. احفظ نتيجة حساب الفائدة في قاعدة البيانات
        $calc = InterestCalculation::create([
            'account_id' => $account->id,
            'principal' => $principal,
            'interest_rate' => $rate,
            'calculation_method' => get_class($this->strategy),
            'period' => $days . ' days',
            'days' => $days,
            'interest_amount' => $interestAmount,
            'total_amount' => $principal + $interestAmount,
            'calculation_date' => now(),
            'is_applied' => false,
            'net_interest' => $interestAmount,
            'applicable_from' => now(),
            'applicable_to' => now()->addDays($days),
        ]);

        // 5. خزن النتيجة في الكاش لمدة ساعة (3600 ثانية)
        Cache::put($cacheKey, [
            'calculation_id' => $calc->id,
            'interest_amount' => $interestAmount,
            'total_amount' => $calc->total_amount,
        ], 3600); // صلاحية ساعة واحدة

        return $calc;
    }
    public function invalidateCacheForAccount(Account $account): void
    {
        $keys = Cache::getRedis()->keys("interest_calc:account_{$account->id}:*");
        
        if (!empty($keys)) {
            foreach ($keys as $key) {
                $cleanKey = str_replace('laravel_database_', '', $key);
                Cache::forget($cleanKey);
            }
        }
}
}
