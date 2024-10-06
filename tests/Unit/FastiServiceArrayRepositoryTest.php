<?php

declare(strict_types=1);

namespace Bashtannik\Fasti\Tests\Unit;

use Bashtannik\Fasti\Repositories\FastiArrayRepository;
use Bashtannik\Fasti\Services\BusJobDispatcher;
use Bashtannik\Fasti\Services\FastiService;
use Bashtannik\Fasti\Tests\Fake\FakeEncryptedJob;
use Bashtannik\Fasti\Tests\Fake\FakeJob;
use Bashtannik\Fasti\Tests\Fake\FakeQueuedJob;
use Bashtannik\Fasti\Tests\TestCase;
use DateTime;
use DateTimeImmutable;
use Illuminate\Support\Facades\Bus;

class FastiServiceArrayRepositoryTest extends TestCase
{
    public static FastiService $fasti;

    protected function setUp(): void
    {
        parent::setUp();

        self::$fasti = new FastiService(new FastiArrayRepository, new BusJobDispatcher);
    }

    public function test_can_schedule_job_at_date_time(): void
    {
        // arrange

        $job = new FakeJob;

        $dateTime = new DateTime('now');

        // act

        $scheduledJob = self::$fasti->schedule($job, $dateTime);

        // assert

        $scheduledJob = self::$fasti->getRepository()->find($scheduledJob->id);

        $this->assertEquals(serialize($job), $scheduledJob->payload, 'The job should be the one we scheduled.');
        $this->assertEquals($dateTime->format('Y-m-d H:i:s'), $scheduledJob->scheduled_at->format('Y-m-d H:i:s'), 'The scheduled date should be the one we scheduled.');
    }

    public function test_can_schedule_job_at_date_time_immutable(): void
    {
        // arrange

        $job = new FakeJob;

        $dateTime = new DateTimeImmutable('now');

        // act

        $scheduledJob = self::$fasti->schedule($job, $dateTime);

        // assert

        $scheduledJob = self::$fasti->getRepository()->find($scheduledJob->id);

        $this->assertEquals(serialize($job), $scheduledJob->payload, 'The job should be the one we scheduled.');
        $this->assertEquals($dateTime->format('Y-m-d H:i:s'), $scheduledJob->scheduled_at->format('Y-m-d H:i:s'), 'The scheduled date should be the one we scheduled.');
    }

    public function test_can_schedule_job_at_carbon(): void
    {
        // arrange

        $job = new FakeJob;

        $dateTime = now();

        // act

        $scheduledJob = self::$fasti->schedule($job, $dateTime);

        // assert

        $scheduledJob = self::$fasti->getRepository()->find($scheduledJob->id);

        $this->assertEquals(serialize($job), $scheduledJob->payload, 'The job should be the one we scheduled.');
        $this->assertEquals($dateTime->format('Y-m-d H:i:s'), $scheduledJob->scheduled_at->format('Y-m-d H:i:s'), 'The scheduled date should be the one we scheduled.');
    }

    public function test_can_schedule_job_at_carbon_immutable(): void
    {
        // arrange

        $job = new FakeJob;

        $dateTime = now();

        // act

        $scheduledJob = self::$fasti->schedule($job, $dateTime);

        // assert

        $scheduledJob = self::$fasti->getRepository()->find($scheduledJob->id);

        $this->assertEquals(serialize($job), $scheduledJob->payload, 'The job should be the one we scheduled.');
        $this->assertEquals($dateTime->format('Y-m-d H:i:s'), $scheduledJob->scheduled_at->format('Y-m-d H:i:s'), 'The scheduled date should be the one we scheduled.');
    }

    public function test_can_schedule_job_at_string(): void
    {
        // arrange

        $job = new FakeJob;

        $dateTime = now();

        // act

        $scheduledJob = self::$fasti->schedule($job, $dateTime->toDateTimeString());

        // assert

        $scheduledJob = self::$fasti->getRepository()->find($scheduledJob->id);

        $this->assertEquals(serialize($job), $scheduledJob->payload, 'The job should be the one we scheduled.');
        $this->assertEquals($dateTime->format('Y-m-d H:i:s'), $scheduledJob->scheduled_at->format('Y-m-d H:i:s'), 'The scheduled date should be the one we scheduled.');
    }

