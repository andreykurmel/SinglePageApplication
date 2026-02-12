<template>
    <div id="tables" ref="mainrequest" class="full-frame relative" :style="{overflow: scrlFlow ? 'auto' : 'hidden'}" @scroll="dcrScroll">
        <template v-if="dcrObject.pass && !pass">

            <request-pass-pop-up :table_request_id="dcrObject.id" @pass-popup-close="setPass"></request-pass-pop-up>

        </template>
        <template v-else>

            <div v-if="canDownload || canDcrEdit" class="flex flex--center-v" style="position: fixed; right: 15px; top: 10px; z-index: 500;">
                <request-dwn-button
                    v-if="canDownload"
                    :dcr-object="dcrObject"
                    :dwn-form-elem="'dcrform_wrap'"
                    :dwn-window-elem="'dcrall_wrap'"
                    class="r-btn"
                    @force-flow="(val) => { forceFlow = val; }"
                ></request-dwn-button>

                <button v-if="canDcrEdit" class="btn btn-default blue-gradient r-btn" @click="editStartOrStore" :style="$root.themeButtonStyle">
                    <i v-if="!dcrEditMode" class="fas fa-edit"></i>
                    <i v-if="dcrEditMode" class="fas fa-check"></i>
                </button>
                <button v-if="canDcrEdit && dcrEditMode" class="btn btn-default r-btn" @click="editCancel">
                    <i class="fa fa-times"></i>
                </button>

                <div v-if="canDcrEdit && dcrEditMode" class="dcr-edit-panel">
                    <label class="no-margin">Design</label>
                    <button class="btn btn-default r-btn edit-hdr-btn" @click="dcrEditOverall = true">
                        Overall
                    </button>
                    <button class="btn btn-default r-btn edit-hdr-btn" @click="dcrEditTitle = true">
                        Header
                    </button>
                    <button class="btn btn-default r-btn edit-hdr-btn" @click="dcrEditForm = true">
                        Form
                    </button>
                    <label style="margin: 5px 0 0 0;">Fields</label>
                    <button class="btn btn-default r-btn edit-hdr-btn" @click="dcrEditThisTable = true">
                        This Table
                    </button>
                    <button class="btn btn-default r-btn edit-hdr-btn" @click="dcrEditEmbedTable = true">
                        Embedded Tables
                    </button>
                    <label style="margin: 5px 0 0 0;">Other</label>
                    <button class="btn btn-default r-btn edit-hdr-btn" @click="dcrEditActions = true">
                        Actions & Status
                    </button>
                    <button class="btn btn-default r-btn edit-hdr-btn" @click="dcrEditNotifications = true">
                        Notifications
                    </button>
                </div>
            </div>

            <div v-if="$root.tableMeta.id" id="dcrall_wrap" class="dcr_wrap flex flex--col" :style="{backgroundColor: getBgCol('dcr_sec_bg_bot')}">

                <img v-if="dcrObject.dcr_sec_background_by === 'image' && dcrObject.dcr_sec_bg_img"
                     class="dcr-title--item item__img"
                     :src="$root.fileUrl({url:dcrObject.dcr_sec_bg_img})"
                     style="z-index: auto;position: fixed;"
                     :style="{
                        height: (['Height','Fill'].indexOf(dcrObject.dcr_sec_bg_img_fit) > -1 ? '100%' : null),
                        width: (['Width','Fill'].indexOf(dcrObject.dcr_sec_bg_img_fit) > -1 ? '100%' : null),
                        objectFit: (dcrObject.dcr_sec_bg_img_fit === 'Fill' ? 'cover' : null),
                     }"
                />

                <main-request-title
                    v-if="!scrlConversational || !dcrObject.dcr_sec_slide_top_header"
                    :dcr-object="dcrObject"
                    :is_embed="!!is_embed"
                    :loaded="!!(table_id && settingsMeta)"
                    :dcr_form_msg="dcrFormMsage"
                    :sm_text="headerSmText"
                    :all-rows="tableRows"
                    :can-add-row="canAdd"
                    :dcr-edit-mode="dcrEditMode"
                    :transition_time_ms="transition_time_ms"
                    :class="{ 'sticky-top': scrlFlow && dcrObject.dcr_flow_header_stick }"
                    @store-rows-click="manyRowsStore"
                    @title-popup="dcrEditTitle = true"
                    @set-view-table="(v) => { viewTable = v; prepareLinkedRows(); }"
                ></main-request-title>

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
                                 class="relative"
                                 :style="{paddingLeft: !viewTable && dcrObject.one_per_submission != 1 ? 'calc('+dcrManyRowsWi.width+' + 5px)' : null}"
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
                                            :rows-count="tableRows.length"
                                            :cell-height="$root.cellHeight"
                                            :full-width-cell="$root.fullWidthCell"
                                            :adding-row="{ active: canAdd && availableAdding, position: 'top' }"
                                            :is-pagination="true"
                                            :sort="sort"
                                            :user="$root.user"
                                            :behavior="'request_view'"
                                            style="width: 100%"
                                            @added-row="tableAddRow"
                                            @updated-row="updateRow"
                                            @delete-row="deleteRow"
                                            @change-page="changePage"
                                            @sort-by-field="sortByField"
                                            @sub-sort-by-field="subSortByField"
                                            @row-index-clicked="rowIndexClicked"
                                            @show-src-record="tableShowSrcRecord"
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
                                            @show-src-record="tableShowSrcRecord"
                                    ></custom-edit-pop-up>
                                </div>

                                <div v-if="!viewTable && dcrObject.one_per_submission != 1"
                                     class="many-rows"
                                     :style="dcrManyRowsWi">
                                    <div class="full-height">
                                        <div class="many-rows--height">
                                            <select class="form-control" v-model="listing_field">
                                                <option v-for="fld in $root.tableMeta._fields"
                                                        v-if="!$root.inArray(fld.field, $root.systemFields)"
                                                        :value="fld.field"
                                                >{{ $root.uniqName(fld.name) }}</option>
                                            </select>
                                            <div class="many-rows--content full-width">
                                                <div v-for="i in tableRows.length" :class="[((i-1) === selIdx ? 'active' : '')]" @click="selRow(i-1)">
                                                    <label v-html="showListingManyRows(i)"></label>
                                                </div>
                                                <div v-if="selIdx === -1" class="active" @click="() => {selIdx = -1}">
                                                    <label>&nbsp;</label>
                                                </div>
                                            </div>
                                        </div>

                                        <table-pagination
                                            :page="page"
                                            :table-meta="$root.tableMeta"
                                            :rows-count="tableRows.length"
                                            :compact="true"
                                            :style="{ position: 'relative', top: '38px' }"
                                            @change-page="changePage"
                                        ></table-pagination>
                                    </div>
                                    <header-resizer :table-header="listingRows"></header-resizer>
                                </div>
                                <request-form-view
                                        v-if="!viewTable && receiveRow(selIdx)"
                                        id="dcrform_wrap"
                                        :table-meta="$root.tableMeta"
                                        :table-row="receiveRow(selIdx)"
                                        :settings-meta="settingsMeta"
                                        :cell-height="$root.cellHeight"
                                        :can-add-row="canAdd"
                                        :user="$root.user"
                                        :dcr-object="dcrObject"
                                        :dcr-linked-rows="dcrLinkedRows[lrKey(selIdx)]"
                                        :footer_height="footer_height"
                                        :frm_color="formBgTransp()"
                                        :box_shad="getBoxShad"
                                        :scrl-flow="scrlFlow"
                                        :scrl-conversational="scrlConversational"
                                        :scrl-accordion="scrlAccordion"
                                        :scrl-tabs="scrlTabs"
                                        :with_edit="!!withEdit"
                                        :is_embed="!!is_embed"
                                        :dcr-form-msage="dcrFormMsage"
                                        :get-ava-rows="getAvaRows"
                                        :available-adding="availableAdding"
                                        :clear-after-submis="clearAfterSubmis"
                                        @add-row="addRow"
                                        @scroll-fields="scrollFields"
                                        @set-form-elem="setFormElem"
                                        @show-src-record="showSrcRecord"
                                        @store-rows-click="manyRowsStore"
                                        @clear-submission-changed="updateClearSubmis"
                                ></request-form-view>

                                <header-resizer
                                    v-if="dcrEditMode"
                                    :table-header="dcrObject"
                                    :hdr_key="'dcr_form_width'"
                                    :resize-only="true"
                                    :step="10"
                                    style="z-index: 150;"
                                ></header-resizer>
                                <i v-if="dcrEditMode" class="fas fa-edit pop-icon" @click="dcrEditThisTable = true" :style="{top: (dcrObject.dcr_form_line_thick || 1)+3+'px'}"></i>

                            </div>
                        </div>
                    </div>
                </div>
                <div class="footer"
                     :style="{
                         borderTop: (dcrObject.dcr_sec_line_bot ? (dcrObject.dcr_sec_line_thick || 1)+'px solid '+(dcrObject.dcr_sec_line_color || '#d3e0e9') : null),
                         backgroundColor: getBgCol('dcr_sec_bg_bot'),
                     }"
                >
                    <div class="footer--toggler" :style="{width: footerWi}" @click="toggleFooter()">
                        <img src="/assets/img/DCR_Hide_Show.png" :style="{transform: show_footer ? 'rotate(180deg)' : '', top: show_footer ? '' : '-25px'}"/>
                    </div>
                    <div class="footer-wr" :style="{
                        color: txtClr,
                        height: (show_footer ? footer_height+'px' : 0),
                        transition: transition_time_ms+'ms',
                        paddingTop: (show_footer ? '10px' : 0),
                    }">
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
                <header-history-pop-up
                    v-if="linkObj.key === 'show' && linkObj.link.link_type === 'History'"
                    :idx="linkObj.index"
                    :table-meta="$root.tableMeta"
                    :table-row="linkObj.row"
                    :history-header="linkObj.header"
                    :link="linkObj.link"
                    :popup-key="idx"
                    :is-visible="true"
                    @popup-close="closeLinkPopup"
                ></header-history-pop-up>
                <link-pop-up
                        v-else-if="linkObj.key === 'show'"
                        :source-meta="$root.tableMeta"
                        :idx="linkObj.index"
                        :link="linkObj.link"
                        :meta-header="linkObj.header"
                        :meta-row="linkObj.row"
                        :popup-key="idx"
                        :view_authorizer="{
                            mrv_marker: $root.is_mrv_page,
                            srv_marker: $root.is_srv_page,
                            dcr_marker: $root.is_dcr_page,
                            dcr_hash: dcrObject.dcr_hash,
                        }"
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

            <!-- DCR password -->
            <dcr-pass-block
                v-if="row_protection_show"
                :dcr_id="dcrObject.id"
                :row_id="row_protection.id"
                @correct-pass="openedRowConfirm(row_protection)"
                @cancel-pass="openedRowRestricted()"
            ></dcr-pass-block>

            <!--Edit Popup for 'Email','Phone Number'-->
            <cell-email-phone-popup v-if="$root.tableMeta.id" :table-meta="$root.tableMeta"></cell-email-phone-popup>

            <!-- DCR Edit Popups -->
            <slot-popup
                v-if="dcrEditOverall"
                :popup_width="1000"
                :popup_height="300"
                @popup-close="dcrEditOverall = false"
            >
                <template v-slot:title><span>DCR / Design / Overall</span></template>
                <template v-slot:body>
                    <tab-settings-requests-row-overall
                        :table_id="$root.tableMeta.id"
                        :table-meta="$root.tableMeta"
                        :cell-height="1"
                        :max-cell-rows="0"
                        :table-request="$root.settingsMeta['table_data_requests']"
                        :request-row="dcrObject"
                        :with_edit="canDcrEdit"
                        @updated-cell="updateDCR"
                        @upload-file="uploadDcrFile"
                        @del-file="delDcrFile"
                    ></tab-settings-requests-row-overall>
                </template>
            </slot-popup>

            <slot-popup
                v-if="dcrEditTitle"
                :popup_width="1000"
                @popup-close="dcrEditTitle = false"
            >
                <template v-slot:title><span>DCR / Design / Header</span></template>
                <template v-slot:body>
                    <tab-settings-requests-row-title
                        :table_id="$root.tableMeta.id"
                        :table-meta="$root.tableMeta"
                        :cell-height="1"
                        :max-cell-rows="0"
                        :table-request="$root.settingsMeta['table_data_requests']"
                        :request-row="dcrObject"
                        :with_edit="canDcrEdit"
                        :titleuniqid="titleuniqid"
                        @updated-cell="updateDCR"
                        @upload-file="uploadDcrFile"
                        @del-file="delDcrFile"
                    ></tab-settings-requests-row-title>
                </template>
            </slot-popup>

            <slot-popup
                v-if="dcrEditForm"
                :popup_width="1000"
                :popup_height="300"
                @popup-close="dcrEditForm = false"
            >
                <template v-slot:title><span>DCR / Design / Form</span></template>
                <template v-slot:body>
                    <tab-settings-requests-row-form
                        :table_id="$root.tableMeta.id"
                        :table-meta="$root.tableMeta"
                        :cell-height="1"
                        :max-cell-rows="0"
                        :table-request="$root.settingsMeta['table_data_requests']"
                        :request-row="dcrObject"
                        :with_edit="canDcrEdit"
                        :titleuniqid="titleuniqid"
                        class="full-height p5"
                        @updated-cell="updateDCR"
                    ></tab-settings-requests-row-form>
                </template>
            </slot-popup>

            <slot-popup
                v-if="dcrEditThisTable"
                :popup_width="1000"
                @popup-close="dcrEditThisTable = false"
            >
                <template v-slot:title><span>DCR / Fields / This Table</span></template>
                <template v-slot:body>
                    <tab-settings-requests-this-table
                        :table_id="$root.tableMeta.id"
                        :table-meta="$root.tableMeta"
                        :dcr-object="dcrObject"
                        :with-edit="canDcrEdit && !stopEdition"
                        :can-adding-row="canDcrEdit"
                        @check-row="dcrSettCheck"
                    ></tab-settings-requests-this-table>
                </template>
            </slot-popup>

            <slot-popup
                v-if="dcrEditEmbedTable"
                :popup_width="1000"
                @popup-close="dcrEditEmbedTable = false"
            >
                <template v-slot:title><span>DCR / Fields / Embedded Tables</span></template>
                <template v-slot:body>
                    <tab-settings-requests-linked-tables
                        :table-meta="$root.tableMeta"
                        :dcr-object="dcrObject"
                    ></tab-settings-requests-linked-tables>
                </template>
            </slot-popup>

            <slot-popup
                v-if="dcrEditActions"
                :popup_width="1000"
                :popup_height="400"
                @popup-close="dcrEditActions = false"
            >
                <template v-slot:title><span>DCR / Actions & Status</span></template>
                <template v-slot:body>
                    <tab-settings-submission-row
                        :table-meta="$root.tableMeta"
                        :table_id="$root.tableMeta.id"
                        :cell-height="1"
                        :max-cell-rows="0"
                        :table-request="$root.settingsMeta['table_data_requests']"
                        :request-row="dcrObject"
                        :with_edit="canDcrEdit"
                        :bg_color="'white'"
                        @updated-cell="updateDCR"
                    ></tab-settings-submission-row>
                </template>
            </slot-popup>

            <slot-popup
                v-if="dcrEditNotifications"
                :popup_width="1000"
                @popup-close="dcrEditNotifications = false"
            >
                <template v-slot:title><span>DCR / Notifications</span></template>
                <template v-slot:body>
                    <tab-settings-request-notifs
                        :table-meta="$root.tableMeta"
                        :table_id="$root.tableMeta.id"
                        :cell-height="1"
                        :max-cell-rows="0"
                        :table-request="$root.settingsMeta['table_data_requests']"
                        :request-row="dcrObject"
                        :with_edit="canDcrEdit"
                        :bg_color="'white'"
                        @updated-cell="updateDCR"
                    ></tab-settings-request-notifs>
                </template>
            </slot-popup>

        </template>
    </div>
