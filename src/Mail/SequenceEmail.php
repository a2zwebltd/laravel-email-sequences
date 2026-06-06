<?php

declare(strict_types=1);

namespace A2ZWeb\EmailSequences\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

/**
 * Renders a single email in a sequence. Subject and view are resolved from
 * `config('email-sequences.sequences.{code}')`.
 */
class SequenceEmail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    /**
     * Retry a failed send a few times with growing backoff before giving up.
     */
    public int $tries = 3;

    public function __construct(
        public Model $user,
        public string $sequenceCode,
    ) {}

    /**
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return [60, 300, 900];
    }

    public function envelope(): Envelope
    {
        $subject = (string) ($this->config('subject') ?? 'A message for you');
        $subject = str_replace(':name', $this->recipientName(), $subject);

        return new Envelope(
            to: $this->recipientEmail(),
            subject: $subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: (string) ($this->config('view') ?? 'email-sequences::emails.generic'),
            with: [
                'user' => $this->user,
                'name' => $this->recipientName(),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }

    protected function config(string $key): mixed
    {
        return config('email-sequences.sequences.'.$this->sequenceCode.'.'.$key);
    }

    protected function recipientEmail(): string
    {
        if (method_exists($this->user, 'getMailingEmail')) {
            return $this->user->getMailingEmail();
        }

        return (string) $this->user->email;
    }

    protected function recipientName(): string
    {
        if (method_exists($this->user, 'getPersonalizedName')) {
            return $this->user->getPersonalizedName();
        }

        $name = trim((string) ($this->user->name ?? ''));
        if ($name !== '') {
            return explode(' ', $name)[0];
        }

        return Str::before((string) $this->user->email, '@');
    }
}
