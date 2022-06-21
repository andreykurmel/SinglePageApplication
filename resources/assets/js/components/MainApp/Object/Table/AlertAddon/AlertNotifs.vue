<template>
    <div class="full-height permissions-tab" :style="textSysStyle">
        <div class="permissions-menu-header">
            <button class="btn btn-default btn-sm" :style="textSysStyle" :class="{active : activeTab === 'mails'}" @click="activeTab = 'mails'">
                Emails
            </button>
            <button class="btn btn-default btn-sm" :style="textSysStyle" :class="{active : activeTab === 'sms'}" @click="activeTab = 'sms'">
                SMS
            </button>
        </div>
        <div class="permissions-menu-body" style="border: 1px solid #CCC;">

            <div class="full-height permissions-panel" v-show="activeTab === 'mails'">
                <table class="spaced-table" style="table-layout: fixed">
                    <colgroup>
                        <col width="80px">
                        <col width="">
                    </colgroup>
                    <tbody>

                    <tr>
                        <td colspan="2" class="pad-bot"></td>
                    </tr>

                    <tr>
                        <td colspan="2">
                            <label>Recipients (email addresses. Use comma, semi-colon or space to separate multiple addresses):</label>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2" class="pad-bot">
                            <div class="flex flex--center-v full-height">
                                <label style="min-width: 45px">&nbsp;&nbsp;&nbsp;To:&nbsp;</label>
                                <select
                                        v-model="alert_sett.row_mail_field_id"
                                        :style="textSysStyle"
                                        :disabled="!can_edit"
                                        @change="sendUpdate()"
                                        class="form-control"
                                >
                                    <option></option>
                                    <option v-for="field in tableMeta._fields"
                                            v-if="inArray(field.f_type, ['String','Text','Long Text'])"
                                            :value="field.id">{{ $root.uniqName(field.name) }}</option>
                                </select>
                                <label>&nbsp;&nbsp;&nbsp;and:&nbsp;</label>
                                <input type="text" :style="textSysStyle" v-model="alert_sett.recipients" :disabled="!can_edit" @change="sendUpdate()" class="form-control"/>
                            </div>
                            <div class="flex flex--center-v full-height">
                                <label style="min-width: 45px">&nbsp;&nbsp;&nbsp;Cc:&nbsp;</label>
                                <select
                                        v-model="alert_sett.cc_row_mail_field_id"
                                        :style="textSysStyle"
                                        :disabled="!can_edit"
                                        @change="sendUpdate()"
                                        class="form-control"
                                >
                                    <option></option>
                                    <option v-for="field in tableMeta._fields"
                                            v-if="inArray(field.f_type, ['String','Text','Long Text'])"
                                            :value="field.id">{{ $root.uniqName(field.name) }}</option>
                                </select>
                                <label>&nbsp;&nbsp;&nbsp;and:&nbsp;</label>
                                <input type="text" :style="textSysStyle" v-model="alert_sett.cc_recipients" :disabled="!can_edit" @change="sendUpdate()" class="form-control"/>
                            </div>
                            <div class="flex flex--center-v full-height">
                                <label style="min-width: 45px">&nbsp;&nbsp;&nbsp;Bcc:&nbsp;</label>
                                <select
                                        v-model="alert_sett.bcc_row_mail_field_id"
                                        :style="textSysStyle"
                                        :disabled="!can_edit"
                                        @change="sendUpdate()"
                                        class="form-control"
                                >
                                    <option></option>
                                    <option v-for="field in tableMeta._fields"
                                            v-if="inArray(field.f_type, ['String','Text','Long Text'])"
                                            :value="field.id">{{ $root.uniqName(field.name) }}</option>
                                </select>
                                <label>&nbsp;&nbsp;&nbsp;and:&nbsp;</label>
                                <input type="text" :style="textSysStyle" v-model="alert_sett.bcc_recipients" :disabled="!can_edit" @change="sendUpdate()" class="form-control"/>
                            </div>
                        </td>
                    </tr>
                    
                    <tr>
                        <td class="pad-bot">
                            <label>Subject:</label>
                        </td>
                        <td class="pad-bot">
                            <div style="position: relative;">
                                <input type="text"
                                       :style="textSysStyle"
                                       v-model="alert_sett.mail_subject"
                                       :disabled="!can_edit"
                                       @keyup="recreaFormula('formula_mail_subject')"
                                       @focus="formula_mail_subject = true"
                                       class="form-control"/>
                                <formula-helper
                                        v-if="formula_mail_subject"
                                        :user="$root.user"
                                        :table-meta="tableMeta"
                                        :table-row="alert_sett"
                                        :header-key="'mail_subject'"
                                        :can-edit="true"
                                        :no-function="true"
                                        :no_prevent="true"
                                        :pop_width="'100%'"
                                        style="padding: 0;"
                                        @set-formula="sendUpdate"
                                ></formula-helper>
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td class="pad-bot">
                            <label>Addressee:</label>
                        </td>
                        <td class="pad-bot">
                            <div style="position: relative;">
                                <input type="text"
                                       :style="textSysStyle"
                                       v-model="alert_sett.mail_addressee"
                                       :disabled="!can_edit"
                                       @keyup="recreaFormula('formula_mail_addressee')"
                                       @focus="formula_mail_addressee = true"
                                       class="form-control"/>
                                <formula-helper
                                        v-if="formula_mail_addressee"
                                        :user="$root.user"
                                        :table-meta="tableMeta"
                                        :table-row="alert_sett"
                                        :header-key="'mail_addressee'"
                                        :can-edit="true"
                                        :no-function="true"
                                        :no_prevent="true"
                                        :pop_width="'100%'"
                                        style="padding: 0;"
                                        @set-formula="sendUpdate"
                                ></formula-helper>
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td class="pad-bot">
                            <label>Opening Message:</label>
                        </td>
                        <td class="pad-bot">
                            <div style="position: relative;">
                                <input type="text"
                                       :style="textSysStyle"
                                       v-model="alert_sett.mail_message"
                                       :disabled="!can_edit"
                                       @keyup="recreaFormula('formula_mail_message')"
                                       @focus="formula_mail_message = true"
                                       class="form-control"/>
                                <formula-helper
                                        v-if="formula_mail_message"
                                        :user="$root.user"
                                        :table-meta="tableMeta"
                                        :table-row="alert_sett"
                                        :header-key="'mail_message'"
                                        :can-edit="true"
                                        :no-function="true"
                                        :no_prevent="true"
                                        :pop_width="'100%'"
                                        style="padding: 0;"
                                        @set-formula="sendUpdate"
                                ></formula-helper>
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td class="pad-bot">
                            <label>Format:</label>
                        </td>
                        <td class="pad-bot">
                            <div class="flex flex--center-v">
                                <select v-model="alert_sett.mail_format" :style="textSysStyle" :disabled="!can_edit" @change="sendUpdate" class="form-control">
                                    <option value="table">Tabular (H)</option>
                                    <option value="vertical">Form / Tabular (V)</option>
                                    <option value="list">Listing</option>
                                </select>

                                <label style="width: 90px;flex-shrink: 0;">&nbsp;&nbsp;&nbsp;&nbsp;Col. Group:&nbsp;</label>
                                <select-block
                                    :options="colGroups()"
                                    :sel_value="alert_sett.mail_col_group_id"
                                    :style="{ height:'36px', ...textSysStyle }"
                                    :with_links="true"
                                    :is_disabled="!can_edit"
                                    :button_txt="'Add New'"
                                    @option-select="optUpdate"
                                    @link-click="showCol(alert_sett.mail_col_group_id)"
                                    @button-click="showCol(null)"
                                ></select-block>
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td class="pad-bot">
                            <label>Delay:</label>
                        </td>
                        <td class="pad-bot">
                            <div class="flex flex--center-v">
                                <input type="text" :style="textSysStyle" v-model="alert_sett.mail_delay_hour" :disabled="!can_edit" class="form-control" @change="sendUpdate"/>
                                <label style="width: 90px;flex-shrink: 0;">&nbsp;hours&nbsp;&nbsp;&nbsp;&nbsp;</label>
                                <input type="text" :style="textSysStyle" v-model="alert_sett.mail_delay_min" :disabled="!can_edit" class="form-control" @change="sendUpdate"/>
                                <label style="width: 90px;flex-shrink: 0;">&nbsp;minutes&nbsp;&nbsp;&nbsp;&nbsp;</label>
                                <input type="text" :style="textSysStyle" v-model="alert_sett.mail_delay_sec" :disabled="!can_edit" class="form-control" @change="sendUpdate"/>
                                <label style="width: 90px;flex-shrink: 0;">&nbsp;seconds&nbsp;&nbsp;&nbsp;&nbsp;</label>
                            </div>
                        </td>
                    </tr>
                    
                    </tbody>
                </table>
            </div>

            <div class="full-height permissions-panel" v-show="activeTab === 'sms'">
                <!--NONE-->
            </div>

        </div>
    </div>
