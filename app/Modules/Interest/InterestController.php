<?php

namespace App\Modules\Interest;

use App\Http\Controllers\Controller;
use App\Modules\Account\Account;
use App\Modules\Banking\BankFacade;

class InterestController extends Controller
{
    protected BankFacade $bank;

    public function __construct(BankFacade $bank)
    {
        $this->bank = $bank;
    }

    public function calculate($accountId)
    {
        $acc = Account::findOrFail($accountId);
        $calc = $this->bank->calculateInterest($acc, 30);

        return response()->json($calc);
    }
}
