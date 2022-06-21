@extends('tablda.app')

@section('page-title', 'Application')

@section('content')
    <iframe src="{{ $iframe_path }}" width="100%" height="100%"></iframe>
@endsection