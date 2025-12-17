<?php

namespace Database\Factories\Modules\Transaction;

use App\Modules\Account\Account;
use Illuminate\Database\Eloquent\Factories\Factory;
use function Database\Factories\rand;
use function Database\Factories\time;

class TransactionFactory extends Factory
{
    public function definition()
    {
        return [
            'reference' => 'TRX-' . now()->timestamp . '-' . rand(1000, 9999),
            'from_account_id' => Account::factory(),
            'to_account_id' => Account::factory(),
            'amount' => 100,
            'fee' => 0,
            'tax' => 0,
            'net_amount' => 100,
            'type' => 'transfer',
            'status' => 'pending',
            'metadata' => [],
        ];
    }

    public function deposit()
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => 'deposit',
                'from_account_id' => null, // للإيداع from_account_id يمكن أن يكون null
                'to_account_id' => Account::factory(),
            ];
        });
    }

    public function withdrawal()
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => 'withdrawal',
                'from_account_id' => Account::factory(),
                'to_account_id' => null, // للسحب to_account_id يمكن أن يكون null
            ];
        });
    }
}
