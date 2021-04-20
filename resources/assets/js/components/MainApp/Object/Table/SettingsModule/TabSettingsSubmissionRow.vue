<template>
    <table class="spaced-table">
        <colgroup>
            <col :width="100">
            <col :width="400">
        </colgroup>
        <tbody v-if="requestFields">

            <tr>
                <!--<td :style="getTdStyle">-->
                    <!--<label>SUBMISSION</label>-->
                <!--</td>-->
                <td :style="getTdStyle" class="flex flex--center">
                    <div class="td td--100 h-32 flex flex--center-v" :style="getTdStyle">
                        <label>{{ $root.uniqName(requestFields['one_per_submission'].name) }}:&nbsp;</label>
                        <label class="switch_t" style="display: inline-block;margin-left: 30px;">
                            <input type="checkbox" v-model="requestRow['one_per_submission']" :disabled="!with_edit" @change="updatedCell">
                            <span class="toggler round" :class="[!with_edit ? 'disabled' : '']"></span>
                        </label>
                    </div>
                </td>
            </tr>

            <!--<tr>-->
                <!--<td>-->
                    <!--<div style="border: 1px solid #ccc; margin: 10px 0;"></div>-->
                <!--</td>-->
            <!--</tr>-->

            <!--<tr v-if="requestRow['one_per_submission']">-->
                <!--<td>-->
                    <!--<label>Set a limit for the number of records allowed to be added for the form and links</label>-->
                <!--</td>-->
            <!--</tr>-->
            <!--<tr v-if="requestRow['one_per_submission']">-->
                <!--<td :style="{height: 'auto'}" class="flex flex&#45;&#45;center">-->
                    <!--<custom-table-->
                            <!--v-if="requestRow && requestRow._link_limits"-->
                            <!--:cell_component_name="'custom-cell-display-links'"-->
                            <!--:global-meta="tableMeta"-->
                            <!--:table-meta="$root.settingsMeta['table_field_link_to_dcr']"-->
                            <!--:all-rows="requestRow._link_limits"-->
                            <!--:rows-count="requestRow._link_limits.length"-->
                            <!--:cell-height="$root.cellHeight"-->
                            <!--:max-cell-rows="$root.maxCellRows"-->
                            <!--:is-full-width="true"-->
                            <!--:fixed_ddl_pos="true"-->
                            <!--:behavior="'settings_display_links'"-->
                            <!--:user="$root.user"-->
                            <!--:adding-row="addingRow"-->
                            <!--:use_theme="true"-->
                            <!--@added-row="addLinkToDcr"-->
                            <!--@updated-row="updateLinkToDcr"-->
                            <!--@delete-row="deleteLinkToDcr"-->
                    <!--&gt;</custom-table>-->
                <!--</td>-->
            <!--</tr>-->

            <tr>
                <td>
                    <div style="border: 1px solid #ccc; margin: 10px 0;"></div>
                </td>
            </tr>

            <tr>
                <td :style="getTdStyle">
                    <div class="td td--66 h-32" :style="getTdStyle">
                        <div class="flex flex--center-v full-height">
                            <label>{{ $root.uniqName(requestFields['dcr_record_url_field_id'].name) }}:&nbsp;</label>
                            <select v-model="requestRow['dcr_record_url_field_id']" :disabled="!with_edit" @change="updatedCell" class="form-control" :style="{color: !requestRow['dcr_record_url_field_id'] ? '#bbb' : ''}">
                                <option :value="null" style="color: #bbb;">Select a String or Text field</option>
                                <option v-for="field in tableMeta._fields"
                                        v-if="inArray(field.f_type, ['String','Text','Long Text'])"
                                        :value="field.id" style="color: #444;"
                                >{{ $root.uniqName(field.name) }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="td td--33 h-32" :style="getTdStyle">
                        <div class="flex flex--center-v full-height">
                            <label>{{ $root.uniqName(requestFields['dcr_record_status_id'].name) }}:&nbsp;</label>
                            <select v-model="requestRow['dcr_record_status_id']" :disabled="!with_edit" @change="updatedCell" class="form-control" :style="{color: !requestRow['dcr_record_status_id'] ? '#bbb' : ''}">
                                <option :value="null" style="color: #bbb;">Select a String or Text field</option>
                                <option v-for="field in tableMeta._fields"
                                        v-if="inArray(field.f_type, ['String','Text','Long Text'])"
                                        :value="field.id" style="color: #444;"
                                >{{ $root.uniqName(field.name) }}</option>
                            </select>
                        </div>
                    </div>
                </td>
            </tr>

            <tr>
                <td :style="getTdStyle">
                    <div class="td td--100 h-32" :style="getTdStyle">
                        <div class="flex flex--center-v full-height">
                            <label class="switch_t">
                                <input type="checkbox"
                                       v-model="requestRow['dcr_record_allow_unfinished']"
                                       :disabled="!with_edit || !requestRow['dcr_record_url_field_id']"
                                       @change="updatedCell">
                                <span class="toggler round" :class="[!with_edit || !requestRow['dcr_record_url_field_id'] ? 'disabled' : '']"></span>
                            </label>
                            <label>&nbsp;{{ $root.uniqName(requestFields['dcr_record_allow_unfinished'].name) }}</label>
                        </div>
                    </div>
                </td>
            </tr>

            <tr>
                <td :style="getTdStyle">
                    <div class="td td--50 h-32" :style="getTdStyle">
                        <div class="flex flex--center-v full-height">
                            <label>{{ $root.uniqName(requestFields['dcr_record_visibility_id'].name) }}:&nbsp;</label>
                            <select v-model="requestRow['dcr_record_visibility_id']"
                                    :disabled="!with_edit || !requestRow['dcr_record_url_field_id']"
                                    @change="updatedCell"
                                    class="form-control"
                                    :style="{color: !requestRow['dcr_record_visibility_id'] ? '#bbb' : ''}"
                            >
                                <option :value="null" style="color: #bbb;">Select a Boolean field</option>
                                <option v-for="field in tableMeta._fields"
                                        v-if="inArray(field.f_type, ['Boolean'])"
                                        :value="field.id" style="color: #444;"
                                >{{ $root.uniqName(field.name) }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="td td--50 h-32" :style="getTdStyle">
                        <div class="flex flex--center-v full-height">
                            <label>{{ $root.uniqName(requestFields['dcr_record_editability_id'].name) }}:&nbsp;</label>
                            <select v-model="requestRow['dcr_record_editability_id']"
                                    :disabled="!with_edit || !requestRow['dcr_record_url_field_id']"
                                    @change="updatedCell"
                                    class="form-control"
                                    :style="{color: !requestRow['dcr_record_editability_id'] ? '#bbb' : ''}"
                            >
                                <option :value="null" style="color: #bbb;">Select a Boolean field</option>
                                <option v-for="field in tableMeta._fields"
                                        v-if="inArray(field.f_type, ['Boolean'])"
                                        :value="field.id" style="color: #444;"
                                >{{ $root.uniqName(field.name) }}</option>
                            </select>
                        </div>
                    </div>
                </td>
            </tr>

            <tr>
                <td :style="getTdStyle">
                    <div class="td td--66 h-32" :style="getTdStyle">
                        <div class="flex flex--center-v full-height">
                            <label>{{ $root.uniqName(requestFields['dcr_record_visibility_def'].name) }}:&nbsp;</label>
                            <select v-model="requestRow['dcr_record_visibility_def']"
                                    :disabled="!with_edit"
                                    @change="updatedCell"
                                    class="form-control"
                            >
                                <option :value="1">On</option>
                                <option :value="null">Off</option>
                            </select>
                        </div>
                    </div>
                    <div class="td td--33 h-32" :style="getTdStyle">
                        <div class="flex flex--center-v full-height">
                            <label>{{ $root.uniqName(requestFields['dcr_record_editability_def'].name) }}:&nbsp;</label>
                            <select v-model="requestRow['dcr_record_editability_def']"
                                    :disabled="!with_edit"
                                    @change="updatedCell"
                                    class="form-control"
                            >
                                <option :value="1">On</option>
                                <option :value="null">Off</option>
                            </select>
                        </div>
                    </div>
                </td>
            </tr>

        </tbody>
    </table>
</template>

<script>
    import CellStyleMixin from "../../../../_Mixins/CellStyleMixin.vue";
    import ReqRowMixin from "./ReqRowMixin.vue";

    import FormulaHelper from "../../../../CustomCell/InCell/FormulaHelper";
    import CustomTable from "../../../../CustomTable/CustomTable";

    export default {
        components: {
            CustomTable,
            FormulaHelper,
        },
        mixins: [
            CellStyleMixin,
            ReqRowMixin,
        ],
        name: "TabSettingsSubmissionRow",
        data: function () {
            return {
                addingRow: {
                    active: true,
                    position: 'bottom'
                },
            };
        },
        props:{
            tableMeta: Object,
            table_id: Number,
            cellHeight: Number,
            maxCellRows: Number,
            tableRequest: Object,
            requestRow: Object,
            with_edit: Boolean
        },
        computed: {
            getTdStyle() {
                return {
                    height: this.tdCellHGT+'px',
                };
            },
        },
        watch: {
            table_id(val) {
                this.setAvailFields();
            }
        },
        methods: {
            inArray(type, array) {
                return array.indexOf(type) > -1;
            },
            
            //Link To Dcr Limits
            addLinkToDcr(tableRow) {
                this.$root.sm_msg_type = 1;

                let fields = _.cloneDeep(tableRow);//copy object
                this.$root.deleteSystemFields(fields);

                axios.post('/ajax/settings/data/link/todcr', {
                    table_field_link_id: tableRow.table_field_link_id,
                    table_dcr_id: this.requestRow.id,
                    fields: fields,
                }).then(({ data }) => {
                    this.requestRow._link_limits.push(data);
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
            updateLinkToDcr(tableRow) {
                this.$root.sm_msg_type = 1;

                let group_id = tableRow.id;
                let fields = _.cloneDeep(tableRow);//copy object
                this.$root.deleteSystemFields(fields);

                axios.put('/ajax/settings/data/link/todcr', {
                    table_field_link_dcr_id: group_id,
                    fields: fields
                }).then(({ data }) => {
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
            deleteLinkToDcr(tableRow) {
                this.$root.sm_msg_type = 1;
                axios.delete('/ajax/settings/data/link/todcr', {
                    params: {
                        table_field_link_dcr_id: tableRow.id
                    }
                }).then(({ data }) => {
                    let idx = _.findIndex(this.requestRow._link_limits, {id: Number(tableRow.id)});
                    if (idx > -1) {
                        this.requestRow._link_limits.splice(idx, 1);
                    }
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
        },
        mounted() {
            this.setAvailFields();
        }
    }
</script>

<style lang="scss" scoped>
    @import "ReqRowStyle";
</style>