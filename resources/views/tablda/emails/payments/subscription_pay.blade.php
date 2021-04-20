@extends('tablda.emails.layout')

@section('email_content')
    <p style="{{ $styles['paragraph'] }}">@lang('app.subscription_pay__thanks', ['domain' => config('app.url_domain')])</p>
    <p style="{{ $styles['paragraph'] }}">@lang('app.subscription_pay__listing')</p>

    <table style="{{ $styles['table'] }}">
        <tr>
            <td style="{{ $styles['table--th'] }}">Date</td>
            <td style="{{ $styles['table--td'] }}">{{ date('Y-m-d H:i') }}</td>
        </tr>
        <tr>
            <td style="{{ $styles['table--th'] }}">Account Billed</td>
            <td style="{{ $styles['table--td'] }}">{{ $username }}, ({{ $user->email }})</td>
        </tr>
        <tr>
            <td style="{{ $styles['table--th'] }}">Plan - {{ $user->_subscription->_plan->name }}</td>
            <td style="{{ $styles['table--td'] }}">${{ $user->_subscription->_plan->{$cost_col} }}</td>
        </tr>
        <tr>
            <td style="{{ $styles['table--th'] }}">Add-on</td>
            <td style="{{ $styles['table--td'] }}"></td>
        </tr>
        @foreach($user->_subscription->_addons as $addon)
        <tr>
            <td style="{{ $styles['table--th'] }};text-align: right">{{ $addon->name }}</td>
            <td style="{{ $styles['table--td'] }}">${{ $addon->{$cost_col} }}</td>
        </tr>
        @endforeach
        <tr>
            <td style="{{ $styles['table--th'] }}">Total</td>
            <td style="{{ $styles['table--td'] }}">${{ $user->_subscription->cost }}</td>
        </tr>
        <tr>
            <td style="{{ $styles['table--th'] }}">Charged To</td>
            <td style="{{ $styles['table--td'] }}">{{ $from_details }}</td>
        </tr>
        <tr>
            <td style="{{ $styles['table--th'] }}">Amount</td>
            <td style="{{ $styles['table--td'] }}">${{ $transferred }}</td>
        </tr>
        <tr>
            <td style="{{ $styles['table--th'] }}">Transaction ID</td>
            <td style="{{ $styles['table--td'] }}">{{ $transaction_id }}</td>
        </tr>
        <tr>
            <td style="{{ $styles['table--th'] }}">For Service</td>
            <td style="{{ $styles['table--td'] }}">{{ $service_date }}</td>
        </tr>
    </table>
    <p></p>
@endsection