<?php

declare(strict_types=1);

namespace Bashtannik\Fasti\Repositories;

use Bashtannik\Fasti\Contracts\SchedulableJob;
use Carbon\CarbonInterface;
use DateTimeInterface;
use Illuminate\Support\Collection;

interface FastiScheduledJobsRepository
{
    /**
     * @return Collection<int, SchedulableJob>|Collection<string, SchedulableJob>
     */
    public function all(): Collection;

    public function store(object $job, DateTimeInterface|CarbonInterface $dateTime): int|string;

    /**
     * @return Collection<int, SchedulableJob>|Collection<string, SchedulableJob>
     */
    public function scheduled(DateTimeInterface|CarbonInterface $at): Collection;

    public function cancel(int|string $id): void;

    /**
     * @return Collection<int, SchedulableJob>|Collection<string, SchedulableJob>
     */
    public function cancelled(): Collection;

    public function find(int|string $id): SchedulableJob;
}
