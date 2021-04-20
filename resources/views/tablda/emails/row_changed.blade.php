@extends('tablda.emails.layout')

@section('email_content')
    @if(!empty($replace_main_message))
        <p style="{{ $styles['paragraph'] }}">
            {{ $replace_main_message }}
        </p>
    @else
        <p style="{{ $styles['paragraph'] }}">Following {{ $type == 'added' ? 'new' : ($type == 'updated' ? 'existing' : '') }}
            {{ (count($all_rows_arr) > 1 ? 'records' : 'record') }}
            {{ (count($all_rows_arr) > 1 ? 'have' : 'has') }} been {{ $type }}
            in table <span style="color: #2ca02c">"{{ ($table_arr['name'] ?? '') }}"</span>:
        </p>
    @endif

    @include('tablda.emails.partial_table', [
        'pt_mail_format' => ($alert_arr['mail_format'] ?? ''),
        'pt_fields_arr' => $fields_arr ?? [],
        'pt_all_rows' => $all_rows_arr ?? [],
        'pt_has_unit' => $has_unit ?? false,
    ])


    {{--DCR LINKS--}}
    @if(isset($link_tables) && is_array($link_tables))
        @foreach($link_tables as $ltable)
            <p style="{{ $styles['paragraph'] }}">Linked Table <span style="color: #2ca02c">"{{ ($ltable['name'] ?? '') }}"</span>:</p>

            @include('tablda.emails.partial_table', [
                'pt_mail_format' => ($alert_arr['mail_format'] ?? ''),
                'pt_fields_arr' => $ltable['fields'] ?? [],
                'pt_all_rows' => $ltable['all_rows'] ?? [],
                'pt_has_unit' => $ltable['has_unit'] ?? false,
            ])
        @endforeach
    @endif

@endsection