@if($pt_mail_format == 'table')
    <table style="{{ $styles['table'] }}">
        {{--Field Headers--}}
        <tr>
            @foreach($pt_fields_arr as $fld)
                @if($pt_has_unit && !($fld['unit'] ?? '') && !($fld['unit_display'] ?? ''))
                    <th style="{{ $styles['table--th'] }}" rowspan="2">
                @else
                    <th style="{{ $styles['table--th'] }}">
                        @endif
                        <span>{{ ($fld['name'] ?? '') }}</span>
                    </th>
                @endforeach
        </tr>
        @if($pt_has_unit)
            <tr>
                @foreach($pt_fields_arr as $fld)
                    @if(($fld['unit'] ?? '') || ($fld['unit_display'] ?? ''))
                        <th style="{{ $styles['table--th'] }}">
                            <span>({{ ($fld['unit_display'] ?? '') ?: ($fld['unit'] ?? '') }})</span>
                        </th>
                    @endif
                @endforeach
            </tr>
        @endif
        {{--Row Values--}}
        @foreach($pt_all_rows as $row_arr)
            <tr>
                @foreach($pt_fields_arr as $fld)
                    @if(isset($type) && $type == 'updated' && in_array($fld['field'], $changed_fields))
                        <td style="{{ $styles['table--td-changed'] }}">{{ ($row_arr[$fld['field']] ?? '') }}</td>
                    @else
                        <td style="{{ $styles['table--td'] }}">{{ ($row_arr[$fld['field']] ?? '') }}</td>
                    @endif
                @endforeach
            </tr>
        @endforeach
    </table>
    <p></p>
@endif

@if($pt_mail_format == 'vertical')
    @foreach($pt_all_rows as $row_arr)
        <table style="{{ $styles['table'] }}">
            @foreach($pt_fields_arr as $fld)
                <tr>
                    <td style="{{ $styles['table--th'] }}">
                        <span>{{ ($fld['name'] ?? '') }}</span>
                    </td>

                    @if(isset($type) && $type == 'updated' && in_array($fld['field'], $changed_fields))
                        <td style="{{ $styles['table--td-changed'] }}">{{ ($row_arr[$fld['field']] ?? '') }}</td>
                    @else
                        <td style="{{ $styles['table--td'] }}">{{ ($row_arr[$fld['field']] ?? '') }}</td>
                    @endif

                    @if($pt_has_unit)
                        <td style="{{ $styles['table--td-noborder'] }}">
                            @if(($fld['unit'] ?? '') || ($fld['unit_display'] ?? ''))
                                <span>({{ ($fld['unit_display'] ?? '') ?: ($fld['unit'] ?? '') }})</span>
                            @endif
                        </td>
                    @endif
                </tr>
            @endforeach
        </table>
        <p></p>
    @endforeach
@endif

@if($pt_mail_format == 'list')
    @foreach($pt_all_rows as $row_arr)
        <ul style="{{ $styles['list'] }}">
            @foreach($pt_fields_arr as $fld)
                <li>
                    <span style="{{ $styles['list--head'] }}">{{ ($fld['name'] ?? '') }}:</span>

                    @if(isset($type) && $type == 'updated' && in_array($fld['field'], $changed_fields))
                        <span style="{{ $styles['list--data-changed'] }}">
                    @else
                        <span style="{{ $styles['list--data'] }}">
                    @endif
                        <span>{{ ($row_arr[$fld['field']] ?? '') }}</span>
                        @if($pt_has_unit && ($fld['unit'] ?? '') || ($fld['unit_display'] ?? ''))
                            <span>({{ ($fld['unit_display'] ?? '') ?: ($fld['unit'] ?? '') }})</span>
                        @endif
                    </span>

                </li>
            @endforeach
        </ul>
        <p></p>
    @endforeach
@endif