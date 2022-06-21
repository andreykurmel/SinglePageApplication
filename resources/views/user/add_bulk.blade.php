@extends('layouts.app')

@section('page-title', trans('app.add_user'))
@section('page-heading', trans('app.add_bulk_users'))

@section('breadcrumbs')
    <li class="breadcrumb-item">
        <a href="{{ route('user.list') }}">@lang('app.users')</a>
    </li>
    <li class="breadcrumb-item active">
        @lang('app.create')
    </li>
@stop

@section('content')

@include('partials.messages')

<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-md-12">
                {!! Form::open(['route' => 'user.store_bulk_csv', 'files' => true, 'id' => 'user-form-csv']) !!}
                    <h5 class="card-title">Upload and import a CSV File saving email addresses.</h5>
                    <div style="display: flex; white-space: nowrap; align-items: center;">
                        <input type="file" class="form-control" name="csv_emails" placeholder="Emails" accept=".csv">
                        <button type="submit" class="btn btn-primary" style="margin-left: 15px;">Import</button>
                    </div>
                {!! Form::close() !!}
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-md-12">
                {!! Form::open(['route' => 'user.store_bulk_paste', 'files' => true, 'id' => 'user-form-paste']) !!}
                    <div class="form-group" style="display: flex; white-space: nowrap; align-items: center; justify-content: space-between;">
                        <h5 class="card-title" style="margin: 0;">Paste email addresses separated by comma, space or semi-colon:</h5>
                        <button type="submit" class="btn btn-primary" style="margin-left: 15px;">Import</button>
                    </div>
                    <textarea class="form-control" name="pasted_emails" placeholder="Emails" rows="5"></textarea>
                {!! Form::close() !!}
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-md-12">
                <h5 class="card-title" style="margin: 0;">
                    A confirmation email with auto-generated password will be sent to emails imported.
                    If there is an existing account associated with any email, that email will be ignored.
                    Newly registered users need to confirm their registration, open, read and accept the ToS at the first-time login.
                </h5>
            </div>
        </div>

    </div>
</div>

<br>
@stop