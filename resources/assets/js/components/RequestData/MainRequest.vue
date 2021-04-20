<template>
    <div id="tables" class="full-frame" :style="{overflow: scrlFlow ? 'auto' : 'hidden'}">
        <template v-if="tablePermission.pass && !pass">

            <request-pass-pop-up :table_permission_id="tablePermission.id" @pass-popup-close="setPass"></request-pass-pop-up>

        </template>
        <template v-else>

            <div v-if="$root.tableMeta.id" class="dcr_wrap flex flex--col" :style="{backgroundColor: getBgCol('dcr_sec_bg_bot')}">

                <img v-if="tablePermission.dcr_sec_bg_img"
                     class="dcr-title--item item__img"
                     :src="$root.fileUrl({url:tablePermission.dcr_sec_bg_img})"
                     style="z-index: auto;position: fixed;"
                     :style="{
                        height: (['Height','Fill'].indexOf(tablePermission.dcr_sec_bg_img_fit) > -1 ? '100%' : null),
                        width: (['Width','Fill'].indexOf(tablePermission.dcr_sec_bg_img_fit) > -1 ? '100%' : null),
                        objectFit: (tablePermission.dcr_sec_bg_img_fit === 'Fill' ? 'cover' : null),
                     }"
                />

                <div class="navbar navbar-default no-border"
                     v-if="(tablePermission.dcr_title || tablePermission.dcr_title_bg_img) && !is_embed"
                     :style="{ backgroundColor: getBgCol('dcr_sec_bg_top'), }"
                >
                    <div class="flex flex--center-h">
                        <div class="dcr-title"
                             :style="{
                                width: (tablePermission.dcr_title_width+'px' || null),
                                height: (tablePermission.dcr_title_height+'px' || null),
                                backgroundColor: getBgCol('dcr_title_bg_color', true),
                                boxShadow: getBgCol('dcr_title_bg_color', true) ? getBoxShad : null,
                                borderTopLeftRadius: (tablePermission.dcr_form_line_type == 'space' ? tablePermission.dcr_form_line_radius+'px' : ''),
                                borderTopRightRadius: (tablePermission.dcr_form_line_type == 'space' ? tablePermission.dcr_form_line_radius+'px' : ''),
                                borderBottomLeftRadius: (tablePermission.dcr_form_line_type == 'space' ? tablePermission.dcr_form_line_radius+'px' : ''),
                                borderBottomRightRadius: (tablePermission.dcr_form_line_type == 'space' ? tablePermission.dcr_form_line_radius+'px' : ''),
                            }"
                        >
                            <h1 class="hid item__h1" :style="fontStyleObj('dcr_title_font')" v-html="tablePermission.dcr_title"></h1>
                            <h1 class="dcr-title--item item__h1" :style="fontStyleObj('dcr_title_font')" v-html="tablePermission.dcr_title"></h1>
                            <img v-if="tablePermission.dcr_title_bg_img"
                                 class="dcr-title--item item__img"
                                 :src="$root.fileUrl({url:tablePermission.dcr_title_bg_img})"
                                 :style="{
                                    height: (['Height','Fill'].indexOf(tablePermission.dcr_title_bg_fit) > -1 ? '100%' : null),
                                    width: (['Width','Fill'].indexOf(tablePermission.dcr_title_bg_fit) > -1 ? '100%' : null),
                                    objectFit: (tablePermission.dcr_title_bg_fit === 'Fill' ? 'cover' : null),
                                 }"
                            />
                        </div>
                    </div>
                    <div class="dcr-top-msg" :style="{
                            width: viewTable ? '100%' : (tablePermission.dcr_form_width || 600)+'px',
                            margin: 'auto',
                            boxShadow: getBoxShad,
                            borderTopLeftRadius: tablePermission.dcr_form_line_radius+'px',
                            borderTopRightRadius: tablePermission.dcr_form_line_radius+'px',
                            borderBottomLeftRadius: (tablePermission.dcr_form_line_type == 'space' ? tablePermission.dcr_form_line_radius+'px' : ''),
                            borderBottomRightRadius: (tablePermission.dcr_form_line_type == 'space' ? tablePermission.dcr_form_line_radius+'px' : ''),
                     }">
                        <div class="navbar-default"
                             :style="{
                                    backgroundColor: formBgTransp(),
                                    borderTopLeftRadius: tablePermission.dcr_form_line_radius+'px',
                                    borderTopRightRadius: tablePermission.dcr_form_line_radius+'px',
                                    borderBottomLeftRadius: (tablePermission.dcr_form_line_type == 'space' ? tablePermission.dcr_form_line_radius+'px' : ''),
                                    borderBottomRightRadius: (tablePermission.dcr_form_line_type == 'space' ? tablePermission.dcr_form_line_radius+'px' : ''),
                             }"
                        >
                            <div :style="fontStyleObj('dcr_form_message')" v-html="tablePermission.dcr_form_message"></div>
                        </div>
                        <div class="flex navbar navbar-default fit-content" :class="flexCenterClass" v-if="!tablePermission.one_per_submission">
                            <div v-if="table_id && $root.tableMeta && settingsMeta" class="nav flex flex--center flex--automargin">
                                <div style="height: 40px;"></div>
                                <div class="active">
                                    <a @click.prevent="viewTable = !viewTable"><span class="glyphicon glyphicon-list"></span> {{ viewTable ? 'Form' : 'List View' }}</a>
                                </div>
                                <div v-if="getAvaRows" v-show="viewTable">
                                    <a><button type="button" class="btn btn-success" :style="$root.themeButtonStyle" @click="storeRows()">Submit</button></a>
                                </div>
                                <div v-show="viewTable">
                                    <a><cell-height-button
                                            :cell-height="$root.cellHeight"
                                            :max-cell-rows="$root.maxCellRows"
                                            @change-cell-height="$root.changeCellHeight"
                                            @change-max-cell-rows="$root.changeMaxCellRows"
                                    ></cell-height-button></a>
                                </div>
                                <div v-if="getAvaRows" v-show="viewTable">
                                    <a><button class="btn btn-primary btn-sm blue-gradient" @click="clickAddRow()" :style="$root.themeButtonStyle">Add</button></a>
                                </div>
                                <div v-show="viewTable">
                                    <a @click.prevent="$root.fullWidthCellToggle()"><full-width-button :full-width-cell="$root.fullWidthCell"></full-width-button></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex__elem-remain"
                     style="position: relative;"
                     :style="{paddingTop: tablePermission.dcr_sec_line_top ? (tablePermission.dcr_sec_line_thick || 1)+'px' : null}"
                >
                    <div :class="[scrlFlow ? '' : 'flx__scroller']">
                        <div v-if="tablePermission.dcr_sec_line_top" class="borderer"
                             :style="{ borderTop: (tablePermission.dcr_sec_line_thick || 1)+'px solid '+(tablePermission.dcr_sec_line_color || '#d3e0e9'), }"></div>
                        <div
                            v-if="$root.tableMeta && settingsMeta"
                            class="flex flex--col tabs-wrapper"
                            :class="tablePermission.dcr_form_shadow ? 'shadow--fix' : ''"
                            :style="specShadow"
                        >
                            <div :class="[scrlFlow ? '' : 'flex__elem-remain']" v-if="$root.tableMeta.id">

                                <div v-show="viewTable" class="full-frame" @scroll="scrollTable" ref="scroll_tb">
                                    <custom-table
                                            :class="{in_center: true}"
                                            :cell_component_name="$root.tdCellComponent($root.tableMeta.is_system)"
                                            :table_id="$root.tableMeta.id"
                                            :global-meta="$root.tableMeta"
                                            :table-meta="$root.tableMeta"
                                            :settings-meta="settingsMeta"
                                            :all-rows="tableRows"
                                            :page="page"
                                            :rows-count="present_row_count"
                                            :cell-height="$root.cellHeight"
                                            :full-width-cell="$root.fullWidthCell"
                                            :is-pagination="true"
                                            :sort="sort"
                                            :adding-row="addingRow"
                                            :user="$root.user"
                                            :behavior="'request_view'"
                                            @added-row="insertRow"
                                            @delete-row="deleteRow"
                                            @change-page="changePage"
                                            @sort-by-field="sortByField"
                                            @sub-sort-by-field="subSortByField"
                                            @row-index-clicked="rowIndexClicked"
                                    ></custom-table>
                                    <custom-edit-pop-up
                                            v-if="$root.tableMeta && editPopUpRow"
                                            :global-meta="$root.tableMeta"
                                            :table-meta="$root.tableMeta"
                                            :table-row="editPopUpRow"
                                            :settings-meta="settingsMeta"
                                            :role="'update'"
                                            :input_component_name="$root.tdCellComponent($root.tableMeta.is_system)"
                                            :behavior="'request_view'"
                                            :user="$root.user"
                                            :cell-height="$root.cellHeight"
                                            @popup-close="closePopUp"
                                    ></custom-edit-pop-up>
                                </div>
                                <request-form-view
                                        v-show="!viewTable"
                                        :table-meta="$root.tableMeta"
                                        :table-row="empty_row"
                                        :settings-meta="settingsMeta"
                                        :cell-height="$root.cellHeight"
                                        :can-add-row="canAdd"
                                        :user="$root.user"
                                        :table-permission="tablePermission"
                                        :footer_height="footer_height"
                                        :frm_color="formBgTransp()"
                                        :box_shad="getBoxShad"
                                        :scrl-flow="scrlFlow"
                                        :with_edit="withEdit"
                                        @insert="insertRow"
                                        @submit="submitRow"
                                        @scroll-fields="scrollFields"
                                        @set-form-elem="setFormElem"
                                        @show-src-record="showSrcRecord"
                                        :style="getWidth"
                                ></request-form-view>

                            </div>
                        </div>
                    </div>
                </div>
                <div class="footer"
                     :style="{
                         borderTop: (tablePermission.dcr_sec_line_bot ? (tablePermission.dcr_sec_line_thick || 1)+'px solid '+(tablePermission.dcr_sec_line_color || '#d3e0e9') : null),
                         backgroundColor: getBgCol('dcr_sec_bg_bot'),
                         height: (show_footer ? footer_height+'px' : 0),
                         transition: transition_time_ms+'ms',
                     }"
                >
                    <div class="footer--toggler" @click="toggleFooter()">
                        <i class="fas" :class="[show_footer ? 'fa-caret-down' : 'fa-caret-up']"></i>
                    </div>
                    <div class="footer-wr" :style="{color: txtClr}">
                        <p>
                            <a href="/privacy" target="_blank" :style="{color: txtClr}">Privacy</a>,
                            <a href="/tos" target="_blank" :style="{color: txtClr}">Terms</a>
                            &amp;
                            <a href="/disclaimer" target="_blank" :style="{color: txtClr}">Disclaimer</a>
                        </p>
                        <p>
                            The content is neither created nor endorsed by TablDA<br>
                            TablDA Data Collection &amp; Request (DCR)
                        </p>
                    </div>
                </div>
            </div>

            <google-address-autocomplete v-if="had_address_fld"></google-address-autocomplete>

            <!--Link Popups from ListView and MapView.-->
            <template v-for="(linkObj, idx) in linkPopups">
                <link-pop-up
                        v-if="linkObj.key === 'show'"
                        :idx="linkObj.index"
                        :settings-meta="settingsMeta"
                        :user="$root.user"
                        :link="linkObj.link"
                        :meta-header="linkObj.header"
                        :meta-row="linkObj.row"
                        :cell-height="$root.cellHeight"
                        :max-cell-rows="$root.maxCellRows"
                        :popup-key="idx"
                        :no_animation="linkObj.behavior === 'map'"
                        :view_authorizer="{dcr_hash: tablePermission.dcr_hash}"
                        @show-src-record="showSrcRecord"
                        @link-popup-close="closeLinkPopup"
                ></link-pop-up>
            </template>

            <!--Popup for showing very long datas-->
            <table-data-string-popup :max-cell-rows="$root.maxCellRows"></table-data-string-popup>

        </template>
    </div>
