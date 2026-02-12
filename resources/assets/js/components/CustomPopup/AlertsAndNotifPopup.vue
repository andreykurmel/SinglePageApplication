<template>
    <div class="popup-wrapper" @click.self="$emit('popup-close')">
        <div class="popup" :style="getPopupStyle()">
            <div class="flex flex--col">
                <div class="popup-header">
                    <div class="drag-bkg" draggable="true" @dragstart="dragPopSt()" @drag="dragPopup()"></div>
                    <div class="flex">
                        <div class="flex__elem-remain">
                            <span>Alerts, Notifications & Automations (ANA)</span><br>
                        </div>
                        <div class="" style="position: relative">
                            <span class="glyphicon glyphicon-remove pull-right header-btn" @click="$emit('popup-close', false)"></span>
                        </div>
                    </div>
                </div>
                <div class="popup-content flex__elem-remain">
                    <div class="flex__elem__inner">
                        <div class="flex full-height">

                            <div class="flex__elem-remain table-container">
                            </div>
                            <div class="info-container">
                                <tab-alert-and-notif
                                        :table_id="tableMeta.id"
                                        :table-meta="tableMeta"
                                        :user="$root.user"
                                ></tab-alert-and-notif>
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

    import PopupAnimationMixin from '../_Mixins/PopupAnimationMixin';

    import TabAlertAndNotif from "../MainApp/Object/Table/TabAlertAndNotif";

    export default {
        name: "AlertsAndNotifPopup",
        components: {
            TabAlertAndNotif,
        },
        mixins: [
            PopupAnimationMixin,
        ],
        data: function () {
            return {
                menu_opened: false,
                //PopupAnimationMixin
                getPopupWidth: 810,
                idx: 0,
            }
        },
        props:{
            tableMeta: Object,
            settingsMeta: Object,
            user:  Object,
        },
        methods: {
            hideMenu(e) {
                if (this.is_vis && e.keyCode === 27 && !this.$root.e__used) {
                    this.$emit('popup-close');
                    this.$root.set_e__used(this);
                }
            },
        },
        created() {
            this.runAnimation();
            eventBus.$on('global-keydown', this.hideMenu);
        },
        beforeDestroy() {
            eventBus.$off('global-keydown', this.hideMenu);
        }
    }
</script>

<style lang="scss" scoped>
    @import "CustomEditPopUp";

    .table-container {
        height: 100%;
        padding: 5px;
        overflow: auto;
        border-right: 2px solid #AAA;
    }
    .info-container {
        width: 310px;
        height: 100%;
        padding: 5px;
        overflow: auto;
    }
</style>