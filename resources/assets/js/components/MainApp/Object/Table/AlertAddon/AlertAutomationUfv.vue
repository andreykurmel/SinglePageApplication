<template>
    <div class="full-height" :style="$root.themeMainBgStyle">
        <div :style="textSysStyleSmart">
            <label>List of records for which field values are to be updated:</label>
        </div>
        <div class="special_he">
            <custom-table
                    :cell_component_name="'custom-cell-alert-notif'"
                    :global-meta="tableMeta"
                    :table-meta="$root.settingsMeta['alert_ufv_tables']"
                    :all-rows="alert_sett._ufv_tables"
                    :rows-count="alert_sett._ufv_tables.length"
                    :cell-height="1"
                    :max-cell-rows="0"
                    :is-full-width="true"
                    :user="$root.user"
                    :behavior="'alert_ufv'"
                    :adding-row="addingRow"
                    :use_theme="true"
                    :with_edit="can_edit"
                    :available-columns="availUfvTable"
                    :selected-row="selectedUfv"
                    style="border: none;"
                    @added-row="addAlertUfv"
                    @updated-row="updateAlertUfv"
                    @delete-row="deleteAlertUfv"
                    @row-index-clicked="rowIndexClickedUfv"
            ></custom-table>
        </div>
        <div class="form-group"></div>
        <div style="height: 36px;" :style="textSysStyleSmart">
            <label style="position: relative; top: 7px;">Details for updating field value for: {{ selUfvTable ? selUfvTable.name : '...' }}</label>
            <div v-if="selUfvTable" class="right-elem" style="height: 32px;">
                <button class="btn btn-default btn-sm blue-gradient full-height"
                        :style="$root.themeButtonStyle"
                        @click="copyUfvFlds()"
                >Copy</button>
                <select-block
                        :options="getUfvCopy()"
                        :sel_value="tb_id_for_copy"
                        :hidden_name="'name'"
                        style="width: 250px;"
                        @option-select="(opt) => { tb_id_for_copy = opt.val; }"
                ></select-block>
            </div>
        </div>
        <div class="special_he">
            <custom-table
                    v-if="selUfvTable"
                    :cell_component_name="'custom-cell-alert-notif'"
                    :global-meta="tableMeta"
                    :table-meta="$root.settingsMeta['alert_ufv_table_fields']"
                    :all-rows="selUfvTable._ufv_fields"
                    :rows-count="selUfvTable._ufv_fields.length"
                    :cell-height="1"
                    :max-cell-rows="0"
                    :is-full-width="true"
                    :user="$root.user"
                    :behavior="'alert_ufv'"
                    :adding-row="addingRow"
                    :use_theme="true"
                    :with_edit="can_edit"
                    :available-columns="availUfvField"
                    :parent-row="selUfvTable"
                    style="border: none;"
                    @added-row="addAlertUfvField"
                    @updated-row="updateAlertUfvField"
                    @delete-row="deleteAlertUfvField"
            ></custom-table>
        </div>
    </div>
</template>

