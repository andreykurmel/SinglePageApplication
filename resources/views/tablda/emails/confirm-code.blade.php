@extends('tablda.emails.layout')

@section('email_content')
    <p style="{{ $styles['paragraph'] }}">@lang('app.thank_you_for_registering')</p>
    <p style="{{ $styles['paragraph'] }}">@lang('app.confirm_email_on_link_below')</p>
@endsection