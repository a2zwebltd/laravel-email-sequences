<?php

declare(strict_types=1);

namespace A2ZWeb\EmailSequences\Database\Factories;

use A2ZWeb\EmailSequences\Models\EmailSequence;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EmailSequence>
 */
class EmailSequenceFactory extends Factory
{
    protected $model = EmailSequence::class;

    public function definition(): array
    {
        return [
            'sequence_code' => $this->faker->unique()->slug(2),
            'name' => $this->faker->sentence(2),
            'days_delay' => $this->faker->numberBetween(0, 60),
        ];
    }
}
