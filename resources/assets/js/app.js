
/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

import { RecycleScroller } from 'vue-virtual-scroller';
Vue.component('recycle-scroller', RecycleScroller);

Vue.component('user-plans', require('./components/NavbarPopups/UserPlans.vue').default);
Vue.component('invite-module', require('./components/NavbarPopups/InviteModule.vue').default);
Vue.component('resources-popup', require('./components/NavbarPopups/ResourcesPopup.vue').default);
Vue.component('theme-button', require('./components/Buttons/ThemeButton.vue').default);
Vue.component('folder-icons-path', require('./components/MainApp/Object/Folder/FolderIconsPath.vue').default);
Vue.component('navbar-data', require('./components/NavbarData.vue').default);
Vue.component('layout-data', require('./components/MainApp/LayoutData.vue').default);
Vue.component('twilio-test-popup', require('./components/MainApp/Object/Table/Twilio/Popup/TwilioTestPopup.vue').default);
Vue.component('link-preview-block', require('./components/CommonBlocks/LinkPreviewBlock.vue').default);
Vue.component('hover-block', require('./components/CommonBlocks/HoverBlock.vue').default);
Vue.component('view-pass-block', require('./components/CommonBlocks/ViewPassBlock.vue').default);
Vue.component('main-app-wrapper', require('./components/MainAppWrapper.vue').default);
Vue.component('saving-message', require('./components/CommonBlocks/SavingMessage.vue').default);
Vue.component('auth-forms', require('./components/_AuthForms/AuthForms.vue').default);
Vue.component('homepage', require('./components/Homepage.vue').default);
Vue.component('static-pages', require('./components/Statics/StaticPages.vue').default);
Vue.component('print-table', require('./components/CustomTable/PrintTable.vue').default);
Vue.component('table-request-wrapper', require('./components/RequestDataWrapper.vue').default);
Vue.component('single-record-wrapper', require('./components/SingleRecordWrapper.vue').default);
Vue.component('single-td-field', require('./components/CommonBlocks/SingleTdField.vue').default);
Vue.component('get-started', require('./components/Statics/GetStarted.vue').default);
Vue.component('inline-linked-tabs', require('./components/CommonBlocks/Link/InlineLinkedTabs.vue').default);
Vue.component('formula-helper', require('./components/CustomCell/InCell/FormulaHelper.vue').default);
Vue.component('vertical-table', require('./components/CustomTable/VerticalTable.vue').default);
Vue.component('link-pop-up', require('./components/CustomPopup/LinkPopUp.vue').default);
Vue.component('slot-popup', require('./components/CustomPopup/SlotPopup.vue').default);
Vue.component('left-menu-tree-accordion-item', require('./components/MainApp/LeftMenu/LeftMenuTreeAccordionItem.vue').default);

Vue.component('my-apps-page', require('./Applications/MyAppsPage.vue').default);
Vue.component('list-tablda-apps', require('./Applications/ListTabldaApps.vue').default);
Vue.component('risa3d-form', require('./Applications/Risa3dForm.vue').default);
Vue.component('risa3d-remover-form', require('./Applications/Risa3dRemoverForm.vue').default);
Vue.component('stim-wid-form', require('./Applications/StimWid/StimWidForm.vue').default);
Vue.component('simple-messager', require('./Applications/SimpleMessager.vue').default);
Vue.component('stim-ma-json', require('./Applications/StimMaJson/StimMaJson.vue').default);
Vue.component('stim-iframe-app', require('./Applications/StimIframeApp/StimIframeApp.vue').default);
Vue.component('stim-calculate-loads', require('./Applications/StimCalculateLoads/StimCalculateLoads.vue').default);
Vue.component('payment-processing-page', require('./Applications/PaymentProcessing/PaymentProcessingPage.vue').default);
Vue.component('eri-parser-writer-settings', require('./Applications/EriParserWriter/EriParserWriterSettings.vue').default);

import ThemeStyleMixin from './global_mixins/ThemeStyleMixin.vue';
import AutologoutMixin from './global_mixins/AutologoutMixin.vue';

import {SpecialFuncs} from './classes/SpecialFuncs';
import {DataReverser} from './classes/DataReverser';
import {RefCondHelper} from "./classes/helpers/RefCondHelper";
import {Validator} from "./classes/Validator";

