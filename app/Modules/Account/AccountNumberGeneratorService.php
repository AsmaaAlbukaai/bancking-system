<?php
namespace App\Modules\Account;

use App\Modules\Account\Account;

class AccountNumberGeneratorService
{
    public function generate(): string
    {
        do {
            // توليد رقم حساب عشوائي مكون من 12 رقم
            $number = str_pad(random_int(1000000, 9999999), 7, '0', STR_PAD_LEFT);
        } 
        while (Account::where('account_number', $number)->exists());

        return $number;
    }
}
