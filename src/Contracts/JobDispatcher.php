<?php

declare(strict_types=1);

namespace Bashtannik\Fasti\Contracts;

interface JobDispatcher
{
    public function dispatch(mixed $job): mixed;

    public function dispatchSync(mixed $job): mixed;
}
