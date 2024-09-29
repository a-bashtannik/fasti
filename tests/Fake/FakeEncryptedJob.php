<?php

declare(strict_types=1);

namespace Bashtannik\Fasti\Tests\Fake;

use Illuminate\Contracts\Queue\ShouldBeEncrypted;

final class FakeEncryptedJob implements ShouldBeEncrypted
{
    public function handle(): void
    {
        // do nothing
    }
}
