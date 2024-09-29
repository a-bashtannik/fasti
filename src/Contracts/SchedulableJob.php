<?php

declare(strict_types=1);

namespace Bashtannik\Fasti\Contracts;

use Carbon\CarbonInterface;

/**
 * A job that can be scheduled and canceled.
 *
 * @property int|string $id
 * @property string $payload
 * @property CarbonInterface $scheduled_at
 * @property CarbonInterface|null $cancelled_at
 */
interface SchedulableJob {}
