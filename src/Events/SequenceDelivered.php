<?php

declare(strict_types=1);

namespace A2ZWeb\EmailSequences\Events;

use A2ZWeb\EmailSequences\Models\EmailSequenceDelivery;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired after a sequence email is delivered to a user. Listen to mirror the
 * message into an in-app notification centre, analytics, etc.
 */
class SequenceDelivered
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly Model $user,
        public readonly string $sequenceCode,
        public readonly EmailSequenceDelivery $delivery,
    ) {}
}
