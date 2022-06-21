
<div>
{{--    <p style="{{ $styles['paragraph'] }}">--}}
{{--        {{ $table_header ?? 'Linked Table' }}--}}
{{--    </p>--}}

    @include('tablda.emails.templates.partial_table', [
        'pt_mail_format' => $mail_format,
        'pt_fields_arr' => $fields_arr ?? [],
        'pt_all_rows' => $all_rows_arr ?? [],
        'pt_has_unit' => $has_unit ?? false,
    ])

    @if(!empty($rows_count) && $rows_count > count($all_rows_arr))
        <p style="{{ $styles['paragraph'] }}">
            * Total number of rows exceed 50. {{ $rows_count - count($all_rows_arr) }} rows of records truncated.
        </p>
    @endif
</div>