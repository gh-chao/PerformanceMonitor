<?php

namespace Lilocon\PerformanceMonitor;

use Illuminate\Support\ServiceProvider;

class PerformanceMonitorServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        $this->commands(\Lilocon\PerformanceMonitor\Commands\Analyze::class);

        if ($this->app->runningInConsole()) {
            return;
        }

        $this->app->singleton(Collector::class);

        $this->app->make(Collector::class)->start();

        /** @var \App\Http\Kernel $kernel */
        $kernel = $this->app->make(\Illuminate\Contracts\Http\Kernel::class);
        $kernel->pushMiddleware(Middleware::class);
        \Event::listen(\Illuminate\Database\Events\QueryExecuted::class, QueryListener::class);
    }
}