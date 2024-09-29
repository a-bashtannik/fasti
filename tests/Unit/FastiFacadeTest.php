<?php

declare(strict_types=1);

namespace Bashtannik\Fasti\Tests\Unit;

use Bashtannik\Fasti\Facades\Fasti;
use Bashtannik\Fasti\Repositories\FastiArrayRepository;
use Bashtannik\Fasti\Services\FastiService;
use Bashtannik\Fasti\Tests\Fake\FakeJob;
use Bashtannik\Fasti\Tests\TestCase;
use DateTime;
use Illuminate\Support\Testing\Fakes\Fake;

class FastiFacadeTest extends TestCase
{
    public function test_can_fake(): void
    {
        // arrange

        Fasti::fake();

        // act

        $instance = Fasti::getFacadeRoot();

        // assert

        $this->assertInstanceOf(FastiService::class, $instance);
        $this->assertInstanceOf(Fake::class, $instance->getRepository());
        $this->assertInstanceOf(FastiArrayRepository::class, $instance->getRepository());
    }

    public function test_can_assert_scheduled_state(): void
    {
        // arrange

        Fasti::fake();

        $job = new FakeJob(1);

        $notScheduledJob = new FakeJob(2);

        $dateTime = new DateTime('now');

        // act

        Fasti::schedule($job, $dateTime);

        // assert

        Fasti::assertScheduled($job);
        Fasti::assertScheduledAt($job, $dateTime);
        Fasti::assertNotScheduled($notScheduledJob);
        Fasti::assertNotScheduledAt($job, new DateTime('tomorrow'));
    }

    public function test_can_assert_canceled_state(): void
    {
        // arrange

        Fasti::fake();

        $job = new FakeJob;

        $dateTime = new DateTime('now');

        // act

        $id = Fasti::schedule($job, $dateTime);

        Fasti::cancel($id);

        // assert

        Fasti::assertCancelled($job);
    }
}
