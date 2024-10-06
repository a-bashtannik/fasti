<?php

namespace Bashtannik\Fasti\Console\Commands;

use Bashtannik\Fasti\Services\FastiService;
use Illuminate\Console\Command;

use function Laravel\Prompts\table;

class FastiListCommand extends Command
{
    protected $signature = 'fasti:list';

    protected $description = 'List all scheduled jobs, add --all to include cancelled jobs.';

    public function handle(FastiService $fasti): void
    {
        $jobs = $fasti->all();

        table(
            array_values([
                'ID',
                'Type',
                'Scheduled at',
                'Cancelled at',
            ]),
            $jobs->map(fn ($job): array => [
                'id' => $job->id,
                'type' => $job->type,
                'scheduled_at' => $job->scheduled_at,
                'cancelled_at' => $job->cancelled_at ?? '-',
            ])->toArray()
        );
    }
}
