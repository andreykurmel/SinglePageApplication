@extends('tablda.emails.layout')

@section('email_content')
    <p style="{{ $styles['paragraph'] }}">
        {{ $replace_main_message ?? 'Backup has been created' }}
    </p>

    @include('tablda.emails.partial_table', [
        'pt_mail_format' => ($alert_arr['mail_format'] ?? ''),
        'pt_fields_arr' => $fields_arr ?? [],
        'pt_all_rows' => $all_rows_arr ?? [],
        'pt_has_unit' => $has_unit ?? false,
    ])

@endsection