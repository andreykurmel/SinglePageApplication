@extends('tablda.app')

@section('page-title', 'Stim Calculate Loads')

@section('content')
    <payment-processing-page
            :input_row="{{ json_encode($row) }}"
            :input_link="{{ json_encode($link) }}"
            :errors_present="{{ json_encode($errors_present) }}"
            v-cloak=""
    ></payment-processing-page>
@endsection