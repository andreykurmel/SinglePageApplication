@extends('layouts.auth')

@section('page-title', trans('app.two_factor_authentication'))

@section('content')

<div class="col-md-8 col-lg-6 col-xl-5 mx-auto my-10p">

    @include('partials/messages')

    <div class="card mt-5">
        <div class="card-body">
            <div class="logo">
                <img src="{{ url('assets/img/TablDA_w_text_full.png') }}" width="80%" alt="{{ config('app.name') }}">
            </div>

            <div class="p-4">
                <form role="form" action="<?= route('auth.token.validate') ?>" method="POST" autocomplete="off">
                <input type="hidden" value="<?= csrf_token() ?>" name="_token">

                <div class="form-group">
                    <label for="password" class="sr-only">@lang('app.token')</label>
                    <input type="text" name="token" id="token" class="form-control" placeholder="@lang('app.authy_2fa_token')">
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary btn-lg btn-block" id="btn-reset-password">
                        @lang('app.validate')
                    </button>
                </div>
            </form>
            </div>
        </div>
    </div>
</div>

@stop