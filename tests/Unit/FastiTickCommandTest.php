<?php

namespace Bashtannik\Fasti\Tests\Unit;

use Bashtannik\Fasti\Console\Commands\FastiTickCommand;
use Bashtannik\Fasti\Facades\Fasti;
use Bashtannik\Fasti\Tests\Fake\FakeJob;
use Bashtannik\Fasti\Tests\TestCase;
use DateTime;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Bus;

class FastiTickCommandTest extends TestCase
{
    public function test_scheduled_job_dispatched()
    {
        // arrange

        $job = new FakeJob;

        Bus::fake();

        Fasti::fake();

        $dateTime = new DateTime('now');

        Fasti::schedule($job, $dateTime);

        // act

        Artisan::call(FastiTickCommand::class);

        // assert

        Bus::assertDispatched(FakeJob::class);
    }

    public function test_scheduled_later_job_not_dispatched()
    {
        // arrange

        $job = new FakeJob;

        Bus::fake();

        Fasti::fake();

        $dateTime = new DateTime('+1 hour');

        Fasti::schedule($job, $dateTime);

        // act

        Artisan::call(FastiTickCommand::class);

        // assert

        Bus::assertNotDispatched(FakeJob::class);
    }

    public function test_scheduled_later_job_dispatched_now()
    {
        // arrange

        $job = new FakeJob;

        Bus::fake();

        Fasti::fake();

        $dateTime = new DateTime('+1 hour');

        Fasti::schedule($job, $dateTime);

        // act

        Artisan::call(FastiTickCommand::class, ['--now' => $dateTime->format('Y-m-d H:i:s')]);

        // assert

        Bus::assertDispatched(FakeJob::class);
    }
}
