@component('mail::message')
# {{ __('Hi') }} {{ $name }},

{{ __('Thanks for being with us.') }}

{{ config('app.name') }}
@endcomponent
