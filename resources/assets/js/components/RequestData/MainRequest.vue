<template>
    <div id="tables" class="full-frame" :style="{overflow: scrlFlow ? 'auto' : 'hidden'}">
        <template v-if="dcrObject.pass && !pass">

            <request-pass-pop-up :table_request_id="dcrObject.id" @pass-popup-close="setPass"></request-pass-pop-up>

        </template>
        <template v-else>

            <div v-if="$root.tableMeta.id" class="dcr_wrap flex flex--col" :style="{backgroundColor: getBgCol('dcr_sec_bg_bot')}">

                <img v-if="dcrObject.dcr_sec_bg_img"
                     class="dcr-title--item item__img"
                     :src="$root.fileUrl({url:dcrObject.dcr_sec_bg_img})"
                     style="z-index: auto;position: fixed;"
                     :style="{
                        height: (['Height','Fill'].indexOf(dcrObject.dcr_sec_bg_img_fit) > -1 ? '100%' : null),
                        width: (['Width','Fill'].indexOf(dcrObject.dcr_sec_bg_img_fit) > -1 ? '100%' : null),
                        objectFit: (dcrObject.dcr_sec_bg_img_fit === 'Fill' ? 'cover' : null),
                     }"
                />

                <div class="navbar navbar-default no-border"
                     v-if="(dcrObject.dcr_title || dcrObject.dcr_title_bg_img) && !is_embed"
                     :style="{ backgroundColor: getBgCol('dcr_sec_bg_top'), }"
                >
                    <!--TITLE-->
                    <div class="flex flex--center-h">
                        <div class="dcr-title"
                             :style="{
                                width: (dcrObject.dcr_title_width+'px' || null),
                                height: (dcrObject.dcr_title_height+'px' || null),
                                backgroundColor: getBgCol('dcr_title_bg_color', true),
                                boxShadow: getBgCol('dcr_title_bg_color', true) ? getBoxShad : null,
                                borderTopLeftRadius: (dcrObject.dcr_form_line_type == 'space' ? dcrObject.dcr_form_line_radius+'px' : ''),
                                borderTopRightRadius: (dcrObject.dcr_form_line_type == 'space' ? dcrObject.dcr_form_line_radius+'px' : ''),
                                borderBottomLeftRadius: (dcrObject.dcr_form_line_type == 'space' ? dcrObject.dcr_form_line_radius+'px' : ''),
                                borderBottomRightRadius: (dcrObject.dcr_form_line_type == 'space' ? dcrObject.dcr_form_line_radius+'px' : ''),
                            }"
                        >
                            <h1 class="hid item__h1" :style="fontStyleObj('dcr_title_font')" v-html="dcrObject.dcr_title"></h1>
                            <h1 class="dcr-title--item item__h1" :style="fontStyleObj('dcr_title_font')" v-html="dcrObject.dcr_title"></h1>
                            <img v-if="dcrObject.dcr_title_bg_img"
                                 class="dcr-title--item item__img"
                                 :src="$root.fileUrl({url:dcrObject.dcr_title_bg_img})"
                                 :style="{
                                    height: (['Height','Fill'].indexOf(dcrObject.dcr_title_bg_fit) > -1 ? '100%' : null),
                                    width: (['Width','Fill'].indexOf(dcrObject.dcr_title_bg_fit) > -1 ? '100%' : null),
                                    objectFit: (dcrObject.dcr_title_bg_fit === 'Fill' ? 'cover' : null),
                                 }"
                            />
                        </div>
                    </div>
                    <!--TITLE-->

                    <div v-if="dcrObject.dcr_form_line_top" :style="{
                        borderBottom: (dcrObject.dcr_form_line_type == 'line' ? (dcrObject.dcr_form_line_thick || 1)+'px solid '+(dcrObject.dcr_form_line_color || '#d3e0e9') : null),
                        marginBottom: (dcrObject.dcr_form_line_type == 'space' ? (dcrObject.dcr_form_line_thick || 1)+'px' : null),
                    }"></div>

                    <!--TOP MESSAGE-->
                    <div class="dcr-top-msg" :style="{
                            width: viewTable ? '100%' : (dcrObject.dcr_form_width || 600)+'px',
                            margin: 'auto',
                            boxShadow: getBoxShad,
                            borderTopLeftRadius: dcrObject.dcr_form_line_radius+'px',
                            borderTopRightRadius: dcrObject.dcr_form_line_radius+'px',
                            borderBottomLeftRadius: (dcrObject.dcr_form_line_type == 'space' ? dcrObject.dcr_form_line_radius+'px' : ''),
                            borderBottomRightRadius: (dcrObject.dcr_form_line_type == 'space' ? dcrObject.dcr_form_line_radius+'px' : ''),
                    }">
                        <div v-if="dcrFormMsage"
                             class="top-message-wrap"
                             :style="{
                                    backgroundColor: formBgTransp(),
                                    borderTopLeftRadius: dcrObject.dcr_form_line_radius+'px',
                                    borderTopRightRadius: dcrObject.dcr_form_line_radius+'px',
                                    borderBottomLeftRadius: (dcrObject.dcr_form_line_type == 'space' ? dcrObject.dcr_form_line_radius+'px' : ''),
                                    borderBottomRightRadius: (dcrObject.dcr_form_line_type == 'space' ? dcrObject.dcr_form_line_radius+'px' : ''),
                             }"
                        >
                            <div v-html="dcrFormMsage"></div>
                        </div>
                        <div class="flex navbar navbar-default fit-content" :class="flexCenterClass" v-if="!dcrObject.one_per_submission">
                            <div v-if="table_id && $root.tableMeta && settingsMeta" class="nav flex flex--center flex--automargin">
                                <div style="height: 40px;"></div>
                                <div class="active">
                                    <a @click.prevent="viewTable = !viewTable"><span class="glyphicon glyphicon-list"></span> {{ viewTable ? 'Form' : 'Grid View' }}</a>
                                </div>
                                <div v-if="getAvaRows" v-show="viewTable">
                                    <a><button type="button" class="btn btn-success" :style="$root.themeButtonStyle" @click="storeRows()">Submit</button></a>
                                </div>
                                <div v-show="viewTable" v-if="$root.tableMeta">
                                    <a><cell-height-button
                                            :table_meta="$root.tableMeta"
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
                    <!--TOP MESSAGE-->
                </div>
                <div class="flex__elem-remain"
                     style="position: relative;"
                     :style="{paddingTop: dcrObject.dcr_sec_line_top ? (dcrObject.dcr_sec_line_thick || 1)+'px' : null}"
                >
                    <div :class="{'flx__scroller': scrlFlow && viewTable, 'absolute-frame': !scrlFlow || viewTable}">
                        <div v-if="dcrObject.dcr_sec_line_top"
                             :class="{borderer: !viewTable}"
                             :style="{ borderTop: (dcrObject.dcr_sec_line_thick || 1)+'px solid '+(dcrObject.dcr_sec_line_color || '#d3e0e9'), }"></div>
                        <div
                            v-if="$root.tableMeta && settingsMeta"
                            class="flex flex--col tabs-wrapper"
                            :class="dcrObject.dcr_form_shadow ? 'shadow--fix' : ''"
                            :style="specShadow"
                        >
                            <div v-if="$root.tableMeta.id"
                                 :class="{'flex__elem-remain': scrlFlow && viewTable, 'full-height': !scrlFlow || viewTable}"
                            >

                                <div v-if="viewTable" class="full-frame" @scroll="scrollTable" ref="scroll_tb">
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
                                            @updated-row="updateRow"
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
                                        v-if="!viewTable && empty_row"
                                        :table-meta="$root.tableMeta"
                                        :table-row="empty_row"
                                        :settings-meta="settingsMeta"
                                        :cell-height="$root.cellHeight"
                                        :can-add-row="canAdd"
                                        :user="$root.user"
                                        :dcr-object="dcrObject"
                                        :dcr-linked-rows="dcrLinkedRows"
                                        :footer_height="footer_height"
                                        :frm_color="formBgTransp()"
                                        :box_shad="getBoxShad"
                                        :scrl-flow="scrlFlow"
                                        :with_edit="!!withEdit"
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
                         borderTop: (dcrObject.dcr_sec_line_bot ? (dcrObject.dcr_sec_line_thick || 1)+'px solid '+(dcrObject.dcr_sec_line_color || '#d3e0e9') : null),
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
                        :view_authorizer="{dcr_hash: dcrObject.dcr_hash}"
                        @show-src-record="showSrcRecord"
                        @link-popup-close="closeLinkPopup"
                ></link-pop-up>
            </template>

            <!--Popup for showing very long datas-->
            <table-data-string-popup :max-cell-rows="$root.maxCellRows"></table-data-string-popup>

            <!--For ANR Automations-->
            <proceed-automation-popup
                    v-if="AnrPop"
                    :user_id="$root.user.id"
                    :table-meta="$root.tableMeta"
                    :table-alert="AnrPop"
                    @hide-popup="AnrPop = null"
            ></proceed-automation-popup>

            <dcr-pass-block
                v-if="row_protection_show"
                :dcr_id="dcrObject.id"
                :row_id="row_protection.id"
                @correct-pass="openedRowConfirm(row_protection)"
                @cancel-pass="openedRowRestricted()"
            ></dcr-pass-block>

        </template>
    </div>
