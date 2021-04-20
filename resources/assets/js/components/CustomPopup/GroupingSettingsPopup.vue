<template>
    <div class="popup-wrapper" v-if="tableMeta && show_popup" @click.self="hide()" :style="{zIndex: zIdx}">
        <div class="popup" :style="getPopupStyle()">
            <div class="flex flex--col">
                <div class="popup-header">
                    <div class="drag-bkg" draggable="true" @dragstart="dragPopSt()" @drag="dragPopup()"></div>
                    <span>Defining Groups</span>
                    <span class="glyphicon glyphicon-remove pull-right header-btn" @click="hide()"></span>
                </div>
                <div class="flex__elem-remain popup-content">
                    <div class="flex__elem__inner popup-main" :style="$root.themeMainBgStyle">

                        <div class="full-frame">
                            <table-grouping-settings
                                    :table-meta="tableMeta"
                                    :settings-meta="$root.settingsMeta"
                                    :cell-height="$root.cellHeight"
                                    :max-cell-rows="$root.maxCellRows"
                                    :user="user"
                                    :table_id="tableMeta.id"
                                    :is_popup_type="show_popup"
                                    :foreign_sel_id="sel_id"
                            ></table-grouping-settings>
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

    import TableGroupingSettings from "../MainApp/Object/Table/SettingsModule/TableGroupingSettings";

    export default {
        name: "GroupingSettingsPopup",
        mixins: [
            PopupAnimationMixin,
        ],
        components: {
            TableGroupingSettings,
        },
        data: function () {
            return {
                show_popup: null, //['col', 'row']
                sel_id: null,
                //PopupAnimationMixin
                getPopupWidth: 1000,
                idx: 0,
            }
        },
        props:{
            tableMeta: Object,
            user:  Object,
        },
        methods: {
            hide() {
                this.show_popup = null;
                this.$root.tablesZidx -= 10;
            },
            showGroupingSettings(db_name, type, sel_id) {
                if (!db_name || db_name === this.tableMeta.db_name) {
                    this.show_popup = type;
                    this.sel_id = sel_id;
                    this.$root.tablesZidx += 10;
                    this.zIdx = this.$root.tablesZidx;
                    this.runAnimation();
                }
            }
        },
        mounted() {
            eventBus.$on('global-keydown', this.hideMenu);
            eventBus.$on('show-grouping-settings-popup', this.showGroupingSettings);
        },
        beforeDestroy() {
            eventBus.$off('global-keydown', this.hideMenu);
            eventBus.$off('show-grouping-settings-popup', this.showGroupingSettings);
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