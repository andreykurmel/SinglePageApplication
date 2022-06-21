@extends('tablda.app')

@section('page-title', 'Application')

@section('content')
    <my-apps-page :my_apps="{{ json_encode($my_apps) }}"
                  :subscribed_apps="{{ json_encode($subscribed_apps) }}"
                  :subs_ids="{{ json_encode($subs_ids) }}"
                  v-cloak=""
    ></my-apps-page>
@endsection