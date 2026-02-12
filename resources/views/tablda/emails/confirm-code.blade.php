@extends('tablda.emails.layout')

@section('email_content')
    <p style="{{ $styles['paragraph'] }}">@lang('app.thank_you_for_registering')</p>
    @if(isset($user) && $user['_tmp_pass'])
    <p style="{{ $styles['paragraph'] }}">Your temporary password is: {{ $user['_tmp_pass'] }}</p>
    @endif
    <p style="{{ $styles['paragraph'] }}">{!! $main_message ?? trans('app.confirm_email_on_link_below') !!}</p>
@endsection