<?php

declare(strict_types=1);

namespace Bashtannik\Fasti\Repositories;

use Bashtannik\Fasti\Contracts\SchedulableJob;
use Bashtannik\Fasti\Models\ScheduledJob;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use DateTimeInterface;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Support\Collection;

class FastiEloquentRepository implements FastiScheduledJobsRepository
{
    public function all(): Collection
    {
        return ScheduledJob::all();
    }

    public function store(object $job, DateTimeInterface|CarbonInterface $dateTime): int|string
    {
        $task = new ScheduledJob;

        if ($job instanceof ShouldBeEncrypted) {
            $task->payload = encrypt(serialize(clone $job));
        } else {
            $task->payload = serialize(clone $job);
        }

        $task->scheduled_at = CarbonImmutable::instance($dateTime);

        $task->save();

        return $task->id;
    }

    public function scheduled(DateTimeInterface|CarbonInterface $at): Collection
    {
        $from = Carbon::instance($at)->startOf('minute');
        $to = Carbon::instance($at)->endOf('minute');

        return ScheduledJob::query()
            ->whereNull('cancelled_at')
            ->whereBetween('scheduled_at', [$from, $to])->get();
    }

    public function cancel(string|int $id): void
    {
        $job = ScheduledJob::query()->findOrFail($id);

        $job->cancelled_at = now()->toImmutable();
        $job->save();
    }

    public function cancelled(): Collection
    {
        return ScheduledJob::query()->whereNotNull('cancelled_at')->get();
    }

    public function find(int|string $id): SchedulableJob
    {
        return ScheduledJob::query()->findOrFail($id);
    }
}
