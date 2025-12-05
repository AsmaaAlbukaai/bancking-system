<?php

namespace App\Modules\Transaction;

use App\Http\Controllers\Controller;
use App\Modules\Account\Account;
use App\Modules\Banking\BankFacade;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    protected BankFacade $bank;

    public function __construct(BankFacade $bank)
    {
        $this->bank = $bank;
    }

    // تنفيذ تحويل
    public function transfer(Request $request)
    {
        $data = $request->validate([
            'from_account_id' => 'required|integer',
            'to_account_id'   => 'required|integer',
            'amount'          => 'required|numeric|min:1'
        ]);

        $from = Account::findOrFail($data['from_account_id']);
        $to = Account::findOrFail($data['to_account_id']);

        $txn = $this->bank->transfer(
            $from,
            $to,
            $data['amount']
        );

        return response()->json($txn);
    }

    // جلب عمليات حساب معين
    public function history($accountId)
    {
        $sx = \App\Modules\Transaction\Transaction::forAccount($accountId)->latest()->get();
        return response()->json($sx);
    }
}
