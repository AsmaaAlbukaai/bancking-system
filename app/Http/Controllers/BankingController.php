<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Services\Banking\BankFacade;

class BankingController extends Controller
{
    public function summary(BankFacade $bank, $accountId)
    {
        $acc = Account::findOrFail($accountId);

        return response()->json([
            'account' => $acc,
            'total_balance' => $bank->getTotalBalance($acc),
        ]);
    }
}
