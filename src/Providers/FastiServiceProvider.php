<?php

namespace Bashtannik\Fasti\Providers;

use Bashtannik\Fasti\Console\Commands\FastiListCommand;
use Bashtannik\Fasti\Console\Commands\FastiTickCommand;
use Bashtannik\Fasti\Contracts\JobDispatcher;
use Bashtannik\Fasti\Repositories\FastiEloquentRepository;
use Bashtannik\Fasti\Repositories\FastiScheduledJobsRepository;
use Bashtannik\Fasti\Services\BusJobDispatcher;
use Bashtannik\Fasti\Services\FastiService;
use Illuminate\Foundation\Console\AboutCommand;
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

        $this->app->when(FastiService::class)
            ->needs(JobDispatcher::class)
            ->give(BusJobDispatcher::class);
    }

    public function boot(): void
    {
        AboutCommand::add('Fasti', fn (): array => ['Version' => '1.0.0']);

        if ($this->app->runningInConsole()) {
            $this->commands([
                FastiTickCommand::class,
                FastiListCommand::class,
            ]);

            $this->publishesMigrations([
                __DIR__.'/../../database/migrations' => database_path('migrations'),
            ]);
        }
    }
}
