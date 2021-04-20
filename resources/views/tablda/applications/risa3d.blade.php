@extends('tablda.app')

@section('page-title', 'Risa3D Parser')

@section('content')
    <risa3d-form
            :usergroup="'{{ $usergroup }}'"
            :mg_name="'{{ $mg_name }}'"
            :file_col="{{ $file_col }}"
            :row_id="{{ $row_id }}"
            :table_id="{{ $table_id }}"
            :init_file_present="{{ $file_present }}"
            v-cloak=""
    ></risa3d-form>
@endsection