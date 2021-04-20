@extends('tablda.app')

@section('page-title', $object->name ? $object->name : 'Data')

@section('content')
    <layout-data
            inline-template
            :init_object_id="{{ $object->id }}"
            :init_object_type="'{{ $type }}'"
            :settings-meta="$root.settingsMeta"
            :panels_preset="{{ !empty($panels_preset) ? json_encode($panels_preset) : '{}' }}"
    >
        <div id="layout-data" class="full-height">
            <template v-if="$root.user && $root.user.see_view && $root.user.view_locked">

                <view-pass-block></view-pass-block>

            </template>
            <template v-else>

                <tos-checker></tos-checker>
                <left-menu
                    v-show="$root.isLeftMenu"
                    :app_domain="'{{$app_domain}}'"
                    :table_id="object_id"
                    :settings-meta="$root.settingsMeta"
                    :embed="{{$embed}}"
                    :view_tree="{{ !empty($tree) ? json_encode($tree) : '{}' }}"
                    @update-object-id="updateObjectId"
                ></left-menu>
                <tables
                    v-show="object_type === 'table'"
                    :settings-meta="$root.settingsMeta"
                    :is-pagination="isPagination"
                    :table_id="object_type === 'table' ? object_id : null"
                    :recalc_id="'{{ Session::get('recalc_id') }}'"
                    :filters_preset="{{ json_encode($filters) }}"
                ></tables>
                <folders
                    v-show="object_type === 'folder'"
                    :folder_id="object_type === 'folder' ? object_id : null"
                    :settings-meta="$root.settingsMeta"
                ></folders>
                <right-menu
                    v-show="$root.isRightMenu"
                    :table_id="object_type === 'table' ? object_id : null"
                ></right-menu>

            </template>
        </div>
    </layout-data>
@endsection