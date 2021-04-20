<template>
    <div class="tab-data">
        <button v-if="selectedType.key !== 'reference' && activeTab === 'fields'"
                class="btn btn-success pull-right"
                :disabled="!presentSource() || !canSave(typeKey)"
                :style="$root.themeButtonStyle"
                @click="importTable()"
        >Save</button>
        <div class="data-menu">
            <button class="btn btn-default" :class="{active: activeTab === 'method'}" @click="activeTab = 'method'">
                Method ({{ selectedType.name }})
            </button>
            <button class="btn btn-default" :disabled="!canSave(typeKey)" :class="{active: activeTab === 'fields'}" @click="activeTab = 'fields'">
                Field Settings
            </button>
            <button v-if="canGetAccess()"
                    class="btn btn-default"
                    :class="{active: activeTab === 'storage-backup'}"
                    @click="activeTab = 'storage-backup'"
            >
                Storage &amp; Backup
            </button>

            <div class="info-icon">
                <info-sign-link
                        v-show="activeTab === 'method'"
                        :app_sett_key="'help_link_import_methods'"
                        :hgt="22"
                ></info-sign-link>
                <info-sign-link
                        v-show="activeTab === 'fields'"
                        :app_sett_key="'help_link_import_fields'"
                        :hgt="22"
                ></info-sign-link>
                <info-sign-link
                        v-show="activeTab === 'storage-backup'"
                        :app_sett_key="'help_link_table_backups'"
                        :hgt="22"
                ></info-sign-link>
            </div>
        </div>


        <!--Tab with import settings-->
        <div class="data-tab" v-show="activeTab === 'method'" :style="$root.themeMainBgStyle">
            <div class="full-height fields-wrapper no-padding">
                <div class="container-fluid data-tab-main">
                    <div class="row" :style="{height: '50px'}">
                        <div class="col-xs-12 form-group">
                            <select class="form-control w w-mid"
                                    v-model="typeKey"
                                    :disabled="disabledKeys"
                                    @change="changeTab()"
                            >
                                <option v-for="elem in importTypes"
                                        v-if="canGetAccess(elem.key)"
                                        :disabled="!canSave(elem.key)"
                                        :value="elem.key"
                                >{{ elem.name }}</option>
                            </select>
                            <select class="form-control w w-mid"
                                    v-model="importAction"
                                    v-if="typeKey !== 'web_scrap' && selectedType.has_action"
                                    :disabled="!canGetAccess('not_only_append')"
                            >
                                <option :disabled="!canGetAccess('not_only_append')">New</option>
                                <option :disabled="!canGetAccess('not_only_append')">Append</option>
                            </select>
                            <select class="form-control w w-mid"
                                    v-model="webAction"
                                    v-if="typeKey === 'web_scrap' && selectedType.has_action"
                            >
                                <option value="html">Import HTML</option>
                                <option value="xml">Import XML</option>
                            </select>

                            <template v-if="selectedType.key === 'paste'">
                                <label>
                                    <span class="indeterm_check__wrap">
                                        <span class="indeterm_check" @click="paste_settings.f_header = !paste_settings.f_header">
                                            <i v-if="paste_settings.f_header" class="glyphicon glyphicon-ok group__icon"></i>
                                        </span>
                                    </span>
                                    <span>First row as headers</span>
                                </label>
                                <button class="btn btn-success pull-right" :style="$root.themeButtonStyle" @click="getFieldsFromPaste()">Import</button>
                            </template>

                            <template v-if="selectedType.key === 'g_sheet'">
                                <label>
                                    <span class="indeterm_check__wrap">
                                        <span class="indeterm_check" @click="g_sheet_settings.f_header = !g_sheet_settings.f_header">
                                            <i v-if="g_sheet_settings.f_header" class="glyphicon glyphicon-ok group__icon"></i>
                                        </span>
                                    </span>
                                    <span>First row as headers</span>
                                </label>
                                <button class="btn btn-success pull-right" :style="$root.themeButtonStyle" @click="getFieldsFromGSheet()">Import</button>
                            </template>
                        </div>
                    </div>
                    <!--CSV settings-->
                    <div v-if="selectedType.key === 'csv'" :style="{height: 'calc(100% - 55px)'}">
                        <div class="row">
                            <div class="col-xs-12 form-group">
                                <input ref="csv_upload" type="file" accept=".csv" class="form-control w w-long"/>
                                <span class="w">OR</span>
                                <input type="text" class="form-control w w-long" v-model="csv_link" placeholder="www address of file"/>
                                <button class="btn btn-info" :style="$root.themeButtonStyle" @click="getFieldsFromCSV()">Import</button>
                            </div>
                        </div>
                        <div class="row form-group">
                            <div class="col-xs-12 col-md-6 col-lg-5">
                                <div class="row">
                                    <div class="col-xs-12">
                                        <label>
                                            <span class="indeterm_check__wrap">
                                                <span class="indeterm_check" @click="csvSettings.firstHeader = !csvSettings.firstHeader">
                                                    <i v-if="csvSettings.firstHeader" class="glyphicon glyphicon-ok group__icon"></i>
                                                </span>
                                            </span>
                                            <span>First row as headers</span>
                                        </label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-xs-12">
                                        <label>
                                            <span class="indeterm_check__wrap">
                                                <span class="indeterm_check" @click="csvSettings.secondType = !csvSettings.secondType">
                                                    <i v-if="csvSettings.secondType" class="glyphicon glyphicon-ok group__icon"></i>
                                                </span>
                                            </span>
                                            <span>Second row as data type</span>
                                        </label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-xs-12">
                                        <label>
                                            <span class="indeterm_check__wrap">
                                                <span class="indeterm_check" @click="csvSettings.thirdSize = !csvSettings.thirdSize">
                                                    <i v-if="csvSettings.thirdSize" class="glyphicon glyphicon-ok group__icon"></i>
                                                </span>
                                            </span>
                                            <span>Third row as size</span>
                                        </label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-xs-12">
                                        <label>
                                            <span class="indeterm_check__wrap">
                                                <span class="indeterm_check" @click="csvSettings.fourthDefault = !csvSettings.fourthDefault">
                                                    <i v-if="csvSettings.fourthDefault" class="glyphicon glyphicon-ok group__icon"></i>
                                                </span>
                                            </span>
                                            <span>Fourth row as default value</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xs-12 col-md-6 col-lg-5">
                                <div class="row">
                                    <div class="col-xs-12">
                                        <label>
                                            <span class="indeterm_check__wrap">
                                                <span class="indeterm_check" @click="csvSettings.replace = !csvSettings.replace">
                                                    <i v-if="csvSettings.replace" class="glyphicon glyphicon-ok group__icon"></i>
                                                </span>
                                            </span>
                                            <span>Replace Accents/Diacriticals</span>
                                        </label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-xs-12">
                                        <label>
                                            <span class="indeterm_check__wrap">
                                                <span class="indeterm_check" @click="csvSettings.quote = !csvSettings.quote">
                                                    <i v-if="csvSettings.quote" class="glyphicon glyphicon-ok group__icon"></i>
                                                </span>
                                            </span>
                                            <span>Treat all Quoting Characted as data</span>
                                        </label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-xs-12">
                                        <label>
                                            <span class="indeterm_check__wrap">
                                                <span class="indeterm_check" @click="csvSettings.apostrophe = !csvSettings.apostrophe">
                                                    <i v-if="csvSettings.apostrophe" class="glyphicon glyphicon-ok group__icon"></i>
                                                </span>
                                            </span>
                                            <span>Input CSV Quoting Characted is Apostrophe</span>
                                        </label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-xs-12">
                                        <label>
                                            <span class="indeterm_check__wrap">
                                                <span class="indeterm_check" @click="csvSettings.backslash = !csvSettings.backslash">
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
                                <div class="row">
                                    <div class="col-xs-12">
                                        <label>Starting row: <input type="number" class="form-control w w-mid" v-model="csvSettings.startRow"></label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-xs-12">
                                        <label>Ending row: <input type="number" class="form-control w w-mid" v-model="csvSettings.endRow"></label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--MySQL settings-->
                    <div v-if="selectedType.key === 'mysql' || selectedType.key === 'remote'" :style="{height: 'calc(100% - 55px)'}">
                        <div class="row form-group">
                            <div class="col-xs-12">
                                <select class="form-control w w-msql" v-model="selected_connection" @change="selectedConnection()">
                                    <option></option>
                                    <option v-for="(conn, i) in settingsMeta.user_connections_data" :value="i">{{ conn.name }}</option>
                                </select>
                                <button class="btn btn-success w fix-btn-h" :style="$root.themeButtonStyle" @click="getDBS()">Connect</button>
                                <select class="form-control w w-msql"
                                        :disabled="remote_dbs.length < 1"
                                        ref="selected_connection"
                                        @change="getTables()"
                                        v-model="mysqlSettings.db"
                                >
                                    <option v-for="db in remote_dbs" :value="db">{{ db }}</option>
                                </select>
                                <select class="form-control w w-msql"
                                        :disabled="remote_tables.length < 1"
                                        ref="selected_connection"
                                        v-model="mysqlSettings.table"
                                >
                                    <option v-for="tb in remote_tables" :value="tb">{{ tb }}</option>
                                </select>
                                <button class="btn btn-success fix-vert" :style="$root.themeButtonStyle" @click="getFieldsFromMySQL()">Go</button>
                            </div>
                        </div>
                    </div>
                    <!--PASTE settings-->
                    <div v-if="selectedType.key === 'paste'" :style="{height: 'calc(100% - 55px)'}">
                        <textarea class="form-control full-height" v-model="paste_data" @paste="onPaste"></textarea>
                    </div>
                    <!--G-Sheet settings-->
                    <div v-if="selectedType.key === 'g_sheet'" :style="{height: 'calc(100% - 55px)'}">
                        <div>
                            <label class="red">* Google table must have public access</label>
                        </div>
                        <label>Google Sheet Link:</label>
                        <input class="form-control form-group w w-long" v-model="g_sheet_link"/>
                        <label>Sheet Name:</label>
                        <input class="form-control form-group w w-mid" v-model="g_sheet_name"/>
                    </div>
                    <!--WEB SCRAPING settings-->
                    <div v-if="selectedType.key === 'web_scrap'" :style="{height: 'calc(100% - 55px)'}">
                        <template v-if="webAction === 'html'">
                            <div class="form-group">
                                <div style="display: inline-block;position: relative;top: -4px;">
                                    <div class="html-xml-wi flex flex--center">
                                        <label>Url:&nbsp;</label>
                                        <input class="form-control" v-model="web_html_url" @change="checkChange('html')"/>
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
                                        <label>Element:&nbsp;</label>
                                        <select class="form-control" v-model="web_html_part" @change="webPartChange()">
                                            <option v-for="el in web_elems" :value="el.key">{{ el.val }}</option>
                                        </select>
                                    </div>
                                </div>
                                <button class="btn btn-success small-up"
                                        :disabled="!web_html_url || !web_html_part"
                                        :style="$root.themeButtonStyle"
                                        @click="getScrapWeb()"
                                >Import</button>
                            </div>
                        </template>
                        <template v-if="webAction === 'xml'">
                            <div class="form-group html-xml-wi flex flex--center">
                                <label>Url:&nbsp;</label>
                                <input class="form-control" v-model="web_xml_url" @change="checkChange('xml')"/>
                            </div>
                            <div class="form-group html-xml-wi flex flex--center">
                                <label>XPath Query:&nbsp;</label>
                                <input class="form-control" v-model="web_xml_xpath"/>
                            </div>
                            <div class="form-group html-xml-wi">
                                <button class="btn btn-success small-up"
                                        :disabled="!web_xml_url || !web_xml_xpath"
                                        :style="$root.themeButtonStyle"
                                        @click="getScrapWeb()"
                                >Import</button>
                            </div>
                        </template>
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
        <div class="data-tab data-tab-fixed" v-show="activeTab === 'fields'" :style="$root.themeMainBgStyle">
            <div class="fields-wrapper fields-wrapper-fixed">

                <table class="wrapper-table">
                    <tr>
                        <td :style="{width: selectedType.key !== 'reference' ? '100%' : ref_tb_widths.cur_table.itself.width+'%'}">
                            <table class="table" id="import_main_columns">
                                <thead>
                                <tr v-show="selectedType.key !== 'reference'">
                                    <th style="width: 3%" rowspan="2">#</th>
                                    <th style="width: 25%" rowspan="2">Table Header</th>
                                    <th style="width: 10%" rowspan="2">Formula Symbol</th>
                                    <th style="width: 20%" rowspan="2" v-show="inArray(selectedType.key, ['csv','mysql','paste','g_sheet','web_scrap'])">Source Field</th>
                                    <th style="width: 10%" rowspan="2" v-show="canGetAccess()">Type</th>
                                    <th style="width: 15%" colspan="2" v-show="canGetAccess()">Format</th>
                                    <th style="width: 15%" rowspan="2">Default Value</th>
                                    <th style="width: 8%" rowspan="2">Actions</th>
                                </tr>
                                <tr v-show="selectedType.key !== 'reference'">
                                    <th v-show="canGetAccess()" style="width: 10%">Type</th>
                                    <th v-show="canGetAccess()" style="width: 5%">Decimals</th>
                                </tr>
                                <tr v-show="selectedType.key === 'reference'">
                                    <th colspan="6" class="centered">Current Table</th>
                                </tr>
                                <tr v-show="selectedType.key === 'reference'">
                                    <th style="width: 3%">#</th>
                                    <th :style="{width: ref_tb_widths.cur_table.header.width+'px'}">
                                        <span>Table Header</span>
                                        <header-resizer :table-header="ref_tb_widths.cur_table.header"></header-resizer>
                                    </th>
                                    <th :style="{width: ref_tb_widths.cur_table.symbol.width+'px'}">
                                        <span>Formula Symbol</span>
                                        <header-resizer :table-header="ref_tb_widths.cur_table.symbol"></header-resizer>
                                    </th>
                                    <th :style="{width: ref_tb_widths.cur_table.type.width+'px'}">
                                        <span>Type</span>
                                        <header-resizer :table-header="ref_tb_widths.cur_table.type"></header-resizer>
                                    </th>
                                    <th :style="{width: ref_tb_widths.cur_table.def.width+'px'}">
                                        <span>Default</span>
                                        <header-resizer :table-header="ref_tb_widths.cur_table.def"></header-resizer>
                                    </th>
                                </tr>
                                </thead>

                                <tbody id="import_table_body">
                                <!--Present rows-->
                                <tr v-for="(header, index) in tableHeaders"
                                    v-if="!inArray(header.field, $root.systemFields)
                                        && header.status !== 'del'
                                        && canViewEditCol(header, 'view_fields')"
                                >
                                    <td class="td-indexes" :class="[overIndex === index ? 'td-overed' : '']" :style="$root.themeButtonStyle">
                                        <span
                                            title="Click and drag to change order."
                                            draggable="true"
                                            @dragstart="startChangeOrder(index)"
                                            @dragover.prevent=""
                                            @dragenter="overIndex = index"
                                            @dragend="overIndex = null"
                                            @drop="endChangeOrder(index)"
                                        >{{ index+1 }}</span>
                                    </td>
                                    <td>
                                        <input type="text"
                                               class="form-control"
                                               :disabled="!presentSource() || !canViewEditCol(header, 'edit_fields')"
                                               v-model="header.name"
                                        />
                                    </td>
                                    <td>
                                        <input type="text"
                                               class="form-control"
                                               maxlength="20"
                                               :disabled="!presentSource() || !canViewEditCol(header, 'edit_fields')"
                                               v-model="header.formula_symbol"
                                        />
                                    </td>
                                    <td v-show="inArray(selectedType.key, ['csv','mysql','paste','g_sheet','web_scrap'])">
                                        <select class="form-control"
                                                :disabled="!presentSource() || !canViewEditCol(header, 'edit_fields')"
                                                v-model="header.col"
                                        >
                                            <template v-if="fieldsColumns">
                                                <option></option>
                                                <option v-for="(col, i) in fieldsColumns" :value="i">{{ col }}</option>
                                            </template>
                                            <template v-if="mysqlColumns">
                                                <option></option>
                                                <option v-for="col in mysqlColumns" :value="col">{{ col }}</option>
                                            </template>
                                        </select>
                                    </td>
                                    <td v-show="canGetAccess()">
                                        <select class="form-control" :disabled="!presentSource()" v-model="header.f_type" @change="checkSize(header)">
                                            <option v-for="type in columnTypes">{{ type }}</option>
                                        </select>
                                    </td>
                                    <td v-show="selectedType.key !== 'reference' && canGetAccess()" :colspan="spanFormat(header) ? 2 : 1">
                                        <select class="form-control"
                                                :disabled="!availfFormat(header)"
                                                v-model="header._f_format_l"
                                                @change="chanfFormat(header)"
                                        >
                                            <option value=""></option>
                                            <option v-for="frm in loadFormats(header)" :value="frm">{{ frm }}</option>
                                        </select>
                                    </td>
                                    <td v-show="selectedType.key !== 'reference' && canGetAccess()" v-if="!spanFormat(header)">
                                        <select class="form-control"
                                                v-if="availFormatDecim(header)"
                                                v-model="header._f_format_r"
                                                @change="chanfFormat(header)"
                                        >
                                            <option value="0">0</option>
                                            <option v-for="i in 10" :value="i">{{ i }}</option>
                                        </select>
                                        <select v-else="" class="form-control" disabled="disabled"></select>
                                    </td>
                                    <td
                                        :is="'custom-cell-table-data'"
                                        :global-meta="tableMeta"
                                        :table-meta="tableMeta"
                                        :settings-meta="settingsMeta"
                                        :table-row="header._empty_row"
                                        :table-header="header"
                                        :cell-value="header._empty_row[header.field]"
                                        :cell-height="$root.cellHeight"
                                        :max-cell-rows="$root.maxCellRows"
                                        :behavior="'list_view'"
                                        :with_edit="presentSource() && canViewEditCol(header, 'edit_fields') && !cannotEditDefa(header)"
                                        :user="user"
                                        :style="{backgroundColor: !presentSource() || !canViewEditCol(header, 'edit_fields') || cannotEditDefa(header) ? '#EEE' : null}"
                                        @updated-cell="(row) => { changeDefaultVal(row, header) }"
                                    ></td>
                                    <td class="centered" v-show="!inArray(selectedType.key, ['reference'])">
                                        <button v-if="canGetAccess()"
                                                title="Delete the row."
                                                type="button"
                                                class="btn btn-sm btn-default blue-gradient"
                                                :disabled="!presentSource()"
                                                :style="$root.themeButtonStyle"
                                                style="line-height: 19px;font-size: 2em;padding: 5px;"
                                                @click="delHeader(header)"
                                        >&times;</button>
                                        <button v-if="!inArray(selectedType.key, ['reference'])"
                                                title="Insert a blank row below."
                                                type="button"
                                                class="btn btn-sm btn-default blue-gradient"
                                                :disabled="!presentSource()"
                                                :style="$root.themeButtonStyle"
                                                @click="addHeader(index+1)"
                                        ><i class="fas fa-reply"></i></button>
                                    </td>
                                </tr>
                                <!--Row for adding new headers-->
                                <tr v-if="selectedType.key !== 'reference'">
                                    <td :style="$root.themeButtonStyle">
                                        <span></span>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control" :disabled="!presentSource()" v-model="newHeader.name"/>
                                    </td>
                                    <td>
                                        <input type="text"
                                               class="form-control"
                                               maxlength="20"
                                               :disabled="!presentSource()"
                                               v-model="newHeader.formula_symbol"/>
                                    </td>
                                    <td v-show="inArray(selectedType.key, ['csv','mysql','paste','g_sheet','web_scrap'])">
                                        <select class="form-control" :disabled="!presentSource()" v-model="newHeader.col">
                                            <template v-if="fieldsColumns">
                                                <option></option>
                                                <option v-for="(col, i) in fieldsColumns" :value="i">{{ col }}</option>
                                            </template>
                                            <template v-if="mysqlColumns">
                                                <option></option>
                                                <option v-for="col in mysqlColumns" :value="col">{{ col }}</option>
                                            </template>
                                        </select>
                                    </td>
                                    <td v-show="canGetAccess()">
                                        <select class="form-control" :disabled="!presentSource()" v-model="newHeader.f_type" @change="checkSize(newHeader)">
                                            <option v-for="type in columnTypes">{{ type }}</option>
                                        </select>
                                    </td>
                                    <td v-show="selectedType.key !== 'reference' && canGetAccess()" :colspan="spanFormat(newHeader) ? 2 : 1">
                                        <select class="form-control"
                                                :disabled="!availfFormat(newHeader)"
                                                v-model="newHeader._f_format_l"
                                                @change="chanfFormat(newHeader)"
                                        >
                                            <option value=""></option>
                                            <option v-for="frm in loadFormats(newHeader)" :value="frm">{{ frm }}</option>
                                        </select>
                                    </td>
                                    <td v-show="selectedType.key !== 'reference' && canGetAccess()" v-if="!spanFormat(newHeader)">
                                        <select class="form-control"
                                                v-if="availFormatDecim(newHeader)"
                                                v-model="newHeader._f_format_r"
                                                @change="chanfFormat(newHeader)"
                                        >
                                            <option value="0">0</option>
                                            <option v-for="i in 10" :value="i">{{ i }}</option>
                                        </select>
                                        <select v-else="" class="form-control" disabled="disabled"></select>
                                    </td>
                                    <td
                                        :is="'custom-cell-table-data'"
                                        :global-meta="tableMeta"
                                        :table-meta="tableMeta"
                                        :settings-meta="settingsMeta"
                                        :table-row="newHeader._empty_row"
                                        :table-header="newHeader"
                                        :cell-value="newHeader._empty_row[newHeader.field]"
                                        :cell-height="$root.cellHeight"
                                        :max-cell-rows="$root.maxCellRows"
                                        :behavior="'list_view'"
                                        :with_edit="presentSource() && canViewEditCol(newHeader, 'edit_fields')"
                                        :user="user"
                                        :style="{backgroundColor: !presentSource() || !canViewEditCol(newHeader, 'edit_fields') || cannotEditDefa(newHeader) ? '#EEE' : null}"
                                        @updated-cell="(row) => { changeDefaultVal(row, newHeader) }"
                                    ></td>
                                    <td class="centered" v-show="!inArray(selectedType.key, ['reference'])">
                                        <button type="button"
                                                class="btn btn-info"
                                                :style="$root.themeButtonStyle"
                                                :disabled="!presentSource()"
                                                @click="addHeader()"
                                        >Add</button>
                                    </td>
                                </tr>
                                </tbody>
                            </table>

                            <header-resizer :table-header="ref_tb_widths.cur_table.itself"></header-resizer>
                        </td>

                        <td :style="{width: ref_tb_widths.ref_table.itself.width+'%'}" v-if="selectedType.key === 'reference'">
                            <table class="table">
                                <thead>
                                <tr>
                                    <th colspan="3" class="right-cell">Referencing</th>
                                </tr>
                                <tr>
                                    <th :style="{width: ref_tb_widths.ref_table.id.width+'px'}">
                                        <span>#</span>
                                        <header-resizer :table-header="ref_tb_widths.ref_table.id"></header-resizer>
                                    </th>
                                    <th :style="{width: ref_tb_widths.ref_table.table.width+'px'}">
                                        <span>Table<br>&nbsp;</span>
                                        <header-resizer :table-header="ref_tb_widths.ref_table.table"></header-resizer>
                                    </th>
                                    <th :style="{width: ref_tb_widths.ref_table.actions.width+'px'}">
                                        <span>Actions</span>
                                    </th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr v-for="(reference, i) in this.referencedTables" :style="{backgroundColor: i === selectedReference ? '#FFD' : ''}">
                                    <td>
                                        <a @click="selectedReferenceChange(i)"><i class="fa fa-upload"></i><!-- <b>{{ i+1 }}</b>--></a>
                                    </td>
                                    <td>{{ reference.name }}</td>
                                    <td>
                                        <button type="button"
                                                class="btn btn-sm btn-default blue-gradient"
                                                :style="$root.themeButtonStyle"
                                                @click="refTableDel(i)"
                                        >&times;</button>
                                        |
                                        <button type="button"
                                                class="btn btn-sm btn-default"
                                                :style="$root.themeButtonStyle"
                                                @click="setSelRef(i);selectedReferenceChange(i);importTable()"
                                        >
                                            <span class="fa fa-arrow-right"></span>
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td>
                                        <select-with-folder-structure
                                                :empty_val="true"
                                                :available_tables="settingsMeta.available_tables"
                                                :user="user"
                                                @sel-changed="function(val) {reference_table_select = val;}"
                                                class="form-control">
                                        </select-with-folder-structure>
                                    </td>
                                    <td>
                                        <input type="button"
                                               class="btn btn-primary"
                                               :style="$root.themeButtonStyle"
                                               @click="refTableAdd()"
                                               value="Add">
                                    </td>
                                </tr>
                                </tbody>
                            </table>

                            <header-resizer :table-header="ref_tb_widths.ref_table.itself"></header-resizer>
                        </td>

                        <td :style="{width: ref_tb_widths.ref_field.itself.width+'%'}" v-if="selectedType.key === 'reference'">
                            <table class="table">
                                <thead>
                                <tr>
                                    <th colspan="3" class="left-cell">Table</th>
                                </tr>
                                <tr>
                                    <th :style="{width: ref_tb_widths.ref_field.field.width+'px'}">
                                        <span>Field<br>&nbsp;</span>
                                        <header-resizer :table-header="ref_tb_widths.ref_field.field"></header-resizer>
                                    </th>
                                    <th :style="{width: ref_tb_widths.ref_field.type.width+'px'}">
                                        <span>Type</span>
                                        <header-resizer :table-header="ref_tb_widths.ref_field.type"></header-resizer>
                                    </th>
                                </tr>
                                </thead>
                                <tbody>
                                <template v-if="selectedReference > -1 && referencedTables[selectedReference]._fields">
                                    <tr v-for="obj in referencedTables[selectedReference].objects">
                                        <td>
                                            <select class="form-control" v-model="obj.ref_field_id" @change="changedRefField(obj)">
                                                <option></option>
                                                <option v-for="r_field in referencedTables[selectedReference]._fields" :value="r_field.id">{{ r_field.name }}</option>
                                            </select>
                                        </td>
                                        <td><input class="form-control" disabled type="text" :value="obj.f_type"></td>
                                    </tr>
                                </template>
                                </tbody>
                            </table>

                            <header-resizer :table-header="ref_tb_widths.ref_field.itself"></header-resizer>
                        </td>
                    </tr>
                </table>

            </div>
        </div>


        <!--Tab with storage and backup settings-->
        <div class="data-tab" v-if="canGetAccess()" v-show="activeTab === 'storage-backup'" :style="$root.themeMainBgStyle">
            <div class="fields-wrapper full-height">
                <tab-storage-backup
                    :table-meta="tableMeta"
                    :settings-meta="settingsMeta"
                    :user="user"
                    :cell-height="$root.cellHeight"
                    :max-cell-rows="$root.maxCellRows"
                ></tab-storage-backup>
            </div>
        </div>


        <!--Popup with transfer data progress bar-->
        <div v-if="import_progress_id" class="import-progressbar-wrapper">
            <div class="import-progressbar">
                <h1>Import Data To Table</h1>
                <div class="progressbar-wrapper">
                    <div ref="transfer_progressbar"></div>
                </div>
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
    import {SpecialFuncs} from './../../../../classes/SpecialFuncs';

    import {eventBus} from './../../../../app';

    import PasteAutowrapperMixin from './../../../_Mixins/PasteAutowrapperMixin.vue';
    import LinkEmptyObjectMixin from './../../../_Mixins/LinkEmptyObjectMixin.vue';
    import CheckRowBackendMixin from './../../../_Mixins/CheckRowBackendMixin.vue';

    import CustomTable from './../../../CustomTable/CustomTable';
    import HeaderResizer from '../../../CustomTable/Header/HeaderResizer';
    import TabStorageBackup from './TabStorageBackup';
    import SelectWithFolderStructure from '../../../CustomCell/InCell/SelectWithFolderStructure';
    import TabDataDefaultCol from "./TabDataDefaultCol";
    import InfoSignLink from "../../../CustomTable/Specials/InfoSignLink";
    import CustomCellTableData from "../../../CustomCell/CustomCellTableData";

    export default {
        name: 'TabData',
        components: {
            CustomCellTableData,
            InfoSignLink,
            TabDataDefaultCol,
            CustomTable,
            HeaderResizer,
            TabStorageBackup,
            SelectWithFolderStructure,
        },
        mixins: [
            PasteAutowrapperMixin,
            LinkEmptyObjectMixin,
            CheckRowBackendMixin,
        ],
        data: function () {
            return {
                add_err_msg: {
                    show: false,
                    header: 'Info',
                    body: '',
                    btn: 'OK',
                },
                empty_row: SpecialFuncs.emptyRow(this.tableMeta),

                draggedIndex: null,
                overIndex: null,

                reference_table_select: null,
                selectedReference: -1,
                referencedTables: null,
                referenceObjects: [],

                import_progress_id: null,
                activeTab: 'method',
                importTypes: [
                    {
                        key: 'scratch',
                        name: 'Build/Update',
                        has_action: false,
                        notes: 'To build a new data table from scratch by adding fields or update the fields for an existing data/table.'
                    },
                    {
                        key: 'csv',
                        name: 'CSV Import',
                        has_action: true,
                        notes: 'Import data from a CSV file with data table fields given or not. If data/table fields not given in the CSV file or not checked to use in the , user can define those in the field settings tab. Any existing data/table info will be completely deleted.'
                    },
                    {
                        key: 'mysql',
                        name: 'MySQL Import',
                        has_action: true,
                        notes: 'Import data of selected fields of a MySQL data table from local or remote server OR uploading a mysql file. The same table fields and data format will be used. User to define table head names. User can add or remove field(s) for importing.'
                    },
                    {
                        key: 'remote',
                        name: 'Remote MySQL',
                        has_action: false,
                        notes: 'To retrieve data from a MySQL table from a local or remote server. No data table will be created (copied) to local. Only management data will be created.'
                    },
                    {
                        key: 'reference',
                        name: 'Referencing',
                        has_action: false,
                        notes: 'To glue the data of selected fields from multiple existing data tables, public or private, through the defined field correspondences between current data table and a selected source data table. Glue means putting the data records of one data table after another into current data table. The data records for a given source data table can be updated by deleting existing referencing and re-importing (add referencing record and then import).'
                    },
                    {
                        key: 'paste',
                        name: 'Paste to Import',
                        has_action: true,
                        notes: ''
                    },
                    {
                        key: 'web_scrap',
                        name: 'Web Scraping',
                        has_action: true,
                        notes: ''
                    },
                    /*{
                        key: 'g_sheet',
                        name: 'Google Sheet',
                        has_action: true,
                        notes: ''
                    },*/
                ],
                availableCodes: {
                    scratch: 'data_build',
                    csv: 'data_csv',
                    mysql: 'data_mysql',
                    remote: 'data_remote',
                    reference: 'data_ref',
                    paste: 'data_paste',
                    g_sheet: 'data_g_sheet',
                    web_scrap: 'data_web_scrap',
                },

                csv_link: '',
                csvSettings: {
                    filename: '',
                    firstHeader: true,
                    secondType: false,
                    thirdSize: false,
                    fourthDefault: false,
                    fifthRequired: false,
                    replace: true,
                    quote: true,
                    apostrophe: true,
                    backslash:true,
                    startRow: null,
                    endRow: null
                },
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

                g_sheet_settings: {
                    f_header: true
                },
                g_sheet_link: '',
                g_sheet_name: 'Sheet1',

                web_elems: [],
                web_html_part: '',
                web_html_url: '',
                web_html_query: '',
                web_html_index: '',
                web_html_xpath: '',
                web_xml_url: '',
                web_xml_xpath: '',

                disabledKeys: false,
                webAction: '',
                importAction: '',
                typeKey: '',
                tableHeaders: this.copyFrom(this.tableMeta._fields),
                newHeader: this.emptyHeader(),
                columnTypes: [
                    'User',
                    'String',
                    'Text',
                    'Long Text',
                    'Integer',
                    'Decimal',
                    'Currency',
                    'Percentage',
                    'Date',
                    'Time',
                    'Duration',
                    'Date Time',
                    'Auto Number',
                    'Attachment',
                    'Address',
                    'Star Rating',
                    'Progress Bar',
                    'Boolean',
                    'Color',
                    'Vote',
                    //'Formula',

                ],
                fieldsWithoutSize: [
                    'Date',
                    'Date Time',
                    'Time',
                    'Duration',
                    'Auto Number',
                    'Attachment',
                    'Address',
                    'Star Rating',
                    'Boolean',
                    'Color',
                    'Vote',
                    'User',
                    'Text',
                    'Long Text',
                ],
                remote_tables: [],
                remote_dbs: [],
                selected_connection: -1,
                ref_tb_widths: {
                    cur_table: {
                        header: {width: 25},
                        symbol: {width: 10},
                        type: {width: 25},
                        format: {width: 25},
                        size: {width: 25},
                        def: {width: 25},
                        formula: {width: 25},
                        itself: {width: 45}
                    },
                    ref_table: {
                        id: {width: 11},
                        table: {width: 72},
                        actions: {width: 17},
                        itself: {width: 20}
                    },
                    ref_field: {
                        field: {width: 33},
                        type: {width: 33},
                        size: {width: 33},
                        itself: {width: 35}
                    }
                },
            };
        },
        computed: {
            allRequire() {
                return _.findIndex(this.tableHeaders, function (el) {
                        return !el.required;
                    }) === -1;
            },
            presentHeadersCols() {
                return this.tableMeta._fields.length - this.$root.systemFields.length;
            },
            selectedType() {
                return this.typeKey !== '' ? _.find(this.importTypes, {key: this.typeKey}) : {};
            }
        },
        props:{
            user: Object,
            tableMeta: Object,
            settingsMeta: Object,
            cellHeight: Number,
            maxCellRows: Number,
            table_id: Number,
        },
        watch: {
            table_id(val) {
                this.initSelects();
            }
        },
        methods: {
            spanFormat(header) {
                return ['Duration','Date','Boolean','Vote'].indexOf(header.f_type) > -1;
            },
            availfFormat(header) {
                return this.inArray(header.f_type,
                    ['Decimal','Currency','Percentage','Progress Bar','Date','Duration','Boolean','Vote']);
            },
            availFormatDecim(header) {
                return this.inArray(header.f_type, ['Decimal','Currency','Percentage','Progress Bar']);
            },
            cannotEditDefa(header) {
                return header.input_type === 'Formula'
                    || ['Attachment','Auto Number'].indexOf(header.f_type) > -1;
            },
            changeDefaultVal(row, header) {
                header.f_default = row[header.field];
                this.checkRowOnBackend(this.table_id, header._empty_row);
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
                this.importAction = this.canGetAccess('not_only_append') ? 'New' : 'Append';
                this.webAction = 'html';
            },
            canViewEditCol(col, fields_type) {
                return this.tableMeta._is_owner
                    ||
                    (this.tableMeta._current_right && this.inArray(col.field, this.tableMeta._current_right[fields_type]))
                    ||
                    col.status === 'add';
            },
            canGetAccess(code) {
                //if no 'code' provided -> check only '_is_owner'
                let can = !!this.tableMeta._is_owner;
                if (code && !can) {
                    let idx_code = ['scratch','csv','mysql','remote','reference','paste','g_sheet','web_scrap'].indexOf(code);
                    if (idx_code > -1) {
                        can = this.tableMeta._current_right && this.tableMeta._current_right.datatab_methods.charAt(idx_code) === '1';
                    }
                    if (code === 'not_only_append') {
                        can = this.tableMeta._current_right && !this.tableMeta._current_right.datatab_only_append;
                    }
                }
                return can;
            },
            //change table notes
            tableNoteChanged(table) {
                axios.put('/ajax/table', {
                    table_id: this.settingsMeta[table].id,
                    notes: this.settingsMeta[table].notes
                }).then(({ data }) => {
                }).catch(errors => {
                    Swal('', getErrors(errors));
                });
            },

            canSave(typeKey) {
                return this.$root.checkAvailable(this.user, this.availableCodes[typeKey]);
            },
            inArray(item, array) {
                return $.inArray(item, array) > -1;
            },
            copyFrom(Headers, forceStat) {
                if (!this.empty_row) {
                    this.empty_row = SpecialFuncs.emptyRow(this.tableMeta);
                }
                let result = [];
                for (let i in Headers) {
                    if (!this.inArray(Headers[i].field, this.$root.systemFields)) {
                        let ffrmat = String(Headers[i].f_format || '').split('-');
                        let erow = _.clone(this.empty_row);
                        erow[Headers[i].field] = Headers[i].f_default;
                        erow['_is_def_cell'] = true;
                        let tmp = _.clone(Headers[i]);
                        tmp.status = forceStat || 'edit';
                        tmp.col = null;
                        tmp.table_id = this.tableMeta.id;
                        tmp._empty_row = erow;
                        tmp._f_format_l = ffrmat[0] || '';
                        tmp._f_format_r = ffrmat[1] || 2;
                        result.push(tmp);
                        if (Headers[i].f_type === 'User') {
                            this.checkRowOnBackend(this.table_id, tmp._empty_row);
                        }
                    }
                }
                return result;
            },
            chanfFormat(header) {
                if (['Decimal','Currency','Percentage','Progress Bar'].indexOf(header.f_type) > -1) {
                    header.f_format = header._f_format_l + '-' + header._f_format_r;
                } else {
                    header.f_format = header._f_format_l;
                }
            },
            emptyHeader() {
                if (!this.empty_row) {
                    this.empty_row = SpecialFuncs.emptyRow(this.tableMeta);
                }
                let erow = _.clone(this.empty_row);
                erow[''] = '';
                return {
                    status: 'add',
                    table_id: this.tableMeta.id,
                    id: '',
                    name: '',
                    formula_symbol: '',
                    field: '',
                    col: null,
                    f_type: 'String',
                    f_size: 64,
                    f_default: '',
                    f_required: 0,
                    f_format: '',
                    input_type: 'Input',
                    _empty_row: erow,
                    _f_format_l: '',
                    _f_format_r: 2,
                };
            },
            addHeader(index) {
                index = index || this.tableHeaders.length;
                this.tableHeaders.splice(index, 0, Object.assign({}, this.newHeader) );
                this.newHeader = Object.assign({}, this.emptyHeader());
                this.referencedTables = this.getRefTables();
            },
            delHeader(header) {
                header.status = 'del';
                this.referencedTables = this.getRefTables();
            },
            changeTab() {
                if (this.inArray(this.selectedType.key, ['scratch', 'reference'])) {
                    this.activeTab = 'fields';
                }
            },
            presentSource() {
                let res = true;
                if (this.inArray(this.selectedType.key, ['csv', 'mysql', 'remote', 'paste'])) {
                    res = (this.csvSettings.filename && this.inArray(this.selectedType.key, ['csv']))
                        ||
                        (this.mysqlColumns.length && this.inArray(this.selectedType.key, ['mysql', 'remote']))
                        ||
                        (this.paste_file && this.inArray(this.selectedType.key, ['paste']))
                        ||
                        (this.g_sheet_link && this.inArray(this.selectedType.key, ['g_sheet']))
                        ||
                        ((this.web_html_url || this.web_xml_url) && this.inArray(this.selectedType.key, ['web_scrap']));
                }
                return res && this.activeTab === 'fields';
            },
            toggleAll() {
                let status = !this.allRequire;
                _.each(this.tableHeaders, function(el) {
                    el.required = status;
                });
            },
            getFieldsFromCSV() {
                let data = new FormData();
                let file = this.$refs.csv_upload.files[0];
                data.append('csv', file);
                data.append('csv_link', this.csv_link);
                data.append('csv_settings', JSON.stringify(this.csvSettings));

                if (file || this.csv_link) {
                    this.$root.sm_msg_type = 2;
                    axios.post('/ajax/import/get-fields/csv', data, {
                        headers: {
                            'Content-Type': 'multipart/form-data'
                        }
                    }).then(({ data }) => {
                        if (this.inArray(this.importAction, ['New'])) {
                            this.tableHeaders = this.copyFrom(data.headers, 'add');
                        }
                        _.each(data.csv_fields, (elem, i) => {
                            if (this.tableHeaders[i] && this.canViewEditCol(this.tableHeaders[i], 'edit_fields')) {
                                this.tableHeaders[i].col = i;
                            }
                        });

                        this.fieldsColumns = data.csv_fields;
                        this.csvSettings.filename = data.csv_file;
                        this.activeTab = 'fields';
                    }).catch(errors => {
                        Swal('', getErrors(errors));
                    }).finally(() => {
                        this.$root.sm_msg_type = 0;
                    });
                } else {
                    Swal('No file', '', 'info');
                }
            },
            getFieldsFromPaste() {
                if (this.paste_data) {
                    this.pasteFieldsFromBackend().then((data) => {
                        if (in_array(this.importAction, ['New'])) {
                            this.tableHeaders = this.copyFrom(data.headers, 'add');
                        }
                        _.each(data.fields, (elem, i) => {
                            if (this.tableHeaders[i] && this.canViewEditCol(this.tableHeaders[i], 'edit_fields')) {
                                this.tableHeaders[i].col = i;
                            }
                        });

                        this.fieldsColumns = data.fields;
                        this.activeTab = 'fields';
                    });
                } else {
                    Swal('No pasted data', '', 'info');
                }
            },
            getFieldsFromGSheet() {
                if (this.g_sheet_link) {
                    this.$root.sm_msg_type = 2;
                    axios.post('/ajax/import/get-fields/g-sheet', {
                        g_sheet_settings: this.g_sheet_settings,
                        g_sheet_link: this.g_sheet_link,
                        g_sheet_name: this.g_sheet_name || 'Sheet1',
                    }).then(({ data }) => {
                        if (this.inArray(this.importAction, ['New'])) {
                            this.tableHeaders = this.copyFrom(data.headers, 'add');
                        }
                        _.each(data.fields, (elem, i) => {
                            if (this.tableHeaders[i] && this.canViewEditCol(this.tableHeaders[i], 'edit_fields')) {
                                this.tableHeaders[i].col = i;
                            }
                        });
                        this.fieldsColumns = data.fields;
                        this.activeTab = 'fields';
                    }).catch(errors => {
                        Swal('', getErrors(errors));
                    }).finally(() => {
                        this.$root.sm_msg_type = 0;
                    });
                } else {
                    Swal('Google Sheet Link is empty!', '', 'info');
                }
            },
            getFieldsFromMySQL() {
                if (this.mysqlSettings.host) {
                    this.$root.sm_msg_type = 2;
                    axios.post('/ajax/import/get-fields/mysql', {
                        mysql_settings: this.mysqlSettings
                    }).then(({ data }) => {
                        if (this.inArray(this.importAction, ['New'])) {
                            this.tableHeaders = this.copyFrom(data.headers, 'add');
                        }
                        let vue = this;
                        _.each(data.mysql_fields, function (elem, i) {
                            vue.tableHeaders[i].col = elem;
                        });
                        this.mysqlColumns = data.mysql_fields;
                        this.mysqlSettings.connection_id = data.connection_id;
                        this.activeTab = 'fields';
                    }).catch(errors => {
                        Swal('', getErrors(errors));
                    }).finally(() => {
                        this.$root.sm_msg_type = 0;
                    });
                } else {
                    Swal('No host', '', 'info');
                }
            },
            importTable() {
                if (this.inArray(this.selectedType.key, ['csv','mysql','remote','paste','g_sheet']) && this.importAction === 'New') {
                    Swal({
                        title: "Import Data",
                        text: "Choosing “New” for importing will erase all existing data and settings for current table. Confirm to proceed.",
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
                $.LoadingOverlay('show');
                let gsheet = {
                    settings: this.g_sheet_settings,
                    link: this.g_sheet_link,
                    name: this.g_sheet_name,
                };
                let htmlxml = {
                    url: this.web_html_url,
                    query: this.web_html_query,
                    index: this.web_html_index,
                    xpath: this.web_html_xpath,
                    xml_url: this.web_xml_url,
                    xml_xpath: this.web_xml_xpath,
                };
                axios.post('/ajax/import/modify-table', {
                    table_id: this.tableMeta.id,
                    columns: this.tableHeaders,
                    present_cols_idx: this.presentHeadersCols,
                    import_type: this.selectedType.key,
                    import_action: this.importAction,
                    csv_settings: this.csvSettings,
                    mysql_settings: this.mysqlSettings,
                    referenced_table: this.selectedReference > -1 ? this.referencedTables[this.selectedReference] : [],
                    paste_settings: this.paste_settings,
                    paste_file: this.paste_file,
                    html_xml: htmlxml,
                    g_sheet: gsheet,

                }).then(({ data }) => {
                    if (data.job_id) {
                        this.import_progress_id = data.job_id;
                    } else {
                        window.location.reload();
                    }
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
            selectedConnection(elem) {
                let conn = this.settingsMeta.user_connections_data[ this.selected_connection ];
                this.mysqlSettings.connection_id = conn ? conn.id : '';
                this.mysqlSettings.name = conn ? conn.name : '';
                this.mysqlSettings.host = conn ? conn.host : '';
                this.mysqlSettings.login = conn ? conn.login : '';
                this.mysqlSettings.pass = conn ? conn.pass : '';
                this.mysqlSettings.db = '';
                this.mysqlSettings.table = '';

                this.remote_dbs = [];
                this.remote_tables = [];
            },
            getDBS() {
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
                        Swal('', getErrors(errors));
                    }).finally(() => {
                        this.$root.sm_msg_type = 0;
                    });
                } else {
                    Swal('Select the connection', '', 'error');
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
                        Swal('', getErrors(errors));
                    }).finally(() => {
                        this.$root.sm_msg_type = 0;
                    });
                }
            },

            //WEB SCRAPING
            checkChange(type) {
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
            },
            loadPageElems() {
                if ((this.web_html_url && this.webAction === 'html') || (this.web_xml_url && this.webAction === 'xml')) {
                    this.$root.sm_msg_type = 2;
                    axios.post('/ajax/import/get-fields/web-scrap', {
                        web_action: 'preload',
                        web_url: this.webAction === 'html' ? this.web_html_url : this.web_xml_url,
                    }).then(({ data }) => {
                        this.web_elems = data.elems;
                        this.web_html_part = _.first(data.elems).key;
                        this.webPartChange();
                    }).catch(errors => {
                        Swal('', getErrors(errors));
                    }).finally(() => {
                        this.$root.sm_msg_type = 0;
                    });
                } else {
                    Swal('Url is empty!', '', 'info');
                }
            },
            webPartChange() {
                let arr = String(this.web_html_part).split('_');
                this.web_html_query = _.trim(arr[0]);
                this.web_html_index = _.trim(arr[1]);
                this.web_html_xpath = '';
            },
            webXpathChang() {
                this.web_html_part = '';
                this.web_html_query = '';
                this.web_html_index = '';
            },
            getScrapWeb() {
                if ((this.web_html_url && this.webAction === 'html') || (this.web_xml_url && this.webAction === 'xml')) {
                    this.$root.sm_msg_type = 2;
                    axios.post('/ajax/import/get-fields/web-scrap', {
                        web_action: this.webAction,
                        web_url: this.webAction === 'html' ? this.web_html_url : this.web_xml_url,
                        web_query: this.web_html_query,
                        web_xpath: this.webAction === 'html' ? this.web_html_xpath : this.web_xml_xpath,
                        web_index: this.web_html_index,
                    }).then(({ data }) => {
                        if (!data.fields || !data.fields.length) {
                            this.add_err_msg.show = true;
                            this.add_err_msg.body = 'Element(s) not found.';
                            return;
                        }
                        _.each(data.fields, (elem, i) => {
                            if (this.tableHeaders[i] && this.canViewEditCol(this.tableHeaders[i], 'edit_fields')) {
                                this.tableHeaders[i].col = i;
                            }
                        });
                        this.fieldsColumns = data.fields;
                        this.activeTab = 'fields';
                    }).catch(errors => {
                        Swal('', getErrors(errors));
                    }).finally(() => {
                        this.$root.sm_msg_type = 0;
                    });
                } else {
                    Swal('Url is empty!', '', 'info');
                }
            },

            //methods for 'reference' import
            getRefTables() {
                let ref = [];
                for (let i in this.tableMeta._table_references) {
                    let elem = this.tableMeta._table_references[i];
                    if ( ! _.find(ref, {id: elem.ref_table_id}) ) {
                        ref.push({
                            id: elem.ref_table_id,
                            name: elem._ref_table.name,
                            fields: Object.assign({}, elem._ref_table._fields),
                            objects: this.getRefFields(elem.ref_table_id),
                            only_del: false
                        });
                    }
                }
                return ref;
            },
            getRefFields(ref_tb_id) {
                //build reference object to fill referencing params
                let refArr = [];
                let that = this;
                _.each(this.tableMeta._fields, function (hdr) {
                    if (!that.inArray(hdr.field, that.$root.systemFields)) {
                        let i = _.findIndex(that.tableMeta._table_references, {table_field_id: hdr.id, ref_table_id: ref_tb_id});
                        let tableRef = that.tableMeta._table_references[i];

                        refArr.push({
                            'table_id': hdr.table_id,
                            'table_field_id': hdr.id,
                            'table_field_db': hdr.field,
                            'ref_table_id': ref_tb_id,
                            'ref_field_id': tableRef ? tableRef.ref_field_id : null,
                            'ref_field_db': tableRef ? tableRef._ref_field.field : '',
                            'f_type': tableRef ? tableRef._ref_field.f_type : '',
                            'f_size': tableRef ? tableRef._ref_field.f_size : '',
                        });
                    }
                });
                return refArr;
            },
            refTableAdd() {
                let id = Number(this.reference_table_select);
                let sel_table = _.find(this.$root.settingsMeta.available_tables, {id: id});
                if (sel_table) {
                    this.$root.sm_msg_type = 1;
                    axios.post('/ajax/table-data/get-headers', {
                        table_id: sel_table.id,
                        user_id: this.$root.user.id,
                    }).then(({ data }) => {
                        this.referencedTables.push({
                            id: id,
                            name: sel_table ? sel_table.name : '',
                            fields: Object.assign({}, data._fields),
                            objects: this.getRefFields(id),
                            only_del: false
                        });
                        this.selectedReferenceChange(this.referencedTables.length-1);
                        this.reference_table_select = null;
                    }).catch(errors => {
                        Swal('', getErrors(errors));
                    }).finally(() => {
                        this.$root.sm_msg_type = 0;
                    });
                }
            },
            refTableDel(index) {
                Swal({
                    title: "Delete Data",
                    text: "Are you sure to remove all data referenced to selected table?",
                    confirmButtonClass: "btn-danger",
                    confirmButtonText: "Yes",
                    showCancelButton: true,
                    closeOnConfirm: true,
                    animation: "slide-from-top"
                }).then(resp => {
                    if (resp.value) {
                        this.selectedReference = index;
                        this.referencedTables[index].only_del = true;

                        this.sendModifyRequest();

                        let ref_tb_id = this.referencedTables[index].id;
                        this.selectedReference = -1;
                        this.referencedTables.splice(index, 1);

                        let references = [];
                        _.each(this.tableMeta._table_references, function (elem) {
                            if (elem.ref_table_id === ref_tb_id) {
                                references.push(elem);
                            }
                        });
                        this.tableMeta._table_references = references;
                    }
                });
            },
            changedRefField(obj) {
                let idx = _.findIndex(this.referencedTables[this.selectedReference]._fields, {id: obj.ref_field_id});
                let ref = this.referencedTables[this.selectedReference]._fields[idx];
                obj.ref_field_db = ref ? ref.field : '';
                obj.f_type = ref ? ref.f_type : '';
                obj.f_size = ref ? ref.f_size : '';
            },
            setSelRef(i) {
                if (i !== this.selectedReference) {
                    this.selectedReference = i;
                }
            },
            selectedReferenceChange(val) {
                //get reference fields for selected table
                if (!this.referencedTables[val]._fields) {
                    this.$root.sm_msg_type = 2;
                    axios.get('/ajax/settings/table-fields', {
                        params: {
                            table_id: this.referencedTables[val].id
                        }
                    }).then(({ data }) => {
                        this.referencedTables[val]._fields = data;
                        this.selectedReference = val;
                    }).catch(errors => {
                        Swal('', getErrors(errors));
                    }).finally(() => {
                        this.$root.sm_msg_type = 0;
                    });
                } else {
                    this.selectedReference = val;
                }
            },
            recopyFieldHandler() {
                this.$nextTick(function () {
                    this.tableHeaders = this.copyFrom(this.tableMeta._fields);
                    this.referencedTables = this.getRefTables();
                    this.typeKey = this.tableMeta.source;
                    this.actionKey = 0;
                });
            },
            checkSize(header) {
                switch (header.f_type) {
                    case 'Progress Bar':
                        header.f_size = '2.4';
                        break;
                    case 'Text':
                    case 'Vote':
                        header.f_size = '65535';
                        break;
                    case 'Long Text':
                        header.f_size = '4294967295';
                        break;
                    case 'Integer':
                    case 'Auto Number':
                    case 'Star Rating':
                    case 'Boolean':
                    case 'Color':
                    case 'Duration':
                    //case 'Formula':
                        header.f_size = '10';
                        break;
                    case 'Decimal':
                    case 'Currency':
                    case 'Percentage':
                        header.f_size = '8.2';
                        break;
                    case 'Date':
                    case 'Date Time':
                    case 'Time':
                        header.f_size = '16';
                        break;
                    default: header.f_size = '64';
                }
            },
            loadFormats(header) {
                switch (header.f_type) {
                    case 'Decimal':
                    case 'Currency':
                    case 'Percentage':
                    case 'Progress Bar':
                        return ['Float','Comma','Percent','Exp'];
                    case 'Date':
                        return ['mm-dd-yyyy','m-d-yyy','yyyy-mm-dd','yyy-m-d','dd-mm-yyyy','d-m-yyy','Month D, Yr','Mon. D, Yr'];
                    case 'Duration':
                        return ['s', 'm, s', 'h, m, s', 'd, h, m, s', 'wk, d, h, m, s'];
                    case 'Boolean':
                        return ['Checkbox','Slider'];
                    case 'Vote':
                        return ['Yes/No','Like/Dislike'];
                    default: return [];
                }
            },
            //change order of the rows
            startChangeOrder(index) {
                this.draggedIndex = index;
            },
            endChangeOrder(index) {
                if (this.draggedIndex) {
                    index = this.draggedIndex < index ? index-1 : index;
                    let hdr = this.tableHeaders.splice( this.draggedIndex, 1 );
                    this.tableHeaders.splice( index, 0, hdr[0] );
                    this.draggedIndex = null;
                }
            },
        },
        mounted() {
            this.referencedTables = this.getRefTables();
            this.initSelects();

            eventBus.$on('recopy-fields', this.recopyFieldHandler);

            setInterval(() => {
                if (this.import_progress_id) {
                    axios.get('/ajax/import/status', {
                        params: {
                            import_id: this.import_progress_id
                        }
                    }).then(({ data }) => {
                        $(this.$refs.transfer_progressbar).css('width', data.complete+'%');
                        if (data.status === 'done') {
                            window.location.reload();
                            this.import_progress_id = false;
                        }
                    });
                }
            }, 1000);
        },
        beforeDestroy() {
            eventBus.$off('recopy-fields', this.recopyFieldHandler);
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
        width: 500px;
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
</style>