<?php

declare(strict_types=1);

namespace A2ZWeb\EmailSequences\Concerns;

use A2ZWeb\EmailSequences\Models\EmailSequence;
use A2ZWeb\EmailSequences\Models\EmailSequenceDelivery;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Apply to the host user model to enrol it into email sequences.
 */
trait HasEmailSequences
{
    /**
     * @return HasMany<EmailSequenceDelivery>
     */
    public function emailSequenceDeliveries(): HasMany
    {
        return $this->hasMany(EmailSequenceDelivery::class);
    }

    /**
     * Enrol this user into every configured sequence, scheduling each delivery
     * relative to the user's registration date. Honours the `should_enroll`
     * gate. Idempotent — existing deliveries are not duplicated.
     */
    public function enrollInEmailSequences(): void
    {
        $gate = config('email-sequences.should_enroll');
        if (is_string($gate) && class_exists($gate)) {
            $gate = app($gate);
        }
        if (is_callable($gate) && ! $gate($this)) {
            return;
        }

        $enrolledFrom = $this->created_at ?? now();

        EmailSequence::query()->each(function (EmailSequence $sequence) use ($enrolledFrom): void {
            EmailSequenceDelivery::firstOrCreate(
                [
                    'user_id' => $this->getKey(),
                    'email_sequence_id' => $sequence->id,
                ],
                [
                    'scheduled_at' => $enrolledFrom->copy()->addDays($sequence->days_delay),
                ],
            );
        });
    }
}
