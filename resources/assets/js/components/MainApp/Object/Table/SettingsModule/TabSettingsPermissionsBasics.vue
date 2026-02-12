<template>
    <div :style="textSysStyle">
        <table class="table">
            <colgroup>
                <col :width="cw['1']">
                <col :width="cw['5']">
                <col :width="cw['1']">
                <col :width="cw['5']">
            </colgroup>
            <thead>
            <tr>
                <th colspan="4" @click.self="otherTablesBody.basic = !otherTablesBody.basic">
                    <span @click="otherTablesBody.basic = !otherTablesBody.basic">Basics</span>
                    <label>
                        <span class="indeterm_check__wrap">
                            <span class="indeterm_check" @click="toggleAllBasics()">
                                <i v-if="allBasicsChecked == 2" class="glyphicon glyphicon-ok group__icon"></i>
                                <i v-if="allBasicsChecked == 1" class="glyphicon glyphicon-minus group__icon"></i>
                            </span>
                        </span>
                        <span> All</span>
                    </label>
                </th>
            </tr>
            </thead>
            <tbody v-show="otherTablesBody.basic">
            <tr>
                <td class="in-tb-check">
                    <span class="indeterm_check__wrap">
                        <span class="indeterm_check"
                              :class="{'disabled': !$root.checkAvailable(user, 'permission_row_add')}"
                              @click="!$root.checkAvailable(user, 'permission_row_add')
                                    ? null
                                    : updateGroup(selPermission,'can_add')"
                        >
                            <i v-if="selPermission.can_add" class="glyphicon glyphicon-ok group__icon"></i>
                        </span>
                    </span>
                </td>
                <td><span>Add New Record</span></td>
                <td class="in-tb-check">
                    <span class="indeterm_check__wrap">
                        <span class="indeterm_check" @click="updateGroup(selPermission,'can_see_history')">
                            <i v-if="selPermission.can_see_history" class="glyphicon glyphicon-ok group__icon"></i>
                        </span>
                    </span>
                </td>
                <td><span>Data Histories</span></td>
            </tr>
            <tr>
                <td class="in-tb-check">
                    <span class="indeterm_check__wrap">
                        <span class="indeterm_check" @click="updateGroup(selPermission,'can_reference')">
                            <i v-if="selPermission.can_reference" class="glyphicon glyphicon-ok group__icon"></i>
                        </span>
                    </span>
                </td>
                <td><span>Referencing for Sharing</span></td>
                <td class="in-tb-check">
                    <span class="indeterm_check__wrap">
                        <span class="indeterm_check" @click="updateGroup(selPermission,'hide_folder_structure')">
                            <i v-if="selPermission.hide_folder_structure" class="glyphicon glyphicon-ok group__icon"></i>
                        </span>
                    </span>
                </td>
                <td><span>Hide Directory</span></td>
            </tr>
            <tr>
                <td class="in-tb-check">
                    <span class="indeterm_check__wrap">
                        <span class="indeterm_check" @click="updateGroup(selPermission,'enforced_theme')">
                            <i v-if="selPermission.enforced_theme" class="glyphicon glyphicon-ok group__icon"></i>
                        </span>
                    </span>
                </td>
                <td><span>Enforced Theme and/or Color Settings</span></td>
                <td class="in-tb-check">
                    <span class="indeterm_check__wrap">
                        <span class="indeterm_check" @click="updateGroup(selPermission,'can_edit_tb')">
                            <i v-if="selPermission.can_edit_tb" class="glyphicon glyphicon-ok group__icon"></i>
                        </span>
                    </span>
                </td>
                <td><span>Initial Loading Settings</span></td>
            </tr>
            <tr>
                <td class="in-tb-check">
                    <span class="indeterm_check__wrap">
                        <span class="indeterm_check" @click="updateGroup(selPermission,'can_drag_rows')">
                            <i v-if="selPermission.can_drag_rows" class="glyphicon glyphicon-ok group__icon"></i>
                        </span>
                    </span>
                </td>
                <td><span>Drag # to Change Row Order</span></td>
                <td class="in-tb-check">
                    <span class="indeterm_check__wrap">
                        <span class="indeterm_check" @click="updateGroup(selPermission,'can_drag_columns')">
                            <i v-if="selPermission.can_drag_columns" class="glyphicon glyphicon-ok group__icon"></i>
                        </span>
                    </span>
                </td>
                <td><span>Drag to Change Column Order</span></td>
            </tr>
            <tr>
                <td class="in-tb-check">
                    <span class="indeterm_check__wrap">
                        <span class="indeterm_check" @click="updateGroup(selPermission,'can_public_copy')">
                            <i v-if="selPermission.can_public_copy" class="glyphicon glyphicon-ok group__icon"></i>
                        </span>
                    </span>
                </td>
                <td><span>Allow Copy for Public Sharing</span></td>
                <td class="in-tb-check">
                    <span class="indeterm_check__wrap">
                        <span class="indeterm_check" @click="updateGroup(selPermission,'can_change_primaryview')">
                            <i v-if="selPermission.can_change_primaryview" class="glyphicon glyphicon-ok group__icon"></i>
                        </span>
                    </span>
                </td>
                <td><span>Change Primary View</span></td>
            </tr>
            </tbody>
        </table>

        <!----------- Settings Basics Tab -------------->

        <table class="table">
            <colgroup>
                <col :width="cw['1']">
                <col :width="cw['5']">
                <col :width="cw['1']">
                <col :width="cw['5']">
            </colgroup>
            <thead>
            <tr>
                <th colspan="4" @click.self="otherTablesBody.columns = !otherTablesBody.columns">
                    <a @click.stop="showSettingsCustPopup()">Availability: Settings/Basics/Customizable</a>
                    <label>
                        <span class="indeterm_check__wrap">
                            <span class="indeterm_check" @click="toggleAllColumnsBasics('all')">
                                <i v-if="allBasicColumnsChecked == 2" class="glyphicon glyphicon-ok group__icon"></i>
                                <i v-if="allBasicColumnsChecked == 1" class="glyphicon glyphicon-minus group__icon"></i>
                            </span>
                        </span>
                        <span> All</span>
                    </label>
                </th>
            </tr>
            </thead>
            <tbody v-show="otherTablesBody.columns">
            <tr v-for="i in max_avails">
                <td class="in-tb-check">
                    <span v-if="availability_cols_first[i-1]" class="indeterm_check__wrap">
                        <span class="indeterm_check" @click="colForbidToggled(availability_cols_first[i-1])">
                            <i v-if="!forbidColPresent(availability_cols_first[i-1])" class="glyphicon glyphicon-ok group__icon"></i>
                        </span>
                    </span>
                </td>
                <td>
                    <span v-if="availability_cols_first[i-1]">{{ forbidColName(availability_cols_first[i-1]) }}</span>
                </td>
                <td class="in-tb-check">
                    <span v-if="availability_cols_second[i-1]" class="indeterm_check__wrap">
                        <span class="indeterm_check" @click="colForbidToggled(availability_cols_second[i-1])">
                            <i v-if="!forbidColPresent(availability_cols_second[i-1])" class="glyphicon glyphicon-ok group__icon"></i>
                        </span>
                    </span>
                </td>
                <td>
                    <span v-if="availability_cols_second[i-1]">{{ forbidColName(availability_cols_second[i-1]) }}</span>
                </td>
            </tr>
            </tbody>
        </table>

        <!----------- Data Tab -------------->

        <table class="table" v-show="selPermission.can_add">
            <colgroup>
                <col :width="cw['1']">
                <col :width="cw['11']">
            </colgroup>
            <thead>
            <tr>
                <th colspan="2" style="background-color: #AAA; color: #000; text-align: center;"
                    @click.self="otherTablesBody.datatab = !otherTablesBody.datatab"
                >
                    <span @click="otherTablesBody.datatab = !otherTablesBody.datatab">Data </span>
                    <label>
                        <span class="indeterm_check__wrap">
                            <span class="indeterm_check" @click="updateGroup(selPermission,'can_see_datatab')">
                                <i v-if="selPermission.can_see_datatab" class="glyphicon glyphicon-ok group__icon"></i>
                            </span>
                        </span>
                    </label>
                </th>
            </tr>
            </thead>
            <tbody v-show="otherTablesBody.datatab">
            <tr v-for="method in import_methods"
                v-if="method.avail"
                :style="{backgroundColor: selPermission.can_see_datatab ? null : '#EEE'}"
            >
                <td class="in-tb-check">
                    <span class="indeterm_check__wrap">
                        <span class="indeterm_check"
                              :class="{'disabled': !datatabAvailable(method.code) || !selPermission.can_see_datatab}"
                              @click="!datatabAvailable(method.code) || !selPermission.can_see_datatab
                                  ? null
                                  : toggleDatatab(method.code)"
                        >
                            <i v-if="selPermission.datatab_methods.charAt(method.key) === '1'" class="glyphicon glyphicon-ok group__icon"></i>
                        </span>
                    </span>
                </td>
                <td>
                    <span> {{ method.name }}</span>
                </td>
            </tr>
            <tr>
                <td colspan="2">&nbsp;</td>
            </tr>
            <tr :style="{backgroundColor: selPermission.can_see_datatab ? null : '#EEE'}">
                <td class="in-tb-check">
                    <span class="indeterm_check__wrap">
                        <span class="indeterm_check"
                              :class="{'disabled': !selPermission.can_see_datatab}"
                              @click="!selPermission.can_see_datatab ? null : updateGroup(selPermission,'datatab_only_append')"
                        >
                            <i v-if="selPermission.datatab_only_append" class="glyphicon glyphicon-ok group__icon"></i>
                        </span>
                    </span>
                </td>
                <td>
                    <span> Only “Append” allowed for importing.</span>
                </td>
            </tr>
            </tbody>
        </table>

        <!----------- View Sharing -------------->

        <table class="table" v-if="$root.checkAvailable(user, 'permission_views')">
            <colgroup>
                <col :width="cw['1']">
                <col :width="cw['11']">
            </colgroup>
            <thead @click="otherTablesBody.view = !otherTablesBody.view">
            <tr>
                <th colspan="2">
                    <a @click.stop="showViewsPopup()">Views</a>
                </th>
            </tr>
            </thead>
            <tbody v-show="otherTablesBody.view">
                <tr>
                    <td class="in-tb-check">
                        <span class="indeterm_check__wrap">
                            <span class="indeterm_check" @click="updateGroup(selPermission,'can_create_view')">
                                <i v-if="selPermission.can_create_view" class="glyphicon glyphicon-ok group__icon"></i>
                            </span>
                        </span>
                    </td>
                    <td><a @click.stop="showViewsPopup()">Create Views</a></td>
                </tr>
                <tr>
                    <td colspan="2">Share Selected Views</td>
                </tr>
                <template v-if="tableMeta._views.length">
                    <tr v-for="view in tableMeta._views">
                        <td class="in-tb-check">
                            <label>
                                <span class="indeterm_check__wrap">
                                    <span class="indeterm_check" @click="toggleViewRight(view)">
                                        <i v-if="findViewRight(view) > -1" class="glyphicon glyphicon-ok group__icon"></i>
                                    </span>
                                </span>
                            </label>
                        </td>
                        <td>
                            <a target="_blank" :href="'/mrv/'+(view.custom_path || view.hash)">{{ view.name }}</a>
                        </td>
                    </tr>
                </template>
                <template v-else="">
                    <tr>
                        <td class="in-tb-check" colspan="2">No items available.</td>
                    </tr>
                </template>
            </tbody>
        </table>

        <!----------- Conditional Formattings -------------->

        <table class="table" v-if="$root.checkAvailable(user, 'permission_cond_format')">
            <colgroup>
                <col :width="cw['1']">
                <col :width="cw['3']">
                <col :width="cw['1']">
                <col :width="cw['3']">
                <col :width="cw['1']">
                <col :width="cw['3']">
            </colgroup>
            <thead @click="otherTablesBody.cond = !otherTablesBody.cond">
            <tr>
                <th colspan="6">
                    <a @click.stop="showCondFormatPopup()">Conditional Formattings(CFs)</a>
                </th>
            </tr>
            </thead>
            <tbody v-show="otherTablesBody.cond">
                <tr>
                    <td class="in-tb-check">
                        <span class="indeterm_check__wrap">
                            <span class="indeterm_check" @click="updateGroup(selPermission,'can_create_condformat')">
                                <i v-if="selPermission.can_create_condformat" class="glyphicon glyphicon-ok group__icon"></i>
                            </span>
                        </span>
                    </td>
                    <td colspan="5"><a @click.stop="showCondFormatPopup()">Define Conditional Formattings(CFs)</a></td>
                </tr>
                <tr>
                    <td colspan="6">Share Selected Conditional Formattings(CFs)</td>
                </tr>
                <template v-if="tableMeta._cond_formats.length">
                    <tr v-for="cond_format in tableMeta._cond_formats">
                        <td class="in-tb-check">
                            <label>
                                <span class="indeterm_check__wrap">
                                    <span class="indeterm_check"
                                          @click="toggleCFRight(cond_format)"
                                    >
                                        <i v-if="findCFRight(cond_format)" class="glyphicon glyphicon-ok group__icon"></i>
                                    </span>
                                </span>
                            </label>
                        </td>
                        <td>
                            <span>{{ cond_format.name }}</span>
                        </td>
                        <td class="in-tb-check">
                            <label>
                                <span class="indeterm_check__wrap">
                                    <span class="indeterm_check"
                                          :class="{'disabled': !findCFRight(cond_format)}"
                                          @click="!findCFRight(cond_format)
                                              ? null
                                              : updateCFRight(cond_format, 'visible_shared')"
                                    >
                                        <i v-if="findCFVisShared(cond_format)" class="glyphicon glyphicon-ok group__icon"></i>
                                    </span>
                                </span>
                            </label>
                        </td>
                        <td>Visible</td>
                        <td class="in-tb-check">
                            <label>
                                <span class="indeterm_check__wrap">
                                    <span class="indeterm_check"
                                          :class="{'disabled': !findCFRight(cond_format)}"
                                          @click="!findCFRight(cond_format)
                                              ? null
                                              : updateCFRight(cond_format, 'always_on')"
                                    >
                                        <i v-if="findCFAlwaysOn(cond_format)" class="glyphicon glyphicon-ok group__icon"></i>
                                    </span>
                                </span>
                            </label>
                        </td>
                        <td>Always On</td>
                    </tr>
                </template>
                <template v-else="">
                    <tr>
                        <td class="in-tb-check" colspan="2">No items available.</td>
                    </tr>
                </template>
            </tbody>
        </table>

        <!----------- Downloading options -------------->

        <table class="table">
            <thead>
            <tr>
                <th colspan="7" style="background-color: #AAA; color: #000; text-align: center;"
                    @click.self="otherTablesBody.download = !otherTablesBody.download"
                >
                    <span @click="otherTablesBody.download = !otherTablesBody.download">Downloading Options: </span>
                    <label :class="anyDwnRestricted ? 'disabled' : ''">
                        <span class="indeterm_check__wrap">
                            <span class="indeterm_check"
                                  :class="anyDwnRestricted ? 'disabled' : ''"
                                  @click="toggleDownload('all')"
                            >
                                <i v-if="allDwnldsChecked == 2" class="glyphicon glyphicon-ok group__icon"></i>
                                <i v-if="allDwnldsChecked == 1" class="glyphicon glyphicon-minus group__icon"></i>
                            </span>
                        </span>
                        <span> All</span>
                    </label>
                </th>
            </tr>
            </thead>
            <tbody v-show="otherTablesBody.download">
            <tr>
                <td v-for="(val, key) in downloadings" style="width: 1fr">
                    <label style="white-space: nowrap">
                        <span class="indeterm_check__wrap">
                            <span class="indeterm_check"
                                  :class="{'disabled': !dwnAvailable(val)}"
                                  @click="!dwnAvailable(val) ? null : toggleDownload(val)"
                            >
                                <i v-if="selPermission.can_download.charAt(key) === '1'" class="glyphicon glyphicon-ok group__icon"></i>
                            </span>
                        </span>
                        <span> {{ val }}</span>
                    </label>
                </td>
            </tr>
            </tbody>
        </table>

        <!----------- Groups -------------->

        <table class="table" style="table-layout: fixed">
            <colgroup>
                <col :width="cw['1']">
                <col :width="cw['5']">
                <col :width="cw['1']">
                <col :width="cw['5']">
            </colgroup>
            <thead @click="otherTablesBody.group = !otherTablesBody.group">
            <tr>
                <th colspan="4" style="background-color: #AAA; color: #000; text-align: center;">
                    <a @click.stop="showGroupsPopup('row')">Row and Column Groups(XGrps)</a>
                </th>
            </tr>
            </thead>
            <tbody v-show="otherTablesBody.group">
            <tr>
                <td colspan="2">
                    <label>Row Groups(RGrps):</label>
                </td>
                <td colspan="2">
                    <label>Column Groups(CGrps):</label>
                </td>
            </tr>
            <tr v-for="i in max_groups">
                <td class="in-tb-check">
                    <span v-if="tableMeta._row_groups[i-1]" class="indeterm_check__wrap">
                        <span class="indeterm_check" @click="shareGroupRow(tableMeta._row_groups[i-1])">
                            <i v-if="rowColShared('_permission_rows', tableMeta._row_groups[i-1].id)" class="glyphicon glyphicon-ok group__icon"></i>
                        </span>
                    </span>
                </td>
                <td>
                    <a @click.stop="showGroupsPopup('row', tableMeta._row_groups[i-1].id)">
                        <span v-if="tableMeta._row_groups[i-1]">{{ tableMeta._row_groups[i-1].name }}</span>
                    </a>
                </td>
                <td class="in-tb-check">
                    <span v-if="tableMeta._column_groups[i-1]" class="indeterm_check__wrap">
                        <span class="indeterm_check" @click="shareGroupColumn(tableMeta._column_groups[i-1])">
                            <i v-if="rowColShared('_permission_columns', tableMeta._column_groups[i-1].id)" class="glyphicon glyphicon-ok group__icon"></i>
                        </span>
                    </span>
                </td>
                <td>
                    <a @click.stop="showGroupsPopup('col', tableMeta._column_groups[i-1].id)">
                        <span v-if="tableMeta._column_groups[i-1]">{{ tableMeta._column_groups[i-1].name }}</span>
                    </a>
                </td>
            </tr>
            </tbody>
        </table>

        <!----------- Addons -------------->

        <table class="table">
            <colgroup>
                <col :width="cw['4']">
                <col :width="cw['4']">
                <col :width="cw['4']">
            </colgroup>
            <thead @click="otherTablesBody.addon = !otherTablesBody.addon">
            <tr>
                <th colspan="3">
                    <span>Add-ons</span>
                </th>
            </tr>
            </thead>
            <tbody v-show="otherTablesBody.addon">
                <template v-if="someAddon">
                    <tr v-for="adn in $root.settingsMeta.all_addons" v-if="tableMeta['add_'+adn.code]">
                        <td colspan="3" style="padding: 0">
                            <tab-settings-permissions-basics-addon
                                    :table-meta="tableMeta"
                                    :sel-addon="adn"
                                    :sel-permission="selPermission"
                            ></tab-settings-permissions-basics-addon>
                        </td>
                    </tr>
                </template>
                <template v-else="">
                    <tr>
                        <td class="in-tb-check" colspan="3">No items available.</td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>
