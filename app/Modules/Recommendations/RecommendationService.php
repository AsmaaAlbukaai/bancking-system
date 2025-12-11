<?php

namespace App\Modules\Recommendations;

use App\Models\User;
use App\Modules\Account\Account;

class RecommendationService
{
    public function getRecommendations(User $user)
    {
        $accounts = Account::where('user_id', $user->id)->get();

        if ($accounts->isEmpty()) {
            return [
                [
                    'code' => 'no_account',
                    'message' => 'لا يوجد حساب مرتبط بالمستخدم.',
                    'account_id' => null,
                    'account_number' => null
                ]
            ];
        }

        $output = [];

        foreach ($accounts as $account) {

            /*
            |-----------------------------------------
            | ١) رصيد عالي → اقترح حساب ادخار
            |-----------------------------------------
            */
            if ($account->balance > 10000) {
                $output[] = [
                    'code' => 'high_balance_saving',
                    'message' => 'ننصحك بفتح حساب ادخار للحصول على فوائد أعلى.',
                    'account_id' => $account->id,
                    'account_number' => $account->account_number,
                ];
            }

            /*
            |-----------------------------------------
            | ٢) عدد كبير من السحوبات → اقترح overdraft
            |-----------------------------------------
            */
            if (method_exists($account, 'getWithdrawalsCountAttribute')) {
                $withdrawals = $account->withdrawals_count;
            } else {
                // fallback لو ما عندك accessor
                $withdrawals = $account->fromTransactions()
                    ->where('type', 'withdrawal')
                    ->count();
            }

            if ($withdrawals > 20) {
                $output[] = [
                    'code' => 'overdraft',
                    'message' => 'تظهر تحركات متكررة، قد يناسبك تفعيل ميزة السحب على المكشوف.',
                    'account_id' => $account->id,
                    'account_number' => $account->account_number,
                ];
            }

            /*
            |-----------------------------------------
            | ٣) عمليات متكررة → اقترح recurring payments
            |-----------------------------------------
            */
            $recurringCount = $account->transactions()
                ->where('is_recurring', true)
                ->count();

            if ($recurringCount < 1) {
                $output[] = [
                    'code' => 'recurring',
                    'message' => 'يمكنك تفعيل الدفع التلقائي لتسهيل المعاملات الشهرية.',
                    'account_id' => $account->id,
                    'account_number' => $account->account_number,
                ];
            }
        }

        /*
        |-----------------------------------------
        | ٤) عدد الشكاوي للمستخدم → Premium Support
        |-----------------------------------------
        */
        $complaintsCount = $user->tickets()->where('type', 'complaint')->count();

        if ($complaintsCount > 3) {
            $output[] = [
                'code' => 'premium_support',
                'message' => 'يبدو أنك تواجه مشاكل متكررة، نقترح الاشتراك بالدعم المميز.',
                'account_id' => null, // التوصية تخص المستخدم نفسه
                'account_number' => null
            ];
        }

        if (empty($output)) {
            $output[] = [
                'code' => 'no_recommendations',
                'message' => 'لا توجد توصيات حالياً، حسابك في حالة جيدة!',
                'account_id' => null,
                'account_number' => null
            ];
        }

        return $output;
    }
}
