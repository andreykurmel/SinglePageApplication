<template>
    <div class="full-height preview-wrap">
        <template v-if="incorrect_settings">
            <div class="form-group label_blocks">
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
        <div v-else-if="all_rows && all_rows.length" class="flex full-frame">
            <div :style="{width: hideFIlter ? '0' : '30%'}">
                <filters-block
                    :table-meta="filter_meta"
                    :input_filters="filter_filters"
                    :no_right_click="true"
                    style="background: white;"
                ></filters-block>
            </div>
            <div class="menu-body" :style="{width: hideFIlter ? '100%' : '70%'}">
                <div class="full-height body-view">
                    <div style="height: 32px; padding: 0 5px;" class="flex flex--center-v flex--space">
                        <span class="glyphicon"
                              :class="[ !hideFIlter ? 'glyphicon-triangle-left': 'glyphicon-triangle-right']"
                              @click="hideFIlter = !hideFIlter"
                        ></span>

                        <button v-if="hasHistory(selected_id)"
                                class="btn btn-primary btn-sm blue-gradient pull-right"
                                :style="$root.themeButtonStyle"
                                @click="clearHistory()"
                        >Clear History</button>
                    </div>
                    <div v-for="prev in preview_emails">
                        <email-preview-element
                            v-if="prev.history && prev.history.length"
                            :element="prev"
                            :table-meta="tableMeta"
                            :email-settings="emailSettings"
                            :short-view="false"
                            :no-main="true"
                            :with-filters="filter_filters"
                            @history-delete="clearHistory"
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
    import FiltersBlock from "../../../../CommonBlocks/FiltersBlock";

    export default {
        name: "EmailHistory",
        mixins: [
        ],
        components: {
            FiltersBlock,
            EmailSendBlock,
            EmailPreviewElement,
        },
        data: function () {
            return {
                hideFIlter: false,
                selected_id: null,

                incorrect_settings: false,
                preview_emails: {},
                all_rows: null,

                filter_meta: {
                    _is_owner: true,
                    _fields: [
                        { field:'preview_from', name:'From', is_showed:1 },
                        { field:'preview_to', name:'To', is_showed:1 },
                        { field:'preview_reply', name:'Reply', is_showed:1 },
                        { field:'preview_subject', name:'Subject', is_showed:1 },
                        { field:'send_date', name:'Sent', is_showed:1 },
                    ],
                },
                filter_filters: [
                    {
                        applied_index: 0,
                        filter_type: 'value',
                        field: 'preview_from',
                        name: 'From',
                        values: [],
                    },
                    {
                        applied_index: 0,
                        filter_type: 'value',
                        field: 'preview_to',
                        name: 'To',
                        values: [],
                    },
                    {
                        applied_index: 0,
                        filter_type: 'value',
                        field: 'preview_reply',
                        name: 'Reply',
                        values: [],
                    },
                    {
                        applied_index: 0,
                        filter_type: 'value',
                        field: 'preview_subject',
                        name: 'Subject',
                        values: [],
                    },
                    {
                        applied_index: 0,
                        filter_type: 'value',
                        field: 'send_date',
                        name: 'Sent',
                        values: [],
                    },
                ],
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
            //Preview
            getPreview(special) {
                this.incorrect_settings = false;
                axios.post('/ajax/addon-email-sett/preview', {
                    email_add_id: this.emailSettings.id,
                    row_id: null,
                    special: special || '',
                }).then(({data}) => {
                    if (data && data.all_rows && data.all_rows.length) {
                        if (!this.all_rows) {
                            this.all_rows = data.all_rows;
                            let row = _.first(this.all_rows);
                            this.selected_id = row ? row.id : null;
                        }
                        this.$root.assignObject(data.previews, this.preview_emails);
                        this.buildFilters();
                    } else {
                        this.incorrect_settings = true;
                    }
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                });
            },
            buildFilters() {
                _.each(this.filter_filters, (filter) => {
                    let vals = [];
                    _.each(this.preview_emails, (prev) => {
                        _.each(prev.history, (hist) => {
                            let subs = typeof hist[filter.field] === 'object' ? hist[filter.field] : [hist[filter.field]];
                            vals = _.concat(vals, subs);
                        });
                    });
                    vals = _.uniq(vals);
                    filter.values = _.map(vals, (vl) => {
                        return {
                            checked: 1,
                            show: vl,
                            val: vl,
                        };
                    });
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
        .glyphicon {
            cursor: pointer;
        }

        .menu-body {
            padding: 0;
            margin-left: 5px;

            .body-view {
                position: relative;
                overflow: auto;
                background: #FFF;
                border: 1px solid #ccc;
                border-radius: 5px;
            }
        }
    }
</style>