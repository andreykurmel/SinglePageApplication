<template>
    <div v-if="draw && popupMetaTable" class="flex flex--col link-displayer" ref="popup_wrapper">

        <div v-if="!inlined" class="popup-header">
            <div class="drag-bkg"
                 draggable="true"
                 @dragstart="() => { dragPopSt(); sendPopupStyle(); }"
                 @drag="() => { dragPopup(); sendPopupStyle(); }"
            ></div>
            <div class="flex">
                <div class="flex__elem-remain">
                    <span v-html="getPopupHeader()"></span>&nbsp;
                </div>
                <div class="flex flex--center-v" style="position: absolute; right: 0; z-index: 100;">
                    <div
                        v-if="viewsAreAvail(link)"
                        class="mr5 relative pointer"
                        :style="{right: inlined ? 0 : 'auto'}"
                        @mouseenter="show_vtype = true"
                        @mouseleave="show_vtype = false"
                    >
                        <i class="fas"
                           :title="viewTypeTitle()"
                           :class="viewTypeIcon()"
                           :style="{marginLeft: '3px'}"
                        ></i>
                        <div v-if="show_vtype" class="view-type-wrapper">
                            <i v-for="type in allTypes(link)"
                               v-if="popupViewType != type"
                               class="fas pull-right"
                               :class="viewTypeIcon(type)"
                               :title="viewTypeTooltip(type)"
                               @click="changeView(type)"
                            ></i>
                        </div>
                    </div>
                    <span v-if="link.table_field_id"
                          @click="showColPop = true"
                          class="field_btn mr5 pointer"
                          title="Turn ON/OFF the display of fields."
                    >
                        <span class="btn btn-primary btn-sm blue-gradient" :style="$root.themeButtonStyle">Fields</span>
                    </span>
                    <span
                        v-if="$root.user.id"
                        class="fas fa-tools mr5 pointer"
                        @click="showLinkSettingsSourcePopup()"
                        title="Settings for this Link"
                    ></span>
                    <span
                        v-if="$root.user.id"
                        class="fa fa-cog mr5 pointer"
                        @click="showLinkSettingsCustPopup()"
                        title="Settings for the linked table."
                    ></span>
                    <span
                        class="glyphicon glyphicon-remove pointer"
                        @click="closeLink()"
                    ></span>
                </div>
            </div>
        </div>

        <div class="popup-content" :class="[autoHeight ? 'overflow-auto' : 'flex__elem-remain']">
            <div class="flex__elem__inner">

                <div v-if="linkAddons.length" style="padding: 2px 5px 0 5px;">
                    <div class="addon-buttons">
                        <span v-for="type in allTypes(link)" class="btn btn-primary btn-sm blue-gradient mr5"
                              :class="{adn_active: popupViewType === type}"
                              :style="$root.themeButtonStyle"
                              @click="changeView(type)"
                        >{{ type }}</span>
                        <span v-for="adn in $root.settingsMeta.all_addons" class="btn btn-primary btn-sm blue-gradient mr5"
                              v-if="$root.inArray(adn.code, linkAddons)"
                              :class="{adn_active: popupViewType === adn.code}"
                              :style="$root.themeButtonStyle"
                              @click="changeView(adn.code)"
                        >{{ String(adn.code).toUpperCase() }}</span>
                    </div>
                </div>

                <div class="flex flex-row" :style="{height: linkAddons.length ? 'calc(100% - 35px)' : '100%'}">

                    <div :class="[autoHeight ? 'overflow-auto' : 'flex__elem-remain']">
                        <div class="flex__elem__inner popup-main" :style="{padding: inlined ? '5px 0' : '5px'}">
                            <div class="flex flex--col">

                                <div class="popup-tab"
                                     :class="{
                                        'no-padding': popupViewType === viewTypeTable,
                                        'overflow-auto': autoHeight,
                                        'flex__elem-remain': !autoHeight,
                                    }"
                                >
                                    <div class="flex__elem__inner">
                                        <div class="flex full-height" v-if="popupMetaTable">

                                            <div v-if="! popupViewType" class="flex flex--center bold" style="margin: auto">
                                                No records available
                                            </div>

                                            <listing-view
                                                v-if="popupViewType === viewTypeListing"
                                                :table-meta="popupMetaTable"
                                                :all-rows="linkRecordRows"
                                                :user="$root.user"
                                                :cell-height="$root.cellHeight"
                                                :max-cell-rows="$root.maxCellRows"
                                                :behavior="'link_popup'"
                                                :rows-count="linkRecordRows.length"
                                                :forbidden-columns="forbiddenColumns || []"
                                                :available-columns="availableColumns"
                                                :link_popup_conditions="link_rules"
                                                :link_popup_tablerow="metaRow"
                                                :active-height-watcher="link.max_height_in_vert_table < 200"
                                                :no-borders="true"
                                                :with_edit="with_edit"
                                                :is-link="link"
                                                :with-adding="addingRow"
                                                class="vert-table"
                                                @toggle-history="toggleHistory"
                                                @show-add-ddl-option="showAddDDLOption"
                                                @added-row="insertRowFromTable"
                                                @updated-row="updateRowFromTable"
                                                @delete-row="deleteRow"
                                                @show-src-record="showSrcRecord"
                                                @total-tb-height-changed="totalTbHeightChanged"
                                            ></listing-view>

                                            <custom-table-with-popup
                                                v-if="popupViewType === viewTypeTable || popupViewType === viewTypeVvs"
                                                :global-meta="popupMetaTable"
                                                :table-meta="popupMetaTable"
                                                :settings-meta="$root.settingsMeta"
                                                :all-rows="linkRecordRows"
                                                :rows-count="linkRecordRows.length"
                                                :cell-height="$root.cellHeight"
                                                :max-cell-rows="$root.maxCellRows"
                                                :adding-row="addingRow"
                                                :user="$root.user"
                                                :behavior="'link_popup'"
                                                :forbidden-columns="$root.systemFields"
                                                :available-columns="availableColumns"
                                                :link_popup_conditions="link_rules"
                                                :link_popup_tablerow="metaRow"
                                                :show_rows_sum="!!link.show_sum"
                                                :external_align="external_align"
                                                :with_edit="with_edit"
                                                :is-link="link"
                                                :is-full-width="!!link.table_fit_width"
                                                :active-height-watcher="link.max_height_in_vert_table < 200"
                                                :has-float-actions="!!link.floating_action"
                                                :use_virtual_scroll="popupViewType === viewTypeVvs"
                                                @copy-row="copyRowFromTable"
                                                @added-row="insertRowFromTable"
                                                @updated-row="updateRowFromTable"
                                                @delete-row="deleteRow"
                                                @sort-by-field="sortByField"
                                                @sub-sort-by-field="subSortByField"
                                                @show-src-record="showSrcRecord"
                                                @total-tb-height-changed="totalTbHeightChanged"
                                                @show-add-ddl-option="showAddDDLOption"
                                            ></custom-table-with-popup>

                                            <board-table
                                                v-if="popupViewType === viewTypeBoards"
                                                :table-meta="popupMetaTable"
                                                :all-rows="linkRecordRows"
                                                :rows-count="linkRecordRows.length"
                                                :cell-height="$root.cellHeight"
                                                :max-cell-rows="$root.maxCellRows"
                                                :user="$root.user"
                                                :behavior="'link_popup'"
                                                :forbidden-columns="$root.systemFields"
                                                :available-columns="availableColumns"
                                                :link_popup_conditions="link_rules"
                                                :link_popup_tablerow="metaRow"
                                                :with_edit="with_edit"
                                                :is-link="link"
                                                :with-adding="addingRow"
                                                :with-border="true"
                                                :active-height-watcher="link.max_height_in_vert_table < 200"
                                                @show-add-ddl-option="showAddDDLOption"
                                                @added-row="insertRowFromTable"
                                                @updated-row="updateRowFromTable"
                                                @delete-row="deleteRow"
                                                @show-src-record="showSrcRecord"
                                                @total-tb-height-changed="totalTbHeightChanged"
                                            ></board-table>

                                            <tab-bi-view
                                                v-if="popupViewType === 'bi' && $root.inArray('bi', linkAddons) && popupMetaTable.add_bi"
                                                :table_id="popupMetaTable.id"
                                                :table-meta="popupMetaTable"
                                                :rows_count="rowsCount"
                                                :request_params="linkRequestParames()"
                                                :is-visible="true"
                                                class="full-width"
                                                @show-src-record="showSrcRecord"
                                            ></tab-bi-view>

                                            <tab-map-view
                                                v-if="popupViewType === 'map' && $root.inArray('map', linkAddons) && popupMetaTable.add_map"
                                                :table_id="popupMetaTable.id"
                                                :table-meta="popupMetaTable"
                                                :request_params="linkRequestParames()"
                                                :can-edit="$root.AddonAvailableToUser(popupMetaTable, 'map', 'edit')"
                                                :is-visible="true"
                                                class="full-width"
                                                @show-src-record="showSrcRecord"
                                            ></tab-map-view>

                                            <tab-settings-requests
                                                v-if="popupViewType === 'request' && $root.inArray('request', linkAddons) && popupMetaTable.add_request"
                                                :table-meta="popupMetaTable"
                                                :cell-height="1"
                                                :max-cell-rows="0"
                                                :table_id="popupMetaTable.id"
                                                :is-visible="popupViewType === 'request'"
                                            ></tab-settings-requests>

                                            <tab-alert-and-notif
                                                v-if="popupViewType === 'alert' && $root.inArray('alert', linkAddons) && popupMetaTable.add_alert"
                                                :table_id="popupMetaTable.id"
                                                :table-meta="popupMetaTable"
                                                :user="$root.user"
                                                class="full-width"
                                            ></tab-alert-and-notif>

                                            <tab-kanban-view
                                                v-if="popupViewType === 'kanban' && $root.inArray('kanban', linkAddons) && popupMetaTable.add_kanban"
                                                :table_id="popupMetaTable.id"
                                                :table-meta="popupMetaTable"
                                                :request_params="linkRequestParames()"
                                                :current-page-rows="linkRecordRows"
                                                :is-visible="true"
                                                class="full-width"
                                                @show-src-record="showSrcRecord"
                                            ></tab-kanban-view>

                                            <tab-gantt-view
                                                v-if="popupViewType === 'gantt' && $root.inArray('gantt', linkAddons) && popupMetaTable.add_gantt"
                                                :table_id="popupMetaTable.id"
                                                :table-meta="popupMetaTable"
                                                :request_params="linkRequestParames()"
                                                :current-page-rows="linkRecordRows"
                                                :is-visible="true"
                                                class="full-width"
                                                @show-src-record="showSrcRecord"
                                            ></tab-gantt-view>

                                            <tab-email-view
                                                v-if="popupViewType === 'email' && $root.inArray('email', linkAddons) && popupMetaTable.add_email"
                                                :table-meta="popupMetaTable"
                                                :user="$root.user"
                                                class="full-width"
                                                :is-visible="true"
                                            ></tab-email-view>

                                            <tab-calendar-view
                                                v-if="popupViewType === 'calendar' && $root.inArray('calendar', linkAddons) && popupMetaTable.add_calendar"
                                                :table_id="popupMetaTable.id"
                                                :table-meta="popupMetaTable"
                                                :request_params="linkRequestParames()"
                                                :current-page-rows="linkRecordRows"
                                                :is-visible="true"
                                                class="full-width"
                                                @show-src-record="showSrcRecord"
                                            ></tab-calendar-view>

                                            <tab-twilio-view
                                                v-if="popupViewType === 'twilio' && $root.inArray('twilio', linkAddons) && popupMetaTable.add_twilio"
                                                :table-meta="popupMetaTable"
                                                :is-visible="true"
                                                class="full-width"
                                            ></tab-twilio-view>

                                            <tab-tournament-view
                                                v-if="popupViewType === 'tournament' && $root.inArray('tournament', linkAddons) && popupMetaTable.add_tournament"
                                                :table-meta="popupMetaTable"
                                                :current-page-rows="linkRecordRows"
                                                :request-params="linkRequestParames()"
                                                :is-visible="true"
                                                class="full-width"
                                                @show-src-record="showSrcRecord"
                                            ></tab-tournament-view>

                                            <tab-report-view
                                                v-if="popupViewType === 'report' && $root.inArray('report', linkAddons) && popupMetaTable.add_report"
                                                :table-meta="popupMetaTable"
                                                :table-rows="linkRecordRows"
                                                :request_params="linkRequestParames()"
                                                :is-visible="true"
                                                class="full-width"
                                                @show-src-record="showSrcRecord"
                                            ></tab-report-view>

                                            <tab-ai-view
                                                v-if="popupViewType === 'ai' && $root.inArray('ai', linkAddons) && popupMetaTable.add_ai"
                                                :table-meta="popupMetaTable"
                                                :table-rows="linkRecordRows"
                                                :request_params="linkRequestParames()"
                                                :is-visible="true"
                                                class="full-width"
                                                @show-src-record="showSrcRecord"
                                            ></tab-ai-view>

                                            <tab-grouping-view
                                                v-if="popupViewType === 'grouping' && $root.inArray('grouping', linkAddons) && popupMetaTable.add_grouping"
                                                :table_id="popupMetaTable.id"
                                                :table-meta="popupMetaTable"
                                                :current-page-rows="linkRecordRows"
                                                :request_params="linkRequestParames()"
                                                :is-visible="true"
                                                class="full-width"
                                                @show-src-record="showSrcRecord"
                                            ></tab-grouping-view>

                                            <tab-simplemap-view
                                                v-if="popupViewType === 'simplemap' && $root.inArray('simplemap', linkAddons) && popupMetaTable.add_simplemap"
                                                :table_id="popupMetaTable.id"
                                                :table-meta="popupMetaTable"
                                                :current-page-rows="linkRecordRows"
                                                :request_params="linkRequestParames()"
                                                :is-visible="true"
                                                class="full-width"
                                                @show-src-record="showSrcRecord"
                                            ></tab-simplemap-view>

                                        </div>
                                    </div>
                                </div>

                                <div class="popup-buttons" v-if="!currentLinkSimple && (popupViewType === viewTypeTable || popupViewType === viewTypeBoards)">
                                    <table-pagination
                                        :page="curPage"
                                        :table-meta="popupMetaTable"
                                        :rows-count="rowsCount"
                                        :is_link="link"
                                        @change-page="changePage"
                                    ></table-pagination>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="!inlined" class="resizer" draggable="true" @dragstart="dragResizeStart" @drag="dragResizeDo" @dragend="sizesToBackend"></div>


        <!--Add Select Option Popup-->
        <add-option-popup
            v-if="popupMetaTable && addOptionPopup.show"
            :table-header="addOptionPopup.tableHeader"
            :table-row="addOptionPopup.tableRow"
            :table-meta="popupMetaTable"
            :settings-meta="$root.settingsMeta"
            :user="$root.user"
            @updated-row="checkRowAutocomplete()"
            @hide="addOptionPopup.show = false"
            @show-src-record="showSrcRecord"
        ></add-option-popup>


        <!--All Table Settings  -->
        <table-settings-all-popup
            v-if="popupMetaTable"
            :table-meta="popupMetaTable"
            :settings-meta="$root.settingsMeta"
            :cell-height="$root.cellHeight"
            :max-cell-rows="$root.maxCellRows"
            :user="$root.user"
            :uid="sett_uid"
            :ava-tabs="['basics']"
            @settings-closed="settingsClose()"
        ></table-settings-all-popup>

        <table-settings-all-popup
            v-if="sourceMeta"
            :table-meta="sourceMeta"
            :settings-meta="$root.settingsMeta"
            :cell-height="$root.cellHeight"
            :max-cell-rows="$root.maxCellRows"
            :user="$root.user"
            :uid="sett_uid_src"
            :ava-tabs="['ref_conds','links']"
            :ref_conds_filter="link.table_ref_condition_id"
            :links_filter="link.id"
            @settings-closed="settingsClose()"
        ></table-settings-all-popup>

        <field-link-columns-pop-up
                v-if="showColPop"
                :link-row="link"
                @popup-close="linkColPopClose()"
        ></field-link-columns-pop-up>
    </div>
