<?php

declare(strict_types=1);

namespace Bashtannik\Fasti\Tests\Fake;

final class FakeJob
{
    public function __construct(public int $id = 0) {}

    public function handle(): void
    {
        // do nothing
    }
}
