<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Modules\Account\Account;
use App\Modules\Account\AccountCompositeService;

class AccountCompositeServiceTest extends TestCase
{
    /** @test */
    public function it_sums_balance_recursively_for_account_tree()
    {
        $root = new Account(['balance' => 100]);
        $child1 = new Account(['balance' => 50]);
        $child2 = new Account(['balance' => 25]);
        $grand = new Account(['balance' => 10]);

        // children relations without DB
        $child2->setRelation('children', collect([$grand]));
        $root->setRelation('children', collect([$child1, $child2]));
        $child1->setRelation('children', collect());
        $grand->setRelation('children', collect());

        $service = new AccountCompositeService();
        $total = $service->getTotalBalance($root);

        $this->assertSame(185.0, $total);
    }
}
