<?php

namespace App\Modules\Support;

use Illuminate\Support\ServiceProvider;

class SupportModuleServiceProvider extends ServiceProvider
{
    public function boot()
    {
        require __DIR__ . '/routes.php';
    }

    public function register() {}
}
