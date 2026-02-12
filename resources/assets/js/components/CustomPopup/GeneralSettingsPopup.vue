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
                            :tb_meta="tmp_meta"
                            :board_settings="tmp_meta"
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
                tmp_meta: {
                    board_view_height: 0,
                    board_title_width: 0,
                    board_image_width: 0,
                    board_image_height: 0,
                    board_image_fld_id: 0,
                    board_display_position: '',
                    board_display_view: '',
                    board_display_fit: '',
                },
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
                this.$root.tablesZidxDecrease();
            },
            showGeneralSettings() {
                this.tmp_meta.board_view_height = this.tableMeta.board_view_height;
                this.tmp_meta.board_title_width = this.tableMeta.board_title_width;
                this.tmp_meta.board_image_width = this.tableMeta.board_image_width;
                this.tmp_meta.board_image_height = this.tableMeta.board_image_height;
                this.tmp_meta.board_image_fld_id = this.tableMeta.board_image_fld_id;
                this.tmp_meta.board_display_position = this.tableMeta.board_display_position;
                this.tmp_meta.board_display_view = this.tableMeta.board_display_view;
                this.tmp_meta.board_display_fit = this.tableMeta.board_display_fit;

                this.show_popup = true;
                this.$root.tablesZidxIncrease();
                this.zIdx = this.$root.tablesZidx;
                this.runAnimation();
            },
            setVals(prop_name, val) {
                //
            },
            updateVals() {
                this.tableMeta.board_view_height = this.tmp_meta.board_view_height;
                this.tableMeta.board_image_width = this.tmp_meta.board_image_width;
                this.tableMeta.board_image_height = this.tmp_meta.board_image_height;
                this.tableMeta.board_image_fld_id = this.tmp_meta.board_image_fld_id;
                this.tableMeta.board_display_position = this.tmp_meta.board_display_position;
                this.tableMeta.board_display_view = this.tmp_meta.board_display_view;
                this.tableMeta.board_display_fit = this.tmp_meta.board_display_fit;

                this.$root.sm_msg_type = 1;
                let data = Object.assign({ table_id: this.tableMeta.id, }, this.tableMeta);
                axios.put('/ajax/table', data).catch(errors => {
                    Swal('Info', getErrors(errors));
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
            height: 230px;
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