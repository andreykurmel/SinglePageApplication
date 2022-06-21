@extends('tablda.app')

@section('page-title', $object->name ? $object->name : 'Data')

@section('content')
    <single-record-wrapper
            inline-template
    >
        <single-record-view
            :settings-meta="$root.settingsMeta"
            :init-object="{{ json_encode($object) }}"
        ></single-record-view>
    </single-record-wrapper>
@endsection