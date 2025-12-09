<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Modules\Transaction\Transaction;
use App\Modules\Account\Account;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TransactionFeatureTest extends TestCase
{
    use RefreshDatabase;

    #[\PHPUnit\Framework\Attributes\Test]
    public function guest_cannot_access_transfer_endpoint()
    {
        $response = $this->postJson('/api/transactions/transfer', []);
        $response->assertStatus(401);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function transfer_validation_works()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/api/transactions/transfer', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'from_account_id', 'to_account_id', 'amount'
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function teller_can_approve_transaction()
    {
        $teller = User::factory()->create(['role' => 'teller']);
        $account = \App\Modules\Account\Account::factory()->create();

        // استخدم فاكتوري Transaction مع state deposit
        $txn = \App\Modules\Transaction\Transaction::factory()
            ->deposit()
            ->create([
                'to_account_id' => $account->id,
                'status' => 'pending',
            ]);

        $response = $this->actingAs($teller)
            ->postJson("/api/transactions/approve/{$txn->id}");

        $response->assertStatus(200);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function manager_cannot_access_admin_endpoints()
    {
        $manager = User::factory()->create(['role' => 'manager']);

        $response = $this->actingAs($manager)
            ->getJson('/api/transactions/all');

        $response->assertStatus(403);
    }
}
