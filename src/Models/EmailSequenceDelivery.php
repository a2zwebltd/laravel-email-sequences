<?php

declare(strict_types=1);

namespace A2ZWeb\EmailSequences\Models;

use A2ZWeb\EmailSequences\Database\Factories\EmailSequenceDeliveryFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property int $email_sequence_id
 * @property Carbon|null $scheduled_at
 * @property Carbon|null $sent_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read EmailSequence $emailSequence
 */
class EmailSequenceDelivery extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'email_sequence_id',
        'scheduled_at',
        'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'sent_at' => 'datetime',
        ];
    }

    protected static function newFactory(): Factory
    {
        return EmailSequenceDeliveryFactory::new();
    }

    public function user(): BelongsTo
    {
        /** @var class-string<Model> $userModel */
        $userModel = config('email-sequences.user_model');

        return $this->belongsTo($userModel);
    }

    public function emailSequence(): BelongsTo
    {
        return $this->belongsTo(EmailSequence::class);
    }
}
