<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <div class="row">
                <div class="col-3">
                    <label class="vertical-flex" for="first_name">@lang('app.role')</label>
                </div>
                <div class="col-9">
                    {!! Form::select('role_id', $roles, $user ? $user->role->id : '',
                        ['class' => 'form-control', 'id' => 'role_id', $profile ? 'disabled' : '']) !!}
                </div>
            </div>
        </div>
        <div class="form-group">
            <div class="row">
                <div class="col-3">
                    <label class="vertical-flex" for="status">@lang('app.status')</label>
                </div>
                <div class="col-9">
                    {!! Form::select('status', $statuses, $user ? $user->status : '',
                        ['class' => 'form-control', 'id' => 'status', $profile ? 'disabled' : '']) !!}
                </div>
            </div>
        </div>
        <div class="form-group">
            <div class="row">
                <div class="col-3">
                    <label class="vertical-flex" for="first_name">@lang('app.first_name')</label>
                </div>
                <div class="col-9">
                    <input type="text" class="form-control" id="first_name"
                           name="first_name" placeholder="@lang('app.first_name')" value="{{ $user ? $user->first_name : '' }}">
                </div>
            </div>
        </div>
        <div class="form-group">
            <div class="row">
                <div class="col-3">
                    <label class="vertical-flex" for="last_name">@lang('app.last_name')</label>
                </div>
                <div class="col-9">
                    <input type="text" class="form-control" id="last_name"
                           name="last_name" placeholder="@lang('app.last_name')" value="{{ $user ? $user->last_name : '' }}">
                </div>
            </div>
        </div>
        <div class="form-group">
            <div class="row">
                <div class="col-3">
                    <label class="vertical-flex" for="team">@lang('app.team')</label>
                </div>
                <div class="col-9">
                    <input type="text" class="form-control" id="team"
                           name="team" placeholder="@lang('app.team')" value="{{ $user ? $user->team : '' }}">
                </div>
            </div>
        </div>
        <div class="form-group">
            <div class="row">
                <div class="col-3">
                    <label class="vertical-flex" for="team">@lang('app.timezone')</label>
                </div>
                <div class="col-9" id="vanguard-timezone">
                    <vanguard-timezone hidden_name="timezone" cur_tz="{{ $user ? $user->timezone : '' }}"></vanguard-timezone>
                </div>
            </div>
        </div>
        <div class="form-group">
            <div class="row" style="white-space: nowrap;">
                <div class="col-3">
                    <label class="vertical-flex" for="auto_logout">@lang('app.auto_logout')</label>
                </div>
                <div class="col-9 d-flex">
                    <input type="number" class="form-control" id="auto_logout"
                           name="auto_logout" placeholder="@lang('app.auto_logout_placeholder')" value="{{ $user ? $user->auto_logout : '' }}">
                    <label class="vertical-flex ml-2" for="">@lang('app.auto_logout_minutes')</label>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group">
            <div class="row">
                <div class="col-3">
                    <label class="vertical-flex" for="birthday">@lang('app.date_of_birth')</label>
                </div>
                <div class="col-9">
                    <input type="text"
                           name="birthday"
                           id='birthday'
                           value="{{ $user ? $user->present()->birthday : '' }}"
                           class="form-control" />
                </div>
            </div>
        </div>
        <div class="form-group">
            <div class="row">
                <div class="col-3">
                    <label class="vertical-flex" for="phone">@lang('app.phone')</label>
                </div>
                <div class="col-9">
                    <input type="text" class="form-control" id="phone"
                           name="phone" placeholder="@lang('app.phone')" value="{{ $user ? $user->phone : '' }}">
                </div>
            </div>
        </div>
        <div class="form-group">
            <div class="row">
                <div class="col-3">
                    <label class="vertical-flex" for="address">@lang('app.address')</label>
                </div>
                <div class="col-9">
                    <input type="text" class="form-control" id="address"
                           name="address" placeholder="@lang('app.address')" value="{{ $user ? $user->address : '' }}">
                </div>
            </div>
        </div>
        <div class="form-group">
            <div class="row">
                <div class="col-3">
                    <label class="vertical-flex" for="address">@lang('app.country')</label>
                </div>
                <div class="col-9">
                    {!! Form::select('country_id', $countries, $user ? $user->country_id : '', ['class' => 'form-control']) !!}
                </div>
            </div>
        </div>
        <div class="form-group">
            <div class="row">
                <div class="col-3">
                    <label class="vertical-flex" for="company">@lang('app.company')</label>
                </div>
                <div class="col-9">
                    <input type="text" class="form-control" id="company"
                           name="company" placeholder="@lang('app.company')" value="{{ $user ? $user->company : '' }}">
                </div>
            </div>
        </div>
        <div class="form-group">
            <div class="row">
                <div class="col-3">
                    <label class="vertical-flex" for="subdomain">@lang('app.subdomain')</label>
                </div>
                <div class="col-9">
                    <input type="text" class="form-control" id="subdomain"
                           disabled="{{ auth()->user()->_available_features->apps_are_avail ? '' : 'disabled' }}"
                           name="subdomain" placeholder="@lang('app.subdomain')" value="{{ $user ? $user->subdomain : '' }}">
                </div>
            </div>
        </div>
        @if($user && !$user->sub_icon)
        <div class="form-group">
            <div class="row">
                <div class="col-3">
                    <label class="vertical-flex" for="icon_sub_domain">@lang('app.sub_icon')</label>
                </div>
                <div class="col-9">
                    <input type="file" class="form-control" id="icon_sub_domain" accept="image/*" onchange="$('#update-details-btn').submit()"
                           name="icon_sub_domain" placeholder="@lang('app.sub_icon')" value="{{ $user ? $user->sub_icon : '' }}">
                </div>
            </div>
        </div>
        @endif
        @if($user && $user->sub_icon)
        <div class="form-group">
            <div class="row">
                <div class="col-3">
                    <label class="vertical-flex" for="icon_sub_domain">@lang('app.sub_icon')</label>
                </div>
                <div class="col-9">
                    <img class="icon-image" src="{{ '/file/' . $user->sub_icon }}">
                    &nbsp;
                    <input type="hidden" name="delete_sub_icon" id="delete_sub_icon" value="0">
                    &nbsp;
                    <button type="submit" class="btn btn-danger btn-sm" onclick="$('#delete_sub_icon').val(1)">X</button>
                </div>
            </div>
        </div>
        @endif
        @if($user)
        <div class="form-group">
            <div class="row">
                <div class="col-3">
                    <label class="vertical-flex" for="tos_accepted">@lang('app.tos_accepted')</label>
                </div>
                <div class="col-9">
                    <input type="text" class="form-control" disabled="disabled" id="tos_accepted" value="{{ $user->tos_accepted }}">
                </div>
            </div>
        </div>
        @endif
    </div>

    @if ($edit)
        <div class="col-md-12 mt-2">
            <button type="submit" class="btn btn-primary" id="update-details-btn">
                <i class="fa fa-refresh"></i>
                @lang('app.update_details')
            </button>
        </div>
    @endif
</div>
