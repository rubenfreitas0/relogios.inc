<?php

namespace App\Providers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Observers\OrderObserver;
use App\Observers\OrderItemObserver;
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

        // Fallback para o driver log caso não haja credenciais de email configuradas
        if (config('mail.default') === 'smtp' && (empty(config('mail.mailers.smtp.username')) || config('mail.mailers.smtp.username') === 'null')) {
            config(['mail.default' => 'log']);
        }

        // Register observers
        Order::observe(OrderObserver::class);
        OrderItem::observe(OrderItemObserver::class);
    }
}
