<?php

use A2ZWeb\EmailSequences\Models\EmailSequence;
use A2ZWeb\EmailSequences\Models\EmailSequenceDelivery;
use A2ZWeb\EmailSequences\Tests\User;
use Illuminate\Support\Facades\Mail;

it('enrols against the oldest row only when a sequence_code is duplicated', function () {
    $canonical = EmailSequence::where('sequence_code', 'mid_trial')->orderBy('id')->first();
    $duplicate = EmailSequence::create([
        'sequence_code' => 'mid_trial',
        'name' => 'Mid Trial (dupe)',
        'days_delay' => 14,
    ]);

    $user = User::factory()->create();

    $midTrialDeliveries = $user->emailSequenceDeliveries()
        ->whereIn('email_sequence_id', [$canonical->id, $duplicate->id])
        ->get();

    expect($midTrialDeliveries)->toHaveCount(1)
        ->and($midTrialDeliveries->first()->email_sequence_id)->toBe($canonical->id);
});

it('excludes soft-deleted sequences from enrollment', function () {
    EmailSequence::where('sequence_code', 'mid_trial')->first()->delete();

    $user = User::factory()->create();

    expect($user->emailSequenceDeliveries()->count())->toBe(3)
        ->and(
            $user->emailSequenceDeliveries()
                ->whereIn('email_sequence_id', EmailSequence::withTrashed()->where('sequence_code', 'mid_trial')->pluck('id'))
                ->exists()
        )->toBeFalse();
});

it('skips deliveries pointing at a trashed sequence without crashing', function () {
    Mail::fake();

    $user = User::factory()->create();
    $sequence = EmailSequence::where('sequence_code', 'mid_trial')->first();

    $delivery = EmailSequenceDelivery::where('user_id', $user->id)
        ->where('email_sequence_id', $sequence->id)
        ->first();
    $delivery->update(['scheduled_at' => now()->subMinute()]);

    $sequence->delete();

    $this->artisan('emails:deliver-sequences')->assertSuccessful();

    expect($delivery->fresh()->sent_at)->toBeNull();
    Mail::assertNothingSent();
});

it('keeps sent history when should_continue stops a sequence', function () {
    Mail::fake();

    $user = User::factory()->create();

    $sent = $user->emailSequenceDeliveries()->orderBy('id')->first();
    $sent->update(['sent_at' => now()->subDay()]);

    $due = $user->emailSequenceDeliveries()->whereNull('sent_at')->orderBy('id')->first();
    $due->update(['scheduled_at' => now()->subMinute()]);

    config()->set('email-sequences.should_continue', fn () => false);

    $this->artisan('emails:deliver-sequences')->assertSuccessful();

    expect(EmailSequenceDelivery::withTrashed()->find($sent->id)->trashed())->toBeFalse()
        ->and(EmailSequenceDelivery::withTrashed()->find($due->id)->trashed())->toBeTrue();
    Mail::assertNothingSent();
});
