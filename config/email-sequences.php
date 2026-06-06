<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | User model
    |--------------------------------------------------------------------------
    |
    | The model enrolled into email sequences. Apply the
    | A2ZWeb\EmailSequences\Concerns\HasEmailSequences trait to it.
    |
    */

    'user_model' => env('EMAIL_SEQUENCES_USER_MODEL', config('auth.providers.users.model', 'App\\Models\\User')),

    /*
    |--------------------------------------------------------------------------
    | Sequences
    |--------------------------------------------------------------------------
    |
    | The set of sequences. Each is keyed by a unique `code` and defines:
    |  - name:       human-readable label (stored on the email_sequences row)
    |  - days_delay: days after enrolment the email is sent
    |  - subject:    mail subject (":name" is replaced with the recipient name)
    |  - view:       blade view rendered as the email body
    |
    | These seed the `email_sequences` table on migrate and drive the default
    | SequenceEmail mailable. Publish the views to rebrand them.
    |
    */

    'sequences' => [
        'mid_trial' => [
            'name' => 'Mid Trial',
            'days_delay' => 14,
            'subject' => ':name, you’re halfway through your free trial',
            'view' => 'email-sequences::emails.mid_trial',
        ],
        'trial_reminder' => [
            'name' => 'Trial Reminder',
            'days_delay' => 21,
            'subject' => ':name, your free trial is ending soon',
            'view' => 'email-sequences::emails.trial_reminder',
        ],
        'post_trial' => [
            'name' => 'Post Trial',
            'days_delay' => 31,
            'subject' => ':name, your free trial has ended',
            'view' => 'email-sequences::emails.post_trial',
        ],
        'non_conversion_feedback' => [
            'name' => 'Non-Conversion Feedback',
            'days_delay' => 45,
            'subject' => 'We’d love your feedback, :name',
            'view' => 'email-sequences::emails.non_conversion_feedback',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Enrolment
    |--------------------------------------------------------------------------
    |
    | auto_enroll: when true, every newly-created user is enrolled into all
    |   sequences automatically (via a model `created` listener).
    |
    | should_enroll: fn ($user) => bool — gate enrolment (e.g. skip lifetime/
    |   comped accounts). Default: always enrol.
    |
    */

    'auto_enroll' => env('EMAIL_SEQUENCES_AUTO_ENROLL', true),

    'should_enroll' => null,

    /*
    |--------------------------------------------------------------------------
    | Delivery
    |--------------------------------------------------------------------------
    |
    | should_continue: fn ($user) => bool — evaluated for each pending delivery.
    |   Return false to stop a user's sequence (their pending deliveries are
    |   removed and skipped), e.g. once they convert to a paid plan. Default:
    |   always continue.
    |
    */

    'should_continue' => null,

    /*
    |--------------------------------------------------------------------------
    | Schedule
    |--------------------------------------------------------------------------
    */

    'schedule' => [
        'deliver' => env('EMAIL_SEQUENCES_SCHEDULE', true),
        'cron' => env('EMAIL_SEQUENCES_CRON', 'hourly'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Nova
    |--------------------------------------------------------------------------
    */

    'nova' => [
        'register_resources' => true,
        'group' => 'Mailings',
        'user_resource' => 'App\\Nova\\User',
    ],

];
