<template>
    <div style="height: 30px;">
        <button v-if="hasHistory"
                class="btn btn-primary btn-sm blue-gradient pull-right"
                :style="$root.themeButtonStyle"
                @click="clearHistory()"
        >Clear History</button>

        <div class="form-group flex flex--center pull-right" style="height: 30px;">

            <select v-show="selected_addon.email_send_time"
                    class="form-control input-sm w-sm"
                    @change="updateAdn(selected_addon, 'email_delay_record_fld_id')"
                    v-model="selected_addon.email_send_time"
                    style="margin-right: 5px;">
                <option value="now">Now</option>
                <option value="at_time">At Time</option>
            </select>

            <div v-show="selected_addon.email_send_time !== 'now'" class="flex flex--center-v">
                <input v-show="!selected_addon.email_delay_by_rec"
                       ref="email_time_delay_inp"
                       @blur="hidePicker()"
                       class="form-control input-sm w-md"/>
                <select v-show="selected_addon.email_delay_by_rec"
                        class="form-control input-sm w-md"
                        @change="updateAdn(selected_addon, 'email_delay_record_fld_id')"
                        v-model="selected_addon.email_delay_record_fld_id">
                    <option :value="null"></option>
                    <option v-for="fld in tableMeta._fields"
                            v-if="$root.inArray(fld.f_type, ['Date','Date Time','Time']) && !$root.inArray(fld.field, $root.systemFields)"
                            :value="fld.id"
                    >{{ fld.name }}</option>
                </select>
                <span>&nbsp;&nbsp;</span>
                <span class="indeterm_check__wrap">
                    <span class="indeterm_check" @click="updCheck('email_delay_by_rec')">
                        <i v-if="selected_addon.email_delay_by_rec" class="glyphicon glyphicon-ok group__icon"></i>
                    </span>
                </span>
                <label class="no-wrap no-margin">&nbsp;by record.&nbsp;</label>
            </div>

        </div>

        <button class="btn btn-primary btn-sm blue-gradient pull-right"
                :style="$root.themeButtonStyle"
                :disabled="!!send_status || !!cannot_all_resend"
                @click="sendEmail()"
        >Send</button>
        <div v-show="send_status" class="pull-right flex flex--center email_status">{{ send_status }}</div>
        <button v-show="error_present > 5"
                class="btn btn-primary btn-sm email_cancel pull-right"
                @click="cancelEmail()"
        >Cancel</button>

        <div v-show="!send_status && !email_row_id" class="pull-right flex flex--center email_status">Total {{ total_emails }} emails generated.</div>
    </div>
</template>

<script>
    import {SpecialFuncs} from "../../../../../classes/SpecialFuncs";

    export default {
        name: "EmailSendBlock",
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
            total_emails: Number,
            email_row_id: Number,
            hasHistory: Boolean,
        },
        computed: {
            send_status() {
                let eml = this.selected_addon;
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
            cannot_all_resend() {
                return !this.email_row_id && !this.selected_addon.allow_resending && this.hasHistory;
            },
        },
        methods: {
            updCheck(key) {
                this.selected_addon[key] = !this.selected_addon[key];
                this.updateAdn(this.selected_addon, key);
            },
            updateAdn(addon, type) {
                this.$emit('update-addon', addon, type);
            },
            sendEmail() {
                axios.post('/ajax/addon-email-sett/send', {
                    email_add_id: this.selected_addon.id,
                    row_id: this.email_row_id || null,
                }).then(({data}) => {
                    if (data.result) {
                        Swal(data.result);
                        this.selected_addon.prepared_emails = data.prepared_emails;
                        this.selected_addon.sent_emails = data.sent_emails;
                        this.error_present = 0;
                    }
                }).catch(errors => {
                    Swal('', getErrors(errors));
                });
            },
            cancelEmail() {
                axios.post('/ajax/addon-email-sett/cancel', {
                    email_add_id: this.selected_addon.id,
                }).then(({data}) => {
                    this.selected_addon.prepared_emails = 0;
                    this.selected_addon.sent_emails = 0;
                    this.error_present = 0;
                    this.$emit('load-history');
                }).catch(errors => {
                    Swal('', getErrors(errors));
                });
            },
            clearHistory() {
                axios.delete('/ajax/addon-email-sett/history', {
                    params: {
                        email_add_id: this.selected_addon.id,
                        row_id: this.email_row_id,
                    },
                }).then(({data}) => {
                    this.$emit('load-history');
                }).catch(errors => {
                    Swal('', getErrors(errors));
                });
            },
            checkStatus() {
                axios.get('/ajax/addon-email-sett/status', {
                    params: { email_add_id: this.selected_addon.id, },
                }).then(({data}) => {
                    if (this.selected_addon.sent_emails == data.sent_emails) {
                        this.error_present += 1;
                    }
                    this.selected_addon.prepared_emails = data.prepared_emails;
                    this.selected_addon.sent_emails = data.sent_emails;
                    if (this.selected_addon.sent_emails >= this.selected_addon.prepared_emails) {
                        this.error_present = 0;
                        this.$emit('load-history');
                    }
                });
            },
            showDelayTimepicker() {
                this.$nextTick(() => {
                    let format = SpecialFuncs.dateTimeFormat();
                    let date = this.selected_addon.email_delay_time || '';
                    date = date ? SpecialFuncs.convertToLocal(date, this.$root.user.timezone) : '';
                    $(this.$refs.email_time_delay_inp).datetimepicker({
                        useCurrent: false,
                        defaultDate: date,
                        format: format,
                        widgetPositioning: {
                            horizontal: 'right',
                            vertical: 'auto'
                        },
                    }).on("input", (e) => {
                        let val = $(this.$refs.email_time_delay_inp).val();
                        if (String(val).match(/am|pm/gi)) {
                            $(this.$refs.email_time_delay_inp).val( moment( val ).format(format) );
                        }
                    });
                });
            },
            hidePicker() {
                let value = $(this.$refs.email_time_delay_inp).val();
                value = moment( value ).format('YYYY-MM-DD HH:mm:ss');
                if (value === 'Invalid date') {
                    value = '';
                }
                value = value ? SpecialFuncs.convertToUTC(value, this.$root.user.timezone) : null;
                this.selected_addon.email_delay_time = value;
                this.updateAdn(this.selected_addon, 'email_delay_time');
            },
        },
        mounted() {
            this.showDelayTimepicker();
            setInterval(() => {
                if (this.selected_addon.prepared_emails) {
                    this.checkStatus();
                }
            }, 1000);
        },
        beforeDestroy() {
        }
    }
</script>

<style lang="scss" scoped>
    .blue-gradient {
        margin-right: 5px;
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

    .w-sm {
        width: 70px;
        padding: 3px;
    }
    .w-md {
        width: 150px;
        padding: 3px;
    }
</style>