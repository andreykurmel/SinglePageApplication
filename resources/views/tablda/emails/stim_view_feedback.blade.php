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
                                    <!-- Content -->
                                    <p style="{{ $styles['paragraph'] }}">{!! $content_body !!}</p>
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