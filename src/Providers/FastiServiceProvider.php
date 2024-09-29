<?php

namespace Bashtannik\Fasti\Providers;

use Bashtannik\Fasti\Console\Commands\FastiTickCommand;
use Bashtannik\Fasti\Repositories\FastiEloquentRepository;
use Bashtannik\Fasti\Repositories\FastiScheduledJobsRepository;
use Bashtannik\Fasti\Services\FastiService;
use Illuminate\Support\ServiceProvider;

class FastiServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            FastiScheduledJobsRepository::class,
            FastiEloquentRepository::class
        );

        $this->app->alias(
            FastiService::class,
            'fasti'
        );
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                FastiTickCommand::class,
            ]);
        }
    }
}
