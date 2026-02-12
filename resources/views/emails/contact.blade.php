Email: {{ $email }} <br>
@if( $company )
Company: {{ $company }} <br>
@endif
@if( $pref_time )
Preferred time: {{ $pref_time }} <br>
@endif
Message: {!! nl2br($mes) !!}