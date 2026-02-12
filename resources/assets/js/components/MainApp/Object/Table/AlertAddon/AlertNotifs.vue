<template>
    <div class="full-height permissions-tab" :style="textSysStyle">
        <div class="permissions-menu-header">
            <button class="btn btn-default btn-sm" :style="textSysStyle" :class="{active : activeTab === 'mails'}" @click="changeTab('mails')">
                Emails
            </button>
            <button class="btn btn-default btn-sm" :style="textSysStyle" :class="{active : activeTab === 'sms'}" @click="changeTab('sms')">
                SMS
            </button>

            <div v-if="activeTab" class="flex flex--center-v flex--end" style="position: absolute; top: 0; right: 0; height: 32px;" :style="textSysStyleSmart">
                <span>Enabled:&nbsp;</span>
                <label class="switch_t">
                    <input type="checkbox" :checked="sliderKey()" :disabled="!can_edit" @click="sliderChange()">
                    <span class="toggler round" :class="{'disabled': !can_edit}"></span>
                </label>
            </div>
        </div>
        <div class="permissions-menu-body" style="border: 1px solid #CCC; right: 1px;">

            <div class="full-frame pl5" v-show="activeTab === 'mails' && alert_sett.enabled_email" :style="$root.themeMainBgStyle">
                <table class="spaced-table" style="table-layout: fixed; width: calc(100% - 11px)" :style="$root.themeMainBgStyle">
                    <colgroup>
                        <col width="100px">
                        <col width="">
                    </colgroup>
                    <tbody :style="textSysStyleSmart">

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
                                <label class="pad-left" style="min-width: 55px">To:&nbsp;</label>
                                <select
                                        v-model="alert_sett.row_mail_field_id"
                                        :style="textSysStyle"
                                        :disabled="!can_edit"
                                        @change="sendUpdate()"
                                        class="form-control"
                                >
                                    <option></option>
                                    <option v-for="field in tableMeta._fields"
                                            v-if="inArray(field.f_type, ['Email'])"
                                            :value="field.id">{{ $root.uniqName(field.name) }}</option>
                                </select>
                                <label>&nbsp;&nbsp;&nbsp;and:&nbsp;</label>
                                <input type="text" :style="textSysStyle" v-model="alert_sett.recipients" :disabled="!can_edit" @change="sendUpdate()" class="form-control"/>
                            </div>
                            <div class="flex flex--center-v full-height">
                                <label class="pad-left" style="min-width: 55px">Cc:&nbsp;</label>
                                <select
                                        v-model="alert_sett.cc_row_mail_field_id"
                                        :style="textSysStyle"
                                        :disabled="!can_edit"
                                        @change="sendUpdate()"
                                        class="form-control"
                                >
                                    <option></option>
                                    <option v-for="field in tableMeta._fields"
                                            v-if="inArray(field.f_type, ['Email'])"
                                            :value="field.id">{{ $root.uniqName(field.name) }}</option>
                                </select>
                                <label>&nbsp;&nbsp;&nbsp;and:&nbsp;</label>
                                <input type="text" :style="textSysStyle" v-model="alert_sett.cc_recipients" :disabled="!can_edit" @change="sendUpdate()" class="form-control"/>
                            </div>
                            <div class="flex flex--center-v full-height">
                                <label class="pad-left" style="min-width: 55px">Bcc:&nbsp;</label>
                                <select
                                        v-model="alert_sett.bcc_row_mail_field_id"
                                        :style="textSysStyle"
                                        :disabled="!can_edit"
                                        @change="sendUpdate()"
                                        class="form-control"
                                >
                                    <option></option>
                                    <option v-for="field in tableMeta._fields"
                                            v-if="inArray(field.f_type, ['Email'])"
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
                                        style="padding: 0; color: #333;"
                                        @close-formula="formula_mail_subject = false"
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
                                        style="padding: 0; color: #333;"
                                        @close-formula="formula_mail_addressee = false"
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
                                        style="padding: 0; color: #333;"
                                        @close-formula="formula_mail_message = false"
                                        @set-formula="sendUpdate"
                                ></formula-helper>
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td class="pad-bot" colspan="2">
                            <label>Content:</label>
                        </td>
                    </tr>

                    <!-- Pad Left -->
                    <tr>
                        <td class="pad-bot pad-left" colspan="2">
                            <div class="flex flex--center-v">
                                <label class="switch_t" :style="{height: Math.min(maxCellHGT, 17)+'px'}">
                                    <input type="checkbox" :checked="alert_sett.notif_email_add_tabledata" :disabled="!can_edit" @click="sendBoolUpdate('notif_email_add_tabledata')">
                                    <span class="toggler round" :class="{'disabled': !can_edit}"></span>
                                </label>
                                <label>&nbsp;Record data</label>
                            </div>
                        </td>
                    </tr>

                    <tr v-show="alert_sett.notif_email_add_tabledata">
                        <td class="pad-bot pad-left-2">
                            <label>Format:</label>
                        </td>
                        <td class="pad-bot">
                            <div class="flex flex--center-v">
                                <select v-model="alert_sett.mail_format" :style="textSysStyle" :disabled="!can_edit" @change="sendUpdate" class="form-control">
                                    <option value="table">Tabular (H)</option>
                                    <option value="vertical">Form / Tabular (V)</option>
                                    <option value="list">Listing</option>
                                </select>

                                <label style="white-space: nowrap;">&nbsp;&nbsp;Col. Group:&nbsp;</label>
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
                        <td class="pad-bot pad-left" colspan="2">
                            <div class="flex flex--center-v">
                                <label class="switch_t" :style="{height: Math.min(maxCellHGT, 17)+'px'}">
                                    <input type="checkbox" :checked="alert_sett.notif_email_add_clicklink" :disabled="!can_edit" @click="sendBoolUpdate('notif_email_add_clicklink')">
                                    <span class="toggler round" :class="{'disabled': !can_edit}"></span>
                                </label>
                                <label>&nbsp;A link for clicking to update field values:</label>
                            </div>
                        </td>
                    </tr>

                    <tr v-show="alert_sett.notif_email_add_clicklink">
                        <td class="pad-bot pad-left-2" colspan="2">
                            <custom-table
                                :cell_component_name="'custom-cell-alert-notif'"
                                :global-meta="tableMeta"
                                :table-meta="$root.settingsMeta['table_alert_click_updates']"
                                :all-rows="alert_sett._click_updates"
                                :rows-count="alert_sett._click_updates.length"
                                :cell-height="1"
                                :max-cell-rows="0"
                                :is-full-width="true"
                                :can_edit="can_edit"
                                :user="$root.user"
                                :behavior="'alert_notif_clicktoupdate'"
                                :adding-row="{ active: true, position: 'bottom', }"
                                :available-columns="['table_field_id','new_value']"
                                :use_theme="true"
                                @added-row="addClickUpdate"
                                @updated-row="updateClickUpdate"
                                @delete-row="deleteClickUpdate"
                            ></custom-table>
                        </td>
                    </tr>

                    <tr v-show="alert_sett.notif_email_add_clicklink">
                        <td colspan="2" class="pad-bot pad-left-2">
                            <div class="flex flex--center-v no-wrap">
                                <label>Introductory message for the link:&nbsp;</label>
                            </div>
                        </td>
                    </tr>

                    <tr v-show="alert_sett.notif_email_add_clicklink">
                        <td colspan="2" class="pad-bot pad-left-2">
                            <div class="flex flex--center-v no-wrap">
                                <input type="text" :style="textSysStyle" v-model="alert_sett.click_introduction" :disabled="!can_edit" @change="sendUpdate()" class="form-control"/>
                                <input type="text" :style="textSysStyle" :value="alertClickLink" disabled class="form-control"/>
                            </div>
                        </td>
                    </tr>

                    <tr v-show="alert_sett.notif_email_add_clicklink">
                        <td colspan="2" class="pad-bot pad-left-2">
                            <div class="flex flex--center-v no-wrap">
                                <label>Message confirming successful updating:&nbsp;</label>
                                <input type="text" :style="textSysStyle" v-model="alert_sett.click_success_message" :disabled="!can_edit" @change="sendUpdate()" class="form-control"/>
                            </div>
                        </td>
                    </tr>
                    <!-- ^^^^^ Pad Left ^^^^^ -->

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

            <div class="full-height permissions-panel" v-show="activeTab === 'sms' && alert_sett.enabled_sms" :style="$root.themeMainBgStyle">
                <table class="spaced-table" style="table-layout: fixed" :style="$root.themeMainBgStyle">
                    <colgroup>
                        <col width="80px">
                        <col width="">
                    </colgroup>
                    <tbody :style="textSysStyleSmart">

                    <tr>
                        <td colspan="2" class="pad-bot"></td>
                    </tr>

                    <tr>
                        <td colspan="2" class="pad-bot">
                            <div class="flex flex--center-v full-height">
                                <label>Recipients&nbsp;To:&nbsp;</label>
                                <select
                                    v-model="alert_sett.row_sms_field_id"
                                    :style="textSysStyle"
                                    :disabled="!can_edit"
                                    @change="sendUpdate()"
                                    class="form-control"
                                >
                                    <option></option>
                                    <option v-for="field in tableMeta._fields"
                                            v-if="inArray(field.f_type, ['Phone Number'])"
                                            :value="field.id">{{ $root.uniqName(field.name) }}</option>
                                </select>
                                <label>&nbsp;&nbsp;&nbsp;and:&nbsp;</label>
                                <input type="text" :style="textSysStyle" v-model="alert_sett.sms_recipients" :disabled="!can_edit" @change="sendUpdate()" class="form-control"/>
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td colspan="2" class="pad-bot">
                            <div style="position: relative;">
                                <textarea rows="5"
                                          :style="textSysStyle"
                                          v-model="alert_sett.sms_body"
                                          :disabled="!can_edit"
                                          @keyup="recreaFormula('formula_sms_body')"
                                          @focus="formula_sms_body = true"
                                          class="form-control"
                                ></textarea>
                                <formula-helper
                                    v-if="formula_sms_body"
                                    :user="$root.user"
                                    :table-meta="tableMeta"
                                    :table-row="alert_sett"
                                    :header-key="'sms_body'"
                                    :can-edit="true"
                                    :no-function="true"
                                    :no_prevent="true"
                                    :pop_width="'100%'"
                                    style="padding: 0; color: #333;"
                                    @close-formula="formula_sms_body = false"
                                    @set-formula="sendUpdate"
                                ></formula-helper>
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td class="pad-bot">
                            <label>Delay:</label>
                        </td>
                        <td class="pad-bot">
                            <div class="flex flex--center-v">
                                <input type="text" :style="textSysStyle" v-model="alert_sett.sms_delay_hour" :disabled="!can_edit" class="form-control" @change="sendUpdate"/>
                                <label style="width: 90px;flex-shrink: 0;">&nbsp;hours&nbsp;&nbsp;&nbsp;&nbsp;</label>
                                <input type="text" :style="textSysStyle" v-model="alert_sett.sms_delay_min" :disabled="!can_edit" class="form-control" @change="sendUpdate"/>
                                <label style="width: 90px;flex-shrink: 0;">&nbsp;minutes&nbsp;&nbsp;&nbsp;&nbsp;</label>
                                <input type="text" :style="textSysStyle" v-model="alert_sett.sms_delay_sec" :disabled="!can_edit" class="form-control" @change="sendUpdate"/>
                                <label style="width: 90px;flex-shrink: 0;">&nbsp;seconds&nbsp;&nbsp;&nbsp;&nbsp;</label>
                            </div>
                        </td>
                    </tr>

                    </tbody>
                </table>
            </div>

        </div>
    </div>
