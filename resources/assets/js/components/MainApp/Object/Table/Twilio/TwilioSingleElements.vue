<template>
    <div class="tabs-vertical">
        <div class="tabs-vertical--header">
            <div class="flex flex--center-v">
                <label class="no-margin">Current selected Twilio credential:</label>
                <select class="form-control" v-model="twilio_acc_id" :style="textSysStyle" style="width: 110px;padding: 0;">
                    <option :value="null">No API Key</option>
                    <option v-for="(key,idx) in $root.user._twilio_api_keys" :value="key.id">{{ key.name || ('#'+(idx+1)) }}</option>
                </select>
            </div>
            <div class="tabs-vertical--buttons">
                <button class="btn btn-default btn-sm left-btn"
                        :class="{active : acttab === 'tw_sms'}"
                        :style="textSysStyle"
                        @click="changeTab('tw_sms')"
                >SMS</button>
                <button class="btn btn-default btn-sm left-btn"
                        :class="{active : acttab === 'tw_phone'}"
                        :style="textSysStyle"
                        @click="changeTab('tw_phone')"
                >Phone Call</button>
            </div>
        </div>
        <div class="tabs-vertical--body">

            <div class="full-height" v-show="acttab === 'tw_sms'">
                <div class="full-frame" style="height: 40%; border-bottom: 3px solid;">
                    <div class="flex flex--space f-bold" style="margin-bottom: 5px;">
                        <div class="flex flex--center-v">From: {{ twAcc.name }} (<span v-html="$root.telFormat(twAcc.twilio_phone)"></span>)</div>
                        <div class="flex flex--center-v">
                            <span>To:&nbsp;</span>
                            <phone-block v-model="sms_phone"></phone-block>
                        </div>
                    </div>
                    <textarea class="form-control" rows="3" v-model="sms_string"></textarea>
                    <button class="btn btn-default" :disabled="!twilio_acc_id || !sms_phone || !sms_string" @click="twSMS()">Send</button>
                    <div>
                        <label>Note: outbound SMS can be sent from a Twilio number only, not from a verified caller ID's number.</label>
                    </div>
                </div>
                <div class="flex flex--col" style="height: 60%;">
                    <div style="border-bottom: 3px solid;">
                        <label style="margin-top: 10px;">History</label>
                    </div>
                    <div class="full-frame">
                        <div v-for="(smsRow,idx) in tw_sms_history" class="sms_table">
                                <div class="sms_table_info">
                                    <span>
                                        <span><b>From:</b> <span v-html="$root.telFormat(smsRow.content['sms_from'])"></span></span>
                                        <i v-if="twAcc.twilio_phone !== smsRow.content['sms_from']"
                                           class="fas fa-sms green"
                                           style="cursor: pointer;"
                                           @click="() => { sms_phone = smsRow.content['sms_from']; sms_string = ''; }"
                                        ></i>
                                    </span>
                                    <span>
                                        <span>&nbsp;&nbsp;&nbsp;<b>To:</b> <span v-html="$root.telFormat(smsRow.content['sms_to'])"></span></span>
                                        <i v-if="twAcc.twilio_phone !== smsRow.content['sms_to']"
                                           class="fas fa-sms green"
                                           style="cursor: pointer;"
                                           @click="() => { sms_phone = smsRow.content['sms_to']; sms_string = ''; }"
                                        ></i>
                                    </span>

                                    <span class="pull-right"><b>Sent:</b> {{ sentDate(smsRow) }}</span>
                                </div>
                                <div style="position: relative;">
                                    <span style="margin-right: 120px;"><b>Message:</b> {{ smsRow.content['sms_message'] }}</span>

                                    <button v-if="twAcc.twilio_phone !== smsRow.content['sms_from']"
                                            class="blue-gradient"
                                            style="right: 65px;"
                                            :style="$root.themeButtonStyle"
                                            @click="clickReply(smsRow)"
                                    >Reply</button>
                                    <button class="blue-gradient"
                                            :style="$root.themeButtonStyle"
                                            @click="() => { mixin_type = 'sms';histDelete(smsRow, idx); }"
                                    >Delete</button>
                                </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="full-height" v-show="acttab === 'tw_phone'">
                <div style="height: 50%;">
                    <div v-show="!call" class="full-frame flex">
                        <div class="full-width flex flex--center">
                            <twilio-dial-pad @digit-click="telPhoneInput"></twilio-dial-pad>
                        </div>
                        <div class="full-width flex flex--center flex--wrap">
                            <div>
                                <div class="f-bold">From: {{ twAcc.name }} (<span v-html="$root.telFormat(twAcc.twilio_phone)"></span>)</div>
                                <label>Phone number to call:</label>
                                <phone-block v-model="tel_phone" :right_drop="true"></phone-block>
                                <div>
                                    <button class="btn btn-default" :disabled="!twilio_acc_id" @click="clickCall(tel_phone)">Click to Call</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <call-ui
                        v-show="call"
                        :can_show_call="differentPhones(tel_phone)"
                        :from_active="from_active"
                        :to_active="to_active"
                        :timer_value="timer_value"
                        :timer-ui="timerUi"
                        :tw-acc="twAcc"
                        :to-name="'Receiver'"
                        :to-phone="tel_phone"
                        :w_theme="false"
                        @browser-call="browserCall(tel_phone)"
                        @hang-up="hangUp()"
                    ></call-ui>
                </div>
                <div class="flex flex--col" style="height: 50%;">
                    <div style="border-top: 3px solid;">
                        <label>Call history</label>
                    </div>
                    <div class="full-frame">
                        <custom-table
                            :cell_component_name="'custom-cell-twilio'"
                            :global-meta="callMeta"
                            :table-meta="callMeta"
                            :settings-meta="$root.settingsMeta"
                            :all-rows="tw_call_history"
                            :rows-count="tw_call_history.length"
                            :cell-height="1"
                            :user="$root.user"
                            :behavior="'tw_history_call'"
                            :is-full-width="true"
                            :forbidden-columns="['call_table']"
                            :parent-row="twAcc"
                            @delete-row="(row, idx) => { mixin_type = 'call';histDelete(row, idx); }"
                            @call-back="(phone) => { tel_phone = phone; }"
                        ></custom-table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</template>

