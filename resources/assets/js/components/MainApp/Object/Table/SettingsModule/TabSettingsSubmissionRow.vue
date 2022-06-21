<template>
    <table class="spaced-table">
        <tbody v-if="requestFields">

            <tr>
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
                            <select v-model="requestRow['dcr_record_url_field_id']" :style="textSysStyle" :disabled="!with_edit" @change="updatedCell" class="form-control">
                                <option :value="null" style="color: #bbb;">Select a String or Text field</option>
                                <option v-for="field in tableMeta._fields"
                                        v-if="$root.inArray(field.f_type, ['String','Text','Long Text'])"
                                        :value="field.id" style="color: #444;"
                                >{{ $root.uniqName(field.name) }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="td td--33 h-32" :style="getTdStyle">
                        <div class="flex flex--center-v full-height">
                            <label>{{ $root.uniqName(requestFields['dcr_record_status_id'].name) }}:&nbsp;</label>
                            <select v-model="requestRow['dcr_record_status_id']" :style="textSysStyle" :disabled="!with_edit" @change="updatedCell" class="form-control">
                                <option :value="null" style="color: #bbb;">Select a String or Text field</option>
                                <option v-for="field in tableMeta._fields"
                                        v-if="$root.inArray(field.f_type, ['String','Text','Long Text'])"
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
                    <div class="td td--40 h-32" :style="getTdStyle">
                        <div class="flex flex--center-v full-height">
                            <label>Fields saving statuses:</label>
                        </div>
                    </div>
                    <div class="td td--60 h-32" :style="getTdStyle">
                        <div class="flex flex--center-v full-height">
                            <label>{{ $root.uniqName(requestFields['dcr_record_visibility_id'].name) }}:&nbsp;</label>
                            <select v-model="requestRow['dcr_record_visibility_id']"
                                    :style="textSysStyle"
                                    :disabled="!with_edit || !requestRow['dcr_record_url_field_id']"
                                    @change="updatedCell"
                                    class="form-control"
                            >
                                <option :value="null" style="color: #bbb;">Select a Boolean field</option>
                                <option v-for="field in tableMeta._fields"
                                        v-if="$root.inArray(field.f_type, ['Boolean'])"
                                        :value="field.id" style="color: #444;"
                                >{{ $root.uniqName(field.name) }}</option>
                            </select>
                            <label>&nbsp;{{ $root.uniqName(requestFields['dcr_record_editability_id'].name) }}:&nbsp;</label>
                            <select v-model="requestRow['dcr_record_editability_id']"
                                    :style="textSysStyle"
                                    :disabled="!with_edit || !requestRow['dcr_record_url_field_id']"
                                    @change="updatedCell"
                                    class="form-control"
                            >
                                <option :value="null" style="color: #bbb;">Select a Boolean field</option>
                                <option v-for="field in tableMeta._fields"
                                        v-if="$root.inArray(field.f_type, ['Boolean'])"
                                        :value="field.id" style="color: #444;"
                                >{{ $root.uniqName(field.name) }}</option>
                            </select>
                        </div>
                    </div>
                </td>
            </tr>

            <tr>
                <td :style="getTdStyle">
                    <div class="td td--40 h-32" :style="getTdStyle">
                        <div class="flex flex--center-v full-height">
                            <label>{{ $root.uniqName(requestFields['dcr_record_save_visibility_def'].name) }}:&nbsp;</label>
                        </div>
                    </div>
                    <div class="td td--60 h-32" :style="getTdStyle">
                        <div class="flex flex--center-v full-height">
                            <label>Visibility:&nbsp;</label>
                            <select v-model="requestRow['dcr_record_save_visibility_def']"
                                    :style="textSysStyle"
                                    :disabled="!with_edit"
                                    @change="updatedCell"
                                    class="form-control"
                            >
                                <option :value="1">On</option>
                                <option :value="null">Off</option>
                            </select>
                            <label>&nbsp;{{ $root.uniqName(requestFields['dcr_record_save_editability_def'].name) }}:&nbsp;</label>
                            <select v-model="requestRow['dcr_record_save_editability_def']"
                                    :style="textSysStyle"
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

            <tr>
                <td :style="getTdStyle">
                    <div class="td td--40 h-32" :style="getTdStyle">
                        <div class="flex flex--center-v full-height">
                            <label>{{ $root.uniqName(requestFields['dcr_record_visibility_def'].name) }}:&nbsp;</label>
                        </div>
                    </div>
                    <div class="td td--60 h-32" :style="getTdStyle">
                        <div class="flex flex--center-v full-height">
                            <label>Visibility:&nbsp;</label>
                            <select v-model="requestRow['dcr_record_visibility_def']"
                                    :disabled="!with_edit"
                                    @change="updatedCell"
                                    class="form-control"
                            >
                                <option :value="1">On</option>
                                <option :value="null">Off</option>
                            </select>
                            <label>&nbsp;{{ $root.uniqName(requestFields['dcr_record_editability_def'].name) }}:&nbsp;</label>
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
                    ...this.textSysStyle,
                };
            },
        },
        watch: {
            table_id(val) {
                this.setAvailFields();
            }
        },
        methods: {
        },
        mounted() {
            this.setAvailFields();
        }
    }
</script>

<style lang="scss" scoped>
    @import "ReqRowStyle";
</style>