<?php

namespace App\Providers;

use App\Models\Order;
use App\Policies\OrderPolicy;
use App\Services\Payment\PaymentInterface;
use App\Services\Payment\PaymentServices\SamanPaymentService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Http;
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
        $this->app->bind(PaymentInterface::class,SamanPaymentService::class);
        Gate::policy(Order::class, OrderPolicy::class);

        Http::fake([
            'https://www.test.com/saman/gateway' => Http::response([
                'redirect_url' => 'https://www.test.com/payment-page/test-transaction',
            ], 200),
            'https://www.test.com/saman/callback' => Http::response([
            ], 200),
        ]);
    }
}