</template>

<script>
    import {SpecialFuncs} from "../../classes/SpecialFuncs";
    import {JsFomulaParser} from "../../classes/JsFomulaParser";
    import {RequestFuncs} from "./RequestFuncs";

    import {eventBus} from '../../app';

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
    import ProceedAutomationPopup from "../CustomPopup/ProceedAutomationPopup";
    import {RefCondHelper} from "../../classes/helpers/RefCondHelper";
    import DcrPassBlock from "../CommonBlocks/DcrPassBlock";

    export default {
        name: "MainRequest",
        mixins: [
            RequestMixin,
        ],
        components: {
            DcrPassBlock,
            ProceedAutomationPopup,
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
                row_protection: null,
                row_protection_show: false,
                AnrPop: null,
                has_errors: '',
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
                empty_row: null,
                hashRow: null,

                dcrLinkedRows: {},
            }
        },
        props: {
            settingsMeta: Object,
            table_id: Number|null,
            dcrObject: Object,
            is_embed: Boolean|Number
        },
        computed: {
            dcrFormMsage() {
                let pars = new JsFomulaParser(this.$root.tableMeta);
                return this.dcrObject.dcr_form_message && this.empty_row ?
                    (
                        this.viewTable
                            ? pars.replaceVars(_.first(this.tableRows), this.dcrObject.dcr_form_message, this.$root.tableMeta.unit_conv_is_active)
                            : pars.replaceVars(this.empty_row, this.dcrObject.dcr_form_message, this.$root.tableMeta.unit_conv_is_active)
                    )
                    : '';
            },
            scrlFlow() {
                return this.dcrObject
                    && String(this.dcrObject.dcr_sec_scroll_style).toLowerCase() === 'flow';
            },
            flexCenterClass() {
                return [window.screen.width > 767 ? 'flex--center' : ''];
            },
            canAdd() {
                return this.$root.tableMeta
                    && this.$root.tableMeta._current_right
                    && this.$root.tableMeta._current_right.can_add
                    && (this.request_row_count === -1 || this.available_row_count !== 0)
                    &&
                    (
                        !this.$root.tableMeta._rows_count
                        ||
                        this.$root.checkAvailable(this.$root.tableMeta._user, 'row_table', this.$root.tableMeta._rows_count)
                    );
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
                    width: Math.min( window.screen.width, (this.dcrObject.dcr_form_width || 600) )+'px',
                };
            },
            getBoxShad() {
                return this.dcrObject.dcr_form_shadow
                    ? (this.dcrObject.dcr_form_shadow_dir == 'BL' ? '-' : '')+'5px 5px 12px '+(this.dcrObject.dcr_form_shadow_color || '#777')
                    : null;
            },
            specShadow() {
                let stl = this.$root.themeMainBgStyle;
                stl.backgroundColor = 'transparent';//this.getBgCol('dcr_sec_bg_bot');
                return stl;
            },
            txtClr() {
                return SpecialFuncs.smartTextColorOnBg(this.getBgCol('dcr_sec_bg_bot'));
            },
            withEdit() {
                return this.empty_row && this.editabil(this.empty_row);
            },
        },
        methods: {
            //getters
            getBgCol(key, force) {
                return this.dcrObject.dcr_sec_bg_img && !force ? 'transparent' : (this.dcrObject[key] || 'transparent');
            },
            dcrTitleStyle() {
                let font = {
                    width: (this.dcrObject.dcr_title_width+'px' || null),
                    height: (this.dcrObject.dcr_title_height+'px' || null),
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
                    fontFamily: this.dcrObject[type+'_font'] || this.dcrObject[type+'_type'] || 'Raleway, sans-serif',
                    fontSize: (this.dcrObject[type+'_size'] || 14)+'px',
                    lineHeight: (to_float(this.dcrObject[type+'_size'] || 14)*1.1)+'px',
                    color: this.dcrObject[type+'_color'] || null,
                };
                let fonts = this.$root.parseMsel(this.dcrObject[type+'_style']);
                _.each(fonts, (f) => {
                    //(f === 'Italic' ? stl.fontStyle = 'italic' : stl.fontStyle = stl.fontStyle || null);
                    (f === 'Bold' ? stl.fontWeight = 'bold' : stl.fontWeight = stl.fontWeight || null);
                    (f === 'Strikethrough' ? stl.textDecoration = 'line-through' : stl.textDecoration = stl.textDecoration || null);
                    (f === 'Overline' ? stl.textDecoration = 'overline' : stl.textDecoration = stl.textDecoration || null);
                    (f === 'Underline' ? stl.textDecoration = 'underline' : stl.textDecoration = stl.textDecoration || null);
                });
                return stl;
            },
            formBgTransp() {
                let clr = this.dcrObject.dcr_form_bg_color || 'transparent';
                if (clr !== 'transparent') {
                    let transp = to_float(this.dcrObject.dcr_form_transparency || 0) / 100 * 255;
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
                        table_dcr_id: this.dcrObject.id,
                        dcr_hash: this.dcrObject.dcr_hash,
                    }
                }).then(({ data }) => {
                    this.$root.metaDcrObject = this.dcrObject;
                    this.$root.setTextRowSett(data);
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
                let record_hdr = RequestFuncs.recordUrlHeader(this.$root.tableMeta, this.dcrObject);
                if (record_hdr) {
                    $.LoadingOverlay('show');
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
                            special_params: {
                                table_dcr_id: this.dcrObject.id,
                                table_dcr_pass: this.dcrObject.pass,
                                dcr_hash: this.dcrObject.dcr_hash,
                            }
                        }
                    }).then(({ data }) => {
                        if (data && data.rows && data.rows.length) {
                            let back_row = _.first(data.rows);
                            if (this.visibil(back_row)) {
                                if (this.dcrObject.stored_row_protection) {
                                    this.row_protection_show = true;
                                    this.row_protection = back_row;
                                } else {
                                    this.openedRowConfirm(back_row);
                                }
                            } else {
                                this.openedRowRestricted();
                                Swal({ title: 'Record is not accessible.', text: '', timer: 3000 });
                            }
                        }
                    }).catch(errors => {
                        Swal('', getErrors(errors));
                    }).finally(() => {
                        $.LoadingOverlay('hide');
                    });
                }
            },
            openedRowConfirm(back_row) {
                this.row_protection_show = false;
                this.empty_row = back_row;
                this.empty_row._new_status = '';
                this.tableRows.push(this.empty_row);
            },
            openedRowRestricted() {
                this.row_protection_show = false;
                window.location.hash = '';
                this.emptyObject();
            },
            emptyObject() {
                this.empty_row = SpecialFuncs.emptyRow(this.$root.tableMeta);
                this.setDefaValues(this.empty_row);
                this.tableRows.splice(0, 0, this.empty_row);
                this.prepareLinkedRows();
            },
            setDefaValues(tableRow) {
                if (this.$root.tableMeta.db_name === "user_connections") {
                    tableRow['user_id'] = this.$root.user.id;
                }

                let visi_hdr = RequestFuncs.recordUrlHeader(this.$root.tableMeta, this.dcrObject, 'dcr_record_visibility_id');
                if (visi_hdr) {
                    tableRow[visi_hdr.field] = tableRow._new_status === 'Saved'
                        ? !!this.dcrObject.dcr_record_save_visibility_def
                        : !!this.dcrObject.dcr_record_visibility_def;
                }
                let edit_hdr = RequestFuncs.recordUrlHeader(this.$root.tableMeta, this.dcrObject, 'dcr_record_editability_id');
                if (edit_hdr) {
                    tableRow[edit_hdr.field] = tableRow._new_status === 'Saved'
                        ? !!this.dcrObject.dcr_record_save_editability_def
                        : !!this.dcrObject.dcr_record_editability_def;
                }
            },
            submitRow(tableRow) {
                this.insertRow(tableRow, 'nonew');
                this.storeRows();
            },
            insertRow(tableRow, nonew) {
                let record_hdr = RequestFuncs.recordUrlHeader(this.$root.tableMeta, this.dcrObject, 'dcr_record_url_field_id');
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
            updateRow(tableRow) {
                //front-end RowGroups and CondFormats
                RefCondHelper.updateRGandCFtoRow(this.$root.tableMeta, tableRow);
            },
            deleteRow(tableRow, index) {
                if (index > -1) {
                    this.tableRows.splice(index, 1);
                }
            },

            //store data
            storeRows() {
                this.has_errors = '';
                this.new_rows = 0;
                let requests = [];
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
                            table_dcr_id: this.dcrObject.id,
                            table_dcr_pass: this.dcrObject.pass,
                            special_params: { dcr_hash: this.dcrObject.dcr_hash, },
                            html_row: this.getHtmlRow(row),
                            dcr_linked_rows: this.dcrLinkedRows,
                        }).then(({data}) => {
                            this.$root.assignObject(data, row);
                            row.id = data.id;
                            row._new_status = '';
                            this.tableRows[idx].id = data.id;
                            this.$root.tableMeta._rows_count += 1;
                        }).catch(errors => {
                            this.has_errors = getErrors(errors);
                            throw new Error(errors);
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
                            table_dcr_id: this.dcrObject.id,
                            table_dcr_pass: this.dcrObject.pass,
                            special_params: { dcr_hash: this.dcrObject.dcr_hash, },
                            html_row: this.getHtmlRow(row),
                            dcr_linked_rows: this.dcrLinkedRows,
                        }).then(({data}) => {
                            let backend = _.first(data.rows || []);
                            if (backend) {
                                this.$root.assignObject(backend, row);
                            }
                            row._new_status = '';
                            this.tableRows[idx]._new_status = '';
                        }).catch(errors => {
                            this.has_errors = getErrors(errors);
                            throw new Error(row);
                        });

                        requests.push(promise);
                    }

                });

                Promise.all(requests)
                    .catch((error) => {
                        let err_message = '';
                        switch (last_status) {
                            case 'Updated':
                                err_message = this.dcrObject.dcr_upd_unique_msg || this.has_errors;
                                break;
                            case 'Submitted':
                                err_message = this.dcrObject.dcr_unique_msg || this.has_errors;
                                break;
                            default:
                                err_message = this.dcrObject.dcr_save_unique_msg || this.has_errors;
                                break;
                        }
                        let pars = new JsFomulaParser(this.$root.tableMeta);
                        err_message = pars.replaceVars(_.first(this.tableRows), err_message, this.$root.tableMeta.unit_conv_is_active);
                        Swal('', err_message);
                    })
                    .then(() => {
                        $.LoadingOverlay('hide');
                        if (!this.has_errors && this.new_rows) {

                            let msg = 'You have successfully submitted '+this.new_rows+' record(s) to "'+(this.dcrObject.dcr_title || 'Data Collection Request')+'"';
                            let pref = RequestFuncs.prefix(last_status);
                            if (this.dcrObject[pref+'confirm_msg']) {
                                let pars = new JsFomulaParser(this.$root.tableMeta);
                                msg = pars.replaceVars(_.first(this.tableRows), this.dcrObject[pref+'confirm_msg'], this.$root.tableMeta.unit_conv_is_active);
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
            getHtmlRow(row) {
                let htmlrow = {};
                _.each(this.$root.tableMeta._fields, (hdr) => {
                    htmlrow[hdr.field] = SpecialFuncs.showFullHtml(hdr, row, this.$root.tableMeta.unit_conv_is_active);
                });
                return htmlrow;
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
                    this.form_elem
                    &&
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
                if (this.dcrObject) {
                    axios.post('/ajax/table-data-request/check', {
                        table_dcr_id: this.dcrObject.id,
                    }).then(({ data }) => {
                        _.each(data, (val, key) => {
                            if (this.dcrObject[key] != val) {
                                this.dcrObject[key] = val
                            }
                        });
                    });
                }
            },
            intervalTickHandler(e) {
                if (this.$root.tableMeta && !this.$root.sm_msg_type) {
                    axios.post('/ajax/table/version_hash', {
                        table_id: this.$root.tableMeta.id,
                        row_list_ids: [],
                        row_fav_ids: [],
                        automations_check: !document.hidden,
                    }).then(({ data }) => {
                        if (!this.AnrPop && data.wait_automations && data.wait_automations._anr_tables) {
                            this.AnrPop = data.wait_automations;
                        }
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
            //DCR Linked Tables
            prepareLinkedRows() {
                this.dcrLinkedRows = {};
                _.each(this.dcrObject._dcr_linked_tables || [], (linkedObj) => {
                    this.$set(this.dcrLinkedRows, linkedObj.linked_table_id, []);
                });
            },
        },
        mounted() {
            console.log('DcrObject', this.dcrObject, 'size about: ', JSON.stringify(this.dcrObject).length);

            if (this.table_id) {
                this.getTableMeta();
            }

            this.request_row_count = Number(this.dcrObject.row_request);
            this.request_row_count = isNaN(this.request_row_count) ? -1 : this.request_row_count;

            $('head title').html(this.$root.app_name+' - DCR: '+this.dcrObject.name);

            this.$root.is_dcr_page = true;

            //sync table dcr with collaborators
            setInterval(() => {
                this.intervalPermis();
            }, this.$root.version_hash_delay);

            //sync datas with collaborators
            setInterval(() => {
                if (!localStorage.getItem('no_ping')) {
                    this.intervalTickHandler();
                }
            }, this.$root.version_hash_delay);

            //change query for getting data
            //eventBus.$on("row-per-page-changed", this.getTableData);
        },
        beforeDestroy() {
            //eventBus.$off("row-per-page-changed", this.getTableData);//
        }
    }
</script>

<style lang="scss">
    .top-message-wrap {
        text-align: initial;
        padding: 10px;

        p, label {
            margin: 0 !important;
        }
    }
</style>

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