</template>

<script>
    import {RowDataHelper} from "../../classes/helpers/RowDataHelper";
    import {RefCondHelper} from "../../classes/helpers/RefCondHelper";
    import {SpecialFuncs} from "../../classes/SpecialFuncs";
    import {JsFomulaParser} from "../../classes/JsFomulaParser";
    import {RequestFuncs} from "./RequestFuncs";
    import {DcrEndpoints} from "../../classes/DcrEndpoints";

    import {eventBus} from '../../app';

    import RequestMixin from "./RequestMixin.vue";
    import SortFieldsForVerticalMixin from './../_Mixins/SortFieldsForVerticalMixin.vue';

    import GoogleAddressAutocomplete from "../CommonBlocks/GoogleAddressAutocomplete";
    import RequestFormView from './RequestFormView';
    import CustomTable from './../CustomTable/CustomTable';
    import CustomEditPopUp from './../CustomPopup/CustomEditPopUp';
    import AddButton from './../Buttons/AddButton';
    import SearchButton from './../Buttons/SearchButton';
    import ShowHideButton from './../Buttons/ShowHideButton';
    import RequestPassPopUp from './RequestPassPopUp';
    import TableDataStringPopup from "../CustomPopup/TableDataStringPopup";
    import ProceedAutomationPopup from "../CustomPopup/ProceedAutomationPopup";
    import DcrPassBlock from "../CommonBlocks/DcrPassBlock";
    import MainRequestTitle from "./MainRequestTitle";
    import RequestDwnButton from "../Buttons/RequestDwnButton";
    import HeaderHistoryPopUp from "../CustomPopup/HeaderHistoryPopUp";
    import CellEmailPhonePopup from "../CustomPopup/CellEmailPhonePopup";
    import TablePagination from "../CustomTable/Pagination/TablePagination.vue";
    import HeaderResizer from "../CustomTable/Header/HeaderResizer.vue";
    import TabSettingsRequestsRowTitle from "../MainApp/Object/Table/SettingsModule/TabSettingsRequestsRowTitle.vue";
    import TabSettingsRequestsRowForm from "../MainApp/Object/Table/SettingsModule/TabSettingsRequestsRowForm.vue";
    import TabSettingsRequestsRowOverall from "../MainApp/Object/Table/SettingsModule/TabSettingsRequestsRowOverall.vue";
    import TabSettingsRequestsThisTable from "../MainApp/Object/Table/SettingsModule/TabSettingsRequestsThisTable.vue";
    import TabSettingsRequestsLinkedTables from "../MainApp/Object/Table/SettingsModule/TabSettingsRequestsLinkedTables.vue";
    import TabSettingsSubmissionRow from "../MainApp/Object/Table/SettingsModule/TabSettingsSubmissionRow.vue";
    import TabSettingsRequestNotifs from "../MainApp/Object/Table/SettingsModule/TabSettingsRequestNotifs.vue";

    export default {
        name: "MainRequest",
        mixins: [
            RequestMixin,
            SortFieldsForVerticalMixin,
        ],
        components: {
            TabSettingsRequestNotifs,
            TabSettingsSubmissionRow,
            TabSettingsRequestsLinkedTables,
            TabSettingsRequestsThisTable,
            TabSettingsRequestsRowOverall,
            TabSettingsRequestsRowForm,
            TabSettingsRequestsRowTitle,
            HeaderResizer,
            TablePagination,
            CellEmailPhonePopup,
            HeaderHistoryPopUp,
            RequestDwnButton,
            MainRequestTitle,
            DcrPassBlock,
            ProceedAutomationPopup,
            TableDataStringPopup,
            GoogleAddressAutocomplete,
            RequestFormView,
            CustomTable,
            AddButton,
            SearchButton,
            ShowHideButton,
            RequestPassPopUp,
            CustomEditPopUp,
        },
        data: function () {
            return {
                titleuniqid: uuidv4(),
                dcrEditNotifications: false,
                dcrEditActions: false,
                dcrEditEmbedTable: false,
                dcrEditThisTable: false,
                dcrEditForm: false,
                dcrEditTitle: false,
                dcrEditOverall: false,
                dcrEditMode: false,
                stopEdition: false,
                headerSmText: false,
                clearAfterSubmis: true,
                forceFlow: false,
                row_protection: null,
                row_protection_show: false,
                AnrPop: null,
                has_errors: '',
                loaded: false,
                pass: '',
                tableRows: [],
                request_row_count: -1,
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
                listingRows: {
                    width: this.dcrObject.dcr_many_rows_width || 250,
                },

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
                listing_field: null,
                selIdx: 0,
                check_errors: 0,
            }
        },
        props: {
            settingsMeta: Object,
            table_id: Number|null,
            dcrObject: Object,
            is_embed: Boolean|Number
        },
        computed: {
            canDownload() {
                return this.dcrObject.download_pdf || this.dcrObject.download_png;
            },
            canDcrEdit() {
                return this.dcrObject.user_id == this.$root.user._pre_id;
            },
            footerWi() {
                let fw = this.dcrObject.dcr_title_width;
                let width = '100%';
                if (fw) {
                    width = fw <= 1 ? (fw*100)+'%' : fw+'px';
                }
                return width;
            },
            dcrManyRowsWi() {
                let listingWi = Number(this.listingRows.width) || 250;
                let minWi = 70;
                return {
                    top: (this.dcrObject.dcr_form_line_thick || 1)+'px',
                    width: listingWi < 1 ? ((listingWi*100) + '%') : (listingWi + 'px'),
                    minWidth: minWi < 1 ? ((minWi*100) + '%') : (minWi + 'px'),
                };
            },
            dcrFormMsage() {
                let pars = new JsFomulaParser(this.$root.tableMeta);
                return this.dcrObject.dcr_form_message && this.receiveRow(this.selIdx) ?
                    (
                        this.viewTable
                            ? pars.replaceVars(_.first(this.tableRows), this.dcrObject.dcr_form_message, this.$root.tableMeta)
                            : pars.replaceVars(this.receiveRow(this.selIdx), this.dcrObject.dcr_form_message, this.$root.tableMeta)
                    )
                    : '';
            },
            scrlFlow() {
                if (this.forceFlow) {
                    return true;
                }
                return this.dcrObject
                    && String(this.dcrObject.dcr_sec_scroll_style).toLowerCase() === 'flow';
            },
            scrlConversational() {
                if (this.forceFlow) {
                    return false;
                }
                return this.dcrObject
                    && String(this.dcrObject.dcr_sec_scroll_style).toLowerCase() === 'conversational';
            },
            scrlAccordion() {
                if (this.forceFlow) {
                    return false;
                }
                return this.dcrObject
                    && String(this.dcrObject.dcr_sec_scroll_style).toLowerCase() === 'accordion';
            },
            scrlTabs() {
                if (this.forceFlow) {
                    return false;
                }
                return this.dcrObject
                    && String(this.dcrObject.dcr_sec_scroll_style).toLowerCase() === 'horizontal_tabs';
            },
            canAdd() {
                return this.$root.tableMeta
                    && this.$root.tableMeta._current_right
                    && this.$root.tableMeta._current_right.can_add
                    &&
                    (
                        !this.$root.tableMeta._rows_count
                        ||
                        this.$root.checkAvailable(this.$root.tableMeta._user, 'row_table', this.$root.tableMeta._rows_count)
                    );
            },
            getAvaRows() {
                return this.canAdd ? (this.request_row_count > -1 ? this.availableRowCount : 'Infinite') : 0;
            },
            getBoxShad() {
                return this.dcrObject.dcr_form_shadow
                    ? (this.dcrObject.dcr_form_shadow_dir == 'BL' ? '-' : '')+'5px 5px 12px '+(this.dcrObject.dcr_form_shadow_color || '#777')
                    : null;
            },
            specShadow() {
                let stl = _.clone(this.$root.themeMainBgStyle);
                stl.backgroundColor = 'transparent';//this.getBgCol('dcr_sec_bg_bot');

                let fw = this.dcrObject.dcr_form_width;
                let width = '100%';
                if (fw) {
                    width = fw <= 1 ? (fw*100)+'%' : fw+'px';
                }
                stl.maxWidth = '100%';
                stl.width = width;

                return stl;
            },
            txtClr() {
                return SpecialFuncs.smartTextColorOnBg(this.getBgCol('dcr_sec_bg_bot'));
            },
            withEdit() {
                return this.receiveRow(this.selIdx) && this.editabil(this.receiveRow(this.selIdx));
            },
            availableRowCount() {
                return Math.max(this.request_row_count - this.tableRows.length, 0);
            },
            availableAdding() {
                return this.request_row_count === -1 || this.availableRowCount !== 0;
            },
        },
        methods: {
            selRow(idx) {
                this.selIdx = -2;
                this.$nextTick(() => {
                    this.selIdx = idx;
                    this.prepareLinkedRows();
                });
            },
            //getters
            getBgCol(key, force) {
                return this.dcrObject.dcr_sec_bg_img && !force ? 'transparent' : (this.dcrObject[key] || 'transparent');
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

                    let fld = _.find(this.$root.tableMeta._fields, {id: Number(this.$root.tableMeta.listing_fld_id)}) || {};
                    this.listing_field = fld.field || null;

                    console.log('TableHeaders', this.$root.tableMeta, 'size about: ', JSON.stringify(this.$root.tableMeta).length);

                    //this.getTableData();
                    (window.location.hash ? this.loadOpenedRow() : this.emptyObject());
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
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
                            RowDataHelper.fillCanEdits(this.$root.tableMeta, data.rows);
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
                                Swal({ title: 'Info', text: 'Record is not accessible.', timer: 3000 });
                            }
                        }
                    }).catch(errors => {
                        Swal('Info', getErrors(errors));
                    }).finally(() => {
                        $.LoadingOverlay('hide');
                    });
                } else {
                    this.emptyObject();
                }
            },
            openedRowConfirm(back_row) {
                this.row_protection_show = false;
                this.empty_row = back_row;
                this.empty_row._new_status = '';
                this.tableRows.push(this.empty_row);
                this.prepareLinkedRows();
            },
            openedRowRestricted() {
                this.row_protection_show = false;
                window.location.hash = '';
                this.emptyObject();
            },
            emptyObject() {
                let savedValues = {};
                if (!this.clearAfterSubmis && this.empty_row) {
                    let visibleFields = this.sortAndFilterFields(this.$root.tableMeta, this.$root.tableMeta._fields, this.empty_row, true);
                    _.each(visibleFields, (header) => {
                        savedValues[header.field] = this.empty_row[header.field];
                    });
                }

                this.empty_row = SpecialFuncs.emptyRow(this.$root.tableMeta, this.dcrObject._default_fields);
                this.setDefaValues(this.empty_row);
                this.$root.assignObject(savedValues, this.empty_row);
                //
                this.tableRows.splice(0, 0, this.empty_row);
                this.selRow(0);
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
            getRecordUrl(tableRow) {
                let record_hdr = RequestFuncs.recordUrlHeader(this.$root.tableMeta, this.dcrObject, 'dcr_record_url_field_id');
                return record_hdr ? tableRow[record_hdr.field] : '';
            },
            setRecordUrl(tableRow) {
                let record_hdr = RequestFuncs.recordUrlHeader(this.$root.tableMeta, this.dcrObject, 'dcr_record_url_field_id');
                if (record_hdr && !tableRow[record_hdr.field]) {
                    tableRow[record_hdr.field] = uuidv4();
                    return tableRow[record_hdr.field];
                }
                return this.getRecordUrl(tableRow);
            },
            //rows functions
            setRowStatus(tableRow, status) {
                this.hasChanges = false;

                let status_hdr = RequestFuncs.recordUrlHeader(this.$root.tableMeta, this.dcrObject, 'dcr_record_status_id');
                if (status_hdr) {
                    tableRow[status_hdr.field] = status;
                }

                tableRow._new_status = status;
            },
            updateClearSubmis(val) {
                this.clearAfterSubmis = val;
            },
            tableAddRow(tableRow) {
                this.insertRow(tableRow, 'from_table');
            },
            addRow(param, new_status, tableRow) {
                if (this.$root.setCheckRequired(this.$root.tableMeta, tableRow)) {
                    this.setRowStatus(tableRow, new_status);
                    switch (param) {
                        case 'submit': this.submitRow(tableRow); break;
                        case 'insert': this.insertRow(tableRow); break;
                    }
                }
            },
            submitRow(tableRow) {
                this.insertRow(tableRow, 'nonew');
                this.storeRows();
            },
            insertRow(tableRow, nonew) {
                let hash = this.setRecordUrl(tableRow);
                this.setDefaValues(tableRow);

                if (nonew === 'from_table') {
                    this.tableRows.splice(0, 0, tableRow);
                } else
                if (nonew === 'nonew') {
                    window.location.hash = hash;
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
            manyRowsStore(status) {
                _.each(this.tableRows, (tableRow) => {
                    if (this.$root.setCheckRequired(this.$root.tableMeta, tableRow)) {
                        this.setRowStatus(tableRow, status);
                        let hash = this.setRecordUrl(tableRow);
                        this.setDefaValues(tableRow);
                        window.location.hash = hash;
                    }
                });
                this.storeRows();
            },

            //store data
            storeRows() {
                this.has_errors = '';
                this.new_rows = 0;
                let requests = [];
                let last_row = {};
                let last_status = '';
                $.LoadingOverlay('show');

                _.each(this.tableRows, (row, idx) => {

                    if (!Number(row.id)) {
                        last_status = row._new_status;
                        last_row = row;
                        this.new_rows++;
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
                            dcr_linked_rows: this.dcrLinkedRows[this.lrKey(idx)],
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
                        let promise = axios.put('/ajax/table-request', {
                            table_id: this.table_id,
                            row_id: row.id,
                            fields: row,
                            table_dcr_id: this.dcrObject.id,
                            table_dcr_pass: this.dcrObject.pass,
                            special_params: { dcr_hash: this.dcrObject.dcr_hash, },
                            html_row: this.getHtmlRow(row),
                            dcr_linked_rows: this.dcrLinkedRows[this.lrKey(idx)],
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
                        err_message = pars.formulaEval(_.first(this.tableRows), err_message, this.$root.tableMeta);
                        Swal('Info', err_message);
                    })
                    .then(() => {
                        if (!this.has_errors && this.new_rows) {

                            let msg = 'You have successfully submitted '+this.new_rows+' record(s) to "'+(this.dcrObject.dcr_title || 'Data Collection Request')+'"';
                            let pref = RequestFuncs.prefix(last_status);
                            if (this.dcrObject[pref+'confirm_msg']) {
                                let pars = new JsFomulaParser(this.$root.tableMeta);
                                msg = pars.formulaEval(_.first(this.tableRows), this.dcrObject[pref+'confirm_msg'], this.$root.tableMeta);
                            }

                            //new record only for 'Submitted', 'Updated' if not 'visibil','editabil'
                            if (
                                this.dcrObject.one_per_submission == 1
                                &&
                                (last_status === 'Submitted' || last_status === 'Updated')
                                &&
                                (!this.visibil(last_row) || !this.editabil(last_row))
                            ) {
                                window.location.hash = '';
                                this.emptyObject();
                            }

                            Swal({ title: 'Info', text: msg, timer: 4000 }).then(() => {
                                if (this.dcrObject.one_per_submission != 1 && last_status === 'Submitted') {
                                    window.location.reload();
                                }
                            });
                        }
                    }).finally(() => {
                        $.LoadingOverlay('hide');
                    });
            },
            getHtmlRow(row) {
                let htmlrow = {};
                _.each(this.$root.tableMeta._fields, (hdr) => {
                    htmlrow[hdr.field] = SpecialFuncs.showFullHtml(hdr, row, this.$root.tableMeta);
                });
                return htmlrow;
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
                    this.$refs.scroll_tb
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
                    this.scroll_table = this.$refs.scroll_tb ? this.$refs.scroll_tb.scrollTop : 0;
                    this.scroll_fields = this.form_elem ? this.form_elem.scrollTop : 0;
                }, this.transition_time_ms);
            },

            intervalPermis(e) {
                if (this.dcrObject && this.check_errors < 3 && ! this.dcrEditMode) {
                    axios.post('/ajax/table-data-request/check', {
                        table_dcr_id: this.dcrObject.id,
                    }).then(({ data }) => {
                        _.each(data, (val, key) => {
                            if (this.dcrObject[key] != val) {
                                this.dcrObject[key] = val
                            }
                        });
                    }).catch(errors => {
                        this.check_errors += 1;
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
                        if (data.job_msg) {
                            Swal('Info', data.job_msg);
                        }
                    });
                }
            },

            //Link PopUps
            tableShowSrcRecord(lnk, header, tableRow, behavior) {
                this.showSrcRecord(lnk, header, tableRow, 'list_view');
            },
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
            dcrScroll() {
                if (this.scrlFlow && this.dcrObject.dcr_flow_header_stick && this.$refs.mainrequest) {
                    this.headerSmText = this.$refs.mainrequest.scrollTop > 50;
                }
            },
            //DCR Linked Tables
            prepareLinkedRows() {
                if (! this.dcrLinkedRows[this.lrKey(this.selIdx)]) {
                    this.$set(this.dcrLinkedRows, this.lrKey(this.selIdx), {});
                    _.each(this.dcrObject._dcr_linked_tables || [], (linkedObj) => {
                        this.$set(this.dcrLinkedRows[this.lrKey(this.selIdx)], linkedObj.linked_table_id, []);
                    });
                }
                console.log('this.dcrLinkedRows',this.dcrLinkedRows);
            },
            lrKey(idx) {
                let row = this.receiveRow(idx) || {};
                return row._temp_id || row.id || 'temp';
            },

            //MANY ROWS
            showListingManyRows(i) {
                if (this.listing_field) {
                    let header = _.find(this.$root.tableMeta._fields, {field: this.listing_field});
                    let val = this.receiveRow(i-1)[this.listing_field];
                    if (val && header && this.$root.isMSEL(header.input_type)) {
                        let arr = SpecialFuncs.parseMsel(val);
                        val = '';
                        _.each(arr, (el) => {
                            val += '<span class="is_select">'+el+'</span> ';
                        });
                    }
                    if (header && $.inArray(header.input_type, this.$root.ddlInputTypes) > -1) {
                        val = this.$root.rcShow(this.receiveRow(i-1), this.listing_field);
                    }
                    return val;
                } else {
                    return i;
                }
            },
            receiveRow(idx) {
                return this.tableRows[idx] || (this.selIdx === -1 ? this.empty_row : null);
            },

            //Edit DCR functions
            editStartOrStore() {
                if (this.dcrEditMode) {
                    this.updateDCR();
                }
                this.dcrEditMode = ! this.dcrEditMode;
            },
            editCancel() {
                window.location.reload();
            },
            updateDCR() {
                DcrEndpoints.update(this.dcrObject);
            },
            uploadDcrFile(dcr, field, file) {
                DcrEndpoints.uploadFile(this.dcrObject, field, file);
            },
            delDcrFile(dcr, field) {
                DcrEndpoints.delFile(this.dcrObject, field);
            },
            dcrSettCheck(field, val) {
                this.stopEdition = true;
                DcrEndpoints.checkColumn(this.dcrObject, field, val)
                    .then(({ data }) => {
                        this.stopEdition = false;
                    });
            },
        },
        mounted() {
            console.log('DcrObject', this.dcrObject, 'size about: ', JSON.stringify(this.dcrObject).length);
            console.log('TableRows', this.tableRows);

            if (this.table_id) {
                this.getTableMeta();
            }

            this.request_row_count = Number(this.dcrObject.row_request)
                || (this.dcrObject.one_per_submission > 1 ? Number(this.dcrObject.one_per_submission) : undefined);
            this.request_row_count = isNaN(this.request_row_count) ? -1 : this.request_row_count;

            $('head title').html(this.$root.app_name+' - DCR: '+this.dcrObject.name);

            this.$root.is_dcr_page = this.dcrObject.id;
            this.$root.dcrPivotFields = this.dcrObject._fields_pivot;

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

<style lang="scss" scoped>
    #tables {
        overflow: hidden;

        .dcr_wrap {
            min-height: 100%;
            height: auto;
        }

        .dcr-title--item {
            max-width: 100%;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }
        .item__img {
            z-index: 10;
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

        .flx__scroller {
            height: 100%;
            width: 100%;
            position: absolute;
        }

        .footer {
            position: relative;
            transition: 1s;

            .footer--toggler {
                text-align: center;
                position: relative;
                height: 15px;
                cursor: pointer;
                margin: 0 auto;
                transition: 1s;
                opacity: 0;

                img {
                    position: absolute;
                    left: 0;
                    width: 100%;
                }

                &:hover {
                    opacity: 1;
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

    }

    .sticky-top {
        position: sticky;
        top: 0;
        z-index: 100;
        transition: all 0.3s linear;
    }

    .many-rows-collapsed {
        width: 7px;
        cursor: pointer;
        background: #eee;
        margin: 5px 0 5px 5px;
    }
    .many-rows {
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 250px;

        .many-rows--height {
            height: calc(100% - 38px);
        }

        .many-rows--content {
            height: calc(100% - 36px);
            border: 1px solid #CCC;
            border-radius: 5px;
            padding: 5px;
            overflow: auto;

            label {
                margin: 0;
            }
            div {
                border-bottom: 1px dashed #CCC;
                cursor: pointer;

                &:hover {
                    border: 1px dashed #AAA;
                }
            }
            .active {
                background-color: #FFC;
            }
        }
    }
    .r-btn {
        width: 35px;
        height: 30px;
        margin-left: 5px;
        padding: 0;
    }
    .pop-icon {
        position: absolute;
        top: 5px;
        right: 2px;
        z-index: 250;
        cursor: pointer;
        color: #777;
    }
    .dcr-edit-panel {
        position: absolute;
        top: 35px;
        right: 0;
        width: auto;
        background: rgba(255,255,255,0.5);
        z-index: 100;
        padding: 2px 5px 5px 5px;
        border-radius: 5px;
    }
    .edit-hdr-btn {
        width: 130px;
        font-size: 14px;
        font-weight: bold;
        margin: 3px 0 0 0;;
    }
</style>