    public function test_can_schedule_encrypted_job(): void
    {
        // arrange

        $job = new FakeEncryptedJob;

        $dateTime = new DateTime('now');

        // act

        $scheduledJob = self::$fasti->schedule($job, $dateTime);

        // assert

        $scheduledJob = self::$fasti->getRepository()->find($scheduledJob->id);

        $this->assertEquals(serialize($job), $scheduledJob->payload, 'The job should be the one we scheduled.');
        $this->assertEquals($dateTime->format('Y-m-d H:i:s'), $scheduledJob->scheduled_at->format('Y-m-d H:i:s'), 'The scheduled date should be the one we scheduled.');
    }

    public function test_can_get_scheduled_jobs(): void
    {
        // arrange

        $job = new FakeJob;
        $scheduledJob = new FakeJob;

        $dateTime = new DateTime('now');

        $scheduledJob = self::$fasti->schedule($job, $dateTime);

        $cancelledId = self::$fasti->schedule($scheduledJob, $dateTime);
        self::$fasti->cancel($cancelledId);

        // act

        $jobs = self::$fasti->scheduled($dateTime);

        // assert

        $this->assertCount(1, $jobs, 'Only the scheduled job should be returned.');
        $this->assertEquals($scheduledJob, $jobs->first(), 'The scheduled job should be the one we scheduled.');
    }

    public function test_can_cancel_scheduled_job(): void
    {
        // arrange

        $job = new FakeJob;

        $dateTime = new DateTime('now');

        $scheduledJob = self::$fasti->schedule($job, $dateTime);

        // act

        self::$fasti->cancel($scheduledJob);

        // assert

        $job = self::$fasti->getRepository()->find($scheduledJob->id);

        $this->assertNotNull($job->cancelled_at, 'The job should be canceled.');
    }

    public function test_can_get_cancelled_jobs(): void
    {
        // arrange

        $job = new FakeJob;
        $canceledJob = new FakeJob;

        $dateTime = new DateTime('now');

        self::$fasti->schedule($job, $dateTime);

        $scheduledJob = self::$fasti->schedule($canceledJob, $dateTime);
        self::$fasti->cancel($scheduledJob);

        // act

        $jobs = self::$fasti->cancelled();

        // assert

        $this->assertCount(1, $jobs, 'Only the canceled job should be returned.');
        $this->assertEquals($scheduledJob, $jobs->first(), 'The canceled job should be the one we canceled.');
    }

    public function test_can_get_all_jobs(): void
    {
        // arrange

        $job = new FakeJob;
        $canceledJob = new FakeJob;

        $dateTime = new DateTime('now');

        self::$fasti->schedule($job, $dateTime);

        $scheduledJob = self::$fasti->schedule($canceledJob, $dateTime);
        self::$fasti->cancel($scheduledJob);

        // act

        $jobs = self::$fasti->all();

        // assert

        $this->assertCount(2, $jobs, 'Both jobs should be returned.');
    }

    public function test_can_dispatch_sync_job(): void
    {
        // arrange

        Bus::fake(FakeJob::class);

        $job = new FakeJob;

        $dateTime = new DateTime('now');

        $scheduledJob = self::$fasti->schedule($job, $dateTime);

        // act

        self::$fasti->dispatch($scheduledJob);

        // assert

        Bus::assertDispatchedSync(FakeJob::class);
    }

    public function test_can_dispatch_queued_job(): void
    {
        // arrange

        Bus::fake(FakeQueuedJob::class);

        $job = new FakeQueuedJob;

        $dateTime = new DateTime('now');

        $scheduledJob = self::$fasti->schedule($job, $dateTime);

        // act

        self::$fasti->dispatch($scheduledJob);

        // assert

        Bus::assertDispatched(FakeQueuedJob::class);
    }

    public function test_can_dispatch_encrypted_job(): void
    {
        // arrange

        Bus::fake(FakeEncryptedJob::class);

        $job = new FakeEncryptedJob;

        $dateTime = new DateTime('now');

        $scheduledJob = self::$fasti->schedule($job, $dateTime);

        // act

        self::$fasti->dispatch($scheduledJob);

        // assert

        Bus::assertDispatchedSync(FakeEncryptedJob::class);
    }
}
