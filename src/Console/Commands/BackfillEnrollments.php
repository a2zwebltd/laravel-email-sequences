<?php

declare(strict_types=1);

namespace A2ZWeb\EmailSequences\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Enrols existing users that have no deliveries yet — useful right after
 * installing the package or adding a new sequence.
 */
class BackfillEnrollments extends Command
{
    protected $signature = 'emails:backfill-enrollments';

    protected $description = 'Enrol existing users that are not yet enrolled in any email sequence.';

    public function handle(): int
    {
        /** @var class-string<Model> $model */
        $model = config('email-sequences.user_model');

        $count = 0;

        $model::query()
            ->doesntHave('emailSequenceDeliveries')
            ->chunkById(500, function (Collection $users) use (&$count): void {
                foreach ($users as $user) {
                    $user->enrollInEmailSequences();
                    $count++;
                }
            });

        $this->info("Enrolled {$count} user(s) into email sequences.");

        return self::SUCCESS;
    }
}
