<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Services\Banking\BankFacade;

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
