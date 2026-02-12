<!DOCTYPE html>
<html>

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

    <style type="text/css" rel="stylesheet" media="all">
        /* Media Queries */
        @media only screen and (max-width: 500px) {
            .button {
                width: 100% !important;
            }
        }
    </style>
</head>
<body style="{{ $styles['body'] }}">
    <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td style="{{ $styles['email-wrapper'] }}" align="center">
                <table width="100%" cellpadding="0" cellspacing="0">
                    <!-- Logo -->
                    <tr>
                        <td style="{{ $styles['email-masthead'] }}">
                            <a style="{{ $fontFamily }} {{ $styles['email-masthead_name'] }}" href="{{ url('/') }}" target="_blank">
                                <img src="{{ url('assets/img/TablDA_w_text_full.png') }}" alt="TablDA" height="60">
                                {{--{{ config('app.name') }}--}}
                            </a>
                        </td>
                    </tr>

                    <!-- Email Body -->
                    <tr>
                        <td style="{{ $styles['email-body'] }}" width="100%">
                            <table style="{{ $styles['email-body_inner'] }}" align="center" width="570" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="{{ $fontFamily }} {{ $styles['email-body_cell'] }}">
                                        <!-- Greeting -->
                                        <h1 style="{{ $styles['header-1'] }}">
                                            @if (! empty($greeting))
                                                {{ $greeting }}
                                            @else
                                                Hello!
                                            @endif
                                        </h1>

                                        <!-- Content -->
                                        @yield('email_content')

                                        <!-- Action Button -->
                                        @if (!empty($mail_action))
                                            <table style="{{ $styles['body_action'] }}" align="center" width="100%" cellpadding="0" cellspacing="0">
                                                <tr>
                                                    <td align="center">
                                                        <a href="{{ $mail_action['url'] ?? '' }}"
                                                            style="{{ $fontFamily }} {{ $styles['button'] }} {{ $styles['button--green'] }}"
                                                            class="button"
                                                            target="_blank">
                                                            {{ $mail_action['text'] ?? '' }}
                                                        </a>
                                                    </td>
                                                </tr>
                                            </table>
                                        @endif

                                        <!-- Salutation -->
                                        @if (!empty($custom_salutation))
                                            <p style="{{ $styles['paragraph'] }}">
                                                {!! nl2br($custom_salutation) !!}
                                            </p>
                                        @else
                                            <p style="{{ $styles['paragraph'] }}">
                                                Best,<br>{{ config('app.name') }} Team
                                            </p>
                                        @endif

                                        <!-- Sub Copy -->
                                        @if (!empty($mail_action))
                                            <table style="{{ $styles['body_sub'] }}">
                                                <tr>
                                                    <td style="{{ $fontFamily }}">
                                                        <p style="{{ $styles['paragraph-sub'] }}">
                                                            @lang('app.if_you_cant_click', ['button' => ($mail_action['text'] ?? '')])
                                                        </p>

                                                        <p style="{{ $styles['paragraph-sub'] }}">
                                                            <a style="{{ $styles['anchor'] }}" href="{{ $mail_action['url'] ?? '' }}" target="_blank">
                                                                {{ $mail_action['url'] ?? '' }}
                                                            </a>
                                                        </p>
                                                    </td>
                                                </tr>
                                            </table>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td>
                            <table style="{{ $styles['email-footer'] }}" align="center" width="570" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="{{ $fontFamily }} {{ $styles['email-footer_cell'] }}">
                                        <p style="{{ $styles['paragraph-sub'] }}">
                                            &copy; {{ date('Y') }}
                                            <a style="{{ $styles['anchor'] }}" href="{{ url('/') }}" target="_blank">{{ config('app.name') }}</a>.
                                            @lang('app.all_rights_reserved')
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
