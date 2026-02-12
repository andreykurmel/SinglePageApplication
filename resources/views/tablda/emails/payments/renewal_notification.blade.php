@extends('tablda.emails.layout')

@section('email_content')
    @if($has_expired)
        <p style="{{ $styles['paragraph'] }}">
            Your saved payment method (credit card ****{{ $has_expired }}) is about to expire.
            Please update your payment settings to ensure your account remains active and avoid any service interruptions.
        </p>
    @elseif($has_recurrent)
        <p style="{{ $styles['paragraph'] }}">
            Your account has a credit balance of ${{ $avail_credit }}.
            This is not sufficient for your next renewal for your current subscription.
            Please update your account accordingly ASAP to avoid service interruption.
        </p>
    @else
        <p style="{{ $styles['paragraph'] }}">
            Your subscription does not have auto-renewal turned ON.
            Your subscription and the associated service will be paused by {{ $end_date }}.
            Please update your account accordingly ASAP to avoid service interruption.
        </p>
    @endif
@endsection