</template>

<script>
    import {eventBus} from '../../../../../app';

    import CellStyleMixin from "../../../../_Mixins/CellStyleMixin";

    import TabSettingsPermissionsBasicsAddon from "./TabSettingsPermissionsBasicsAddon";

    export default {
        name: "TabSettingsPermissionsBasics",
        components: {
            TabSettingsPermissionsBasicsAddon
        },
        mixins: [
            CellStyleMixin,
        ],
        data: function () {
            return {
                downloadings: ['Print', 'CSV', 'PDF', 'XLSX', 'JSON', 'XML', 'PNG'],
                import_methods: [
                    {name: 'Paste to Import',  avail: true,    key: 5,     code: 'paste'},
                    {name: 'Build/Update',     avail: false,   key: 0,     code: 'scratch'},
                    {name: 'CSV/Excel Import', avail: true,    key: 1,     code: 'csv'},
                    {name: 'MySQL Import',     avail: false,   key: 2,     code: 'mysql'},
                    {name: 'Remote MySQL',     avail: false,   key: 3,     code: 'remote'},
                    {name: 'Referencing',      avail: true,    key: 4,     code: 'reference'},
                    {name: 'Web Scraping',     avail: false,   key: 6,     code: 'web_scrap'},
                    {name: 'Google Sheets',    avail: false,   key: 7,     code: 'g_sheets'},
                ],
                otherTablesBody: {
                    basic: false,
                    columns: false,
                    datatab: false,
                    view: false,
                    cond: false,
                    download: false,
                    group: false,
                    addon: false,
                },
                basic_params: [
                    'can_add','enforced_theme','can_see_history','can_reference','can_public_copy',
                    'can_edit_tb','can_drag_rows','can_drag_columns','hide_folder_structure','can_change_primaryview',
                ],
                availability_cols_first: [],
                availability_cols_second: [],
                max_avails: 0,
                max_groups: 0,
                cw: {
                    '1': 1/12*100,
                    '2': 2/12*100,
                    '3': 3/12*100,
                    '4': 4/12*100,
                    '5': 5/12*100,
                    '11': 11/12*100,
                },
            }
        },
        props:{
            tableMeta: Object,
            user: Object,
            selPermission: Object,
        },
        computed: {
            anyDwnRestricted() {
                return !this.user.is_admin
                    && (
                        !this.$root.checkAvailable(this.user, 'dwn_print')
                        ||
                        !this.$root.checkAvailable(this.user, 'dwn_csv')
                        ||
                        !this.$root.checkAvailable(this.user, 'dwn_pdf')
                        ||
                        !this.$root.checkAvailable(this.user, 'dwn_xls')
                        ||
                        !this.$root.checkAvailable(this.user, 'dwn_json')
                        ||
                        !this.$root.checkAvailable(this.user, 'dwn_xml')
                    );
            },
            allDwnldsChecked() {
                let check = this.selPermission.can_download.indexOf('1') > -1;
                let uncheck = this.selPermission.can_download.indexOf('0') > -1;
                return !uncheck
                    ? 2
                    : check ? 1 : 0;
            },
            allBasicsChecked() {
                let check = false;
                _.each(this.basic_params, (par) => { check = check || this.selPermission[par]==true });
                let uncheck = false;
                _.each(this.basic_params, (par) => { uncheck = uncheck || this.selPermission[par]==false });
                return !uncheck
                    ? 2
                    : check ? 1 : 0;
            },
            allBasicColumnsChecked() {
                let check = false;
                _.each(this.$root.settingsMeta['user_headers_attributes'], (db_name) => { check = check || !this.forbidColPresent(db_name) });
                let uncheck = false;
                _.each(this.$root.settingsMeta['user_headers_attributes'], (db_name) => { uncheck = uncheck || !!this.forbidColPresent(db_name) });
                return !uncheck
                    ? 2
                    : check ? 1 : 0;
            },
            someAddon() {
                return this.tableMeta.add_map || this.tableMeta.add_bi || this.tableMeta.add_request || this.tableMeta.add_email
                    || this.tableMeta.add_alert || this.tableMeta.add_kanban || this.tableMeta.add_gantt || this.tableMeta.add_calendar
                    || this.tableMeta.add_twilio || this.tableMeta.add_tournament || this.tableMeta.add_report || this.tableMeta.add_ai
                    || this.tableMeta.add_grouping || this.tableMeta.add_simplemap;
            },
        },
        methods: {
            in_array(key, arr) {
                return window.in_array(key, arr);
            },
            updateGroup(tableRow, key) {
                if (tableRow && key) {
                    tableRow[key] = !tableRow[key];
                }
                this.$emit('update-group', tableRow);
            },

            //BASICS
            toggleAllBasics() {
                let status = !this.allBasicsChecked;
                _.each(this.basic_params, (par) => {
                    this.selPermission[par] = status;
                });
                this.updateGroup(this.selPermission);
            },

            //COLUMNS BASICS
            toggleAllColumnsBasics() {
                this.$root.sm_msg_type = 1;
                if (!this.allBasicColumnsChecked) {
                    axios.delete('/ajax/table-permission/forbid-settings', {
                        params: {
                            table_permission_id: this.selPermission.id,
                        }
                    }).then(({ data }) => {
                        this.selPermission._forbid_settings = data;
                    }).catch(errors => {
                        Swal('Info', getErrors(errors));
                    }).finally(() => {
                        this.$root.sm_msg_type = 0;
                    });
                } else {
                    axios.post('/ajax/table-permission/forbid-settings', {
                        table_permission_id: this.selPermission.id,
                    }).then(({ data }) => {
                        this.selPermission._forbid_settings = data;
                    }).catch(errors => {
                        Swal('Info', getErrors(errors));
                    }).finally(() => {
                        this.$root.sm_msg_type = 0;
                    });
                }
            },

            //Share columns or rows
            rowColShared(type, id) {
                let rowCol = type === '_permission_rows'
                    ? _.find(this.selPermission._permission_rows, {table_row_group_id: Number(id)})
                    : _.find(this.selPermission._permission_columns, {table_column_group_id: Number(id)});
                return rowCol && rowCol.shared;
            },

            //User Group Columns Functions
            shareGroupColumn(col) {
                let shared_toggle = this.rowColShared('_permission_columns', col.id) ? 0 : 1;
                this.$emit('share-group-column', col.id, shared_toggle);
            },

            //User Group Rows Functions
            shareGroupRow(row) {
                let shared_toggle = this.rowColShared('_permission_rows', row.id) ? 0 : 1;
                this.$emit('share-group-row', row.id, shared_toggle);
            },
            
            //Right Others Functions
            dwnAvailable(field) {
                let res = false;
                switch (field) {
                    case 'Print': res = this.$root.checkAvailable(this.user, 'dwn_print'); break;
                    case 'CSV': res = this.$root.checkAvailable(this.user, 'dwn_csv'); break;
                    case 'PDF': res = this.$root.checkAvailable(this.user, 'dwn_pdf'); break;
                    case 'XLSX': res = this.$root.checkAvailable(this.user, 'dwn_xls'); break;
                    case 'JSON': res = this.$root.checkAvailable(this.user, 'dwn_json'); break;
                    case 'XML': res = this.$root.checkAvailable(this.user, 'dwn_xml'); break;
                    case 'PNG': res = this.$root.checkAvailable(this.user, 'dwn_print'); break;
                }
                return res;
            },
            toggleDownload(field) {
                let right = this.selPermission.can_download;

                if (field == 'all') {
                    right = right === '111111' ? '000000' : '111111';
                    this.selPermission.can_download = right;
                } else {
                    switch (field) {
                        case 'Print': right = (right.charAt(0) === '0' ? 1 : 0) + right.substr(1); break;
                        case 'CSV': right = right.substr(0,1) + (right.charAt(1) === '0' ? 1 : 0) + right.substr(2); break;
                        case 'PDF': right = right.substr(0,2) + (right.charAt(2) === '0' ? 1 : 0) + right.substr(3); break;
                        case 'XLSX': right = right.substr(0,3) + (right.charAt(3) === '0' ? 1 : 0) + right.substr(4); break;
                        case 'JSON': right = right.substr(0,4) + (right.charAt(4) === '0' ? 1 : 0) + right.substr(5); break;
                        case 'XML': right = right.substr(0,5) + (right.charAt(5) === '0' ? 1 : 0) + right.substr(6); break;
                        case 'PNG': right = right.substr(0,6) + (right.charAt(6) === '0' ? 1 : 0); break;
                    }
                    this.selPermission.can_download = right;
                }

                this.updateGroup(this.selPermission);
            },
            datatabAvailable(method_code) {
                let res = false;
                switch (method_code) {
                    case 'scratch': res = this.$root.checkAvailable(this.user, 'data_build'); break;
                    case 'csv': res = this.$root.checkAvailable(this.user, 'data_csv'); break;
                    case 'mysql': res = this.$root.checkAvailable(this.user, 'data_mysql'); break;
                    case 'remote': res = this.$root.checkAvailable(this.user, 'data_remote'); break;
                    case 'reference': res = this.$root.checkAvailable(this.user, 'data_ref'); break;
                    case 'paste': res = this.$root.checkAvailable(this.user, 'data_paste'); break;
                    case 'g_sheets': res = this.$root.checkAvailable(this.user, 'data_g_sheets'); break;
                    case 'web_scrap': res = this.$root.checkAvailable(this.user, 'data_web_scrap'); break;
                }
                return res;
            },
            toggleDatatab(method_code) {
                let right = this.selPermission.datatab_methods;

                switch (method_code) {
                    case 'scratch': right = (right.charAt(0) === '0' ? 1 : 0) + right.substr(1); break;
                    case 'csv': right = right.substr(0,1) + (right.charAt(1) === '0' ? 1 : 0) + right.substr(2); break;
                    case 'mysql': right = right.substr(0,2) + (right.charAt(2) === '0' ? 1 : 0) + right.substr(3); break;
                    case 'remote': right = right.substr(0,3) + (right.charAt(3) === '0' ? 1 : 0) + right.substr(4); break;
                    case 'reference': right = right.substr(0,4) + (right.charAt(4) === '0' ? 1 : 0) + right.substr(5); break;
                    case 'paste': right = right.substr(0,5) + (right.charAt(5) === '0' ? 1 : 0); break;
                }
                this.selPermission.datatab_methods = right;

                this.updateGroup(this.selPermission);
            },

            //Views
            findViewRight(view) {
                return _.findIndex(view._view_rights, {table_permission_id: this.selPermission.id});
            },
            toggleViewRight(view) {
                this.$root.sm_msg_type = 1;
                let vr_idx = this.findViewRight(view);
                if (vr_idx > -1) {
                    axios.delete('/ajax/table-view/right', {
                        params: {
                            table_view_id: view.id,
                            table_view_right_id: view._view_rights[vr_idx].id
                        }
                    }).then(({ data }) => {
                        view._view_rights.splice(vr_idx, 1);
                    }).catch(errors => {
                        Swal('Info', getErrors(errors));
                    }).finally(() => {
                        this.$root.sm_msg_type = 0;
                    });
                } else {
                    axios.post('/ajax/table-view/right', {
                        table_view_id: view.id,
                        table_permission_id: this.selPermission.id
                    }).then(({ data }) => {
                        view._view_rights.push(data);
                    }).catch(errors => {
                        Swal('Info', getErrors(errors));
                    }).finally(() => {
                        this.$root.sm_msg_type = 0;
                    });
                }
            },

            //Cond formats
            specParams(cond_format) {
                let tp_id = this.selPermission.id;
                return _.find(cond_format._special_statuses, {table_permission_id: Number(tp_id)});
            },
            findCFRight(cond_format) {
                return !!this.specParams(cond_format);
            },
            toggleCFRight(cond_format) {
                this.$root.sm_msg_type = 1;
                if (this.findCFRight(cond_format)) {
                    axios.delete('/ajax/cond-format/right', {
                        params: {
                            cond_format_id: cond_format.id,
                            table_permission_id: this.selPermission.id
                        }
                    }).then(({ data }) => {
                        Object.assign(cond_format, data);
                    }).catch(errors => {
                        Swal('Info', getErrors(errors));
                    }).finally(() => {
                        this.$root.sm_msg_type = 0;
                    });
                } else {
                    axios.post('/ajax/cond-format/right', {
                        cond_format_id: cond_format.id,
                        table_permission_id: this.selPermission.id
                    }).then(({ data }) => {
                        Object.assign(cond_format, data);
                        //when sharing cond format -> used col/row groups also must be shared
                        if (cond_format.table_column_group_id) {
                            this.$emit('share-group-column', cond_format.table_column_group_id, 1);
                        }
                        if (cond_format.table_row_group_id) {
                            this.$emit('share-group-row', cond_format.table_row_group_id, 1);
                        }
                    }).catch(errors => {
                        Swal('Info', getErrors(errors));
                    }).finally(() => {
                        this.$root.sm_msg_type = 0;
                    });
                }
            },
            findCFAlwaysOn(cond_format) {
                let param = this.specParams(cond_format);
                return param ? param.always_on == 1 : false;
            },
            findCFVisShared(cond_format) {
                let param = this.specParams(cond_format);
                return param ? param.visible_shared == 1 : false;
            },
            updateCFRight(cond_format, key) {
                this.$root.sm_msg_type = 1;
                let alw_on = this.findCFAlwaysOn(cond_format);
                let vis_shared = this.findCFVisShared(cond_format);
                axios.put('/ajax/cond-format/right', {
                    cond_format_id: cond_format.id,
                    table_permission_id: this.selPermission.id,
                    always_on: (key === 'always_on' ? !alw_on : alw_on),
                    visible_shared: (key === 'visible_shared' ? !vis_shared : vis_shared)
                }).then(({ data }) => {
                    Object.assign(cond_format, data);
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },

            //Forbid Column Settings
            forbidColPresent(db_name) {
                return _.find(this.selPermission._forbid_settings, {db_col_name: db_name});
            },
            colForbidToggled(db_name) {
                this.$root.sm_msg_type = 1;
                if (this.forbidColPresent(db_name)) {
                    axios.delete('/ajax/table-permission/forbid-settings', {
                        params: {
                            table_permission_id: this.selPermission.id,
                            db_col_name: db_name
                        }
                    }).then(({ data }) => {
                        this.selPermission._forbid_settings = data;
                    }).catch(errors => {
                        Swal('Info', getErrors(errors));
                    }).finally(() => {
                        this.$root.sm_msg_type = 0;
                    });
                } else {
                    axios.post('/ajax/table-permission/forbid-settings', {
                        table_permission_id: this.selPermission.id,
                        db_col_name: db_name
                    }).then(({ data }) => {
                        this.selPermission._forbid_settings = data;
                    }).catch(errors => {
                        Swal('Info', getErrors(errors));
                    }).finally(() => {
                        this.$root.sm_msg_type = 0;
                    });
                }
            },
            forbidColName(db_col_name) {
                let fld = _.find(this.$root.settingsMeta.table_fields._fields, {field: db_col_name});
                return this.$root.uniqName( fld ? fld.name : '' );
            },

            //emits
            showGroupsPopup(type, id) {
                eventBus.$emit('show-grouping-settings-popup', this.tableMeta.db_name, type, id);
            },
            showCondFormatPopup() {
                eventBus.$emit('show-cond-format-popup', this.tableMeta.db_name);
            },
            showViewsPopup() {
                eventBus.$emit('show-table-views-popup', this.tableMeta.db_name);
            },
            showSettingsCustPopup() {
                eventBus.$emit('show-table-settings-all-popup', {tab:'customizable'});
            },
        },
        mounted() {
            let ava_arr = this.$root.settingsMeta['user_headers_attributes'];
            let half = Math.ceil(ava_arr.length/2);
            this.max_avails = half;
            this.availability_cols_first = ava_arr.slice(0, half);
            this.availability_cols_second = ava_arr.slice(half, ava_arr.length);

            this.max_groups = Math.max(this.tableMeta._column_groups.length, this.tableMeta._row_groups.length);
        }
    }
</script>

<style lang="scss" scoped>
    @import "TabSettingsPermissions";

    .permissions-tab {
        .table {
            table-layout: fixed;

            td {
                white-space: normal !important;
                overflow: hidden;
            }
        }
    }
</style>