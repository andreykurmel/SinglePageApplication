<template>
    <div class="container-fluid full-height" style="background-color: #FFF;">
        <div class="row full-height">
            <div class="col-xs-12 full-height" style="padding: 10px;" v-if="!show_preview">
                <button class="btn btn-sm btn-success pull-right"
                        :disabled="!fileSheets || !fileSheets.length"
                        :style="$root.themeButtonStyle"
                        @click="goToPreview"
                >Preview</button>
                <table class="g-sheet-elems">
                    <tr>
                        <td>
                            <label>Select a data source:&nbsp;</label>
                        </td>
                        <td>
                            <select-block
                                    :options="[
                                        { val:'google_drive', show:'Google Drive', img: '/assets/img/google-drive.png' },
                                        { val:'dropbox', show:'Dropbox' },
                                        { val:'one_drive', show:'OneDrive' },
                                        { val:'local_upload', show:'Local Upload' },
                                        { val:'table_ocr', show:'OCR Import' },
                                        { val:'airtable_import', show:'Airtable Base' },
                                    ]"
                                    :sel_image="imgSource"
                                    :sel_value="importobj.source"
                                    class="form-control form-group"
                                    @option-select="sourceSelected"
                            ></select-block>
                        </td>
                    </tr>

                    <!--AIRTABLE IMPORT-->
                    <template v-if="importobj.source === 'airtable_import'">
                        <tr>
                            <td colspan="2">
                                <airtable-import-block
                                    :import_action="'new'"
                                    :some_presets="airtable_preset"
                                    @props-changed="saveSettAirtable"
                                    @multiple-names-parsed="onAirtableTables"
                                ></airtable-import-block>
                            </td>
                        </tr>
                        <tr v-if="importobj.filename_show">
                            <td colspan="2">
                                <label>File: {{ importobj.filename_show }}</label>
                            </td>
                        </tr>
                    </template>

                    <!--OCR IMPORT-->
                    <template v-if="importobj.source === 'table_ocr'">
                        <tr>
                            <td colspan="2">
                                <ocr-import-block
                                    :import_action="'new'"
                                    :some_presets="ocr_preset"
                                    @props-changed="saveSettOcr"
                                    @ocr-image-parsed="onOcrRecognized"
                                ></ocr-import-block>
                            </td>
                        </tr>
                        <tr v-if="importobj.filename_show">
                            <td colspan="2">
                                <label>File: {{ importobj.filename_show }}</label>
                            </td>
                        </tr>
                    </template>

                    <!--LOCAL IMPORT-->
                    <template v-if="importobj.source === 'local_upload'">
                        <tr>
                            <td colspan="2">
                                <file-uploader-block
                                        :clear_before="true"
                                        :table_id="'temp'"
                                        :field_id="'folder'"
                                        :row_id="file_uuid"
                                        class="full-width form-group"
                                        @uploaded-file="onLocalUpload"
                                ></file-uploader-block>
                            </td>
                        </tr>
                        <tr v-if="importobj.filename_show">
                            <td colspan="2">
                                <label>File: {{ importobj.filename_show }}</label>
                            </td>
                        </tr>
                    </template>

                    <!--CLOUD IMPORT-->
                    <template v-else-if="['google_drive','dropbox','one_drive'].indexOf(importobj.source) > -1">
                        <tr>
                            <td>
                                <label>Select a connected <a href="" @click.prevent="showUsrSetting">
                                    <span v-if="importobj.source === 'google_drive'">Google Account</span>
                                    <span v-if="importobj.source === 'dropbox'">Dropbox Account</span>
                                    <span v-if="importobj.source === 'one_drive'">OneDrive Account</span>
                                </a>:&nbsp;</label>
                            </td>
                            <td>
                                <select-block
                                        :options="userCloudOptions()"
                                        :sel_value="importobj.conn_account"
                                        class="form-control form-group"
                                        @option-select="connectedAccount"
                                ></select-block>
                            </td>
                        </tr>

                        <tr>
                            <td>
                                <label>Select a file type:&nbsp;</label>
                            </td>
                            <td>
                                <select-block
                                        :options="filetypeOptions()"
                                        :sel_value="importobj.filetype"
                                        :is_disabled="!importobj.conn_account"
                                        class="form-control form-group"
                                        @option-select="filetypeChanged"
                                ></select-block>
                            </td>
                        </tr>

                        <tr>
                            <td>
                                <label>Select a file:&nbsp;</label>
                            </td>
                            <td>
                                <select-block
                                        :options="filesOptions()"
                                        :sel_value="importobj.filename_show"
                                        :sel_image="selImg"
                                        :is_disabled="!importobj.filetype"
                                        class="form-control form-group"
                                        @option-select="fileimportSelected"
                                ></select-block>
                            </td>
                        </tr>
                    </template>

                    <!--SELECT WHAT TO IMPORT-->
                    <tr v-if="fileSheets && fileSheets.length">
                        <td style="vertical-align: top; padding-top: 5px;">
                            <label>Check tables to import:</label>
                        </td>
                        <td>
                            <table>
                                <tr v-for="(sheet,idx) in fileSheets">
                                    <td>
                                        <label class="sheet-label flex">
                                            <span>&nbsp;&nbsp;</span>
                                            <span class="indeterm_check__wrap">
                                                <span class="indeterm_check" @click="sheet.for_import = !sheet.for_import">
                                                    <i v-if="sheet.for_import" class="glyphicon glyphicon-ok group__icon"></i>
                                                </span>
                                            </span>
                                            <span>&nbsp;{{ sheet.show || sheet.name }}&nbsp;</span>
                                        </label>
                                    </td>
                                    <td v-if="importobj.source !== 'airtable_import'">
                                        <label class="flex">
                                            <span class="indeterm_check__wrap">
                                                <span class="indeterm_check" @click="sheet.f_header = !sheet.f_header">
                                                    <i v-if="sheet.f_header" class="glyphicon glyphicon-ok group__icon"></i>
                                                </span>
                                            </span>
                                            <span>&nbsp;First row as headers</span>
                                        </label>
                                    </td>
                                    <td>
                                        <input class="form-control input-sm import_input" placeholder="Table name" v-model="sheet.new_name"/>

                                        <input v-if="importobj.filetype === 'xml'"
                                               class="form-control input-sm import_input"
                                               placeholder="XPath"
                                               v-model="importobj.xpath"/>
                                        <select v-if="importobj.filetype === 'xml'"
                                                class="form-control input-sm import_input"
                                                v-model="importobj.xml_nested">
                                            <option>0</option>
                                            <option>1</option>
                                            <option>2</option>
                                            <option>3</option>
                                        </select>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </div>

            <div class="col-xs-12 full-height" style="padding: 10px;" v-else="">
                <div class="full-height import_views">
                    <button class="btn btn-sm btn-success pull-right" :style="$root.themeButtonStyle" @click="sendModifyRequest()">Import</button>
                    <button class="btn btn-sm btn-default pull-right" @click="show_preview = false">Back</button>
                    <div class="menu-header">
                        <button v-for="(sheet,idx) in fileSheets"
                                v-if="sheet.for_import"
                                class="btn btn-default btn-sm left-btn"
                                :class="{active : sheet_tab === 'sheet_'+idx}"
                                @click="sheet_tab = 'sheet_'+idx"
                        >{{ sheet.new_name || sheet.name }}&nbsp;</button>
                    </div>
                    <div class="menu-body">
                        <div class="full-height"
                             v-for="(sheet,idx) in fileSheets"
                             v-if="sheet.for_import"
                             v-show="sheet_tab === 'sheet_'+idx"
                        >
                            <folder-import-prepare
                                    :table-meta="emptyMeta"
                                    :table-headers="tableHeaders"
                                    :part-key="'sheet_'+idx"
                                    :import_settings="importobj"
                                    :sheet_settings="sheet"
                            ></folder-import-prepare>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!--Popup with transfer data progress bar-->
        <div v-if="import_progress_jobs.length" class="import-progressbar-wrapper">
            <div class="import-progressbar">
                <h1>Importing Data To Table</h1>
                <div class="progressbar-wrapper">
                    <div ref="transfer_progressbar"></div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import GoogleSheetMixin from './../../../_Mixins/GoogleSheetMixin.vue';

    import FolderImportPrepare from "./FolderImportPrepare";
    import SelectBlock from "../../../CommonBlocks/SelectBlock";

    import {eventBus} from "../../../../app";
    import FileUploaderBlock from "../../../CommonBlocks/FileUploaderBlock";
    import OcrImportBlock from "../../../CommonBlocks/OcrImportBlock";
    import AirtableImportBlock from "../../../CommonBlocks/AirtableImportBlock";

    export default {
        name: "FolderImport",
        components: {
            AirtableImportBlock,
            OcrImportBlock,
            FileUploaderBlock,
            SelectBlock,
            FolderImportPrepare,
        },
        mixins: [
            GoogleSheetMixin,
        ],
        data: function () {
            return {
                show_preview: false,
                file_uuid: uuidv4(),

                importobj: {
                    source: '',
                    conn_account: '',
                    filetype: '',
                    filename: '',
                    filename_show: '',
                    xpath: '',
                    xml_nested: 0,
                },
                airtable_preset: null,
                ocr_preset: null,

                sheet_tab: 'sheet_0',
                tableHeaders: null,

                emptyMeta: {
                    id: null,
                    _is_owner: true,
                    _fields: [],
                    _table_references: [],
                },
                import_progress_jobs: [],
            }
        },
        props: {
            folderMeta: Object,
        },
        watch: {
            fileSheets(val) {
                let all_headers = {};
                _.each(val, (sheet,i) => {
                    all_headers['sheet_'+i] = [];
                });
                this.tableHeaders = all_headers;
            },
        },
        computed: {
            selectedViewName() {
                if (this.selectedView > -1) {
                    let ug_id = this.folderMeta._folder_views[this.selectedView].user_group_id;
                    let group = _.find(this.$root.user._user_groups, {id: Number(ug_id)});
                    return group ? group.name : this.folderMeta._folder_views[this.selectedView].name;
                } else {
                    return '';
                }
            },
            imgSource() {
                switch (this.importobj.source) {
                    case "google_drive": return '/assets/img/google-drive.png';
                    default: return '';
                }
            },
            selImg() {
                switch (this.importobj.filetype) {
                    case "sheet": return '/assets/img/google-sheet.png';
                    default: return '';
                }
            },
        },
        methods: {
            //select options
            userCloudOptions() {
                let cloud_key = this.importobj.source === 'google_drive' ? ['Google']
                    : this.importobj.source === 'dropbox' ? ['Dropbox']
                    : this.importobj.source === 'one_drive' ? ['OneDrive'] : [];

                let filtered = _.filter(this.$root.settingsMeta.user_clouds_data, (acc) => {
                    return cloud_key.indexOf(acc.cloud) > -1 && acc.__is_connected;
                });
                return _.map(filtered, (acc) => {
                    return {val:acc.id, show:acc.name};
                });
            },
            filetypeOptions() {
                let arr = [
                    { val:'xls', show:'Excel' },
                    { val:'csv', show:'CSV' },
                    { val:'xml', show:'XML' },
                ];
                if (this.importobj.source === 'google_drive') {
                    arr.unshift({val: 'sheet', show: 'Google Sheets'});
                }
                return arr;
            },
            filesOptions() {
                return _.map(this.foundImportFiles, (acc) => {
                    return { val:acc.id, show:acc.name, img:this.selImg };
                });
            },
            sourceSelected(opt) {
                this.folderMeta.import_source = opt.val;
                this.importobj.source = opt.val;
                this.importobj.conn_account = '';
                this.importobj.filetype = '';
                this.importobj.filename = '';
                this.importobj.filename_show = '';
                this.fileSheets = [];
                if (['table_ocr','airtable_import'].indexOf(this.importobj.source) > -1) {
                    this.importobj.filetype = this.importobj.source;
                }
                //save source
                axios.put('/ajax/folder', {
                    folder_id: this.folderMeta.id,
                    fields: { import_source: this.importobj.source }
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                });
                this.applySavedSettings();
            },

            //import preparing process
            connectedAccount(opt) {
                this.importobj.conn_account = opt.val;
                this.importobj.filetype = '';
                this.importobj.filename = '';
                this.importobj.filename_show = '';
                this.fileSheets = [];
                this.saveSettCloud();
            },
            onLocalUpload(idx, file) {
                this.importobj.filetype = file.substr(file.lastIndexOf('.') + 1).toLowerCase();
                this.importobj.filetype = this.importobj.filetype == 'xlsx' ? 'xls' : this.importobj.filetype;

                this.importobj.filename = 'folder/' + this.file_uuid;
                this.importobj.filename_show = file;

                this.saveSettCloud();
                this.loadAvailTables();
            },
            filetypeChanged(opt, simulate) {
                this.importobj.filetype = opt.val;
                if (!simulate) {
                    this.importobj.filename = '';
                    this.importobj.filename_show = '';
                }
                this.fileSheets = [];
                //Google cloud
                if (this.importobj.source === 'google_drive') {
                    let mime = '';
                    switch (this.importobj.filetype) {
                        case 'sheet': mime = 'application/vnd.google-apps.spreadsheet'; break;
                        case 'xls': mime = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'; break;//application/vnd.ms-excel
                        case 'csv': mime = 'text/csv'; break;
                        case 'xml': mime = 'text/xml'; break;
                    }
                    this.fillCloudFiles('google',{
                        cloud_id: this.importobj.conn_account,
                        mime: mime || '',
                    });
                }
                //Dropbox cloud
                if (this.importobj.source === 'dropbox') {
                    let extensions = [];
                    switch (this.importobj.filetype) {
                        case 'xls': extensions = ['xls','xlsx']; break;
                        case 'csv': extensions = ['csv']; break;
                        case 'xml': extensions = ['xml']; break;
                    }
                    this.fillCloudFiles('dropbox',{
                        cloud_id: this.importobj.conn_account,
                        extensions: extensions,
                    });
                }
                //OneDrive cloud
                if (this.importobj.source === 'one_drive') {
                    let extns = [];
                    switch (this.importobj.filetype) {
                        case 'xls': extns = 'xlsx'; break;
                        case 'csv': extns = 'csv'; break;
                        case 'xml': extns = 'xml'; break;
                    }
                    this.fillCloudFiles('one_drive',{
                        cloud_id: this.importobj.conn_account,
                        extension: extns,
                    });
                }

                if (!simulate) {
                    this.saveSettCloud();
                }
            },
            fileimportSelected(opt) {
                //set filename
                this.importobj.filename = opt.val;
                this.importobj.filename_show = opt.val;
                let promise = new Promise((resolve) => { resolve({}); });
                //Store Google file
                if (this.importobj.source === 'google_drive' && ['xls','csv','xml'].indexOf(this.importobj.filetype) > -1) {
                    promise = this.downloadCloudFile('/ajax/import/google-drive/store-file', {
                        cloud_id: this.importobj.conn_account,
                        file_id: opt.val,
                        new_path: 'google/' + this.file_uuid,
                    });
                    this.importobj.filename = 'google/' + this.file_uuid;
                }
                //Store Dropbox file
                if (this.importobj.source === 'dropbox' && ['xls','csv','xml'].indexOf(this.importobj.filetype) > -1) {
                    promise = this.downloadCloudFile('/ajax/import/dropbox/store-file', {
                        cloud_id: this.importobj.conn_account,
                        file_id: opt.val,
                        new_path: 'dropbox/' + this.file_uuid,
                    });
                    this.importobj.filename = 'dropbox/' + this.file_uuid;
                }
                //Store OneDrive file
                if (this.importobj.source === 'one_drive' && ['xls','csv','xml'].indexOf(this.importobj.filetype) > -1) {
                    promise = this.downloadCloudFile('/ajax/import/one-drive/store-file', {
                        cloud_id: this.importobj.conn_account,
                        file_id: opt.val,
                        new_path: 'one_drive/' + this.file_uuid,
                    });
                    this.importobj.filename = 'one_drive/' + this.file_uuid;
                }

                promise.then((data) => {
                    this.saveSettCloud();
                    this.loadAvailTables();
                });
            },

            //load settings for tables available to import
            loadAvailTables() {
                this.fileSheets = [];
                if (this.importobj.filename) {
                    //load sheets from file
                    if (this.importobj.filetype === 'sheet') {
                        this.loadTableSheets(this.importobj.conn_account, opt.val);
                    } else if (this.importobj.filetype === 'xls') {
                        this.getXlsSheets(this.importobj.filename);
                    } else {
                        this.fileAsSheet(this.importobj.filename, this.importobj.filename_show);
                    }
                }
            },
            onOcrRecognized(templatesArray) {
                this.fillBySaved(templatesArray);
            },
            onAirtableTables(user_api_key, table_names) {
                this.fillForAirtable(user_api_key, table_names);
            },

            //preview and import
            goToPreview() {
                this.show_preview = true;
                this.sheet_tab = '';
                _.each(this.fileSheets, (sheet,i) => {
                    this.sheet_tab = !this.sheet_tab && sheet.for_import ? 'sheet_'+i : this.sheet_tab;
                });
            },
            sendModifyRequest() {
                let requests = [];
                this.import_progress_jobs = [];
                _.each(this.fileSheets, (sheet,i) => {
                    if (!sheet.for_import) {
                        return;
                    }
                    let msg = this.theSameHeaders(this.tableHeaders['sheet_'+i]);
                    if (msg) {
                        Swal(msg);
                        return;
                    }

                    let itype = '';
                    switch (this.importobj.filetype) {
                        case "sheet": itype = 'g_sheets'; break;
                        case "xml": itype = 'web_scrap'; break;
                        case "csv": itype = 'csv'; break;
                        case "xls": itype = 'csv'; break;
                        case "table_ocr": itype = 'table_ocr'; break;
                        case "airtable_import": itype = 'airtable_import'; break;
                    }
                    let promise = axios.post('/ajax/import/modify-table', {
                        new_table: {
                            name: sheet.new_name || sheet.name,
                            folder_id: this.folderMeta.id,
                        },
                        columns: this.tableHeaders['sheet_'+i],
                        present_cols_idx: 0,
                        import_type: itype,
                        import_action: 'Create',
                        csv_settings: {
                            extension: this.importobj.filetype,
                            xls_sheet: sheet.name,
                            filename: this.importobj.filename,
                            firstHeader: sheet.f_header,
                        },
                        g_sheets: {
                            settings: { f_header: sheet.f_header },
                            cloud_id: this.importobj.conn_account,
                            sheet_id: this.importobj.filename,
                            page: sheet.name,
                        },
                        html_xml: {
                            web_action: 'xml',
                            web_xpath: this.importobj.xpath,
                            web_xml_file: this.importobj.filename,
                            web_xml_nested: this.importobj.xml_nested,
                            web_scrap_xpath_query: true,
                        },
                        ocr_data: {
                            first_header: sheet.f_header,
                            csv_source_file: sheet.source_file,
                        },
                        airtable_data: sheet.airtable_data,
                    }).then(({ data }) => {
                        if (data.job_id) {
                            this.import_progress_jobs.push(data.job_id);
                        }
                    }).catch(errors => {
                        Swal('Info', getErrors(errors));
                    });
                    requests.push(promise);
                });

                Promise.all(requests).then(() => {
                    //Swal('Info','Done!');
                });
            },

            //Settings saving
            applySavedSettings() {
                this.importobj.source = this.folderMeta.import_source || 'google_drive';
                switch (this.importobj.source) {
                    case "airtable_import": this.applySettAirtable(); break;
                    case "table_ocr": this.applySettOcr(); break;
                    case "local_upload": this.applySettCloud('local_upload'); break;
                    case "one_drive": this.applySettCloud('one_drive'); break;
                    case "dropbox": this.applySettCloud('dropbox'); break;
                    case "google_drive": this.applySettCloud('google_drive'); break;
                }
            },
            saveSettAirtable(datas) {
                let json = {
                    base_id: datas.user_api_obj ? datas.user_api_obj.id : null,
                    master_table: datas.master_table_name,
                    master_field: datas.master_field,
                    table_names: datas.table_names_string,
                };
                axios.put('/ajax/folder', {
                    folder_id: this.folderMeta.id,
                    fields: { importfolder_airtable_save: JSON.stringify(json) }
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                });
            },
            applySettAirtable() {
                if (this.folderMeta.importfolder_airtable_save) {
                    this.importobj.filetype = 'airtable_import';
                    this.airtable_preset = JSON.parse(this.folderMeta.importfolder_airtable_save);
                }
            },
            saveSettOcr(datas) {
                axios.put('/ajax/folder', {
                    folder_id: this.folderMeta.id,
                    fields: { importfolder_ocr_save: JSON.stringify(datas) }
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                });
            },
            applySettOcr() {
                if (this.folderMeta.importfolder_ocr_save) {
                    this.importobj.filetype = 'table_ocr';
                    this.ocr_preset = JSON.parse(this.folderMeta.importfolder_ocr_save);
                }
            },
            saveSettCloud() {
                let json = null;
                switch (this.importobj.source) {
                    case 'google_drive': json = { importfolder_google_save: JSON.stringify(this.importobj) };
                        break;
                    case 'dropbox': json = { importfolder_dropbox_save: JSON.stringify(this.importobj) };
                        break;
                    case 'one_drive': json = { importfolder_onedrive_save: JSON.stringify(this.importobj) };
                        break;
                    case 'local_upload': json = { importfolder_local_save: JSON.stringify(this.importobj) };
                        break;
                }
                axios.put('/ajax/folder', {
                    folder_id: this.folderMeta.id,
                    fields: json
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                });
            },
            applySettCloud(cloud) {
                let string = '';
                switch (this.importobj.source) {
                    case 'google_drive': string = this.folderMeta.importfolder_google_save;
                        break;
                    case 'dropbox': string = this.folderMeta.importfolder_dropbox_save;
                        break;
                    case 'one_drive': string = this.folderMeta.importfolder_onedrive_save;
                        break;
                    case 'local_upload': string = this.folderMeta.importfolder_local_save;
                        break;
                }
                if (string) {
                    this.importobj = JSON.parse(string);
                    this.filetypeChanged({val: this.importobj.filetype}, true);
                    this.loadAvailTables();
                }
            },

            //additionals
            showUsrSetting() {
                eventBus.$emit('open-resource-popup', 'connections', 0, 'cloud');
            },
        },
        mounted() {
            this.applySavedSettings();

            setInterval(() => {
                if (this.import_progress_jobs.length) {
                    axios.get('/ajax/import/status', {
                        params: {
                            import_jobs: this.import_progress_jobs
                        }
                    }).then(({ data }) => {
                        let complete = 0;
                        _.each(data, (el) => {
                            complete += Number(el.complete);
                        });
                        complete /= data.length;
                        $(this.$refs.transfer_progressbar).css('width', complete+'%');

                        let done = true;
                        _.each(data, (el) => {
                            done = done && el.status === 'done';
                        });
                        if (done) {
                            window.location.reload();
                            this.import_progress_jobs = [];
                        }
                    });
                }
            }, 1000);
        }
    }
</script>

<style lang="scss" scoped>
    @import "./../Table/TabData";

    .g-sheet-elems {
        width: 500px;
        white-space: nowrap;
        background-color: transparent !important;

        label {
            margin-bottom: 15px;
        }
        .form-control {
            width: 250px;
            display: inline-block;
        }
        .import_input {
            margin-bottom: 15px;
            margin-left: 10px;
            width: 150px;
        }
    }

    .import_views {

        .pull-right {
            margin-right: 10px;
            top: -7px;
            position: relative;
        }

        .menu-header {
            position: relative;
            margin-left: 10px;
            top: 3px;
            width: 50%;

            .left-btn {
                position: relative;
                top: -5px;
                margin-right: 5px;
            }

            button {
                background-color: #CCC;
                outline: none;
                &.active {
                    background-color: #FFF;
                }
                &:not(.active):hover {
                    color: black;
                }
            }
        }

        .menu-body {
            position: absolute;
            top: 35px;
            right: 5px;
            left: 5px;
            bottom: 5px;
            border: 1px solid #CCC;
            border-radius: 5px;
            background-color: #FFF;
        }
    }

    .sheet-label {
        max-width: 250px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
</style>