<script>
    import CellStyleMixin from "../../../../_Mixins/CellStyleMixin";

    import CustomTable from "../../../../CustomTable/CustomTable";
    import SelectBlock from "../../../../CommonBlocks/SelectBlock";

    export default {
        name: "AlertAutomationUfv",
        components: {
            SelectBlock,
            CustomTable,
        },
        mixins: [
            CellStyleMixin,
        ],
        data: function () {
            return {
                tb_id_for_copy: null,
                addingRow: {
                    active: true,
                    position: 'bottom'
                },
                selectedUfv: -1,
            }
        },
        props:{
            can_edit: Boolean|Number,
            tableMeta: Object,
            alert_sett: Object,
            is_temp: Boolean,
        },
        computed: {
            availUfvTable() {
                return ['name','table_ref_cond_id','is_active'];
            },
            availUfvField() {
                return ['table_field_id','source','input','inherit_field_id'];
            },
            selUfvTable() {
                return this.alert_sett._ufv_tables[this.selectedUfv] || null;
            },
        },
        watch: {
            alert_sett(val) {
                this.selectedUfv = -1;
            },
        },
        methods: {
            updAlertCheck(key) {
                this.alert_sett[key] = !this.alert_sett[key];
                this.$emit('update-alert', this.alert_sett);
            },
            rowIndexClickedUfv(idx) {
                this.selectedUfv = idx;
            },
            //Ufv Table
            addAlertUfv(tableRow) {
                let fields = _.cloneDeep(tableRow);//copy object
                this.$root.deleteSystemFields(fields);

                $.LoadingOverlay('show');
                axios.post('/ajax/table/alert/ufv', {
                    table_alert_id: this.alert_sett.id,
                    fields: fields
                }).then(({ data }) => {
                    this.alert_sett._ufv_tables = data._ufv_tables;
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
            updateAlertUfv(tableRow) {
                let fields = _.cloneDeep(tableRow);//copy object
                this.$root.deleteSystemFields(fields);

                $.LoadingOverlay('show');
                axios.put('/ajax/table/alert/ufv', {
                    table_alert_id: this.alert_sett.id,
                    id: tableRow.id,
                    fields: fields
                }).then(({ data }) => {
                    this.alert_sett._ufv_tables = data._ufv_tables;
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
            deleteAlertUfv(tableRow) {
                $.LoadingOverlay('show');
                axios.delete('/ajax/table/alert/ufv', {
                    params: {
                        table_alert_id: this.alert_sett.id,
                        id: tableRow.id
                    }
                }).then(({ data }) => {
                    this.alert_sett._ufv_tables = data._ufv_tables;
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
            //Ufv Fields
            addAlertUfvField(tableRow) {
                let fields = _.cloneDeep(tableRow);//copy object
                this.$root.deleteSystemFields(fields);

                $.LoadingOverlay('show');
                axios.post('/ajax/table/alert/ufv/fld', {
                    ufv_table_id: this.selUfvTable.id,
                    fields: fields
                }).then(({ data }) => {
                    this.selUfvTable._ufv_fields = data._ufv_fields;
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
            updateAlertUfvField(tableRow) {
                let fields = _.cloneDeep(tableRow);//copy object
                this.$root.deleteSystemFields(fields);

                $.LoadingOverlay('show');
                axios.put('/ajax/table/alert/ufv/fld', {
                    ufv_table_id: this.selUfvTable.id,
                    id: tableRow.id,
                    fields: fields
                }).then(({ data }) => {
                    this.selUfvTable._ufv_fields = data._ufv_fields;
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
            deleteAlertUfvField(tableRow) {
                $.LoadingOverlay('show');
                axios.delete('/ajax/table/alert/ufv/fld', {
                    params: {
                        ufv_table_id: this.selUfvTable.id,
                        id: tableRow.id
                    }
                }).then(({ data }) => {
                    this.selUfvTable._ufv_fields = data._ufv_fields;
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
            //
            copyUfvFlds() {
                $.LoadingOverlay('show');
                axios.post('/ajax/table/alert/ufv/copy', {
                    to_ufv_table_id: this.selUfvTable.id,
                    from_ufv_table_id: this.tb_id_for_copy,
                }).then(({ data }) => {
                    this.selUfvTable._ufv_fields = data._ufv_fields;
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
            getUfvCopy() {
                let title_style =  {
                    color: '#000',
                    backgroundColor: '#eee',
                    cursor: 'auto',
                    textDecoration: 'none'
                };

                let grs = [];
                _.each(this.tableMeta._alerts, (al) => {
                    let has_table_different = _.find(al._ufv_tables, (ufv_tb) => { return this.selUfvTable.id != ufv_tb.id; });
                    if (has_table_different) {
                        grs.push({
                            val: null,
                            show: '//ANA: ' + al.name,
                            style: title_style,
                            isTitle: true,
                        });
                        _.each(al._ufv_tables, (ufv_tb) => {
                            if (this.selUfvTable.id != ufv_tb.id) {
                                grs.push({
                                    val: ufv_tb.id,
                                    show: ufv_tb.name,
                                    style: {color: '#333'},
                                });
                            }
                        });
                    }
                });
                return grs;
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
    .special_he {
        height: calc(50% - 34px);
        border: 1px solid #CCC;
    }
</style>