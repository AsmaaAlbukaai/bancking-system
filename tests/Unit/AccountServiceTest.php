<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Modules\Account\AccountService;
use App\Modules\Account\AccountNumberGeneratorService;
use App\Modules\Account\States\AccountStateFactory;
use App\Modules\Account\Account;
use App\Modules\Account\Contracts\AccountRepositoryInterface;
use Mockery;

class AccountServiceTest extends TestCase
{
    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function create_account_generates_number_and_persists_via_repository()
    {
        // Arrange: mock repository contract (not concrete class)
        $repo = Mockery::mock(AccountRepositoryInterface::class);

        // number generator
        $generator = Mockery::mock(AccountNumberGeneratorService::class);
        $generator->shouldReceive('generate')->once()->andReturn('ACC-12345');

        // state factory is not used by createAccount; pass a dummy mock to satisfy constructor
        $stateFactory = Mockery::mock(AccountStateFactory::class);

        // expected data passed to repository
        $input = [
            'name'  => 'Ansam',
            'email' => 'ansamalmgdlawi@gmail.com',
        ];
        $expectedPersisted = $input + ['account_number' => 'ACC-12345'];

        // repository should return an Account model instance
        $repo->shouldReceive('create')
             ->once()
             ->with($expectedPersisted)
             ->andReturn(new Account(['id' => 1, 'account_number' => 'ACC-12345']));

        // Act
        $service = new AccountService($repo, $generator, $stateFactory);
        $result  = $service->createAccount($input);

        // Assert
        $this->assertInstanceOf(Account::class, $result);
        $this->assertSame('ACC-12345', (string) $result->account_number);
    }
}
