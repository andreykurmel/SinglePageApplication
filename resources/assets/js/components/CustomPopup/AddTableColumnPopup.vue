<template>
    <div class="popup-wrapper" v-if="tableMeta && show_popup" @click.self="hide()" :style="{zIndex: zIdx}">
        <div class="popup" :style="getPopupStyle()">
            <div class="flex flex--col">
                <div class="popup-header">
                    <div class="drag-bkg" draggable="true" @dragstart="dragPopSt()" @drag="dragPopup()"></div>
                    <span>Data/Field Settings</span>
                    <span class="glyphicon glyphicon-remove pull-right header-btn" @click="hide()"></span>
                </div>
                <div class="flex__elem-remain popup-content">
                    <div class="flex__elem__inner popup-main">
                        <div class="flex flex--col">
                            <div class="flex__elem-remain popup-tab">
                                <import-fields-block
                                        :table-meta="tableMeta"
                                        :selected-type="{key:'scratch'}"
                                        :table-headers="tbHdrs"
                                        :can-get-access="true"
                                        :present-source="true"
                                        :fields-columns="[]"
                                        :mysql-columns="[]"
                                ></import-fields-block>
                            </div>
                            <div class="popup-buttons">
                                <button class="btn btn-success"
                                        v-if="tableMeta._is_owner"
                                        :style="$root.themeButtonStyle"
                                        @click="saveColumns"
                                >Save</button>
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

    import {SpecialFuncs} from '../../classes/SpecialFuncs';

    import PopupAnimationMixin from './../_Mixins/PopupAnimationMixin.vue';
    import DataImportMixin from './../_Mixins/DataImportMixin.vue';

    import ImportFieldsBlock from "../CommonBlocks/ImportFieldsBlock";

    export default {
        name: "AddTableColumnPopup",
        mixins: [
            PopupAnimationMixin,
            DataImportMixin,
        ],
        components: {
            ImportFieldsBlock
        },
        data: function () {
            return {
                show_popup: false,
                hdrClone: null,
                tbHdrs: null,
                //PopupAnimationMixin
                getPopupWidth: 1000,
                idx: 0,
            }
        },
        computed: {
            presentHeadersCols() {
                return this.tableMeta._fields.length - this.$root.systemFields.length;
            },
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
                this.show_popup = true;
                this.$root.tablesZidx += 10;
                this.zIdx = this.$root.tablesZidx;
                this.runAnimation();

                this.hdrClone = null;
                this.tbHdrs = null;
                this.$nextTick(() => {
                    this.hdrClone = _.clone(this.tableMeta._fields[0]);
                    SpecialFuncs.clearObject(this.hdrClone);
                    this.tbHdrs = this.copyFrom(this.tableMeta._fields);
                });
            },
            saveColumns() {
                $.LoadingOverlay('show');
                axios.post('/ajax/import/modify-table', {
                    table_id: this.tableMeta.id,
                    columns: this.tbHdrs,
                    present_cols_idx: this.presentHeadersCols,
                    import_type: 'scratch',
                    import_action: 'append',
                    csv_settings: {filename: ''},
                }).then(({ data }) => {
                    eventBus.$emit('reload-meta-tb__fields');
                    this.hide();
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
        },
        mounted() {
            eventBus.$on('global-keydown', this.hideMenu);
            eventBus.$on('show-add-table-column-popup', this.showBackupSettings);
        },
        beforeDestroy() {
            eventBus.$off('global-keydown', this.hideMenu);
            eventBus.$off('show-add-table-column-popup', this.showBackupSettings);
        }
    }
</script>

<style lang="scss" scoped>
    @import "CustomEditPopUp";

    .popup-wrapper {
        .popup {
            position: relative;

            .popup-main {
                padding: 15px 15px 15px 20px;
            }
        }
    }
</style>