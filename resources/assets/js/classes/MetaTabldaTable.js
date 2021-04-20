import {columnSettRadioFields} from './../app';

export class MetaTabldaTable {
    /**
     * constructor
     * @param obj
     */
    constructor(obj) {
        obj = obj || {};

        //REQUIRED
        this.table_id = obj.table_id;
        this.user_id = obj.user_id;
        this.user_hash = obj.user_hash;
        //REQUIRED

        this.load_process = false;
        this.is_loaded = false;
        this.params = null;
        this.empty_row = {};

        this.can_add = false;
        this.can_edit = false;
        this.can_delete = false;
    }

    /**
     * metaRequest
     * @returns {{table_id: (null|*), user_id: (null|*)}}
     */
    metaRequest() {
        return {
            table_id: this.table_id,
            user_id: this.user_id,
            user_hash: this.user_hash,
        };
    }

    /**
     * deleteSystemFields
     * @param arr
     */
    deleteSystemFields(arr) {
        for (let key in arr) {
            if (key.charAt(0) === '_') {
                delete arr[key];
            }
        }
    }

    /**
     * loadHeaders
     * Request is fired only once!!!
     * @returns {Promise}
     */
    loadHeaders() {
        if (this.is_loaded) {
            return new Promise((resolve) => { resolve() });
        }

        return new Promise((resolve) => {
            if (!this.load_process) {
                //start loading
                this.load_process = true;
                axios.post('/ajax/table-data/get-headers', this.metaRequest()).then(({ data }) => {
                    this.params = data;
                    this.params._view_rows_count = true;
                    this.empty_row = this.emptyObject();
                    this.is_loaded = true;
                    this.load_process = false;

                    let edit = data._is_owner;
                    _.each(data._fields, (tableHeader) => {
                        edit = edit || $.inArray(tableHeader.field, data._current_right.edit_fields) > -1;
                    });

                    this.can_add = data._is_owner || (data._current_right && data._current_right.can_add);
                    this.can_edit = edit;
                    this.can_delete = data._is_owner || (data._current_right && data._current_right.delete_row_groups.length);

                    resolve();
                }).catch(errors => {
                    console.log('', getErrors(errors));
                });

            } else {

                //loading already started -> wait for finishing
                let interval = setInterval(() => {
                    if (this.is_loaded) {
                        resolve();
                        clearInterval(interval);
                    }
                }, 300);
            }
        });
    }

    /**
     * emptyObject
     * @returns {{}}
     */
    emptyObject() {
        let obj = {};
        if (this.params) {
            for (let i in this.params._fields) {
                if (this.params._fields[i].f_type === 'Boolean') {
                    obj[this.params._fields[i].field] = this.params._fields[i].f_default == '1';
                } else {
                    obj[this.params._fields[i].field] = null;
                }
            }
        }
        obj._temp_id = uuidv4();
        return obj;
    }

    /**
     * updateSettings
     * @param tableRow
     */
    updateSettings(tableRow) {
        return axios.put('/ajax/settings/data', {
            table_field_id: tableRow.id,
            field: tableRow._changed_field,
            val: tableRow[tableRow._changed_field],
        }).then(({ data }) => {
            if (in_array(tableRow._changed_field, columnSettRadioFields))
            {
                this.is_loaded = false;
                this.loadHeaders();
            }
        }).catch(errors => {
            Swal('', getErrors(errors));
        });
    }
}