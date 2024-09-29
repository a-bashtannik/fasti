<?php

declare(strict_types=1);

namespace Bashtannik\Fasti\Repositories;

use Bashtannik\Fasti\Contracts\SchedulableJob;
use Carbon\CarbonInterface;

class GenericScheduledJob implements SchedulableJob
{
    public function __construct(
        public int|string $id,
        public string $payload,
        public CarbonInterface $scheduled_at,
        public ?CarbonInterface $cancelled_at,
    ) {}
}
