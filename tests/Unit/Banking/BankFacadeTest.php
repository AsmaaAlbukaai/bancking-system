<?php

namespace Tests\Unit\Banking;

use Tests\TestCase;
use App\Modules\Banking\BankFacade;

class BankFacadeTest extends TestCase
{
    public function test_facade_exposes_transfer_method()
    {
        $facade = app(BankFacade::class);

        $this->assertTrue(method_exists($facade, 'transfer'));
    }
}
