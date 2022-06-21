<template>
    <div class="full-height">
        <div style="margin: 10px 0;">
            <label v-if="is_temp">Users may make temporary changes to the settings.</label>
            <label v-else="">
                <span class="indeterm_check__wrap">
                    <span class="indeterm_check" @click="updAlertCheck('ask_anr_confirmation')">
                        <i v-if="alert_sett.ask_anr_confirmation" class="glyphicon glyphicon-ok group__icon"></i>
                    </span>
                </span>
                <span>Ask for confirmation / editing prior to execution.</span>
            </label>
        </div>
        <div>
            <label>List of tables (in which records are to be added to):</label>
        </div>
        <div class="special_he">
            <custom-table
                    :cell_component_name="'custom-cell-alert-notif'"
                    :global-meta="tableMeta"
                    :table-meta="$root.settingsMeta['alert_anr_tables']"
                    :all-rows="alert_sett._anr_tables"
                    :rows-count="alert_sett._anr_tables.length"
                    :cell-height="1"
                    :max-cell-rows="0"
                    :is-full-width="true"
                    :user="$root.user"
                    :behavior="'alert_anr'"
                    :adding-row="addingRow"
                    :use_theme="true"
                    :with_edit="can_edit"
                    :available-columns="availAnrTable"
                    :selected-row="selectedAnr"
                    style="border: none;"
                    @added-row="addAlertAnr"
                    @updated-row="updateAlertAnr"
                    @delete-row="deleteAlertAnr"
                    @row-index-clicked="rowIndexClickedAnr"
            ></custom-table>
        </div>
        <div class="form-group"></div>
        <div style="height: 36px;">
            <label style="position: relative; top: 7px;">Details of adding new record for: {{ selAnrTable ? selAnrTable.name : '...' }}</label>
            <div v-if="selAnrTable && !is_temp" class="right-elem" style="height: 32px;">
                <button class="btn btn-default btn-sm blue-gradient full-height"
                        :style="$root.themeButtonStyle"
                        @click="copyAnrFlds()"
                >Copy</button>
                <select-block
                        :options="getAnrCopy()"
                        :sel_value="tb_id_for_copy"
                        :hidden_name="'name'"
                        style="width: 250px;"
                        @option-select="(opt) => { tb_id_for_copy = opt.val; }"
                ></select-block>
            </div>
        </div>
        <div class="special_he">
            <custom-table
                    v-if="selAnrTable"
                    :cell_component_name="'custom-cell-alert-notif'"
                    :ref_tb_from_refcond="selTableObjectFromAnr"
                    :global-meta="tableMeta"
                    :table-meta="$root.settingsMeta['alert_anr_table_fields']"
                    :all-rows="selAnrTable._anr_fields"
                    :rows-count="selAnrTable._anr_fields.length"
                    :cell-height="1"
                    :max-cell-rows="0"
                    :is-full-width="true"
                    :user="$root.user"
                    :behavior="'alert_anr'"
                    :adding-row="addingRow"
                    :use_theme="true"
                    :with_edit="can_edit"
                    :available-columns="availAnrField"
                    style="border: none;"
                    @added-row="addAlertAnrField"
                    @updated-row="updateAlertAnrField"
                    @delete-row="deleteAlertAnrField"
            ></custom-table>
        </div>
    </div>
</template>

