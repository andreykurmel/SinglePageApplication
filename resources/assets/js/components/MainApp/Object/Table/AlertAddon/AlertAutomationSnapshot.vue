<template>
    <div class="full-height snp-wrap" :style="$root.themeMainBgStyle">
        <div class="flex flex--center no-wrap mrg" :style="textSysStyleSmart">
            <label>Name:&nbsp;</label>
            <div class="full-width relative">
                <input type="text"
                       :style="textSysStyle"
                       v-model="alert_sett.snp_name"
                       :disabled="!can_edit"
                       @keyup="recreaFormula('snp_name_formula')"
                       @focus="snp_name_formula = true"
                       class="form-control h-snp"/>
                <formula-helper
                        v-if="snp_name_formula"
                        :user="$root.user"
                        :table-meta="tableMeta"
                        :table-row="alert_sett"
                        :header-key="'snp_name'"
                        :can-edit="true"
                        :no-function="true"
                        :no_prevent="true"
                        :pop_width="'100%'"
                        style="padding: 0; color: #333;"
                        @close-formula="snp_name_formula = false"
                        @set-formula="sendUpdate"
                ></formula-helper>
            </div>
            <label>&nbsp;&nbsp;&nbsp;Field for Name:&nbsp;</label>
            <select-block
                :options="availFlds()"
                :sel_value="alert_sett.snp_field_id_name"
                :is_disabled="!can_edit"
                class="h-snp"
                @option-select="(opt) => { updAlert('snp_field_id_name', opt.val); }"
            ></select-block>
            <label>&nbsp;&nbsp;&nbsp;Field for Time:&nbsp;</label>
            <select-block
                :options="availFlds()"
                :sel_value="alert_sett.snp_field_id_time"
                :is_disabled="!can_edit"
                class="h-snp"
                @option-select="(opt) => { updAlert('snp_field_id_time', opt.val); }"
            ></select-block>
        </div>
        <div class="flex flex--center no-wrap mrg" :style="textSysStyleSmart">
            <label>Source Data Table:&nbsp;</label>
            <select-with-folder-structure
                    :cur_val="alert_sett.snp_src_table_id"
                    :available_tables="$root.settingsMeta.available_tables"
                    :user="$root.user"
                    :empty_val="true"
                    @sel-changed="(val) => { updAlert('snp_src_table_id', val); }"
                    class="form-control h-snp"
            ></select-with-folder-structure>
            <label>&nbsp;&nbsp;&nbsp;Data Range:&nbsp;</label>
            <select-block
                    :is_disabled="!snpSourceTable"
                    :options="snpSourceTable ? getRGr(snpSourceTable, true) : []"
                    :sel_value="alert_sett.snp_data_range"
                    class="h-snp"
                    @option-select="(opt) => { updAlert('snp_data_range', opt.val); }"
            ></select-block>
        </div>
        <div v-if="snpSourceTable" class="flex flex--center-v no-wrap mrg" :style="textSysStyleSmart" style="max-width: 650px;">
            <label>Field correspondences for saving snapshot data:</label>
        </div>
        <div style="height: calc(100% - 120px); max-width: 650px;">
            <custom-table
                    v-if="snpSourceTable"
                    :cell_component_name="'custom-cell-alert-notif'"
                    :ref_tb_from_refcond="snpSourceTable"
                    :global-meta="tableMeta"
                    :table-meta="$root.settingsMeta['table_alert_snapshot_fields']"
                    :all-rows="alert_sett._snapshot_fields"
                    :rows-count="alert_sett._snapshot_fields.length"
                    :cell-height="1"
                    :max-cell-rows="0"
                    :is-full-width="true"
                    :user="$root.user"
                    :behavior="'alert_snapshot'"
                    :adding-row="{ active: true, position: 'bottom' }"
                    :use_theme="true"
                    :no_width="true"
                    :with_edit="can_edit"
                    @added-row="addAlertSnp"
                    @updated-row="updateAlertSnp"
                    @delete-row="deleteAlertSnp"
            ></custom-table>
        </div>
    </div>
</template>

<script>
    import {eventBus} from "../../../../../app";//DataRangeMixin

    import DataRangeMixin from "../../../../_Mixins/DataRangeMixin";
    import CellStyleMixin from "../../../../_Mixins/CellStyleMixin";

    import SelectBlock from "../../../../CommonBlocks/SelectBlock";
    import SelectWithFolderStructure from "../../../../CustomCell/InCell/SelectWithFolderStructure.vue";
    import CustomTable from "../../../../CustomTable/CustomTable.vue";

    export default {
        name: "AlertAutomationSnapshot",
        components: {
            CustomTable,
            SelectWithFolderStructure,
            SelectBlock,
        },
        mixins: [
            DataRangeMixin,
            CellStyleMixin,
        ],
        data: function () {
            return {
                snp_name_formula: false,
            }
        },
        computed: {
            snpSourceTable() {
                return _.find(this.$root.settingsMeta.available_tables, {id: this.alert_sett.snp_src_table_id});
            },
        },
        props:{
            can_edit: Boolean|Number,
            tableMeta: Object,
            alert_sett: Object,
        },
        methods: {
            availFlds() {
                return _.map(this.tableMeta._fields, (ff) => {
                    return { val:ff.id, show:ff.name };
                });
            },
            updAlert(key, val) {
                this.alert_sett[key] = val;
                if (key === 'snp_src_table_id' && !val) {
                    this.alert_sett[key] = null;
                }
                this.sendUpdate();
            },
            sendUpdate() {
                this.snp_name_formula = false;
                this.$emit('update-alert', this.alert_sett);
            },
            recreaFormula(param) {
                this[param] = false;
                this.$nextTick(() => {
                    this[param] = true;
                });
            },
            //Snapshot endpoints
            addAlertSnp(tableRow) {
                let fields = _.cloneDeep(tableRow);//copy object
                this.$root.deleteSystemFields(fields);

                $.LoadingOverlay('show');
                axios.post('/ajax/table/alert/snapshot-fields', {
                    table_alert_id: this.alert_sett.id,
                    fields: fields
                }).then(({ data }) => {
                    this.alert_sett._snapshot_fields = data._snapshot_fields;
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
            updateAlertSnp(tableRow) {
                let fields = _.cloneDeep(tableRow);//copy object
                this.$root.deleteSystemFields(fields);

                $.LoadingOverlay('show');
                axios.put('/ajax/table/alert/snapshot-fields', {
                    table_alert_id: this.alert_sett.id,
                    id: tableRow.id,
                    fields: fields
                }).then(({ data }) => {
                    this.alert_sett._snapshot_fields = data._snapshot_fields;
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
            deleteAlertSnp(tableRow) {
                $.LoadingOverlay('show');
                axios.delete('/ajax/table/alert/snapshot-fields', {
                    params: {
                        table_alert_id: this.alert_sett.id,
                        id: tableRow.id
                    }
                }).then(({ data }) => {
                    this.alert_sett._snapshot_fields = data._snapshot_fields;
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
        },
        mounted() {
        },
        beforeDestroy() {
        }
    }
</script>

<style lang="scss" scoped>
    label {
        margin: 0;
    }
    .mrg {
        margin: 10px 0;
    }
    .h-snp {
        height: 32px !important;
    }
</style>
<style lang="scss">
    .snp-wrap {
        .select2-container {
            z-index: 1000;
        }
    }
</style>