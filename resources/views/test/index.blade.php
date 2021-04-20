@extends('tablda.app')

@section('page-title', trans('app.data'))

@section('content')
    <layout-data inline-template>
        <div id="layout-data" class="full-height">
            <left-menu v-show="isLeftMenu"
                    :tree="{{$tree}}"
            > </left-menu>
            <tables
                    :is-right-menu="isRightMenu"
                    :is-left-menu="isLeftMenu"
                    @update-show-right-menu="showRightMenu"
                    @update-show-left-menu="showLeftMenu"
            > </tables>
            <right-menu v-show="isRightMenu"> </right-menu>
        </div>
    </layout-data>
@endsection