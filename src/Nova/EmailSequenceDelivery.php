<?php

declare(strict_types=1);

namespace A2ZWeb\EmailSequences\Nova;

use A2ZWeb\EmailSequences\Models\EmailSequenceDelivery as EmailSequenceDeliveryModel;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Resource;

class EmailSequenceDelivery extends Resource
{
    public static string $model = EmailSequenceDeliveryModel::class;

    public static $title = 'id';

    public static $search = [
        'id',
        'user_id',
        'email_sequence_id',
    ];

    public static function group(): string
    {
        return (string) (config('email-sequences.nova.group') ?? 'Mailings');
    }

    public function fields(NovaRequest $request): array
    {
        return array_values(array_filter([
            ID::make()->sortable(),

            $this->userField(),

            BelongsTo::make('Email Sequence', 'emailSequence', EmailSequence::class)
                ->searchable()
                ->sortable(),

            DateTime::make('Scheduled At')
                ->rules('nullable', 'date')
                ->sortable(),

            DateTime::make('Sent At')
                ->rules('nullable', 'date')
                ->sortable(),

            DateTime::make('Created At')->onlyOnDetail(),
            DateTime::make('Updated At')->onlyOnDetail(),
        ]));
    }

    protected function userField(): ?Field
    {
        /** @var class-string|null $userResource */
        $userResource = config('email-sequences.nova.user_resource');

        if (! $userResource || ! class_exists($userResource)) {
            return null;
        }

        return BelongsTo::make('User', 'user', $userResource)
            ->searchable()
            ->sortable();
    }
}
