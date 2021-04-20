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
                    <div class="flex__elem__inner popup-main" :style="$root.themeMainBgStyle">
                        <div class="full-frame">
                            <table-view-module
                                    :table-meta="tableMeta"
                                    @flash-msg="(msg, show) => { flash_msg = msg; flash_show = show; }"
                            ></table-view-module>
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

    import EmbedButton from '../Buttons/EmbedButton';
    import CustomTable from "../CustomTable/CustomTable";
    import TableViewModule from "../MainApp/Object/Table/SettingsModule/TableViewModule";

    export default {
        name: "TableViewsPopup",
        mixins: [
            PopupAnimationMixin,
        ],
        components: {
            TableViewModule,
            CustomTable,
            EmbedButton,
        },
        data: function () {
            return {
                show_this: false,

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

    .popup-main {
        padding: 0;
    }

    .flasher--ctr {
        position: absolute;
        left: 50%;
        transform: translateX(-50%);
        transition: all 0.75s;
    }
</style>