<script>
    /**
     *  should be present:
     *
     *  this.tableMeta: Object
     *  this.fieldsColumns: Array
     *  */
    import {SpecialFuncs} from './../../classes/SpecialFuncs';
    import {StringHelper} from './../../classes/helpers/StringHelper';

    import CheckRowBackendMixin from './../_Mixins/CheckRowBackendMixin.vue';

    export default {
        data: function () {
            return {
                empty_row: SpecialFuncs.emptyRow(this.tableMeta),
                activeTab: 'method',
                importAction: '',
                usedNames: [],
                jira_cache: '',
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
                return {
                    status: 'add',
                    table_id: this.tableMeta.id,
                    id: '',
                    name: '',
                    formula_symbol: '',
                    rating_icon: '',
                    field: '',
                    col: null,
                    last_col_corr: '',
                    trps_type: 'inheritance',
                    trps_value: null,
                    f_type: 'String',
                    f_size: 64,
                    f_required: 0,
                    f_format: '',
                    input_type: 'Input',
                    _source_type: '',
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
                        if (Headers[i].f_type === 'Auto Number' && !String(Headers[i].f_format || '').match('-')) {
                            Headers[i].f_format = String(Headers[i].f_format || '0') + '-blank';
                        }

                        let ffrmat = ['Attachment','Decimal','Currency','Percentage','Progress Bar','Rating','Auto String','Auto Number','Gradient Color'].indexOf(Headers[i].f_type) > -1
                            ? String(Headers[i].f_format || '').split('-')
                            : [Headers[i].f_format];

                        let lchar = String(ffrmat).slice(-1);
                        if (Headers[i].f_type === 'Date' && ['-','/'].indexOf(lchar) > -1) {
                            ffrmat = [String(ffrmat).slice(0, -1), String(ffrmat).slice(-1)];
                        }

                        let ffrmatR = ffrmat[1] || (Headers[i].f_type === 'Auto String' ? 7 : 2);

                        if (Headers[i].f_type === 'Boolean') {
                            ffrmat = String(Headers[i].f_format || '').split('/');
                            ffrmatR = ffrmat[1] || '';
                        }

                        let tmp = _.clone(Headers[i]);
                        tmp.status = forceStat || 'edit';
                        tmp.col = null;
                        tmp.last_col_corr = '';
                        tmp.trps_type = 'inheritance';
                        tmp.trps_value = null;
                        tmp.table_id = this.tableMeta.id;
                        tmp._source_type = tmp._source_type || 'Single Line Text';
                        tmp._f_format_l = ffrmat[0] || '';
                        tmp._f_format_r = ffrmatR;
                        tmp._ref_tmp_id = null;
                        result.push(tmp);
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
            theSameHeaders(tableHeaders) {
                let names = _.filter(tableHeaders, (th) => th.name);
                names = _.map(names, 'name');

                let formulas = _.filter(tableHeaders, (th) => th.formula_symbol);
                formulas = _.map(formulas, 'formula_symbol');

                let theSameNames = _.filter(names, (nm) => {
                    return _.filter(tableHeaders, (th) => th.name === nm && th.status !== 'del').length > 1;
                });

                let theSameFormulas = _.filter(formulas, (fs) => {
                    return _.filter(tableHeaders, (th) => th.formula_symbol === fs && th.status !== 'del').length > 1;
                });

                return theSameNames.length
                    ? 'There is an existing field with the name: '+_.uniq(theSameNames).join(', ')+'. Change it and Save again.'
                    : theSameFormulas.length
                        ? 'There is an existing field with formula symbol: '+_.uniq(theSameFormulas).join(', ')+'. Change it and Save again.'
                        : '';
            },

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
                this.usedNames = [];
                //Match fields with the similar names
                _.each(headersArray[hKey], (hdr, i) => {
                    //clear last selection
                    headersArray[hKey][i].col = null;

                    if (this.canViewEditCol(hdr, 'edit_fields')) {
                        //apply last columns correspondence
                        if (headersArray[hKey][i].last_col_corr) {
                            let ii = _.findIndex(fields, (name) => {
                                return name === headersArray[hKey][i].last_col_corr;
                            });
                            if (ii > -1) {
                                this.usedNames.push(fields[ii]);
                                headersArray[hKey][i].col = ii;
                                headersArray[hKey][i].last_col_corr = fields[ii];
                            }
                        }
                        //apply "fuzzy matching"
                        if (headersArray[hKey][i].col === null) {
                            let sim = 0;
                            let ii = -1;
                            _.each(fields, (name, idx) => {
                                let curSim = StringHelper.similarity(name, hdr.name);
                                if (curSim > 0.7 && curSim > sim) {
                                    sim = curSim;
                                    ii = idx;
                                }
                            });
                            if (ii > -1) {
                                this.usedNames.push(fields[ii]);
                                headersArray[hKey][i].col = ii;
                                headersArray[hKey][i].last_col_corr = fields[ii];
                            }
                        }
                    }
                });
                //Fill the rest to the empty columns
                /*_.each(headersArray[hKey], (hdr, i) => {
                    if (this.canViewEditCol(hdr, 'edit_fields') && hdr.col === null) {
                        let ii = _.findIndex(fields, (name) => !this.inArray(name, usedNames));
                        if (ii > -1) {
                            usedNames.push(fields[ii]);
                            headersArray[hKey][i].col = ii;
                        }
                    }
                });*/
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
             * @param tbHeaders
             * @param srcId
             */
            transposeFuzzyMatching(tbHeaders, srcId) {
                let srcTb = _.find(this.$root.settingsMeta.available_tables, {id: srcId});

                this.$root.usedTrps = [];
                _.each(tbHeaders, (tHdr) => {
                    let sim = 0;
                    let ii = -1;
                    _.each(srcTb._fields, (fld, idx) => {
                        let curSim = StringHelper.similarity(fld.name, tHdr.name);
                        if (curSim > 0.7 && curSim > sim) {
                            sim = curSim;
                            ii = idx;
                        }
                    });
                    if (ii > -1 && tHdr.trps_type === 'inheritance') {
                        this.$root.usedTrps.push(srcTb._fields[ii].id);
                        tHdr.trps_value = srcTb._fields[ii].id;
                    }
                });
            },
            /**
             *
             * @param tableHeaders {Object}
             * @param tkey {string}
             * @param ocrSettings {{sourceFile: string, firstHeader: boolean}}
             * @param error_to
             * @param noJump
             */
            getFieldsFromOCR(tableHeaders, tkey, ocrSettings, error_to, noJump) {
                if (ocrSettings.sourceFile) {
                    this._sendCsvRequest(null, ocrSettings.sourceFile, ocrSettings, error_to).then(({data}) => {
                        this._fillTableHeaders(tableHeaders, tkey, data.headers, data.csv_fields);
                        this.fieldsColumns = data.csv_fields;
                        if (!noJump) {
                            this.activeTab = 'fields';
                        }
                        ocrSettings.sourceFile = data.csv_file;
                    });
                }
            },
            /**
             *
             * @param tableHeaders
             * @param tkey
             * @param error_to
             * @param noJump
             */
            getFieldsFromCSV(tableHeaders, tkey, error_to, noJump) {
                if (!this.importAction) {
                    Swal('Info','Please select an option.');
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
                            if (!noJump) {
                                this.activeTab = 'fields';
                            }
                            this.xlsSheets = [];
                        } else {
                            this.xlsSheets = data.xls_sheets;
                        }
                        this.saveCsvSett();
                    });
                } else {
                    Swal('Info','No file');
                }
            },
            /**
             *
             * @param tableHeaders
             * @param tkey
             * @param error_to
             * @param noJump
             */
            getFieldsFromPaste(tableHeaders, tkey, error_to, noJump) {
                if (!this.importAction) {
                    Swal('Info','Please select an option.');
                    return;
                }
                tkey = tkey || 'def';
                if (this.paste_data) {
                    this.pasteFieldsFromBackend(error_to).then((data) => {
                        this._fillTableHeaders(tableHeaders, tkey, data.headers, data.fields);
                        this.fieldsColumns = data.fields;
                        if (!noJump) {
                            this.activeTab = 'fields';
                        }
                    });
                } else {
                    Swal('Info','No pasted data');
                }
            },
            /**
             *
             * @param tableHeaders
             * @param tkey
             * @param error_to
             * @param noJump
             */
            getFieldsFromGSheet(tableHeaders, tkey, error_to, noJump) {
                if (!this.importAction) {
                    Swal('Info','Please select an option.');
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
                        if (!noJump) {
                            this.activeTab = 'fields';
                        }
                    }).catch(errors => {
                        this.swalError(errors, error_to);
                    }).finally(() => {
                        this.$root.sm_msg_type = 0;
                    });
                } else {
                    Swal('Info','Google Sheets Link is empty!');
                }
            },
            /**
             *
             * @param tableHeaders
             * @param tkey
             * @param error_to
             * @param noJump
             */
            getFieldsFromMySQL(tableHeaders, tkey, error_to, noJump) {
                if (!this.importAction) {
                    Swal('Info','Please select an option.');
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
                        if (!noJump) {
                            this.activeTab = 'fields';
                        }
                    }).catch(errors => {
                        this.swalError(errors, error_to);
                    }).finally(() => {
                        this.$root.sm_msg_type = 0;
                    });
                } else {
                    Swal('Info','No host');
                }
            },
            /**
             *
             * @param tableHeaders
             * @param tkey
             * @param error_to
             * @param noJump
             */
            getScrapWeb(tableHeaders, tkey, error_to, noJump) {
                if (!this.importAction) {
                    Swal('Info','Please select an option.');
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
                        if (!noJump) {
                            this.activeTab = 'fields';
                        }
                    }).catch(errors => {
                        this.swalError(errors, error_to);
                    }).finally(() => {
                        this.$root.sm_msg_type = 0;
                    });
                } else {
                    Swal('Info','Url or XPath is empty!');
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
                    Swal('Info','Please select an option.');
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
                    Swal('Info','Base or Table name are empty!');
                }
            },

            /**
             *
             * @param tableHeaders
             * @param tkey
             * @param jira_item {{cloud_id: int, project_names: array}}
             */
            loadFieldsFromJira(tableHeaders, tkey, jira_item) {
                if (!this.importAction) {
                    Swal('Info','Please select an option.');
                    return;
                }
                let projects = jira_item.jql_query || jira_item.project_names;

                let key = jira_item ? jira_item.cloud_id + projects : '';
                if (jira_item && jira_item.cloud_id && projects && this.jira_cache !== key) {
                    this.jira_cache = key;
                    this.$root.sm_msg_type = 2;
                    axios.post('/ajax/jira/issue-fields', {
                        cloud_id: jira_item.cloud_id,
                        project_names: jira_item.project_names,
                        jql_query: jira_item.jql_query,
                    }).then(({data}) => {
                        this._fillTableHeaders(tableHeaders, tkey, data.headers, data.fields);
                        this.fieldsColumns = data.fields;
                        this.nameConverter = data.name_converter;
                    }).catch(errors => {
                        this.swalError(errors, 'message');
                    }).finally(() => {
                        this.$root.sm_msg_type = 0;
                    });
                }
            },

            /**
             *
             * @param tableHeaders
             * @param tkey
             * @param salesforce_item {{cloud_id: int, project_names: array}}
             */
            loadFieldsFromSalesforce(tableHeaders, tkey, salesforce_item) {
                if (!this.importAction) {
                    Swal('Info','Please select an option.');
                    return;
                }

                let key = salesforce_item ? salesforce_item.cloud_id + '_' + salesforce_item.object_id : '';
                if (key && salesforce_item.cloud_id && salesforce_item.object_id && this.salesforce_cache !== key) {
                    this.salesforce_cache = key;
                    this.$root.sm_msg_type = 2;
                    axios.post('/ajax/salesforce/object/fields', {
                        cloud_id: salesforce_item.cloud_id,
                        object_id: salesforce_item.object_id,
                    }).then(({data}) => {
                        this._fillTableHeaders(tableHeaders, tkey, data.headers, data.fields);
                        this.fieldsColumns = data.fields;
                        this.nameConverter = data.name_converter;
                    }).catch(errors => {
                        this.swalError(errors, 'message');
                    }).finally(() => {
                        this.$root.sm_msg_type = 0;
                    });
                }
            },

            /**
             *
             * @param errors
             * @param error_to
             */
            swalError(errors, error_to) {
                Swal('Info', getErrors(errors));
                if (error_to) {
                    this[error_to] = getErrors(errors)
                }
            },

            //LAST COLUMNS CORRESPONDENCE
            saveLastColsCorrespondence(tableHeaders) {
                this.tableMeta.import_last_cols_corresp = JSON.stringify(
                    _.filter(
                        _.map(tableHeaders, (el) => {
                            return { f: el.field, l: el.last_col_corr };
                        }),
                        (el) => {
                            return !!el.l;
                        }
                    )
                );
                axios.put('/ajax/table', {
                    table_id: this.tableMeta.id,
                    import_last_cols_corresp: this.tableMeta.import_last_cols_corresp,
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                });
            },
            applyLastColsCorrespondence(tableHeaders) {
                if (this.tableMeta.import_last_cols_corresp) {
                    let corrs = JSON.parse(this.tableMeta.import_last_cols_corresp);
                    _.each(corrs, (lcc) => {
                        let ii = _.findIndex(tableHeaders, {field: lcc.f});
                        if (ii > -1) {
                            tableHeaders[ii].last_col_corr = lcc.l;
                        }
                    });
                }
            },
        },
    }
</script>