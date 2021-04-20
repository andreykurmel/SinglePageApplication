@extends('tablda.app')

@section('page-title',  settings('app_name'))

@section('content')
    <static-pages
            :pages="{{ json_encode($pages) }}"
            :selected_type="'{{ $selected_type }}'"
            :selected_page="{{ json_encode($selected_page) }}"
    ></static-pages>
@endsection