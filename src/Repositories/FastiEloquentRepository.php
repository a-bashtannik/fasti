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
    public static string $model = ScheduledJob::class;

    /**
     * @var array<string, class-string>
     */
    public static array $morphMap = [];

    public function all(): Collection
    {
        return static::$model::all();
    }

    /**
     * @param  array<string, class-string>  $morphMap
     */
    public static function enforceTypeMap(array $morphMap): void
    {
        static::$morphMap = $morphMap;
    }

    public function store(object $job, DateTimeInterface|CarbonInterface $dateTime): SchedulableJob
    {
        $scheduledJob = new static::$model;

        if (count(self::$morphMap) && isset(array_flip(self::$morphMap)[$job::class])) {
            $scheduledJob->type = array_flip(self::$morphMap)[$job::class];
        } else {
            $scheduledJob->type = $job::class;
        }

        if ($job instanceof ShouldBeEncrypted) {
            $scheduledJob->payload = encrypt(serialize(clone $job));
        } else {
            $scheduledJob->payload = serialize(clone $job);
        }

        $scheduledJob->scheduled_at = CarbonImmutable::instance($dateTime);
        $scheduledJob->save();

        return $scheduledJob;
    }

    public function scheduled(DateTimeInterface|CarbonInterface $at): Collection
    {
        $from = Carbon::instance($at)->startOf('minute');
        $to = Carbon::instance($at)->endOf('minute');

        return static::$model::query()
            ->whereNull('cancelled_at')
            ->whereBetween('scheduled_at', [$from, $to])->get();
    }

    public function cancel(string|int|SchedulableJob $id): SchedulableJob
    {
        $scheduledJob = static::$model::query()->findOrFail($id instanceof SchedulableJob ? $id->id : $id);

        $scheduledJob->cancelled_at = now()->toImmutable();
        $scheduledJob->save();

        return $scheduledJob;
    }

    public function cancelled(): Collection
    {
        return static::$model::query()->whereNotNull('cancelled_at')->get();
    }

    public function find(int|string $id): SchedulableJob
    {
        return static::$model::query()->findOrFail($id);
    }
}
