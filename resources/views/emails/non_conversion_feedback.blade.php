@component('mail::message')
# {{ __('Hi') }} {{ $name }},

{{ __('We noticed your trial ended without upgrading, and that’s okay!') }}

{{ __('Would you mind sharing what didn’t work for you? Just reply to this email — your feedback helps us improve.') }}

{{ __('Thanks,') }}<br>
{{ config('app.name') }}
@endcomponent
