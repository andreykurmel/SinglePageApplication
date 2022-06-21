<div class="modal-form">
    <div class="form-wrap">
        <div class="logo">
            <img src="{{ url('assets/img/TablDA_w_text_full.png') }}" width="80%" alt="{{ config('app.name') }}">
        </div>

        @include('partials/messages')

        <form role="form" action="<?= url('register') ?>" method="post" id="registration-form" autocomplete="off">
            <input type="hidden" value="<?= csrf_token() ?>" name="_token">
            <div class="form-group input-icon">
                <i class="fa fa-at"></i>
                <input type="email" ref="register_email" name="email" class="form-control" placeholder="@lang('app.email')" value="{{ old('email') }}">
            </div>
            <div class="form-group input-icon">
                <i class="fa fa-user"></i>
                <input type="text" ref="register_name" name="username" class="form-control" placeholder="@lang('app.username')"  value="{{ old('username') }}">
            </div>
            <div class="form-group input-icon pwd_wrapper">
                <i class="fa fa-lock"></i>
                <input v-model="register_pass" type="password" name="password" class="form-control" placeholder="@lang('app.password')">
                <div class="pwd_info"
                     v-show="!register_pass.match(/[A-z]/)
                         || !register_pass.match(/[A-Z]/)
                         || !register_pass.match(/\d/)
                         || !register_pass.match(/[!$#%@]/)
                         || register_pass.length < 6"
                >
                    <h4>Password must meet following requirements:</h4>
                    <ul>
                        <li :class="[register_pass.match(/[A-z]/) ? 'valid-i' : 'invalid-i']">At least <strong>one letter.</strong></li>
                        <li :class="[register_pass.match(/[A-Z]/) ? 'valid-i' : 'invalid-i']">At least <strong>one capital letter.</strong></li>
                        <li :class="[register_pass.match(/\d/) ? 'valid-i' : 'invalid-i']">At least <strong>one number.</strong></li>
                        <li :class="[register_pass.match(/[!$#%@]/) ? 'valid-i' : 'invalid-i']">At least <strong>one special character.</strong></li>
                        <li :class="[register_pass.length > 6 ? 'valid-i' : 'invalid-i']">At least <strong>6 characters</strong> in length.</li>
                    </ul>
                </div>
            </div>
            <div class="form-group input-icon">
                <i class="fa fa-lock"></i>
                <input type="password"
                       v-model="register_pass_confirm"
                       name="password_confirmation"
                       class="form-control"
                       placeholder="@lang('app.confirm_password')"
                >
            </div>

            @if (settings('tos'))
                <div class="form-group">
                    <div class="">
                        <input type="checkbox" :disabled="!register_check" name="tos" id="tos" value="1"/>
                        <label for="tos">@lang('app.i_accept')
                                <a href="/tos" target="_blank" v-on:click="register_check = true">@lang('app.terms_of_service')</a>
                        </label>
                    </div>
                </div>
            @endif

            {{-- Only display captcha if it is enabled --}}
            @if (settings('registration.captcha.enabled'))
                <div class="form-group">
                    {!! app('captcha')->display() !!}
                </div>
            @endif
            {{-- end captcha --}}

            <div class="form-group">
                <button type="submit" class="btn btn-success btn-lg btn-block" id="btn-register" :disabled="!register_check || !register_pass_the_same">
                    @lang('app.register')
                </button>
            </div>

            <div class="form-group have-acc">
                <a href="javascript:void(0)" onclick="$('#login').show();$('#registar, #remind').hide()">Already have an account?</a>
            </div>
        </form>

        @include('tablda.buttons')

    </div>
    <div class="row">
        <div class="col-xs-12 footer">
            <p>@lang('app.copyright') © - {{ config('app.name') }} {{ date('Y') }}</p>
        </div>
    </div>
</div>