<?php

namespace App\Modules\Reports;

use Illuminate\Support\ServiceProvider;

class ReportsModuleServiceProvider extends ServiceProvider
{
    public function boot()
    {
        require __DIR__.'/routes.php';
    }
}
