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
                        </div>
                    </div>
                </div>
                <div class="flex__elem-remain popup-content">
                    <div class="flex__elem__inner permissions-tab" :style="$root.themeMainBgStyle">
                        <div class="permissions-panel full-height">
                            <div class="permissions-menu-header">
                                <button class="btn btn-default btn-sm" :style="textSysStyle" :class="{active : activeRightTab === 'multiple'}" @click="activeRightTab = 'multiple'">
                                    Multiple-Record Views (MRV)
                                </button>
                                <button class="btn btn-default btn-sm":style="textSysStyle" :class="{active : activeRightTab === 'single'}" @click="activeRightTab = 'single'">
                                    Single-Record View (SRV)
                                </button>
                            </div>
                            <div class="permissions-menu-body">
                                <div v-if="activeRightTab === 'multiple'" class="full-frame" style="background-color: #047">
                                    <table-view-module
                                            :table-meta="tableMeta"
                                            @flash-msg="(msg, show) => { flash_msg = msg; flash_show = show; }"
                                    ></table-view-module>
                                </div>
                                <div v-if="activeRightTab === 'single'" class="full-frame" style="background-color: #FFF">
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

    export default {
        name: "TableViewsPopup",
        mixins: [
            PopupAnimationMixin,
            CellStyleMixin,
        ],
        components: {
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
        },
        methods: {
            //additionals
            hide() {
                this.show_this = false;
                this.$root.tablesZidx -= 10;
                this.$emit('popup-close');
            },
            showTableViewsPopupHandler(db_name) {
                if (!db_name || db_name === this.tableMeta.db_name) {
                    this.show_this = true;
                    this.$root.tablesZidx += 10;
                    this.zIdx = this.$root.tablesZidx;
                    this.runAnimation();
                }
            },
        },
        created() {
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