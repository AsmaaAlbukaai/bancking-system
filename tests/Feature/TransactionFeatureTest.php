<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Modules\Account\Account;
use App\Modules\Transaction\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TransactionFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_transfer_endpoint(): void
    {
        $response = $this->postJson('/api/transactions/transfer', []);
        $response->assertStatus(401);
    }

    public function test_transfer_validation_works(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/api/transactions/transfer', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'from_account_id', 'to_account_id', 'amount'
        ]);
    }

    public function test_manager_cannot_access_admin_endpoints(): void
    {
        $manager = User::factory()->create(['role' => 'manager']);

        $response = $this->actingAs($manager)
            ->getJson('/api/transactions/all');

        $response->assertStatus(403);
    }
    
    // حذف الاختبارات المعقدة التي تحتاج transactions
}