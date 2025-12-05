<?php

namespace App\Modules\Banking;

use App\Http\Controllers\Controller;
use App\Modules\Account\Account;

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
