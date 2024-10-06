<?php

declare(strict_types=1);

namespace Bashtannik\Fasti\Services;

use Bashtannik\Fasti\Contracts\JobDispatcher;
use Illuminate\Support\Facades\Bus;

class BusJobDispatcher implements JobDispatcher
{
    public function dispatch(mixed $job): mixed
    {
        return Bus::dispatch($job);
    }

    public function dispatchSync(mixed $job): mixed
    {
        return Bus::dispatchSync($job);
    }
}
