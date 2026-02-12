
<script>
    import {eventBus} from "../../../../../../app";

    import {TwilioEndpoints} from "../../../../../../classes/TwilioEndpoints";

    /**
     * used variables:
     * {Object} this.tableMeta
     * {Object} this.tableHeader
     * {Object} this.tableRow
     * {String} this.mixin_type
     */
    export default {
        data: function () {
            return {
                twilio_acc_id: null,
                from_active: false,
                to_active: false,
                time_start: null,
                timer: null,
                timer_value: 0,
                twDevice: null,
                twCall: null,

                smsMeta: {
                    id: null,
                    name: this.tableMeta ? this.tableMeta.name : '',
                    _is_owner: true,
                    _fields: [
                        { id:null, name:'From', field: 'sms_from', is_showed: true, width:120, },
                        { id:null, name:'To', field: 'sms_to', is_showed: true, width:120, },
                        { id:null, name:'Message', field: 'sms_message', is_showed: true, width:200, },
                    ],
                },

                callMeta: {
                    id: null,
                    name: this.tableMeta ? this.tableMeta.name : '',
                    _is_owner: true,
                    _fields: [
                        { id:null, name:'From', field: 'call_from', is_showed: true, width:120, },
                        { id:null, name:'To', field: 'call_to', is_showed: true, width:120, },
                        { id:null, name:'Time Start', field: 'call_start', is_showed: true, width:150, },
                        { id:null, name:'Duration', field: 'call_duration', is_showed: true, width:70, },
                        { id:null, name:'Table', field: 'call_table', is_showed: true, width:120, },
                    ],
                },

                emailMeta: {
                    id: null,
                    name: this.tableMeta ? this.tableMeta.name : '',
                    _is_owner: true,
                    _fields: [
                        { id:null, name:'From', field: 'email_from_email', is_showed: true, width:120, },
                        { id:null, name:'To', field: 'email_to', is_showed: true, width:120, },
                        { id:null, name:'Reply To', field: 'email_reply_to', is_showed: true, width:120, },
                        { id:null, name:'Subject', field: 'email_subject', is_showed: true, width:150, },
                        { id:null, name:'Body', field: 'email_body', is_showed: true, width:250, },
                    ],
                },
            }
        },
        computed: {
            twAcc() {
                return _.find(this.$root.user._twilio_api_keys, {id: Number(this.twilio_acc_id)}) || {};
            },
            timerUi() {
                return parseInt(this.timer_value/60) + ':' + (this.timer_value%60 < 10 ? '0' : '') + (this.timer_value%60);
            },
            tw_histories() {
                return this.historyMethod(this.tableRow, this.tableHeader, this.cellItem, this.mixin_type);
            },
        },
        methods: {
            historyMethod(tableRow, tableHeader, cellItem, mixin_type) {
                if (!tableRow || !tableHeader || !mixin_type) {
                    return [];
                }
                let rowHist = tableRow['_tw_'+tableHeader.field] || {};
                let calls = rowHist[mixin_type] || [];
                return calls && calls.length
                    ? calls.filter((cl) => { return !cellItem || cl.receiver === cellItem; })
                    : [];
            },
            differentPhones(phoneNumber) {
                return String(this.twAcc.twilio_phone || '').replace('+', '')
                    !== String(phoneNumber).replace('+', '');
            },

            //'Browser' call
            browserCall(phoneNumber) {
                this.initiateUi();
                TwilioEndpoints.browserToken(this.twilio_acc_id)
                    .then((data) => {
                        this.twDevice = new twilioDevice(data.token);
                        this.twDevice.connect({
                            "params": {
                                "phoneNumber": phoneNumber,
                                "twilioAccId": this.twilio_acc_id,
                            }
                        }).then((call) => {
                            this.twCall = call;

                            this.twCall.on('error', (error) => {
                                console.log('Twilio error: '+error);
                                this.endUi();
                            });
                            this.twCall.on('accept', () => {
                                this.time_start = moment().format('YYYY-MM-DD HH:mm:ss');
                                this.acceptUi();
                            });
                            this.twCall.on('disconnect', () => {
                                this.endUi(phoneNumber);
                            });
                            this.twCall.on('reject', () => {
                                this.endUi(phoneNumber);
                            });
                            this.twCall.on('cancel', () => {
                                this.endUi(phoneNumber);
                            });
                        });
                    });
            },
            hangUp() {
                if (this.twCall && this.twCall.disconnect) {
                    this.twCall.disconnect();
                    this.twCall = null;
                }
                this.endUi();
            },
            localTest(phoneNumber) {
                this.twDevice = {};
                this.twCall = { disconnect: () => {} };
                setTimeout(() => {
                    this.time_start = moment().format('YYYY-MM-DD HH:mm:ss');
                    this.acceptUi();

                    setTimeout(() => {
                        this.endUi(phoneNumber);
                    }, 5000);

                }, 2500);
            },

            //UI
            initiateUi() {
                this.from_active = true;
                this.to_active = false;
            },
            acceptUi() {
                this.from_active = true;
                this.to_active = true;
                this.startTimer();
            },
            endUi(phoneNumber) {
                this.from_active = false;
                this.to_active = false;
                this.endTimer();
                if (phoneNumber) {
                    this.storeCall(phoneNumber);
                }
            },

            //Timer
            startTimer() {
                this.timer_value = 0;
                this.timer = setInterval(() => {
                    this.timer_value++;
                }, 1000);
            },
            endTimer() {
                clearTimeout(this.timer);
            },

            //Store call history
            storeCall(phoneNumber) {
                TwilioEndpoints.storeCallHistory(
                    this.$root.user.id,
                    this.tableHeader ? this.tableHeader.table_id : null,
                    this.tableHeader ? this.tableHeader.id : null,
                    this.tableRow ? this.tableRow.id : null,
                    this.twAcc.twilio_phone,
                    phoneNumber,
                    this.time_start,
                    this.timer_value,
                ).then((data) => {
                    this.newInHistory(data);
                    setTimeout(() => {
                        this.timer_value = 0;
                        if (this.call) {
                            this.call = false;
                        }
                    }, 3000);
                });
            },
            histDelete(histRow, index) {
                TwilioEndpoints.removeHistory(
                    histRow.id,
                ).then((data) => {
                    if (data.status) {
                        this.fromTheHistory(index);
                    }
                });
            },
            newInHistory(histObject) {
                if (this.tableRow
                    && this.tableHeader
                    && this.tableRow['_tw_'+this.tableHeader.field]
                    && this.tableRow['_tw_'+this.tableHeader.field][this.mixin_type]
                ) {
                    this.tableRow['_tw_'+this.tableHeader.field][this.mixin_type].unshift(histObject);
                }
            },
            fromTheHistory(index) {
                if (this.tableRow
                    && this.tableHeader
                    && this.tableRow['_tw_'+this.tableHeader.field]
                    && this.tableRow['_tw_'+this.tableHeader.field][this.mixin_type]
                ) {
                    this.tableRow['_tw_'+this.tableHeader.field][this.mixin_type].splice(index, 1);
                }
            },
            showResPop(tab) {
                this.$emit('close-pop');
                eventBus.$emit('open-resource-popup', 'connections', 0, tab);
            },
        },
    }
</script>