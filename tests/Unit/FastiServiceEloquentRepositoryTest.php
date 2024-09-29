<?php

namespace Bashtannik\Fasti\Tests\Unit;

use Bashtannik\Fasti\Repositories\FastiEloquentRepository;
use Bashtannik\Fasti\Services\FastiService;
use Bashtannik\Fasti\Tests\Fake\FakeEncryptedJob;
use Bashtannik\Fasti\Tests\Fake\FakeJob;
use Bashtannik\Fasti\Tests\Fake\FakeQueuedJob;
use Bashtannik\Fasti\Tests\TestCase;
use DateTime;
use DateTimeImmutable;
use Illuminate\Support\Facades\Bus;

class FastiServiceEloquentRepositoryTest extends TestCase
{
    public static FastiService $fasti;

    protected function setUp(): void
    {
        parent::setUp();

        self::$fasti = new FastiService(new FastiEloquentRepository);
    }

    public function test_can_schedule_job_date_time(): void
    {
        // arrange

        $job = new FakeJob;

        $dateTime = new DateTime('now');

        // act

        self::$fasti->schedule($job, $dateTime);

        // assert

        $this->assertDatabaseHas('scheduled_jobs', [
            'payload' => serialize($job),
            'scheduled_at' => $dateTime->format('Y-m-d H:i:s'),
        ]);
    }

    public function test_can_schedule_job_date_time_immutable(): void
    {
        // arrange

        $job = new FakeJob;

        $dateTime = new DateTimeImmutable('now');

        // act

        self::$fasti->schedule($job, $dateTime);

        // assert

        $this->assertDatabaseHas('scheduled_jobs', [
            'payload' => serialize($job),
            'scheduled_at' => $dateTime->format('Y-m-d H:i:s'),
        ]);
    }

    public function test_can_schedule_job_carbon(): void
    {
        // arrange

        $job = new FakeJob;

        $dateTime = now();

        // act

        self::$fasti->schedule($job, $dateTime);

        // assert

        $this->assertDatabaseHas('scheduled_jobs', [
            'payload' => serialize($job),
            'scheduled_at' => $dateTime->format('Y-m-d H:i:s'),
        ]);
    }

    public function test_can_schedule_job_carbon_immutable(): void
    {
        // arrange

        $job = new FakeJob;

        $dateTime = now();

        // act

        self::$fasti->schedule($job, $dateTime);

        // assert

        $this->assertDatabaseHas('scheduled_jobs', [
            'payload' => serialize($job),
            'scheduled_at' => $dateTime->format('Y-m-d H:i:s'),
        ]);
    }

    public function test_can_schedule_encrypted_job(): void
    {
        // arrange

        $job = new FakeEncryptedJob;

        $dateTime = new DateTime('now');

        // act

        self::$fasti->schedule($job, $dateTime);

        // assert

        $first = self::$fasti->getRepository()->all()->first();

        $this->assertEquals(serialize($job), decrypt($first->payload), 'The job should be encrypted.');
    }

    public function test_can_get_scheduled_jobs(): void
    {
        // arrange

        $job = new FakeJob;
        $scheduledJob = new FakeJob;

        $dateTime = new DateTime('now');

        $id = self::$fasti->schedule($job, $dateTime);

        $cancelledId = self::$fasti->schedule($scheduledJob, $dateTime);
        self::$fasti->cancel($cancelledId);

        // act

        $jobs = self::$fasti->scheduled($dateTime);

        // assert

        $this->assertCount(1, $jobs, 'Both jobs should be returned.');
        $this->assertEquals($id, $jobs->first()->id, 'The scheduled job should be the one we scheduled.');
    }

    public function test_can_cancel_scheduled_job(): void
    {
        // arrange

        $job = new FakeJob;

        $dateTime = new DateTime('now');

        $id = self::$fasti->schedule($job, $dateTime);

        // act

        self::$fasti->cancel($id);

        // assert

        $this->assertDatabaseHas('scheduled_jobs', [
            'id' => $id,
            'cancelled_at' => now()->format('Y-m-d H:i:s'),
        ]);
    }

    public function test_can_get_cancelled_jobs(): void
    {
        // arrange

        $job = new FakeJob;
        $canceledJob = new FakeJob;

        $dateTime = new DateTime('now');

        self::$fasti->schedule($job, $dateTime);

        $id = self::$fasti->schedule($canceledJob, $dateTime);
        self::$fasti->cancel($id);

        // act

        $jobs = self::$fasti->cancelled();

        // assert

        $this->assertCount(1, $jobs, 'Only the canceled job should be returned.');
        $this->assertEquals($id, $jobs->first()->id, 'The canceled job should be the one we canceled.');
    }

    public function test_can_get_all_jobs(): void
    {
        // arrange

        $job = new FakeJob;
        $canceledJob = new FakeJob;

        $dateTime = new DateTime('now');

        self::$fasti->schedule($job, $dateTime);

        $id = self::$fasti->schedule($canceledJob, $dateTime);
        self::$fasti->cancel($id);

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

        $id = self::$fasti->schedule($job, $dateTime);

        // act

        self::$fasti->dispatch($id);

        // assert

        Bus::assertDispatchedSync(FakeJob::class);
    }

    public function test_can_dispatch_queued_job(): void
    {
        // arrange

        Bus::fake(FakeQueuedJob::class);

        $job = new FakeQueuedJob;

        $dateTime = new DateTime('now');

        $id = self::$fasti->schedule($job, $dateTime);

        // act

        self::$fasti->dispatch($id);

        // assert

        Bus::assertDispatched(FakeQueuedJob::class);
    }

    public function test_can_dispatch_encrypted_job(): void
    {
        // arrange

        Bus::fake(FakeEncryptedJob::class);

        $job = new FakeEncryptedJob;

        $dateTime = new DateTime('now');

        $id = self::$fasti->schedule($job, $dateTime);

        // act

        self::$fasti->dispatch($id);

        // assert

        Bus::assertDispatchedSync(FakeEncryptedJob::class);
    }
}
