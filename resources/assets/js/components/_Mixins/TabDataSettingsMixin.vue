
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
                }
            },
            saveSource() {
                this.tableMeta.source = this.typeKey;
                axios.put('/ajax/table', {
                    table_id: this.tableMeta.id,
                    source: this.tableMeta.source,
                }).catch(errors => {
                    Swal('', getErrors(errors));
                });
            },

            //CSV
            saveCsvSett() {
                let obj = {
                    csv_file: this.csv_link,
                    csvSettings: this.csvSettings,
                };
                this.tableMeta.import_csv_save = JSON.stringify(obj);
                axios.put('/ajax/table', {
                    table_id: this.tableMeta.id,
                    import_csv_save: this.tableMeta.import_csv_save,
                }).catch(errors => {
                    Swal('', getErrors(errors));
                });
            },
            applyCsvSett() {
                if (this.tableMeta.import_csv_save) {
                    let obj = JSON.parse(this.tableMeta.import_csv_save);
                    this.csv_link = obj.csv_file;
                    this.csvSettings = obj.csvSettings;
                    this.getFieldsFromCSV(this.tableHeaders);
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
                    Swal('', getErrors(errors));
                });
            },
            applyMysqlSett() {
                if (this.tableMeta.import_mysql_save) {
                    let obj = JSON.parse(this.tableMeta.import_mysql_save);
                    this.selected_connection = obj.selected_connection;
                    this.mysqlSettings = obj.mysqlSettings;
                    this.getDBS();
                    this.getTables();
                    this.getFieldsFromMySQL(this.tableHeaders);
                }
            },

            //MYSQL + REMOTE
            savePasteSett(data) {
                this.tableMeta.import_paste_save = data;
                axios.put('/ajax/table', {
                    table_id: this.tableMeta.id,
                    import_paste_save: this.tableMeta.import_paste_save,
                }).catch(errors => {
                    Swal('', getErrors(errors));
                });
            },
            applyPasteSett() {
                if (this.tableMeta.import_paste_save) {
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
                    Swal('', getErrors(errors));
                });
            },
            applyOcrSett() {
                if (this.tableMeta.import_table_ocr_save) {
                    let obj = JSON.parse(this.tableMeta.import_table_ocr_save);
                    this.ocr_preset = obj.ocr_preset;
                    this.ocr_item = obj.ocr_item;
                    this.getFieldsFromOCR(this.tableHeaders, 'def', this.ocr_item);
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
                    Swal('', getErrors(errors));
                });
            },
            applyWebScrapSett() {
                if (this.tableMeta.import_web_scrap_save) {
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
                    page: this.g_sheets_element,
                };
                this.tableMeta.import_gsheet_save = JSON.stringify(obj);
                axios.put('/ajax/table', {
                    table_id: this.tableMeta.id,
                    import_gsheet_save: this.tableMeta.import_gsheet_save,
                }).catch(errors => {
                    Swal('', getErrors(errors));
                });
            },
            applyGsheetSett() {
                if (this.tableMeta.import_gsheet_save) {
                    let obj = JSON.parse(this.tableMeta.import_gsheet_save);
                    this.g_sheets_settings.f_header = !!obj.f_header;
                    this.g_sheets_account = obj.cloud_id;
                    this.g_sheets_file = obj.sheet_id;
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
                    Swal('', getErrors(errors));
                });
            },
            applyAirtableSett() {
                if (this.tableMeta.import_airtable_save) {
                    let obj = JSON.parse(this.tableMeta.import_airtable_save);
                    this.airtable_item.user_key_id = obj.user_key_id;
                    this.airtable_item.table_name = obj.table_name;
                }
            },
        },
    }
</script>