@if (! Authy::isEnabled($user))
    <div class="alert alert-info">
        @lang('app.in_order_to_enable_2fa_you_must') <a target="_blank" href="https://www.authy.com/">Authy</a> @lang('app.application_on_your_phone').
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="country_code">@lang('app.country_code')</label>
                <select class="form-control" id="country_code" name="country_code">
                    @foreach($phone_countries as $country)
                        <option {{ ($user->two_factor_country_code ?: 1) == $country['code'] ? 'selected' : '' }}
                                value="{{ $country['code'] }}"
                        >{{ $country['name'] }} +{{ $country['code'] }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="phone_number">@lang('app.cell_phone')</label>
                <input type="text" class="form-control" id="phone_number" placeholder="@lang('app.phone_without_country_code')"
                       name="phone_number" value="{{ $user->two_factor_phone }}">
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="country_code">@lang('app.two_factor_type')</label>
                <select id="two_factor_type" name="two_factor_type" class="form-control">
                    <option {{ $user->two_factor_type == 'authy' ? 'selected' : '' }} value="authy">Authy</option>
                    <option {{ $user->two_factor_type == 'sms' ? 'selected' : '' }} value="sms">SMS</option>
                </select>
            </div>
        </div>
    </div>
    <button type="submit" class="btn btn-primary" data-toggle="loader" data-loading-text="@lang('app.enabling')">
        @lang('app.enable')
    </button>
@else
    <button type="submit" class="btn btn-danger mt-2" data-toggle="loader" data-loading-text="@lang('app.disabling')">
        <i class="fa fa-close"></i>
        @lang('app.disable')
    </button>
@endif
