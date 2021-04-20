<template>
    <div class="full-height permissions-tab">
        <div class="permissions-menu-header">
            <button class="btn btn-default btn-sm" :class="{active : activeTab === 'mails'}" @click="activeTab = 'mails'">
                Emails
            </button>
            <button class="btn btn-default btn-sm" :class="{active : activeTab === 'sms'}" @click="activeTab = 'sms'">
                SMS
            </button>
        </div>
        <div class="permissions-menu-body" style="border: 1px solid #CCC;">

            <div class="full-height permissions-panel" v-show="activeTab === 'mails'">
                <table class="spaced-table" style="table-layout: fixed">
                    <colgroup>
                        <col :width="100">
                        <col :width="300">
                    </colgroup>
                    <tbody>

                    <tr>
                        <td colspan="2" class="pad-bot"></td>
                    </tr>
                    
                    <tr>
                        <td class="pad-bot" style="vertical-align: top;">
                            <label>Recipients:</label>
                        </td>
                        <td class="pad-bot">
                            <div class="form-group">
                                <textarea rows="3"
                                          class="form-control"
                                          v-model="alert_sett.recipients"
                                          @change="sendUpdate()"
                                ></textarea>
                                <div class="flex flex--center-v">
                                    <div class="search-user-wrapper">
                                        <select ref="search_user"></select>
                                    </div>
                                    <button class="btn btn-primary btn-sm blue-gradient" :style="$root.themeButtonStyle" @click="addUserToRecipients()">Add</button>
                                </div>
                            </div>

                            <div class="">
                                <label>Send record specific A&N to:</label>
                                <select
                                        v-model="alert_sett.row_mail_field_id"
                                        @change="sendUpdate()"
                                        class="form-control"
                                >
                                    <option></option>
                                    <option v-for="field in tableMeta._fields" :value="field.id">{{ $root.uniqName(field.name) }}</option>
                                </select>
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
                                       v-model="alert_sett.mail_subject"
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
                                       v-model="alert_sett.mail_addressee"
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
                                       v-model="alert_sett.mail_message"
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
                                <select v-model="alert_sett.mail_format" @change="sendUpdate" class="form-control">
                                    <option value="table">Tabular (H)</option>
                                    <option value="vertical">Form / Tabular (V)</option>
                                    <option value="list">Listing</option>
                                </select>

                                <label style="width: 90px;flex-shrink: 0;">&nbsp;&nbsp;&nbsp;&nbsp;Col. Group:&nbsp;</label>
                                <select v-model="alert_sett.mail_col_group_id" @change="sendUpdate" class="form-control">
                                    <option></option>
                                    <option v-for="colGr in tableMeta._column_groups" :value="colGr.id">{{ colGr.name }}</option>
                                </select>
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

    export default {
        name: "AlertNotifs",
        components: {
            FormulaHelper,
        },
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
        },
        computed: {
        },
        watch: {
            table_id(val) {
                this.createSearchUser();
            }
        },
        methods: {
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
                        minimumInputLength: 3,
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
</style>