@extends('tablda.app')

@section('page-title', $object->name ? $object->name : 'Data')

@section('content')
    <table-request-wrapper
            inline-template
            :init_object_id="{{ $object->id }}"
            :init_data_request="{{ json_encode($dcrObject) }}"
            :settings-meta="$root.settingsMeta"
    >
        <main-request
            :settings-meta="$root.settingsMeta"
            :table_id="object_id"
            :dcr-object="data_request"
            :is_embed="{{ $is_embed }}"
        ></main-request>
    </table-request-wrapper>
@endsection