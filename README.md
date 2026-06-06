# Laravel Email Sequences

A portable Laravel drip / lifecycle email engine. Enrol users into time-delayed
email sequences (onboarding, trial nurture, win-back, feedback), deliver them on
a cron, and stop a user's sequence the moment they convert — all driven by config
and pluggable eligibility rules, with ready-made Nova admin resources.

Designed to drop into any Laravel app while leaving the host in control of its
user model, eligibility rules, branding and notifications.

## Requirements

- PHP 8.2+
- Laravel 11 / 12 / 13
- `laravel/nova` ^5 (optional — auto-registers Nova resources when present)

## Installation

```bash
composer require a2zwebltd/laravel-email-sequences
php artisan migrate
php artisan vendor:publish --tag=email-sequences-config   # optional
php artisan vendor:publish --tag=email-sequences-views     # optional, to rebrand emails
```

Add the trait to your `User` model:

```php
use A2ZWeb\EmailSequences\Concerns\HasEmailSequences;

class User extends Authenticatable
{
    use HasEmailSequences;
}
```

That's it — with `auto_enroll` on (the default), every newly registered user is
enrolled into all sequences and delivery runs hourly.

## How it works

1. **Configure** your sequences in `config/email-sequences.php` (code, delay,
   subject, view). They seed the `email_sequences` table on migrate.
2. **Enrolment** — new users are auto-enrolled (or call
   `$user->enrollInEmailSequences()` yourself). Each sequence is scheduled
   relative to the user's registration date.
3. **Delivery** — the `emails:deliver-sequences` command (scheduled hourly) sends
   every due message, fires `SequenceDelivered`, and marks it sent.
4. **Eligibility** — `should_enroll` gates who gets enrolled; `should_continue`
   stops a user's remaining deliveries (e.g. once they upgrade to a paid plan).

Use `emails:backfill-enrollments` to enrol users that pre-date installation or a
newly added sequence.

## Configuration

See `config/email-sequences.php`:

| Key | Purpose |
|-----|---------|
| `user_model` | The model enrolled into sequences |
| `sequences` | The sequence set: `code => [name, days_delay, subject, view]` |
| `auto_enroll` | Enrol every newly-created user automatically |
| `should_enroll` | `fn ($user) => bool` — gate enrolment |
| `should_continue` | `fn ($user) => bool` — stop a user's sequence when false |
| `schedule.deliver` / `schedule.cron` | Self-scheduling of the delivery command |
| `nova.group` / `nova.user_resource` | Nova menu group and User resource for relations |

## Events

| Event | When |
|-------|------|
| `SequenceDelivered` | A sequence email was delivered (in-app notifications, analytics) |

## Commands

| Command | Purpose |
|---------|---------|
| `emails:deliver-sequences` | Deliver all due sequence emails (self-scheduled hourly) |
| `emails:backfill-enrollments` | Enrol users that have no deliveries yet |

## Testing

```bash
composer test
```

## License

MIT — see LICENSE file.
