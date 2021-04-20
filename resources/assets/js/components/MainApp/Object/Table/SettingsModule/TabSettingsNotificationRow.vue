<template>
    <table class="spaced-table">
        <colgroup>
            <col :width="100">
            <col :width="400">
        </colgroup>
        <tbody v-if="requestFields">

            <tr>
                <td :style="getTdStyle">
                    <div class="td td--100 h-32" :style="getTdStyle">
                        <div class="flex flex--center-v full-height">
                            <label>{{ $root.uniqName(requestFields[prefix_fld+'confirm_msg'].name) }}:&nbsp;</label>
                            <div class="full-width full-height" style="position: relative;">
                                <input type="text"
                                       v-model="requestRow[prefix_fld+'confirm_msg']"
                                       :disabled="!with_edit"
                                       @keyup="recreateFormul('formula_dcr_email_subject')"
                                       @focus="formula_dcr_confirm_msg = true"
                                       class="form-control"/>
                                <formula-helper
                                        v-if="formula_dcr_confirm_msg"
                                        :user="$root.user"
                                        :table-meta="$root.tableMeta"
                                        :table-row="requestRow"
                                        :header-key="prefix_fld+'confirm_msg'"
                                        :can-edit="with_edit"
                                        :no-function="true"
                                        :no_prevent="true"
                                        :pop_width="'100%'"
                                        @set-formula="updatedCell"
                                ></formula-helper>
                            </div>
                        </div>
                    </div>
                </td>
            </tr>

            <tr>
                <td :style="getTdStyle">
                    <div class="td td--100 h-32" :style="getTdStyle">
                        <div class="flex flex--center-v full-height">
                            <label>{{ $root.uniqName(requestFields[prefix_fld+'email_field_id'].name) }}:&nbsp;</label>
                            <select v-model="requestRow[prefix_fld+'email_field_id']" :disabled="!with_edit" @change="updatedCell" class="form-control" :style="{color: !requestRow[prefix_fld+'email_field_id'] ? '#bbb' : ''}">
                                <option :value="null" style="color: #bbb;">Select a field saving emails</option>
                                <option v-for="field in $root.tableMeta._fields"
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
                            <label>{{ $root.uniqName(requestFields[prefix_fld+'email_field_static'].name) }}:&nbsp;</label>
                            <input type="text" v-model="requestRow[prefix_fld+'email_field_static']" :disabled="!with_edit" @change="updatedCell" class="form-control" placeholder="Enter emails separated with comma, semicolon"/>
                        </div>
                    </div>
                </td>
            </tr>

            <tr>
                <td :style="getTdStyle">
                    <div class="td td--100 h-32" :style="getTdStyle">
                        <div class="flex flex--center-v full-height">
                            <label>{{ $root.uniqName(requestFields[prefix_fld+'email_subject'].name) }}:&nbsp;</label>
                            <div class="full-width full-height" style="position: relative;">
                                <input type="text"
                                       v-model="requestRow[prefix_fld+'email_subject']"
                                       :disabled="!with_edit"
                                       @keyup="recreateFormul('formula_dcr_email_subject')"
                                       @focus="formula_dcr_email_subject = true"
                                       class="form-control"/>
                                <formula-helper
                                        v-if="formula_dcr_email_subject"
                                        :user="$root.user"
                                        :table-meta="$root.tableMeta"
                                        :table-row="requestRow"
                                        :header-key="prefix_fld+'email_subject'"
                                        :can-edit="with_edit"
                                        :no-function="true"
                                        :no_prevent="true"
                                        :pop_width="'100%'"
                                        @set-formula="updatedCell"
                                ></formula-helper>
                            </div>
                        </div>
                    </div>
                </td>
            </tr>

            <tr>
                <td :style="getTdStyle">
                    <div class="td td--100 h-32" :style="getTdStyle">
                        <div class="flex flex--center-v full-height">
                            <label>{{ $root.uniqName(requestFields[prefix_fld+'addressee_txt'].name) }}:&nbsp;</label>
                            <div class="full-width full-height" style="position: relative;">
                                <input type="text"
                                       v-model="requestRow[prefix_fld+'addressee_txt']"
                                       :disabled="!with_edit"
                                       @keyup="recreateFormul('formula_dcr_email_subject')"
                                       @focus="formula_dcr_addressee_txt = true"
                                       class="form-control"/>
                                <formula-helper
                                        v-if="formula_dcr_addressee_txt"
                                        :user="$root.user"
                                        :table-meta="$root.tableMeta"
                                        :table-row="requestRow"
                                        :header-key="prefix_fld+'addressee_txt'"
                                        :can-edit="with_edit"
                                        :no-function="true"
                                        :no_prevent="true"
                                        :pop_width="'100%'"
                                        @set-formula="updatedCell"
                                ></formula-helper>
                            </div>
                        </div>
                    </div>
                </td>
            </tr>

            <tr>
                <td :style="getTdStyle">
                    <div class="td td--100 h-32" :style="getTdStyle">
                        <div class="flex flex--center-v full-height">
                            <label>{{ $root.uniqName(requestFields[prefix_fld+'email_message'].name) }}:&nbsp;</label>
                            <input type="text"
                                   v-model="requestRow[prefix_fld+'email_message']"
                                   :disabled="!with_edit"
                                   @keyup="recreateFormul('formula_dcr_email_subject')"
                                   @focus="formula_dcr_email_message = true"
                                   class="form-control"/>
                            <formula-helper
                                    v-if="formula_dcr_email_message"
                                    :user="$root.user"
                                    :table-meta="$root.tableMeta"
                                    :table-row="requestRow"
                                    :header-key="prefix_fld+'email_message'"
                                    :can-edit="with_edit"
                                    :no-function="true"
                                    :no_prevent="true"
                                    :pop_width="'100%'"
                                    @set-formula="updatedCell"
                            ></formula-helper>
                        </div>
                    </div>
                </td>
            </tr>

            <tr>
                <td :style="getTdStyle">
                    <label style="white-space: normal;">All fields in the selected Column Groups and associated values will be included and displayed in selected Format in the notification:</label>
                </td>
            </tr>

            <tr>
                <td :style="getTdStyle">
                    <div class="td td--50 h-32" :style="getTdStyle">
                        <div class="flex flex--center-v full-height">
                            <label>{{ $root.uniqName(requestFields[prefix_fld+'email_format'].name) }}:&nbsp;</label>
                            <select v-model="requestRow[prefix_fld+'email_format']" :disabled="!with_edit" @change="updatedCell" class="form-control">
                                <option value="table">Tabular (H)</option>
                                <option value="vertical">Form / Tabular (V)</option>
                                <option value="list">Listing</option>
                            </select>
                        </div>
                    </div>
                    <div class="td td--50 h-32" :style="getTdStyle">
                        <div class="flex flex--center-v full-height">
                            <label>{{ $root.uniqName(requestFields[prefix_fld+'email_col_group_id'].name) }}:&nbsp;</label>
                            <select v-model="requestRow[prefix_fld+'email_col_group_id']" :disabled="!with_edit" @change="updatedCell" class="form-control">
                                <option></option>
                                <option v-for="colGr in $root.tableMeta._column_groups" :value="colGr.id">{{ colGr.name }}</option>
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


    export default {
        components: {
            FormulaHelper,
        },
        mixins: [
            CellStyleMixin,
            ReqRowMixin,
        ],
        name: "TabSettingsNotificationRow",
        data: function () {
            return {
            };
        },
        props:{
            table_id: Number,
            cellHeight: Number,
            maxCellRows: Number,
            tableRequest: Object,
            requestRow: Object,
            with_edit: Boolean,
            prefix_fld: String,
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
        },
        mounted() {
            this.setAvailFields();
        }
    }
</script>

<style lang="scss" scoped>
    @import "ReqRowStyle";
</style>