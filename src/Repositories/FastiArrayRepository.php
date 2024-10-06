<?php

declare(strict_types=1);

namespace Bashtannik\Fasti\Repositories;

use Bashtannik\Fasti\Contracts\SchedulableJob;
use Carbon\CarbonInterface;
use DateTimeInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Testing\Fakes\Fake;

class FastiArrayRepository implements Fake, FastiScheduledJobsRepository
{
    /**
     * @var array<int, SchedulableJob>
     */
    private array $jobsToFake = [];

    public function all(): Collection
    {
        return collect($this->jobsToFake);
    }

    public function store(object $job, DateTimeInterface|CarbonInterface $dateTime): SchedulableJob
    {
        $scheduledJob = new GenericScheduledJob(
            id: count($this->jobsToFake),
            type: $job::class,
            payload: serialize($job),
            scheduled_at: Carbon::instance($dateTime),
            cancelled_at: null,
        );

        $this->jobsToFake[] = $scheduledJob;

        return $scheduledJob;
    }

    public function scheduled(DateTimeInterface|CarbonInterface $at): Collection
    {
        return collect(
            array_filter($this->jobsToFake, fn (SchedulableJob $job): bool => $job->scheduled_at->isSameMinute($at) && $job->cancelled_at === null)
        );
    }

    public function cancel(int|string|SchedulableJob $id): SchedulableJob
    {
        $key = $id instanceof SchedulableJob ? $id->id : $id;

        $this->jobsToFake[$key]->cancelled_at = now();

        return $this->jobsToFake[$key];
    }

    public function cancelled(): Collection
    {
        return collect(array_filter($this->jobsToFake, fn (SchedulableJob $job): bool => $job->cancelled_at !== null));
    }

    public function find(int|string $id): SchedulableJob
    {
        return $this->jobsToFake[$id];
    }
}
