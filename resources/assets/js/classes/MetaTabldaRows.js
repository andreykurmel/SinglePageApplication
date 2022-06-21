import {SpecialFuncs} from './SpecialFuncs';
import {FoundModel} from './FoundModel';
import {StimLinkParams} from './StimLinkParams';
import {RefCondHelper} from "./helpers/RefCondHelper";

export class MetaTabldaRows {
    /**
     * constructor
     * @param obj
     * @param user_hash
     */
    constructor(obj, user_hash) {
        obj = obj || {};

        //REQUIRED
        this.table_id = obj.table_id;
        this.user_hash = user_hash;

        //REQUEST
        this.page = obj.page || 1;
        this.rows_per_page = obj.rows_per_page || 50;
        this.sort = [];
        this.search_words = [];
        this.search_columns = [];
        this.direct_row_id = null;

        this.for_search_block = {
            keyWords: '',
            columns: [],
            direct_row_id: null,
        };

        //DATA
        this.master_row = obj.all_rows ? obj.all_rows[0] || null : null;
        this.all_rows = obj.all_rows || [];
        this.rows_count = obj.rows_count || 0;
        this.state_hash = '';

        this.filters_active = !!obj.filters_active;
        this.filters = obj.filters || [];

        //3D Model
        this.maps = obj.app_maps || [];
        this.rows_3d = obj.rows_3d || [];
        this.temp_3d_row = {};

        //STATES
        this.can_load = false;
        this.is_loaded = false;
    }

    /**
     * metaRequest
     * @returns {{table_id: (null|*), user_id: (null|*)}}
     */
    rowsRequest(non_active) {
        let s_words = this.search_words;
        if (this.for_search_block.keyWords) {
            s_words.push({word: this.for_search_block.keyWords});
        }
        return {
            ...SpecialFuncs.tableMetaRequest(this.table_id, undefined, this.filters_active && !non_active),
            ...{
                user_hash: this.user_hash,
                page: this.page,
                rows_per_page: this.rows_per_page,
                row_id: this.direct_row_id || this.for_search_block.direct_row_id,
                sort: this.sort,
                search_words: s_words,
                search_columns: this.for_search_block.columns.length ? this.for_search_block.columns : this.search_columns,
                applied_filters: this.filters,
            }
        };
    }

    /**
     * deleteSystemFields
     * @param arr
     */
    deleteSystemFields(arr) {
        return arr;
        /*for (let key in arr) {
            if (key.charAt(0) === '_') {
                delete arr[key];
            }
        }*/
    }

    /**
     * setModelAndGroup
     * @param {Object} foundModelRow
     * @param {StimLinkParams} stimParams
     */
    setModelAndGroup(foundModelRow, stimParams) {
        this.can_load = false;
        this.search_words = [];
        this.search_columns = [];
        if (foundModelRow && stimParams.link_fields) {
            let word = foundModelRow._virtual_mr ? '%' : '"';
            let arr = [];
            _.each(stimParams.link_fields, (fld) => {
                this.search_words.push({
                    word: word+foundModelRow[fld.link_field_db]+word,
                    type: 'and',
                    direct_fld: fld.data_field,
                });
                this.search_columns.push(fld.data_field);
                arr.push(foundModelRow[fld.link_field_db]);
            });
            this.can_load = true;
        }
    }

    /**
     *
     */
    localSort() {
        let fields = _.map(this.sort, el => el.field);
        let directions = _.map(this.sort, el => el.direction);
        this.all_rows = _.orderBy(this.all_rows, fields, directions);
    }

    /**
     * loadRows
     */
    loadRows() {
        //Not ask server if not all params are filled.
        if (this.can_load) {
            return new Promise((resolve) => {
                axios.post('/ajax/table-data/get', this.rowsRequest()).then(({data}) => {
                    this.master_row = data.rows ? data.rows[0] || null : null;
                    this.all_rows = data.rows;
                    this.state_hash = uuidv4();
                    this.rows_count = data.rows_count;
                    this.filters = SpecialFuncs.prepareFilters(this.filters, data.filters);
                    this.is_loaded = true;
                    this._convertRows();
                    resolve(data);
                }).catch(errors => {
                    Swal('', getErrors(errors));
                });
            });
        } else {
            return new Promise((resolve) => {
                this.master_row = null;
                this.all_rows = [];
                this.state_hash = uuidv4();
                this.rows_count = 0;
                this.is_loaded = true;
                resolve({});
            });
        }
    }

    /**
     * massReplace
     *
     * @param $col
     * @param $from
     * @param $to
     * @param {Object} foundModelRow
     * @param {StimLinkParams} stimParams
     * @returns {Promise}
     */
    massReplace($col, $from, $to, foundModelRow, stimParams) {
        if (foundModelRow._virtual_mr) {
            return new Promise((resolve) => { resolve({}); });
        }
        this.setModelAndGroup(foundModelRow, stimParams);
        return new Promise((resolve) => {
            axios.post('/ajax/table-data/do-replace', {
                table_id: this.table_id,
                term: String($from),
                replace: String($to),
                columns: [$col],
                request_params: this.rowsRequest(),
                force: true,
            }).then(({data}) => {
                resolve(data);
            }).catch(errors => {
                Swal('', getErrors(errors));
            });
        });
    }

    /**
     * reloadFilters
     */
    reloadFilters() {
        if (this.is_loaded && this.filters_active) {
            axios.post('/ajax/table-data/get', this.rowsRequest()).then(({data}) => {
                this.filters = SpecialFuncs.prepareFilters(this.filters, data.filters);
            }).catch(errors => {
                Swal('', getErrors(errors));
            });
        }
    }

