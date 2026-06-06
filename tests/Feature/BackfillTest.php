<?php

use A2ZWeb\EmailSequences\Tests\User;

beforeEach(function () {
    // Disable auto-enrolment so users start without deliveries.
    config()->set('email-sequences.auto_enroll', false);
});

it('enrols existing users that have no deliveries', function () {
    $user = User::factory()->create();
    expect($user->emailSequenceDeliveries()->count())->toBe(0);

    $this->artisan('emails:backfill-enrollments')->assertSuccessful();

    expect($user->fresh()->emailSequenceDeliveries()->count())->toBe(4);
});
