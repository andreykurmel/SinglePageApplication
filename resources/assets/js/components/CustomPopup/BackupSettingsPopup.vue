<template>
    <div class="popup-wrapper" v-if="tableMeta && show_popup" @click.self="hide()" :style="{zIndex: zIdx}">
        <div class="popup" :style="getPopupStyle()">
            <div class="flex flex--col">
                <div class="popup-header">
                    <div class="drag-bkg" draggable="true" @dragstart="dragPopSt()" @drag="dragPopup()"></div>
                    <span>Storage & Backups Notifications - Email</span>
                    <span class="glyphicon glyphicon-remove pull-right header-btn" @click="hide()"></span>
                </div>
                <div class="flex__elem-remain popup-content">
                    <div class="flex__elem__inner popup-main">
                        <div class="full-frame">
                            <backup-add-settings
                                    :table-meta="tableMeta"
                                    :tb-backup="tbBackup"
                            ></backup-add-settings>
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

    import BackupAddSettings from "../MainApp/Object/Table/BackupAddSettings";

    export default {
        name: "BackupSettingsPopup",
        mixins: [
            PopupAnimationMixin,
        ],
        components: {
            BackupAddSettings,
        },
        data: function () {
            return {
                show_popup: false,
                tbBackup: null,
                //PopupAnimationMixin
                getPopupWidth: 700,
                getPopupHeight: '400px',
                idx: 0,
                //
            }
        },
        props:{
            tableMeta: Object,
        },
        methods: {
            hide() {
                this.show_popup = false;
                this.$root.tablesZidx -= 10;
            },
            showBackupSettings(row_id) {
                this.tbBackup = _.find(this.tableMeta._backups, {id: Number(row_id)});
                this.show_popup = true;
                this.$root.tablesZidx += 10;
                this.zIdx = this.$root.tablesZidx;
                this.runAnimation();
            },
        },
        mounted() {
            eventBus.$on('global-keydown', this.hideMenu);
            eventBus.$on('show-backup-settings-popup', this.showBackupSettings);
        },
        beforeDestroy() {
            eventBus.$off('global-keydown', this.hideMenu);
            eventBus.$off('show-backup-settings-popup', this.showBackupSettings);
        }
    }
</script>

<style lang="scss" scoped>
    @import "CustomEditPopUp";
    @import "../MainApp/Object/Table/SettingsModule/ReqRowStyle";

    .popup-wrapper {
        .popup {
            position: relative;

            .popup-main {
                padding: 15px 15px 15px 20px;
            }
        }
    }
</style>