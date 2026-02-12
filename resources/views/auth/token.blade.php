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

                <div class="form-group" style="display: flex; align-items: center;">
                    <label for="password" class="sr-only">@lang('app.token')</label>
                    @if ($user->two_factor_type == 'sms')
                        <input type="text"
                               name="token"
                               id="token"
                               class="form-control"
                               placeholder="Authy 2FA Token"
                               onkeyup="value = String(parseInt(value) || '').substring(0, 7);">
                    @else
                        <input type="text"
                               name="token"
                               id="token"
                               class="form-control"
                               placeholder="Email 2FA Token"
                               onkeyup="value = String(parseInt(value) || '').substring(0, 6);">
                    @endif

{{--                    <input type="text" name="token[0]" id="token1" class="form-control" onfocus="document.getElementById('token1').value = ''" onkeyup="document.getElementById('token2').focus()">--}}
{{--                    <input type="text" name="token[1]" id="token2" class="form-control" onfocus="document.getElementById('token2').value = ''" onkeyup="document.getElementById('token3').focus()">--}}
{{--                    <span class="span">-</span>--}}
{{--                    <input type="text" name="token[2]" id="token3" class="form-control" onfocus="document.getElementById('token3').value = ''" onkeyup="document.getElementById('token4').focus()">--}}
{{--                    <input type="text" name="token[3]" id="token4" class="form-control" onfocus="document.getElementById('token4').value = ''" onkeyup="document.getElementById('token5').focus()">--}}
{{--                    <input type="text" name="token[4]" id="token5" class="form-control" onfocus="document.getElementById('token5').value = ''" onkeyup="document.getElementById('token6').focus()">--}}
{{--                    <span class="span">-</span>--}}
{{--                    <input type="text" name="token[5]" id="token6" class="form-control" onfocus="document.getElementById('token6').value = ''" onkeyup="document.getElementById('token7').focus()">--}}
{{--                    <input type="text" name="token[6]" id="token7" class="form-control" onfocus="document.getElementById('token7').value = ''" onkeyup="document.getElementById('btn-reset-password').focus()">--}}
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
<style>
    .form-control {
        margin: 0 5px;
        text-align: center;
    }
    .span {
        font-size: 22px;
        font-weight: bold;
    }
</style>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        document.getElementById('token').focus();
    });
</script>

@stop