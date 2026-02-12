@extends('tablda.app')

@section('page-title', 'Application')

@section('content')
    <stim-iframe-app
            :iframe_path="'{{ $iframe_path }}'"
            v-cloak=""
    ></stim-iframe-app>
@endsection