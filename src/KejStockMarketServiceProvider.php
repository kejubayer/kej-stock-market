<?php

namespace Kejubayer\StockMarket;

use Illuminate\Support\ServiceProvider;
use Kejubayer\StockMarket\Services\DseService;
use Kejubayer\StockMarket\Services\CseService;

class KejStockMarketServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(DseService::class, function () {
            return new DseService();
        });

        $this->app->singleton(CseService::class, function () {
            return new CseService();
        });
    }

    public function boot()
    {
        // Optional: publish config or other assets in future
    }
}