    /**
     * Prepare for 3D Model
     * @private
     */
    _convertRows() {
        let arr = [];
        _.each(this.all_rows, (row) => {
            arr.push( this.convertOne(row) );
        });
        this.rows_3d = arr;
    }

    /**
     * Convert Row to 3D type.
     * @param row
     * @returns {{}}
     */
    convertOne(row) {
        let conv = {};
        if (row) {
            _.each(this.maps, (tablda, app) => {
                conv[app] = row[tablda];
                conv['_c_'+app] = tablda;
            });
        }
        return conv;
    }

    /**
     * Convert Key 'to 3D type' or 'from 3D type' (autodetect).
     * @param key
     * @returns {string}
     */
    convertKey(key) {
        let converted = '';
        _.each(this.maps, (tablda, app) => {
            if (!converted && key === tablda) {
                converted = app;
            }
            if (!converted && key === app) {
                converted = tablda;
            }
        });
        return converted;
    }

    /**
     * getRow
     * @param index
     * @returns {*}
     */
    getRow(index) {
        return this.all_rows[index];
    }

    /**
     * get3D
     * @param index
     * @returns {*}
     */
    get3D(index) {
        return this.rows_3d[index];
    }

    /**
     * set3DTempRow
     * @param row
     */
    set3DTempRow(row) {
        this.temp_3d_row = this.convertOne(row);
    }

    /**
     * getTemp3DRow
     * @returns {*}
     */
    getTemp3DRow() {
        return this.temp_3d_row || {};
    }

    /**
     * setRows
     * @param rows
     */
    setRows(rows) {
        this.master_row = rows ? rows[0] || null : null;
        this.all_rows = rows;
        this._convertRows();
    }

    /**
     * setDirectId
     * @param id
     */
    setDirectId(id) {
        this.direct_row_id = id;
        this.can_load = !!id;
    }

    /**
     * insertRow
     * @param tableRow
     * @param no_reload
     * @returns {Promise}
     */
    insertRow(tableRow, no_reload) {
        let fields = _.cloneDeep(tableRow);//copy object

        return new Promise((resolve) => {
            axios.post('/ajax/table-data', {
                table_id: this.table_id,
                fields: fields,
                get_query: {
                    table_id: this.table_id
                },
            }).then(({ data }) => {
                if (data.rows && data.rows.length) {
                    data.rows[0]._is_new = 1;
                    this.all_rows.push( data.rows[0] );
                    this.state_hash = uuidv4();
                    this.rows_count++;
                    this._convertRows();
                }
                if (!no_reload) {
                    this.reloadFilters();
                }
                resolve(data);
            }).catch(errors => {
                Swal('', getErrors(errors));
            });
        });
    }

    /**
     * updateRow
     * @param tableMeta
     * @param tableRow
     * @param no_reload
     * @returns {Promise}
     */
    updateRow(tableMeta, tableRow, no_reload) {
        return this.massUpdatedRows(tableMeta, [tableRow], no_reload);
    }

    /**
     *
     * @param tableMeta
     * @param massTableRows
     * @param no_reload
     * @returns {Promise<unknown>}
     */
    massUpdatedRows(tableMeta, massTableRows, no_reload) {
        let row_datas = {};
        _.each(massTableRows, (tableRow) => {
            let row_id = tableRow.id;
            let fields = _.cloneDeep(tableRow);//copy object
            this.deleteSystemFields(fields);

            //front-end RowGroups and CondFormats
            RefCondHelper.updateRGandCFtoRow(tableMeta, tableRow);

            row_datas[row_id] = fields;
        });

        return new Promise((resolve) => {
            axios.put('/ajax/table-data/mass', {
                table_id: this.table_id,
                row_datas: row_datas,
                get_query: {
                    table_id: this.table_id
                },
            }).then(({data}) => {
                _.each(data.rows, (d_row) => {
                    let idx = _.findIndex(this.all_rows, el => el.id == d_row.id);
                    if (idx > -1) {
                        this.master_row = d_row || null;
                        this.state_hash = uuidv4();
                        this.all_rows.splice(idx, 1, d_row);
                        this._convertRows();
                    }
                });
                if (!no_reload) {
                    this.reloadFilters();
                }
                resolve(data);
            }).catch(errors => {
                Swal('', getErrors(errors));
            });
        });
    }

    /**
     * deleteRow
     * @param tableRow
     * @param no_reload
     * @returns {Promise}
     */
    deleteRow(tableRow, no_reload) {
        let row_id = tableRow.id;

        return new Promise((resolve) => {
            axios.delete('/ajax/table-data', {
                params: {
                    table_id: this.table_id,
                    row_id: row_id,
                }
            }).then(({data}) => {
                this._afterDelete([row_id], no_reload);
                resolve(data);
            }).catch(errors => {
                Swal('', getErrors(errors));
            });
        });
    }

    /**
     * massDelete
     * @param rows_ids
     * @param no_reload
     * @returns {Promise}
     */
    deleteSelected(rows_ids, no_reload) {
        return new Promise((resolve) => {
            if (rows_ids && rows_ids.length) {
                axios.post('/ajax/table-data/delete-selected', {
                    table_id: this.table_id,
                    rows_ids: rows_ids,
                }).then(({data}) => {
                    this._afterDelete(rows_ids, no_reload);
                    resolve(data);
                }).catch(errors => {
                    Swal('', getErrors(errors));
                });
            } else {
                resolve({});
            }
        });
    }

    /**
     *
     * @param rows_ids
     * @param no_reload
     * @private
     */
    _afterDelete(rows_ids, no_reload) {
        this.all_rows = _.filter(this.all_rows, (r) => {
            return !in_array(r.id, rows_ids);
        });
        this.state_hash = uuidv4();
        this.rows_count -= rows_ids.length;
        this._convertRows();
        if (!no_reload) {
            this.reloadFilters();
        }
    }
}