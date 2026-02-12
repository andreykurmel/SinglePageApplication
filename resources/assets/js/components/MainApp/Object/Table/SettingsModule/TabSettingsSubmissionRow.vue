<template>
    <table class="spaced-table" :style="bgColor">
        <tbody v-if="requestFields" :style="textColor">

            <tr>
                <td :style="getTdStyle" class="flex flex--center">
                    <div class="td td--100 h-32 flex flex--center-v" :style="getTdStyle">
                        <label>Download:&nbsp;&nbsp;</label>
                        <label class="switch_t" style="display: inline-block;">
                            <input type="checkbox" v-model="requestRow['download_pdf']" :disabled="!with_edit" @change="updatedCell">
                            <span class="toggler round" :class="[!with_edit ? 'disabled' : '']"></span>
                        </label>
                        <label>&nbsp;PDF&nbsp;&nbsp;</label>
                        <label class="switch_t" style="display: inline-block;">
                            <input type="checkbox" v-model="requestRow['download_png']" :disabled="!with_edit" @change="updatedCell">
                            <span class="toggler round" :class="[!with_edit ? 'disabled' : '']"></span>
                        </label>
                        <label>&nbsp;PNG</label>
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
                    <div class="td td--50 h-32" :style="getTdStyle">
                        <div class="flex flex--center-v full-height">
                            <label class="frst-col-label">Field for saving record specific URL:&nbsp;</label>
                            <select v-model="requestRow['dcr_record_url_field_id']" :style="textSysStyle" :disabled="!with_edit" @change="updatedCell('dcr_record_url_field_id')" class="form-control">
                                <option :value="null" style="color: #bbb;">Select a String or Text field</option>
                                <option v-for="field in tableMeta._fields"
                                        v-if="$root.inArray(field.f_type, ['String','Text','Long Text'])"
                                        :value="field.id" style="color: #444;"
                                >{{ $root.uniqName(field.name) }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="td td--50 h-32" :style="getTdStyle">
                        <div class="flex flex--center-v full-height">
                            <label class="secnd-col-label">Field for saving "Save/Submit/Update" Status:&nbsp;</label>
                            <select v-model="requestRow['dcr_record_status_id']" :style="textSysStyle" :disabled="!with_edit" @change="updatedCell('dcr_record_status_id')" class="form-control">
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
                            <label>&nbsp;Allow saving unfinished form and Submitting later (enable “Save”).</label>
                        </div>
                    </div>
                </td>
            </tr>

            <tr>
                <td :style="getTdStyle">
                    <div class="td td--50 h-32" :style="getTdStyle">
                        <div class="flex flex--center-v full-height">
                            <label class="frst-col-label">Fields saving statuses for: Visibility:&nbsp;</label>
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
                        </div>
                    </div>
                    <div class="td td--50 h-32" :style="getTdStyle">
                        <div class="flex flex--center-v full-height">
                            <label :class="{'visi-hidden': !requestRow['dcr_record_visibility_id']}" class="secnd-col-label">Editability:&nbsp;</label>
                            <select v-model="requestRow['dcr_record_editability_id']"
                                    :class="{'visi-hidden': !requestRow['dcr_record_visibility_id']}"
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

            <template v-if="requestRow['dcr_record_visibility_id']">
                <tr>
                    <td :style="getTdStyle">
                        <div class="td td--50 h-32" :style="getTdStyle">
                            <div class="flex flex--center-v full-height">
                                <label class="frst-col-label">Default value upon saving: Visibility:&nbsp;</label>
                                <select v-model="requestRow['dcr_record_save_visibility_def']"
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
                        <div class="td td--50 h-32" :style="getTdStyle">
                            <div class="flex flex--center-v full-height">
                                <label :class="{'visi-hidden': !requestRow['dcr_record_editability_id'] || !requestRow['dcr_record_save_visibility_def']}" class="secnd-col-label">
                                    Editability:&nbsp;
                                </label>
                                <select v-model="requestRow['dcr_record_save_editability_def']"
                                        :class="{'visi-hidden': !requestRow['dcr_record_editability_id'] || !requestRow['dcr_record_save_visibility_def']}"
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
                        <div class="td td--50 h-32" :style="getTdStyle">
                            <div class="flex flex--center-v full-height">
                                <label class="frst-col-label">Default value upon submitting: Visibility:&nbsp;</label>
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
                        <div class="td td--50 h-32" :style="getTdStyle">
                            <div class="flex flex--center-v full-height">
                                <label :class="{'visi-hidden': !requestRow['dcr_record_editability_id'] || !requestRow['dcr_record_visibility_def']}" class="secnd-col-label">
                                    Editability:&nbsp;
                                </label>
                                <select v-model="requestRow['dcr_record_editability_def']"
                                        :class="{'visi-hidden': !requestRow['dcr_record_editability_id'] || !requestRow['dcr_record_visibility_def']}"
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
            </template>

        </tbody>
    </table>
</template>

<script>
    import StyleMixinWithBg from "../../../../_Mixins/StyleMixinWithBg.vue";
    import ReqRowMixin from "./ReqRowMixin.vue";

    import CustomTable from "../../../../CustomTable/CustomTable";

    export default {
        components: {
            CustomTable,
        },
        mixins: [
            StyleMixinWithBg,
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
            with_edit: Boolean,
            bg_color: String,
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
    .visi-hidden {
        visibility: hidden;
    }
    .frst-col-label {
        width: 100%;
        text-align: left;
    }
    .secnd-col-label {
        width: 100%;
        text-align: right;
    }
    .spaced-table td label {
        white-space: normal;
    }
    .spaced-table td select {
        width: 170px;
        flex-shrink: 0;
    }
</style>