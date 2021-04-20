<!doctype html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, shrink-to-fit=no">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!--<title>@yield('page-title')</title>-->
    <title>TablDA : Table + Data + APPs</title>

    <link rel="apple-touch-icon-precomposed" sizes="144x144" href="{{ url('assets/img/icons/apple-touch-icon-144x144.png') }}" />
    <link rel="apple-touch-icon-precomposed" sizes="152x152" href="{{ url('assets/img/icons/apple-touch-icon-152x152.png') }}" />
    <link rel="icon" type="image/png" href="{{ url('assets/img/icons/favicon-32x32.png') }}" sizes="32x32" />
    <link rel="icon" type="image/png" href="{{ url('assets/img/icons/favicon-16x16.png') }}" sizes="16x16" />
    <meta name="application-name" content="{{ settings('app_name') }}"/>
    <meta name="msapplication-TileColor" content="#FFFFFF" />
    <meta name="msapplication-TileImage" content="{{ url('assets/img/icons/mstile-144x144.png') }}" />

    <link media="all" type="text/css" rel="stylesheet" href="{{ mix('assets/css/vendor.css') }}">
    <link media="all" type="text/css" rel="stylesheet" href="{{ mix('assets/css/tablda-app.css') }}">

    @yield('styles')
</head>
<body>
    <div id="app"
         class="full-height over-hidden"
         data-app_url="{{ $app_url }}"
         data-clear_url="{{ $clear_url }}"
         data-app_domain="{{ $app_domain }}"
    >
        <main-app-wrapper
            inline-template
            class="body-table full-frame div-screen"
            :app_name="'{{ config('app.name') }}'"
            :app_debug="'{{ config('app.debug') }}'"
            :vue_labels="{{ json_encode($vue_labels) }}"
            :init_user="{{ json_encode($user) }}"
            :flash_message="'{{ Session::get('flash_message') }}'"
            :js-validator-functions="[
                '#login-form__init_validation',
                '#registration-form__init_validation',
                '#remind-password-form__init_validation'
            ]"
            v-cloak=""
        >
            <div v-if="$root.settingsMeta.is_loaded">
                @if(!$embed)
                    @guest
                    <auth-forms
                        v-bind:show_login="show_login"
                        v-bind:settings="{
                            root_url: '{{ config('app.url') }}',
                            app_name: '{{ config('app.name') }}',
                            errors: {{ json_encode($errors->all()) }},
                            session_success: {{ json_encode(Session::get('success', false)) }},
                            year: {{ date('Y') }},
                            social_provider: {{ !!$socialProviders }},
                            csrf_token: '{{ csrf_token() }}',
                            register_old_email: '{{ old('email') }}',
                            register_old_username: '{{ old('username') }}',
                        }"
                    ></auth-forms>
                    @endif

                    <div class="body-row">
                        @include('tablda.navbar')
                    </div>
                @endif

                <div class="body-row full-height">
                    <div class="body-cell full-height">
                        <div class="relative-cell full-height">
                            <div class="full-screen-cell">
                                <div class="container-fluid full-height">
                                    <div class="row full-height">
                                        <main role="main" class="full-height">
                                            @yield('content')
                                        </main>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main-app-wrapper>
        <div class="div-print">
            <print-table v-if="printHeaders" :print-headers="printHeaders" :print-rows="printRows" :all-showed="allShowed"></print-table>
        </div>
        <form action="{{ route('downloader') }}" method="post" id="downloader_form">
            {{ csrf_field() }}
            <input type="hidden" name="filename" id="downloader_method" value="">
            <input type="hidden" name="data" id="downloader_data" value="">
            <input type="hidden" name="time_zone" id="downloader_time" value="">
        </form>
        <form action="{{ route('dwn_chart') }}" method="post" id="dwn_chart">
            {{ csrf_field() }}
            <input type="hidden" name="chart_headers" id="dwn_chart_headers" value="">
            <input type="hidden" name="chart_rows" id="dwn_chart_rows" value="">
            <input type="hidden" name="format_type" id="dwn_chart_format_type" value="">
            <input type="hidden" name="file_name" id="dwn_filename" value="">
        </form>
        <textarea id="for_paste_get" style="position: fixed;top:100%;"></textarea>

        <hover-block v-if="$root.hover_html && $root.hover_show"
                     :html_str="$root.hover_html"
                     :p_left="$root.hover_left"
                     :p_top="$root.hover_top"
                     :c_offset="-10"
                    v-on:tooltip-blur="$root.hover_show = false"
                    v-on:another-click="$root.hover_show = false"
        ></hover-block>

        <link-preview-block v-if="$root.linkprev_object"
                     :meta_table="$root.linkprev_meta"
                     :meta_header="$root.linkprev_meta_header"
                     :meta_row="$root.linkprev_meta_row"
                     :link_object="$root.linkprev_object"
                     :p_left="$root.linkprev_left"
                     :p_top="$root.linkprev_top"
                     :c_offset="-1"
                    v-on:tooltip-blur="$root.linkprev_object = null"
                    v-on:another-click="$root.linkprev_object = null"
        ></link-preview-block>
    </div>

    @if(!$lightweight)
        <script src="https://maps.googleapis.com/maps/api/js?key={{ $gmap_api_key }}&hl=en&language=en&libraries=geometry,places"></script>
        <script src="https://www.paypal.com/sdk/js?client-id={{ $paypal_client }}"></script>
        <script src="https://js.stripe.com/v3/"></script>
    @endif

    @if(isset($load_three_3d))
        <script src="{{ mix('assets/js/three-3d-lib.js') }}"></script>
        <script src="{{ mix('assets/js/three-webgl.js') }}"></script>
    @endif

    <script src="{{ mix('assets/js/tablda/vendor.js') }}"></script>
    <script src="{{ mix('assets/js/tablda/app.js') }}"></script>
    <script src="{{ url('assets/js/as/app.js') }}"></script>

    {!! $vueJsValidator->formRequest('Vanguard\Http\Requests\Auth\LoginRequest', '#login-form') !!}
    {!! $vueJsValidator->formRequest('Vanguard\Http\Requests\Auth\RegisterRequest', '#registration-form') !!}
    {!! $vueJsValidator->formRequest('Vanguard\Http\Requests\Auth\PasswordRemindRequest', '#remind-password-form') !!}

    @if(!$embed && !config('app.debug'))
        @include('tablda.each-file-scripts')
    @endif

    <script src="{{ url('assets/ckeditor/ckeditor.js') }}"></script>

    @stack('scripts')
</body>
</html>
