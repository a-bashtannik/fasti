<?php

declare(strict_types=1);

namespace Bashtannik\Fasti\Facades;

use Bashtannik\Fasti\Contracts\SchedulableJob;
use Bashtannik\Fasti\Repositories\FastiArrayRepository;
use Bashtannik\Fasti\Services\BusJobDispatcher;
use Bashtannik\Fasti\Services\FastiService;
use Carbon\CarbonInterface;
use Closure;
use DateTimeInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;
use PHPUnit\Framework\Assert as PHPUnit;

/**
 * @method static Collection<int, SchedulableJob> all()
 * @method static SchedulableJob schedule(object $job, DateTimeInterface|CarbonInterface $at)
 * @method static Collection<int, SchedulableJob> scheduled(DateTimeInterface|CarbonInterface|string $at)
 * @method static void cancel(int|string|SchedulableJob $id)
 * @method static Collection<int, SchedulableJob> cancelled()
 * @method static SchedulableJob find(int|string $id)
 * @method static void dispatch(int|string $id)
 * @method static FastiService getFacadeRoot()
 */
class Fasti extends Facade
{
    public static function fake(): void
    {
        $fakeRepository = new FastiArrayRepository;

        $fake = new FastiService($fakeRepository, new BusJobDispatcher);

        static::swap($fake);
    }

    protected static function getFacadeAccessor(): string
    {
        return 'fasti';
    }

    public static function assertScheduled(object|string $job, ?Closure $closure = null): void
    {
        $scheduled = static::getFacadeRoot()->all();

        PHPUnit::assertTrue(
            $scheduled->contains(
                function (SchedulableJob $scheduledJob) use ($job, $closure): bool {
                    $isSame = is_string($job) ? unserialize($scheduledJob->payload)::class === $job : $scheduledJob->payload === serialize($job);

                    return $isSame && (! $closure instanceof \Closure || $closure($scheduledJob));
                }),
            'The job was not scheduled.'
        );
    }

    public static function assertNotScheduled(object|string $job, ?Closure $closure = null): void
    {
        $scheduled = static::getFacadeRoot()->all();

        PHPUnit::assertFalse(
            $scheduled->contains(
                function (SchedulableJob $scheduledJob) use ($job, $closure): bool {
                    $isSame = is_string($job) ? unserialize($scheduledJob->payload)::class === $job : $scheduledJob->payload === serialize($job);

                    return $isSame && (! $closure instanceof \Closure || $closure($scheduledJob));
                }),
            'The job was not scheduled.'
        );
    }

    public static function assertScheduledAt(object|string $job, DateTimeInterface|CarbonInterface|string $at, ?Closure $closure = null): void
    {
        $scheduled = static::getFacadeRoot()->all();

        PHPUnit::assertTrue(
            $scheduled->contains(function (SchedulableJob $scheduledJob) use ($job, $closure, $at): bool {
                $isSame = is_string($job) ? unserialize($scheduledJob->payload)::class === $job : $scheduledJob->payload === serialize($job);

                return $isSame
                    && $scheduledJob->scheduled_at->isSameMinute($at)
                    && (! $closure instanceof \Closure || $closure($scheduledJob));
            }),
            'The job was not scheduled at the specified time.'
        );
    }

    public static function assertNotScheduledAt(object|string $job, DateTimeInterface|CarbonInterface|string $at, ?Closure $closure = null): void
    {
        $scheduled = static::getFacadeRoot()->all();

        PHPUnit::assertFalse(
            $scheduled->contains(function (SchedulableJob $scheduledJob) use ($job, $closure, $at): bool {
                $isSame = is_string($job) ? unserialize($scheduledJob->payload)::class === $job : $scheduledJob->payload === serialize($job);

                return $isSame
                    && $scheduledJob->scheduled_at->isSameMinute($at)
                    && (! $closure instanceof \Closure || $closure($scheduledJob));
            }),
            'The job was scheduled at the specified time.'
        );
    }

    public static function assertCancelled(object|string $job, ?Closure $closure = null): void
    {
        $canceled = static::getFacadeRoot()->cancelled();

        PHPUnit::assertTrue(
            $canceled->contains(
                function (SchedulableJob $scheduledJob) use ($job, $closure): bool {
                    $isSame = is_string($job) ? unserialize($scheduledJob->payload)::class === $job : $scheduledJob->payload === serialize($job);

                    return $isSame && (! $closure instanceof \Closure || $closure($scheduledJob));
                }),
            'The job was not canceled.'
        );
    }
}
