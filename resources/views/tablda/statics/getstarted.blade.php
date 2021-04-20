@extends('tablda.app')

@section('page-title',  settings('app_name'))

@section('content')
    <get-started :pages="{{ json_encode($pages) }}"></get-started>
@endsection
