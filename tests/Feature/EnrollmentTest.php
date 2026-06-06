<?php

use A2ZWeb\EmailSequences\Models\EmailSequence;
use A2ZWeb\EmailSequences\Models\EmailSequenceDelivery;
use A2ZWeb\EmailSequences\Tests\User;

it('seeds the configured sequences', function () {
    expect(EmailSequence::count())->toBe(4)
        ->and(EmailSequence::where('sequence_code', 'mid_trial')->value('days_delay'))->toBe(14);
});

it('auto-enrols a new user into every sequence', function () {
    $user = User::factory()->create();

    expect($user->emailSequenceDeliveries()->count())->toBe(4);

    $midTrial = EmailSequence::where('sequence_code', 'mid_trial')->first();
    $delivery = EmailSequenceDelivery::where('user_id', $user->id)
        ->where('email_sequence_id', $midTrial->id)
        ->first();

    expect($delivery->sent_at)->toBeNull()
        ->and($delivery->scheduled_at->toDateString())
        ->toBe($user->created_at->copy()->addDays(14)->toDateString());
});

it('does not enrol when should_enroll returns false', function () {
    config()->set('email-sequences.should_enroll', fn () => false);

    $user = User::factory()->create();

    expect($user->emailSequenceDeliveries()->count())->toBe(0);
});
