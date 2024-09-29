<?php

declare(strict_types=1);

namespace Bashtannik\Fasti\Services;

use Bashtannik\Fasti\Models\ScheduledJob;
use Bashtannik\Fasti\Repositories\FastiScheduledJobsRepository;
use Carbon\CarbonInterface;
use DateTimeInterface;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Bus;
use RuntimeException;
use stdClass;

class FastiService
{
    public function __construct(protected FastiScheduledJobsRepository $repository) {}

    /**
     * Retrieve the repository.
     */
    public function getRepository(): FastiScheduledJobsRepository
    {
        return $this->repository;
    }

    /**
     * Set the repository.
     *
     * @return $this
     */
    public function setRepository(FastiScheduledJobsRepository $repository): static
    {
        $this->repository = $repository;

        return $this;
    }

    /**
     * Retrieve all scheduled jobs.
     *
     * @return Collection<int, ScheduledJob>|Collection<int|string, array{id: int|string, payload: stdClass, scheduled_at: CarbonInterface, canceled_at: CarbonInterface|null}>
     */
    public function all(): Collection
    {
        return $this->repository->all();
    }

    /**
     * Schedule a job to run at a specific time.
     */
    public function schedule(object $job, DateTimeInterface|CarbonInterface $at): int|string
    {
        return $this->repository->store($job, $at);
    }

    /**
     * List all scheduled jobs for a specific minute.
     */
    public function scheduled(DateTimeInterface|CarbonInterface $at): Collection
    {
        return $this->repository->scheduled($at);
    }

    /**
     * Cancel a scheduled job.
     */
    public function cancel(int|string $id): void
    {
        $this->repository->cancel($id);
    }

    /**
     * List all canceled jobs.
     */
    public function cancelled(): Collection
    {
        return $this->repository->cancelled();
    }

    public function dispatch(int|string $id): void
    {
        $scheduledJob = $this->repository->find($id);

        if (! str_starts_with((string) $scheduledJob->payload, 'O:')) {
            $data = decrypt($scheduledJob->payload);

            if (! is_string($data)) {
                throw new RuntimeException('Invalid job data, must be a serialized string');
            }

            $instance = unserialize($data);
        } else {
            $instance = unserialize($scheduledJob->payload);
        }

        if ($instance instanceof ShouldQueue) {
            Bus::dispatch($instance);
        } else {
            Bus::dispatchSync($instance);
        }
    }
}