<script>
    import {TwilioEndpoints} from "../../../../../classes/TwilioEndpoints";
    import {SpecialFuncs} from "../../../../../classes/SpecialFuncs";

    import CellStyleMixin from "../../../../_Mixins/CellStyleMixin";
    import CallMixin from "./Popup/CallMixin";

    import PhoneBlock from "../../../../CommonBlocks/PhoneBlock";
    import TwilioDialPad from "./TwilioDialPad";
    import CallUi from "./Popup/CallUi";
    import CustomTable from "../../../../CustomTable/CustomTable";

    export default {
        name: "TwilioSingleElements",
        mixins: [
            CellStyleMixin,
            CallMixin,
        ],
        components: {
            CustomTable,
            CallUi,
            TwilioDialPad,
            PhoneBlock,
        },
        data: function () {
            return {
                call: false,
                acttab: 'tw_sms',

                sms_phone: '',
                sms_string: '',

                tel_phone: '',
                tel_admin_phone: '',

                tableMeta: {
                    id: null,
                    name: '',
                },
                tableRow: {
                    id: null,
                    _tw_test_history: this.$root.user._twilio_test_history,
                },
                tableHeader: {
                    id: null,
                    table_id: null,
                    field: 'test_history',
                },
                mixin_type: '',
                sms_cache: [],
                tw_sms_history: [],
                tw_call_history: [],
            }
        },
        props: {
            row_id: Number,
            ext_tab: String,
            is_vis: Boolean,
        },
        watch: {
            row_id: {
                handler(val) {
                    this.twilio_acc_id = val;
                },
                immediate: true,
            },
            ext_tab: {
                handler(val) {
                    if (val) {
                        this.acttab = val;
                    }
                },
                immediate: true,
            },
            is_vis: {
                handler(val) {
                    if (val) {
                        this.buildTwSmsHistory();
                    }
                },
                immediate: true,
            },
        },
        computed: {
        },
        methods: {
            buildTwSmsHistory() {
                this.tw_sms_history = this.historyMethod(this.tableRow, this.tableHeader, '', 'sms');
                this.tw_call_history = this.historyMethod(this.tableRow, this.tableHeader, '', 'call');
            },
            clickReply(smsRow) {
                this.sms_phone = smsRow.content['sms_from'];
                this.sms_string = '--- ' + String(smsRow.content['sms_message']).substr(0, 50);
            },
            sentDate(row) {
                return SpecialFuncs.convertToLocal(row.created_on, this.$root.user.timezone);
            },
            changeTab(key) {
                this.acttab = key;
            },
            twSMS() {
                this.mixin_type = 'sms';
                TwilioEndpoints.sms(this.twilio_acc_id, this.sms_phone, this.sms_string, null, null, null)
                    .then((data) => {
                        this.newInHistory(data);
                    });
            },
            clickCall(phone) {
                this.call = true;
                this.mixin_type = 'call';
                this.browserCall(phone || '')
            },
            telPhoneInput(key) {
                this.tel_phone += key;
            },
        },
        mounted() {
        },
        beforeDestroy() {
        }
    }
</script>

<style lang="scss" scoped>
    .tabs-vertical {
        padding: 5px;
        background-color: #FFF;
    }
    .sms_table {
        width: 100%;
        margin-bottom: 10px;

        .sms_table_info {
            border-bottom: 1px solid #777;
        }
        div {
            padding: 3px 6px;

            label {
                margin: 0;
            }
            .blue-gradient {
                position: absolute;
                top: 3px;
                right: 7px;
            }
        }
    }
</style>