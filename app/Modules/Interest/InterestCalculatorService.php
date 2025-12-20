<?php

namespace App\Modules\Interest;

use App\Models\InterestCalculation;
use App\Modules\Account\Account;
use App\Modules\Interest\InterestStrategyFactory;
use App\Modules\Interest\Strategies\InterestStrategyInterface;
use Illuminate\Support\Facades\Cache;

class InterestCalculatorService
{
    protected InterestStrategyFactory $factory;

    public function __construct(InterestStrategyFactory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * حساب الفائدة لحساب معين
     */
    public function calculateForAccount(
        Account $account,
        int $days,
        ?string $method = null
    ): InterestCalculation {

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

        // تحديد طريقة الحساب (الجديد من الإصدار الأول)
        $method = $method ?? 'simple'; // default
        $strategy = $this->factory->make($method, $account);

        $interestAmount = $strategy->calculate($principal, $rate, $days);

        // الضرائب (مثال 5%) - جديد من الإصدار الأول
        $taxRate = 0.05;
        $taxAmount = $interestAmount * $taxRate;
        $netInterest = $interestAmount - $taxAmount;

        // 4. احفظ نتيجة حساب الفائدة في قاعدة البيانات
        $calc = InterestCalculation::create([
            'account_id'          => $account->id,
            'principal'           => $principal,
            'interest_rate'       => $rate,
            'calculation_method'  => $method, // ✔ enum صحيح (من الإصدار الأول)
            'period'              => "{$days} days", // ✔ string (من الإصدار الأول)
            'days'                => $days,
            'interest_amount'     => $interestAmount,
            'tax_amount'          => $taxAmount, // جديد من الإصدار الأول
            'net_interest'        => $netInterest, // جديد من الإصدار الأول
            'total_amount'        => $principal + $netInterest, // معدل من الإصدار الأول
            'calculation_date'    => now(),
            'applicable_from'     => now(), // جديد من الإصدار الأول
            'applicable_to'       => now()->addDays($days), // جديد من الإصدار الأول
            'is_applied'          => false,
        ]);

        // 5. خزن النتيجة في الكاش لمدة ساعة (3600 ثانية)
        Cache::put($cacheKey, [
            'calculation_id' => $calc->id,
            'interest_amount' => $interestAmount,
            'total_amount' => $calc->total_amount,
            'net_interest' => $netInterest, // إضافة جديدة
            'tax_amount' => $taxAmount, // إضافة جديدة
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
