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

{!! Form::open(['route' => 'user.store_bulk', 'files' => true, 'id' => 'user-form']) !!}
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <h5 class="card-title">
                        CSV File
                    </h5>
                    <p class="text-muted font-weight-light">
                        Starting from top left cell, with or without header row (username, email, password, first_name, last_name),
                        sequentially entering Username, Email, Password, First Name and Last Name for users to be added.
                        Username and Email to be unique. Record(s) with username or email taken by existing account(s) will not be added.
                    </p>
                </div>
                <div class="col-md-8">
                    <div class="form-group">
                        <label for="users">@lang('app.users')</label>
                        <input type="file" class="form-control" id="users" name="users" placeholder="@lang('app.users')" accept=".csv">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <button type="submit" class="btn btn-primary">
                Add Users
            </button>
        </div>
    </div>
{!! Form::close() !!}

<br>
@stop