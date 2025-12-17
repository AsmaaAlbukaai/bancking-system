<?php

namespace App\Modules\Interest;

use App\Http\Controllers\Controller;
use App\Modules\Account\Account;
use App\Modules\Banking\BankFacade;
use Illuminate\Http\Request;

class InterestController extends Controller
{
    protected BankFacade $bank;

    public function __construct(BankFacade $bank)
    {
        $this->bank = $bank;
    }

    /**
     * حساب الفائدة لحساب معيّن
     * params:
     * - days: عدد الأيام (افتراضي 30)
     * - method: simple | compound (افتراضي يعتمد على نوع الحساب)
     */
    public function calculate(Request $request, $accountId)
    {
        $days = (int) $request->input('days', 30);
        $method = $request->input('method'); // optional: simple|compound

        $acc = Account::findOrFail($accountId);
        $calc = $this->bank->calculateInterest($acc, $days, $method);

        return response()->json($calc);
    }
}
