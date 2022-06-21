
/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

import Vue from 'vue';
import Vuex from 'vuex';

Vue.use(Vuex);

Vue.component('link-preview-block', require('./components/CommonBlocks/LinkPreviewBlock.vue'));
Vue.component('hover-block', require('./components/CommonBlocks/HoverBlock.vue'));
Vue.component('view-pass-block', require('./components/CommonBlocks/ViewPassBlock.vue'));
Vue.component('main-app-wrapper', require('./components/MainAppWrapper.vue'));
Vue.component('saving-message', require('./components/CommonBlocks/SavingMessage.vue'));
Vue.component('auth-forms', require('./components/_AuthForms/AuthForms.vue'));
Vue.component('homepage', require('./components/Homepage.vue'));
Vue.component('static-pages', require('./components/Statics/StaticPages.vue'));
Vue.component('print-table', require('./components/CustomTable/PrintTable.vue'));
Vue.component('table-request-wrapper', require('./components/RequestDataWrapper.vue'));
Vue.component('single-record-wrapper', require('./components/SingleRecordWrapper.vue'));
Vue.component('get-started', require('./components/Statics/GetStarted.vue'));

Vue.component('my-apps-page', require('./Applications/MyAppsPage.vue'));
Vue.component('list-tablda-apps', require('./Applications/ListTabldaApps.vue'));
Vue.component('risa3d-form', require('./Applications/Risa3dForm.vue'));
Vue.component('risa3d-remover-form', require('./Applications/Risa3dRemoverForm.vue'));
Vue.component('stim-wid-form', require('./Applications/StimWid/StimWidForm.vue'));
Vue.component('stim-ma-json', require('./Applications/StimMaJson/StimMaJson.vue'));
Vue.component('stim-calculate-loads', require('./Applications/StimCalculateLoads/StimCalculateLoads.vue'));
Vue.component('payment-processing-page', require('./Applications/PaymentProcessing/PaymentProcessingPage.vue'));

import ThemeStyleMixin from './global_mixins/ThemeStyleMixin.vue';
import AutologoutMixin from './global_mixins/AutologoutMixin.vue';

import {SpecialFuncs} from './classes/SpecialFuncs';
import {DataReverser} from './classes/DataReverser';

export const eventBus = new Vue();

export const columnSettRadioFields = [
    'is_image_on_board',
    'is_lat_field',
    'is_long_field',
    'is_info_header_field',
    'map_find_street_field',
    'map_find_city_field',
    'map_find_state_field',
    'map_find_county_field',
    'map_find_zip_field',
    'is_gantt_group',
    'is_gantt_parent_group',
    'is_gantt_main_group',
    'is_gantt_name',
    'is_gantt_parent',
    'is_gantt_start',
    'is_gantt_end',
    'is_gantt_progress',
    'is_gantt_color',
    'is_gantt_label_symbol',
    'is_gantt_milestone',
    'is_calendar_start',
    'is_calendar_end',
    'is_calendar_title',
    'is_calendar_cond_format',
];

window.addEventListener("message", function(event) {
    if (event.data && event.data.event_name === 'close-application') {
        eventBus.$emit('global-close-application', event.data.app_code);
    }
});