</template>

<script>
    import {SpecialFuncs} from "../../classes/SpecialFuncs";
    import {JsFomulaParser} from "../../classes/JsFomulaParser";
    import {RequestFuncs} from "./RequestFuncs";

    import {eventBus} from './../../app';

    import RequestMixin from "./RequestMixin.vue";

    import GoogleAddressAutocomplete from "../CommonBlocks/GoogleAddressAutocomplete";
    import RequestFormView from './RequestFormView';
    import CustomTable from './../CustomTable/CustomTable';
    import CustomEditPopUp from './../CustomPopup/CustomEditPopUp';
    import FullWidthButton from './../Buttons/FullWidthButton';
    import CellHeightButton from './../Buttons/CellHeightButton';
    import AddButton from './../Buttons/AddButton';
    import SearchButton from './../Buttons/SearchButton';
    import ShowHideButton from './../Buttons/ShowHideButton';
    import RequestPassPopUp from './RequestPassPopUp';
    import LinkPopUp from "../CustomPopup/LinkPopUp";
    import TableDataStringPopup from "../CustomPopup/TableDataStringPopup";

    export default {
        name: "MainRequest",
        mixins: [
            RequestMixin,
        ],
        components: {
            TableDataStringPopup,
            LinkPopUp,
            GoogleAddressAutocomplete,
            RequestFormView,
            CustomTable,
            FullWidthButton,
            CellHeightButton,
            AddButton,
            SearchButton,
            ShowHideButton,
            RequestPassPopUp,
            CustomEditPopUp,
        },
        data: function () {
            return {
                loaded: false,
                pass: '',
                tableRows: [],
                present_row_count: 0,
                request_row_count: 0,
                available_row_count: 1000,
                searchObject: {
                    keyWord: '',
                    columns: [],
                    direct_row_id: null,
                },
                viewTable: false,
                page: 1,
                sort: [{
                    field: 'id',
                    direction: 'desc'
                }],

                editPopUpRow: null,

                new_rows: 0,
                requests: 0,

                show_footer: true,
                scroll_fields: 0,
                scroll_table: 0,
                footer_height: 100,
                transition_time_ms: 1000,
                scroll_process: false,
                form_elem: null,
                had_address_fld: false,
                linkPopups: [],
                empty_row: {},
                hashRow: null,
            }
        },
        props: {
            settingsMeta: Object,
            table_id: Number|null,
            tablePermission: Object,
            is_embed: Boolean|Number
        },
        computed: {
            scrlFlow() {
                return this.tablePermission
                    && String(this.tablePermission.dcr_sec_scroll_style).toLowerCase() === 'flow';
            },
            flexCenterClass() {
                return [window.screen.width > 767 ? 'flex--center' : ''];
            },
            canAdd() {
                return this.$root.tableMeta
                    && this.$root.tableMeta._current_right
                    && this.$root.tableMeta._current_right.can_add
                    && (this.request_row_count === -1 || this.available_row_count !== 0);
            },
            getAvaRows() {
                return this.canAdd ? (this.request_row_count > -1 ? this.available_row_count : 'Infinite') : 0;
            },
            addingRow() {
                return {
                    active: !!this.getAvaRows,
                    position: 'top'
                }
            },
            getWidth() {
                return {
                    width: Math.min( window.screen.width, (this.tablePermission.dcr_form_width || 600) )+'px',
                };
            },
            getBoxShad() {
                return this.tablePermission.dcr_form_shadow
                    ? (this.tablePermission.dcr_form_shadow_dir == 'BL' ? '-' : '')+'5px 5px 12px '+(this.tablePermission.dcr_form_shadow_color || '#777')
                    : null;
            },
            specShadow() {
                let stl = this.$root.themeMainBgStyle;
                stl.backgroundColor = 'transparent';//this.getBgCol('dcr_sec_bg_bot');
                return stl;
            },
            txtClr() {
                return SpecialFuncs.textColorOnBg(this.getBgCol('dcr_sec_bg_bot'))
            },
            withEdit() {
                return this.editabil(this.empty_row);
            },
        },
        methods: {
            //getters
            getBgCol(key, force) {
                return this.tablePermission.dcr_sec_bg_img && !force ? 'transparent' : (this.tablePermission[key] || 'transparent');
            },
            dcrTitleStyle() {
                let font = {
                    width: (this.tablePermission.dcr_title_width+'px' || null),
                    height: (this.tablePermission.dcr_title_height+'px' || null),
                    backgroundColor: this.getBgCol('dcr_title_bg_color', true),
                };
                let style = this.fontStyleObj('dcr_title_font');
                return {
                    ...font,
                    ...style,
                };
            },
            fontStyleObj(type) {
                let stl = {
                    fontFamily: this.tablePermission[type+'_font'] || this.tablePermission[type+'_type'] || 'Raleway, sans-serif',
                    fontSize: (this.tablePermission[type+'_size'] || 14)+'px',
                    lineHeight: (to_float(this.tablePermission[type+'_size'] || 14)*1.1)+'px',
                    color: this.tablePermission[type+'_color'] || null,
                };
                let fonts = this.$root.parseMsel(this.tablePermission[type+'_style']);
                _.each(fonts, (f) => {
                    (f === 'Italic' ? stl.fontStyle = 'italic' : stl.fontStyle = stl.fontStyle || null);
                    (f === 'Bold' ? stl.fontWeight = 'bold' : stl.fontWeight = stl.fontWeight || null);
                    (f === 'Strikethrough' ? stl.textDecoration = 'line-through' : stl.textDecoration = stl.textDecoration || null);
                    (f === 'Overline' ? stl.textDecoration = 'overline' : stl.textDecoration = stl.textDecoration || null);
                    (f === 'Underline' ? stl.textDecoration = 'underline' : stl.textDecoration = stl.textDecoration || null);
                });
                return stl;
            },
            formBgTransp() {
                let clr = this.tablePermission.dcr_form_bg_color || 'transparent';
                if (clr !== 'transparent') {
                    let transp = to_float(this.tablePermission.dcr_form_transparency || 0) / 100 * 255;
                    transp = Math.ceil(transp);
                    transp = Math.max(Math.min(transp, 255), 0);
                    clr += Number(255 - transp).toString(16);
                }
                return clr;
            },
            //system
            inArray(item, array) {
                return $.inArray(item, array) > -1;
            },
            setPass(pass) {
                this.pass = pass;
            },

            //get data and meta
            getTableMeta() {
                $.LoadingOverlay('show');
                axios.post('/ajax/table-data/get-headers', {
                    table_id: this.table_id,
                    user_id: this.$root.user.id,
                    special_params: {
                        table_permission_id: this.tablePermission.id,
                        dcr_hash: this.tablePermission.dcr_hash,
                    }
                }).then(({ data }) => {
                    this.$root.tableMeta = data;
                    this.searchObject.columns = _.map(this.$root.tableMeta._fields, 'field');

                    _.each(this.$root.tableMeta._fields, (fld) => {
                        fld.is_showed = 1;
                    });

                    this.had_address_fld = true;//!!_.find(this.$root.tableMeta._fields, {f_type: 'Address'});

                    console.log('TableHeaders', this.$root.tableMeta, 'size about: ', JSON.stringify(this.$root.tableMeta).length);

                    //this.getTableData();
                    (window.location.hash ? this.loadOpenedRow() : this.emptyObject());
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => $.LoadingOverlay('hide'));
            },
            /*getTableData() {
                $.LoadingOverlay('show');
                let url = '/ajax/table-data/get';
                let request = {
                    table_id: this.table_id,
                    page: this.page,
                    rows_per_page: this.$root.tableMeta.rows_per_page,
                    sort: this.sort,
                    search_words: this.searchObject.keyWords,
                    search_columns: this.searchObject.columns,
                    row_id: this.searchObject.direct_row_id || null,
                    applied_filters: [],
                    special_params: {
                        table_permission_id: this.tablePermission.id,
                        table_permission_pass: this.tablePermission.pass,
                        dcr_hash: this.tablePermission.dcr_hash,
                    }
                };
                axios.post(url, request).then(({ data }) => {
                    console.log('ListViewData', data, 'size about: ', JSON.stringify(data).length);
                    this.tableRows = data.rows;
                    this.present_row_count = data.rows_count;
                    this.available_row_count = Math.max(this.request_row_count - this.present_row_count, 0);
                    if(!this.loaded) {
                        this.viewTable = !this.getAvaRows;
                    }
                    this.loaded = true;
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => $.LoadingOverlay('hide'));
            },*/

            //sort rows
            sortByField(tableHeader, $sub) {
                let max_idx = ($sub ? this.sort.length-1 : 0);
                let sort_obj = {
                    field: tableHeader.field,
                    direction: (this.sort.length > 0 && this.sort[max_idx].direction === 'asc' ? 'desc' : 'asc')
                };

                if ($sub) {
                    ($sub === 'add' ? this.sort.push(sort_obj) : this.sort[max_idx] = sort_obj);
                } else {
                    this.sort = [sort_obj];
                }

                //this.getTableData();
            },
            subSortByField(tableHeader) {
                if (this.sort.length > 0 && this.sort[this.sort.length-1].field === tableHeader.field) {
                    this.sortByField(tableHeader, 'replace');
                } else {
                    this.sortByField(tableHeader, 'add');
                }
            },
            changePage(page) {
                this.page = page;
                //this.getTableData();
            },

            //insert data
            loadOpenedRow() {
                let record_hdr = RequestFuncs.recordUrlHeader(this.$root.tableMeta, this.tablePermission);
                if (record_hdr) {
                    axios.post('/ajax/table-data/get', {
                        ...SpecialFuncs.tableMetaRequest(this.table_id),
                        ...{
                            table_id: this.table_id,
                            page: this.page,
                            rows_per_page: this.$root.tableMeta.rows_per_page,
                            search_words: [{
                                word: '"' + window.location.hash.substr(1) + '"',
                                type: 'and',
                                direct_fld: record_hdr.field,
                            }],
                            search_columns: [record_hdr.field],
                        }
                    }).then(({ data }) => {
                        if (data && data.rows && data.rows.length) {
                            let back_row = _.first(data.rows);
                            if (this.visibil(back_row)) {
                                this.empty_row = back_row;
                                this.empty_row._new_status = '';
                                this.tableRows.push(this.empty_row);
                            } else {
                                window.location.hash = '';
                                Swal({ title: 'Record is not accessible.', text: '', timer: 3000 });
                                this.emptyObject();
                            }
                        }
                    }).catch(errors => {
                        Swal('', getErrors(errors));
                    }).finally(() => {
                        $.LoadingOverlay('hide');
                    });
                }
            },
            emptyObject() {
                this.empty_row = SpecialFuncs.emptyRow(this.$root.tableMeta);
                this.setDefaValues(this.empty_row);
                this.tableRows.splice(0, 0, this.empty_row);
            },
            setDefaValues(tableRow) {
                //Def permissions
                if (this.$root.tableMeta._current_right.default_values) {
                    _.each(this.$root.tableMeta._current_right.default_values, (val, key) => {
                        if (!tableRow[key]) {
                            tableRow[key] = val;
                        }
                    });
                }
                //Def fields
                if (this.$root.tableMeta._fields) {
                    _.each(this.$root.tableMeta._fields, (fld) => {
                        if (fld.f_default && !tableRow[fld.field]) {
                            tableRow[fld.field] = fld.f_default;
                        }
                        //Set 'cur user' as Default.
                        if (fld.f_type == 'User' && !tableRow[fld.field]) {
                            tableRow[fld.field] = this.$root.user._pre_id;
                        }
                    });
                }
                if (this.$root.tableMeta.db_name === "user_connections") {
                    tableRow['user_id'] = this.$root.user.id;
                }

                let visi_hdr = RequestFuncs.recordUrlHeader(this.$root.tableMeta, this.tablePermission, 'dcr_record_visibility_id');
                if (visi_hdr) {
                    tableRow[visi_hdr.field] = !!this.tablePermission.dcr_record_visibility_def;
                }
                let edit_hdr = RequestFuncs.recordUrlHeader(this.$root.tableMeta, this.tablePermission, 'dcr_record_editability_id');
                if (edit_hdr) {
                    tableRow[edit_hdr.field] = !!this.tablePermission.dcr_record_editability_def;
                }
            },
            submitRow(tableRow) {
                this.insertRow(tableRow, 'nonew');
                this.storeRows();
            },
            insertRow(tableRow, nonew) {
                let record_hdr = RequestFuncs.recordUrlHeader(this.$root.tableMeta, this.tablePermission, 'dcr_record_url_field_id');
                if (record_hdr && !tableRow[record_hdr.field]) {
                    tableRow[record_hdr.field] = uuidv4();
                }
                this.setDefaValues(tableRow);

                this.present_row_count++;
                this.available_row_count = Math.max(this.request_row_count - this.present_row_count, 0);

                if (nonew === 'nonew') {
                    window.location.hash = record_hdr ? tableRow[record_hdr.field] : '';
                } else {
                    this.emptyObject();
                }
            },
            deleteRow(tableRow, index) {
                if (index > -1) {
                    this.tableRows.splice(index, 1);
                }
            },

            //store data
            storeRows() {
                this.new_rows = 0;
                let requests = [];
                let row_ids = [];
                let last_row = {};
                let last_status = '';

                _.each(this.tableRows, (row, idx) => {

                    if (!Number(row.id)) {
                        last_status = row._new_status;
                        last_row = row;
                        this.new_rows++;
                        $.LoadingOverlay('show');
                        let promise = axios.post('/ajax/table-request', {
                            table_id: this.table_id,
                            fields: row,
                            get_query: {
                                table_id: this.table_id,
                                page: this.page,
                                rows_per_page: this.$root.tableMeta.rows_per_page,
                                sort: this.sort,
                                search_words: this.searchObject.keyWords,
                                search_columns: this.searchObject.columns,
                                row_id: this.searchObject.direct_row_id || null,
                                applied_filters: []
                            },
                            table_permission_id: this.tablePermission.id,
                            table_permission_pass: this.tablePermission.pass,
                            special_params: { dcr_hash: this.tablePermission.dcr_hash, },
                        }).then(({data}) => {
                            row_ids.push(data);
                            row.id = data;
                            this.tableRows[idx].id = data;
                        }).catch(errors => {
                            Swal('', getErrors(errors));
                        }).finally(() => {
                            $.LoadingOverlay('hide');
                        });

                        requests.push(promise);
                    }

                    if (Number(row.id) && row._new_status) {
                        last_status = row._new_status;
                        last_row = row;
                        this.new_rows++;
                        $.LoadingOverlay('show');
                        let promise = axios.put('/ajax/table-request', {
                            table_id: this.table_id,
                            row_id: row.id,
                            fields: row,
                            table_permission_id: this.tablePermission.id,
                            table_permission_pass: this.tablePermission.pass,
                            special_params: { dcr_hash: this.tablePermission.dcr_hash, },
                        }).then(({data}) => {
                            row_ids.push(row.id);
                            row._new_status = '';
                            this.tableRows[idx]._new_status = '';
                        }).catch(errors => {
                            Swal('', getErrors(errors));
                        }).finally(() => {
                            $.LoadingOverlay('hide');
                        });

                        requests.push(promise);
                    }

                });

                Promise.all(requests)
                    .then(() => {
                        if (this.new_rows) {

                            let rows_htmls = _.map(this.tableRows, (rr) => {
                                let htmlrow = {};
                                _.each(this.$root.tableMeta._fields, (hdr) => {
                                    htmlrow[hdr.field] = SpecialFuncs.showFullHtml(hdr, rr, this.$root.tableMeta.unit_conv_is_active);
                                });
                                return htmlrow;
                            });

                            axios.post('/ajax/table-request/finished', {
                                request_id: this.tablePermission.id,
                                table_id: this.table_id,
                                row_ids: row_ids,
                                rows_htmls: rows_htmls,
                                special_params: { dcr_hash: this.tablePermission.dcr_hash, },
                            }).then(({data}) => {
                            }).catch(errors => {
                                Swal('', getErrors(errors));
                            });

                            let msg = 'You have successfully submitted '+this.new_rows+' record(s) to "'+(this.tablePermission.dcr_title || 'Data Collection Request')+'"';
                            let pref = RequestFuncs.prefix(last_status);
                            if (this.tablePermission[pref+'confirm_msg']) {
                                let pars = new JsFomulaParser(this.$root.tableMeta);
                                msg = pars.replaceVars(_.first(this.tableRows), this.tablePermission[pref+'confirm_msg'], this.$root.tableMeta.unit_conv_is_active);
                            }

                            //new record only for 'Submitted', 'Updated' if not 'visibil','editabil'
                            if (
                                (last_status === 'Submitted' || last_status === 'Updated')
                                &&
                                (!this.visibil(last_row) || !this.editabil(last_row))
                            ) {
                                window.location.hash = '';
                                this.emptyObject();
                            }

                            Swal({ title: msg, text: '', timer: 4000 });
                        }
                    });
            },
            clickAddRow() {
                eventBus.$emit('add-inline-clicked');
            },

            /**** popups ******/
            rowIndexClicked(index) {
                this.editPopUpRow = this.tableRows[index];
                this.popUpRole = 'update';
            },
            closePopUp() {
                this.editPopUpRow = null;
            },

            setFormElem(elem) {
                this.form_elem = elem;
            },
            scrollFields() {
                if (
                    !this.scroll_process
                    &&
                    (
                        (this.show_footer && this.scroll_fields < this.form_elem.scrollTop)
                        ||
                        (!this.show_footer && this.scroll_fields > this.form_elem.scrollTop)
                    )
                ) {
                    this.toggleFooter();
                }
            },
            scrollTable(e) {
                if (
                    !this.scroll_process
                    &&
                    (
                        (this.show_footer && this.scroll_table > this.$refs.scroll_tb.scrollTop)
                        ||
                        (!this.show_footer && this.scroll_table < this.$refs.scroll_tb.scrollTop)
                    )
                ) {
                    this.toggleFooter();
                }
            },
            toggleFooter() {
                this.show_footer = !this.show_footer;
                this.scroll_process = true;
                setTimeout(() => {
                    this.scroll_process = false;
                    this.scroll_table = this.$refs.scroll_tb.scrollTop;
                    this.scroll_fields = this.form_elem.scrollTop;
                }, this.transition_time_ms);
            },

            intervalPermis(e) {
                if (this.tablePermission) {
                    axios.post('/ajax/table-permission/check', {
                        table_permission_id: this.tablePermission.id,
                    }).then(({ data }) => {
                        _.each(data, (val, key) => {
                            if (this.tablePermission[key] != val) {
                                this.tablePermission[key] = val
                            }
                        });
                    });
                }
            },

            //Link PopUps
            showSrcRecord(lnk, header, tableRow, behavior) {
                let index = this.linkPopups.filter((el) => {return el.key === 'show'}).length;
                this.linkPopups.push({
                    key: 'show',
                    index: index,
                    link: lnk,
                    header: header,
                    row: tableRow,
                    behavior: behavior,//['map','link','list_view']
                });
            },
            closeLinkPopup(idx, should_update) {
                if (idx > -1) {
                    this.linkPopups[idx].key = 'hide';
                    this.$forceUpdate();

                    if (should_update) {
                        eventBus.$emit('reload-page');
                    }
                }
            },
            /*clearLimits() {
                _.each(this.tablePermission._link_limits, (lim) => {
                    this.$set(lim, '__added_records', 0);
                });
            },
            linkRowAdded(link_limit) {
                link_limit.__added_records += 1;
            },*/
        },
        mounted() {
            if (this.table_id) {
                this.getTableMeta();
            }

            this.request_row_count = Number(this.tablePermission.row_request);
            this.request_row_count = isNaN(this.request_row_count) ? -1 : this.request_row_count;

            $('head title').html(this.$root.app_name+' - DCR: '+this.tablePermission.name);

            this.$root.is_dcr_page = true;

            //sync table permission with collaborators
            setInterval(() => {
                if (!this.$root.debug) {
                    this.intervalPermis();
                }
            }, 1000 * 3);

            //change query for getting data
            //eventBus.$on("row-per-page-changed", this.getTableData);
        },
        beforeDestroy() {
            //eventBus.$off("row-per-page-changed", this.getTableData);
        }
    }
</script>

<style lang="scss" scoped>
    #tables {
        overflow: hidden;

        .dcr_wrap {
            min-height: 100%;
            height: auto;
        }

        .dcr-top-msg {
            max-width: 100%;
        }

        .dcr-title {
            max-width: 100%;
            position: relative;
            overflow: hidden;
        }

        .hid {
            visibility: hidden;
        }
        .dcr-title--item {
            max-width: 100%;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }
        .item__h1 {
            z-index: 20;
            margin: 0;
            width: 100%;
        }
        .item__img {
            z-index: 10;
        }

        .titler {
            position: absolute;
            left: 0;
            right: 0;
            top: 0;
            height: 44px;
        }

        .tabs-wrapper {
            overflow: visible;
            position: relative;
            width: min-content;
            margin: auto;
            height: 100%;
            max-width: 100%;

            .in_center {
                width: fit-content;
                margin: 0 auto;
            }
        }
        .shadow--fix {
            margin-bottom: 10px;
            height: calc(100% - 10px);
        }

        .navbar-default {
            text-align: center;

            .navbar-nav {
                float: left;
                margin: 0;

                li {
                    float: left;
                }
            }
        }

        .no-border {
            border: none;
        }

        .available-rows {
            display: flex;
            height: 40px;
            align-items: center;
            padding: 0 5px;
            font-weight: bold;
        }

        .flx__scroller {
            height: 100%;
            width: 100%;
            position: absolute;
        }

        .footer {
            position: relative;
            transition: 1s;

            .footer--toggler {
                position: absolute;
                left: calc(50% - 13px);
                top: -20px;
                font-size: 3em;
                width: 26px;
                height: 20px;
                cursor: pointer;

                i {
                    position: relative;
                    top: -24px;
                }
            }

            .footer-wr {
                text-align: center;
                padding-top: 10px;
                overflow: hidden;

                p {
                    margin: 0 0 10px 0;
                }
                a {
                    text-decoration: underline;
                }
            }
        }

        .top_off {
            position: relative;
            top: 1px;
        }
        .borderer {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            z-index: auto;
        }

        .flex__elem-remain {
            overflow: visible;
        }

        .fit-content {
            width: fit-content;
            margin: 0 auto;
        }

    }
</style>