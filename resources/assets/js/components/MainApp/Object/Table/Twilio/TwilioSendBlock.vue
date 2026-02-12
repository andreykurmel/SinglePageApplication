<template>
    <div style="height: 30px;">
        <div class="form-group flex flex--center pull-right" style="height: 30px;">

            <select v-show="selected_addon.sms_send_time"
                    class="form-control input-sm w-md"
                    @change="updateAdn(selected_addon, 'sms_send_time')"
                    v-model="selected_addon.sms_send_time"
                    :disabled="!can_edit"
                    style="margin-right: 5px; width: 130px;">
                <option value="now">Now</option>
                <option value="at_time">At Time</option>
                <option value="field_specific">Record Specific</option>
            </select>

            <div v-show="selected_addon.sms_send_time !== 'now'" class="flex flex--center-v">
                <input v-show="selected_addon.sms_send_time === 'at_time'"
                       ref="sms_time_delay_inp"
                       @blur="hidePicker()"
                       :disabled="!can_edit"
                       class="form-control input-sm w-md"/>
                <select v-show="selected_addon.sms_send_time === 'field_specific'"
                        class="form-control input-sm w-md"
                        @change="updateAdn(selected_addon, 'sms_delay_record_fld_id')"
                        :disabled="!can_edit"
                        v-model="selected_addon.sms_delay_record_fld_id">
                    <option :value="null"></option>
                    <option v-for="fld in tableMeta._fields"
                            v-if="$root.inArray(fld.f_type, ['Date','Date Time','Time']) && !$root.inArray(fld.field, $root.systemFields)"
                            :value="fld.id"
                    >{{ fld.name }}</option>
                </select>
            </div>

        </div>

        <button class="btn btn-primary btn-sm blue-gradient pull-right"
                :style="$root.themeButtonStyle"
                :disabled="!!send_status || !!cannot_all_resend || !can_edit"
                @click="sendSms()"    
        >Send</button>
        <div v-show="send_status" class="pull-right flex flex--center twilio_status">{{ send_status }}</div>
        <button v-show="error_present > 5"
                class="btn btn-primary btn-sm twilio_cancel pull-right"
                @click="cancelSms()"
        >Cancel</button>

        <div v-show="!send_status && !table_row_id" class="pull-right flex flex--center twilio_status">Total {{ total_sms }} msg generated.</div>
    </div>
</template>

<script>
    import {SpecialFuncs} from "../../../../../classes/SpecialFuncs";

    export default {
        name: "TwilioSendBlock",
        components: {
        },
        data: function () {
            return {
                error_present: 0,
            }
        },
        props:{
            tableMeta: Object,
            selected_addon: Object,
            total_sms: Number,
            table_row_id: Number,
            hasHistory: Boolean,
            can_edit: Boolean|Number,
        },
        computed: {
            send_status() {
                let eml = this.selected_addon;
                let str = '';
                if (eml.prepared_sms > 0) {
                    if (eml.sent_sms > 0) {
                        if (eml.prepared_sms == eml.sent_sms) {
                            str = 'Completed. '+eml.prepared_sms+' sms sent.';
                        } else {
                            str = eml.sent_sms+' of '+eml.prepared_sms+' sms sent.';
                        }
                    } else {
                        str = 'In preparation';
                    }
                }
                return str;
            },
            cannot_all_resend() {
                return !this.table_row_id && !this.selected_addon.allow_resending && this.hasHistory;
            },
        },
        methods: {
            updateAdn(addon, type) {
                this.$emit('update-addon', addon, type);
            },
            sendSms() {
                axios.post('/ajax/addon-twilio-sett/send', {
                    twilio_add_id: this.selected_addon.id,
                    row_id: this.table_row_id || null,
                }).then(({data}) => {
                    if (data.result) {
                        Swal(data.result);
                        this.selected_addon.prepared_sms = data.prepared_sms;
                        this.selected_addon.sent_sms = data.sent_sms;
                        this.error_present = 0;
                    }
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                });
            },
            cancelSms() {
                axios.post('/ajax/addon-twilio-sett/cancel', {
                    twilio_add_id: this.selected_addon.id,
                }).then(({data}) => {
                    this.selected_addon.prepared_sms = 0;
                    this.selected_addon.sent_sms = 0;
                    this.error_present = 0;
                    this.$emit('load-history');
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                });
            },
            checkStatus() {
                axios.get('/ajax/addon-twilio-sett/status', {
                    params: { twilio_add_id: this.selected_addon.id, },
                }).then(({data}) => {
                    if (this.selected_addon.sent_sms == data.sent_sms) {
                        this.error_present += 1;
                    }
                    this.selected_addon.prepared_sms = data.prepared_sms;
                    this.selected_addon.sent_sms = data.sent_sms;
                    if (this.selected_addon.sent_sms >= this.selected_addon.prepared_sms) {
                        this.error_present = 0;
                        this.$emit('load-history');
                    }
                });
            },
            showDelayTimepicker() {
                this.$nextTick(() => {
                    let format = SpecialFuncs.dateTimeFormat();
                    let date = this.selected_addon.sms_delay_time || '';
                    date = date ? SpecialFuncs.convertToLocal(date, this.$root.user.timezone) : '';
                    $(this.$refs.sms_time_delay_inp).datetimepicker({
                        useCurrent: false,
                        defaultDate: date,
                        format: format,
                        widgetPositioning: {
                            horizontal: 'right',
                            vertical: 'auto'
                        },
                    }).on("input", (e) => {
                        let val = $(this.$refs.sms_time_delay_inp).val();
                        if (String(val).match(/am|pm/gi)) {
                            $(this.$refs.sms_time_delay_inp).val( moment( val ).format(format) );
                        }
                    });
                });
            },
            hidePicker() {
                let value = $(this.$refs.sms_time_delay_inp).val();
                value = moment( value ).format('YYYY-MM-DD HH:mm:ss');
                if (value === 'Invalid date') {
                    value = '';
                }
                value = value ? SpecialFuncs.convertToUTC(value, this.$root.user.timezone) : null;
                this.selected_addon.sms_delay_time = value;
                this.updateAdn(this.selected_addon, 'sms_delay_time');
            },
        },
        mounted() {
            this.showDelayTimepicker();
            setInterval(() => {
                if (this.selected_addon.prepared_sms) {
                    this.checkStatus();
                }
            }, 1500);
        },
        beforeDestroy() {
        }
    }
</script>

<style lang="scss" scoped>
    .blue-gradient {
        margin-right: 5px;
    }

    .twilio_status {
        height: 28px;
        margin-right: 10px;
        font-weight: bold;
    }
    .twilio_cancel {
        background-color: #bf5329 !important;
        border-color: #aa4a24;
        padding: 0 3px;
        margin-top: 3px;
        margin-right: 10px;
    }

    .w-sm {
        width: 70px;
        padding: 3px;
    }
    .w-md {
        width: 150px;
        padding: 3px;
    }
</style>