<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Previne lazy loading em dev — lança exceção em N+1 queries
        Model::preventLazyLoading(! app()->isProduction());

        // Previne atribuições silenciosas a campos não-fillable
        Model::preventSilentlyDiscardingAttributes(! app()->isProduction());
    }
}
