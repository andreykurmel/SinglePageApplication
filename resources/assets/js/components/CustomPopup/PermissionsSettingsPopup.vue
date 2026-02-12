<template>
    <div class="popup-wrapper" v-if="tableMeta && show_popup" @click.self="hide()" :style="{zIndex: zIdx}">
        <div class="popup" :style="getPopupStyle()">
            <div class="flex flex--col">
                <div class="popup-header">
                    <div class="drag-bkg" draggable="true" @dragstart="dragPopSt()" @drag="dragPopup()"></div>
                    <span>Defining Permissions</span>
                    <span class="glyphicon glyphicon-remove pull-right header-btn" @click="hide()"></span>
                </div>
                <div class="flex__elem-remain popup-content">
                    <div class="flex__elem__inner popup-main" :style="$root.themeMainBgStyle">

                        <div class="full-frame">
                            <table-settings-permissions
                                    :table-meta="tableMeta"
                                    :user="user"
                                    :table_id="tableMeta.id"
                                    :init_ddl_idx="init_ddl_idx"
                            ></table-settings-permissions>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import {eventBus} from '../../app';

    import PopupAnimationMixin from './../_Mixins/PopupAnimationMixin';

    import TableSettingsPermissions from "../MainApp/Object/Table/SettingsModule/TableSettingsPermissions";

    export default {
        name: "PermissionsSettingsPopup",
        mixins: [
            PopupAnimationMixin,
        ],
        components: {
            TableSettingsPermissions,
        },
        data: function () {
            return {
                show_popup: !!this.init_show,
                init_ddl_idx: -1,
                //PopupAnimationMixin
                getPopupWidth: 1200,
                idx: 0,
            }
        },
        props:{
            tableMeta: Object,
            user: Object,
            init_show: Boolean,
        },
        methods: {
            hide() {
                this.show_popup = false;
                this.$root.tablesZidxDecrease();
                this.$emit('hidden-form');
            },
            showPermissionSettings(db_name, row_id) {
                if (!db_name || db_name === this.tableMeta.db_name) {
                    this.init_ddl_idx = _.findIndex(this.tableMeta._table_permissions, {id: Number(row_id)});
                    this.show_popup = true;
                    this.$root.tablesZidxIncrease();
                    this.zIdx = this.$root.tablesZidx;
                    this.runAnimation();
                }
            }
        },
        mounted() {
            eventBus.$on('global-keydown', this.hideMenu);
            eventBus.$on('show-permission-settings-popup', this.showPermissionSettings);
            if (this.show_popup) {
                this.showPermissionSettings(null);
            }
        },
        beforeDestroy() {
            eventBus.$off('global-keydown', this.hideMenu);
            eventBus.$off('show-permission-settings-popup', this.showPermissionSettings);
        }
    }
</script>

<style lang="scss" scoped>
    @import "CustomEditPopUp";

    .popup-wrapper {

        .popup {
            position: relative;

            .popup-main {
                padding: 0;
            }
        }
    }
</style>