@extends('tablda.app')

@section('page-title', 'Risa3D Deleter')

@section('content')
    <risa3d-remover-form
            :usergroup="'{{ $usergroup }}'"
            :mg_name="'{{ $mg_name }}'"
            :tables_delete="'{{ $tables_delete }}'"
            v-cloak=""
    ></risa3d-remover-form>
@endsection