<script>
    /**
     *  should be present:
     *
     *  this.tableMeta: Object
     *  */
    import {SpecialFuncs} from './../../classes/SpecialFuncs';

    import CheckRowBackendMixin from './../_Mixins/CheckRowBackendMixin.vue';

    export default {
        data: function () {
            return {
                empty_row: SpecialFuncs.emptyRow(this.tableMeta),
                activeTab: 'method',
                importAction: '',
            };
        },
        mixins: [
            CheckRowBackendMixin,
        ],
        methods: {
            inArray(item, array) {
                return $.inArray(item, array) > -1;
            },
            emptyHeader() {
                if (!this.empty_row) {
                    this.empty_row = SpecialFuncs.emptyRow(this.tableMeta);
                    this.empty_row._is_def_cell = true;
                }
                let erow = _.clone(this.empty_row);
                erow[''] = '';
                return {
                    status: 'add',
                    table_id: this.tableMeta.id,
                    id: '',
                    name: '',
                    formula_symbol: '',
                    rating_icon: '',
                    field: '',
                    col: null,
                    f_type: 'String',
                    f_size: 64,
                    f_default: '',
                    f_required: 0,
                    f_format: '',
                    input_type: 'Input',
                    _source_type: '',
                    _empty_row: erow,
                    _f_format_l: '',
                    _f_format_r: 2,
                    _ref_tmp_id: null,
                };
            },
            copyFrom(Headers, forceStat) {
                if (!this.empty_row) {
                    this.empty_row = SpecialFuncs.emptyRow(this.tableMeta);
                    this.empty_row._is_def_cell = true;
                }
                let result = [];
                for (let i in Headers) {
                    if (!this.inArray(Headers[i].field, this.$root.systemFields)) {
                        let ffrmat = ['Attachment','Decimal','Currency','Percentage','Progress Bar','Rating','Auto String'].indexOf(Headers[i].f_type) > -1
                            ? String(Headers[i].f_format || '').split('-')
                            : [Headers[i].f_format];
                        let erow = _.clone(this.empty_row);
                        erow[Headers[i].field] = Headers[i].f_default;
                        let tmp = _.clone(Headers[i]);
                        tmp.status = forceStat || 'edit';
                        tmp.col = null;
                        tmp.table_id = this.tableMeta.id;
                        tmp._source_type = tmp._source_type || 'Single Line Text';
                        tmp._empty_row = erow;
                        tmp._f_format_l = ffrmat[0] || '';
                        tmp._f_format_r = ffrmat[1] || (Headers[i].f_type === 'Auto String' ? 7 : 2);
                        tmp._ref_tmp_id = null;
                        result.push(tmp);

                        if (Headers[i].f_type === 'User') {
                            this.checkRowOnBackend(this.tableMeta.id, tmp._empty_row);
                        }
                    }
                }
                return result;
            },
            canViewEditCol(col, fields_type) {
                return this.tableMeta._is_owner
                    ||
                    (this.tableMeta._current_right && this.inArray(col.field, this.tableMeta._current_right[fields_type]))
                    ||
                    col.status === 'add';
            },

            //Prepare Fields
            /**
             *
             * @param headersArray {Object}
             * @param hKey {string}
             * @param headers {Array}
             * @param fields {Array}
             * @private
             */
            _fillTableHeaders(headersArray, hKey, headers, fields) {
                hKey = hKey || 'def';
                if (this.inArray(this.importAction, ['new'])) {
                    headersArray[hKey] = this.copyFrom(headers, 'add');
                }
                _.each(fields, (elem, i) => {
                    if (headersArray[hKey][i] && this.canViewEditCol(headersArray[hKey][i], 'edit_fields')) {
                        headersArray[hKey][i].col = i;
                    }
                });
            },
            /**
             *
             * @param file {UploadedFile}
             * @param link {string}
             * @param settings {Object}: {filename: '', firstHeader: true, ...}
             * @param error_to {string}
             * @returns {Promise<unknown>}
             * @private
             */
            _sendCsvRequest(file, link, settings, error_to) {
                let data = new FormData();
                data.append('csv', file);
                data.append('csv_link', link);
                data.append('csv_settings', JSON.stringify(settings));

                this.$root.sm_msg_type = 2;
                return axios.post('/ajax/import/get-fields/csv', data, {
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    }
                }).catch(errors => {
                    this.swalError(errors, error_to);
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
            /**
             *
             * @param tableHeaders {Object}
             * @param tkey {string}
             * @param ocrSettings {{sourceFile: string, firstHeader: boolean}}
             * @param error_to
             */
            getFieldsFromOCR(tableHeaders, tkey, ocrSettings, error_to) {
                if (ocrSettings.sourceFile) {
                    this._sendCsvRequest(null, ocrSettings.sourceFile, ocrSettings, error_to).then(({data}) => {
                        this._fillTableHeaders(tableHeaders, tkey, data.headers, data.csv_fields);
                        this.fieldsColumns = data.csv_fields;
                        this.activeTab = 'fields';
                        ocrSettings.sourceFile = data.csv_file;
                    });
                }
            },
            /**
             *
             * @param tableHeaders
             * @param tkey
             * @param error_to
             */
            getFieldsFromCSV(tableHeaders, tkey, error_to) {
                if (!this.importAction) {
                    Swal('Please select an option.');
                    return;
                }

                this.xlsSheets = [];
                if (!tableHeaders) {
                    this.csvSettings.xls_sheet = '';
                }

                let file = this.$refs.csv_upload ? this.$refs.csv_upload.files[0] : null;
                if (file || this.csv_link) {
                    this._sendCsvRequest(file, this.csv_link, this.csvSettings, error_to).then(({ data }) => {
                        if (tableHeaders) {
                            this._fillTableHeaders(tableHeaders, tkey, data.headers, data.csv_fields);
                            this.csv_link = data.csv_file;
                            this.fieldsColumns = data.csv_fields;
                            this.csvSettings.filename = data.csv_file;
                            this.activeTab = 'fields';
                            this.xlsSheets = [];
                        } else {
                            this.xlsSheets = data.xls_sheets;
                        }
                        this.saveCsvSett();
                    });
                } else {
                    Swal('No file', '', 'info');
                }
            },
            /**
             *
             * @param tableHeaders
             * @param tkey
             * @param error_to
             */
            getFieldsFromPaste(tableHeaders, tkey, error_to) {
                if (!this.importAction) {
                    Swal('Please select an option.');
                    return;
                }
                tkey = tkey || 'def';
                if (this.paste_data) {
                    this.pasteFieldsFromBackend(error_to).then((data) => {
                        this._fillTableHeaders(tableHeaders, tkey, data.headers, data.fields);
                        this.fieldsColumns = data.fields;
                        this.activeTab = 'fields';
                    });
                } else {
                    Swal('No pasted data', '', 'info');
                }
            },
            /**
             *
             * @param tableHeaders
             * @param tkey
             * @param error_to
             */
            getFieldsFromGSheet(tableHeaders, tkey, error_to) {
                if (!this.importAction) {
                    Swal('Please select an option.');
                    return;
                }
                tkey = tkey || 'def';
                if (this.g_sheets_file) {
                    this.$root.sm_msg_type = 2;
                    axios.post('/ajax/import/get-fields/g-sheet', {
                        g_sheets_settings: this.g_sheets_settings,
                        g_sheets_file: this.g_sheets_file,
                        g_sheets_element: this.g_sheets_element,
                    }).then(({ data }) => {
                        this._fillTableHeaders(tableHeaders, tkey, data.headers, data.fields);
                        this.fieldsColumns = data.fields;
                        this.activeTab = 'fields';
                    }).catch(errors => {
                        this.swalError(errors, error_to);
                    }).finally(() => {
                        this.$root.sm_msg_type = 0;
                    });
                } else {
                    Swal('Google Sheets Link is empty!', '', 'info');
                }
            },
            /**
             *
             * @param tableHeaders
             * @param tkey
             * @param error_to
             */
            getFieldsFromMySQL(tableHeaders, tkey, error_to) {
                if (!this.importAction) {
                    Swal('Please select an option.');
                    return;
                }
                tkey = tkey || 'def';
                if (this.mysqlSettings.host) {
                    this.$root.sm_msg_type = 2;
                    axios.post('/ajax/import/get-fields/mysql', {
                        mysql_settings: this.mysqlSettings
                    }).then(({ data }) => {
                        this._fillTableHeaders(tableHeaders, tkey, data.headers, data.mysql_fields);
                        this.mysqlColumns = data.mysql_fields;
                        this.mysqlSettings.connection_id = data.connection_id;
                        this.activeTab = 'fields';
                    }).catch(errors => {
                        this.swalError(errors, error_to);
                    }).finally(() => {
                        this.$root.sm_msg_type = 0;
                    });
                } else {
                    Swal('No host', '', 'info');
                }
            },
            /**
             *
             * @param tableHeaders
             * @param tkey
             * @param error_to
             */
            getScrapWeb(tableHeaders, tkey, error_to) {
                if (!this.importAction) {
                    Swal('Please select an option.');
                    return;
                }
                if (this.web_html_url || this.web_xml_url || this.web_xml_file) {
                    this.$root.sm_msg_type = 2;
                    axios.post('/ajax/import/get-fields/web-scrap', {
                        web_action: this.webAction,
                        web_url: this.webAction === 'html' ? this.web_html_url : this.web_xml_url,
                        web_query: this.web_html_query,
                        web_xpath: this.webAction === 'html' ? '' : this.web_xml_xpath,
                        web_index: this.web_html_index,
                        web_xml_file: this.web_xml_file,
                        web_xml_nested: this.web_xml_nested,
                        web_scrap_headers: this.web_scrap_headers,
                        web_scrap_xpath_query: this.web_scrap_xpath_query,
                    }).then(({ data }) => {
                        if (!data.fields || !data.fields.length) {
                            this.add_err_msg.show = true;
                            this.add_err_msg.body = 'Element(s) not found.';
                            return;
                        }

                        this._fillTableHeaders(tableHeaders, tkey, data.headers, data.fields);
                        this.fieldsColumns = data.fields;
                        this.activeTab = 'fields';
                    }).catch(errors => {
                        this.swalError(errors, error_to);
                    }).finally(() => {
                        this.$root.sm_msg_type = 0;
                    });
                } else {
                    Swal('Url or XPath is empty!', '', 'info');
                }
            },
            /**
             *
             * @param tableHeaders
             * @param tkey
             * @param airtable_item {{user_key_id: int, table_name: string, fromtype: string}}
             * @param error_to
             */
            loadFieldsFromAirtable(tableHeaders, tkey, airtable_item, error_to) {
                if (!this.importAction) {
                    Swal('Please select an option.');
                    return;
                }
                if (airtable_item && airtable_item.user_key_id && airtable_item.table_name) {
                    this.$root.sm_msg_type = 2;
                    axios.post('/ajax/import/get-fields/airtable', {
                        user_key_id: airtable_item.user_key_id,
                        table_name: airtable_item.table_name,
                        fromtype: airtable_item.fromtype,
                    }).then(({ data }) => {
                        this._fillTableHeaders(tableHeaders, tkey, data.headers, data.fields);
                        this.fieldsColumns = data.fields;
                    }).catch(errors => {
                        this.swalError(errors, error_to);
                    }).finally(() => {
                        this.$root.sm_msg_type = 0;
                    });
                } else {
                    Swal('Base or Table name are empty!', '', 'info');
                }
            },
            /**
             *
             * @param errors
             * @param error_to
             */
            swalError(errors, error_to) {
                Swal('', getErrors(errors));
                if (error_to) {
                    this[error_to] = getErrors(errors)
                }
            },

        },
    }
</script>