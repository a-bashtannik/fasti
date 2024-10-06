<?php

declare(strict_types=1);

namespace Bashtannik\Fasti\Tests\Unit;

use Bashtannik\Fasti\Facades\Fasti;
use Bashtannik\Fasti\Repositories\FastiArrayRepository;
use Bashtannik\Fasti\Services\FastiService;
use Bashtannik\Fasti\Tests\Fake\FakeEncryptedJob;
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

        $notScheduledEncryptedJob = new FakeEncryptedJob; // Other instance

        $dateTime = new DateTime('now');

        // act

        Fasti::schedule($job, $dateTime);

        // assert

        Fasti::assertScheduled($job);
        Fasti::assertScheduledAt($job, $dateTime);
        Fasti::assertScheduledAt($job, $dateTime->format('Y-m-d H:i:s'));
        Fasti::assertNotScheduled($notScheduledJob);
        Fasti::assertNotScheduledAt($job, new DateTime('tomorrow'));
        Fasti::assertNotScheduledAt($job, (new DateTime('tomorrow'))->format('Y-m-d H:i:s'));

        Fasti::assertScheduled($job::class);
        Fasti::assertScheduledAt($job::class, $dateTime);
        Fasti::assertScheduledAt($job::class, $dateTime->format('Y-m-d H:i:s'));
        Fasti::assertNotScheduled($notScheduledEncryptedJob::class); // Other instance
        Fasti::assertNotScheduledAt($job::class, new DateTime('tomorrow'));
        Fasti::assertNotScheduledAt($job::class, (new DateTime('tomorrow'))->format('Y-m-d H:i:s'));
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
