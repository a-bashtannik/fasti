<?php

declare(strict_types=1);

namespace Bashtannik\Fasti\Facades;

use Bashtannik\Fasti\Contracts\SchedulableJob;
use Bashtannik\Fasti\Repositories\FastiArrayRepository;
use Bashtannik\Fasti\Services\FastiService;
use Carbon\CarbonInterface;
use Closure;
use DateTimeInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;
use PHPUnit\Framework\Assert as PHPUnit;

/**
 * @method static Collection<int, SchedulableJob> all()
 * @method static int|string schedule(object $job, DateTimeInterface|CarbonInterface $at)
 * @method static Collection<int, SchedulableJob> scheduled(DateTimeInterface|CarbonInterface $at)
 * @method static void cancel(int|string $id)
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

        $fake = new FastiService($fakeRepository);

        static::swap($fake);
    }

    protected static function getFacadeAccessor(): string
    {
        return 'fasti';
    }

    public static function assertScheduled(object $job, ?Closure $closure = null): void
    {
        $scheduled = static::getFacadeRoot()->all();

        PHPUnit::assertTrue(
            $scheduled->contains(fn (SchedulableJob $scheduledJob): bool => $scheduledJob->payload === serialize($job)
                && (! $closure instanceof \Closure || $closure($scheduledJob))
            ),
            'The job was not scheduled.'
        );
    }

    public static function assertNotScheduled(object $job, ?Closure $closure = null): void
    {
        $scheduled = static::getFacadeRoot()->all();

        PHPUnit::assertFalse(
            $scheduled->contains(fn (SchedulableJob $scheduledJob): bool => $scheduledJob->payload === serialize($job)
                && (! $closure instanceof \Closure || $closure($scheduledJob))
            ),
            'The job was scheduled.'
        );
    }

    public static function assertScheduledAt(object $job, DateTimeInterface|CarbonInterface $at, ?Closure $closure = null): void
    {
        $scheduled = static::getFacadeRoot()->all();

        PHPUnit::assertTrue(
            $scheduled->contains(fn (SchedulableJob $scheduledJob): bool => $scheduledJob->payload === serialize($job)
                && $scheduledJob->scheduled_at->isSameMinute($at)
                && (! $closure instanceof \Closure || $closure($scheduledJob))
            ),
            'The job was not scheduled at the specified time.'
        );
    }

    public static function assertNotScheduledAt(object $job, DateTimeInterface|CarbonInterface $at, ?Closure $closure = null): void
    {
        $scheduled = static::getFacadeRoot()->all();

        PHPUnit::assertFalse(
            $scheduled->contains(fn (SchedulableJob $scheduledJob): bool => $scheduledJob->payload === serialize($job)
                && $scheduledJob->scheduled_at->isSameMinute($at)
                && (! $closure instanceof \Closure || $closure($scheduledJob))
            ),
            'The job was scheduled at the specified time.'
        );
    }

    public static function assertCancelled(object $job, ?Closure $closure = null): void
    {
        $canceled = static::getFacadeRoot()->cancelled();

        PHPUnit::assertTrue(
            $canceled->contains(fn (SchedulableJob $canceledJob): bool => $canceledJob->payload === serialize($job)
                && (! $closure instanceof \Closure || $closure($canceledJob))
            ),
            'The job was not canceled.'
        );
    }
}
