@component('mail::message')
# {{ __('Hi') }} {{ $name }},

{{ __('Your free trial has ended. Subscribe any time to pick up right where you left off.') }}

{{ __('Thanks,') }}<br>
{{ config('app.name') }}
@endcomponent
