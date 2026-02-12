<template>
    <div class="tabs-vertical" style="padding: 5px;">
        <div class="tabs-vertical--header">
            <div class="tabs-vertical--buttons">
                <button class="btn btn-default btn-sm left-btn"
                        :class="{active : acttab === 'tw_new_email'}"
                        @click="acttab = 'tw_new_email';$emit('setWidth',600)"
                >New</button>
                <button class="btn btn-default btn-sm left-btn"
                        :class="{active : acttab === 'tw_sent_email'}"
                        @click="acttab = 'tw_sent_email';$emit('setWidth',900)"
                >History</button>

                <div v-if="tableHeader.twilio_google_acc_id" style="position: absolute; right: 10px; top: 0; font-size: 16px;">
                    Gmail: <a @click="showResPop('google')">{{ googleAcc.name || '#'+(googleAccIdx+1) }}</a>
                </div>
                <div v-if="tableHeader.twilio_sendgrid_acc_id" style="position: absolute; right: 10px; top: 0; font-size: 16px;">
                    Sendgrid: <a @click="showResPop('twilio_tab')">{{ sendgridAcc.name || '#'+(sendgridAccIdx+1) }}</a>
                </div>
            </div>
        </div>
        <div class="tabs-vertical--body" :style="$root.themeMainBgStyle">

            <div class="full-frame flex flex--col" v-show="acttab === 'tw_new_email'">
                <div class="flex flex--center form-group">
                    <label :style="$root.themeMainTxtColor">Sender:&nbsp;</label>
                    <input class="form-control" v-model="email.from_name"/>
                    <label :style="$root.themeMainTxtColor">&nbsp;&nbsp;&nbsp;Email:&nbsp;</label>
                    <input class="form-control" :disabled="!!googleAcc.id" v-model="email.from"/>
                </div>
                <div class="flex flex--center form-group no-wrap">
                    <label :style="$root.themeMainTxtColor">Reply To:&nbsp;</label>
                    <input class="form-control" v-model="email.reply_to"/>
                </div>
                <div class="form-group">
                    <label :style="$root.themeMainTxtColor">To: {{ cellItem }}</label>
                </div>
                <div class="form-group">
                    <label :style="$root.themeMainTxtColor">Subject:&nbsp;</label>
                    <div style="position: relative">
                        <input class="form-control"
                               @keyup="remadeFormula('formula_subject')"
                               @focus="formula_subject = true"
                               v-model="email.subject"/>
                        <formula-helper
                            v-if="formula_subject"
                            :user="$root.user"
                            :table-meta="tableMeta"
                            :table-row="email"
                            :header-key="'subject'"
                            :no-function="true"
                            :no_prevent="true"
                            :pop_width="'100%'"
                            @close-formula="formula_subject = false"
                            @set-formula="formula_subject = false"
                        ></formula-helper>
                    </div>
                </div>
                <div class="form-group flex__elem-remain flex flex--col" style="overflow: visible">
                    <label :style="$root.themeMainTxtColor">Body:</label>
                    <div class="full-height" style="position: relative;">
                        <textarea v-model="email.body"
                                  @keyup="remadeFormula('formula_body')"
                                  @focus="formula_body = true"
                                  class="form-control full-height"
                        ></textarea>
                        <formula-helper
                            v-if="formula_body"
                            :user="$root.user"
                            :table-meta="tableMeta"
                            :table-row="email"
                            :header-key="'body'"
                            :no-function="true"
                            :no_prevent="true"
                            :pop_width="'100%'"
                            style="padding: 0;"
                            @close-formula="formula_body = false"
                            @set-formula="formula_body = false"
                        ></formula-helper>
                    </div>
                </div>
                <div>
                    <button class="btn btn-success" :disabled="process" @click="sendEmail()">Send</button>
                </div>
            </div>

            <div class="full-frame" v-show="acttab === 'tw_sent_email'">
                <div v-for="hist in tw_histories" class="eml_block">
                    <div class="eml_header">
                        <div class="flex flex--center-v">
                            <label>From:</label>
                            <span>{{ hist.content.email_from_name }} &lt;{{ hist.content.email_from_email }}&gt;</span>
                            <template v-if="hist.content.email_reply">
                                <label>&nbsp;&nbsp;&nbsp;Reply To:</label>
                                <span>{{ hist.content.email_reply }}</span>
                            </template>
                        </div>
                        <div>
                            <label>To:</label>
                            <span>{{ hist.content.email_to }}</span>
                        </div>
                        <div v-if="hist.content.email_cc.length">
                            <label>CC:</label>
                            <span>{{ hist.content.email_cc.join(', ') }}</span>
                        </div>
                        <div v-if="hist.content.email_bcc.length">
                            <label>BCC:</label>
                            <span>{{ hist.content.email_bcc.join(', ') }}</span>
                        </div>
                        <div>
                            <label>Subject:</label>
                            <span>{{ hist.content.email_subject }}</span>
                        </div>

                        <div class="sent_at">
                            <label>Sent at: {{ $root.convertToLocal(hist.created_at, $root.user.timezone) }}</label>
                        </div>
                    </div>

                    <div>
                        <div v-html="hist.content.email_body"></div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</template>

