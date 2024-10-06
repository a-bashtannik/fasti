<?php

namespace Bashtannik\Fasti\Tests\Unit;

use Bashtannik\Fasti\Events\JobCancelled;
use Bashtannik\Fasti\Events\JobDispatched;
use Bashtannik\Fasti\Events\JobScheduled;
use Bashtannik\Fasti\Models\ScheduledJob;
use Bashtannik\Fasti\Repositories\FastiEloquentRepository;
use Bashtannik\Fasti\Services\BusJobDispatcher;
use Bashtannik\Fasti\Services\FastiService;
use Bashtannik\Fasti\Tests\Fake\FakeEncryptedJob;
use Bashtannik\Fasti\Tests\Fake\FakeJob;
use Bashtannik\Fasti\Tests\Fake\FakeQueuedJob;
use Bashtannik\Fasti\Tests\TestCase;
use DateTime;
use DateTimeImmutable;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Event;

class FastiServiceEloquentRepositoryTest extends TestCase
{
    public static FastiService $fasti;

    protected function setUp(): void
    {
        parent::setUp();

        self::$fasti = new FastiService(new FastiEloquentRepository, new BusJobDispatcher);
    }

    public function test_can_schedule_job_at_date_time(): void
    {
        // arrange

        Event::fake(JobScheduled::class);

        $job = new FakeJob;

        $dateTime = new DateTime('now');

        // act

        $scheduledJob = self::$fasti->schedule($job, $dateTime);

        // assert

        $this->assertInstanceOf(ScheduledJob::class, $scheduledJob);

        $this->assertDatabaseHas('scheduled_jobs', [
            'payload' => serialize($job),
            'type' => FakeJob::class,
            'scheduled_at' => $dateTime->format('Y-m-d H:i:s'),
        ]);

        Event::assertDispatched(JobScheduled::class);
    }

    public function test_can_schedule_job_at_date_time_immutable(): void
    {
        // arrange

        Event::fake(JobScheduled::class);

        $job = new FakeJob;

        $dateTime = new DateTimeImmutable('now');

        // act

        $scheduledJob = self::$fasti->schedule($job, $dateTime);

        // assert

        $this->assertInstanceOf(ScheduledJob::class, $scheduledJob);

        $this->assertDatabaseHas('scheduled_jobs', [
            'payload' => serialize($job),
            'scheduled_at' => $dateTime->format('Y-m-d H:i:s'),
        ]);

        Event::assertDispatched(JobScheduled::class);
    }

    public function test_can_schedule_job_at_carbon(): void
    {
        // arrange

        Event::fake(JobScheduled::class);

        $job = new FakeJob;

        $dateTime = now();

        // act

        $scheduledJob = self::$fasti->schedule($job, $dateTime);

        // assert

        $this->assertInstanceOf(ScheduledJob::class, $scheduledJob);

        $this->assertDatabaseHas('scheduled_jobs', [
            'payload' => serialize($job),
            'scheduled_at' => $dateTime->format('Y-m-d H:i:s'),
        ]);

        Event::assertDispatched(JobScheduled::class);
    }

    public function test_can_schedule_job_at_carbon_immutable(): void
    {
        // arrange

        Event::fake(JobScheduled::class);

        $job = new FakeJob;

        $dateTime = now()->toImmutable();

        // act

        $scheduledJob = self::$fasti->schedule($job, $dateTime);

        // assert

        $this->assertInstanceOf(ScheduledJob::class, $scheduledJob);

        // assert

        $this->assertDatabaseHas('scheduled_jobs', [
            'payload' => serialize($job),
            'scheduled_at' => $dateTime->format('Y-m-d H:i:s'),
        ]);

        Event::assertDispatched(JobScheduled::class);
    }

    public function test_can_schedule_job_at_string(): void
    {
        // arrange

        Event::fake(JobScheduled::class);

        $job = new FakeJob;

        $dateTime = now();

        // act

        $scheduledJob = self::$fasti->schedule($job, $dateTime);

        // assert

        $this->assertInstanceOf(ScheduledJob::class, $scheduledJob);

        $this->assertDatabaseHas('scheduled_jobs', [
            'payload' => serialize($job),
            'scheduled_at' => $dateTime->format('Y-m-d H:i:s'),
        ]);

        Event::assertDispatched(JobScheduled::class);
    }

    public function test_can_schedule_encrypted_job(): void
    {
        // arrange

        Event::fake(JobScheduled::class);

        $job = new FakeEncryptedJob;

        $dateTime = new DateTime('now');

        // act

        $scheduledJob = self::$fasti->schedule($job, $dateTime);

        // assert

        $this->assertInstanceOf(ScheduledJob::class, $scheduledJob);

        $first = self::$fasti->getRepository()->all()->first();

        $this->assertEquals(serialize($job), decrypt($first->payload), 'The job should be encrypted.');

        Event::assertDispatched(JobScheduled::class);
    }

