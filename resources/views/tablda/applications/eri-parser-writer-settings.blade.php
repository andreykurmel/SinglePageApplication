@extends('tablda.app')

@section('page-title', $page_title)

@section('content')
    <eri-parser-writer-settings
            :message="'{{ $message }}'"
            :page_code="'{{ $page_code }}'"
            :table_id="'{{ $table_id }}'"
            :link_id="'{{ $link_id }}'"
            :row_id="'{{ $row_id }}'"
            :parts="{{ $parts }}"
            v-cloak=""
    ></eri-parser-writer-settings>
@endsection