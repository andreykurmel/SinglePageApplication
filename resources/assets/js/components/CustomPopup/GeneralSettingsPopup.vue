<template>
    <div class="popup-wrapper" v-if="tableMeta && show_popup" @click.self="hide()" :style="{zIndex: zIdx}">
        <div class="popup">
            <div class="flex flex--col">
                <div class="popup-header">
                    <span>General Settings for Board Display</span>
                    <span class="glyphicon glyphicon-remove pull-right header-btn" @click="hide()"></span>
                </div>
                <div class="flex__elem-remain popup-content" style="padding: 20px;">
                    <board-setting-block
                            :board_view_height="tmp_view_height"
                            :board_image_width="tmp_image_width"
                            @val-changed="setVals"
                    ></board-setting-block>
                    <div class="popup-buttons" style="float: right;padding-top: 15px;">
                        <button class="btn btn-success btn-sm" :style="$root.themeButtonStyle" @click="updateVals">Update</button>
                        <button class="btn btn-default btn-sm" @click="hide()">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import {eventBus} from '../../app';

    import PopupAnimationMixin from './../_Mixins/PopupAnimationMixin';

    import BoardSettingBlock from "../CommonBlocks/BoardSettingBlock";

    export default {
        name: "GeneralSettingsPopup",
        mixins: [
            PopupAnimationMixin,
        ],
        components: {
            BoardSettingBlock,
        },
        data: function () {
            return {
                show_popup: false,
                tmp_view_height: 0,
                tmp_image_width: 0,
                //PopupAnimationMixin
                getPopupWidth: 750,
                idx: 0,
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
            showGeneralSettings() {
                this.tmp_view_height = this.tableMeta.board_view_height;
                this.tmp_image_width = this.tableMeta.board_image_width;
                this.show_popup = true;
                this.$root.tablesZidx += 10;
                this.zIdx = this.$root.tablesZidx;
                this.runAnimation();
            },
            setVals(prop_name, val) {
                if (prop_name === 'board_view_height') {
                    this.tmp_view_height = val;
                }
                if (prop_name === 'board_image_width') {
                    this.tmp_image_width = val;
                }
            },
            updateVals() {
                this.tableMeta.board_view_height = this.tmp_view_height;
                this.tableMeta.board_image_width = this.tmp_image_width;

                this.$root.sm_msg_type = 1;
                let data = Object.assign({ table_id: this.tableMeta.id, }, this.tableMeta);
                axios.put('/ajax/table', data).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                    this.hide();
                });
            },
        },
        mounted() {
            eventBus.$on('global-keydown', this.hideMenu);
            eventBus.$on('show-general-settings-popup', this.showGeneralSettings);
        },
        beforeDestroy() {
            eventBus.$off('global-keydown', this.hideMenu);
            eventBus.$off('show-general-settings-popup', this.showGeneralSettings);
        }
    }
</script>

<style lang="scss" scoped>
    @import "CustomEditPopUp";

    .popup-wrapper {
        .popup {
            height: 185px;
            width: 550px;
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