</template>

<script>
    import {eventBus} from "../../../../../app";

    import CellStyleMixin from "../../../../_Mixins/CellStyleMixin";

    import SelectBlock from "../../../../CommonBlocks/SelectBlock";
    import CustomTable from "../../../../CustomTable/CustomTable.vue";

    export default {
        name: "AlertNotifs",
        components: {
            CustomTable,
            SelectBlock,
        },
        mixins: [
            CellStyleMixin,
        ],
        data: function () {
            return {
                activeTab: 'mails',
                formula_sms_body: false,
                formula_mail_subject: false,
                formula_mail_addressee: false,
                formula_mail_message: false,
            }
        },
        props: {
            table_id: Number,
            tableMeta: Object,
            alert_sett: Object,
            can_edit: Boolean|Number,
        },
        computed: {
            alertClickLink() {
                let firstRow = _.first(this.$root.listTableRows);
                return this.$root.clear_url + '/ana_click?link=' + uuidv4() + '_' + this.alert_sett.id + '_' + (firstRow ? firstRow.id : 1) + '_' + uuidv4();
            },
        },
        watch: {
            table_id(val) {
                this.createSearchUser();
            }
        },
        methods: {
            sliderKey() {
                let key = '';
                switch (this.activeTab) {
                    case 'mails': key = 'enabled_email'; break;
                    case 'sms': key = 'enabled_sms'; break;
                }
                return this.alert_sett[key];
            },
            sliderChange() {
                switch (this.activeTab) {
                    case 'mails': this.alert_sett.enabled_email = this.alert_sett.enabled_email ? 0 : 1; break;
                    case 'sms': this.alert_sett.enabled_sms = this.alert_sett.enabled_sms ? 0 : 1; break;
                }
                this.sendUpdate();
            },
            changeTab(key) {
                this.activeTab = key;
                this.$emit('set-sub-tab', key);
            },
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
            sendBoolUpdate(key) {
                this.alert_sett[key] = this.alert_sett[key] ? 0 : 1;
                this.sendUpdate();
            },
            sendUpdate() {
                this.formula_sms_body = false;
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
            addClickUpdate(tableRow) {
                let fields = _.cloneDeep(tableRow);//copy object
                this.$root.deleteSystemFields(fields);

                $.LoadingOverlay('show');
                axios.post('/ajax/table/alert/click-to-update', {
                    table_alert_id: this.alert_sett.id,
                    fields: fields
                }).then(({ data }) => {
                    this.alert_sett._click_updates = data;
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
            updateClickUpdate(tableRow) {
                let fields = _.cloneDeep(tableRow);//copy object
                this.$root.deleteSystemFields(fields);

                $.LoadingOverlay('show');
                axios.put('/ajax/table/alert/click-to-update', {
                    table_alert_id: this.alert_sett.id,
                    id: tableRow.id,
                    fields: fields
                }).then(({ data }) => {
                    this.alert_sett._click_updates = data;
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
            deleteClickUpdate(tableRow) {
                $.LoadingOverlay('show');
                axios.delete('/ajax/table/alert/click-to-update', {
                    params: {
                        table_alert_id: this.alert_sett.id,
                        id: tableRow.id,
                    }
                }).then(({ data }) => {
                    this.alert_sett._click_updates = data;
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
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
    .permissions-tab {
        position: relative;
    }

    .spaced-table {
        width: 100%;

        label {
            margin: 0;
        }

        .pad-bot {
            padding-bottom: 15px;
        }

        .pad-left {
            padding-left: 15px;
        }
        .pad-left-2 {
            padding-left: 30px;
        }

        .search-user-wrapper {
            width: 100%;
            max-width: 380px;
        }
    }
</style>