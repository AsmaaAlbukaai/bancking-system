<?php

namespace Tests\Feature;

use Tests\TestCase;

class AuthTest extends TestCase
{
    public function test_login_validation_fails_without_email()
    {
        $response = $this->postJson('/api/login', [
            'password' => '123456',
        ]);

        $response->assertStatus(422);
    }
}