    public function test_can_schedule_mapped_type_job(): void
    {
        // arrange

        Event::fake(JobScheduled::class);

        $job = new FakeJob;

        $dateTime = new DateTime('now');

        FastiEloquentRepository::enforceTypeMap([
            'fake_job' => FakeJob::class,
        ]);

        // act

        $scheduledJob = self::$fasti->schedule($job, $dateTime);

        // assert

        $this->assertInstanceOf(ScheduledJob::class, $scheduledJob);

        $this->assertDatabaseHas('scheduled_jobs', [
            'payload' => serialize($job),
            'type' => 'fake_job',
            'scheduled_at' => $dateTime->format('Y-m-d H:i:s'),
        ]);

        Event::assertDispatched(JobScheduled::class);
    }

    public function test_can_get_scheduled_jobs(): void
    {
        // arrange

        $job = new FakeJob;
        $job2 = new FakeJob;

        $dateTime = new DateTime('now');

        $scheduledJob = self::$fasti->schedule($job, $dateTime);
        $scheduledJob2 = self::$fasti->schedule($job2, $dateTime);

        self::$fasti->cancel($scheduledJob2->id);

        // act

        $jobs = self::$fasti->scheduled($dateTime);

        // assert

        $this->assertCount(1, $jobs, 'Both jobs should be returned.');
        $this->assertEquals($scheduledJob->id, $jobs->first()->id, 'The scheduled job should be the one we scheduled.');
    }

    public function test_can_cancel_scheduled_job(): void
    {
        // arrange

        Event::fake(JobCancelled::class);

        $job = new FakeJob;

        $dateTime = new DateTime('now');

        $scheduledJob = self::$fasti->schedule($job, $dateTime);

        // act

        self::$fasti->cancel($scheduledJob->id);

        // assert

        $this->assertDatabaseHas('scheduled_jobs', [
            'id' => $scheduledJob->id,
            'cancelled_at' => now()->format('Y-m-d H:i:s'),
        ]);

        Event::assertDispatched(JobCancelled::class);
    }

    public function test_can_get_cancelled_jobs(): void
    {
        // arrange

        $job = new FakeJob;
        $job2 = new FakeJob;

        $dateTime = new DateTime('now');

        self::$fasti->schedule($job, $dateTime);

        $scheduledJob = self::$fasti->schedule($job2, $dateTime);
        self::$fasti->cancel($scheduledJob->id);

        // act

        $jobs = self::$fasti->cancelled();

        // assert

        $this->assertCount(1, $jobs, 'Only the canceled job should be returned.');
        $this->assertEquals($scheduledJob->id, $jobs->first()->id, 'The canceled job should be the one we canceled.');
    }

    public function test_can_get_all_jobs(): void
    {
        // arrange

        $job = new FakeJob;
        $job2 = new FakeJob;

        $dateTime = new DateTime('now');

        self::$fasti->schedule($job, $dateTime);

        $scheduledJob = self::$fasti->schedule($job2, $dateTime);
        self::$fasti->cancel($scheduledJob->id);

        // act

        $jobs = self::$fasti->all();

        // assert

        $this->assertCount(2, $jobs, 'Both jobs should be returned.');
    }

    public function test_can_dispatch_sync_job(): void
    {
        // arrange

        Bus::fake(FakeJob::class);
        Event::fake(JobDispatched::class);

        $job = new FakeJob;

        $dateTime = new DateTime('now');

        $scheduledJob = self::$fasti->schedule($job, $dateTime);

        // act

        self::$fasti->dispatch($scheduledJob->id);

        // assert

        Bus::assertDispatchedSync(FakeJob::class);
        Event::fake(JobDispatched::class);
    }

    public function test_can_dispatch_queued_job(): void
    {
        // arrange

        Bus::fake(FakeQueuedJob::class);
        Event::fake(JobDispatched::class);

        $job = new FakeQueuedJob;

        $dateTime = new DateTime('now');

        $scheduledJob = self::$fasti->schedule($job, $dateTime);

        // act

        self::$fasti->dispatch($scheduledJob->id);

        // assert

        Bus::assertDispatched(FakeQueuedJob::class);
        Event::assertDispatched(JobDispatched::class);
    }

    public function test_can_dispatch_encrypted_job(): void
    {
        // arrange

        Bus::fake(FakeEncryptedJob::class);
        Event::fake(JobDispatched::class);

        $job = new FakeEncryptedJob;

        $dateTime = new DateTime('now');

        $scheduledJob = self::$fasti->schedule($job, $dateTime);

        // act

        self::$fasti->dispatch($scheduledJob->id);

        // assert

        Bus::assertDispatchedSync(FakeEncryptedJob::class);
        Event::assertDispatched(JobDispatched::class);
    }
}
