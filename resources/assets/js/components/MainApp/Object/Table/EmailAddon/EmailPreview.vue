<template>
    <div class="full-height preview-wrap">
        <template v-if="incorrect_settings">
            <div class="form-group label_blocks">
                <label>Server settings are incorrect!</label>
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
                <label>Or rows are not found!</label>
            </div>
        </template>
        <template v-else-if="preview_emails && preview_emails.length">
            <div v-for="prev in preview_emails" class="eml_block form-group">
                <div>
                    <label>From:</label>
                    <span>{{ prev.from }}</span>
                </div>
                <div>
                    <label>Reply To:</label>
                    <span>{{ prev.reply.join(', ') }}</span>
                </div>
                <div>
                    <label>To:</label>
                    <span>{{ prev.to.join(', ') }}</span>
                </div>
                <div>
                    <label>Subject:</label>
                    <span>{{ prev.subject }}</span>
                </div>
                <div>
                    <label>Body:</label>
                    <div v-html="prev.body"></div>
                </div>
            </div>
        </template>
        <template v-else="">
            <div class="form-group">
                <label>Loading ...</label>
            </div>
        </template>
    </div>
</template>

<script>
    import CellStyleMixin from "./../../../../_Mixins/CellStyleMixin.vue";

    export default {
        name: "EmailPreview",
        mixins: [
            CellStyleMixin,
        ],
        components: {
        },
        data: function () {
            return {
                incorrect_settings: false,
                preview_emails: [],
            }
        },
        props:{
            emailSettings: Object,
        },
        computed: {
        },
        methods: {
            getPreview() {
                this.incorrect_settings = false;
                this.preview_emails = [];
                axios.post('/ajax/addon-email-sett/preview', {
                    email_add_id: this.emailSettings.id,
                }).then(({data}) => {
                    if (data && data.length) {
                        this.preview_emails = data;
                    } else {
                        this.incorrect_settings = true;
                    }
                }).catch(errors => {
                    Swal('', getErrors(errors));
                });
            },
        },
        mounted() {
            this.getPreview();
        },
        beforeDestroy() {
        }
    }
</script>

<style lang="scss" scoped>
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
        .eml_block {
            background-color: #F4f4f4;
            margin-bottom: 30px;
        }
        .label_blocks {
            label {
                display: block;
            }
        }
    }
</style>