<script>
    import CustomTable from "../../../../CustomTable/CustomTable";
    import SelectBlock from "../../../../CommonBlocks/SelectBlock";

    export default {
        name: "AlertAutomationAnr",
        components: {
            SelectBlock,
            CustomTable,
        },
        data: function () {
            return {
                tb_id_for_copy: null,
                addingRow: {
                    active: true,
                    position: 'bottom'
                },
                selectedAnr: -1,
            }
        },
        props:{
            can_edit: Boolean,
            is_temp: Boolean,
            tableMeta: Object,
            alert_sett: Object,
        },
        computed: {
            availAnrTable() {
                return this.is_temp
                    ? ['temp_is_active','temp_name','temp_table_id','temp_qty']
                    : ['name','table_id','qty','is_active'];
            },
            availAnrField() {
                return this.is_temp
                    ? ['temp_table_field_id','temp_source','temp_input','temp_inherit_field_id']
                    : ['table_field_id','source','input','inherit_field_id'];
            },
            selAnrTable() {
                return this.alert_sett._anr_tables[this.selectedAnr] || null;
            },
            selTableObjectFromAnr() {
                let id = Number(this.is_temp ? this.selAnrTable.temp_table_id : this.selAnrTable.table_id);
                return this.selAnrTable
                    ? _.find(this.$root.settingsMeta.available_tables, {id: id})
                    : null;
            },
        },
        watch: {
            alert_sett(val) {
                this.selectedAnr = -1;
            },
        },
        methods: {
            updAlertCheck(key) {
                this.alert_sett[key] = !this.alert_sett[key];
                this.$emit('update-alert', this.alert_sett);
            },
            rowIndexClickedAnr(idx) {
                this.selectedAnr = idx;
            },
            //Anr Table
            addAlertAnr(tableRow) {
                let fields = _.cloneDeep(tableRow);//copy object
                this.$root.deleteSystemFields(fields);

                if (this.is_temp) {
                    let tb = _.first(this.alert_sett._anr_tables) || {};
                    fields.triggered_row = tb.triggered_row;
                }

                $.LoadingOverlay('show');
                axios.post('/ajax/table/alert/anr', {
                    table_alert_id: this.alert_sett.id,
                    fields: fields
                }).then(({ data }) => {
                    this.alert_sett._anr_tables = data._anr_tables;
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
            updateAlertAnr(tableRow) {
                let fields = _.cloneDeep(tableRow);//copy object
                this.$root.deleteSystemFields(fields);

                $.LoadingOverlay('show');
                axios.put('/ajax/table/alert/anr', {
                    table_alert_id: this.alert_sett.id,
                    id: tableRow.id,
                    fields: fields
                }).then(({ data }) => {
                    this.alert_sett._anr_tables = data._anr_tables;
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
            deleteAlertAnr(tableRow) {
                $.LoadingOverlay('show');
                axios.delete('/ajax/table/alert/anr', {
                    params: {
                        table_alert_id: this.alert_sett.id,
                        id: tableRow.id
                    }
                }).then(({ data }) => {
                    this.alert_sett._anr_tables = data._anr_tables;
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
            //Anr Fields
            addAlertAnrField(tableRow) {
                let fields = _.cloneDeep(tableRow);//copy object
                this.$root.deleteSystemFields(fields);

                $.LoadingOverlay('show');
                axios.post('/ajax/table/alert/anr/fld', {
                    anr_table_id: this.selAnrTable.id,
                    fields: fields
                }).then(({ data }) => {
                    this.selAnrTable._anr_fields = data._anr_fields;
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
            updateAlertAnrField(tableRow) {
                let fields = _.cloneDeep(tableRow);//copy object
                this.$root.deleteSystemFields(fields);

                $.LoadingOverlay('show');
                axios.put('/ajax/table/alert/anr/fld', {
                    anr_table_id: this.selAnrTable.id,
                    id: tableRow.id,
                    fields: fields
                }).then(({ data }) => {
                    this.selAnrTable._anr_fields = data._anr_fields;
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
            deleteAlertAnrField(tableRow) {
                $.LoadingOverlay('show');
                axios.delete('/ajax/table/alert/anr/fld', {
                    params: {
                        anr_table_id: this.selAnrTable.id,
                        id: tableRow.id
                    }
                }).then(({ data }) => {
                    this.selAnrTable._anr_fields = data._anr_fields;
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
            //
            copyAnrFlds() {
                $.LoadingOverlay('show');
                axios.post('/ajax/table/alert/anr/copy', {
                    to_anr_table_id: this.selAnrTable.id,
                    from_anr_table_id: this.tb_id_for_copy,
                }).then(({ data }) => {
                    this.selAnrTable._anr_fields = data._anr_fields;
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
            getAnrCopy() {
                let title_style =  {
                    color: '#000',
                    backgroundColor: '#eee',
                    cursor: 'auto',
                    textDecoration: 'none'
                };

                let grs = [];
                _.each(this.tableMeta._alerts, (al) => {
                    let has_table_different = _.find(al._anr_tables, (anr_tb) => { return this.selAnrTable.id != anr_tb.id; });
                    if (has_table_different) {
                        grs.push({
                            val: null,
                            show: '//Alert: ' + al.name,
                            style: title_style,
                            isTitle: true,
                        });
                        _.each(al._anr_tables, (anr_tb) => {
                            if (this.selAnrTable.id != anr_tb.id) {
                                grs.push({
                                    val: anr_tb.id,
                                    show: anr_tb.name,
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
        height: calc(50% - 56px);
        border: 1px solid #CCC;
    }
</style>