<?php

declare(strict_types=1);

namespace Bashtannik\Fasti\Repositories;

use Bashtannik\Fasti\Contracts\SchedulableJob;
use Carbon\CarbonInterface;

/**
 * @codeCoverageIgnore
 */
class GenericScheduledJob implements SchedulableJob
{
    /**
     * @var array<string, mixed>
     */
    private array $properties = [];

    public function __construct(
        public int|string $id,
        public string $type,
        public string $payload,
        public CarbonInterface $scheduled_at,
        public ?CarbonInterface $dispatched_at,
        public ?CarbonInterface $cancelled_at,
    ) {}

    public function __set(string $name, mixed $value): void
    {
        if (! property_exists($this, $name)) {
            $this->properties[$name] = $value;
        }
    }

    public function __get(string $name): mixed
    {
        if (property_exists($this, $name)) {
            return $this->$name;
        }

        return $this->properties[$name] ?? null;
    }

    public function __call(string $name, mixed $arguments): void
    {
        // Just silently ignores the call
    }
}
