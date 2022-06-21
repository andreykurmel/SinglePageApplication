<template>
    <table class="spaced-table">
        <tbody v-if="requestFields">

            <tr>
                <td :style="getTdStyle">
                    <div class="td td--100 h-32" :style="getTdStyle">
                        <div class="flex flex--center-v full-height">
                            <label>{{ $root.uniqName(requestFields[prefix_fld+'confirm_msg'].name) }}:&nbsp;</label>
                            <div class="full-width full-height" style="position: relative;">
                                <input type="text"
                                       :style="textSysStyle"
                                       v-model="requestRow[prefix_fld+'confirm_msg']"
                                       :disabled="!with_edit"
                                       @keyup="recreateFormul('formula_dcr_confirm_msg')"
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

            <tr v-show="hasUniq">
                <td :style="getTdStyle">
                    <div class="td td--100 h-32" :style="getTdStyle">
                        <div class="flex flex--center-v full-height">
                            <label>{{ $root.uniqName(requestFields[prefix_fld+'unique_msg'].name) }}:&nbsp;</label>
                            <div class="full-width full-height" style="position: relative;">
                                <input type="text"
                                       :style="textSysStyle"
                                       v-model="requestRow[prefix_fld+'unique_msg']"
                                       :disabled="!with_edit"
                                       @keyup="recreateFormul('formula_dcr_unique_msg')"
                                       @focus="formula_dcr_unique_msg = true"
                                       class="form-control"/>
                                <formula-helper
                                        v-if="formula_dcr_unique_msg"
                                        :user="$root.user"
                                        :table-meta="$root.tableMeta"
                                        :table-row="requestRow"
                                        :header-key="prefix_fld+'unique_msg'"
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
                    <label style="white-space: normal;">Recipients (email addresses. Use comma, semi-colon or space to separate multiple addresses):</label>
                </td>
            </tr>

            <tr>
                <td :style="getTdStyle">
                    <div class="td td--100 h-32" :style="getTdStyle">
                        <div class="flex flex--center-v full-height">
                            <label style="min-width: 45px">&nbsp;&nbsp;&nbsp;{{ $root.uniqName(requestFields[prefix_fld+'email_field_id'].name) }}:&nbsp;</label>
                            <select v-model="requestRow[prefix_fld+'email_field_id']" :disabled="!with_edit" @change="updatedCell" class="form-control" :style="textSysStyle">
                                <option :value="null" style="color: #bbb;"></option>
                                <option v-for="field in $root.tableMeta._fields"
                                        v-if="inArray(field.f_type, ['String','Text','Long Text'])"
                                        :value="field.id" style="color: #444;"
                                >{{ $root.uniqName(field.name) }}</option>
                            </select>
                            <label>&nbsp;&nbsp;&nbsp;{{ $root.uniqName(requestFields[prefix_fld+'email_field_static'].name) }}:&nbsp;</label>
                            <input type="text" :style="textSysStyle" v-model="requestRow[prefix_fld+'email_field_static']" :disabled="!with_edit" @change="updatedCell" class="form-control"/>
                        </div>
                    </div>
                </td>
            </tr>

            <tr>
                <td :style="getTdStyle">
                    <div class="td td--100 h-32" :style="getTdStyle">
                        <div class="flex flex--center-v full-height">
                            <label style="min-width: 45px">&nbsp;&nbsp;&nbsp;{{ $root.uniqName(requestFields[prefix_fld+'cc_email_field_id'].name) }}:&nbsp;</label>
                            <select v-model="requestRow[prefix_fld+'cc_email_field_id']" :disabled="!with_edit" @change="updatedCell" class="form-control" :style="textSysStyle">
                                <option :value="null" style="color: #bbb;"></option>
                                <option v-for="field in $root.tableMeta._fields"
                                        v-if="inArray(field.f_type, ['String','Text','Long Text'])"
                                        :value="field.id" style="color: #444;"
                                >{{ $root.uniqName(field.name) }}</option>
                            </select>
                            <label>&nbsp;&nbsp;&nbsp;{{ $root.uniqName(requestFields[prefix_fld+'cc_email_field_static'].name) }}:&nbsp;</label>
                            <input type="text" :style="textSysStyle" v-model="requestRow[prefix_fld+'cc_email_field_static']" :disabled="!with_edit" @change="updatedCell" class="form-control"/>
                        </div>
                    </div>
                </td>
            </tr>

            <tr>
                <td :style="getTdStyle">
                    <div class="td td--100 h-32" :style="getTdStyle">
                        <div class="flex flex--center-v full-height">
                            <label style="min-width: 45px">&nbsp;&nbsp;&nbsp;{{ $root.uniqName(requestFields[prefix_fld+'bcc_email_field_id'].name) }}:&nbsp;</label>
                            <select v-model="requestRow[prefix_fld+'bcc_email_field_id']" :disabled="!with_edit" @change="updatedCell" class="form-control" :style="textSysStyle">
                                <option :value="null" style="color: #bbb;"></option>
                                <option v-for="field in $root.tableMeta._fields"
                                        v-if="inArray(field.f_type, ['String','Text','Long Text'])"
                                        :value="field.id" style="color: #444;"
                                >{{ $root.uniqName(field.name) }}</option>
                            </select>
                            <label>&nbsp;&nbsp;&nbsp;{{ $root.uniqName(requestFields[prefix_fld+'bcc_email_field_static'].name) }}:&nbsp;</label>
                            <input type="text" :style="textSysStyle" v-model="requestRow[prefix_fld+'bcc_email_field_static']" :disabled="!with_edit" @change="updatedCell" class="form-control"/>
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
                                       :style="textSysStyle"
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
                                       :style="textSysStyle"
                                       v-model="requestRow[prefix_fld+'addressee_txt']"
                                       :disabled="!with_edit"
                                       @keyup="recreateFormul('formula_dcr_addressee_txt')"
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
                                   :style="textSysStyle"
                                   v-model="requestRow[prefix_fld+'email_message']"
                                   :disabled="!with_edit"
                                   @keyup="recreateFormul('formula_dcr_email_message')"
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
                            <select v-model="requestRow[prefix_fld+'email_format']" :disabled="!with_edit" @change="updatedCell" class="form-control" :style="textSysStyle">
                                <option value="table">Tabular (H)</option>
                                <option value="vertical">Form / Tabular (V)</option>
                                <option value="list">Listing</option>
                            </select>
                        </div>
                    </div>
                    <div class="td td--50 h-32" :style="getTdStyle">
                        <div class="flex flex--center-v full-height">
                            <label>{{ $root.uniqName(requestFields[prefix_fld+'email_col_group_id'].name) }}:&nbsp;</label>
                            <select-block
                                    :style="textSysStyle"
                                    :options="getCGrps()"
                                    :sel_value="requestRow[prefix_fld+'email_col_group_id']"
                                    :with_links="true"
                                    @option-select="(opt) => { requestRow[prefix_fld+'email_col_group_id'] = opt.val; updatedCell() }"
                                    @link-click="showGroupSetPop"
                            ></select-block>
                        </div>
                    </div>
                </td>
            </tr>

        </tbody>
    </table>
</template>

<script>
    import {eventBus} from "../../../../../app";

    import CellStyleMixin from "../../../../_Mixins/CellStyleMixin.vue";
    import ReqRowMixin from "./ReqRowMixin.vue";

    import FormulaHelper from "../../../../CustomCell/InCell/FormulaHelper";
    import SelectBlock from "../../../../CommonBlocks/SelectBlock";

    export default {
        components: {
            SelectBlock,
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
            tableMeta: Object,
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
                    ...this.textSysStyle,
                };
            },
            hasUniq() {
                return _.find(this.tableMeta._fields, {'is_unique_collection': 1})
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
            getCGrps() {
                let rrows = _.map(this.$root.tableMeta._column_groups, (rc) => {
                    return { val:rc.id, show:rc.name };
                });
                rrows.unshift({ val:null, show:"" });
                return rrows;
            },
            showGroupSetPop() {
                eventBus.$emit('show-grouping-settings-popup', this.tableMeta.db_name, 'row', this.requestRow[this.prefix_fld+'email_col_group_id']);
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