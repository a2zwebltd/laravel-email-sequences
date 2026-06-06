@component('mail::message')
# {{ __('Hi') }} {{ $name }},

{{ __('Your free trial is ending soon. We’d love for you to stick around.') }}

{{ __('Upgrade now to keep uninterrupted access.') }}

{{ __('Thanks,') }}<br>
{{ config('app.name') }}
@endcomponent
