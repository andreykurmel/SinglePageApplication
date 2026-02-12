<template>
    <div class="popup-wrapper" @click.self="$emit('popup-close')">
        <div :style="getPopupStyle()" class="popup">
            <div class="flex flex--col">
                <div class="popup-header">
                    <div class="drag-bkg" draggable="true" @drag="dragPopup()" @dragstart="dragPopSt()"></div>
                    <div class="flex">
                        <div class="flex__elem-remain">Field display for linked records.</div>
                        <div class="" style="position: relative">
                            <span class="glyphicon glyphicon-remove pull-right header-btn"
                                  @click="$emit('popup-close', false)"></span>
                        </div>
                    </div>
                </div>
                <div class="popup-content flex__elem-remain">
                    <div class="flex__elem__inner popup-main">
                        <div class="popup-overflow">
                            <custom-table
                                v-if="tableMeta"
                                :all-rows="colRows"
                                :behavior="'table_field_link_columns'"
                                :cell-height="1"
                                :cell_component_name="'custom-cell-table-data'"
                                :global-meta="tableMeta"
                                :headers-with-check="['in_popup_display','in_inline_display']"
                                :is-full-width="true"
                                :max-cell-rows="$root.maxCellRows"
                                :rows-count="colRows.length"
                                :special_extras="{
                                        header_check: 'slider',
                                        no_row_menu: true,
                                    }"
                                :table-meta="colMeta"
                                :user="$root.user"
                                @updated-row="toggleLinkCol"
                                @check-row="massToggle"
                                @show-header-settings="syncWithListView"
                            ></custom-table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import CustomTable from './../CustomTable/CustomTable';

import PopupAnimationMixin from './../_Mixins/PopupAnimationMixin';

export default {
    name: "FieldLinkColumnsPopUp",
    mixins: [
        PopupAnimationMixin,
    ],
    components: {
        CustomTable
    },
    data: function () {
        return {
            tableMeta: null,
            colMeta: {
                id: null,
                name: 'Link Columns',
                _is_owner: true,
                _fields: [
                    {
                        id: null,
                        name: 'Field',
                        field: 'name',
                        is_showed: true,
                        width: 200,
                        input_type: 'Mirror',
                        f_type: 'String'
                    },
                    {
                        id: null,
                        name: 'Status,Pop-up',
                        field: 'in_popup_display',
                        is_showed: true,
                        width: 100,
                        f_type: 'Boolean',
                        f_format: 'Slider',
                        input_type: 'Input'
                    },
                    {
                        id: null,
                        name: 'Status,In-line',
                        field: 'in_inline_display',
                        is_showed: true,
                        width: 100,
                        f_type: 'Boolean',
                        f_format: 'Slider',
                        input_type: 'Input'
                    },
                ],
            },
            colRows: [],
            //PopupAnimationMixin
            getPopupWidth: 500,
            idx: 0,
        };
    },
    props: {
        linkRow: Object,
    },
    methods: {
        prepareDatas() {
            this.tableMeta = null;
            let rootTableMeta = this.$root.tableMeta;
            let rc = rootTableMeta
                ? _.find(rootTableMeta._ref_conditions || [], {id: Number(this.linkRow.table_ref_condition_id)})
                : null;
            let refTb = _.find(this.$root.settingsMeta.available_tables, {id: Number(rc ? rc.ref_table_id : 0)});

            if (rootTableMeta && refTb && rootTableMeta.id == refTb.id) {
                this.createRows(rootTableMeta);
            } else {
                this.getLinkTbWithPermis();
            }
        },
        getLinkTbWithPermis() {
            axios.post('/ajax/table-data/get-headers', {
                ref_cond_id: this.linkRow.table_ref_condition_id,
                user_id: !this.$root.user.see_view ? this.$root.user.id : null,
                special_params: {view_hash: this.$root.user.view_hash, is_folder_view: this.$root.user._is_folder_view},
            }).then(({data}) => {
                this.createRows(data);
            }).catch(errors => {
                Swal('Info', getErrors(errors));
            });
        },
        createRows(refTb) {
            this.tableMeta = refTb;
            let fields = refTb ? refTb._fields : [];
            this.colRows = _.map(fields, (fld) => {
                let lnkCol = _.find(this.linkRow._columns, {field_id: Number(fld.id)});
                return {
                    id: fld.id,
                    name: fld.name,
                    field: fld.field,
                    in_popup_display: lnkCol && lnkCol.in_popup_display ? 1 : 0,
                    in_inline_display: lnkCol && lnkCol.in_inline_display ? 1 : 0,
                };
            });
        },
        //Link Toggles
        toggleLinkCol(row) {
            if (in_array(row._changed_field, ['in_popup_display', 'in_inline_display'])) {
                $.LoadingOverlay('show');
                axios.post('/ajax/settings/data/link/column', {
                    table_link_id: this.linkRow.id,
                    field_id: row.id,
                    field_db: row.field,
                    in_popup: row.in_popup_display,
                    in_inline: row.in_inline_display,
                }).then(({data}) => {
                    this.linkRow._columns = data;
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            }
        },
        massToggle(field, status, arr) {
            _.each(this.colRows, (item) => {
                item[field] = status ? 1 : 0;
            });

            $.LoadingOverlay('show');
            axios.post('/ajax/settings/data/link/column/mass', {
                table_link_id: this.linkRow.id,
                fields_objects: this.colRows,
            }).then(({data}) => {
                this.linkRow._columns = data;
                this.prepareDatas();
            }).catch(errors => {
                Swal('Info', getErrors(errors));
            }).finally(() => {
                $.LoadingOverlay('hide');
            });
        },
        syncWithListView(field) {
            $.LoadingOverlay('show');
            axios.post('/ajax/settings/data/link/column/mass-sync', {
                table_link_id: this.linkRow.id,
                field_key: field.field,
            }).then(({data}) => {
                this.linkRow._columns = data;
                this.prepareDatas();
            }).catch(errors => {
                Swal('Info', getErrors(errors));
            }).finally(() => {
                $.LoadingOverlay('hide');
            });
        },
    },
    mounted() {
        this.runAnimation();
        this.prepareDatas();
    }
}
</script>

<style lang="scss" scoped>
@import "CustomEditPopUp";
</style>