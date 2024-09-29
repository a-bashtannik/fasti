<?php

namespace Bashtannik\Fasti\Console\Commands;

use Bashtannik\Fasti\Services\FastiService;
use Illuminate\Console\Command;

use function Laravel\Prompts\table;

class FastiListCommand extends Command
{
    protected $signature = 'fasti:list --all';

    protected $description = 'List all scheduled jobs, add --all to include cancelled jobs.';

    public function handle(FastiService $fasti): void
    {
        $all = $this->option('all');

        $jobs = $all
            ? $fasti->all()
            : $fasti->scheduled(now());

        table(
            array_values([
                'ID',
                'Scheduled at',
                'Canceled at',
            ]),
            $jobs->only([
                'id',
                'scheduled_at',
                'canceled_at',
            ])->toArray()
        );
    }
}
