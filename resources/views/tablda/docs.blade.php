@extends('tablda.app')

@section('page-title', settings('app_name'))

@section('content')
    <iframe src="" id="docs-iframe" data-init-path="{{ $docs_domain }}" width="100%" height="100%" frameborder="0"></iframe>
@endsection
