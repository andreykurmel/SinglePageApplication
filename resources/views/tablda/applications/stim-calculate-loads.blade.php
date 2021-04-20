@extends('tablda.app')

@section('page-title', 'Stim Calculate Loads')

@section('content')
    <stim-calculate-loads
            :apppath="'{{ $apppath }}'"
            :tiapath="'{{ $tiapath }}'"
            :jsonfile="'{{ $jsonfile }}'"
            :targetfile="'{{ $targetfile }}'"
            :usergroup="'{{ $usergroup }}'"
            :model="'{{ $model }}'"
            :errors_present="{{ json_encode($errors_present) }}"
            v-cloak=""
    ></stim-calculate-loads>
@endsection