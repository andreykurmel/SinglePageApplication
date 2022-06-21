
export class Endpoints {

    /**
     *
     * @param {Number} table_id
     * @param {Array|null} row_ids
     * @param {Object|null} request_params
     * @param {Object|null} replaces
     * @param {Array|null} only_columns
     * @returns {Promise<unknown>}
     */
    static massCopyRows(table_id, row_ids = null, request_params = null, replaces = null, only_columns = null) {
        return new Promise((resolve) => {
            axios.post('/ajax/table-data/mass-copy', {
                table_id: table_id,
                rows_ids: row_ids || null,
                request_params: request_params || null,
                replaces: replaces || null,
                only_columns: only_columns || null,
            }).then(({data}) => {
                resolve(data);
            }).catch(errors => {
                Swal('', getErrors(errors));
            }).finally(() => $.LoadingOverlay('hide'));
        });
    }

    /**
     *
     * @param input
     * @returns {AxiosPromise<any>}
     */
    static fileUpload(input) {
        let data = new FormData();
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
}