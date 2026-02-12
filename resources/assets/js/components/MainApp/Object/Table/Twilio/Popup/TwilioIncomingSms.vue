<template>
    <div class="popup right-bot" v-show="incomingMessages && incomingMessages.length" :style="{width: popWi}">
        <i v-if="popWi" class="fas fa-arrow-left pull-right white" @click="maximize()"></i>
        <div v-else class="flex flex--col">
            <div class="popup-header flex">
                <div class="flex__elem-remain">
                    <span>Incoming SMS</span>
                </div>
                <div class="" style="position: relative">
                    <span class="glyphicon glyphicon-remove pull-right" @click="hide()"></span>
                    <i class="fas fa-arrow-right pull-right" @click="minimize()"></i>
                    <span class="pull-right" @click="showTestApp()">A</span>
                </div>
            </div>
            <div class="popup-content flex__elem-remain">
                <div class="popup-main full-height flex flex--col">
                    <div class="full-frame">
                        <div v-for="sms in incomingMessages" style="border: 1px solid #ccc; padding: 0 3px;">
                            <div class="flex flex--space">
                                <label>From: {{ sms.content.sms_from }}</label>
                                <label>To: {{ sms.content.sms_to }}</label>
                            </div>
                            <div>
                                <label>Message: {{ sms.content.sms_message }}</label>
                            </div>
                        </div>
                    </div>
                    <div class="flex">
                        <textarea class="form-control" rows="2" v-model="replyMessage"></textarea>
                        <button class="btn btn-sm btn-primary"
                                :style="$root.themeButtonStyle"
                                style="height: 60px;"
                                @click="sendSMS()"
                        >
                            <i class="glyphicon glyphicon-send"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import {eventBus} from "../../../../../../app";

    import {TwilioEndpoints} from "../../../../../../classes/TwilioEndpoints";
    import {SpecialFuncs} from "../../../../../../classes/SpecialFuncs";

    export default {
        name: "TwilioIncomingSms",
        mixins: [
        ],
        components: {
        },
        data: function () {
            return {
                messagesFromDate: moment().format('YYYY-MM-DD HH:mm:ss'),
                incomingMessages: [],
                popWi: null,
                replyMessage: '',
            }
        },
        props: {
            twilio_acc_id: Number,
        },
        computed: {
            twAcc() {
                return _.find(this.$root.user._twilio_api_keys, {id: Number(this.twilio_acc_id)});
            },
        },
        methods: {
            watchSms() {
                if (!this.$root.user.id || this.$root.user.see_view) {
                    return;
                }
                if (!this.twAcc) {
                    Swal('Info','Twilio account for Incoming Sms not found!');
                    return;
                }
                if (!this.twAcc.search_key) {
                    Swal('Info','TwiMl App for Incoming Sms not found!');
                    return;
                }

                setInterval(() => {
                    let utc = SpecialFuncs.convertToUTC(this.messagesFromDate, this.$root.user.timezone);
                    TwilioEndpoints.incomingSms(this.twAcc.search_key, utc).then((data) => {
                        this.incomingMessages = data;
                        if (this.incomingMessages && this.incomingMessages.length) {
                            _.each(this.incomingMessages, (incSms) => {
                                if (!_.find(this.$root.user._twilio_test_history.sms, {id: Number(incSms.id)})) {
                                    this.$root.user._twilio_test_history.sms.unshift(incSms);
                                }
                            });
                        }
                    });
                }, 9000);
            },
            hide() {
                this.messagesFromDate = moment().format('YYYY-MM-DD HH:mm:ss');
                this.incomingMessages = [];
            },
            minimize() {
                this.popWi = '20px';
            },
            maximize() {
                this.popWi = null;
            },
            showTestApp() {
                eventBus.$emit('show-twilio-test-popup', this.twilio_acc_id);
            },
            sendSMS() {
                let lastSms = _.first(this.incomingMessages);
                if (lastSms && lastSms.content) {
                    TwilioEndpoints.sms(this.twilio_acc_id, lastSms.content.sms_from, this.replyMessage, null, null, null)
                        .then((data) => {
                            this.$root.user._twilio_test_history.sms.unshift(data);
                        });
                }
            },
        },
        mounted() {
            if (!localStorage.getItem('no_ping')) {
                this.watchSms();
            }
        },
        beforeDestroy() {
        }
    }
</script>

<style scoped lang="scss">
@import "resources/assets/js/components/CustomPopup/CustomEditPopUp";

.right-bot {
    z-index: 5000;
    bottom: 5px;
    right: 5px;
    width: 320px;
    height: 250px;

    label {
        margin: 0;
    }
    .popup-header {
        font-size: 1.2em;
    }
    .popup-main {
        padding: 10px !important;
    }
    .fsize {
        font-size: 36px;
        margin: 18px;
    }
    .fa-phone-alt {
        cursor: pointer;
    }
    .pull-right {
        margin-left: 5px;
        cursor: pointer;
    }
}
</style>