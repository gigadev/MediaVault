<?php

namespace App\Providers;

use App\Models\Family;
use App\Services\BorrowRequestService;
use App\Services\CheckoutService;
use App\Services\MediaLookupService;
use App\Services\PlanLimitService;
use Illuminate\Support\ServiceProvider;
use Laravel\Cashier\Cashier;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(CheckoutService::class);
        $this->app->singleton(BorrowRequestService::class);
        $this->app->singleton(PlanLimitService::class);
        $this->app->singleton(MediaLookupService::class);
    }

    public function boot(): void
    {
        Cashier::useCustomerModel(Family::class);
    }
}
