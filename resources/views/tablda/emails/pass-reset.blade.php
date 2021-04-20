@extends('tablda.emails.layout')

@section('email_content')
    <p style="{{ $styles['paragraph'] }}">@lang('app.request_for_password_reset_made')</p>
    <p style="{{ $styles['paragraph'] }}">@lang('app.if_you_did_not_requested')</p>
@endsection