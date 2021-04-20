@extends('tablda.emails.layout')

@section('email_content')
    <p style="{{ $styles['paragraph'] }}">@lang('app.confirmation_thank')</p>
@endsection