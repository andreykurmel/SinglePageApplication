<script>
    /* this.$root.tableMeta: Object - should be present */
    import {ImportHelper} from "../../classes/helpers/ImportHelper";
    import {Endpoints} from "../../classes/Endpoints";

    export default {
        data: function () {
            return {
                g_sheets_settings: {
                    f_header: true
                },
                g_sheets_account: '',
                g_sheets_file: '',
                g_sheets_url: '',
                g_sheets_element: '',
                foundImportFiles: [],
                fileSheets: [],
            }
        },
        methods: {
            preloadGoogleTables(mime) {
                this.g_sheets_file = '';
                this.g_sheets_element = '';
                this.fillCloudFiles('google',{
                    cloud_id: this.g_sheets_account,
                    mime: 'application/vnd.google-apps.spreadsheet',
                });
            },
            fillCloudFiles(type, body) {
                this.foundImportFiles = [];
                Endpoints.listCloudFiles(type, body).then((data) => {
                    this.foundImportFiles = data; // [ {id:string, name:string}, ... ]
                });
            },
            loadTableSheets(account, sheet) {
                this.g_sheets_element = '';
                let google_account = account || this.g_sheets_account;
                let google_sheet = sheet || this.g_sheets_file;
                if (google_account && google_sheet) {

                    axios.post('/ajax/import/google-drive/sheets-for-table', {
                        cloud_id: google_account,
                        spreadsheet_id: google_sheet,
                    }).then(({data}) => {
                        this.fileSheets = [];
                        this.$nextTick(() => {
                            this.fileSheets = _.map(data, (sheet_name) => {
                                return ImportHelper.importTemplate({ name:sheet_name });
                            });
                        });
                    }).catch(errors => {
                        Swal('Info', getErrors(errors));
                    });
                }
            },
            getXlsSheets(filepath) {
                axios.post('/ajax/import/xls-sheets', {
                    file_path: filepath,
                }).then(({data}) => {
                    this.fileSheets = [];
                    this.$nextTick(() => {
                        this.fileSheets = _.map(data, (sheet_name) => {
                            return ImportHelper.importTemplate({ name:sheet_name });
                        });
                    });
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                });
            },
            fileAsSheet(file, show) {
                this.fileSheets = [];
                this.$nextTick(() => {
                    this.fileSheets = [
                        ImportHelper.importTemplate({name: file, show: show, new_name: show})
                    ];
                });
            },
            fillBySaved(templatesArray) {
                this.fileSheets = [];
                this.$nextTick(() => {
                    _.each(templatesArray, (result) => {
                        this.fileSheets.push(ImportHelper.importTemplate(result));
                    });
                });
            },
            fillForAirtable(user_api_key, table_names) {
                this.fileSheets = [];
                this.$nextTick(() => {
                    _.each(table_names, (tb_name) => {
                        this.fileSheets.push(ImportHelper.importTemplate({
                            name: tb_name,
                            filetype: 'airtable_import',
                            airtable_data: { user_key_id: user_api_key, table_name: tb_name, fromtype: 'folder/table' }
                        }));
                    });
                });
            },
            downloadCloudFile(url, body) {
                return axios.post(url, body).then(({data}) => {
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                });
            },
        }
    }
</script>