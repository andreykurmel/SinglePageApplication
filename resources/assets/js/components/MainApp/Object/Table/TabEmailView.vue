<template>
    <div v-if="tableMeta && settingsMeta" class="tab-settings full-height">
        <div v-if="!$root.AddonAvailableToUser(tableMeta, 'email')" class="row full-frame flex flex--center">
            <label>Addon is unavailable!</label>
        </div>
        <template v-else-if="tableMeta._is_owner">
            <div class="menu-header">
                <button class="btn btn-default btn-sm left-btn"
                        :class="{active : acttab === 'setup'}"
                        @click="saveChanges();acttab = 'setup';"
                >Setup</button>
                <button class="btn btn-default btn-sm left-btn"
                        :class="{active : acttab === 'body'}"
                        @click="saveChanges();acttab = 'body';"
                >Body</button>
                <button class="btn btn-default btn-sm left-btn"
                        :class="{active : acttab === 'preview'}"
                        @click="saveChanges();acttab = 'preview';"
                >Preview</button>

                <template v-if="acttab === 'preview'">
                    <button class="btn btn-primary btn-sm blue-gradient pull-right"
                            :style="$root.themeButtonStyle"
                            :disabled="!!send_status"
                            @click="sendEmail()"
                    >Send</button>
                    <div v-show="send_status" class="pull-right flex flex--center email_status">{{ send_status }}</div>
                    <button v-show="error_present > 5"
                            class="btn btn-primary btn-sm email_cancel pull-right"
                            @click="cancelEmail()"
                    >Cancel</button>

                    <div v-show="!send_status" class="pull-right flex flex--center email_status">Total {{ total_emails }} emails generated.</div>
                </template>
            </div>
            <div class="menu-body">

                <!--SETUP TAB-->
                <div class="full-frame" v-if="acttab === 'setup'">
                    <email-setup
                            :table-meta="tableMeta"
                            :email-settings="tableMeta._email_addon_settings"
                            :total_emails="total_emails"
                            @save-backend="saveChanges"
                    ></email-setup>
                </div>
                <!--BODY TAB-->
                <div class="full-frame" v-if="acttab === 'body'">
                    <email-body
                            :table-meta="tableMeta"
                            :email-settings="tableMeta._email_addon_settings"
                            @save-backend="saveChanges"
                    ></email-body>
                </div>
                <!--PREVIEW TAB-->
                <div class="full-frame" v-if="acttab === 'preview'">
                    <email-preview
                            :email-settings="tableMeta._email_addon_settings"
                    ></email-preview>
                </div>

            </div>
        </template>
    </div>
</template>

<script>
    import EmailSetup from "./EmailAddon/EmailSetup";
    import EmailPreview from "./EmailAddon/EmailPreview";
    import EmailBody from "./EmailAddon/EmailBody";

    export default {
        name: "TabEmailView",
        components: {
            EmailBody,
            EmailPreview,
            EmailSetup,
        },
        data: function () {
            return {
                acttab: 'setup',
                error_present: 0,
                total_emails: null,
            }
        },
        props:{
            table_id: Number,
            tableMeta: Object,
            settingsMeta: Object,
            user: Object,
        },
        watch: {
            table_id(val) {
                this.countRows();
            }
        },
        computed: {
            send_status() {
                let eml = this.tableMeta._email_addon_settings;
                let str = '';
                if (eml.prepared_emails > 0) {
                    if (eml.sent_emails > 0) {
                        if (eml.prepared_emails == eml.sent_emails) {
                            str = 'Completed. '+eml.prepared_emails+' emails sent.';
                        } else {
                            str = eml.sent_emails+' of '+eml.prepared_emails+' emails sent.';
                        }
                    } else {
                        str = 'In preparation';
                    }
                }
                return str;
            },
        },
        methods: {
            saveChanges(type) {
                this.tableMeta._email_addon_settings.email_body =
                    this.tableMeta._email_addon_settings.email_body.replaceAll('?s="', '?s='+this.tableMeta._email_addon_settings.hash+'"');
                axios.put('/ajax/addon-email-sett', {
                    email_add_id: this.tableMeta._email_addon_settings.id,
                    fields: this.tableMeta._email_addon_settings,
                }).then(({data}) => {
                    if (type === 'limit_row_group_id') {
                        this.countRows();
                    }
                }).catch(errors => {
                    Swal('', getErrors(errors));
                });
            },
            sendEmail() {
                axios.post('/ajax/addon-email-sett/send', {
                    email_add_id: this.tableMeta._email_addon_settings.id,
                }).then(({data}) => {
                    if (data.result) {
                        //Swal(data.result);
                        this.tableMeta._email_addon_settings.prepared_emails = data.prepared_emails;
                        this.tableMeta._email_addon_settings.sent_emails = data.sent_emails;
                        this.error_present = 0;
                    }
                }).catch(errors => {
                    Swal('', getErrors(errors));
                });
            },
            cancelEmail() {
                axios.post('/ajax/addon-email-sett/cancel', {
                    email_add_id: this.tableMeta._email_addon_settings.id,
                }).then(({data}) => {
                    this.tableMeta._email_addon_settings.prepared_emails = 0;
                    this.tableMeta._email_addon_settings.sent_emails = 0;
                    this.error_present = 0;
                }).catch(errors => {
                    Swal('', getErrors(errors));
                });
            },
            countRows() {
                axios.post('/ajax/row-group/count', {
                    table_row_group_id: this.tableMeta._email_addon_settings.limit_row_group_id,
                }).then(({data}) => {
                    this.total_emails = data.total;
                });
            },
            checkStatus() {
                axios.get('/ajax/addon-email-sett/status', {
                    params: { email_add_id: this.tableMeta._email_addon_settings.id, },
                }).then(({data}) => {
                    if (this.tableMeta._email_addon_settings.sent_emails == data.sent_emails) {
                        this.error_present += 1;
                    }
                    this.tableMeta._email_addon_settings.prepared_emails = data.prepared_emails;
                    this.tableMeta._email_addon_settings.sent_emails = data.sent_emails;
                    if (this.tableMeta._email_addon_settings.sent_emails >= this.tableMeta._email_addon_settings.prepared_emails) {
                        this.error_present = 0;
                    }
                });
            },
        },
        mounted() {
            setInterval(() => {
                if (this.tableMeta._email_addon_settings.prepared_emails) {
                    this.checkStatus();
                }
            }, 1000);
            this.countRows();
        },
        beforeDestroy() {
        }
    }
</script>

<style lang="scss" scoped>
    .tab-settings {
        position: relative;
        height: 100%;
        background-color: #FFF;

        .blue-gradient {
            margin-right: 5px;
        }

        .menu-header {
            position: relative;
            margin-left: 10px;
            top: 3px;

            .left-btn {
                position: relative;
                top: 5px;
            }

            button {
                background-color: #CCC;
                outline: none;
                &.active {
                    background-color: #FFF;
                }
                &:not(.active):hover {
                    color: black;
                }
            }

            .right-elm {
                float: right;
                margin-left: 10px;
            }
        }

        .menu-body {
            position: absolute;
            top: 35px;
            right: 5px;
            left: 5px;
            bottom: 5px;
            border-radius: 5px;
            background-color: inherit;
            border: 1px solid #CCC;
            padding: 10px;
        }

        .email_status {
            height: 28px;
            margin-right: 10px;
            font-weight: bold;
        }
        .email_cancel {
            background-color: #bf5329 !important;
            border-color: #aa4a24;
            padding: 0 3px;
            margin-top: 3px;
            margin-right: 10px;
        }
    }
</style>