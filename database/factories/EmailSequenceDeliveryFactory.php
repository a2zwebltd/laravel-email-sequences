<?php

declare(strict_types=1);

namespace A2ZWeb\EmailSequences\Database\Factories;

use A2ZWeb\EmailSequences\Models\EmailSequence;
use A2ZWeb\EmailSequences\Models\EmailSequenceDelivery;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EmailSequenceDelivery>
 */
class EmailSequenceDeliveryFactory extends Factory
{
    protected $model = EmailSequenceDelivery::class;

    public function definition(): array
    {
        return [
            'email_sequence_id' => EmailSequence::factory(),
            'scheduled_at' => now()->subDay(),
            'sent_at' => null,
        ];
    }

    public function sent(): static
    {
        return $this->state(['sent_at' => now()]);
    }
}
