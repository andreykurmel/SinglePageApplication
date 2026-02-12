<template>
    <div class="popup-wrapper" v-show="show_this" @click.self="hide()" :style="{zIndex: zIdx}">
        <div class="popup" :style="getPopupStyle()">
            <div class="flex flex--col">
                <div class="popup-header">
                    <div class="drag-bkg" draggable="true" @dragstart="dragPopSt()" @drag="dragPopup()"></div>
                    <span>Referencing Conditions (RCs)</span>
                    <span class="glyphicon glyphicon-remove pull-right header-btn" @click="hide()"></span>
                </div>
                <div class="flex__elem-remain popup-content">
                    <div class="flex__elem__inner popup-main" :style="$root.themeMainBgStyle">
                        <div class="full-frame">
                            <tab-settings-ref-conditions
                                v-if="show_this"
                                :table-meta="tableMeta"
                                :settings-meta="$root.settingsMeta"
                                :user="user"
                                :table_id="table_id"
                                :ext-ref-group="extRefGroup"
                            ></tab-settings-ref-conditions>
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

    import TabSettingsRefConditions from "../MainApp/Object/Table/SettingsModule/TabSettingsRefConditions";

    export default {
        name: "RefConditionsPopup",
        mixins: [
            PopupAnimationMixin,
        ],
        components: {
            TabSettingsRefConditions,
        },
        data: function () {
            return {
                show_this: false,
                extRefGroup: -1,
                //PopupAnimationMixin
                getPopupWidth: 1200,
                idx: 0,
            }
        },
        props:{
            tableMeta: Object,
            table_id: Number|null,
            user:  Object
        },
        methods: {
            hide() {
                this.show_this = false;
                this.$root.tablesZidxDecrease();
                eventBus.$emit('ref-conditions-popup-closed');
            },
            showRefConditionsPopupHandler(db_name, refId) {
                if (!db_name || db_name === this.tableMeta.db_name) {
                    this.extRefGroup = _.findIndex(this.tableMeta._ref_conditions, {id: Number(refId)});
                    this.show_this = true;
                    this.$root.tablesZidxIncrease();
                    this.zIdx = this.$root.tablesZidx;
                    this.runAnimation();
                }
            }
        },
        mounted() {
            eventBus.$on('global-keydown', this.hideMenu);
            eventBus.$on('show-ref-conditions-popup', this.showRefConditionsPopupHandler);
        },
        beforeDestroy() {
            eventBus.$off('global-keydown', this.hideMenu);
            eventBus.$off('show-ref-conditions-popup', this.showRefConditionsPopupHandler);
        }
    }
</script>

<style lang="scss" scoped>
    @import "CustomEditPopUp";

    .popup-wrapper {

        .popup {
            position: relative;

            .popup-content {

                .popup-main {
                    padding: 0px;
                }
                .section-text {
                    padding: 5px 10px;
                    font-size: 16px;
                    font-weight: bold;
                    background-color: #CCC;
                }
                .section-text:not(:first-child) {
                    margin-top: 5px;
                }

            }

            .right-elem {
                button {
                    padding: 0 6px;
                }
                select {
                    padding: 0;
                }
            }
        }
    }
</style>