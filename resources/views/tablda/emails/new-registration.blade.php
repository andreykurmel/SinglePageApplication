@extends('tablda.emails.layout')

@section('email_content')
    <p style="{{ $styles['paragraph'] }}">@lang('app.new_user_was_registered_on', ['app' => settings('app_name')])</p>

    <p style="{{ $styles['paragraph'] }}">Email: {{ $user->email }}</p>
    <p style="{{ $styles['paragraph'] }}">Username: {{ $user->username }}</p>
    <p style="{{ $styles['paragraph'] }}">Time: {{ date('Y-m-d H:i:s', time()) }} (UTC)</p>
    <p style="{{ $styles['paragraph'] }}">Timezone: {{ $locator->timezone() }}</p>
    <p style="{{ $styles['paragraph'] }}">IP: {{ $locator->ip() }}</p>
    <p style="{{ $styles['paragraph'] }}">Country: {{ $locator->country() }}, City: {{ $locator->city() }}</p>
    <p style="{{ $styles['paragraph'] }}">Organisation: {{ $locator->organisation() }}</p>
    <p style="{{ $styles['paragraph'] }}">Status: {{ $user->status }}</p>

    <p style="{{ $styles['paragraph'] }}">
        <span>@lang('app.to_view_details_visit_link_below')</span>&nbsp;
        <span style="{{ $styles['red-text'] }}">(@lang('app.logged_in_admin_only'))</span>:<br>
        <a href="{{ route('user.show', $user->id) }}">{{ route('user.show', $user->id) }}</a>
    </p>
@endsection