@extends('tablda.emails.layout')

@section('email_content')
    <p style="{{ $styles['paragraph'] }}">
        Enter following code on the sign-in page to login:
    </p>
    <p style="color: #74787e; text-align: center; font-weight: bold; font-size: 24px;">
        {{ $code }}
    </p>
@endsection