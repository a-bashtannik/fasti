<?php

namespace Bashtannik\Fasti\Models;

use Bashtannik\Fasti\Contracts\SchedulableJob;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $type
 * @property string $payload
 * @property CarbonImmutable|null $cancelled_at
 * @property CarbonImmutable|null $dispatched_at
 * @property CarbonImmutable $scheduled_at
 */
class ScheduledJob extends Model implements SchedulableJob
{
    public $timestamps = false;

    protected $guarded = [];

    protected $hidden = ['payload'];

    /**
     * @return array<string,string>
     */
    protected function casts(): array
    {
        return [
            'cancelled_at' => 'immutable_datetime',
            'scheduled_at' => 'immutable_datetime',
            'dispatched_at' => 'immutable_datetime',
        ];
    }
}
