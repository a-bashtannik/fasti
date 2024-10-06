<?php

declare(strict_types=1);

namespace Bashtannik\Fasti\Services;

use Bashtannik\Fasti\Contracts\JobDispatcher;
use Bashtannik\Fasti\Contracts\SchedulableJob;
use Bashtannik\Fasti\Events\JobCancelled;
use Bashtannik\Fasti\Events\JobDispatched;
use Bashtannik\Fasti\Events\JobScheduled;
use Bashtannik\Fasti\Repositories\FastiScheduledJobsRepository;
use Carbon\CarbonInterface;
use DateTimeInterface;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;
use RuntimeException;

class FastiService
{
    public function __construct(
        protected FastiScheduledJobsRepository $repository,
        protected JobDispatcher $dispatcher,
    ) {}

    /**
     * Retrieve the repository.
     */
    public function getRepository(): FastiScheduledJobsRepository
    {
        return $this->repository;
    }

    /**
     * Retrieve all scheduled jobs.
     *
     * @return Collection<int, SchedulableJob>
     */
    public function all(): Collection
    {
        return $this->repository->all();
    }

    /**
     * Schedule a job to run at a specific time.
     */
    public function schedule(object $job, DateTimeInterface|CarbonInterface|string $at): SchedulableJob
    {
        if (is_string($at)) {
            $at = Carbon::parse($at);
        }

        $scheduledJob = $this->repository->store($job, $at);

        Event::dispatch(new JobScheduled($scheduledJob));

        return $scheduledJob;
    }

    /**
     * List all scheduled jobs for a specific minute.
     * @return Collection<int, SchedulableJob>
     */
    public function scheduled(DateTimeInterface|CarbonInterface $at): Collection
    {
        return $this->repository->scheduled($at);
    }

    /**
     * Cancel a scheduled job.
     */
    public function cancel(int|string|SchedulableJob $id): void
    {
        $scheduledJob = $this->repository->cancel($id);

        Event::dispatch(new JobCancelled($scheduledJob));
    }

    /**
     * List all canceled jobs.
     * @return Collection<int, SchedulableJob>
     */
    public function cancelled(): Collection
    {
        return $this->repository->cancelled();
    }

    public function dispatch(int|string|SchedulableJob $id): void
    {
        $scheduledJob = $id instanceof SchedulableJob ? $id : $this->repository->find($id);

        if (! str_starts_with((string) $scheduledJob->payload, 'O:')) {
            $data = decrypt($scheduledJob->payload);

            if (! is_string($data)) {
                throw new RuntimeException('Invalid job data, must be a serialized string');
            }

            $instance = unserialize($data);
        } else {
            $instance = unserialize($scheduledJob->payload);
        }

        Event::dispatch(new JobDispatched($scheduledJob));

        if ($instance instanceof ShouldQueue) {
            $this->dispatcher->dispatch($instance);
        } else {
            $this->dispatcher->dispatchSync($instance);
        }
    }
}
