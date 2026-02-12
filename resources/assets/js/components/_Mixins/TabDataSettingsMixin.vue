
<script>
    export default {
        data: function () {
            return {};
        },
        methods: {
            //Save Settings
            applySaves() {
                this.saveSource();
                switch (this.typeKey) {
                    case 'csv': this.applyCsvSett();
                        break;
                    case 'mysql': this.applyMysqlSett();
                        break;
                    case 'remote': this.applyMysqlSett();
                        break;
                    case 'paste': this.applyPasteSett();
                        break;
                    case 'table_ocr': this.applyOcrSett();
                        break;
                    case 'web_scrap': this.applyWebScrapSett();
                        break;
                    case 'g_sheets': this.applyGsheetSett();
                        break;
                    case 'airtable_import': this.applyAirtableSett();
                        break;
                    case 'transpose_import': this.applyTransposeSett();
                        break;
                    case 'jira_import': this.applyJiraSett();
                        break;
                    case 'salesforce_import': this.applySalesforceSett();
                        break;
                }
            },
            saveSource() {
                this.tableMeta.source = this.typeKey;
                axios.put('/ajax/table', {
                    table_id: this.tableMeta.id,
                    source: this.tableMeta.source,
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                });
            },

            //CSV
            saveCsvSett() {
                let obj = {
                    csvSettings: this.csvSettings,
                };
                this.tableMeta.import_csv_save = JSON.stringify(obj);
                axios.put('/ajax/table', {
                    table_id: this.tableMeta.id,
                    import_csv_save: this.tableMeta.import_csv_save,
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                });
            },
            applyCsvSett() {
                if (this.tableMeta.import_csv_save) {
                    this.importAction = 'append';
                    let obj = JSON.parse(this.tableMeta.import_csv_save);
                    if (!obj.csvSettings.headerRowNum) {
                        obj.csvSettings.headerRowNum = 1;
                    }
                    this.csvSettings = obj.csvSettings;
                    //this.getFieldsFromCSV(this.tableHeaders, null, null, 1);
                }
            },

            //MYSQL + REMOTE
            saveMysqlSett() {
                let obj = {
                    selected_connection: this.selected_connection,
                    mysqlSettings: this.mysqlSettings,
                };
                this.tableMeta.import_mysql_save = JSON.stringify(obj);
                axios.put('/ajax/table', {
                    table_id: this.tableMeta.id,
                    import_mysql_save: this.tableMeta.import_mysql_save,
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                });
            },
            applyMysqlSett() {
                if (this.tableMeta.import_mysql_save) {
                    this.importAction = 'append';
                    let obj = JSON.parse(this.tableMeta.import_mysql_save);
                    this.selected_connection = obj.selected_connection;
                    this.mysqlSettings = obj.mysqlSettings;
                    this.getDBS();
                    this.getTables();
                    this.getFieldsFromMySQL(this.tableHeaders, null, null, 1);
                }
            },

            //MYSQL + REMOTE
            savePasteSett(data) {
                this.tableMeta.import_paste_save = data;
                axios.put('/ajax/table', {
                    table_id: this.tableMeta.id,
                    import_paste_save: this.tableMeta.import_paste_save,
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                });
            },
            applyPasteSett() {
                if (this.tableMeta.import_paste_save) {
                    this.importAction = 'append';
                    this.paste_data = this.tableMeta.import_paste_save;
                }
            },

            //TABLE OCR
            saveOcrSett() {
                let obj = {
                    ocr_preset: this.ocr_preset,
                    ocr_item: this.ocr_item,
                };
                this.tableMeta.import_table_ocr_save = JSON.stringify(obj);
                axios.put('/ajax/table', {
                    table_id: this.tableMeta.id,
                    import_table_ocr_save: this.tableMeta.import_table_ocr_save,
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                });
            },
            applyOcrSett() {
                if (this.tableMeta.import_table_ocr_save) {
                    this.importAction = 'append';
                    let obj = JSON.parse(this.tableMeta.import_table_ocr_save);
                    this.ocr_preset = obj.ocr_preset;
                    this.ocr_item = obj.ocr_item;
                    this.getFieldsFromOCR(this.tableHeaders, 'def', this.ocr_item, null, 1);
                }
            },

            //WEB SCRAP
            saveWebScrapSett() {
                let obj = {
                    type: this.webAction,
                    url: this.webAction === 'html' ? this.web_html_url : this.web_xml_url,
                    part: this.webAction === 'html' ? this.web_html_part : this.web_xml_xpath,
                    frst_headers: this.web_scrap_headers,
                    xpath_query: this.web_scrap_xpath_query,
                    xml_source: this.web_xml_source,
                    xml_nested: this.web_xml_nested,
                };
                this.tableMeta.import_web_scrap_save = JSON.stringify(obj);
                axios.put('/ajax/table', {
                    table_id: this.tableMeta.id,
                    import_web_scrap_save: this.tableMeta.import_web_scrap_save,
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                });
            },
            applyWebScrapSett() {
                if (this.tableMeta.import_web_scrap_save) {
                    this.importAction = 'append';
                    let obj = JSON.parse(this.tableMeta.import_web_scrap_save);
                    this.webAction = obj.type;
                    this.web_scrap_headers = !!obj.frst_headers;
                    this.web_scrap_xpath_query = !!obj.xpath_query;
                    this.web_xml_source = obj.xml_source || 'url';
                    this.web_xml_nested = obj.xml_nested || 0;
                    if (this.webAction === 'html') {
                        this.web_html_url = obj.url;
                        this.loadPageElems(obj.part);
                    } else {
                        this.web_xml_url = obj.url;
                        this.web_xml_xpath = obj.part;
                    }
                }
            },

            //GSHEET
            saveGsheetSett() {
                let obj = {
                    f_header: this.g_sheets_settings.f_header,
                    cloud_id: this.g_sheets_account,
                    sheet_id: this.g_sheets_file,
                    sheet_url: this.g_sheets_url,
                    page: this.g_sheets_element,
                };
                this.tableMeta.import_gsheet_save = JSON.stringify(obj);
                axios.put('/ajax/table', {
                    table_id: this.tableMeta.id,
                    import_gsheet_save: this.tableMeta.import_gsheet_save,
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                });
            },
            applyGsheetSett() {
                if (this.tableMeta.import_gsheet_save) {
                    this.importAction = 'append';
                    let obj = JSON.parse(this.tableMeta.import_gsheet_save);
                    this.g_sheets_settings.f_header = !!obj.f_header;
                    this.g_sheets_account = obj.cloud_id;
                    this.g_sheets_file = obj.sheet_id;
                    this.g_sheets_url = obj.sheet_url;
                    this.g_sheets_element = obj.page;
                    this.preloadGoogleTables();
                    this.g_sheets_file = obj.sheet_id;
                    this.loadTableSheets();
                    this.g_sheets_element = obj.page;
                }
            },

            //AIRTABLE
            saveAirtableSett() {
                this.tableMeta.import_airtable_save = JSON.stringify(this.airtable_item);
                axios.put('/ajax/table', {
                    table_id: this.tableMeta.id,
                    import_airtable_save: this.tableMeta.import_airtable_save,
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                });
            },
            applyAirtableSett() {
                if (this.tableMeta.import_airtable_save) {
                    this.importAction = 'append';
                    let obj = JSON.parse(this.tableMeta.import_airtable_save);
                    this.airtable_item.user_key_id = obj.user_key_id;
                    this.airtable_item.table_name = obj.table_name;
                }
            },

            //TRANSPOSE
            saveTransposeSett() {
                this.tableMeta.import_transpose_save = JSON.stringify({
                    dir: this.transpose_item.direction,
                    src_id: this.transpose_item.source_tb_id,
                    rg_id: this.transpose_item.row_group_id,
                    skipempty: this.transpose_item.skip_empty,
                    grfid: this.transpose_item.grouper_field_id,
                    rvfid: this.transpose_item.reverse_val_field_id,
                    fields: _.filter(
                        _.map(this.tableHeaders.def, (el) => {
                            return { f: el.field, tt: el.trps_type, tv: el.trps_value };
                        }),
                        (el) => {
                            return !!el.f;
                        }
                    ),
                });
                axios.put('/ajax/table', {
                    table_id: this.tableMeta.id,
                    import_transpose_save: this.tableMeta.import_transpose_save,
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                });
            },
            applyTransposeSett() {
                if (this.tableMeta.import_transpose_save) {
                    this.importAction = 'append';
                    let obj = JSON.parse(this.tableMeta.import_transpose_save);
                    this.transpose_item.direction = obj.dir;
                    this.transpose_item.source_tb_id = obj.src_id;
                    this.transpose_item.row_group_id = obj.rg_id;
                    this.transpose_item.skip_empty = obj.skipempty;
                    this.transpose_item.grouper_field_id = obj.grfid;
                    this.transpose_item.reverse_val_field_id = obj.rvfid;
                    _.each(obj.fields, (el) => {
                        let ii = _.findIndex(this.tableHeaders.def, {field: el.f});
                        if (ii > -1) {
                            this.tableHeaders.def[ii].trps_type = el.tt;
                            this.tableHeaders.def[ii].trps_value = el.tv;
                            this.$root.usedTrps = this.$root.usedTrps.concat(el.tv);
                        }
                    });
                }
            },

            //JIRA
            saveJiraSett() {
                this.tableMeta.import_jira_save = JSON.stringify({
                    ac: this.jira_item.action,
                    sd: this.jira_item.sync_direction,
                    ci: this.jira_item.cloud_id,
                    pn: this.jira_item.project_names,
                    jql: this.jira_item.jql_query,
                    rnf: this.jira_item.remove_not_found,
                    anr: this.jira_item.add_new_records,
                    ucr: this.jira_item.update_changed,
                });
                axios.put('/ajax/table', {
                    table_id: this.tableMeta.id,
                    import_jira_save: this.tableMeta.import_jira_save,
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                });
            },
            applyJiraSett() {
                if (this.tableMeta.import_jira_save) {
                    this.importAction = 'append';
                    let obj = JSON.parse(this.tableMeta.import_jira_save);
                    this.jira_item.action = obj.ac || 'import';
                    this.jira_item.sync_direction = obj.sd || 'to_tablda';
                    this.jira_item.cloud_id = obj.ci;
                    this.jira_item.project_names = typeof obj.pn === 'object' ? obj.pn : [];
                    this.jira_item.jql_query = obj.jql || '';
                    this.jira_item.remove_not_found = obj.rnf || 0;
                    this.jira_item.add_new_records = obj.anr || 1;
                    this.jira_item.update_changed = obj.ucr || 1;
                    this.$root.jiraReloadProjects();
                    this.loadFieldsFromJira(this.tableHeaders, 'def', this.jira_item);
                }
            },

            //SALESFORCE
            saveSalesforceSett() {
                this.tableMeta.import_salesforce_save = JSON.stringify({
                    ac: this.salesforce_item.action,
                    ci: this.salesforce_item.cloud_id,
                    on: this.salesforce_item.object_name,
                    oi: this.salesforce_item.object_id,
                    sd: this.salesforce_item.sync_direction,
                    rnf: this.salesforce_item.remove_not_found,
                    anr: this.salesforce_item.add_new_records,
                    ucr: this.salesforce_item.update_changed,
                });
                axios.put('/ajax/table', {
                    table_id: this.tableMeta.id,
                    import_salesforce_save: this.tableMeta.import_salesforce_save,
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                });
            },
            applySalesforceSett() {
                if (this.tableMeta.import_salesforce_save) {
                    this.importAction = 'append';
                    let obj = JSON.parse(this.tableMeta.import_salesforce_save);
                    this.salesforce_item.action = obj.ac || 'import';
                    this.salesforce_item.cloud_id = obj.ci;
                    this.salesforce_item.object_name = obj.on;
                    this.salesforce_item.object_id = obj.oi;
                    this.salesforce_item.sync_direction = obj.sd || 'to_tablda';
                    this.salesforce_item.remove_not_found = obj.rnf || 0;
                    this.salesforce_item.add_new_records = obj.anr || 1;
                    this.salesforce_item.update_changed = obj.ucr || 1;
                    this.$root.salesforceReloadObjects();
                    this.loadFieldsFromSalesforce(this.tableHeaders, 'def', this.salesforce_item);
                }
            },
        },
    }
</script>