export const eventBus = new Vue();

export const columnSettRadioFields = [
    'is_lat_field',
    'is_long_field',
    'is_info_header_field',
    'is_info_header_value',
    'map_find_street_field',
    'map_find_city_field',
    'map_find_state_field',
    'map_find_county_field',
    'map_find_zip_field',
];

window.addEventListener("message", function(event) {
    if (event.data && event.data.event_name === 'close-application') {
        eventBus.$emit('global-close-application', event.data.app_code);
    }
    if (event.data && event.data.event_name === 'docs-path') {
        eventBus.$emit('global-docs-path-updated', event.data.path);
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
                recaptcha_key: '',
                overviewFormatWaiting: false,
                ddlHeight: Math.max(window.innerHeight * 0.35, 150),
                usedTrps: [],
                ping_delay: 10000,
                version_hash_delay: 3000,

                last_checked_id: null,
                all_checked_rows: false,
                discourse_login_iframe: '',
                e__used: false,
                data_reverser: new DataReverser(),
                debug: false,
                dcr_notification_shown: false,
                is_dcr_page: null,//DCR id
                is_srv_page: null,//SRV record id
                is_mrv_page: null,//MRV record id
                dcrPivotFields: [],
                intlTelInput: null,
                telCacher: {},

                tablda_highlights: [], // arr of objects: {table_id: null, row_id: null}

                lastMouseClick: {
                    clientX: null,
                    clientY: null,
                },
                sm_msg_type: undefined,
                prevent_cell_edit: false, // user cannot edit cell during saving process
                global_no_edit: false,

                //#app_avail_formulas
                availRowSumFormulas: ['count','countunique','sum','min','max','mean','avg','var','std'],

                labels: {},
                guestListingFields: {},
                //TABLE_FIELDS
                availableMapColumns: [
                    'name',
                    'map_find_street_field',
                    'map_find_city_field',
                    'map_find_state_field',
                    'map_find_county_field',
                    'map_find_zip_field',
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
                    'is_inherited_tree',
                    'mirror_rc_id',
                    'mirror_field_id',
                    'mirror_part',
                    'mirror_one_value',
                    'mirror_editable',
                    'mirror_edit_component',
                    'fetch_source_id',
                    'fetch_by_row_cloud_id',
                    'fetch_one_cloud_id',
                    'fetch_uploading',
                    'has_copy_prefix',
                    'copy_prefix_value',
                    'has_copy_suffix',
                    'copy_suffix_value',
                    'has_datetime_suffix',
                    'f_default',
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
                    'header_triangle',
                    'validation_rules',
                ],
                availableNotOwnerDisplayColumns: [
                    'name',
                    'filter',
                    'filter_type',
                    'filter_search',
                    'popup_header',
                    'popup_header_val',
                    'is_floating',
                    'unit_display',
                    'min_width',
                    'max_width',
                    'width',
                    'show_history',
                    'col_align',
                    'show_zeros',
                    'image_fitting',
                    'fill_by_asterisk',
                ],
                availablePopupDisplayColumns: [
                    'name',
                    'fld_popup_shown',
                    'fld_display_name',
                    'fld_display_value',
                    'fld_display_border',
                    'fld_display_header_type',
                    'is_topbot_in_popup',
                    'verttb_he_auto',
                    'verttb_cell_height',
                    'verttb_row_height',
                    'is_show_on_board',
                    'is_default_show_in_popup',
                    'is_table_field_in_popup',
                    'is_hdr_lvl_one_row',
                    'width_of_table_popup',
                    'is_start_table_popup',
                    'form_row_spacing',
                    'pop_tab_name',
                    'pop_tab_order',
                    'section_header',
                    'section_font',
                    'section_size',
                    'section_align_h',
                    'section_align_v',
                    'section_height',
                ],
                availableOthersColumns: [
                    'name',
                    'markerjs_annotations',
                    'markerjs_cropro',
                    'markerjs_savetype',
                    'twilio_google_acc_id',
                    'twilio_sendgrid_acc_id',
                    'twilio_sms_acc_id',
                    'twilio_voice_acc_id',
                    'twilio_sender_name',
                    'twilio_sender_email',
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
                reportTemplates: [],
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
                otherTableMetas: {},
                styleCaches: {},

                selectedAddon: {
                    code: '',
                    name: '',
                    sub_name: '',
                    sub_id: '',
                },
                addonFilters: {
                    'kanban': {},
                    'gantt': {},
                    'dcr': {},
                    'map': {},
                    'bi': {},
                    'alert': {},
                    'email': {},
                    'calendar': {},
                    'twilio': {},
                    'tournament': {},
                    'simplemap': {},
                    'grouping': {},
                    'report': {},
                    'ai': {},
                },
                filters: [],
                rowPerPage: 0,
                oldTbName: '',
                tableMeta: {},
                listTableRows: [],
                favoriteTableRows: [],
                prevent_cell_keyup: false,
                reportBiSaverMetas: {},

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
                twilio_help: 'To obtain a Twilio SMS/Voice ' +
                    '<a target="_blank" href="https://sendgrid.com/docs/ui/account-and-settings/api-keys/#creating-an-api-key">API Key</a>,' +
                    '<br>you need to register an account at <a target="_blank" href="https://www.twilio.com/">twilio.com</a>' +
                    '<br>and create one.',

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
            safeName($name) {
                $name = String($name).trim().replace(newRegexp('[\\s]+'), ' ');
                return String($name).replace(newRegexp('[^\\p{L}\\d\\(\\)\\-\\.,_ ]'), '');
            },
            tablesZidxIncrease() {
                if (this.tablesZidx < 1400) {
                    this.tablesZidx = 1400;
                }
                this.tablesZidx += 10;
            },
            tablesZidxDecrease() {
                this.tablesZidx -= 10;
                if (this.tablesZidx < 1400) {
                    this.tablesZidx = 1400;
                }
            },
            inArray(item, array) {
                return $.inArray(item, array) > -1;
            },
            inArraySys(item, array) {
                array = array || [];
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
            fileOnServer(file_obj) {
                return String(this.fileUrl(file_obj)).indexOf(location.origin) > -1;
            },
            fileUrl(file_obj, thumb_size) {
                file_obj = file_obj || {};
                if (file_obj.remote_link && !file_obj.local_thumb) {
                    return file_obj.remote_link;
                }
                let url = file_obj.local_thumb || file_obj.filehash || file_obj.url;
                if (String(url).startsWith('http')) {
                    return url;
                }
                return location.origin
                    + '/file/'
                    + url
                    + '?s='
                    + (this.user.view_hash || this.user._is_folder_view || this.user._dcr_hash || this.user._srv_hash || '')
                    + (thumb_size ? '&thumb='+thumb_size : '');
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
            changeRightMenuWi(val, tableMeta) {
                this.chckMetaSetts(tableMeta);
                if (tableMeta && tableMeta._cur_settings) {
                    tableMeta._cur_settings.right_menu_width = val;
                }
                this.updateTbUserSetts(tableMeta, 'local_right_menu_width', val);
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
                return name ? _.uniq( String(name).split(',') ).join(' ') : '';
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
                    if (header && header.f_required && ['Radio'].indexOf(header.f_type) === -1 && !fld && fld !== 0) {
                        emptyFields.push(tableMeta._fields[idx].name);
                    }
                });

                //check required fields
                if (emptyFields.length) {
                    let req_msg = [];
                    _.each(emptyFields, (fld) => {
                        req_msg.push( 'Required "'+fld+'" is empty.' );
                    });
                    Swal({ title: 'Info', html: spec_message || req_msg.join('<br>') });
                }

                let validErr = this.checkAllValidations(tableMeta, fields);

                return !emptyFields.length && !validErr;
            },
            checkAllValidations(tableMeta, tableRow) {
                let msgs = [];
                _.each(tableMeta._fields, (tableHeader) => {
                    let err = Validator.check(tableHeader, tableRow[tableHeader.field]);
                    if (err) {
                        msgs.push(tableHeader.name + ': ' + err);
                    }
                });
                if (msgs.length) {
                    let string = '<b>The input does not meet following Validation(s):</b>';
                    string += '<ul style="padding-inline-start: 55px; text-align: left;"><li>';
                    string += msgs.join('</li><li>');
                    string += '</li></ul>';
                    Swal({ title: 'Info', html: string });
                }
                return !!msgs.length;
            },
            checkSingleValidation(tableHeader, tableRow) {
                let err = Validator.check(tableHeader, tableRow[tableHeader.field]);
                if (err) {
                    let string = '<b>The input does not meet following Validation(s):</b>';
                    string += '<ul style="padding-inline-start: 55px; text-align: left;"><li>'+tableHeader.name + ': ' + err+'</li></ul>';
                    Swal({ title: 'Info', html: string });
                }
                return !!err;
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
            getUserOneStr(value, row, header, usr_flds, prefix = 'user_fld') {
                let usr = this.smallUserStr(row, header, value, true);
                let res = this.getUserSimple(usr, usr_flds, prefix);
                if (!res) {
                    res = value && (!isNaN(value) || value[0] == '_')
                        ? 'loading...'
                        : value;
                }
                return res;
            },
            getUserSimple(user, usr_flds, prefix = 'user_fld') {
                if (!user) {
                    return '';
                }

                let unadeded = false;
                let fsize = this.themeTextFontSize;
                let res = this.getUsrAvatar(user) && (!usr_flds || usr_flds[prefix+'_show_image'])
                    ? '<img src="'+this.getUsrAvatar(user)+'" width="'+fsize+'" height="'+fsize+'">&nbsp;'
                    : '';

                if (!usr_flds || usr_flds[prefix+'_show_first']) {
                    res += user.first_name || '';
                }
                if (user.first_name && (!usr_flds || usr_flds[prefix+'_show_last'])) {
                    res += user.last_name ? ' '+user.last_name : '';
                }

                res += user.email && (!usr_flds || usr_flds[prefix+'_show_email'])
                    ? ' ('+user.email+')'
                    : '';

                res += !unadeded && user.username && (!usr_flds || usr_flds[prefix+'_show_username'])
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
                    Swal('Info','Copied');
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
                        Swal('Info', getErrors(errors));
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

                    this.sm_msg_type = 1;
                    promise = new Promise((resolve) => {

                        axios.put('/ajax/settings/data', {
                            table_field_id: tableRow.id,
                            field: tableRow._changed_field,
                            val: tableRow[tableRow._changed_field],
                        }).then(({ data }) => {
                            if ($.inArray(tableRow._changed_field, this.columnSettRadioFields) > -1)
                            {
                                eventBus.$emit('reload-meta-tb__fields');
                            }
                            if ($.inArray(tableRow._changed_field, ['filter','input_type','filter_type','filter_search']) > -1)
                            {
                                eventBus.$emit('reload-filters', tableMeta.id);
                            }
                            if ($.inArray(tableRow._changed_field, ['is_floating']) > -1)
                            {
                                eventBus.$emit('clear-header-height');
                            }
                            let idx = _.findIndex(tableMeta._fields, {id: tableRow.id});
                            if (idx > -1 && data.fld) {
                                this.$set(tableMeta._fields, idx, {...tableRow, ...data.fld});
                            }
                            RefCondHelper.setMirrorsAndFormulas(tableMeta);
                            resolve(data);
                        }).catch(errors => {
                            Swal('Info', getErrors(errors));
                        }).finally(() => {
                            this.sm_msg_type = 0;
                        });

                    });

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

                //front-end formula recalculation
                if ($.inArray(tableRow._changed_field, ['is_uniform_formula','f_formula']) > -1) {
                    eventBus.$emit('table-formulas-recalculate', tableMeta.id, tableRow.id, tableRow[tableRow._changed_field]);
                }

                return promise || new Promise((resolve) => { resolve(); });
            },
            updateTable(tableMeta, prop_name) {
                if (this.user.see_view) {
                    return;//Changes are temporal in MRV!
                }
                this.sm_msg_type = 1;

                let data = {
                    table_id: tableMeta.id,
                    _theme: {},
                    _cur_settings: {},
                    _changed_prop_name: prop_name,
                };
                this.justObject(tableMeta, data);
                this.justObject(tableMeta._theme, data._theme);
                this.justObject(tableMeta._cur_settings, data._cur_settings);

                return axios.put('/ajax/table', data).then(({ data }) => {
                    if (in_array(prop_name, [
                        'unit_conv_is_active','unit_conv_by_user','unit_conv_table_id','unit_conv_from_fld_id',
                        'unit_conv_to_fld_id','unit_conv_operator_fld_id','unit_conv_factor_fld_id',
                        'unit_conv_formula_fld_id','unit_conv_formula_reverse_fld_id',
                    ])) {
                        tableMeta.__unit_convers = data.__unit_convers || [];
                    }
                    if (prop_name === 'name') {
                        this.$root.user.memutree_hash = null;//reload menutree in 10 sec (MainAppWrapper)

                        $('head title').html(this.$root.app_name+': '+tableMeta.name);

                        let path = window.location.href.replace(this.oldTbName, tableMeta.name);
                        window.history.pushState(tableMeta.name, tableMeta.name, path);
                        this.oldTbName = tableMeta.name;
                    }
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    this.sm_msg_type = 0;
                });
            },
            //create empty obj
            emptyObject() {
                return SpecialFuncs.emptyRow(this.tableMeta);
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
                if (hdr.tooltip_show && hdr.tooltip) {
                    this.glob_hover = setTimeout(() => {
                        this.hover_html = hdr.tooltip;
                        this.hover_show = true;
                        this.hover_left = e.clientX;
                        this.hover_top = e.clientY;
                    }, this.hover_delay);
                }
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

            getPopUpHeader(tableMeta, tableRow) {
                let headers = tableMeta._fields;
                let res = [];
                _.each(headers, (hdr) => {
                    if (hdr.popup_header || hdr.popup_header_val) {
                        let row_value = tableRow
                            ? SpecialFuncs.showhtml(hdr, tableRow, tableRow[hdr.field], tableMeta)
                            : '';
                        let ar = hdr.popup_header ? [this.uniqName(hdr.name)] : [];
                        if (hdr.popup_header_val) { ar.push(row_value) }
                        res.push( ar.join(': ') );
                    }
                });
                return res.length ? res.join(' | ') : '';
            },

            //Availability of Addons.
            AddonAvailableToUser(tableMeta, code, value) {
                let curDomain = window.location.host.split('.')[0];
                let canSeeAdn = this.is_dcr_page || this.is_srv_page || this.is_mrv_page; // All addons are available for 'SRV/MRV/DCR' (without subscription)
                return (this.userHasAddon(code) || canSeeAdn || curDomain === 'public') // User has subscription OR he is viewing 'SRV/MRV/DCR'
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
                let addons = tableMeta && tableMeta._current_right ? tableMeta._current_right._addons : [];
                _.each(addons || [], (element) => {
                    if (element.code === code && element._link['type'] == value) {
                        present = true;
                    }
                });
                return present;
            },
            anyAddon(tableMeta) {
                return tableMeta.add_bi
                    || tableMeta.add_map
                    || tableMeta.add_request
                    || tableMeta.add_alert
                    || tableMeta.add_kanban
                    || tableMeta.add_gantt
                    || tableMeta.add_email
                    || tableMeta.add_calendar
                    || tableMeta.add_twilio
                    || tableMeta.add_tournament
                    || tableMeta.add_simplemap
                    || tableMeta.add_grouping
                    || tableMeta.add_ai
                    || tableMeta.add_report;
            },
            saveDiscourse() {
                try {
                    document.cookie = '_discourse_sso=' + document.getElementById('discourse_iframe').contentWindow.location.search;
                } catch (e) {
                    console.log(e);
                }
            },
            attachFileToRow(tRow, tHeader, uFile, replace_file_id) {
                if (!tRow['_images_for_'+tHeader.field]) {
                    this.$set(tRow, '_images_for_'+tHeader.field, []);
                }
                if (!tRow['_files_for_'+tHeader.field]) {
                    this.$set(tRow, '_files_for_'+tHeader.field, []);
                }

                let files_array = uFile.is_img ? tRow['_images_for_'+tHeader.field] : tRow['_files_for_'+tHeader.field];

                //add uploaded file to the row
                if (replace_file_id) {
                    let idx = _.findIndex(files_array, {id: Number(replace_file_id)});
                    files_array.splice(idx, 1, uFile);
                } else {
                    files_array.push(uFile);
                }

                if (tHeader) {
                    tRow[tHeader.field] = uFile.filepath + uFile.filename;
                }
            },
            getImagesFromClipboard(e) {
                return this.getFilesFromClipboard(e, ['image/bmp','image/gif','image/jpeg','image/png','image/svg+xml','image/tiff','image/webp']);
            },
            getDocxFromClipboard(e) {
                return this.getFilesFromClipboard(e, ['text/plain', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']);
            },
            getFilesFromClipboard(e, mimes) {
                let files = [];
                _.each(e.clipboardData ? e.clipboardData.items : [], (item) => {
                    if (mimes.indexOf(item.type) > -1) {
                        files.push(item.getAsFile());
                    }
                });
                return files;
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
            //OTHERS
            oneFilterSelected(ext_filters, need_show) {
                let justOneValue = null;
                _.each(_.sortBy(ext_filters || this.filters, 'applied_index'), (filter) => {
                    if (filter.applied_index && filter.filter_type !== 'range') {
                        let valu = '';
                        let len = _.reduce(filter.values, (res, vl) => {
                            if (vl.checked) {
                                valu = need_show ? vl.show : vl.val;
                                return res + 1;
                            } else {
                                return res;
                            }
                        }, 0);

                        if (len == 1 && !justOneValue) {
                            justOneValue = valu;
                        }
                    }
                });
                return justOneValue;
            },
            telFormat(v) {
                if (!v || String(v).match(/[^+0-9;]/gi)) {
                    return '';
                }
                if (String(v).charAt(0) !== '+') {
                    v = '+' + v;
                }

                if (!this.telCacher[v] && this.intlTelInput) {
                    this.intlTelInput.setNumber(v);
                    let img = '';
                    if (this.intlTelInput.selectedCountryData) {
                        img = '<div class="d-inline-block iti__flag iti__'+this.intlTelInput.selectedCountryData.iso2+'"></div> ';
                    }
                    this.telCacher[v] = img + this.intlTelInput.getNumber(1);
                }
                return this.telCacher[v] || v;
            },
            jiraReloadProjects() {
                this.$nextTick(() => {
                    eventBus.$emit('jira-load-projects');
                });
            },
            salesforceReloadObjects() {
                this.$nextTick(() => {
                    eventBus.$emit('salesforce-load-objects');
                });
            },
            addonCanPermisEdit(tableMeta, addonMeta, rightsKey) {
                if (tableMeta._is_owner) {
                    return true;
                }
                return !!(
                    tableMeta._current_right
                    && tableMeta._current_right.permis_ids
                    && addonMeta
                    && addonMeta[rightsKey]
                    && _.find(addonMeta[rightsKey], (r) => {
                        return r.can_edit && this.inArray(r.table_permission_id, tableMeta._current_right.permis_ids);
                    })
                );
            },
            addonCanEditGeneral(tableMeta, code) {
                if (tableMeta._is_owner) {
                    return true;
                }
                return !!(
                    tableMeta._current_right
                    && tableMeta._current_right._addons
                    && _.find(tableMeta._current_right._addons, (a) => {
                        return a.code === code && a._link && a._link.type === 'edit';
                    })
                );
            },
            allIsAccordion(subTree) {
                let all = true;
                _.each(subTree, (folder) => {
                    all = all && folder && folder['li_attr'] && folder['li_attr']['data-object'] && folder['li_attr']['data-object']['menutree_accordion_panel'];
                });
                return all && subTree.length;
            },
            //extras: {page:int, sort:array, hidden_columns:array, user_id:int, }
            getTableViewData(tableMeta, extras) {
                let order_columns = _.map(tableMeta._fields, (el,order) => {
                    return {
                        id: el.id,
                        field: el.field,
                        order: order,
                        width: el.width,
                    };
                });

                let panels_preset = {
                    top: $('#main_navbar').is(':visible'),
                    right: this.isRightMenu,
                    left: this.isLeftMenu,
                };

                return JSON.stringify({
                    user_id: extras.user_id,
                    table_id: tableMeta.id,
                    page: extras.page,
                    rows_per_page: tableMeta.rows_per_page,
                    sort: extras.sort,
                    search_words: extras.search_keywords,
                    search_columns: extras.search_columns,
                    row_id: extras.search_direct_row_id,
                    applied_filters: this.filters,
                    hidden_columns: extras.hidden_columns,
                    order_columns: order_columns,
                    hidden_row_groups: tableMeta.__hidden_row_groups,
                    panels_preset: panels_preset,
                });
            },
            getViewPartOption(code) {
                switch (code) {
                    case 'tab-list-view': return {val: 'tab-list-view', show: 'Primary View'};
                    case 'tab-favorite': return {val: 'tab-favorite', show: 'Favorite'};
                    case 'tab-settings': return {val: 'tab-settings', show: 'Settings General'};
                    case 'tab-settings-cust': return {val: 'tab-settings-cust', show: 'Settings Customizable'};
                    case 'tab-map-add': return {val: 'tab-map-add', show: 'Addon GSI'};
                    case 'tab-bi-add': return {val: 'tab-bi-add', show: 'Addon BI'};
                    case 'tab-dcr-add': return {val: 'tab-dcr-add', show: 'Addon DCR'};
                    case 'tab-alert-add': return {val: 'tab-alert-add', show: 'Addon ANA'};
                    case 'tab-kanban-add': return {val: 'tab-kanban-add', show: 'Addon Kanban'};
                    case 'tab-gantt-add': return {val: 'tab-gantt-add', show: 'Addon Gantt'};
                    case 'tab-email-add': return {val: 'tab-email-add', show: 'Addon Email'};
                    case 'tab-calendar-add': return {val: 'tab-calendar-add', show: 'Addon Calendar'};
                    case 'tab-twilio-add': return {val: 'tab-twilio-add', show: 'Addon Twilio'};
                    case 'tab-tournament-add': return {val: 'tab-tournament-add', show: 'Addon Brackets'};
                    case 'tab-simplemap-add': return {val: 'tab-simplemap-add', show: 'Addon TMap'};
                    case 'tab-grouping-add': return {val: 'tab-grouping-add', show: 'Addon Grouping'};
                    case 'tab-report-add': return {val: 'tab-report-add', show: 'Addon Report'};
                    case 'tab-ai-add': return {val: 'tab-ai-add', show: 'Addon AI'};
                    default: return null;
                }
            },
            actionForMassCheckRows(tableRow) {
                this.all_checked_rows = false;
                let cmdOrCtrl = window.event.metaKey || window.event.ctrlKey;
                if (cmdOrCtrl && this.last_checked_id && tableRow._checked_row) {
                    let between = false;
                    _.each(this.allRows, (row) => {
                        if ((this.last_checked_id == row.id) || (tableRow.id == row.id)) {
                            between = !between;
                        } else {
                            if (between) {
                                row._checked_row = true;
                            }
                        }
                    });
                } else {
                    this.last_checked_id = tableRow._checked_row ? tableRow.id : null;
                }
            },
            //reCAPTCHA
            protectFormSubmitByCaptha(e, formId) {
                if (! window.grecaptcha || ! this.recaptcha_key) {
                    return;
                }

                e.preventDefault();
                window.grecaptcha.ready(() => {
                    window.grecaptcha.execute(this.recaptcha_key, {action: 'submit'}).then((token) => {
                        document.getElementById(formId).submit();
                    });
                });
            },
            captchaSkipped() {
                return ! window.grecaptcha
                    || ! this.$root.recaptcha_key
                    || this.user.id
                    || this.user.captcha_checked;
            },
            ownerOf(tableMeta) {
                let uid = this.user.id || this.user._pre_id;
                return tableMeta._is_owner || tableMeta.user_id == uid;
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
        },
        mounted() {
            //View is not active for Public or User.
            if (this.user.view_all && !this.user.view_all._for_user_or_active) {
                Swal({
                    title: 'Info',
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

            if (!localStorage.getItem('no_ping') && this.user.id) {
                axios.post('/ping', {
                    app_user: { id: this.user.id || 0 },
                    pathname: window.location.pathname,
                }).catch((err) => {
                    window.location = '/logout';
                });
            }
        }
    });
    window.vueRootApp = app;

});