</template>

<script>
import {eventBus} from '../../../app';

import {SpecialFuncs} from '../../../classes/SpecialFuncs';
import {RefCondHelper} from "../../../classes/helpers/RefCondHelper";

import TableSortMixin from '../../_Mixins/TableSortMixin';
import CanEditMixin from '../../_Mixins/CanViewEditMixin';
import LinkEmptyObjectMixin from "../../_Mixins/LinkEmptyObjectMixin";
import CheckRowBackendMixin from "../../_Mixins/CheckRowBackendMixin";
import PopupAnimationMixin from '../../_Mixins/PopupAnimationMixin';
import ViewTypeLinkMixin from './ViewTypeLinkMixin';

import AttachmentsBlock from '../../CommonBlocks/AttachmentsBlock';
import AddOptionPopup from "../../CustomPopup/AddOptionPopup";
import BoardTable from "../../CustomTable/BoardTable";
import TablePagination from "../../CustomTable/Pagination/TablePagination";
import CustomTableWithPopup from "../../CustomTable/CustomTableWithPopup";
import TableSettingsAllPopup from "../../CustomPopup/TableSettingsAllPopup";
import VerticalTableWithHistory from "../../CustomTable/VerticalTableWithHistory";
import FieldLinkColumnsPopUp from "../../CustomPopup/FieldLinkColumnsPopUp.vue";
import TabBiView from "../../MainApp/Object/Table/TabBiView.vue";
import HeaderResizer from "../../CustomTable/Header/HeaderResizer.vue";
import TabMapView from "../../MainApp/Object/Table/TabMapView.vue";
import TabAlertAndNotif from "../../MainApp/Object/Table/TabAlertAndNotif.vue";
import TabKanbanView from "../../MainApp/Object/Table/TabKanbanView.vue";
import TabEmailView from "../../MainApp/Object/Table/TabEmailView.vue";
import TabGanttView from "../../MainApp/Object/Table/TabGanttView.vue";
import TabCalendarView from "../../MainApp/Object/Table/TabCalendarView.vue";
import TabTournamentView from "../../MainApp/Object/Table/TabTournamentView.vue";
import TabReportView from "../../MainApp/Object/Table/TabReportView.vue";
import TabTwilioView from "../../MainApp/Object/Table/TabTwilioView.vue";
import TabAiView from "../../MainApp/Object/Table/TabAiView.vue";
import TabGroupingView from "../../MainApp/Object/Table/TabGroupingView.vue";
import TabSettingsRequests from "../../MainApp/Object/Table/SettingsModule/TabSettingsRequests.vue";
import RowSpaceButton from "../../Buttons/RowSpaceButton.vue";
import TabSettings from "../../MainApp/Object/Table/SettingsModule/TabSettings.vue";
import CustomTable from "../../CustomTable/CustomTable.vue";
import TabSimplemapView from "../../MainApp/Object/Table/TabSimplemapView.vue";
import ListingView from "../../CustomTable/ListingView.vue";