<script>
    import {TwilioEndpoints} from "../../../../../../classes/TwilioEndpoints";

    import CustomTable from "../../../../../CustomTable/CustomTable";

    import CallMixin from "./CallMixin";

    export default {
        name: "TwilioPopupEmail",
        mixins: [
            CallMixin,
        ],
        components: {
            CustomTable,
        },
        data: function () {
            return {
                process: false,
                acttab: 'tw_new_email',
                email: {
                    from: this.tableHeader.twilio_sendgrid_acc_id
                        ? (this.tableHeader.twilio_sender_email || this.$root.user.email)
                        : this.$root.user.email,
                    from_name: this.tableHeader.twilio_sender_name || this.$root.user.first_name,
                    to: this.cellItem,
                    reply_to: '',
                    subject: '',
                    body: '',
                },
                formula_subject: false,
                formula_body: false,
                mixin_type: 'email',
            }
        },
        props: {
            tableMeta: Object,
            tableHeader: Object,
            tableRow: Object,
            cellItem: String,
        },
        computed: {
            googleAccIdx() {
                return _.findIndex(this.$root.user._google_email_accs, {id: Number(this.tableHeader.twilio_google_acc_id)});
            },
            googleAcc() {
                return _.find(this.$root.user._google_email_accs, {id: Number(this.tableHeader.twilio_google_acc_id)}) || {};
            },
            sendgridAccIdx() {
                return _.findIndex(this.$root.user._sendgrid_api_keys, {id: Number(this.tableHeader.twilio_sendgrid_acc_id)});
            },
            sendgridAcc() {
                return _.find(this.$root.user._sendgrid_api_keys, {id: Number(this.tableHeader.twilio_sendgrid_acc_id)}) || {};
            },
        },
        methods: {
            remadeFormula(param) {
                this[param] = false;
                this.$nextTick(() => {
                    this[param] = true;
                });
            },
            sendEmail() {
                this.process = true;
                axios.post('/ajax/addon-email-sett/send-single', {
                    twilio_google_acc_id: this.tableHeader.twilio_google_acc_id,
                    twilio_sendgrid_acc_id: this.tableHeader.twilio_sendgrid_acc_id,
                    email_data: this.email,
                    field_id: this.tableHeader.id,
                    row_id: this.tableRow.id,
                }).then(({data}) => {
                    if (data.result) {
                        Swal(data.result);
                    }
                    if (data.history) {
                        this.newInHistory(data.history);
                    }
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    this.process = false;
                });
            },
        },
        mounted() {
            if (this.googleAcc.email) {
                this.email.from = this.googleAcc.email;
            }
        },
        beforeDestroy() {
        }
    }
</script>

<style scoped lang="scss">
    label {
        margin: 0;
    }
    .eml_block {
        padding: 5px;
        border: 1px solid #777;
        border-radius: 5px;
        background-color: #F4f4f4;
        margin-bottom: 10px;

        .eml_header {
            position: relative;
            background-color: #DDD;
        }
        .sent_at {
            position: absolute;
            bottom: 5px;
            right: 5px;
        }
    }
</style>