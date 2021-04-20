@extends('tablda.app')

@section('page-title', 'STIM 3D')

@section('content')

    <template v-if="$root.user && $root.user.view_locked">
        <view-pass-block></view-pass-block>
    </template>
    <template v-else>
        <stim-wid-form
                :init_tab="'{{ $init_tab }}'"
                :init_select="'{{ $init_select }}'"
                @if(!empty($init_model))
                :init_model="{{ json_encode($init_model) }}"
                @endif
                :stim_link_params="{{ json_encode($stim_link_params) }}"
                :stim_settings="{{ json_encode($stim_settings) }}"
                v-cloak=""
        ></stim-wid-form>
    </template>

@endsection