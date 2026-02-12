<template>
    <div class="tab-data">
        <button v-if="selectedType.key !== 'reference' && activeTab === 'fields'"
                class="btn btn-success pull-right"
                :disabled="!presentSource() || !canSave(typeKey)"
                :style="$root.themeButtonStyle"
                @click="importTable()"
        >{{ selectedType.key === 'transpose_import' ? 'Transpose' : 'Save' }}</button>
        <div class="data-menu">
            <button class="btn btn-default"
                    :class="{active: activeTab === 'method'}"
                    :style="textSysStyle"
                    @click="activeTab = 'method'"
            >
                Method ({{ selectedType.name }})
            </button>
            <button class="btn btn-default"
                    :disabled="!canSave(typeKey)"
                    :class="{active: activeTab === 'fields'}"
                    :style="textSysStyle"
                    @click="activeTab = 'fields'"
            >
                Field Settings
            </button>
            <button v-if="canGetAccess()"
                    class="btn btn-default"
                    :style="textSysStyle"
                    :class="{active: activeTab === 'storage-backup', disabled: !$root.checkAvailable($root.user, 'data_storage_backup')}"
                    title="You can create schedule to backup your table with attachments to your Google drive or Dropbox"
                    @click="activeTab = 'storage-backup'"
            >
                Autobackup
            </button>

            <div class="info-icon">
                <info-sign-link
                        v-show="activeTab === 'method'"
                        :app_sett_key="'help_link_import_methods'"
                        :hgt="22"
                        :txt="'for Import/Method'"
                ></info-sign-link>
                <info-sign-link
                        v-show="activeTab === 'fields'"
                        :app_sett_key="'help_link_import_fields'"
                        :hgt="22"
                        :txt="'for Import/Fields'"
                ></info-sign-link>
                <info-sign-link
                        v-show="activeTab === 'storage-backup'"
                        :app_sett_key="'help_link_table_backups'"
                        :hgt="22"
                        :txt="'for Data/Autobackup'"
                ></info-sign-link>
            </div>
        </div>


        <!--Tab with import settings-->
        <div class="data-tab" v-show="activeTab === 'method'" style="background-color: #FFF" :style="textSysStyle">
            <div class="full-height fields-wrapper no-padding">
                <div class="container-fluid data-tab-main">
                    <div class="form-group flex flex--center-v" style="position: relative">
                        <select-block
                            :options="importTypesOptions()"
                            :sel_image="imgSource"
                            :sel_value="typeKey"
                            :is_disabled="disabledKeys"
                            class="form-control w w-method"
                            :style="textSysStyle"
                            @option-select="changeTab"
                        ></select-block>

                        <select class="form-control w w-mid"
                                :style="textSysStyle"
                                v-model="csvAction"
                                v-if="typeKey === 'csv'"
                        >
                            <option value="upload">Upload</option>
                            <option value="url">Web URL</option>
                        </select>

                        <select class="form-control w w-mid"
                                :style="textSysStyle"
                                v-model="webAction"
                                v-if="typeKey === 'web_scrap'"
                                @change="saveWebScrapSett"
                        >
                            <option value="html">Import HTML</option>
                            <option value="xml">Import XML</option>
                        </select>

                        <!--JIRA-->
                        <select class="form-control w w-mid"
                                :style="textSysStyle"
                                v-model="jira_item.action"
                                v-if="typeKey === 'jira_import'"
                                @change="saveJiraSett"
                        >
                            <option value="import">Import</option>
                            <option value="sync">Sync</option>
                        </select>
                        <select class="form-control w w-mid"
                                v-model="importAction"
                                v-if="typeKey === 'jira_import' && jira_item.action === 'import'"
                                :style="textSysStyle"
                        >
                            <option :disabled="!canGetAccess('not_only_append')" value="new">New</option>
                            <option value="append">Append</option>
                        </select>
                        <select class="form-control w w-method"
                                :style="textSysStyle"
                                v-model="jira_item.sync_direction"
                                v-if="typeKey === 'jira_import' && jira_item.action === 'sync'"
                                @change="saveJiraSett"
                        >
                            <option value="to_tablda">From Jira to {{ $root.app_name }}</option>
                            <option value="to_jira" :disabled="1">From {{ $root.app_name }} to Jira</option>
                        </select>

                        <!--SALESFORCE-->
                        <select class="form-control w w-mid"
                                :style="textSysStyle"
                                v-model="salesforce_item.action"
                                v-if="typeKey === 'salesforce_import'"
                                @change="saveSalesforceSett"
                        >
                            <option value="import">Import</option>
                            <option value="sync">Sync</option>
                        </select>
                        <select class="form-control w w-mid"
                                v-model="importAction"
                                v-if="typeKey === 'salesforce_import' && salesforce_item.action === 'import'"
                                :style="textSysStyle"
                        >
                            <option :disabled="!canGetAccess('not_only_append')" value="new">New</option>
                            <option value="append">Append</option>
                        </select>
                        <select class="form-control w w-method"
                                :style="textSysStyle"
                                v-model="salesforce_item.sync_direction"
                                v-if="typeKey === 'salesforce_import' && salesforce_item.action === 'sync'"
                                @change="saveSalesforceSett"
                        >
                            <option value="to_tablda">From Salesforce to {{ $root.app_name }}</option>
                            <option value="to_salesforce" :disabled="1">From {{ $root.app_name }} to Salesforce</option>
                        </select>

                        <select class="form-control w w-mid"
                                v-model="importAction"
                                v-if="selectedType.has_action"
                                :style="textSysStyle"
                        >
                            <option value="">Select an option</option>
                            <option :disabled="!canGetAccess('not_only_append')" value="new">New</option>
                            <option value="append">Append</option>
                        </select>

                        <button v-if="typeKey === 'web_scrap'"
                                class="btn btn-success absolute-right"
                                :disabled="(webAction === 'html' && (!web_html_url || !web_html_part))
                                    || (webAction === 'xml' && !web_xml_xpath)"
                                :style="$root.themeButtonStyle"
                                @click="getScrapWeb(tableHeaders, 'def')"
                        >Import</button>

                        <template v-if="selectedType.key === 'paste'">
                            <div class="flex flex--center-v" style="width: 44%;flex-wrap: wrap;">
                                <label style="margin: 0 30px 0 0;">
                                    <span class="indeterm_check__wrap">
                                        <span class="indeterm_check" @click="paste_settings.f_header = !paste_settings.f_header">
                                            <i v-if="paste_settings.f_header" class="glyphicon glyphicon-ok group__icon"></i>
                                        </span>
                                    </span>
                                    <span>First row as headers</span>
                                </label>
                                <label style="color: #333;margin: 0;">
                                    <span>{{ paste_lines }} (</span>
                                    <span v-if="paste_lines > paste_row_limit" style="color: #F00">{{ '>'+paste_row_limit }}</span>
                                    <span v-else="">{{ '<'+paste_row_limit }}</span>
                                    <span>) lines.</span>

                                    <span>{{ paste_chars }} (</span>
                                    <span v-if="paste_chars > paste_char_limit" style="color: #F00">{{ '>'+paste_char_limit }}</span>
                                    <span v-else="">{{ '<'+paste_char_limit }}</span>
                                    <span>) characters.</span>
                                </label>
                            </div>
                            <button class="btn btn-success absolute-right" :style="$root.themeButtonStyle" @click="getFieldsFromPaste(tableHeaders)">Import</button>
                        </template>

                        <template v-if="selectedType.key === 'g_sheets'">
                            <button class="btn btn-success absolute-right" :style="$root.themeButtonStyle" @click="getFieldsFromGSheet(tableHeaders)">Import</button>
                        </template>

                        <template v-if="selectedType.key === 'table_ocr'">
                            <label style="margin: 0;">
                                    <span class="indeterm_check__wrap">
                                        <span class="indeterm_check" @click="ocr_item.firstHeader = !ocr_item.firstHeader">
                                            <i v-if="ocr_item.firstHeader" class="glyphicon glyphicon-ok group__icon"></i>
                                        </span>
                                    </span>
                                <span>First row as headers</span>
                            </label>
                        </template>

                        <button v-if="selectedType.key === 'transpose_import'"
                                class="btn btn-success absolute-right"
                                :disabled="!transpose_item.source_tb_id"
                                :style="$root.themeButtonStyle"
                                @click="activeTab = 'fields'"
                        >Next</button>
                        <button v-if="selectedType.key === 'jira_import'"
                                class="btn btn-success absolute-right"
                                :disabled="(jira_item.action === 'import' && !jira_item.project_names.length && !jira_item.jql_query)
                                    || (jira_item.action === 'sync' && !tableMeta.import_last_jira_action)"
                                :style="$root.themeButtonStyle"
                                @click="activeTab = 'fields'"
                        >{{ jira_item.action === 'import' ? 'Next' : 'Sync' }}</button>
                        <button v-if="selectedType.key === 'salesforce_import'"
                                class="btn btn-success absolute-right"
                                :disabled="(salesforce_item.action === 'import' && !salesforce_item.object_id)
                                    || (salesforce_item.action === 'sync' && !tableMeta.import_last_salesforce_action)"
                                :style="$root.themeButtonStyle"
                                @click="activeTab = 'fields'"
                        >{{ salesforce_item.action === 'import' ? 'Next' : 'Sync' }}</button>
                    </div>
                    <!--CSV settings-->
                    <div v-if="selectedType.key === 'csv'" :style="{height: 'calc(100% - 60px)'}">
                        <div class="row">
                            <div class="col-xs-12 form-group">
                                <input v-show="csvAction === 'upload'"
                                       ref="csv_upload"
                                       type="file"
                                       accept=".csv,.xls,.xlsx,.xlsm"
                                       class="form-control w w-long"
                                       :style="textSysStyle"
                                       @change="getFieldsFromCSV()"
                                />
                                <input v-show="csvAction === 'url'"
                                       type="text"
                                       class="form-control w w-long"
                                       v-model="csv_link"
                                       placeholder="www address of file"
                                       :style="textSysStyle"
                                       @change="getFieldsFromCSV()"
                                />
                            </div>
                        </div>
                        <div class="row" v-if="xlsSheets && xlsSheets.length">
                            <div class="col-xs-12 form-group">
                                <label>Select a sheet:</label>
                                <select class="form-control w w-long" v-model="csvSettings.xls_sheet" :style="textSysStyle" @change="saveCsvSett()">
                                    <option v-for="sh in xlsSheets">{{ sh }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="row form-group">
                            <div class="col-xs-12">
                                <div class="row">
                                    <div class="col-xs-12">
                                        <label>
                                            <span>Row # </span>
                                            <input type="number"
                                                   min="1"
                                                   class="form-control w w-mid"
                                                   v-model="csvSettings.headerRowNum"
                                                   :style="textSysStyle"
                                                   style="width: 65px; margin-right: 0;"
                                                   @change="changeCsvRowNum()">
                                            <span>as field header.</span>
                                        </label>
                                        <label>Starting row:
                                            <input type="number"
                                                   min="1"
                                                   class="form-control w w-mid"
                                                   v-model="csvSettings.startRow"
                                                   :style="textSysStyle"
                                                   style="width: 65px;"
                                                   @change="changeCsvRowNum()">
                                        </label>
                                        <label>Ending row:
                                            <input type="number"
                                                   :disabled="!csvSettings.startRow"
                                                   min="1"
                                                   class="form-control w w-mid"
                                                   v-model="csvSettings.endRow"
                                                   :style="textSysStyle"
                                                   style="width: 65px;"
                                                   @change="changeCsvRowNum()">
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-xs-12">
                                        <label>
                                            <span class="indeterm_check__wrap">
                                                <span class="indeterm_check" @click="csvSettings.replace = !csvSettings.replace;saveCsvSett();">
                                                    <i v-if="csvSettings.replace" class="glyphicon glyphicon-ok group__icon"></i>
                                                </span>
                                            </span>
                                            <span>Replace accents/diacriticals.</span>
                                        </label>
                                    </div>
                                    <div class="col-xs-12">
                                        <label>
                                            <span class="indeterm_check__wrap">
                                                <span class="indeterm_check" @click="csvSettings.quote = !csvSettings.quote;saveCsvSett()">
                                                    <i v-if="csvSettings.quote" class="glyphicon glyphicon-ok group__icon"></i>
                                                </span>
                                            </span>
                                            <span>Treat all quoting characted as data.</span>
                                        </label>
                                    </div>
                                    <div class="col-xs-12">
                                        <label>
                                            <span class="indeterm_check__wrap">
                                                <span class="indeterm_check" @click="csvSettings.apostrophe = !csvSettings.apostrophe;saveCsvSett()">
                                                    <i v-if="csvSettings.apostrophe" class="glyphicon glyphicon-ok group__icon"></i>
                                                </span>
                                            </span>
                                            <span>Input CSV quoting character is apostrophe</span>
                                        </label>
                                    </div>
                                    <div class="col-xs-12">
                                        <label>
                                            <span class="indeterm_check__wrap">
                                                <span class="indeterm_check" @click="csvSettings.backslash = !csvSettings.backslash;saveCsvSett()">
                                                    <i v-if="csvSettings.backslash" class="glyphicon glyphicon-ok group__icon"></i>
                                                </span>
                                            </span>
                                            <span>CSV contains backslash escaping like \n, \t and \.</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xs-12 col-md-6 col-lg-5">
                                <button class="btn btn-info" :style="$root.themeButtonStyle" @click="getFieldsFromCSV(tableHeaders)">Import</button>
                            </div>
                        </div>
                    </div>
                    <!--MySQL settings-->
                    <div v-if="selectedType.key === 'mysql' || selectedType.key === 'remote'" :style="{height: 'calc(100% - 60px)'}">
                        <div class="row form-group">
                            <div class="col-xs-12">
                                <select class="form-control w w-msql" v-model="selected_connection" :style="textSysStyle" @change="selectedConnection()">
                                    <option></option>
                                    <option v-for="(conn, i) in $root.settingsMeta.user_connections_data" :value="i">{{ conn.name }}</option>
                                </select>
                                <button class="btn btn-success w fix-btn-h" :style="$root.themeButtonStyle" @click="getDBS(true)">Connect</button>
                                <select class="form-control w w-msql"
                                        :style="textSysStyle"
                                        :disabled="remote_dbs.length < 1"
                                        ref="selected_connection"
                                        @change="getTables();saveMysqlSett();"
                                        v-model="mysqlSettings.db"
                                >
                                    <option v-for="db in remote_dbs" :value="db">{{ db }}</option>
                                </select>
                                <select class="form-control w w-msql"
                                        :style="textSysStyle"
                                        :disabled="remote_tables.length < 1"
                                        ref="selected_connection"
                                        v-model="mysqlSettings.table"
                                        @change="saveMysqlSett()"
                                >
                                    <option v-for="tb in remote_tables" :value="tb">{{ tb }}</option>
                                </select>
                                <button class="btn btn-success fix-vert" :style="$root.themeButtonStyle" @click="getFieldsFromMySQL(tableHeaders)">Go</button>
                            </div>
                        </div>
                    </div>
                    <!--PASTE settings-->
                    <div v-if="selectedType.key === 'paste'" :style="{height: 'calc(100% - 60px)'}">
                        <textarea class="form-control full-height"
                                  v-model="paste_data"
                                  @change="(e) => { onPasteChange(e);savePasteSett(paste_data); }"
                        ></textarea>
                    </div>
                    <!--OCR settings-->
                    <div v-if="selectedType.key === 'table_ocr'" :style="{height: 'calc(100% - 60px)'}">
                        <ocr-import-block
                            style="max-width: 550px;"
                            :just_one="true"
                            :some_presets="ocr_preset"
                            :import_action="importAction"
                            @props-changed="saveOcrSett"
                            @ocr-image-parsed="ocrParsed"
                        ></ocr-import-block>
                    </div>
                    <!--Airtable settings-->
                    <div v-if="selectedType.key === 'airtable_import'" :style="{height: 'calc(100% - 60px)'}">
                        <airtable-import-block
                            style="max-width: 550px;"
                            :just_one="true"
                            :import_action="importAction"
                            :some_presets="{
                                base_id: airtable_item.user_key_id,
                                single_table: airtable_item.table_name,
                            }"
                            @props-changed="airtableChanged"
                        ></airtable-import-block>
                        <button class="btn btn-success"
                                :disabled="!airtable_item.user_key_id || !airtable_item.table_name"
                                :style="$root.themeButtonStyle"
                                @click="activeTab = 'fields'"
                        >Import</button>
                    </div>
                    <!--G-Sheet settings-->
                    <div v-if="selectedType.key === 'g_sheets'" :style="{height: 'calc(100% - 60px)'}">
                        <table class="g-sheet-elems">
                            <tr>
                                <td>
                                    <label>Select a connected Google Account:&nbsp;</label>
                                </td>
                                <td>
                                    <select class="form-control form-group w w-mid"
                                            :style="textSysStyle"
                                            v-model="g_sheets_account"
                                            @change="gsheetTables"
                                    >
                                        <option v-for="acc in $root.settingsMeta.user_clouds_data"
                                                v-if="acc.cloud == 'Google' && acc.__is_connected"
                                                :value="acc.id"
                                        >{{ acc.name }}</option>
                                    </select>
                                </td>
                                <td></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>
                                    <label>Select a Google Sheets file:</label>
                                </td>
                                <td>
                                    <select class="form-control form-group w w-mid"
                                            :style="textSysStyle"
                                            :disabled="!g_sheets_account"
                                            v-model="g_sheets_file"
                                            @change="importFilesFind"
                                    >
                                        <option v-for="file in foundImportFiles"
                                                :value="file.id"
                                        >{{ file.name }}</option>
                                    </select>
                                </td>
                                <td>
                                    <label>Or paste public sheet URL:</label>
                                </td>
                                <td>
                                    <input class="form-control form-group w w-long"
                                           :style="textSysStyle"
                                           :disabled="!g_sheets_account"
                                           v-model="g_sheets_url"
                                           @change="importFileUrl"/>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label>Select the Sheet to import:</label>
                                </td>
                                <td>
                                    <select class="form-control form-group w w-mid"
                                            :style="textSysStyle"
                                            :disabled="!g_sheets_file"
                                            v-model="g_sheets_element"
                                            @change="saveGsheetSett()"
                                    >
                                        <option v-for="sheet in fileSheets"
                                                :value="sheet.name"
                                        >{{ sheet.name }}</option>
                                    </select>
                                </td>
                                <td>
                                    <label>
                                        <span class="indeterm_check__wrap">
                                            <span class="indeterm_check" @click="gsheetHeaderTgl()">
                                                <i v-if="g_sheets_settings.f_header" class="glyphicon glyphicon-ok group__icon"></i>
                                            </span>
                                        </span>
                                        <span>First row as headers</span>
                                    </label>
                                </td>
                                <td></td>
                            </tr>
                        </table>
                    </div>
                    <!--WEB SCRAPING settings-->
                    <div v-if="selectedType.key === 'web_scrap'" :style="{height: 'calc(100% - 60px)'}">
                        <template v-if="webAction === 'html'">
                            <div class="form-group">
                                <div style="display: inline-block;position: relative;top: -4px;">
                                    <div class="html-xml-wi flex flex--center">
                                        <label>URL:&nbsp;</label>
                                        <input class="form-control" v-model="web_html_url" @change="checkChange('html')" :style="textSysStyle"/>
                                    </div>
                                </div>
                                <button class="btn btn-success small-up"
                                        :disabled="!web_html_url"
                                        :style="$root.themeButtonStyle"
                                        @click="loadPageElems()"
                                >Check</button>
                            </div>
                            <div class="form-group">
                                <div style="display: inline-block;position: relative;top: -4px;">
                                    <div class="html-xml-wi flex flex--center">
                                        <label>Elements:&nbsp;</label>
                                        <select class="form-control" v-model="web_html_part" @change="webPartChange()" :style="textSysStyle">
                                            <option v-for="el in web_elems" :value="el.key">{{ uni(el.val) }}</option>
                                        </select>
                                    </div>
                                </div>
                                <label>
                                    <span class="indeterm_check__wrap">
                                        <span class="indeterm_check" @click="scrapHeaderTgl()">
                                            <i v-if="web_scrap_headers" class="glyphicon glyphicon-ok group__icon"></i>
                                        </span>
                                    </span>
                                    <span>First row as headers.</span>
                                </label>
                            </div>
                        </template>
                        <template v-if="webAction === 'xml'">
                            <div class="form-group html-xml-wi flex flex--start">
                                <select class="form-control" v-model="web_xml_source" @change="webxmlSourceChange()" :style="textSysStyle" style="width: 150px;margin-right: 10px;">
                                    <option value="url">URL(*.xml):</option>
                                    <option value="file">Upload a file:</option>
                                </select>
                                <!--<label style="margin-left: 15px;">{{ web_xml_file }}</label>-->
                                <input v-if="web_xml_source === 'url'"
                                       class="form-control"
                                       v-model="web_xml_url"
                                       @change="checkChange('xml')"
                                       :style="textSysStyle"/>
                                <file-uploader-block
                                        v-if="web_xml_source === 'file'"
                                        :clear_before="true"
                                        :table_id="'temp'"
                                        :field_id="'xml'"
                                        :row_id="xml_uuid"
                                        class="full-width"
                                        @uploaded-file="saveXmlFile"
                                ></file-uploader-block>
                            </div>
                            <div class="form-group html-xml-wi flex flex--center">
                                <label>XPath Query:&nbsp;</label>
                                <input class="form-control" v-model="web_xml_xpath" @change="xpathChanged()" :style="textSysStyle"/>

                                <label style="margin: 0 10px;">
                                    <span class="indeterm_check__wrap">
                                        <span class="indeterm_check" @click="scrapXpathColTgl()">
                                            <i v-if="web_scrap_xpath_query" class="glyphicon glyphicon-ok group__icon"></i>
                                        </span>
                                    </span>
                                    <span>Attribute as a column.</span>
                                </label>

                                <select class="form-control" v-model="web_xml_nested" @change="saveWebScrapSett()" :style="textSysStyle" style="width: 150px;">
                                    <option>0</option>
                                    <option>1</option>
                                    <option>2</option>
                                    <option>3</option>
                                </select>
                                <label style="margin-left: 5px;">Nested level</label>
                            </div>
                        </template>
                    </div>
                    <!--TRANSPOSE settings-->
                    <div v-if="selectedType.key === 'transpose_import'" :style="{height: 'calc(100% - 60px)'}">
                        <transpose-import-block
                                :transpose_item="transpose_item"
                                @prop-changed="saveTransposeSett()"
                                @src-changed="transposeFuzzyMatching(tableHeaders.def, transpose_item.source_tb_id)"
                        ></transpose-import-block>
                    </div>
                    <!--JIRA settings-->
                    <div v-if="selectedType.key === 'jira_import'" :style="{height: 'calc(100% - 60px)'}">
                        <jira-import-block
                                :table_meta="tableMeta"
                                :jira_item="jira_item"
                                @project-changed="jiraChanged"
                                @jira-item-changed="saveJiraSett"
                        ></jira-import-block>
                    </div>
                    <!--SALESFORCE settings-->
                    <div v-if="selectedType.key === 'salesforce_import'" :style="{height: 'calc(100% - 60px)'}">
                        <salesforce-import-block
                                :table_meta="tableMeta"
                                :salesforce_item="salesforce_item"
                                @object-changed="salesforceChanged"
                                @salesforce-item-changed="saveSalesforceSett"
                        ></salesforce-import-block>
                    </div>
                </div>
                <div class="container-fluid data-tab-footer">
                    <div class="row">
                        <div class="col-xs-12">
                            <label>Notes: {{ selectedType.notes }}</label>
                            <label v-if="alert_note" style="color: red">{{ alert_note }}</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <!--Tab with fields settings-->
        <div class="data-tab data-tab-fixed full-width" v-if="activeTab === 'fields'" :style="$root.themeMainBgStyle">
            <div class="fields-wrapper fields-wrapper-fixed">

                <import-fields-block
                        :table-meta="tableMeta"
                        :selected-type="selectedType"
                        :table-headers="tableHeaders.def"
                        :can-get-access="canGetAccess()"
                        :present-source="presentSource()"
                        :fields-columns="fieldsColumns"
                        :mysql-columns="mysqlColumns"
                        :transpose_item="transpose_item"
                        :name-converter="nameConverter"
                        @sel-ref-table="(v) => { sel_ref_table = v; }"
                        @reference-import="importTable"
                        @store-transpose="saveTransposeSett()"
                ></import-fields-block>

            </div>
        </div>


        <!--Tab with storage and backup settings-->
        <div class="data-tab" v-if="canGetAccess() && activeTab === 'storage-backup'" :style="$root.themeMainBgStyle">
            <div class="fields-wrapper full-height">
                <tab-storage-backup
                    :table-meta="tableMeta"
                    :settings-meta="$root.settingsMeta"
                    :user="$root.user"
                    :cell-height="1"
                    :max-cell-rows="0"
                ></tab-storage-backup>
            </div>
        </div>


        <!--Popup with transfer data progress bar-->
        <div v-if="import_progress_id" class="import-progressbar-wrapper">
            <div class="import-progressbar">
                <h1 v-if="import_progress_type === 'jira_import_sync'">Syncing Data To Table</h1>
                <h1 v-else>Importing Data To Table</h1>
                <div class="progressbar-wrapper">
                    <div ref="transfer_progressbar"></div>
                </div>
                <div v-if="import_progress_info" class="bold" v-html="import_progress_info"></div>
            </div>
        </div>

        <!--Error Msg-->
        <div v-if="add_err_msg.show" class="modal-wrapper">
            <div class="modal">
                <div class="modal-dialog modal-sm">
                    <div class="modal-content">
                        <div class="modal-header">{{ add_err_msg.header }}</div>
                        <div class="modal-body">{{ add_err_msg.body }}</div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-success" @click="add_err_msg.show = false">{{ add_err_msg.btn }}</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import {SpecialFuncs} from '../../../../classes/SpecialFuncs';

    import {eventBus} from '../../../../app';

    import PasteAutowrapperMixin from './../../../_Mixins/PasteAutowrapperMixin.vue';
    import GoogleSheetMixin from './../../../_Mixins/GoogleSheetMixin.vue';
    import DataImportMixin from './../../../_Mixins/DataImportMixin.vue';
    import TabDataSettingsMixin from './../../../_Mixins/TabDataSettingsMixin.vue';
    import CellStyleMixin from "../../../_Mixins/CellStyleMixin";

    import TabStorageBackup from './TabStorageBackup';
    import InfoSignLink from "../../../CustomTable/Specials/InfoSignLink";
    import ImportFieldsBlock from "../../../CommonBlocks/ImportFieldsBlock";
    import FileUploaderBlock from "../../../CommonBlocks/FileUploaderBlock";
    import OcrImportBlock from "../../../CommonBlocks/OcrImportBlock";
    import AirtableImportBlock from "../../../CommonBlocks/AirtableImportBlock";
    import SelectBlock from "../../../CommonBlocks/SelectBlock";
    import TransposeImportBlock from "../../../CommonBlocks/TransposeImportBlock.vue";
    import JiraImportBlock from "../../../CommonBlocks/JiraImportBlock.vue";
    import SalesforceImportBlock from "../../../CommonBlocks/SalesforceImportBlock.vue";

    export default {
        name: 'TabData',
        components: {
            SalesforceImportBlock,
            JiraImportBlock,
            TransposeImportBlock,
            SelectBlock,
            AirtableImportBlock,
            OcrImportBlock,
            FileUploaderBlock,
            ImportFieldsBlock,
            InfoSignLink,
            TabStorageBackup,
        },
        mixins: [
            PasteAutowrapperMixin,
            GoogleSheetMixin,
            DataImportMixin,
            TabDataSettingsMixin,
            CellStyleMixin,
        ],
        data: function () {
            return {
                nameConverter: null,
                add_err_msg: {
                    show: false,
                    header: 'Info',
                    body: '',
                    btn: 'OK',
                },

                import_progress_id: null,
                import_progress_type: '',
                import_progress_info: '',
                import_progress_item: null,
                activeTab: 'method',
                importTypes: [
                    {
                        key: 'scratch',
                        name: 'Build/Update',
                        has_action: false,
                        notes: 'to build a new data table from scratch by adding fields or updating the fields for an existing data/table.'
                    },
                    {
                        key: 'csv',
                        name: 'CSV/Excel Import',
                        has_action: true,
                        notes: 'import data from a CSV file with data table fields given or not. If data/table fields not given in the CSV file or not checked to use in the settings, user can define those in the field settings tab. Any existing data/table info will be overwirrten.'
                    },
                    {
                        key: 'mysql',
                        name: 'MySQL Import',
                        has_action: true,
                        notes: 'import data of selected fields of a MySQL data table from local or remote server OR uploading a mysql file. The same table fields and data format will be used. User to define table head names. User can add or remove field(s) for importing.'
                    },
                    {
                        key: 'remote',
                        name: 'Remote MySQL',
                        has_action: true,
                        notes: 'to retrieve data from a MySQL table from a local or remote server. No data table will be created (copied) to local. Only management data will be created.'
                    },
                    {
                        key: 'reference',
                        name: 'Referencing',
                        has_action: false,
                        notes: 'to glue the data of selected fields from multiple existing data tables, public or private, through the defined field correspondences between current data table and a selected source data tables. Glue means putting the data records of one data table after another into current data table. The data records for a given source data table can be updated by deleting existing referencing and re-importing (add referencing record and then import).'
                    },
                    {
                        key: 'paste',
                        name: 'Paste to Import',
                        has_action: true,
                        notes: 'to import formatted data copied from external sources, i.e., Excel tables, Goole Sheets and HTML tables, and pasted onto the container.'
                    },
                    {
                        key: 'web_scrap',
                        name: 'Web Scraping',
                        has_action: true,
                        notes: 'to import data from a table (“Import HTML” option for table data) or list (“Import XML” option for structured XML data) within an HTML page given by www address.'
                    },
                    {
                        key: 'g_sheets',
                        name: 'Google Sheets',
                        has_action: true,
                        notes: 'to import data from a selected sheet of a Google Sheets file.'
                    },
                    {
                        key: 'table_ocr',
                        name: 'OCR Import',
                        has_action: true,
                        notes: 'to import data from a table of an uploaded image.'
                    },
                    {
                        key: 'airtable_import',
                        name: 'Airtable Import',
                        has_action: true,
                        special_sources: true,
                        notes: 'to import data from Airtable.com.'
                    },
                    {
                        key: 'transpose_import',
                        name: 'Transpose',
                        has_action: false,
                        notes: 'to transpose data.'
                    },
                    {
                        key: 'jira_import',
                        name: 'Jira Import/Sync',
                        has_action: false,
                        notes: 'to import/sync data from Jira.'
                    },
                    {
                        key: 'salesforce_import',
                        name: 'Salesforce Import/Sync',
                        has_action: false,
                        notes: 'to import/sync data from Salesforce.'
                    },
                ],
                availableCodes: {
                    scratch: 'data_build',
                    csv: 'data_csv',
                    mysql: 'data_mysql',
                    remote: 'data_remote',
                    reference: 'data_ref',
                    paste: 'data_paste',
                    g_sheets: 'data_g_sheets',
                    web_scrap: 'data_web_scrap',
                },

                csv_link: '',
                csvSettings: {
                    xls_sheet:'',
                    filename: '',
                    headerRowNum: 1,
                    replace: true,
                    quote: true,
                    apostrophe: true,
                    backslash:true,
                    startRow: null,
                    endRow: null
                },
                xlsSheets: [],
                fieldsColumns: [],

                mysqlSettings: {
                    connection_id: null,
                    name: null,
                    host: null,
                    login: null,
                    pass: null,
                    db: null,
                    table: null,
                    save: false
                },
                mysqlColumns: [],

                xml_uuid: uuidv4(),
                web_elems: [],
                web_html_part: '',
                web_html_url: '',
                web_html_query: '',
                web_html_index: '',
                web_xml_source: 'url',
                web_xml_nested: 0,
                web_xml_file: '',
                web_xml_url: '',
                web_xml_xpath: '',
                web_scrap_headers: false,
                web_scrap_xpath_query: false,

                ocr_preset: null,
                ocr_item: {
                    firstHeader: true,
                    sourceFile: null,
                },

                airtable_item: {
                    user_key_id: null,
                    table_name: '',
                    fromtype: 'single',
                },

                jira_item: {
                    action: 'import',
                    sync_direction: 'to_tablda',
                    cloud_id: null,
                    project_names: [],
                    jql_query: '',
                    remove_not_found: 0,
                    add_new_records: 1,
                    update_changed: 1,
                },

                salesforce_item: {
                    action: 'import',
                    sync_direction: 'to_tablda',
                    cloud_id: null,
                    object_id: null,
                    object_name: '',
                    remove_not_found: 0,
                    add_new_records: 1,
                    update_changed: 1,
                },

                transpose_item: {
                    direction: 'direct',
                    skip_empty: false,
                    source_tb_id: null,
                    row_group_id: null,
                    grouper_field_id: null,
                    reverse_val_field_id: null,
                    options: [
                        {val: 'inheritance', show: 'Saves the data of ->'},
                        {val: 'field_name', show: 'Saves the Names of Columns after Transposing'},
                        {val: 'field_values', show: 'Saves the Values of Columns to be Transposed from ->'},
                    ],
                    options_rev: [
                        {val: 'inheritance', show: 'Saves the data of ->'},
                        {val: 'reverse_group', show: 'Saves the Values of Rows to be Transposed from ->'},
                    ],
                },

                disabledKeys: false,
                csvAction: 'upload',
                webAction: '',
                importAction: '',
                typeKey: '',
                tableHeaders: { def: this.copyFrom(this.tableMeta._fields) },
                remote_tables: [],
                remote_dbs: [],
                selected_connection: -1,
                sel_ref_table: null,
            };
        },
        computed: {
            presentHeadersCols() {
                return this.tableMeta._fields.length - this.$root.systemFields.length;
            },
            selectedType() {
                return this.typeKey !== ''
                    ? _.find(this.importTypes, {key: this.typeKey}) || {}
                    : {};
            },
            imgSource() {
                switch (this.typeKey) {
                    case "g_sheets": return '/assets/img/google-sheet.png';
                    default: return '';
                }
            },
        },
        props:{
            tableMeta: Object,
            cellHeight: Number,
            maxCellRows: Number,
            table_id: Number,
        },
        methods: {
            changeCsvRowNum() {
                this.csvSettings.headerRowNum = Math.max(1, this.csvSettings.headerRowNum);

                this.csvSettings.startRow = to_float(this.csvSettings.headerRowNum) > to_float(this.csvSettings.startRow)
                    ? null
                    : this.csvSettings.startRow;

                this.csvSettings.endRow = to_float(this.csvSettings.startRow) > to_float(this.csvSettings.endRow)
                    ? null
                    : this.csvSettings.endRow;

                this.saveCsvSett();
            },
            importTypesOptions() {
                let filter = _.filter(this.importTypes, (itype) => {
                    return this.canGetAccess(itype.key);
                });

                return _.map(filter, (itype) => {
                    return {
                        img: itype.key === 'g_sheets' ? '/assets/img/google-sheet.png' : '',
                        val: itype.key,
                        show: itype.name,
                        disabled: !this.canSave(itype.key),
                    };
                });
            },
            initSelects() {
                if (this.tableMeta._is_owner) {
                    this.typeKey = this.tableMeta.source;
                } else {
                    _.each(this.importTypes, (el) => {
                        if (this.canGetAccess(el.key)) {
                            if (!this.typeKey) {
                                this.typeKey = el.key;
                                this.disabledKeys = true;
                            } else {
                                this.disabledKeys = false;
                            }
                        }
                    });
                }
                this.webAction = 'html';
            },
            canGetAccess(code) {
                //if no 'code' provided -> check only '_is_owner'
                let can = !!this.tableMeta._is_owner;
                if (code && !can) {
                    let idx_code = ['scratch','csv','mysql','remote','reference','paste','g_sheets','web_scrap'].indexOf(code);
                    if (idx_code > -1) {
                        can = this.tableMeta._current_right && this.tableMeta._current_right.datatab_methods.charAt(idx_code) === '1';
                    }
                    if (code === 'not_only_append') {
                        can = this.tableMeta._current_right && !this.tableMeta._current_right.datatab_only_append;
                    }
                }
                return can;
            },

            canSave(typeKey) {
                if (['table_ocr', 'airtable_import', 'transpose_import', 'jira_import', 'salesforce_import'].indexOf(typeKey) > -1) {
                    return true;
                }
                return this.$root.checkAvailable(this.$root.user, this.availableCodes[typeKey]);
            },

            changeTab(opt) {
                this.typeKey = opt.val;
                if (this.inArray(this.selectedType.key, ['scratch', 'reference'])) {
                    this.activeTab = 'fields';
                }
                this.applySaves();
            },
            presentSource() {
                let res = true;
                if (this.selectedType.key === 'csv') {
                    res = this.csvSettings.filename;
                }
                if (this.inArray(this.selectedType.key, ['mysql', 'remote'])) {
                    res = this.mysqlColumns.length;
                }
                if (this.selectedType.key === 'paste') {
                    res = this.paste_file;
                }
                if (this.selectedType.key === 'g_sheets') {
                    res = this.g_sheets_file && this.g_sheets_element;
                }
                if (this.selectedType.key === 'web_scrap') {
                    res = this.web_html_url || this.web_xml_url;
                }
                if (this.selectedType.key === 'table_ocr') {
                    res = this.ocr_item && this.ocr_item.sourceFile;
                }
                if (this.selectedType.key === 'airtable_import') {
                    res = this.airtable_item.table_name && this.airtable_item.user_key_id;
                }
                if (this.selectedType.key === 'transpose_import') {
                    res = this.transpose_item.source_tb_id;
                }
                if (this.selectedType.key === 'jira_import') {
                    res = this.jira_item.cloud_id && (this.jira_item.project_names.length || this.jira_item.jql_query);
                }
                if (this.selectedType.key === 'salesforce_import') {
                    res = this.salesforce_item.cloud_id && this.salesforce_item.object_id;
                }
                return Boolean(res && this.activeTab === 'fields');
            },

            prepareImport() {
                if (this.selectedType.key === 'transpose_import') {
                    this.importAction = 'append';

                    if (
                        this.transpose_item.direction === 'direct'
                        &&
                        (
                            !_.find(this.tableHeaders.def, {trps_type: 'field_name'})
                            ||
                            !_.find(this.tableHeaders.def, {trps_type: 'field_values'})
                        )
                    ) {
                        Swal('Info', 'For transpose should be selected columns for "Name" and "Values" (Transpose Settings / Actions)!');
                        return false;
                    }

                    if (
                        this.transpose_item.direction === 'reverse'
                        &&
                        (
                            ! this.transpose_item.grouper_field_id
                            ||
                            ! this.transpose_item.reverse_val_field_id
                        )
                    ) {
                        Swal('Info', 'For transpose should be selected "Groups from Column" and "Column of Value"!');
                        return false;
                    }

                    this.saveTransposeSett();
                }
                if (this.selectedType.key === 'jira_import') {
                    if (this.jira_item.action === 'import') {
                        let issKey = _.find(this.tableHeaders.def, {last_col_corr: "IssueId"});
                        if (!issKey) {
                            Swal('Info', '“Issue Id” needs to be available for implementing sync. function');
                            return false;
                        }
                    }
                    if (this.jira_item.action === 'sync') {
                        this.importAction = 'append';
                    }
                }
                return true;
            },
            importTable() {
                if (!this.prepareImport()) {
                    return;
                }

                if (
                    this.inArray(this.selectedType.key, ['csv','mysql','remote','paste','web_scrap','g_sheets','table_ocr','airtable_import','transpose_import','jira_import'])
                    &&
                    this.importAction === 'new'
                ) {
                    Swal({
                        title: 'Info',
                        text: "Import Data. Choosing “New” for importing will erase all existing data and settings for current table. Confirm to proceed.",
                        confirmButtonClass: "btn-danger",
                        confirmButtonText: "Yes",
                        cancelButtonText: "No",
                        showCancelButton: true,
                        closeOnConfirm: true,
                        animation: "slide-from-top"
                    }).then(resp => {
                        if (resp.value) {
                            this.sendModifyRequest();
                        }
                    });
                } else {
                    this.sendModifyRequest();
                }
            },
            sendModifyRequest() {
                if (!this.importAction && this.inArray(this.selectedType.key, ['csv','mysql','remote','paste','web_scrap','g_sheets','transpose_import','jira_import'])) {
                    Swal('Info','Please select an option.');
                    return;
                }
                let msg = this.theSameHeaders(this.tableHeaders.def);
                if (msg) {
                    Swal(msg);
                    return;
                }
                $.LoadingOverlay('show');
                let gsheet = {
                    settings: this.g_sheets_settings,
                    cloud_id: this.g_sheets_account,
                    sheet_id: this.g_sheets_file,
                    page: this.g_sheets_element,
                };
                let htmlxml = {
                    web_action: this.webAction,
                    web_url: this.webAction === 'html' ? this.web_html_url : this.web_xml_url,
                    web_query: this.web_html_query,
                    web_xpath: this.webAction === 'html' ? '' : this.web_xml_xpath,
                    web_index: this.web_html_index,
                    web_xml_file: this.web_xml_file,
                    web_xml_nested: this.web_xml_nested,
                    web_scrap_headers: this.web_scrap_headers,
                    web_scrap_xpath_query: this.web_scrap_xpath_query,
                };
                let ocr_data = {
                    first_header: this.ocr_item.firstHeader,
                    csv_source_file: this.ocr_item.sourceFile,
                };

                this.saveLastColsCorrespondence(this.tableHeaders.def);
                axios.post('/ajax/import/modify-table', {
                    table_id: this.tableMeta.id,
                    columns: this.tableHeaders.def,
                    present_cols_idx: this.presentHeadersCols,
                    import_type: this.selectedType.key,
                    import_action: this.importAction || 'new',
                    csv_settings: this.csvSettings,
                    mysql_settings: this.mysqlSettings,
                    referenced_table: this.sel_ref_table,
                    paste_settings: this.paste_settings,
                    paste_file: this.paste_file,
                    transpose_item: this.transpose_item,
                    jira_item: this.jira_item,
                    salesforce_item: this.salesforce_item,
                    html_xml: htmlxml,
                    g_sheets: gsheet,
                    ocr_data: ocr_data,
                    airtable_data: this.airtable_item,
                }).then(({ data }) => {
                    this.tableMeta.id = data.new_id || this.tableMeta.id;
                    if (data.job_id) {
                        this.import_progress_id = data.job_id;
                        this.import_progress_type = this.jira_item && this.jira_item.action === 'sync'
                            ? 'jira_import_sync'
                            : this.selectedType.key;
                        this.import_progress_item = this.jira_item;
                    } else {
                        this.$emit('import-finished', this.tableMeta.id);
                    }
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
            selectedConnection(elem) {
                let conn = this.$root.settingsMeta.user_connections_data[ this.selected_connection ];
                this.mysqlSettings.connection_id = conn ? conn.id : '';
                this.mysqlSettings.name = conn ? conn.name : '';
                this.mysqlSettings.host = conn ? conn.host : '';
                this.mysqlSettings.login = conn ? conn.login : '';
                this.mysqlSettings.pass = conn ? conn.pass : '';
                this.mysqlSettings.db = '';
                this.mysqlSettings.table = '';

                this.remote_dbs = [];
                this.remote_tables = [];
                this.saveMysqlSett();
            },
            getDBS(can_swal) {
                if (this.mysqlSettings.connection_id) {
                    this.$root.sm_msg_type = 2;
                    axios.get('/ajax/import/remote-dbs', {
                        params: {
                            host: this.mysqlSettings.host,
                            login: this.mysqlSettings.login,
                            pass: this.mysqlSettings.pass
                        }
                    }).then(({ data }) => {
                        this.remote_dbs = data;
                    }).catch(errors => {
                        Swal('Info', getErrors(errors));
                    }).finally(() => {
                        this.$root.sm_msg_type = 0;
                    });
                } else {
                    if (can_swal) {
                        Swal('Info','Select the connection');
                    }
                }
            },
            getTables() {
                if (this.mysqlSettings.db) {
                    this.$root.sm_msg_type = 2;
                    axios.get('/ajax/import/remote-tables', {
                        params: {
                            host: this.mysqlSettings.host,
                            login: this.mysqlSettings.login,
                            pass: this.mysqlSettings.pass,
                            db: this.mysqlSettings.db
                        }
                    }).then(({ data }) => {
                        this.remote_tables = data;
                    }).catch(errors => {
                        Swal('Info', getErrors(errors));
                    }).finally(() => {
                        this.$root.sm_msg_type = 0;
                    });
                }
            },
            ocrParsed(importItem) {
                this.ocr_item.sourceFile = importItem.source_file;
                this.getFieldsFromOCR(this.tableHeaders, 'def', this.ocr_item);
            },
            airtableChanged(datas) {
                this.airtable_item.user_key_id = datas.user_api_obj ? datas.user_api_obj.id : null;
                this.airtable_item.table_name = datas.single_table_name;
                this.saveAirtableSett();
                if (datas.single_table_name) {
                    this.loadFieldsFromAirtable(this.tableHeaders, 'def', this.airtable_item);
                }
            },
            jiraChanged() {
                this.saveJiraSett();
                this.loadFieldsFromJira(this.tableHeaders, 'def', this.jira_item);
            },
            salesforceChanged() {
                this.saveSalesforceSett();
                this.loadFieldsFromSalesforce(this.tableHeaders, 'def', this.salesforce_item);
            },

            //WEB SCRAPING
            checkChange(type) {
                this.web_elems = [];
                this.web_xml_xpath = '';
                this.web_xml_file = '';
                if (type === 'html' && String(this.web_html_url).match(/\.xml$/gi)) {
                    this.web_xml_url = this.web_html_url;
                    this.web_html_url = '';
                    this.webAction = 'xml';
                }
                if (type === 'xml' && String(this.web_xml_url).match(/\.html$/gi)) {
                    this.web_html_url = this.web_xml_url;
                    this.web_xml_url = '';
                    this.webAction = 'html';
                }
                this.saveWebScrapSett();
            },
            loadPageElems(kkey) {
                if (this.web_html_url || this.web_xml_url || this.web_xml_file) {
                    this.$root.sm_msg_type = 2;
                    axios.post('/ajax/import/get-fields/web-scrap', {
                        web_action: 'preload',
                        web_url: this.webAction === 'html' ? this.web_html_url : this.web_xml_url,
                        web_xml_file: this.webAction === 'html' ? '' : this.web_xml_file,
                    }).then(({ data }) => {
                        if (data.elems && data.elems.length) {
                            this.web_elems = data.elems;
                            this.web_html_part = kkey || _.first(data.elems).key;
                            this.webPartChange();
                        } else {
                            Swal('Info', 'Elements are not found!');
                        }
                    }).catch(errors => {
                        Swal('Info', getErrors(errors));
                    }).finally(() => {
                        this.$root.sm_msg_type = 0;
                    });
                } else {
                    //Swal('Info','Url is empty!');
                }
            },
            uni(txt) {
                return unicodeToChar(txt);
            },
            webPartChange() {
                let arr = String(this.web_html_part).split('_');
                this.web_html_query = _.trim(arr[0]);
                this.web_html_index = _.trim(arr[1]);
                this.saveWebScrapSett();
            },
            webXpathChang() {
                this.web_html_part = '';
                this.web_html_query = '';
                this.web_html_index = '';
            },
            saveXmlFile() {
                this.checkChange('xml');
                this.web_xml_file = 'xml/'+this.xml_uuid;
            },
            webxmlSourceChange() {
                this.web_xml_file = '';
                this.web_xml_url = '';
                this.saveWebScrapSett();
            },
            scrapHeaderTgl() {
                this.web_scrap_headers = !this.web_scrap_headers;
                this.saveWebScrapSett();
            },
            scrapXpathColTgl() {
                this.web_scrap_xpath_query = !this.web_scrap_xpath_query;
                this.saveWebScrapSett();
            },
            xpathChanged() {
                this.web_xml_xpath = '//' + String(this.web_xml_xpath).replace(/^[\/]+/i, '');
                this.saveWebScrapSett();
            },
            gsheetHeaderTgl() {
                this.g_sheets_settings.f_header = !this.g_sheets_settings.f_header;
                this.saveGsheetSett();
            },
            gsheetTables() {
                this.preloadGoogleTables();
                this.saveGsheetSett();
            },
            importFilesFind() {
                this.loadTableSheets();
                this.saveGsheetSett();
            },
            importFileUrl() {
                let match = String(this.g_sheets_url || '').match(/\/d\/[^\/]+/gi);
                match = _.first(match);
                let shid = String(match || '').replace('/d/', '');
                if (shid) {
                    this.g_sheets_file = shid;
                    this.loadTableSheets(null, shid);
                    this.saveGsheetSett();
                } else {
                    Swal('Info','Incorrect Gsheet url.');
                }
            },
        },
        mounted() {
            this.importAction = this.tableMeta._global_rows_count ? 'append' : 'new';
            this.initSelects();
            this.applySaves();
            this.applyLastColsCorrespondence(this.tableHeaders.def);

            setInterval(() => {
                if (this.import_progress_id) {
                    axios.get('/ajax/import/status', {
                        params: {
                            import_jobs: [this.import_progress_id]
                        }
                    }).then(({ data }) => {
                        let frst = _.first(data);
                        $(this.$refs.transfer_progressbar).css('width', frst.complete+'%');
                        this.import_progress_info = frst.info;
                        if (frst.status === 'done') {
                            this.import_progress_id = false;
                            this.$emit('import-finished', this.tableMeta.id);
                        }
                    });
                }
            }, 1000);
        },
        beforeDestroy() {
        }
    }
</script>

<style lang="scss" scoped>
    @import "TabData";

    .fa-reply {
        transform: scaleY(-1);
    }

    .td-indexes {
        background-color: #DDD;

        span {
            display: block;
            cursor: pointer;
        }
    }

    .td-overed {
        border-top: 2px dashed #000 !important;
    }

    .small-up {
        position: relative;
        top: -2px;
    }

    .html-xml-wi {
        width: 600px;
        white-space: nowrap;
    }

    .modal-dialog {
        width: 450px;

        .modal-header {
            background-color: #444;
            padding: 5px 10px;
            font-size: 2em;
            font-weight: bold;
            color: #FFF;
        }
        .modal-body {
            font-size: 1.5em;
        }
    }

    .g-sheet-elems {
        background-color: transparent !important;

        label {
            margin-bottom: 15px;
        }
    }

    .absolute-right {
        position: absolute;
        right: 0;
    }

    .btn-default {
        height: 36px;
    }
</style>