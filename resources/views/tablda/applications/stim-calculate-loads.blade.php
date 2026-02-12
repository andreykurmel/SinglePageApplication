@extends('tablda.app')

@section('page-title', 'Stim Calculate Loads')

@section('content')
    <stim-calculate-loads
            :apppath="'{{ $apppath }}'"
            :tiapath="'{{ $tiapath }}'"
            :jsonfile="'{{ $jsonfile }}'"
            :usergroup="'{{ $usergroup }}'"
            :model="'{{ $model }}'"
            :errors_present="{{ json_encode($errors_present) }}"
            :tbid="'{{ $tbid }}'"
            :fldid="'{{ $fldid }}'"
            :rwid="'{{ $rwid }}'"
            :fname="'{{ $fname }}'"
            v-cloak=""
    ></stim-calculate-loads>
@endsection