@extends('tablda.app')

@section('page-title', 'Stim Ma Json')

@section('content')
    <stim-ma-json
            :errors_present="{{ json_encode($errors_present) }}"
            :warnings_present="{{ json_encode($warnings_present) }}"
            v-cloak=""
    ></stim-ma-json>
@endsection