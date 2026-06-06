<?php

declare(strict_types=1);

namespace A2ZWeb\EmailSequences\Console\Commands;

use A2ZWeb\EmailSequences\Events\SequenceDelivered;
use A2ZWeb\EmailSequences\Mail\SequenceEmail;
use A2ZWeb\EmailSequences\Models\EmailSequenceDelivery;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class DeliverEmailSequences extends Command
{
    protected $signature = 'emails:deliver-sequences';

    protected $description = 'Deliver any email-sequence messages that are due.';

    public function handle(): int
    {
        $this->line('<info>Starting email sequence delivery process...</info>');

        $pending = EmailSequenceDelivery::with(['user', 'emailSequence'])
            ->whereNull('sent_at')
            ->where('scheduled_at', '<=', now())
            ->get();

        if ($pending->isEmpty()) {
            $this->line('<comment>No pending email sequences found for delivery.</comment>');

            return self::SUCCESS;
        }

        $shouldContinue = config('email-sequences.should_continue');
        if (is_string($shouldContinue) && class_exists($shouldContinue)) {
            $shouldContinue = app($shouldContinue);
        }

        foreach ($pending as $delivery) {
            $user = $delivery->user;

            if (! $user instanceof Model) {
                continue;
            }

            if (is_callable($shouldContinue) && ! $shouldContinue($user)) {
                $this->line("Stopping sequence for {$user->email}; removing pending deliveries.");
                $user->emailSequenceDeliveries()->delete();

                continue;
            }

            $code = $delivery->emailSequence->sequence_code;

            $this->line("Delivering '{$code}' to {$user->email}");

            Mail::send(new SequenceEmail($user, $code));

            $delivery->update(['sent_at' => now()]);

            event(new SequenceDelivered($user, $code, $delivery));

            Log::debug("EmailSequenceDelivery #{$delivery->id} ('{$code}') sent to user #{$user->getKey()}");
        }

        $this->line('<info>Email sequence delivery process completed.</info>');

        return self::SUCCESS;
    }
}
