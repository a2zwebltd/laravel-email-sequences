<?php

declare(strict_types=1);

namespace A2ZWeb\EmailSequences\Nova;

use A2ZWeb\EmailSequences\Models\EmailSequence as EmailSequenceModel;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Resource;

class EmailSequence extends Resource
{
    public static string $model = EmailSequenceModel::class;

    public static $title = 'name';

    public static $search = [
        'id',
        'sequence_code',
        'name',
    ];

    public static function group(): string
    {
        return (string) (config('email-sequences.nova.group') ?? 'Mailings');
    }

    public static function authorizedToCreate(Request $request): bool
    {
        return false;
    }

    public function authorizedToUpdate(Request $request): bool
    {
        return false;
    }

    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }

    public function authorizedToReplicate(Request $request): bool
    {
        return false;
    }

    public function fields(NovaRequest $request): array
    {
        return [
            ID::make()->sortable(),

            Text::make('Sequence Code', 'sequence_code')->sortable(),

            Text::make('Name')->sortable(),

            Number::make('Days Delay', 'days_delay')->sortable(),

            HasMany::make('Email Sequence Deliveries', 'emailSequenceDeliveries', EmailSequenceDelivery::class),
        ];
    }
}