export default {
    name: "LinkDisplayer",
    mixins: [
        TableSortMixin,
        CanEditMixin,
        LinkEmptyObjectMixin,
        CheckRowBackendMixin,
        PopupAnimationMixin,
        ViewTypeLinkMixin,
    ],
    components: {
        ListingView,
        TabSimplemapView,
        CustomTable,
        TabSettings,
        RowSpaceButton,
        TabSettingsRequests,
        TabGroupingView,
        TabAiView,
        TabTwilioView, TabReportView, TabTournamentView,
        TabCalendarView, TabGanttView, TabEmailView,
        TabKanbanView, TabAlertAndNotif,
        TabMapView,
        HeaderResizer,
        TabBiView,
        FieldLinkColumnsPopUp,
        VerticalTableWithHistory,
        TableSettingsAllPopup,
        CustomTableWithPopup,
        TablePagination,
        BoardTable,
        AddOptionPopup,
        AttachmentsBlock,
    },
    data: function () {
        return {
            interval: null,
            linkAddons: this.link.avail_addons ? JSON.parse(this.link.avail_addons) : [],
            showColPop: false,
            draw: true,
            sett_uid: uuidv4(),
            sett_uid_src: uuidv4(),

            addOptionPopup: {
                show: false,
                tableHeader: null,
                tableRow: null,
            },
            open_history: false,
            linkRecordRows: [],
            linkTableMeta: null,
            link_rules: null,
            add_mode: true,
            immediate_add: false,

            curPage: 1,
            rowsCount: 0,
            should_update: false,
            listingRows: {
                width: this.link.listing_rows_width || 250
            },
            getPopupWidth: 768,
            getPopupHeight: '80%',
            minimalHeight: this.link.max_height_in_vert_table || 400,
        };
    },
    props: {
        sourceMeta: Object,
        idx: String|Number,//PopupAnimationMixin
        shiftObject: Object,//PopupAnimationMixin
        link: Object,
        metaHeader: Object,
        metaRow: Object,
        popupKey: String|Number,
        forbiddenColumns: Array,
        availableColumns: Array,
        view_authorizer: Object,
        inlined: Boolean,
        externalViewType: String,
        external_align: String,
        externalRows: Array,
        with_edit: {
            type: Boolean,
            default: true
        },
    },
    watch: {
        externalViewType(val) {
            this.popupViewType = val;
        },
    },
    computed: {
        autoHeight() {
            return this.link && !to_float(this.link.pop_height);
        },
        currentLinkSimple() {
            return this.inlined && this.link && this.link.inline_style === 'simple';
        },
        notOneRecord() {
            return !this.link || this.link.add_record_limit != 1;
        },
        popupMetaTable() {
            return this.linkTableMeta;
        },
        canAddvialink() {
            return this.canAdd && this.limitLinkAvail && this.link.can_row_add && this.nodcrOrAvail;
        },
        nodcrOrAvail() {
            return !this.$root.is_dcr_page || Number(this.metaRow.id);
        },
        canDeletevialink() {
            return this.canDelete && this.link.can_row_delete;
        },
        limitLinkAvail() {
            return !isNumber(this.link.add_record_limit) || (to_float(this.rowsCount) < to_float(this.link.add_record_limit));
        },
        addingRow() {
            return {
                active: this.add_mode && this.canAddvialink,
                immediate: this.immediate_add,
                position: 'top',
            };
        },
    },
    methods: {
        maxHeightRecalc() {
            /*if (this.popupViewType !== this.viewTypeTable) {
                this.minimalHeight = Math.max(200, this.minimalHeight);
            }*/
            this.$emit('max-height-changed', this.minimalHeight);
        },
        totalTbHeightChanged(height) {
            this.minimalHeight = height;

            switch (this.popupViewType) {
                case this.viewTypeListing:
                    this.minimalHeight += this.currentLinkSimple ? 34 : 70;
                    break;
                case this.viewTypeTable:
                    this.minimalHeight += this.currentLinkSimple ? 32 : 62;
                    break;
                case this.viewTypeBoards:
                    this.minimalHeight += this.currentLinkSimple ? 20 : 50;
                    break;
            }

            if (
                !this.currentLinkSimple && this.popupViewType === this.viewTypeListing
                && (this.canAddvialink || this.canDeletevialink)
            ) {
                this.minimalHeight += 30;
            }

            this.maxHeightRecalc();
        },
        storeSizes(width_px, height_px) {
            width_px -= (this.linkPopWi(this.popupViewType) - this.wiBase());

            let minW = Number(this.link.pop_width_px_min || this.linkTableMeta.linkd_tb_width_px_min);
            if (minW) {
                width_px = Math.max(width_px, minW);
            }
            let maxW = Number(this.link.pop_width_px_max || this.linkTableMeta.linkd_tb_width_px_max);
            if (maxW) {
                width_px = Math.min(width_px, maxW);
            }
            this.link.pop_width_px = width_px;

            let minH = Number(this.link.pop_height_min || this.linkTableMeta.linkd_tb_height_min);
            let height_perc = Number((height_px / window.innerHeight) * 100).toFixed(0);
            if (minH) {
                height_perc = Math.max(height_perc, minH);
            }
            let maxH = Number(this.link.pop_height_max || this.linkTableMeta.linkd_tb_height_max);
            if (maxH) {
                height_perc = Math.min(height_perc, maxH);
            }
            this.link.pop_height = height_perc;
            this.sendPopupStyle();
        },
        sizesToBackend() {
            if (this.link.id) {
                this.$root.sm_msg_type = 1;
                axios.put('/ajax/settings/data/link', {
                    table_link_id: this.link.id,
                    fields: {
                        table_ref_condition_id: this.link.table_ref_condition_id,
                        pop_width_px: this.link.pop_width_px,
                        pop_height: this.link.pop_height,
                    },
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            }
        },
        //sys methods
        showSrcRecord(lnk, header, tableRow) {
            this.$emit('show-src-record', lnk, header, tableRow, 'link');
        },
        hideMenu(e) {
            if (this.isVisible && e.keyCode === 27 && !this.$root.e__used) {
                this.closeLink();
                this.$root.set_e__used(this);
            }
        },
        //Headers
        getPopupHeader() {
            let linkTableName = (this.popupMetaTable ? '<i class="fas fa-table mr5"></i>' + this.popupMetaTable.name : '');
            let linkName = '<i class="fa fa-link mr5"></i>' + this.link.name;

            let headers = this.popupMetaTable ? this.popupMetaTable._fields : [];
            let row = _.first(this.linkRecordRows);
            let globalShows = [];
            _.each(headers, (hdr) => {
                let ar = [];
                if (hdr.popup_header && this.link.lnk_header) {
                    ar.push(this.$root.uniqName(hdr.name));
                }
                if (hdr.popup_header_val && this.link.lnk_header) {
                    let row_value = row
                        ? SpecialFuncs.showhtml(hdr, row, row[hdr.field], this.popupMetaTable)
                        : '';
                    ar.push(row_value);
                }
                if (ar.length) {
                    globalShows.push(ar.join(': '));
                }
            });

            return [linkTableName, linkName].join(' | ') + (globalShows.length ? ' - '+globalShows.join(' | ') : '');
        },

        //row edit functions
        copyRowFromTable(tableRow) {
            this.insertRow(tableRow, true);
        },
        insertRowFromTable(tableRow) {
            this.insertRow(tableRow);
        },
        updateRowFromTable(tableRow) {
            this.updateRow(tableRow, this.popupMetaTable.id);
        },
        insertRow(tableRow, copy) {
            let fields = _.cloneDeep(tableRow);//copy object

            this.$root.sm_msg_type = 1;
            axios.post('/ajax/table-data', {
                table_id: this.popupMetaTable.id,
                fields: fields,
                get_query: {
                    table_id: this.popupMetaTable.id,
                    page: 1,
                    rows_per_page: 0,
                },
                from_link_id: this.link.id,
                is_copy: copy ? 1 : 0,
            }).then(({data}) => {
                if (data.rows && data.rows.length) {
                    this.linkRecordRows.splice(0, 0, data.rows[0]);
                    this.rowsCount = this.linkRecordRows.length;
                }
                this.link.already_added_records = to_float(this.link.already_added_records) + 1;
                this.should_update = true;
            }).catch(errors => {
                Swal('Info', getErrors(errors));
            }).finally(() => {
                this.$root.sm_msg_type = 0;
            });
        },
        updateRow(tableRow, table_id) {
            let row_id = tableRow.id;
            let fields = _.cloneDeep(tableRow);//copy object
            this.$root.deleteSystemFields(fields);

            //front-end RowGroups and CondFormats
            RefCondHelper.updateRGandCFtoRow(this.popupMetaTable, tableRow);

            this.$root.sm_msg_type = 1;
            this.$root.prevent_cell_edit = true;
            axios.put('/ajax/table-data', {
                table_id: table_id,
                row_id: row_id,
                fields: fields,
                get_query: {
                    table_id: table_id,
                    page: 1,
                    rows_per_page: 0,
                },
            }).then(({ data }) => {
                if (data.rows && data.rows.length) {
                    SpecialFuncs.assignProps(tableRow, data.rows[0]);
                }
                this.should_update = true;
            }).catch(errors => {
                Swal('Info', getErrors(errors));
            }).finally(() => {
                eventBus.$emit('sync-favorites-update', tableRow);
                this.$root.sm_msg_type = 0;
                this.$root.prevent_cell_edit = false;
            });
        },
        deleteRow(tableRow) {
            this.$root.sm_msg_type = 1;
            axios.delete('/ajax/table-data', {
                params: {
                    table_id: this.popupMetaTable.id,
                    row_id: tableRow.id,
                }
            }).then(({ data }) => {
                let idx = _.findIndex(this.linkRecordRows, {id: tableRow.id});
                if (idx > -1) {
                    this.linkRecordRows.splice(idx, 1);
                    this.rowsCount = this.linkRecordRows.length;
                }
                this.should_update = true;
                if (!this.linkRecordRows.length) {
                    this.closeLink();
                }
            }).catch(errors => {
                Swal('Info', getErrors(errors));
            }).finally(() => {
                this.$root.sm_msg_type = 0;
            });
        },

        //rows changing
        showAddDDLOption(tableHeader, tableRow) {
            this.addOptionPopup = {
                show: true,
                tableHeader: tableHeader,
                tableRow: tableRow,
            };
        },

        //backend autocomplete
        checkRowAutocomplete() {
            let tableRow = this.addOptionPopup.tableRow;
            let link_params = this.getLinkParams(this.link_rules, this.metaRow);
            let promise = this.checkRowOnBackend(this.popupMetaTable.id, tableRow, link_params);
            if (promise) {
                promise.then((data) => {
                    Number(tableRow.id)
                        ? this.updateRowFromTable(tableRow)
                        : null;
                });
            }
        },

        //Get Data
        getHeaders() {
            if (this.externalRows) {
                return new Promise((resolve) => {
                    resolve();
                    this.linkTableMeta = null;
                    this.$nextTick(() => {
                        this.linkTableMeta = this.sourceMeta;
                        this.sendPopupStyle();
                    });
                });
            }

            return axios.post('/ajax/table-data/get-headers', {
                table_id: this.link.dir_table_id || null,
                ref_cond_id: this.link.table_ref_condition_id,
                linked_object_id: this.link.id,
                linked_object_inlined: this.inlined ? 1 : 0,
                user_id: !this.$root.user.see_view ? this.$root.user.id : null,
                special_params: this.view_authorizer || {
                    mrv_marker: this.$root.is_mrv_page,
                    srv_marker: this.$root.is_srv_page,
                    dcr_marker: this.$root.is_dcr_page,
                },
            }).then(({ data }) => {
                this.linkTableMeta = null;
                this.$nextTick(() => {
                    this.linkTableMeta = data;
                    this.sendPopupStyle();
                });
            }).catch(errors => {
                Swal('Info', getErrors(errors));
            });
        },
        linkRequestParames() {
            let mRow = _.cloneDeep(this.metaRow);
            mRow[this.metaHeader.field] = this.link._c_value;

            let params = SpecialFuncs.tableMetaRequest(this.link.dir_table_id || null, this.link.table_ref_condition_id);
            params.sort = this.sort;
            params.field = this.metaHeader.field;
            params.link = this.link;
            params.table_row = this.metaHeader.field ? mRow : {};
            params.page = this.curPage;
            params.rows_per_page = 0;
            params.special_params = this.view_authorizer || {
                mrv_marker: this.$root.is_mrv_page,
                srv_marker: this.$root.is_srv_page,
                dcr_marker: this.$root.is_dcr_page,
            };
            return params;
        },
        getTableData() { //For TableSortMixin !!!
            this.getRows();
        },
        getRows() {
            if (this.externalRows) {
                return new Promise((resolve) => {
                    resolve();
                    this.linkRecordRows = this.externalRows;
                    this.rowsCount = Number(this.externalRows);
                });
            }

            return axios.post('/ajax/table-data/field/get-rows', this.linkRequestParames()).then(({ data }) => {
                this.link_rules = data.references;
                this.linkRecordRows = data.rows;
                this.rowsCount = Number(data.rows_count);
            }).catch(errors => {
                Swal('Info', getErrors(errors));
            });
        },
        changePage(page) {
            this.curPage = Number(page);

            $.LoadingOverlay('show');
            Promise.all([
                this.getRows(),
            ]).then(() => {
                $.LoadingOverlay('hide');
            });
        },
        showLinkSettingsCustPopup() {
            eventBus.$emit('show-table-settings-all-popup', {tab:'general', filter:this.popupViewType, uid:this.sett_uid});
        },
        showLinkSettingsSourcePopup() {
            eventBus.$emit('show-table-settings-all-popup', {tab:'general', uid:this.sett_uid_src});
        },
        rhUpdate() {
            this.metaRow.row_hash = uuidv4();
        },
        settingsClose() {
            this.getHeaders();
        },
        linkColPopClose() {
            this.showColPop = false;
            this.settingsClose();
        },

        //widths for Popup
        calcPopupSizes() {
            let meta = this.linkTableMeta || {}

            if (this.link.pop_width_px === 0 && this.popupViewType === this.viewTypeTable) {
                this.getPopupWidth = 'auto';
            } else {
                this.getPopupWidth = this.linkPopWi(this.popupViewType);
            }

            if (this.link.pop_height === 0) {
                this.getPopupHeight = 'auto';
            } else {
                this.getPopupHeight = (to_float(this.link.pop_height) || to_float(meta.linkd_tb_height) || 80) + '%';
            }
        },
        sendPopupStyle() {
            this.calcPopupSizes();
            let style = this.getPopupStyle();
            if (this.viewType === 'Table') {
                style.minWidth = String(style.width).replace('px', '') / 2;
                style.minWidth = style.minWidth + 'px';
                style.maxWidth = style.width;
                style.width = 'auto';
            }
            this.$emit('popup-style', style);
            this.maxHeightRecalc();
        },
        linkPopWi(type) {
            if (this.inlined && type === this.viewTypeTable) {
                return this.$refs.popup_wrapper.clientWidth;
            }
            if (type === this.viewTypeBoards) {
                return this.wiBase();
            }

            let listingWi = this.listingRows.width < 1 ? 250 : Number(this.listingRows.width);

            let add_pixel = this.notOneRecord ? listingWi : 0;
            add_pixel += this.open_history ? 305 : 0;
            add_pixel = (type === this.viewTypeListing ? add_pixel : 305+listingWi);
            return this.wiBase() + add_pixel;
        },
        wiBase() {
            let meta = this.linkTableMeta || {};
            return to_float(this.link.pop_width_px) || to_float(meta.linkd_tb_width_px) || 768;
        },
        toggleHistory(open_history) {
            this.open_history = open_history;
            if (this.open_history) {
                this.leftPos -= 152;
            } else {
                this.leftPos += 152;
            }
            this.sendPopupStyle();
        },
        changeView(type) {
            this.open_history = this.popupViewType === this.viewTypeListing ? this.open_history : false;
            let oldWi = this.linkPopWi(this.popupViewType);
            this.popupViewType = type;
            this.$nextTick(() => {
                let newWi = this.linkPopWi(type);
                this.leftPos += (oldWi - newWi)/2;
                this.sendPopupStyle();
                if (!type) {
                    this.totalTbHeightChanged(100);
                }
            });
        },
        //Popup visibility
        closeLink() {
            this.add_mode = false;
            this.changeView('');
            let $id = this.popupMetaTable ? this.popupMetaTable.id : null;
            this.$emit('link-close', this.popupKey, this.should_update, $id);
        },
        showLink() {
            this.$emit('link-show');
            this.noAnimation({anim_transform:'none',transition_ms:0});
        },
        //MOUNTED
        openPopup() {
            this.$root.tablesZidxIncrease();
            this.zIdx = this.$root.tablesZidx;
            this.sendPopupStyle();

            $.LoadingOverlay('show');
            Promise.all([
                this.getHeaders(),
                this.getRows(),
            ]).then(() => {
                $.LoadingOverlay('hide');
                this.$emit('loaded-link');

                if (!this.linkTableMeta) {
                    return;
                }
                //"Table" view for a big amount of Cells make this popup unusable
                let linkView = this.externalViewType || this.link.popup_display || 'Listing';
                if (linkView === this.viewTypeTable && this.linkRecordRows.length * this.linkTableMeta._fields.length > 1000) {
                    linkView = this.viewTypeListing;
                    console.log('Warning: too much cells for Table view. Link - '+this.link.name);
                }

                if (!this.linkRecordRows.length) {

                    if (this.$root.is_dcr_page) {

                        if (this.canAddvialink && Number(this.metaRow.id)) {
                            this.showLink();
                            this.$nextTick(() => {
                                this.changeView(linkView);
                            });
                        } else {
                            if (!this.$root.is_dcr_page || !this.$root.dcr_notification_shown) {
                                this.$root.dcr_notification_shown = true;
                                Swal('Info', Number(this.metaRow.id)
                                    ? 'You don`t have permissions to add linked records'
                                    : 'There are linked tables in this Form. You must save the form first before you can add records to the linked tables. Ensure the "Save" feature is activated on the Form.');
                            }
                            if (this.inlined) {
                                this.$nextTick(() => {
                                    this.changeView(linkView);
                                });
                            } else {
                                this.closeLink();
                            }
                        }
                        return;
                    }

                    if (this.canAddvialink) {
                        Swal({
                            title: 'Info',
                            text: 'No records found. Add a new record?',
                            showCancelButton: true,
                            confirmButtonText: 'Yes',
                            cancelButtonText: 'No',
                        }).then((result) => {
                            if (result.value) {
                                this.immediate_add = true;
                                this.showLink();
                                this.$nextTick(() => {
                                    this.changeView(linkView);
                                });
                            } else {
                                this.closeLink();
                            }
                        });
                    } else {
                        if (! this.inlined) {
                            Swal('Info', 'No records found');
                        }
                        this.closeLink();
                    }
                } else {
                    this.showLink();
                    this.$nextTick(() => {
                        this.changeView(linkView);
                    });
                }
            });
        },
        intervalTickHandler(e) {
            if (this.linkTableMeta && !this.$root.sm_msg_type) {
                axios.post('/ajax/table/version_hash', {
                    table_id: this.linkTableMeta.id,
                    row_list_ids: [],
                    row_fav_ids: [],
                    automations_check: !document.hidden,
                }).then(({ data }) => {
                    if (this.linkTableMeta.version_hash !== data.version_hash) {
                        this.linkTableMeta.version_hash = data.version_hash;
                        this.getRows();
                    }
                });
            }
        },
    },
    mounted() {
        this.openPopup();

        this.interval = setInterval(() => {
            if (!localStorage.getItem('no_ping')) {
                this.intervalTickHandler();
            }
        }, this.$root.version_hash_delay);

        eventBus.$on('table-settings-all-popup__closed', this.rhUpdate);
        eventBus.$on('global-keydown', this.hideMenu);
    },
    beforeDestroy() {
        clearInterval(this.interval);
        eventBus.$off('table-settings-all-popup__closed', this.rhUpdate);
        eventBus.$off('global-keydown', this.hideMenu);
    }
}
</script>

<style lang="scss" scoped>
    @import "../../CustomPopup/CustomEditPopUp";

    .overflow-auto {
        overflow: auto;
    }

    .full-height {
        position: relative;
    }

    .view-type-icon {
        position: absolute;
        top: 9px;
        right: 40px;
        color: #FFF;
        cursor: pointer;
    }
    .drag-icon {
        position: absolute;
        top: 10px;
        right: 12px;
        color: #FFF;
        cursor: pointer;
    }
    .view-type-wrapper {
        right: 21px;
        position: absolute;
        background-color: transparent;
        width: max-content;
        white-space: nowrap;
        top: -3px;

        .fas {
            margin: 3px;
        }
    }
    .fas {
        color: #fff !important;
    }
    .field_btn {
        cursor: pointer;
        z-index: 100;
        top: 0;
        font-size: 14px;

        span {
            padding: 0 5px !important;
        }
    }

    .addon-buttons {
        position: relative;
        top: 5px;

        .adn_active {
            color: #444 !important;
            background: white !important;
            border: 1px solid #ccc;
        }
    }
</style>
