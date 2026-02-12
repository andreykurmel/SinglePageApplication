<template>
    <div class="popup-wrapper" v-if="tableMeta && show_popup" @click.self="hide()" :style="{zIndex: zIdx}">
        <div class="popup" :style="getPopupStyle()">
            <div class="flex flex--col">
                <div class="popup-header">
                    <div class="drag-bkg" draggable="true" @dragstart="dragPopSt()" @drag="dragPopup()"></div>
                    <span>Defining DDLs</span>
                    <span class="glyphicon glyphicon-remove pull-right header-btn" @click="hide()"></span>
                </div>
                <div class="flex__elem-remain popup-content">
                    <div class="flex__elem__inner popup-main" :style="$root.themeMainBgStyle">

                        <div class="full-frame">
                            <table-ddl-settings
                                    :table-meta="tableMeta"
                                    :settings-meta="settingsMeta"
                                    :cell-height="$root.cellHeight"
                                    :max-cell-rows="$root.maxCellRows"
                                    :user="user"
                                    :table_id="tableMeta.id"
                                    :init_ddl_idx="init_ddl_idx"
                            ></table-ddl-settings>
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

    import TableDdlSettings from "../MainApp/Object/Table/SettingsModule/TableDdlSettings";

    export default {
        name: "DdlSettingsPopup",
        mixins: [
            PopupAnimationMixin,
        ],
        components: {
            TableDdlSettings,
        },
        data: function () {
            return {
                show_popup: false,
                init_ddl_idx: -1,
                //PopupAnimationMixin
                getPopupWidth: 1200,
                idx: 0,
            }
        },
        props:{
            tableMeta: Object,
            settingsMeta: Object,
            cellHeight: Number,
            maxCellRows: Number,
            user:  Object,
        },
        methods: {
            hide() {
                this.show_popup = false;
                this.$root.tablesZidxDecrease();
            },
            showDdlSettings(db_name, row_id) {
                if (!db_name || db_name === this.tableMeta.db_name) {
                    this.init_ddl_idx = _.findIndex(this.tableMeta._ddls, {id: Number(row_id)});
                    this.show_popup = true;
                    this.$root.tablesZidxIncrease();
                    this.zIdx = this.$root.tablesZidx;
                    this.runAnimation();
                }
            }
        },
        mounted() {
            eventBus.$on('global-keydown', this.hideMenu);
            eventBus.$on('show-ddl-settings-popup', this.showDdlSettings);
        },
        beforeDestroy() {
            eventBus.$off('global-keydown', this.hideMenu);
            eventBus.$off('show-ddl-settings-popup', this.showDdlSettings);
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