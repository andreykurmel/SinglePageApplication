@extends('tablda.emails.layout')

@section('email_content')
    <p style="{{ $styles['paragraph'] }}">@lang('app.transfer_add__thanks', ['domain' => config('app.url_domain')])</p>
    <p style="{{ $styles['paragraph'] }}">@lang('app.transfer_add__listing')</p>

    <table style="{{ $styles['table'] }}">
        <tr>
            <td style="{{ $styles['table--th'] }}">Date</td>
            <td style="{{ $styles['table--td'] }}">{{ date('Y-m-d H:i') }}</td>
        </tr>
        <tr>
            <td style="{{ $styles['table--th'] }}">Account</td>
            <td style="{{ $styles['table--td'] }}">{{ $username }}, ({{ $user->email }})</td>
        </tr>
        <tr>
            <td style="{{ $styles['table--th'] }}">Balance</td>
            <td style="{{ $styles['table--td'] }}">${{ $user->avail_credit - $transferred }}</td>
        </tr>
        <tr>
            <td style="{{ $styles['table--th'] }}">Credit added</td>
            <td style="{{ $styles['table--td'] }}">${{ $transferred }}</td>
        </tr>
        <tr>
            <td style="{{ $styles['table--th'] }}">New Balance</td>
            <td style="{{ $styles['table--td'] }}">${{ $user->avail_credit }}</td>
        </tr>
        <tr>
            <td style="{{ $styles['table--th'] }}">Credit Source</td>
            <td style="{{ $styles['table--td'] }}">{{ $credit_source }}</td>
        </tr>
        <tr>
            <td style="{{ $styles['table--th'] }}">Transaction ID</td>
            <td style="{{ $styles['table--td'] }}">{{ $transaction_id }}</td>
        </tr>
    </table>
    <p></p>
@endsection