</template>

<script>
    import FormulaHelper from "../../../../CustomCell/InCell/FormulaHelper";
    import SelectBlock from "../../../../CommonBlocks/SelectBlock";

    import CellStyleMixin from "../../../../_Mixins/CellStyleMixin";

    import {eventBus} from "../../../../../app";

    export default {
        name: "AlertNotifs",
        components: {
            SelectBlock,
            FormulaHelper,
        },
        mixins: [
            CellStyleMixin,
        ],
        data: function () {
            return {
                activeTab: 'mails',
                formula_mail_subject: false,
                formula_mail_addressee: false,
                formula_mail_message: false,
            }
        },
        props:{
            table_id: Number,
            tableMeta: Object,
            alert_sett: Object,
            can_edit: Boolean,
        },
        computed: {
        },
        watch: {
            table_id(val) {
                this.createSearchUser();
            }
        },
        methods: {
            inArray(type, array) {
                return array.indexOf(type) > -1;
            },
            //update
            showCol(id) {
                eventBus.$emit('show-grouping-settings-popup', this.tableMeta.db_name, 'col', id);
            },
            colGroups() {
                return _.map(this.tableMeta._column_groups, (tp) => {
                    return { val: tp.id, show: tp.name, }
                });
            },
            optUpdate(opt) {
                this.alert_sett.mail_col_group_id = opt.val;
                this.sendUpdate();
            },
            sendUpdate() {
                this.formula_mail_subject = false;
                this.formula_mail_addressee = false;
                this.formula_mail_message = false;
                this.$emit('update-alert', this.alert_sett);
            },
            createSearchUser() {
                if (this.$refs.search_user && $(this.$refs.search_user).hasClass('select2-hidden-accessible')) {
                    $(this.$refs.search_user).select2('destroy');
                }
                this.$nextTick(() => {
                    $(this.$refs.search_user).select2({
                        ajax: {
                            url: '/ajax/user/search-can-group',
                            dataType: 'json',
                            delay: 250,
                            data: (params) => {
                                return {
                                    q: params.term,
                                    request_field: 'email',
                                    table_id: this.table_id
                                }
                            },
                        },
                        minimumInputLength: {val:3},
                        width: '100%',
                        dropdownAutoWidth: true
                    });
                    $(this.$refs.search_user).next().css('height', '30px');
                });
            },
            addUserToRecipients() {
                let new_email = $(this.$refs.search_user).val();
                if (new_email) {
                    this.alert_sett.recipients = [
                        this.alert_sett.recipients,
                        new_email
                    ].join(";\n");
                }
                $(this.$refs.search_user).val(null).trigger('change');

                this.sendUpdate();
            },
            recreaFormula(param) {
                this[param] = false;
                this.$nextTick(() => {
                    this[param] = true;
                });
            },
        },
        mounted() {
            this.createSearchUser();
        },
        beforeDestroy() {
        }
    }
</script>

<style lang="scss" scoped>
    @import "./../SettingsModule/TabSettingsPermissions";

    .spaced-table {
        width: 100%;

        label {
            margin: 0;
        }

        .pad-bot {
            padding-bottom: 15px;
        }

        .search-user-wrapper {
            width: 100%;
            max-width: 380px;
        }
    }
    .btn-default {
        height: 30px;
    }
</style>