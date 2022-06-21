<div class="modal-form modal-sm">
    <div class="form-wrap">
        <h1 class="remind">@lang('app.forgot_your_password')</h1>

        @include('partials.messages')

        <form role="form" action="<?= url('password/remind') ?>" method="POST" id="remind-password-form" autocomplete="off">
            <input type="hidden" value="<?= csrf_token() ?>" name="_token">

            <div class="form-group password-field input-icon">
                <label for="password" class="sr-only">@lang('app.email')</label>
                <i class="fa fa-at"></i>
                <input type="email" name="email" class="form-control" placeholder="@lang('app.your_email')">
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-success btn-lg btn-block" id="btn-reset-password">
                    @lang('app.reset_password')
                </button>
                <a href="javascript:void(0)" onclick="$('#login').show();$('#remind, #registar').hide()" class="btn btn-default btn-lg btn-block">
                    Cancel
                </a>
            </div>
        </form>

    </div>
    <div class="row">
        <div class="col-xs-12 footer">
            <p>@lang('app.copyright') © - {{ config('app.name') }} {{ date('Y') }}</p>
        </div>
    </div>
</div>