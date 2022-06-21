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
                            <table-settings-basics
                                    :table-meta="tableMeta"
                                    :settings-meta="settingsMeta"
                                    :user="user"
                                    :table_id="tableMeta.id"
                                    :init_tab="init_tab"
                                    :is-visible="is_vis"
                                    :filter_for_field="filter_for"
                            ></table-settings-basics>
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

    export default {
        name: "TableSettingsAllPopup",
        mixins: [
            PopupAnimationMixin,
        ],
        components: {
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
        },
        methods: {
            hide() {
                this.is_vis = false;
                this.$root.tablesZidx -= 10;
                this.$emit('settings-closed');
                eventBus.$emit('table-settings-all-popup__closed');
            },
            showSettings(object) {
                if (!object.uid || object.uid === this.uid) {
                    this.filter_for = object.filter;
                    this.init_tab = object.tab;
                    this.is_vis = true;
                    this.$root.tablesZidx += 10;
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