<div class="modal-form">
    <div class="form-wrap">
        <div class="logo">
            <img src="{{ url('assets/img/TablDA_w_text_full.png') }}" width="80%" alt="{{ config('app.name') }}">
        </div>

        @include('partials/messages')

        <form role="form" action="<?= url('login') ?>" method="POST" id="login-form" autocomplete="off" novalidate="novalidate">
            <input type="hidden" value="<?= csrf_token() ?>" name="_token">
            <input type="hidden" value="<?= $_SERVER['REQUEST_URI'] ?>" name="cur_path">
            <div class="form-group input-icon has-success">
                <label for="username" class="sr-only">@lang('app.email_or_username')</label>
                <i class="fa fa-user"></i>
                <input type="email" name="username" class="form-control valid" placeholder="@lang('app.email_or_username')" aria-invalid="false" aria-describedby="username-error"><span id="username-error" class="help-block error-help-block"></span>
            </div>
            <div class="form-group password-field input-icon">
                <label for="password" class="sr-only">@lang('app.password')</label>
                <i class="fa fa-lock"></i>
                <input type="password" name="password" class="form-control" placeholder="@lang('app.password')">
                <a href="javascript:void(0)" onclick="$('#remind').show();$('#login, #registar').hide()" class="forgot">@lang('app.i_forgot_my_password')</a>
            </div>
            <div class="form-group remember-row">
                <input type="checkbox" name="remember" id="remember" value="1">
                <label for="remember">@lang('app.remember_me')</label>

                <a href="javascript:void(0)" v-on:click="showRegister()">@lang('app.dont_have_an_account')</a>
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-success btn-lg btn-block" id="btn-login">Log In</button>
            </div>
        </form>
        <div class="divider-wrapper">
            <hr class="or-divider">
        </div>

        @include('tablda.buttons')

    </div>
    <div class="row">
        <div class="col-xs-12" style="text-align: center;font-size: 12px;">
            <p>@lang('app.copyright') © - {{ config('app.name') }} {{ date('Y') }}</p>
        </div>
    </div>
</div>