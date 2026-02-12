@extends('tablda.app')

@section('page-title', $page_title)

@section('content')
    <simple-messager
            :messages="{{ json_encode($messages) }}"
            v-cloak=""
    ></simple-messager>
@endsection