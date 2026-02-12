
export class TwilioEndpoints {

    /**
     *
     * @param {Number} twilio_acc_id
     * @param {String} sms_phone
     * @param {String} sms_message
     * @param {int|null} table_id
     * @param {int|null} field_id
     * @param {int|null} row_id
     * @returns {Promise<unknown>}
     */
    static sms(twilio_acc_id, sms_phone, sms_message, table_id, field_id, row_id) {
        return new Promise((resolve) => {
            axios.post('/ajax/twilio/sms', {
                twilio_acc_id: twilio_acc_id,
                phone: sms_phone,
                message: sms_message,
                table_id: table_id,
                field_id: field_id,
                row_id: row_id,
            }).then(({ data }) => {
                Swal('Info','Message Sent!');
                resolve(data);
            }).catch(errors => {
                Swal('Info', getErrors(errors));
            });
        });
    }

    /**
     *
     * @param {Number} twilio_acc_id
     * @param {String} to_phone
     * @param {String|null} from_phone
     * @returns {Promise<unknown>}
     */
    static call(twilio_acc_id, to_phone, from_phone = null) {
        return new Promise((resolve) => {
            axios.post('/ajax/twilio/voice', {
                twilio_acc_id: twilio_acc_id,
                to_number: to_phone,
                from_number: from_phone,
            }).then(({ data }) => {
                Swal('Info','Call is initiated!');
                resolve(data);
            }).catch(errors => {
                Swal('Info', getErrors(errors));
            });
        });
    }

    /**
     *
     * @param {Number} twilio_acc_id
     * @param {String} call_sid
     * @returns {Promise<unknown>}
     */
    static callStop(twilio_acc_id, call_sid) {
        return new Promise((resolve) => {
            axios.post('/ajax/twilio/voice-stop', {
                twilio_acc_id: twilio_acc_id,
                call_sid: call_sid,
            }).then(({ data }) => {
                Swal('Info','Call is ended!');
                resolve(data);
            }).catch(errors => {
                Swal('Info', getErrors(errors));
            });
        });
    }

    /**
     *
     * @param {Number} twilio_acc_id
     * @param {Boolean} incoming
     * @returns {Promise<unknown>}
     */
    static browserToken(twilio_acc_id, incoming) {
        return new Promise((resolve) => {
            axios.post('/ajax/twilio/token', {
                twilio_acc_id: twilio_acc_id,
                incoming: incoming ? 1 : 0,
            }).then(({ data }) => {
                resolve(data);
            }).catch(errors => {
                Swal('Info', getErrors(errors));
            });
        });
    }

    /**
     *
     * @param {Number} user_id
     * @param {Number} table_id
     * @param {Number} field_id
     * @param {Number} row_id
     * @param {String} call_from
     * @param {String} call_to
     * @param {String} call_start
     * @param {Number} call_duration
     * @returns {Promise<unknown>}
     */
    static storeCallHistory(user_id, table_id, field_id, row_id, call_from, call_to, call_start, call_duration) {
        return new Promise((resolve) => {
            axios.post('/ajax/twilio/browser-call-history', {
                user_id: user_id,
                table_id: table_id,
                field_id: field_id,
                row_id: row_id,
                call_from: call_from,
                call_to: call_to,
                call_start: call_start,
                call_duration: call_duration,
            }).then(({ data }) => {
                resolve(data);
            }).catch(errors => {
                Swal('Info', getErrors(errors));
            });
        });
    }

    /**
     *
     * @param {Number} history_id
     * @returns {Promise<unknown>}
     */
    static removeHistory(history_id) {
        return new Promise((resolve) => {
            axios.post('/ajax/twilio/remove-history', {
                history_id: history_id,
            }).then(({ data }) => {
                resolve(data);
            }).catch(errors => {
                Swal('Info', getErrors(errors));
            });
        });
    }

    /**
     *
     * @param {String} incoming_app_id
     * @param {String} from_date
     * @returns {Promise<unknown>}
     */
    static incomingSms(incoming_app_id, from_date) {
        return new Promise((resolve) => {
            axios.post('/ajax/twilio/get-incoming-sms', {
                incoming_app_id: incoming_app_id,
                from_date: from_date,
            }).then(({ data }) => {
                resolve(data);
            }).catch(errors => {
                //Swal('Info', getErrors(errors));
            });
        });
    }
}