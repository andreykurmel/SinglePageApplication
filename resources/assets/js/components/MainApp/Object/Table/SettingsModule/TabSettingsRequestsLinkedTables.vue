<template>
    <div class="flex flex--col">
        <custom-table
            :cell_component_name="'custom-cell-settings-dcr'"
            :global-meta="tableMeta"
            :table-meta="$root.settingsMeta['dcr_linked_tables']"
            :all-rows="dcrObject._dcr_linked_tables"
            :rows-count="dcrObject._dcr_linked_tables.length"
            :cell-height="1"
            :max-cell-rows="0"
            :is-full-width="true"
            :with_edit="tableMeta._is_owner"
            :adding-row="addingRow"
            :user="$root.user"
            :behavior="'dcr_linked_tb'"
            :available-columns="['is_active','linked_table_id','linked_permission_id']"
            :forbidden-columns="$root.systemFields"
            style="height: 40%"
            @added-row="addLinkedDcr"
            @updated-row="updateLinkedDcr"
            @delete-row="deleteLinkedDcr"
            @row-index-clicked="linkedDcrClickedGroup"
        ></custom-table>
        <div class="full-frame" style="height: 60%" :style="textSysStyle">
            <div style="margin-top: 18px; font-weight: bold;">
                <label>More details for embedding linked table: {{ linTb ? linTb.name : '' }}</label>
            </div>
            <template v-if="dcrlinkedRow">
                <div class="flex flex--center-v no-wrap">
                    <label class="no-margin">Select RC in this table for passing field values:</label>
                    <select-block
                        :options="linkRefConds"
                        :sel_value="dcrlinkedRow.passed_ref_cond_id"
                        :with_links="true"
                        :button_txt="'Add New'"
                        @option-select="(opt) => { updateSelect('passed_ref_cond_id', opt) }"
                        @link-click="showRCP(dcrlinkedRow.passed_ref_cond_id)"
                        @button-click="showRCP(null)"
                    ></select-block>
                </div>
                <div>
                    <label>Display:</label>
                </div>
                <vertical-table
                    :td="'custom-cell-settings-dcr'"
                    :global-meta="tableMeta"
                    :table-meta="$root.settingsMeta['dcr_linked_tables']"
                    :settings-meta="$root.settingsMeta"
                    :table-row="dcrlinkedRow"
                    :user="$root.user"
                    :cell-height="1"
                    :max-cell-rows="0"
                    :behavior="'dcr_linked_tb'"
                    :available-columns="['header','position_field_id','position','style']"
                    :forbidden-columns="$root.systemFields"
                    @updated-cell="updateLinkedDcr"
                ></vertical-table>
            </template>
        </div>
    </div>
</template>

<script>
import {eventBus} from '../../../../../app';

import CustomTable from '../../../../CustomTable/CustomTable';
import VerticalTable from "../../../../CustomTable/VerticalTable";
import SelectBlock from "../../../../CommonBlocks/SelectBlock";

import CellStyleMixin from "../../../../_Mixins/CellStyleMixin";

export default {
        name: "TabSettingsRequestsLinkedTables",
        components: {
            SelectBlock,
            VerticalTable,
            CustomTable,
        },
        mixins: [
            CellStyleMixin,
        ],
        data: function () {
            return {
                linkedIdx: -1,
                addingRow: {
                    active: true,
                    position: 'bottom',
                },
            }
        },
        props:{
            tableMeta: Object,
            dcrObject: Object,
        },
        computed: {
            dcrlinkedRow() {
                return this.dcrObject._dcr_linked_tables[this.linkedIdx];
            },
            linTb() {
                let row = this.dcrlinkedRow || {};
                return _.find(this.$root.settingsMeta.available_tables, {id: Number(row.linked_table_id)});
            },
            linkRefConds() {
                let data = this.dcrlinkedRow
                    ? _.filter(this.tableMeta._ref_conditions, (rc) => {
                        return rc.ref_table_id == this.dcrlinkedRow.linked_table_id;
                    })
                    : [];
                return _.map(data, (rc) => {
                    return {show: rc.name, val: rc.id};
                });
            },
        },
        methods: {
            linkedDcrClickedGroup(index) {
                this.linkedIdx = index;
            },

            //Linked Tables for DCR
            addLinkedDcr(tableRow) {
                this.$root.sm_msg_type = 1;

                let fields = _.cloneDeep(tableRow);//copy object
                this.$root.deleteSystemFields(fields);

                let frst = _.first(_.filter(this.tableMeta._fields, (fld) => {
                    return this.$root.systemFields.indexOf(fld.field) === -1;
                }));
                fields.position_field_id = frst ? frst.id : null;

                axios.post('/ajax/table-data-request/linked-table', {
                    table_dcr_id: this.dcrObject.id,
                    fields: fields,
                }).then(({ data }) => {
                    this.dcrObject._dcr_linked_tables.push(data);
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
            updateLinkedDcr(tableRow) {
                this.$root.sm_msg_type = 1;

                let fields = _.cloneDeep(tableRow);//copy object
                this.$root.deleteSystemFields(fields);

                axios.put('/ajax/table-data-request/linked-table', {
                    table_linked_id: tableRow.id,
                    fields: fields
                }).then(({ data }) => {
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
            deleteLinkedDcr(tableRow) {
                this.$root.sm_msg_type = 1;
                axios.delete('/ajax/table-data-request/linked-table', {
                    params: {
                        table_linked_id: tableRow.id
                    }
                }).then(({ data }) => {
                    let idx = _.findIndex(this.dcrObject._dcr_linked_tables, {id: Number(tableRow.id)});
                    if (idx > -1) {
                        this.dcrObject._dcr_linked_tables.splice(idx, 1);
                    }
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
            updateSelect(key, option) {
                this.dcrlinkedRow[key] = option.val;
                this.updateLinkedDcr(this.dcrlinkedRow);
            },

            //eventbus
            showRCP(id) {
                eventBus.$emit('show-ref-conditions-popup', this.tableMeta.db_name, id);
            },
        },
        mounted() {
            if (this.dcrObject._dcr_linked_tables.length) {
                this.linkedDcrClickedGroup(0);
            }
        },
        beforeDestroy() {
        }
    }
</script>

<style lang="scss" scoped>
    @import "./TabSettingsPermissions";
</style>