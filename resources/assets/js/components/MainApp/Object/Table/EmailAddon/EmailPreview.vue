<template>
    <div class="full-height preview-wrap">
        <template v-if="incorrect_settings">
            <div class="form-group label_blocks" :style="textSysStyleSmart">
                <label>Server/email settings are incorrect!</label>
                <label v-if="emailSettings.server_type === 'sendgrid' && !emailSettings.sender_email && !emailSettings.sender_email_fld_id">
                    <span>Please check 'Sender Email'.</span>
                </label>
                <label v-if="!emailSettings.recipient_field_id && !emailSettings.recipient_email">
                    <span>Please check 'Recipients'.</span>
                </label>
                <label v-if="emailSettings.server_type === 'sendgrid' && !emailSettings.sender_reply_to && !emailSettings.sender_reply_to_fld_id">
                    <span>'Reply To' is required.</span>
                </label>
                <label v-if="emailSettings.server_type === 'sendgrid' && emailSettings.smtp_key_mode === 'account' && !emailSettings.acc_sendgrid_key_id">
                    <span>Empty 'Api Key'.</span>
                </label>
                <label v-if="emailSettings.server_type === 'sendgrid' && emailSettings.smtp_key_mode === 'table' && !emailSettings.sendgrid_api_key">
                    <span>Empty 'Api Key'.</span>
                </label>
                <label v-if="emailSettings.server_type === 'google' && emailSettings.smtp_key_mode === 'account' && !emailSettings.acc_google_key_id">
                    <span>Empty 'Email' or 'App Password'.</span>
                </label>
                <label v-if="emailSettings.server_type === 'google' && emailSettings.smtp_key_mode === 'table' && !emailSettings.google_email && !emailSettings.google_app_pass">
                    <span>Empty 'Email' or 'App Password'.</span>
                </label>
                <label>Or Row Group is not found!</label>
            </div>
        </template>
        <div v-else-if="all_rows && all_rows.length" class="tab-settings">
            <div class="menu-header" style="width: 415px;height: 5px;">
                <button class="btn btn-default btn-sm pull-right"
                        :class="{active : acttab === 'single'}"
                        @click="changeActtab('single')"
                >Single</button>
                <button class="btn btn-default btn-sm pull-right"
                        :class="{active : acttab === 'all'}"
                        :disabled="all_rows.length > 50"
                        :title="all_rows.length > 50 ? 'Maximum emails = 50' : ''"
                        @click="changeActtab('all')"
                >All</button>
            </div>
            <div class="menu-body">
                <div v-if="acttab === 'single'" class="full-height body-view flex">
                    <div style="width: 25%; border-right: 1px solid #CCC;">
                        <select class="form-control"
                                v-model="emailSettings.preview_listing_id"
                                @change="sendUpdate(emailSettings, 'preview_listing_id')"
                        >
                            <option value="id">ID</option>
                            <option v-for="fld in tableMeta._fields"
                                    v-if="!$root.inArray(fld.field, $root.systemFields)"
                                    :value="fld.id"
                            >{{ $root.uniqName(fld.name) }}</option>
                        </select>
                        <div class="many-rows-content">
                            <div v-for="row in all_rows" :class="[(row.id === selected_id ? 'active' : '')]" @click="cngManyRow(row)">
                                <label v-html="shwManyRows(row)"></label>
                            </div>
                        </div>
                    </div>
                    <div style="width: 75%; overflow: auto;">
                        <email-send-block
                            v-if="selPreview"
                            :table-meta="tableMeta"
                            :selected_addon="emailSettings"
                            :total_emails="total_emails"
                            :email_row_id="selected_id"
                            :can_edit="can_edit"
                            :has-history="hasHistory(selected_id)"
                            @update-addon="sendUpdate"
                            @load-history="getPreview"
                        ></email-send-block>
                        <email-preview-element
                            v-if="selPreview"
                            :element="selPreview"
                            :table-meta="tableMeta"
                            :email-settings="emailSettings"
                            :short-view="true"
                            style="margin: 5px;"
                            @history-delete="clearHistory"
                        ></email-preview-element>
                    </div>
                </div>

                <div v-if="acttab === 'all'" class="full-height body-view">
                    <div class="allow_resend">
                        <span class="indeterm_check__wrap">
                            <span class="indeterm_check" @click="boolUpdate('allow_resending')">
                                <i v-if="emailSettings.allow_resending" class="glyphicon glyphicon-ok group__icon"></i>
                            </span>
                        </span>
                        <label>Allow resending a sent email</label>
                    </div>
                    <email-send-block
                        :table-meta="tableMeta"
                        :selected_addon="emailSettings"
                        :total_emails="total_emails"
                        :can_edit="can_edit"
                        :has-history="hasHistory()"
                        @update-addon="sendUpdate"
                        @load-history="getPreview"
                    ></email-send-block>
                    <div v-for="prev in preview_emails">
                        <email-preview-element
                            :element="prev"
                            :table-meta="tableMeta"
                            :email-settings="emailSettings"
                            :short-view="true"
                            class="form-group"
                        ></email-preview-element>
                    </div>
                </div>
            </div>
        </div>
        <template v-else="">
            <div class="form-group">
                <label>Loading ...</label>
            </div>
        </template>
    </div>