window.addEventListener("load", function(event) {

    const app = new Vue({
        el: '#app',
        mixins: [
            ThemeStyleMixin,
            AutologoutMixin,
        ],
        data: function () {
            return {
                ping_delay: 10000,
                version_hash_delay: 3000,

                last_checked_id: null,
                all_checked_rows: false,
                discourse_login_iframe: '',
                e__used: false,
                data_reverser: new DataReverser(),
                debug: false,
                is_dcr_page: false,

                tablda_highlights: [], // arr of objects: {table_id: null, row_id: null}

                lastMouseClick: {
                    clientX: null,
                    clientY: null,
                },
                sm_msg_type: undefined,
                prevent_cell_edit: false,
                global_no_edit: false,

                //#app_avail_formulas
                availRowSumFormulas: ['count','countunique','sum','min','max','mean','avg','var','std'],

                labels: {},
                availableCalendarColumns: [
                    'name',
                    'is_calendar_start',
                    'is_calendar_end',
                    'is_calendar_title',
                    'is_calendar_cond_format',
                ],
                availableGanttColumns: [
                    'name',
                    'is_gantt_group',
                    'is_gantt_parent_group',
                    'is_gantt_main_group',
                    'is_gantt_name',
                    'is_gantt_parent',
                    'is_gantt_start',
                    'is_gantt_end',
                    'is_gantt_progress',
                    'is_gantt_color',
                    'is_gantt_tooltip',
                    'is_gantt_left_header',
                    'is_gantt_label_symbol',
                    'is_gantt_milestone',
                ],
                availableMapColumns: [
                    'name',
                    'is_lat_field',
                    'is_long_field',
                    /*'map_find_street_field',
                    'map_find_city_field',
                    'map_find_state_field',
                    'map_find_county_field',
                    'map_find_zip_field',*/
                    'info_box',
                    'is_info_header_field',
                ],
                availableInpsColumns: [
                    'name',
                    'input_type',
                    'is_uniform_formula',
                    'f_formula',
                    'ddl_id',
                    'ddl_add_option',
                    'ddl_auto_fill',
                    'ddl_style',
                    'mirror_rc_id',
                    'mirror_field_id',
                    'mirror_part',
                    'fetch_source_id',
                ],
                availableSettingsColumns: [
                    'name',
                    'header_background',
                    'header_unit_ddl',
                    'unit',
                    'unit_ddl_id',
                    'f_required',
                    'tooltip',
                    'tooltip_show',
                    'placeholder_content',
                    'placeholder_only_form',
                    'notes',
                    'default_stats',
                    'is_unique_collection',
                    'is_search_autocomplete_display',
                ],
                availableNotOwnerDisplayColumns: [
                    'name',
                    'filter',
                    'filter_type',
                    'popup_header',
                    'is_floating',
                    'unit_display',
                    'min_width',
                    'max_width',
                    'width',
                    'show_history',
                    'col_align',
                    'show_zeros',
                ],
                availablePopupDisplayColumns: [
                    'name',
                    'fld_display_name',
                    'fld_display_value',
                    'fld_display_border',
                    'is_topbot_in_popup',
                    'verttb_he_auto',
                    'verttb_cell_height',
                    'verttb_row_height',
                    'is_show_on_board',
                    'is_image_on_board',
                    'image_display_view',
                    'image_display_fit',
                    'is_default_show_in_popup',
                    'is_table_field_in_popup',
                    'is_start_table_popup',
                    'is_dcr_section',
                ],
                systemFields: [
                    'id',
                    'row_hash',
                    'static_hash',
                    'row_order',
                    'refer_tb_id',
                    'request_id',
                    'created_on',
                    'created_by',
                    'modified_on',
                    'modified_by'
                ],
                systemFieldsNoId: [
                    'row_hash',
                    'static_hash',
                    'row_order',
                    'refer_tb_id',
                    'request_id',
                    'created_on',
                    'created_by',
                    'modified_on',
                    'modified_by'
                ],
                columnSettRadioFields: columnSettRadioFields,
                ddlInputTypes: ['S-Select','S-Search','S-SS','M-Select','M-Search','M-SS'],
                isSafari: false,
                isIphone: false,
                app_url: '',
                app_name: '',
                clear_url: '',
                app_domain: '',
                color_palette: { 1:'', 2:'', 3:'', 4:'', 5:'', 6:'', 7:'', 8:'' },

                cellHeight: 1,
                maxCellRows: 0,
                fullWidthCell: Boolean(Cookies.get('full_width_cell') == 1),

                allShowed: false,
                printHeaders: null,
                printRows: null,

                user: {},

                settingsMeta: {},
                metaDcrObject: {},
                metaSrvObject: {},

                filters: [],
                rowPerPage: 0,
                tableMeta: {},
                listTableRows: [],
                listTableRows_state: '',
                favoriteTableRows: [],
                data_is_editing: false,
                prevent_cell_keyup: false,

                request_view_filtering: null,
                request_params: null,
                selectParam: {
                    minimumResultsForSearch: Infinity,
                    width: '100%',
                    dropdownAutoWidth: true,
                },
                isRightMenu: (Cookies.get('right_menu') !== '0'),
                isLeftMenu: (Cookies.get('left_menu') !== '0'),
                private_menu_tree: null,
                account_menu_tree: null,

                all_rg_toggled: null,

                max_cell_width: 500,

                google_help: '"Maps JavaScript API" needs to be allowed for the provided API Key for using Google Map.' +
                    '<br>"Places API" needs to be allowed for using "Address" type field.',
                sendgrid_help: 'To obtain a Sendgrid API Key, you need to register' +
                    '<br>an account at <a target="_blank" href="https://sendgrid.com/">sendgrid.com</a> ' +
                    'and create an <a target="_blank" href="https://sendgrid.com/docs/ui/account-and-settings/api-keys/#creating-an-api-key">API Key</a>.',

                tablesZidx: 1400,

                //Hover singleton
                glob_hover: null,
                hover_html: '',
                hover_delay: 1000,
                hover_show: false,
                hover_left: 0,
                hover_top: 0,

                //link Preview singleton
                glob_linkprev: null,
                linkprev_meta: null,
                linkprev_meta_header: null,
                linkprev_meta_row: null,
                linkprev_object: null,
                linkprev_delay: 500,
                linkprev_left: 0,
                linkprev_top: 0,
            }
        },
        computed: {
            app_stim_uh() {
                return this.user.view_all ? this.user.view_all.hash : null;
            },
        },
        methods: {
            inArray(item, array) {
                return $.inArray(item, array) > -1;
            },
            inArraySys(item, array) {
                let concated = _.concat(this.systemFields, array);
                return $.inArray(item, concated) > -1;
            },
            checkedRowObject(allRows) {
                let rows_ids = [];
                let all_rows_checked = this.all_checked_rows;
                _.each(allRows, (row) => {
                    if (row._checked_row) {
                        rows_ids.push(row.id);
                    } else {
                        all_rows_checked = false;
                    }
                });
                return {
                    rows_ids: rows_ids,
                    all_checked: all_rows_checked,
                };
            },
            fileUrl(file_obj) {
                let url = file_obj.filehash || file_obj.url;
                return this.clear_url
                    + '/file/'
                    + url
                    + '?s='
                    + (this.user.view_hash || this.user._is_folder_view || this.user._dcr_hash || '');
            },
            //select and m-select
            hasStype(input_type) {
                return SpecialFuncs.hasStype(input_type);
            },
            hasDsearch(input_type) {
                return SpecialFuncs.hasDsearch(input_type);
            },
            isMSEL(input_type) {
                return SpecialFuncs.isMSEL(input_type);
            },
            issel(input_type) {
                return SpecialFuncs.issel(input_type);
            },
            //RC getters
            tb_id(id) {
                return Math.random().toString(36).replace(/[^a-z]+/g, '') + id + ':' + uuidv4();
            },
            in_array(key, arr) {
                return in_array(key, arr);
            },
            rcShow(tableRow, field) {
                return SpecialFuncs.rcObj(tableRow, field, tableRow[field]).show_val || tableRow[field];
            },
            //togglers
            setTextRowSett(tableMeta) {
                this.chckMetaSetts(tableMeta);
                if (tableMeta && tableMeta._cur_settings) {
                    this.cellHeight = Number(tableMeta._cur_settings.cell_height)
                        || Number(readLocalStorage('local_cell_height'))
                        || 1;
                    this.maxCellRows = Number(tableMeta._cur_settings.max_cell_rows)
                        || Number(readLocalStorage('local_max_cell_rows'))
                        || 0;
                }
            },
            changeCellHeight(val, tableMeta) {
                this.cellHeight = val;
                this.chckMetaSetts(tableMeta);
                if (tableMeta && tableMeta._cur_settings) {
                    tableMeta._cur_settings.cell_height = val;
                }
                this.updateTbUserSetts(tableMeta, 'local_cell_height', val);
            },
            changeMaxCellRows(val, tableMeta) {
                this.maxCellRows = val;
                this.chckMetaSetts(tableMeta);
                if (tableMeta && tableMeta._cur_settings) {
                    tableMeta._cur_settings.max_cell_rows = val;
                }
                this.updateTbUserSetts(tableMeta, 'local_max_cell_rows', val);
            },
            changeLeftMenuWi(val, tableMeta) {
                this.chckMetaSetts(tableMeta);
                if (tableMeta && tableMeta._cur_settings) {
                    tableMeta._cur_settings.left_menu_width = val;
                }
                this.updateTbUserSetts(tableMeta, 'local_left_menu_width', val);
            },
            changeStimFilterWi(val, tableMeta) {
                this.chckMetaSetts(tableMeta);
                if (tableMeta && tableMeta._cur_settings) {
                    tableMeta._cur_settings.stim_filter_width = val;
                }
                this.updateTbUserSetts(tableMeta, 'local_stim_filter_width', val);
            },
            chckMetaSetts(tableMeta) {
                if (tableMeta && !tableMeta._cur_settings) {
                    tableMeta._cur_settings = { id:null };
                }
            },
            fullWidthCellToggle() {
                this.fullWidthCell = !this.fullWidthCell;
                SpecialFuncs.set_cookie('full_width_cell', this.fullWidthCell ? 1 : 0);
            },
            toggleRightMenu() {
                if (!this.sideIsNa('side_right')) {
                    this.isRightMenu = !this.isRightMenu;
                    SpecialFuncs.set_cookie('right_menu', this.isRightMenu ? 1 : 0);
                    eventBus.$emit('redraw-gannt');
                }
            },
            toggleLeftMenu() {
                if (!this.sideIsNa('side_left_menu') || !this.sideIsNa('side_left_filter')) {
                    this.isLeftMenu = !this.isLeftMenu;
                    SpecialFuncs.set_cookie('left_menu', this.isLeftMenu ? 1 : 0);
                    eventBus.$emit('redraw-gannt');
                }
            },
            sideIsNa(side_str) {
                let pnls = this.user.view_all;
                return pnls && pnls[side_str] === 'na';
            },
            toggleTopMenu() {
                let pnls = this.user.view_all;
                if (!pnls || pnls.side_top != 'na') {
                    $('#main_navbar').toggle();
                }
            },
            //togglers ^^^^^

            nl2br(str) {
                return SpecialFuncs.nl2br(str);
            },
            getFloat(val) {
                return parseFloat(val) ? parseFloat(val) : 0;
            },
            deleteSystemFields: function(arr) {
                return arr;
                /*for (var key in arr) {
                    if (key.charAt(0) === '_') {
                        delete arr[key];
                    }
                }*/
            },
            checkAvailable(out_user, code, rows_count) {
                let user = out_user || this.user;
                if (user.is_admin) {
                    return true;
                }

                //available if code=unlimited or param < code
                if (code === 'q_tables' || code === 'row_table') {

                    return !Number(user._available_features[code])
                        ||
                        rows_count < Number(user._available_features[code]);

                } else {

                    return (
                            user._subscription.plan_code === 'basic'
                            ||
                            user._subscription.left_days > 0
                        )
                        &&
                        user._available_features[code];

                }
            },
            convertToLocal(date, timezone, f_type = 'Date Time') {
                return SpecialFuncs.convertToLocal(date, timezone, f_type);
            },
            convertToUTC(date, timezone, f_type = 'Date Time') {
                return SpecialFuncs.convertToUTC(date, timezone, f_type);
            },
            uniqName(name) {
                return name ? _.uniq( name.split(',') ).join(' ') : '';
            },
            strip_tags (input, allowed) {
                return SpecialFuncs.strip_tags(input, allowed);
            },
            strip_danger_tags (input) {
                return SpecialFuncs.strip_danger_tags(input);
            },

            //Required and Default Fields Check
            setCheckRequired(tableMeta, fields, spec_message) {
                let emptyFields = [];

                _.each(fields, (fld, key) => {
                    let idx = _.findIndex(tableMeta._fields, {field: key});
                    let header = idx > -1 ? tableMeta._fields[idx] : null;
                    if (header && header.f_required && ['Radio'].indexOf(header.f_type) === -1 && !fld) {
                        emptyFields.push(tableMeta._fields[idx].name);
                    }
                });

                //check required fields
                if (emptyFields.length) {
                    let req_msg = [];
                    _.each(emptyFields, (fld) => {
                        req_msg.push( 'Required "'+fld+'" is empty.' );
                    });
                    Swal({ html: spec_message || req_msg.join('<br>') });
                }

                return !emptyFields.length;
            },

            //USER Fields
            getUsrObj(tableRow, field) {
                return tableRow['_u_'+field] || {};
            },
            smallUserStr(row, header, val, as_object) {
                let usr = '';
                try { usr = row['_u_'+header.field][_.trim(val)]; } catch (e) {}

                if (as_object) {
                    return usr;
                }

                return usr
                    ? (usr.first_name ? usr.first_name + ' ' + (usr.last_name || '') : usr.username)
                    : row[header.field];
            },
            getUserFullStr(row, header, usr_flds, prefix = 'user_fld') {
                let users = [];
                try { users = row['_u_'+header.field]; } catch (e) {}
                let res = '';
                _.each(users, (usr) => {
                    res += this.getUserSimple(usr, usr_flds, prefix);
                });
                return res;
            },
            getUserSimple(user, usr_flds, prefix = 'user_fld') {
                if (!user) {
                    return '';
                }

                let fsize = this.themeTextFontSize;
                let res = this.getUsrAvatar(user) && (!usr_flds || usr_flds[prefix+'_show_image'])
                    ? '<img src="'+this.getUsrAvatar(user)+'" width="'+fsize+'" height="'+fsize+'">&nbsp;'
                    : '';

                if (!usr_flds || usr_flds[prefix+'_show_first']) {
                    res += user.first_name;
                }
                if (user.first_name && (!usr_flds || usr_flds[prefix+'_show_last'])) {
                    res += user.last_name ? ' '+user.last_name : '';
                }

                res += user.email && (!usr_flds || usr_flds[prefix+'_show_email'])
                    ? ' ('+user.email+')'
                    : '';

                res += user.username && (!usr_flds || usr_flds[prefix+'_show_username'])
                    ? ' '+user.username
                    : '';

                return res;
            },
            getUsrAvatar(user) {
                if (!user || !user.avatar) {
                    return '/assets/img/profile.png';
                }

                return user.avatar.indexOf('http') > -1
                    ? user.avatar
                    : '/upload/users/' + user.avatar;
            },
            //USER Fields

            copyToClipboard(el) {
                if (el) {
                    SpecialFuncs.domToClipboard(el);
                    Swal('Copied');
                }
            },

            //switch rows in popup
            anotherPopup(all_rows, row_id, is_next, openFunc) {
                let idx = _.findIndex(all_rows, {id: Number(row_id)});
                idx = idx > -1 ? idx : _.findIndex(all_rows, {_id: Number(row_id)});
                //next
                if (idx > -1 && is_next && all_rows[idx+1]) {
                    this.$nextTick(() => {
                        openFunc ? openFunc(idx+1) : null;
                    });
                }
                //prev
                if (idx > -1 && !is_next && all_rows[idx-1]) {
                    this.$nextTick(() => {
                        openFunc ? openFunc(idx-1) : null;
                    });
                }
            },

            //cell components
            tdCellComponent(is_system) {
                if (!is_system) {
                    return 'custom-cell-table-data';
                } else
                if (is_system == 1) {
                    return 'custom-cell-system-table-data';
                } else
                if (is_system == 2) {
                    return 'custom-cell-corresp-table-data';
                } else {
                    return '';
                }
            },
            //parse M-Select
            parseMsel(val) {
                return SpecialFuncs.parseMsel(val);
            },


            //UPDATE SETTINGS COLUMN
            updateTbUserSetts(tableMeta, key, val) {
                if (tableMeta && tableMeta.id && tableMeta._cur_settings && this.user && this.user.id) {
                    axios.post('/ajax/settings/data/just_user_setts', {
                        table_id: tableMeta.id,
                        datas: tableMeta._cur_settings,
                    }).then(({data}) => {
                        tableMeta._cur_settings = data._cur_settings;
                    }).catch(errors => {
                        Swal('', getErrors(errors));
                    });
                } else {
                    setLocalStorage(key, val);
                }
            },
            updateSettingsColumn(tableMeta, tableRow) {
                let promise = null;
                //add/remove filter on front-end
                if ($.inArray(tableRow._changed_field, ['filter']) > -1) {
                    _.each(tableMeta._fields, (fld) => {
                        if (fld.filter) {
                            if (!_.find(this.$root.filters, {id: Number(fld.id)})) {
                                this.$root.filters.push({id: fld.id, field: fld.field, filter_type: fld.filter_type});
                            }
                        } else {
                            this.$root.filters = _.filter(this.$root.filters, (el) => { return el.id != fld.id; });
                        }
                    });
                }

                //save data and emit events
                if (this.user && this.user.id && !this.user.see_view) {

                    let noswal = tableRow[tableRow._changed_field]
                        && $.inArray(tableRow._changed_field, ['is_gantt_main_group','is_gantt_parent_group']) > -1
                        && _.find(tableMeta._fields, (hdr) => {
                            return !!hdr.is_gantt_parent_group && hdr.id != tableRow.id;
                        });

                    let vis_rows = $.inArray(tableRow._changed_field, ['is_uniform_formula','f_formula']) > -1
                        ? _.map(this.listTableRows, 'id')
                        : [];

                    this.sm_msg_type = 1;
                    promise = new Promise((resolve) => {

                        axios.put('/ajax/settings/data', {
                            table_field_id: tableRow.id,
                            field: tableRow._changed_field,
                            val: tableRow[tableRow._changed_field],
                            visible_rows: vis_rows,
                        }).then(({ data }) => {
                            if ($.inArray(tableRow._changed_field, this.columnSettRadioFields) > -1)
                            {
                                eventBus.$emit('reload-meta-tb__fields');
                            }
                            if ($.inArray(tableRow._changed_field, ['filter','input_type','filter_type']) > -1)
                            {
                                eventBus.$emit('reload-filters', tableMeta.id);
                            }
                            if ($.inArray(tableRow._changed_field, ['is_floating']) > -1)
                            {
                                eventBus.$emit('clear-header-height');
                            }
                            //Gantt
                            if ($.inArray(tableRow._changed_field, ['is_gantt_main_group','is_gantt_parent_group']) > -1 && tableRow[tableRow._changed_field] && !noswal)
                            {
                                Swal('No additional column allowed for levelled header.');
                            }
                            let idx = _.findIndex(tableMeta._fields, {id: tableRow.id});
                            if (idx > -1 && data.fld) {
                                this.$set(tableMeta._fields, idx, {...tableRow, ...data.fld});
                            }
                            resolve(data);
                        }).catch(errors => {
                            Swal('', getErrors(errors));
                        }).finally(() => {
                            this.sm_msg_type = 0;
                        });

                    });

                    if (tableRow._changed_field === 'unit_ddl_id') {
                        axios.put('/ajax/settings/data', {table_field_id: tableRow.id, field: 'unit', val: '',});
                        axios.put('/ajax/settings/data', {table_field_id: tableRow.id, field: 'unit_display', val: '',});
                    }

                } else {
                    if ($.inArray(tableRow._changed_field, ['filter','input_type','filter_type']) > -1)
                    {
                        eventBus.$emit('reload-filters', tableMeta.id);
                    }
                    if ($.inArray(tableRow._changed_field, ['is_floating']) > -1)
                    {
                        eventBus.$emit('clear-header-height');
                    }
                }

                return promise || new Promise((resolve) => { resolve(); });
            },
            //create empty obj
            emptyObject() {
                let obj = {};
                for (let i in this.tableMeta._fields) {
                    if (this.tableMeta._fields[i].f_type === 'Boolean') {
                        obj[ this.tableMeta._fields[i].field ] = this.tableMeta._fields[i].f_default == '1';
                    } else {
                        obj[ this.tableMeta._fields[i].field ] = null;
                    }
                }
                obj._temp_id = uuidv4();
                return obj;
            },
            assignObject(from, to) {
                _.each(from, (val, key) => {
                    if (key !== 'id') {
                        this.$set(to, key, val);
                    }
                });
            },
            justObject(from, to) {
                _.each(from, (val, key) => {
                    if (key[0] !== '_') {
                        to[key] = val;
                    }
                });
            },

            //event is used in Previous handler.
            set_e__used(vue) {
                this.e__used = true;
            },

            //Hover Singleton
            showHoverTooltip(e, hdr) {
                if (this.glob_hover) {
                    clearTimeout(this.glob_hover);
                }
                this.glob_hover = setTimeout(() => {
                    this.hover_html = hdr.tooltip_show ? hdr.tooltip : '';
                    this.hover_show = true;
                    this.hover_left = e.clientX;
                    this.hover_top = e.clientY;
                }, this.hover_delay);
            },
            leaveHoverTooltip() {
                if (this.glob_hover) {
                    clearTimeout(this.glob_hover);
                }
            },

            //Link Preview Singleton
            showLinkPreview(e, link, metaHeader, metaRow, globalMeta) {
                if (this.glob_linkprev) {
                    clearTimeout(this.glob_linkprev);
                }
                this.glob_linkprev = setTimeout(() => {
                    this.linkprev_meta = globalMeta;
                    this.linkprev_meta_header = metaHeader;
                    this.linkprev_meta_row = metaRow;
                    this.linkprev_object = link;
                    this.linkprev_left = e.clientX;
                    this.linkprev_top = e.clientY;
                }, this.linkprev_delay);
            },
            leaveLinkPreview() {
                if (this.glob_linkprev) {
                    clearTimeout(this.glob_linkprev);
                }
            },

            //Availability of Addons.
            AddonAvailableToUser(tableMeta, code, value) {
                return (this.userHasAddon(code) || this.user.see_view) // User has subscription OR he is viewing 'View'
                    && (tableMeta._is_owner || this.findAddonRight(tableMeta, code, value)); // Owner OR has Permission rights
            },
            userHasAddon(code) {
                let res = false;
                if (this.user._subscription) {
                    res = _.findIndex(this.user._subscription._addons, {code: code}) > -1;
                }
                return res;
            },
            findAddonRight(tableMeta, code, value, field) {
                field = field || 'type';
                value = value || 'view';
                let present = false;
                _.each(tableMeta._current_right._addons, (element) => {
                    if (element.code === code && element._link['type'] == value) {
                        present = true;
                    }
                });
                return present;
            },
            saveDiscourse() {
                try {
                    document.cookie = '_discourse_sso=' + document.getElementById('discourse_iframe').contentWindow.location.search;
                } catch (e) {
                    console.log(e);
                }
            },
            attachFileToRow(tRow, tHeader, uFile) {
                let row_images = tRow['_images_for_'+tHeader.field];
                let row_files = tRow['_files_for_'+tHeader.field];

                //add uploaded file to the row
                if (uFile.is_img) {
                    (row_images ? row_images.push(uFile) : this.$set(tRow, '_images_for_'+tHeader.field, [uFile]));
                } else {
                    (row_files ? row_files.push(uFile) : this.$set(tRow, '_files_for_'+tHeader.field, [uFile]));
                }

                if (tHeader) {
                    tRow[tHeader.field] = uFile.filepath + uFile.filename;
                }
            },
            getImagesFromClipboard(e) {
                let images = [];
                _.each(e.clipboardData ? e.clipboardData.items : [], (item) => {
                    if (item.type.indexOf('image') != -1) {
                        images.push(item.getAsFile());
                    }
                });
                return images;
            },
            //COLORS
            loadColorPalette() {
                let palette = readLocalStorage('user_color_palette');
                palette = palette ? JSON.parse(palette) : [];
                _.each(this.color_palette, (val, key) => {
                    this.color_palette[key] = palette[key] || val;
                });
            },
            saveColorToPalette(clr, idx) {
                if (idx) {
                    this.color_palette[idx] = clr;
                } else {
                    _.each(this.color_palette, (val, key) => {
                        if (!val && clr) {
                            this.color_palette[key] = clr;
                            clr = '';
                        }
                    });
                }
                setLocalStorage('user_color_palette', JSON.stringify(this.color_palette));
            },
        },
        created() {
            //inits
            let elem_app = document.getElementById('app');
            this.app_url = elem_app ? elem_app.dataset.app_url : '';
            this.clear_url =  elem_app ? elem_app.dataset.clear_url : '';
            this.app_domain =  elem_app ? elem_app.dataset.app_domain : '';
            window.vue_app_domain = this.app_domain;

            //auto logout on front-end
            this.refreshAutologout();
            setInterval(() => {
                this.checkAutologout();
            }, this.autologout_delay);
            eventBus.$on('global-click', (e) => {
                this.refreshAutologout();
            });
            //-----

            eventBus.$on("print-table-data", (tableHeaders, tableRows, allShowed)=>{
                this.printHeaders = tableHeaders;
                this.printRows = tableRows;
                this.allShowed = !!allShowed;
                this.$nextTick(function () {
                    window.print();
                });
            });

            document.addEventListener('mousedown', (e) => {
                eventBus.$emit('global-click', e);
                this.lastMouseClick.clientX = e.clientX;
                this.lastMouseClick.clientY = e.clientY;
            });
            document.addEventListener('contextmenu', (e) => {
                eventBus.$emit('global-click', e);
                this.lastMouseClick.clientX = e.clientX;
                this.lastMouseClick.clientY = e.clientY;
            });
            document.addEventListener('keydown', (e) => {
                this.e__used = false;
                eventBus.$emit('global-keydown', e);
            });

            this.loadColorPalette();

            if (this.cellHeight > 5) {
                this.changeCellHeight(1);
            }

            //user state check
            setInterval(() => {
                if (!localStorage.getItem('no_ping') && !this.user.is_force_guested) {
                    //check if user was logout from another terminal
                    axios.post('/user_state', {
                        app_user_id: this.user.id || 0,
                    }).then(({ data }) => {
                        if (data.changed) {
                            this.debug = true;
                            window.location.reload();
                            // Swal('', 'User is changed').then(() => {
                            //     window.location.reload();
                            // });
                        }
                    });
                }
            }, this.ping_delay);
        },
        mounted() {
            //View is not active for Public or User.
            if (this.user.view_all && !this.user.view_all._for_user_or_active) {
                Swal({
                    text: 'The View you are trying to access is not available, either due to you are not given the permission or the view does not have Public Access turned ON.',
                    //timer: 10000
                }).then(() => {
                    window.location = '/data';
                });
            }

            this.discourse_login_iframe = decodeURIComponent(Cookies.get('_discourse_login') || '');
            if (this.discourse_login_iframe) {
                document.cookie = '_discourse_login=';
            }
        }
    });
    window.vueRootApp = app;

});