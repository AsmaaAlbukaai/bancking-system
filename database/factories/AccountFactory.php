<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Modules\Account\Account;

class AccountFactory extends Factory
{
    protected $model = \App\Modules\Account\Account::class;

    public function definition()
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'account_number' => $this->faker->unique()->numerify('##########'),
            'balance' => $this->faker->numberBetween(1000, 10000),
            'type' => 'savings',
            'status' => 'active',
            'currency' => 'USD',
        ];
    }

    public function savings()
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => 'savings',
            ];
        });
    }

    public function checking()
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => 'checking',
            ];
        });
    }
}
