<template>
    <div class="popup-wrapper" v-if="tableMeta && show_popup" @click.self="hide()" :style="{zIndex: zIdx}">
        <div class="popup" :style="getPopupStyle()">
            <div class="flex flex--col">
                <div class="popup-header">
                    <div class="drag-bkg" draggable="true" @dragstart="dragPopSt()" @drag="dragPopup()"></div>
                    <span>Define Display Links</span>
                    <span class="glyphicon glyphicon-remove pull-right header-btn" @click="hide()"></span>
                </div>
                <div class="flex__elem-remain popup-content">
                    <div class="flex__elem__inner popup-main" :style="$root.themeMainBgStyle">

                        <div class="full-frame">
                            <table-settings-display-links
                                    :table-meta="tableMeta"
                                    :settings-meta="$root.settingsMeta"
                                    :user="user"
                                    :table_id="tableMeta.id"
                                    :foreign_sel_fld_id="sel_fld_id"
                                    :foreign_sel_id="sel_id"
                            ></table-settings-display-links>
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

    import TableSettingsDisplayLinks from "../MainApp/Object/Table/SettingsModule/TableSettingsDisplayLinks";

    export default {
        name: "DisplayLinkSettingsPopup",
        mixins: [
            PopupAnimationMixin,
        ],
        components: {
            TableSettingsDisplayLinks,
        },
        data: function () {
            return {
                show_popup: false,
                sel_fld_id: null,
                sel_id: null,
                //PopupAnimationMixin
                getPopupWidth: 1000,
                idx: 0,
            }
        },
        props:{
            tableMeta: Object,
            user: Object,
        },
        methods: {
            hide() {
                this.show_popup = false;
                this.$root.tablesZidx -= 10;
            },
            showGroupingSettings(table_field_id, sel_id) {
                this.show_popup = true;
                this.sel_fld_id = table_field_id;
                this.sel_id = sel_id;
                this.$root.tablesZidx += 10;
                this.zIdx = this.$root.tablesZidx;
                this.runAnimation();
            }
        },
        mounted() {
            eventBus.$on('global-keydown', this.hideMenu);
            eventBus.$on('show-display-links-settings-popup', this.showGroupingSettings);
        },
        beforeDestroy() {
            eventBus.$off('global-keydown', this.hideMenu);
            eventBus.$off('show-display-links-settings-popup', this.showGroupingSettings);
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