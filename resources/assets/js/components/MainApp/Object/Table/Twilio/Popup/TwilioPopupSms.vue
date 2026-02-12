<template>
    <div class="tabs-vertical" style="padding: 5px;">
        <div class="tabs-vertical--header">
            <div class="tabs-vertical--buttons">
                <button class="btn btn-default btn-sm left-btn"
                        :class="{active : acttab === 'tw_new_sms'}"
                        @click="acttab = 'tw_new_sms';$emit('setWidth',600)"
                >New</button>
                <button class="btn btn-default btn-sm left-btn"
                        :class="{active : acttab === 'tw_sent_sms'}"
                        @click="acttab = 'tw_sent_sms';$emit('setWidth',600)"
                >History</button>

                <div v-if="tableHeader.twilio_sms_acc_id && twAcc" style="position: absolute; right: 10px; top: 0; font-size: 16px;">
                    Twilio/SMS: <a @click="showResPop('twilio_tab')">{{ twAcc.name || '#'+(twAccIdx+1) }}</a>
                </div>
            </div>
        </div>
        <div class="tabs-vertical--body" :style="$root.themeMainBgStyle">

            <div class="full-frame" v-show="acttab === 'tw_new_sms'">
                <div class="flex flex--space">
                    <label :style="$root.themeMainTxtColor">From: <span v-html="$root.telFormat(twAccPhone)"></span></label>
                    <label :style="$root.themeMainTxtColor">To: <span v-html="$root.telFormat(cellItem)"></span></label>
                </div>
                <div class="form-group">
                    <label :style="$root.themeMainTxtColor">Message:</label>
                    <div style="position: relative;">
                        <textarea rows="3"
                                  v-model="sms.message"
                                  @keyup="remadeFormula('formula_sms')"
                                  @focus="formula_sms = true"
                                  class="form-control"
                                  style="resize: none;"
                        ></textarea>
                        <formula-helper
                            v-if="formula_sms"
                            :user="$root.user"
                            :table-meta="tableMeta"
                            :table-row="sms"
                            :header-key="'message'"
                            :can-edit="true"
                            :no-function="true"
                            :no_prevent="true"
                            :pop_width="'100%'"
                            style="padding: 0;"
                            @close-formula="formula_sms = false"
                            @set-formula="formula_sms = false"
                        ></formula-helper>
                    </div>
                </div>
                <div>
                    <button class="btn btn-success" :disabled="process" @click="twilioSms()">Send</button>
                </div>
            </div>

            <div class="full-frame" v-show="acttab === 'tw_sent_sms'">
                <div v-for="hist in tw_histories">
                    <div class="flex flex--space">
                        <label :style="$root.themeMainTxtColor">From: <span v-html="$root.telFormat(hist.content.sms_from)"></span></label>
                        <label :style="$root.themeMainTxtColor">To: <span v-html="$root.telFormat(hist.content.sms_to)"></span></label>
                    </div>
                    <div class="flex flex--space">
                        <label :style="$root.themeMainTxtColor">Message:</label>
                        <label :style="$root.themeMainTxtColor">Sent: {{ sentDate(hist) }}</label>
                    </div>
                    <div class="form-group">
                        <textarea v-model="hist.content.sms_message" class="form-control" readonly style="resize: none;"></textarea>
                    </div>
                </div>
            </div>

        </div>
    </div>
</template>

<script>
    import {TwilioEndpoints} from "../../../../../../classes/TwilioEndpoints";
    import {SpecialFuncs} from "../../../../../../classes/SpecialFuncs";

    import CallMixin from "./CallMixin";

    export default {
        name: "TwilioPopupSms",
        mixins: [
            CallMixin,
        ],
        components: {
        },
        data: function () {
            return {
                process: false,
                acttab: 'tw_new_sms',
                sms: {
                    message: '',
                },
                formula_sms: false,
                mixin_type: 'sms',
            }
        },
        props: {
            tableMeta: Object,
            tableHeader: Object,
            tableRow: Object,
            cellItem: String,
        },
        computed: {
            twAccPhone() {
                let acc = _.find(this.$root.user._twilio_api_keys, {id: Number(this.tableHeader.twilio_sms_acc_id)});
                return acc ? acc.twilio_phone : 'Not Found!';
            },
            twAcc() {
                return _.find(this.$root.user._twilio_api_keys, {id: Number(this.tableHeader.twilio_sms_acc_id)});
            },
            twAccIdx() {
                return _.findIndex(this.$root.user._twilio_api_keys, {id: Number(this.tableHeader.twilio_sms_acc_id)});
            },
        },
        methods: {
            sentDate(row) {
                return SpecialFuncs.convertToLocal(row.created_on, this.$root.user.timezone);
            },
            remadeFormula(param) {
                this[param] = false;
                this.$nextTick(() => {
                    this[param] = true;
                });
            },
            twilioSms() {
                if (this.sms.message) {
                    this.process = true;
                    TwilioEndpoints.sms(this.tableHeader.twilio_sms_acc_id, this.cellItem, this.sms.message, this.tableMeta.id, this.tableHeader.id, this.tableRow.id)
                        .then((data) => {
                            this.newInHistory(data);
                        })
                        .finally(() => {
                            this.process = false;
                        });
                } else {
                    Swal('Info','Message is empty!');
                }
            },
        },
        mounted() {
        },
        beforeDestroy() {
        }
    }
</script>

<style scoped lang="scss">
</style>