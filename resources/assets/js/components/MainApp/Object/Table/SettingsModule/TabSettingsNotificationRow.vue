<template>
    <table class="spaced-table" :style="bgColor">
        <tbody v-if="requestFields" :style="textColor">

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
                                        @close-formula="formula_dcr_confirm_msg = false"
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
                                        @close-formula="formula_dcr_unique_msg = false"
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
                            <label style="min-width: 65px">&nbsp;&nbsp;&nbsp;{{ $root.uniqName(requestFields[prefix_fld+'email_field_id'].name) }}:&nbsp;</label>
                            <select v-model="requestRow[prefix_fld+'email_field_id']" :disabled="!with_edit" @change="updatedCell" class="form-control" :style="textSysStyle">
                                <option :value="null" style="color: #bbb;"></option>
                                <option v-for="field in $root.tableMeta._fields"
                                        v-if="inArray(field.f_type, ['Email'])"
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
                            <label style="min-width: 65px">&nbsp;&nbsp;&nbsp;{{ $root.uniqName(requestFields[prefix_fld+'cc_email_field_id'].name) }}:&nbsp;</label>
                            <select v-model="requestRow[prefix_fld+'cc_email_field_id']" :disabled="!with_edit" @change="updatedCell" class="form-control" :style="textSysStyle">
                                <option :value="null" style="color: #bbb;"></option>
                                <option v-for="field in $root.tableMeta._fields"
                                        v-if="inArray(field.f_type, ['Email'])"
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
                            <label style="min-width: 65px">&nbsp;&nbsp;&nbsp;{{ $root.uniqName(requestFields[prefix_fld+'bcc_email_field_id'].name) }}:&nbsp;</label>
                            <select v-model="requestRow[prefix_fld+'bcc_email_field_id']" :disabled="!with_edit" @change="updatedCell" class="form-control" :style="textSysStyle">
                                <option :value="null" style="color: #bbb;"></option>
                                <option v-for="field in $root.tableMeta._fields"
                                        v-if="inArray(field.f_type, ['Email'])"
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
                                        @close-formula="formula_dcr_email_subject = false"
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
                                        @close-formula="formula_dcr_addressee_txt = false"
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
                                    @close-formula="formula_dcr_email_message = false"
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

            <tr>
                <td :style="getTdStyle">
                    <div class="td td--100 h-32" style="height: 55px;">
                        <div class="flex">
                            <label>{{ $root.uniqName(requestFields[prefix_fld+'signature_txt'].name) }}:&nbsp;</label>
                            <textarea
                                style="height: auto;"
                                class="form-control"
                                rows="3"
                                v-model="requestRow[prefix_fld+'signature_txt']"
                                :disabled="!with_edit"
                                :style="textSysStyle"
                                @change="updatedCell"
                            ></textarea>
                        </div>
                    </div>
                </td>
            </tr>

            <tr>
                <td :style="getTdStyle">
                    <div class="td td--100" style="font-weight: bold; border-top: 1px solid; padding-top: 5px;">
                        Select and add link(s) for the records that are to be included in the email:
                    </div>
                </td>
            </tr>
            <tr>
                <td :style="getTdStyle">
                    <div class="td td--100 h-32" style="height: auto; width: 100%; position: relative; padding-left: 30px;">
                        <custom-table
                            v-if="requestRow['_'+prefix_fld+'linked_notifs']"
                            :cell_component_name="'custom-cell-settings-dcr'"
                            :global-meta="tableMeta"
                            :table-meta="$root.settingsMeta['dcr_notif_linked_tables']"
                            :all-rows="requestRow['_'+prefix_fld+'linked_notifs']"
                            :rows-count="requestRow['_'+prefix_fld+'linked_notifs'].length"
                            :cell-height="1"
                            :max-cell-rows="0"
                            :is-full-width="true"
                            :with_edit="with_edit"
                            :behavior="'table_permission_group'"
                            :user="$root.user"
                            :adding-row="{ active: true, position: 'bottom'}"
                            @added-row="addDcrLink"
                            @updated-row="updateDcrLink"
                            @delete-row="deleteDcrLink"
                            @show-add-ddl-option="showRelColGroup"
                        ></custom-table>
                    </div>
                </td>
            </tr>

        </tbody>
    </table>
</template>

<script>
    import {eventBus} from "../../../../../app";

    import StyleMixinWithBg from "../../../../_Mixins/StyleMixinWithBg.vue";
    import ReqRowMixin from "./ReqRowMixin.vue";

    import SelectBlock from "../../../../CommonBlocks/SelectBlock";
    import CustomTable from "../../../../CustomTable/CustomTable.vue";

    export default {
        components: {
            CustomTable,
            SelectBlock,
        },
        mixins: [
            StyleMixinWithBg,
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
            bg_color: String,
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
            //Linked tables endpoints
            addDcrLink(tableRow) {
                $.LoadingOverlay('show');
                axios.post('/ajax/table-data-request/notif-linked', {
                    type: '_'+this.prefix_fld+'linked_notifs',
                    dcr_id: this.requestRow.id,
                    fields: this.dcrLinkFields(tableRow),
                }).then(({ data }) => {
                    this.requestRow['_'+this.prefix_fld+'linked_notifs'] = data;
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
            updateDcrLink(tableRow) {
                $.LoadingOverlay('show');
                axios.put('/ajax/table-data-request/notif-linked', {
                    type: '_'+this.prefix_fld+'linked_notifs',
                    id: tableRow.id,
                    fields: this.dcrLinkFields(tableRow),
                }).then(({ data }) => {
                    this.requestRow['_'+this.prefix_fld+'linked_notifs'] = data;
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
            deleteDcrLink(tableRow) {
                $.LoadingOverlay('show');
                axios.delete('/ajax/table-data-request/notif-linked', {
                    params: {
                        type: '_'+this.prefix_fld+'linked_notifs',
                        id: tableRow.id
                    }
                }).then(({ data }) => {
                    this.requestRow['_'+this.prefix_fld+'linked_notifs'] = data;
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
            dcrLinkFields(tableRow) {
                let fields = _.cloneDeep(tableRow);//copy object
                this.$root.deleteSystemFields(fields);

                switch (this.prefix_fld) {
                    case 'dcr_save_': fields.type = 'save'; break;
                    case 'dcr_upd_': fields.type = 'update'; break;
                    case 'dcr_':
                    default: fields.type = 'submit'; break;
                }

                return fields;
            },
            showRelColGroup(tableId, colGroupId) {
                this.$emit('show-add-ddl-option', tableId, colGroupId);
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