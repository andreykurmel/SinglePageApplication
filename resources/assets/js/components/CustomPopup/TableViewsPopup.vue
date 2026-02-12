<template>
    <div class="popup-wrapper" v-show="show_this" @click.self="hide()" :style="{zIndex: zIdx}">
        <div class="popup" :style="getPopupStyle()">
            <div class="flex flex--col">
                <div class="popup-header">
                    <div class="drag-bkg" draggable="true" @dragstart="dragPopSt()" @drag="dragPopup()"></div>
                    <div class="flex">
                        <div class="flex__elem-remain">Views</div>
                        <div class="flasher--ctr" :style="{opacity: flash_show ? 1 : 0}">{{ flash_msg }}</div>
                        <div class="" style="position: relative">
                            <span class="glyphicon glyphicon-remove pull-right header-btn" @click="hide()"></span>
                            <div style="position: absolute; right: 25px; z-index: 100;">
                                <info-sign-link v-if="$root.settingsMeta.is_loaded"
                                                :app_sett_key="'help_link_views_pop'"
                                                :hgt="24"
                                                :txt="'for Views'"
                                ></info-sign-link>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex__elem-remain popup-content">
                    <div class="flex__elem__inner permissions-tab" style="background: #FFF;">
                        <div v-if="isLimited === 'MRV'" class="permissions-panel full-height">
                            <table-view-module
                                :table-meta="tableMeta"
                                :no-grid-view-relation="true"
                                @flash-msg="(msg, show) => { flash_msg = msg; flash_show = show; }"
                            ></table-view-module>
                        </div>
                        <div v-else class="permissions-panel full-height">
                            <div class="permissions-menu-header" style="padding-left: 5px">
                                <button class="btn btn-default btn-sm" :style="textSysStyle" :class="{active : activeRightTab === 'multiple'}" @click="activeRightTab = 'multiple'">
                                    Multiple-Record Views (MRV)
                                </button>
                                <button class="btn btn-default btn-sm" :style="textSysStyle" :class="{active : activeRightTab === 'single'}" @click="activeRightTab = 'single'">
                                    Single-Record View (SRV)
                                </button>
                            </div>
                            <div class="permissions-menu-body">
                                <div v-show="activeRightTab === 'multiple'" class="full-frame">
                                    <table-view-module
                                            :table-meta="tableMeta"
                                            @flash-msg="(msg, show) => { flash_msg = msg; flash_show = show; }"
                                    ></table-view-module>
                                </div>
                                <div v-show="activeRightTab === 'single'" class="full-frame">
                                    <single-view-module
                                            :table-meta="tableMeta"
                                            @flash-msg="(msg, show) => { flash_msg = msg; flash_show = show; }"
                                    ></single-view-module>
                                </div>
                            </div>
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
    import CellStyleMixin from "../_Mixins/CellStyleMixin";

    import EmbedButton from '../Buttons/EmbedButton';
    import CustomTable from "../CustomTable/CustomTable";
    import TableViewModule from "../MainApp/Object/Table/SettingsModule/TableViewModule";
    import SingleViewModule from "../MainApp/Object/Table/SettingsModule/SingleViewModule";
    import InfoSignLink from "../CustomTable/Specials/InfoSignLink.vue";
    import PermissionsSettingsPopup from "./PermissionsSettingsPopup.vue";

    export default {
        name: "TableViewsPopup",
        mixins: [
            PopupAnimationMixin,
            CellStyleMixin,
        ],
        components: {
            PermissionsSettingsPopup,
            InfoSignLink,
            SingleViewModule,
            TableViewModule,
            CustomTable,
            EmbedButton,
        },
        data: function () {
            return {
                show_this: false,
                activeRightTab: 'multiple',

                flash_msg: '',
                flash_show: false,
                //PopupAnimationMixin
                getPopupWidth: 1300,
                idx: 0,
            }
        },
        props:{
            tableMeta: Object,
            isLimited: String,
            init_show: Boolean,
        },
        methods: {
            //additionals
            hide() {
                this.show_this = false;
                this.$root.tablesZidxDecrease();
                this.$emit('popup-close');
            },
            showTableViewsPopupHandler(db_name, right_tab) {
                if (!db_name || db_name === this.tableMeta.db_name) {
                    if (right_tab) {
                        this.activeRightTab = right_tab;
                    }
                    this.show_this = true;
                    this.$root.tablesZidxIncrease();
                    this.zIdx = this.$root.tablesZidx;
                    this.runAnimation();
                }
            },
        },
        mounted() {
            if (this.init_show) {
                this.showTableViewsPopupHandler(this.tableMeta.db_name);
            }

            eventBus.$on('global-keydown', this.hideMenu);
            eventBus.$on('show-table-views-popup', this.showTableViewsPopupHandler);
        },
        beforeDestroy() {
            eventBus.$off('global-keydown', this.hideMenu);
            eventBus.$off('show-table-views-popup', this.showTableViewsPopupHandler);
        }
    }
</script>

<style lang="scss" scoped>
    @import "./CustomEditPopUp";
    @import "./../CustomTable/CustomTable";
    @import "./../MainApp/Object/Table/SettingsModule/TabSettingsPermissions";

    .flasher--ctr {
        position: absolute;
        left: 50%;
        transform: translateX(-50%);
        transition: all 0.75s;
    }

    .permissions-tab {
        .permissions-panel {
            background-color: inherit;
        }
        .permissions-panel {
            .permissions-menu-body {
                background-color: inherit;
                border: 2px solid #CCC;
                left: 5px;
                right: 5px;
                bottom: 5px;
                div {
                    border: none;
                }
            }
        }
    }

    .btn-default {
        height: 30px;
    }
</style>