<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Builder;

class TransactionMacroServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // إضافة forAccount كمـاكرو
        Builder::macro('forAccount', function ($accountId) {
            return $this->where(function ($q) use ($accountId) {
                $q->where('from_account_id', $accountId)
                  ->orWhere('to_account_id', $accountId);
            });
        });
    }
}
