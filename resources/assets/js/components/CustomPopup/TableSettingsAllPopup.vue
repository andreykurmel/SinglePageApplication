<template>
    <div class="popup-wrapper" v-if="tableMeta && is_vis" @click.self="hide()" :style="{zIndex: zIdx}">
        <div class="popup" :style="getPopupStyle()">
            <div class="flex flex--col">
                <div class="popup-header">
                    <div class="drag-bkg" draggable="true" @dragstart="dragPopSt()" @drag="dragPopup()"></div>
                    <span>Table Settings</span>
                    <span class="glyphicon glyphicon-remove pull-right header-btn" @click="hide()"></span>
                </div>
                <div class="flex__elem-remain popup-content">
                    <div class="flex__elem__inner popup-main" :style="$root.themeMainBgStyle">

                        <div class="full-frame" style="background-color: inherit;">
                            <tab-settings
                                    :table-meta="tableMeta"
                                    :settings-meta="settingsMeta"
                                    :user="user"
                                    :table_id="tableMeta.id"
                                    :is-visible="is_vis"
                                    :ava-tabs="avaTabs"
                                    :basics_init_tab="init_tab"
                                    :basics_filter_for_field="filter_for"
                                    :ref_conds_filter="ref_conds_filter"
                                    :links_filter="links_filter"
                            ></tab-settings>
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

    import TableSettingsBasics from "../MainApp/Object/Table/SettingsModule/TableSettingsBasics";
    import TabSettings from "../MainApp/Object/Table/SettingsModule/TabSettings.vue";

    export default {
        name: "TableSettingsAllPopup",
        mixins: [
            PopupAnimationMixin,
        ],
        components: {
            TabSettings,
            TableSettingsBasics,
        },
        data: function () {
            return {
                filter_for: '',
                is_vis: false,
                init_tab: '',
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
            user: Object,
            uid: String,
            avaTabs: Array,
            ref_conds_filter: Number,
            links_filter: Number,
        },
        methods: {
            hide() {
                this.is_vis = false;
                this.$root.tablesZidxDecrease();
                this.$emit('settings-closed');
                eventBus.$emit('table-settings-all-popup__closed');
            },
            showSettings(object) {
                if (!object.uid || object.uid === this.uid) {
                    this.filter_for = object.filter;
                    this.init_tab = object.tab;
                    this.is_vis = true;
                    this.$root.tablesZidxIncrease();
                    this.zIdx = this.$root.tablesZidx + (this.uid ? 300 : 0);
                    this.runAnimation();
                }
            }
        },
        mounted() {
            eventBus.$on('global-keydown', this.hideMenu);
            eventBus.$on('show-table-settings-all-popup', this.showSettings);
        },
        beforeDestroy() {
            eventBus.$off('global-keydown', this.hideMenu);
            eventBus.$off('show-table-settings-all-popup', this.showSettings);
        }
    }
</script>

<style lang="scss" scoped>
    @import "CustomEditPopUp";

    .popup-wrapper {
        .popup {
            width: 1200px;
            position: relative;
            margin: 3% auto;
            transform: initial;
            top: initial;
            left: initial;

            .popup-main {
                padding: 0;
            }
        }
    }
</style>