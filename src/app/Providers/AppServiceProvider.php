<?php

namespace App\Providers;

use App\Models\Order;
use App\Policies\OrderPolicy;
use App\Services\Payment\AbstractPaymentService;
use App\Services\Payment\PaymentServices\SamanPaymentService;
use Illuminate\Auth\Access\Gate;
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
        $this->app->bind(AbstractPaymentService::class,SamanPaymentService::class);
        Gate::policy(Order::class, OrderPolicy::class);

    }
}
