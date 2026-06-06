@component('mail::message')
# {{ __('Hi') }} {{ $name }},

{{ __('You’re halfway through your free trial. We hope you’re getting value out of it!') }}

{{ __('If there’s anything we can help with, just reply to this email.') }}

{{ __('Thanks,') }}<br>
{{ config('app.name') }}
@endcomponent
