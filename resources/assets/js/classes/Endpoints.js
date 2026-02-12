import {RefCondHelper} from "./helpers/RefCondHelper";
import {SpecialFuncs} from "./SpecialFuncs";

export class Endpoints {

    /**
     *
     * @param {Number} table_id
     * @param {Array|null} row_ids
     * @param {Object|null} request_params
     * @param {Object|null} replaces
     * @param {Array|null} only_columns
     * @param {Array|null} no_inheritance_ids
     * @returns {Promise<unknown>}
     */
    static massCopyRows(table_id, row_ids = null, request_params = null, replaces = null, only_columns = null, no_inheritance_ids = null) {
        return new Promise((resolve) => {
            axios.post('/ajax/table-data/mass-copy', {
                table_id: table_id,
                rows_ids: row_ids || null,
                request_params: request_params || null,
                replaces: replaces || null,
                only_columns: only_columns || null,
                no_inheritance_ids: no_inheritance_ids || null,
            }).then(({data}) => {
                resolve(data);
            }).catch(errors => {
                Swal('Info', getErrors(errors));
            }).finally(() => $.LoadingOverlay('hide'));
        });
    }

    /**
     *
     * @param {Number} table_id
     * @param {Array|null} row_ids
     * @param {Object|null} request_params
     * @param {Array|null} no_inheritance_ids
     * @returns {Promise<unknown>}
     */
    static massDeleteRows(table_id, row_ids = null, request_params = null, no_inheritance_ids = null) {
        return new Promise((resolve) => {
            axios.post('/ajax/table-data/delete-selected', {
                table_id: table_id,
                rows_ids: row_ids || null,
                request_params: request_params || null,
                no_inheritance_ids: no_inheritance_ids || null,
            }).then(({ data }) => {
                resolve(data);
            }).catch(errors => {
                Swal('Info', getErrors(errors));
            });
        });
    }

    /**
     *
     * @param {Object} tableMeta
     * @param {Array} updatedRows
     * @param {Object} requestParams
     * @returns {Promise<unknown>}
     */
    static massUpdateRows(tableMeta, updatedRows, requestParams) {
        let row_datas = {};
        _.each(updatedRows, (tableRow) => {
            let row_id = tableRow.id;
            let fields = _.cloneDeep(tableRow);//copy object
            window.vueRootApp.deleteSystemFields(fields);

            //front-end RowGroups and CondFormats
            RefCondHelper.updateRGandCFtoRow(tableMeta, tableRow);

            row_datas[row_id] = fields;
        });

        window.vueRootApp.sm_msg_type = 1;
        window.vueRootApp.prevent_cell_edit = true;
        return new Promise((resolve, reject) => {
            axios.put('/ajax/table-data/mass', {
                table_id: tableMeta.id,
                row_datas: row_datas,
                get_query: requestParams,
            }).then(({ data }) => {
                resolve(data, row_datas);
            }).catch(errors => {
                Swal('Info', getErrors(errors));
                reject(errors);
            }).finally(() => {
                window.vueRootApp.sm_msg_type = 0;
                window.vueRootApp.prevent_cell_edit = false;
            });
        });
    }