</template>

<script>
    import {SpecialFuncs} from "../../../../../classes/SpecialFuncs";

    import EmailPreviewElement from "./EmailPreviewElement";
    import EmailSendBlock from "./EmailSendBlock";

    import CellStyleMixin from "../../../../_Mixins/CellStyleMixin.vue";

    export default {
        name: "EmailPreview",
        mixins: [
            CellStyleMixin,
        ],
        components: {
            EmailSendBlock,
            EmailPreviewElement,
        },
        data: function () {
            return {
                selected_id: null,

                incorrect_settings: false,
                preview_emails: {},
                all_rows: null,
                acttab: 'single',
            }
        },
        props:{
            tableMeta: Object,
            emailSettings: Object,
            total_emails: Number,
            can_edit: Boolean|Number,
        },
        computed: {
            selPreview() {
                return this.preview_emails[this.selected_id];
            },
        },
        methods: {
            //Changes
            hasHistory(selected_id) {
                let find = _.find(this.preview_emails, (prev) => {
                    if (selected_id) {
                        return prev.history && prev.history.length && _.find(prev.history, {row_id: selected_id});
                    } else {
                        return prev.history && prev.history.length;
                    }
                });
                return !!find;
            },
            changeActtab(str) {
                this.acttab = str;
                if (str == 'all' && this.all_rows.length <= 50) {
                    this.getPreview();
                }
            },
            boolUpdate(key) {
                if (!this.can_edit) {
                    return;
                }
                this.emailSettings[key] = !this.emailSettings[key];
                this.sendUpdate(this.emailSettings, 'allow_resending');
            },
            sendUpdate(addonSettings, type) {
                if (!this.can_edit) {
                    return;
                }
                this.$emit('update-addon', addonSettings, type);
            },
            //Single
            cngManyRow(row) {
                this.selected_id = row ? row.id : null;
                if (!this.selPreview) {
                    this.getPreview();
                }
            },
            shwManyRows(row) {
                let header = _.find(this.tableMeta._fields, {id: this.emailSettings.preview_listing_id});
                if (header) {
                    let val = row[header.field];
                    if (val && this.$root.isMSEL(header.input_type)) {
                        let arr = SpecialFuncs.parseMsel(val);
                        val = '';
                        _.each(arr, (el) => {
                            val += '<span class="is_select">'+el+'</span> ';
                        });
                    }
                    if ($.inArray(header.input_type, this.$root.ddlInputTypes) > -1) {
                        val = this.$root.rcShow(row, header.field);
                    }
                    return val;
                } else {
                    return row.id;
                }
            },
            //Preview
            getPreview(special) {
                this.incorrect_settings = false;
                axios.post('/ajax/addon-email-sett/preview', {
                    email_add_id: this.emailSettings.id,
                    row_id: this.acttab === 'single' ? this.selected_id : null,
                    special: special || '',
                }).then(({data}) => {
                    let rows = data && data.all_rows && data.all_rows.length;
                    let prevs = special || (data && data.previews);
                    if (rows && prevs) {
                        if (!this.all_rows) {
                            this.all_rows = data.all_rows;
                            this.cngManyRow(_.first(this.all_rows));
                        }
                        this.$root.assignObject(data.previews, this.preview_emails);
                    } else {
                        this.incorrect_settings = true;
                    }
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                });
            },
            clearHistory(hist_id) {
                if (!this.can_edit) {
                    return;
                }
                axios.delete('/ajax/addon-email-sett/history', {
                    params: {
                        email_add_id: this.emailSettings.id,
                        history_id: hist_id,
                    },
                }).then(({data}) => {
                    this.getPreview();
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                });
            },
        },
        mounted() {
            this.getPreview('initial');
        },
        beforeDestroy() {
        }
    }
</script>

<style lang="scss" scoped>
    @import "./../SettingsModule/TabSettings";

    .preview-wrap {
        label {
            margin: 0;
        }
        .form-group {
            border: 1px solid #ccd0d2;
            border-radius: 4px;
            padding: 5px;
            font-size: 14px;
        }
        .label_blocks {
            label {
                display: block;
            }
        }

        .menu-body {
            padding: 0;

            .body-view {
                position: relative;
                overflow: auto;
                background: #FFF;
                border: 1px solid #ccc;
                border-radius: 5px;
            }
        }

        .many-rows-content {
            border-radius: 4px;
            height: calc(100% - 36px);
            padding: 3px;
            overflow: auto;

            .active {
                background-color: #FFC;
            }
            div {
                padding: 0 5px;
                border-bottom: 1px dashed #CCC;
                cursor: pointer;
                font-size: 1.2em;

                &:hover {
                    border: 1px dashed #777;
                }
                label {
                    cursor: pointer;
                    margin-bottom: 0;
                    white-space: nowrap;
                }
            }
        }

        .allow_resend {
            position: absolute;
            top: 5px;
            left: 5px;
        }
    }
</style>