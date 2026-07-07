<?php

use A2ZWeb\EmailSequences\Events\SequenceDelivered;
use A2ZWeb\EmailSequences\Mail\SequenceEmail;
use A2ZWeb\EmailSequences\Models\EmailSequenceDelivery;
use A2ZWeb\EmailSequences\Tests\User;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;

it('delivers due sequences, fires the event and marks them sent', function () {
    Mail::fake();
    Event::fake([SequenceDelivered::class]);

    $user = User::factory()->create();
    $delivery = $user->emailSequenceDeliveries()->first();
    $delivery->update(['scheduled_at' => now()->subDay()]);

    $this->artisan('emails:deliver-sequences')->assertSuccessful();

    Mail::assertQueued(SequenceEmail::class);
    Event::assertDispatched(SequenceDelivered::class);
    expect($delivery->fresh()->sent_at)->not->toBeNull();
});

it('does not deliver sequences scheduled in the future', function () {
    Mail::fake();

    User::factory()->create(); // all deliveries scheduled 14+ days out

    $this->artisan('emails:deliver-sequences')->assertSuccessful();

    Mail::assertNothingQueued();
});

it('stops a user’s sequence when should_continue returns false', function () {
    Mail::fake();
    config()->set('email-sequences.should_continue', fn () => false);

    $user = User::factory()->create();
    $user->emailSequenceDeliveries()->update(['scheduled_at' => now()->subDay()]);

    $this->artisan('emails:deliver-sequences')->assertSuccessful();

    Mail::assertNothingQueued();
    expect(EmailSequenceDelivery::where('user_id', $user->id)->count())->toBe(0);
});

it('skips a delivery when its should_send gate returns false without touching other deliveries', function () {
    Mail::fake();
    config()->set('email-sequences.sequences.mid_trial.should_send', fn () => false);

    $user = User::factory()->create();
    $user->emailSequenceDeliveries()->update(['scheduled_at' => now()->subDay()]);
    $skipped = $user->emailSequenceDeliveries()
        ->whereHas('emailSequence', fn ($q) => $q->where('sequence_code', 'mid_trial'))
        ->first();

    $this->artisan('emails:deliver-sequences')->assertSuccessful();

    // mid_trial marked sent without a mail; the other sequences still go out
    expect($skipped->fresh()->sent_at)->not->toBeNull();
    Mail::assertQueued(SequenceEmail::class, fn (SequenceEmail $mail) => $mail->sequenceCode !== 'mid_trial');
    Mail::assertNotQueued(SequenceEmail::class, fn (SequenceEmail $mail) => $mail->sequenceCode === 'mid_trial');
    expect(EmailSequenceDelivery::where('user_id', $user->id)->count())->toBeGreaterThan(1);
});

it('configures sequence emails to auto-retry on failure', function () {
    $user = User::factory()->make();
    $mail = new SequenceEmail($user, 'mid_trial');

    expect($mail->tries)->toBe(3)
        ->and($mail->backoff())->toBe([60, 300, 900]);
});
