<?php

declare(strict_types=1);

namespace A2ZWeb\EmailSequences\Models;

use A2ZWeb\EmailSequences\Database\Factories\EmailSequenceFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $sequence_code
 * @property string $name
 * @property int $days_delay
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
class EmailSequence extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'sequence_code',
        'name',
        'days_delay',
    ];

    protected function casts(): array
    {
        return [
            'days_delay' => 'integer',
        ];
    }

    protected static function newFactory(): Factory
    {
        return EmailSequenceFactory::new();
    }

    /**
     * @return HasMany<EmailSequenceDelivery>
     */
    public function emailSequenceDeliveries(): HasMany
    {
        return $this->hasMany(EmailSequenceDelivery::class);
    }
}
