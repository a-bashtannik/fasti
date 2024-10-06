<?php

declare(strict_types=1);

namespace Bashtannik\Fasti\Events;

use Bashtannik\Fasti\Contracts\SchedulableJob;

class JobCancelled
{
    public function __construct(public SchedulableJob $job) {}
}
