<template>
    <div class="popup right-bot" v-show="callTwilio || timer_value" :style="{width: popWi}">
        <i v-if="popWi" class="fas fa-arrow-left pull-right white" @click="maximize()"></i>
        <div v-else class="flex flex--col">
            <div class="popup-header flex">
                <div class="flex__elem-remain">
                    <span>Incoming Call</span>
                </div>
                <div class="" style="position: relative">
                    <span class="glyphicon glyphicon-remove pull-right" @click="hide()"></span>
                    <i class="fas fa-arrow-right pull-right" @click="minimize()"></i>
                    <span class="pull-right" @click="showTestApp()">A</span>
                </div>
            </div>
            <div class="popup-content flex__elem-remain">
                <div class="flex__elem__inner popup-main">
                    <div class="flex flex--col">
                        <div class="txt--center">
                            <label>From: {{ fromTwilioNumber || 'no incoming' }}</label>
                        </div>

                        <template v-if="callTwilio">
                            <div v-if="!time_start" class="flex__elem-remain flex flex--center flex--around" style="position: relative;">
                                <a @click="declineCall">
                                    <i class="fas fa-phone-alt fsize red" title="Decline" :style="{rotate: '135deg'}"></i>
                                </a>
                                <a @click="answerCall">
                                    <i class="fas fa-phone-alt fsize green" title="Accept"></i>
                                </a>
                            </div>
                            <div v-if="time_start" class="flex__elem-remain flex flex--col flex--center" style="position: relative;">
                                <span style="position: relative;line-height: 1px;">In Progress</span>
                                <span style="font-size: 36px;line-height: 24px;margin-bottom: 10px;">&lt;---------&gt;</span>
                                <span style="position: relative;top: -10px;line-height: 1px;">{{ timerUi }}</span>
                                <button class="btn btn-danger btn-sm" @click="hangUp">End the Call</button>
                            </div>
                        </template>

                        <div class="txt--center">
                            <label v-html="toCall"></label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import {TwilioEndpoints} from "../../../../../../classes/TwilioEndpoints";
    import {eventBus} from "../../../../../../app";

    export default {
        name: "TwilioIncomingCall",
        mixins: [
        ],
        components: {
        },
        data: function () {
            return {
                time_start: '',
                timer: null,
                timer_value: 0,
                popWi: null,

                devTwilio: null,
                callTwilio: null,
                fromTwilioNumber: '',
            }
        },
        props: {
            twilio_acc_id: Number,
        },
        computed: {
            twAcc() {
                return _.find(this.$root.user._twilio_api_keys, {id: Number(this.twilio_acc_id)});
            },
            toCall() {
                return 'To: ' + (this.twAcc ? this.$root.telFormat(this.twAcc.twilio_phone) : 'no account');
            },
        },
        methods: {
            hide() {
                this.callTwilio = null;
                this.timer_value = 0;
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
            //buttons
            answerCall() {
                this.callTwilio
                    ? this.callTwilio.accept()
                    : Swal('Info','Twilio call object not found!');
            },
            declineCall() {
                this.callTwilio
                    ? this.callTwilio.reject()
                    : Swal('Info','Twilio call object not found!');
            },
            hangUp() {
                if (this.callTwilio && this.callTwilio.disconnect) {
                    this.callTwilio.disconnect();
                }
                this.callTwilio = null;
                this.endTimer();
            },
            //timer
            startTimer() {
                this.time_start = moment().format('YYYY-MM-DD HH:mm:ss');
                this.timer_value = 0;
                this.timer = setInterval(() => {
                    this.timer_value++;
                }, 1000);
            },
            endTimer() {
                clearTimeout(this.timer);
                this.callTwilio = null;
                if (this.fromTwilioNumber) {
                    TwilioEndpoints.storeCallHistory(
                        this.$root.user.id,
                        null,
                        null,
                        null,
                        this.twAcc.twilio_phone,
                        this.fromTwilioNumber,
                        this.time_start,
                        this.timer_value,
                    ).then((data) => {
                        let twiHist = this.$root.user._twilio_test_history;
                        if (twiHist && twiHist.call) {
                            twiHist.call.unshift(data);
                        }
                    });

                    this.time_start = '';
                    this.timer_value = 0;
                }
            },
            twilioWatcher() {
                if (!this.twAcc) {
                    Swal('Info','Twilio account for Incoming Calls not found!');
                    return;
                }
                if (!this.twAcc.search_key) {
                    Swal('Info','TwiMl App for Incoming Calls not found!');
                    return;
                }

                TwilioEndpoints.browserToken(this.twilio_acc_id, true)
                    .then((data) => {
                        console.log('Setup incoming device...');
                        this.devTwilio = new twilioDevice(data.token, { logLevel:1 });
                        this.devTwilio.on('incoming', call => {
                            console.log('incoming', call);
                            this.callTwilio = call;
                            this.callTwilio.on('error', (error) => {
                                console.log('Twilio error: '+error);
                                this.endTimer();
                            });
                            this.callTwilio.on('accept', () => {
                                this.startTimer();
                            });
                            this.callTwilio.on('disconnect', () => {
                                this.endTimer();
                            });
                            this.callTwilio.on('reject', () => {
                                this.endTimer();
                            });
                            this.callTwilio.on('cancel', () => {
                                this.endTimer();
                            });
                            this.fromTwilioNumber = this.callTwilio.customParameters && this.callTwilio.customParameters.get("dialFrom")
                                ? this.callTwilio.customParameters.get("dialFrom")
                                : this.callTwilio.parameters.From;
                        });

                        this.devTwilio.on('registered', (device) => {
                            console.log('Device is ready.', device);
                        });
                        this.devTwilio.on('error', (err) => {
                            console.log('Device error: ' + err);
                        });

                        this.devTwilio.register();
                    });
            },
        },
        mounted() {
            if (!localStorage.getItem('no_ping')) {
                this.twilioWatcher();
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
    width: 250px;
    height: 200px;

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