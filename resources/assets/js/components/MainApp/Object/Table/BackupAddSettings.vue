<template>
    <table class="spaced-table" v-if="tableMeta && tbBackup">
        <tbody v-if="requestFields">

        <tr>
            <td :style="getTdStyle">
                <div class="td td--100 h-32" :style="getTdStyle">
                    <div class="flex flex--center-v full-height">
                        <label :style="{width: label_wi}">{{ $root.uniqName(requestFields['bkp_email_field_id'].name) }}:&nbsp;</label>
                        <input
                            type="text"
                            v-model="tbBackup['bkp_email_field_static']"
                            :disabled="!with_edit" @change="updateBackup"
                            class="form-control"
                            placeholder="Enter email addresses separated with comma, space or semicolon."/>
                    </div>
                </div>
            </td>
        </tr>

        <tr>
            <td :style="getTdStyle">
                <div class="td td--100" :style="getTdStyle">
                    <div class="flex full-height">
                        <label :style="{width: label_wi}">{{ $root.uniqName(requestFields['bkp_email_subject'].name) }}:&nbsp;</label>
                        <div class="full-width full-height" style="position: relative;">
                            <textarea
                                    rows="2"
                                    v-model="tbBackup['bkp_email_subject']"
                                    :disabled="!with_edit"
                                    @keyup="recreateFrm('formula_dcr_email_subject')"
                                    @focus="formula_dcr_email_subject = true"
                                    class="form-control"
                                    placeholder="Use formula to generate a email subject with field value(s)."
                            ></textarea>
                            <formula-helper
                                    v-if="formula_dcr_email_subject"
                                    :user="$root.user"
                                    :table-meta="tableMeta"
                                    :table-row="tbBackup"
                                    :header-key="'bkp_email_subject'"
                                    :can-edit="with_edit"
                                    :no-function="true"
                                    :no_prevent="true"
                                    :pop_width="'100%'"
                                    @set-formula="updateBackup"
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
                        <label :style="{width: label_wi}">{{ $root.uniqName(requestFields['bkp_addressee_txt'].name) }}:&nbsp;</label>
                        <div class="full-width full-height" style="position: relative;">
                            <input type="text"
                                   v-model="tbBackup['bkp_addressee_txt']"
                                   :disabled="!with_edit"
                                   @keyup="recreateFrm('formula_dcr_addressee_txt')"
                                   @focus="formula_dcr_addressee_txt = true"
                                   class="form-control"
                                   placeholder="Use formula to generate a addressee with field value(s)."/>
                            <formula-helper
                                    v-if="formula_dcr_addressee_txt"
                                    :user="$root.user"
                                    :table-meta="tableMeta"
                                    :table-row="tbBackup"
                                    :header-key="'bkp_addressee_txt'"
                                    :can-edit="with_edit"
                                    :no-function="true"
                                    :no_prevent="true"
                                    :pop_width="'100%'"
                                    @set-formula="updateBackup"
                            ></formula-helper>
                        </div>
                    </div>
                </div>
            </td>
        </tr>

        <tr>
            <td :style="getTdStyle">
                <div class="td td--100" :style="getTdStyle">
                    <div class="flex full-height">
                        <label :style="{width: label_wi}">{{ $root.uniqName(requestFields['bkp_email_message'].name) }}:&nbsp;</label>
                        <textarea
                                rows="3"
                                v-model="tbBackup['bkp_email_message']"
                                :disabled="!with_edit"
                                @keyup="recreateFrm('formula_dcr_email_message')"
                                @focus="formula_dcr_email_message = true"
                                class="form-control"
                                placeholder="Use formula to generate an opening message with field value(s)."
                        ></textarea>
                        <formula-helper
                                v-if="formula_dcr_email_message"
                                :user="$root.user"
                                :table-meta="tableMeta"
                                :table-row="tbBackup"
                                :header-key="'bkp_email_message'"
                                :can-edit="with_edit"
                                :no-function="true"
                                :no_prevent="true"
                                :pop_width="'100%'"
                                @set-formula="updateBackup"
                        ></formula-helper>
                    </div>
                </div>
            </td>
        </tr>

        </tbody>
    </table>
</template>

<script>
    import CellStyleMixin from "../../../_Mixins/CellStyleMixin.vue";

    import FormulaHelper from "./../../../CustomCell/InCell/FormulaHelper";

    export default {
        name: "BackupAddSettings",
        mixins: [
            CellStyleMixin,
        ],
        components: {
            FormulaHelper,
        },
        data: function () {
            return {
                label_wi: '75px',
                i_avail_fields: [
                    'bkp_email_field_id',
                    'bkp_email_field_static',
                    'bkp_email_subject',
                    'bkp_addressee_field_id',
                    'bkp_addressee_txt',
                    'bkp_email_message',
                ],
                requestFields: null,
                //
                formula_dcr_email_subject: false,
                formula_dcr_addressee_txt: false,
                formula_dcr_email_message: false,
            }
        },
        props:{
            tableMeta: Object,
            tbBackup: Object,
        },
        computed: {
            getTdStyle() {
                return {
                    height: this.tdCellHGT+'px',
                };
            },
            with_edit() {
                return !!this.tableMeta._is_owner;
            },
        },
        watch: {
            'tableMeta.id': function(val) {
                this.setAvailFields();
            }
        },
        methods: {
            inArray(type, array) {
                return array.indexOf(type) > -1;
            },
            recreateFrm(param) {
                this[param] = false;
                this.$nextTick(() => {
                    this[param] = true;
                });
            },
            updateBackup() {
                this.formula_dcr_email_subject = false;
                this.formula_dcr_addressee_txt = false;
                this.formula_dcr_email_message = false;

                this.$root.sm_msg_type = 1;

                let row_id = this.tbBackup.id;
                let fields = _.cloneDeep(this.tbBackup);//copy object
                this.$root.deleteSystemFields(fields);

                axios.put('/ajax/table/backup', {
                    table_backup_id: row_id,
                    fields: fields
                }).then(({ data }) => {
                    this.$forceUpdate();
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
            setAvailFields() {
                this.requestFields = null;
                this.$nextTick(() => {

                    this.requestFields = {};
                    _.each(this.i_avail_fields, (fld_key) => {
                        this.requestFields[fld_key] = _.find(this.$root.settingsMeta.table_backups._fields, {field: fld_key}) || {};
                    });

                });
            },
        },
        mounted() {
            this.setAvailFields();
        },
        beforeDestroy() {
        }
    }
</script>

<style lang="scss" scoped>
    @import "./SettingsModule/ReqRowStyle";

    .spaced-table {
        td {
            label {
                white-space: normal;
                flex-shrink: 0;
            }
        }
    }
</style>