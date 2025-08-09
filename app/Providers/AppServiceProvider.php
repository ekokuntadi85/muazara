<?php

namespace App\Providers;

use App\Models\Product;
use App\Observers\ProductObserver;
use App\Models\ProductBatch;
use App\Observers\ProductBatchObserver;
use App\Models\TransactionDetail;
use App\Observers\TransactionDetailObserver;
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
        Product::observe(ProductObserver::class);
        ProductBatch::observe(ProductBatchObserver::class);
        TransactionDetail::observe(TransactionDetailObserver::class);
    }
}