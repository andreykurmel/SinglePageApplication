
export class DcrEndpoints {

    /**
     *
     * @param {Object} dcr
     * @returns {Promise<unknown>}
     */
    static update(dcr) {
        window.vueRootApp.sm_msg_type = 1;

        let group_id = dcr.id;
        let fields = _.cloneDeep(dcr);//copy object
        window.vueRootApp.deleteSystemFields(fields);

        return new Promise((resolve) => {
            axios.put('/ajax/table-data-request', {
                table_data_request_id: group_id,
                fields: fields
            }).then(({ data }) => {
                resolve(data);
            }).catch(errors => {
                Swal({
                    title: 'Info',
                    text: getErrors(errors),
                    customClass: 'no-wrap'
                });
            }).finally(() => {
                window.vueRootApp.sm_msg_type = 0;
            });
        });
    }
    
    /**
     *
     * @param {Object} dcr
     * @param {String} field
     * @param {File} file
     * @returns {Promise<unknown>}
     */
    static uploadFile(dcr, field, file) {
        window.vueRootApp.sm_msg_type = 1;
        let group_id = dcr.id;
        let formData = new FormData();
        formData.append('table_data_request_id', group_id);
        formData.append('field', field);
        formData.append('u_file', file);

        return new Promise((resolve) => {
            axios.post('/ajax/table-data-request/dcr-file', formData, {
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            }).then(({ data }) => {
                dcr[field] = data.filepath;
                resolve(data);
            }).catch(errors => {
                Swal('Info', getErrors(errors));
            }).finally(() => {
                window.vueRootApp.sm_msg_type = 0;
            });
        });
    }
    
    /**
     *
     * @param {Object} dcr
     * @param {String} field
     * @returns {Promise<unknown>}
     */
    static delFile(dcr, field) {
        window.vueRootApp.sm_msg_type = 1;
        let group_id = dcr.id;

        return new Promise((resolve) => {
            axios.delete('/ajax/table-data-request/dcr-file', {
                params: {
                    table_data_request_id: group_id,
                    field: field,
                    u_file: 'delete',
                }
            }).then(({ data }) => {
                dcr[field] = null;
                resolve(data);
            }).catch(errors => {
                Swal('Info', getErrors(errors));
            }).finally(() => {
                window.vueRootApp.sm_msg_type = 0;
            });
        });
    }
    
    /**
     *
     * @param {Object} dcr
     * @param {String} field
     * @param {Object} val
     * @returns {Promise<unknown>}
     */
    static checkColumn(dcr, field, val) {
        window.vueRootApp.sm_msg_type = 1;

        return new Promise((resolve) => {
            axios.post('/ajax/table-data-request/dcr-column', {
                table_id: dcr.table_id,
                dcr_id: dcr.id,
                field_id: field,
                setting: val.setting,
                val: val.val,
            }).then(({ data }) => {
                dcr._fields_pivot = data._fields_pivot;
            }).catch(errors => {
                Swal('Info', getErrors(errors));
            }).finally(() => {
                window.vueRootApp.sm_msg_type = 0;
            });
        });
    }
}