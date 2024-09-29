<?php

declare(strict_types=1);

namespace Bashtannik\Fasti\Tests\Fake;

use Illuminate\Contracts\Queue\ShouldQueue;

final class FakeQueuedJob implements ShouldQueue
{
    public function handle(): void
    {
        // do nothing
    }
}
