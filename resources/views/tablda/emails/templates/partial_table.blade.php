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
                    <td style="{{ $styles['table--td-changed'] }}" align="{{ $fld['col_align'] }}">
                    @else
                    <td style="{{ $styles['table--td'] }}" align="{{ $fld['col_align'] }}">
                    @endif
                        @if(Vanguard\Services\Tablda\HelperService::hasWebLinkUrl($row_arr, $fld))
                            <a href="{{ Vanguard\Services\Tablda\HelperService::hasWebLinkUrl($row_arr, $fld) }}" target="_blank">
                                {{ Vanguard\Services\Tablda\HelperService::cellForEmail($row_arr, $fld) }}
                            </a>
                        @else
                            <span>{{ Vanguard\Services\Tablda\HelperService::cellForEmail($row_arr, $fld) }}</span>
                        @endif
                    </td>
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
                    <td style="{{ $styles['table--td-changed'] }}" align="{{ $fld['col_align'] }}">
                    @else
                    <td style="{{ $styles['table--td'] }}" align="{{ $fld['col_align'] }}">
                    @endif
                        @if(Vanguard\Services\Tablda\HelperService::hasWebLinkUrl($row_arr, $fld))
                            <a href="{{ Vanguard\Services\Tablda\HelperService::hasWebLinkUrl($row_arr, $fld) }}" target="_blank">
                                {{ Vanguard\Services\Tablda\HelperService::cellForEmail($row_arr, $fld) }}
                            </a>
                        @else
                            <span>{{ Vanguard\Services\Tablda\HelperService::cellForEmail($row_arr, $fld) }}</span>
                        @endif
                    </td>

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

                        <span>{{ Vanguard\Services\Tablda\HelperService::cellForEmail($row_arr, $fld) }}</span>
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