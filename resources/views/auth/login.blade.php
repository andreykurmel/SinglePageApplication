@extends('layouts.auth')

@section('page-title', trans('app.login'))

@section('content')

<div class="col-md-8 col-lg-6 col-xl-5 mx-auto my-10p" id="login">
    <div class="card mt-5">
        <div class="card-body">
            <div class="logo">
                <img src="{{ url('assets/img/TablDA_w_text_full.png') }}" width="80%" alt="{{ config('app.name') }}">
            </div>

            <div class="p-4">
                @include('auth.social.buttons')

                @include('partials.messages')

                <form role="form" action="<?= url('login') ?>" method="POST" id="login-form" autocomplete="off" class="mt-3">

                    <input type="hidden" value="<?= csrf_token() ?>" name="_token">
                    <input type="hidden" value="<?= $_SERVER['REQUEST_URI'] ?>" name="cur_path">

                    @if (Input::has('to'))
                        <input type="hidden" value="{{ Input::get('to') }}" name="to">
                    @endif

                    <div class="form-group">
                        <label for="username" class="sr-only">@lang('app.email')</label>
                        <input type="text"
                                name="username"
                                id="username"
                                class="form-control"
                                placeholder="@lang('app.email')">
                    </div>

                    <div class="form-group password-field">
                        <label for="password" class="sr-only">@lang('app.password')</label>
                        <input type="password"
                               name="password"
                               id="password"
                               class="form-control"
                               placeholder="@lang('app.password')">
                    </div>


                    @if (settings('remember_me'))
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" name="remember" id="remember" value="1"/>
                            <label class="custom-control-label font-weight-normal" for="remember">
                                @lang('app.remember_me')
                            </label>
                        </div>
                    @endif


                    <div class="form-group mt-4">
                        <button type="submit" class="btn btn-primary btn-lg btn-block" id="btn-login">
                            @lang('app.log_in')
                        </button>
                    </div>
                </form>

                @if (settings('forgot_password'))
                    <a href="<?= url('password/remind') ?>" class="forgot">@lang('app.i_forgot_my_password')</a>
                @endif
                <div class="text-center text-muted" style="margin-top: 15px">
                    @if (settings('reg_enabled'))
                        @lang('app.dont_have_an_account') <a class="font-weight-bold" href="<?= url("register") ?>">Sign Up</a>
                    @endif
                </div>
            </div>
        </div>
    </div>



</div>

@stop

@section('scripts')
    {!! HTML::script('assets/js/as/login.js') !!}
    {!! JsValidator::formRequest('Vanguard\Http\Requests\Auth\LoginRequest', '#login-form') !!}
@stop