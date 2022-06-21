@extends('tablda.app')

@section('page-title', 'Application')

@section('content')
    <list-tablda-apps :all_apps="{{ json_encode($all_apps) }}"
                      :subs_ids="{{ json_encode($subs_ids) }}"
                      v-cloak=""
    ></list-tablda-apps>
@endsection