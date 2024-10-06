<?php

namespace Bashtannik\Fasti\Console\Commands;

use Bashtannik\Fasti\Contracts\SchedulableJob;
use Bashtannik\Fasti\Facades\Fasti;
use Carbon\Carbon;
use Illuminate\Console\Command;

class FastiTickCommand extends Command
{
    protected $signature = 'fasti:tick {--now= : The time to tick to (Y-m-d H:i)}';

    protected $description = 'Get all scheduled jobs and dispatch them.';

    public function handle(): void
    {
        $now = $this->hasOption('now') ? Carbon::parse($this->option('now')) : now();

        $jobs = Fasti::scheduled($now);

        $jobs->each(function (SchedulableJob $job): void {

            $this->info('Dispatching job: '.$job->id);

            Fasti::dispatch($job->id);
        });
    }
}
