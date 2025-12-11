<?php

namespace App\Modules\Recommendations;

use Illuminate\Support\ServiceProvider;

class RecommendationModuleServiceProvider extends ServiceProvider
{
    public function boot()
    {
        require __DIR__.'/routes.php';
    }

    public function register() {}
}