    /**
     *
     * @param {Object} tableMeta
     * @param {Object} tableRow
     * @param {Object} requestParams
     * @returns {Promise<unknown>}
     */
    static insertRow(tableMeta, tableRow, requestParams) {
        let fields = _.cloneDeep(tableRow);//copy object
        window.vueRootApp.sm_msg_type = 1;
        return new Promise((resolve) => {
            axios.post('/ajax/table-data', {
                table_id: tableMeta.id,
                fields: fields,
                get_query: requestParams,
            }).then(({data}) => {
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
     * @param {Object} tableMeta
     * @param {Object} tableRow
     * @returns {Promise<unknown>}
     */
    static deleteRow(tableMeta, tableRow) {
        let fields = _.cloneDeep(tableRow);//copy object
        window.vueRootApp.sm_msg_type = 1;
        return new Promise((resolve) => {
            axios.delete('/ajax/table-data', {
                params: {
                    table_id: tableMeta.id,
                    row_id: tableRow.id,
                }
            }).then(({ data }) => {
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
     * @param {Object} requestParams
     * @returns {Promise<unknown>}
     */
    static getOnlyRows(requestParams) {
        return new Promise((resolve) => {
            return axios.post('/ajax/table-data/get-only-rows', requestParams).then(({ data }) => {
                resolve(data);
            }).catch(errors => {
                Swal('Info', getErrors(errors));
            });
        });
    }

    /**
     *
     * @param input
     * @returns {AxiosPromise<any>}
     */
    static fileUpload(input) {
        let data = new FormData();
        data.append('replace_file_id', input.replace_file_id || 0);
        data.append('table_id', input.table_id);
        data.append('table_field_id', input.table_field_id);
        data.append('row_id', input.row_id);
        data.append('file', input.file);
        data.append('file_link', input.file_link || '');
        data.append('file_new_name', input.file_new_name || '');
        data.append('clear_before', input.clear_before || 0);
        if (input.special_params) {
            data.append('special_params', JSON.stringify(input.special_params));
        }

        return axios.post('/ajax/files', data, {
            headers: {
                'Content-Type': 'multipart/form-data'
            },
        });
    }

    /**
     *
     * @param report {Object}
     * @param tableMeta {Object}
     * @param requestParams {Object}
     * @param directRowId {Number}
     * @param silent {Boolean}
     * @returns {Promise<unknown>}
     */
    static runReportsMaking(report, tableMeta, requestParams, directRowId, silent) {
        let params = SpecialFuncs.dataRangeRequestParams(report.report_data_range, tableMeta.id, requestParams);
        if (!silent) {
            $.LoadingOverlay('show');
        }

        return axios.post('/ajax/addon-report/make', {
            report_id: report.id,
            request_params: params,
            direct_row_id: directRowId,
        }).then(({ data }) => {
            if (!silent) {
                Swal('Info', 'Reports are ready!');
            }
        }).catch(errors => {
            Swal('Info', getErrors(errors));
        }).finally(() => {
            if (!silent) {
                $.LoadingOverlay('hide');
            }
        });
    }

    /**
     *
     * @param type
     * @param body
     * @returns {Promise<unknown>}
     */
    static listCloudFiles(type, body) {
        let url = '';
        switch (type) {
            case 'google': url = '/ajax/import/google-drive/all-files'; break;
            case 'dropbox': url = '/ajax/import/dropbox/all-files'; break;
            case 'one_drive': url = '/ajax/import/one-drive/all-files'; break;
        }

        window.vueRootApp.sm_msg_type = 1;
        return new Promise((resolve, reject) => {
            axios.post(url, body).then(({ data }) => {
                resolve(data);
            }).catch(errors => {
                Swal('Info', getErrors(errors));
                reject(errors);
            }).finally(() => {
                window.vueRootApp.sm_msg_type = 0;
            });
        });
    }

    /**
     *
     * @returns {Promise<unknown>}
     */
    static reloadSharedDDLs() {
        return new Promise((resolve, reject) => {
            axios.post('/ajax/get-settings', {
                only_part: {
                    shared_ddls: 1,
                },
            }).then(({ data }) => {
                if (data && data.shared_ddls) {
                    window.vueRootApp.settingsMeta.shared_ddls = data.shared_ddls;
                }
                resolve(data);
            }).catch(errors => {
                Swal('Info', getErrors(errors));
                reject(errors);
            });
        });
    }

    /**
     *
     * @param tableId
     * @returns {Promise<unknown>}
     */
    static loadTbHeaders(tableId) {
        return new Promise((resolve, reject) => {
            if (window.vueRootApp.otherTableMetas[tableId]) {
                resolve(window.vueRootApp.otherTableMetas[tableId]);
            } else {
                axios.post('/ajax/table-data/get-headers', {
                    table_id: tableId,
                    user_id: window.vueRootApp.user.id,
                }).then(({ data }) => {
                    window.vueRootApp.otherTableMetas[tableId] = data;
                    resolve(data);
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                    reject(errors);
                });
            }
        });
    }
}