<template>
    <div>
        <div class="popup" v-show="show_popup" :style="getPopupStyle()" ref="popup_wrapper" :class="[activePop ? 'active-popup' : 'passive-popup']">
            <div class="flex flex--col">

                <div class="popup-header">
                    <div class="drag-bkg" draggable="true" @dragstart="dragPopSt()" @drag="dragPopup()"></div>
                    <div class="flex">
                        <div class="flex__elem-remain">
                            <span v-html="getPopupHeader()"></span>
                        </div>
                        <div class="" style="position: relative">
                            <span class="glyphicon glyphicon-remove pull-right header-btn" @click="startClose()"></span>
                            <span class="fa fa-cog pull-right header-btn" @click="showLinkSettingsCustPopup()" style="right: 25px;"></span>
                            <i style="right: 50px;" class="fas pull-right header-btn" :class="viewTypeIcon" :title="viewTypeTooltip" @click="changeView()"></i>
                        </div>
                    </div>
                </div>

                <div class="flex__elem-remain popup-content" v-if="linkTableMeta">
                    <div class="flex__elem__inner">
                        <div class="flex flex-row full-height">
                            <div class="flex flex--col many-rows" v-if="rowsCount > 1 && popupViewType === viewTypeListing">
                                <div class="full-height">
                                    <div class="many-rows--height">
                                        <select class="form-control" v-model="listing_field" @changed="changeListingField(listing_field)">
                                            <option v-for="fld in linkTableMeta._fields"
                                                    v-if="!inArray(fld.field, $root.systemFields)"
                                                    :value="fld.field"
                                            >{{ $root.uniqName(fld.name) }}</option>
                                        </select>
                                        <div class="many-rows-content">
                                            <div v-for="i in linkRecordRows.length" :class="[((i-1) === linkRecordSelIdx ? 'active' : '')]" @click="changeManyRow(i-1)">
                                                <label v-html="showListingManyRows(i)"></label>
                                            </div>
                                        </div>
                                    </div>

                                    <table-pagination
                                            :page="curPage"
                                            :table-meta="linkTableMeta"
                                            :rows-count="rowsCount"
                                            :is_link="true"
                                            :compact="true"
                                            @change-page="changePage"
                                    ></table-pagination>
                                </div>
                            </div>
                            <div class="flex__elem-remain">
                                <div class="flex__elem__inner popup-main">
                                    <div class="flex flex--col">

                                        <div class="popup-menu" v-show="popupViewType === viewTypeListing">
                                            <button class="btn btn-default" :class="{active: activeTab === 'details'}" @click="activeTab = 'details'">
                                                Details
                                            </button>
                                            <button class="btn btn-default"
                                                    :class="{active: activeTab === 'attachments'}"
                                                    @click="activeTab = 'attachments'"
                                                    v-if="hasAttachments"
                                            >
                                                Attachments (P: {{ imgCount }}, F: {{ fileCount }})
                                            </button>

                                            <div class="right-icons flex flex--automargin pull-right">
                                                <button class="btn btn-sm btn-primary blue-gradient" @click="smallSpace()" :style="$root.themeButtonStyle">
                                                    <img v-if="is_small_spacing === 'no'" src="/assets/img/elevator-dn1.png" width="15" height="15"/>
                                                    <img v-else="" src="/assets/img/elevator-up1.png" width="15" height="15"/>
                                                </button>
                                                <template v-if="linkRecordRows && linkRecordRows.length > 1 && linkRecordSelIdx > -1">
                                                    <button class="btn btn-sm btn-primary blue-gradient" @click="anotherRow(false)" :style="$root.themeButtonStyle">
                                                        <i class="fas fa-arrow-left"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-primary blue-gradient" @click="anotherRow(true)" :style="$root.themeButtonStyle">
                                                        <i class="fas fa-arrow-right"></i>
                                                    </button>
                                                </template>
                                            </div>
                                        </div>

                                        <div class="flex__elem-remain popup-tab" v-show="activeTab === 'details'">
                                            <div class="flex__elem__inner">
                                                <div class="flex full-height">
                                                    <div class="flex__elem-remain" v-if="linkTableMeta">
                                                        <div class="flex__elem__inner">

                                                            <vertical-table
                                                                v-if="popupViewType === viewTypeListing"
                                                                class="vert-table"
                                                                :td="$root.tdCellComponent(linkTableMeta.is_system)"
                                                                :global-meta="linkTableMeta"
                                                                :table-meta="linkTableMeta"
                                                                :settings-meta="settingsMeta"
                                                                :table-row="linkRecordSelIdx > -1 ? linkRecordRows[linkRecordSelIdx] : objectForAdd"
                                                                :user="user"
                                                                :cell-height="$root.cellHeight"
                                                                :max-cell-rows="$root.maxCellRows"
                                                                :behavior="'link_popup'"
                                                                :forbidden-columns="forbiddenColumns"
                                                                :available-columns="availableColumns"
                                                                :can-see-history="canSeeHistory"
                                                                :is_small_spacing="is_small_spacing"
                                                                :is-add-row="linkRecordSelIdx === -1"
                                                                @updated-cell="checkRowAutocomplete"
                                                                @toggle-history="toggleHistory"
                                                                @show-add-ddl-option="showAddDDLOption"
                                                                @show-src-record="showSrcRecord"
                                                            ></vertical-table>

                                                            <custom-table-with-popup
                                                                v-if="popupViewType === viewTypeTable"
                                                                :global-meta="linkTableMeta"
                                                                :table-meta="linkTableMeta"
                                                                :settings-meta="settingsMeta"
                                                                :all-rows="linkRecordRows"
                                                                :rows-count="linkRecordRows.length"
                                                                :cell-height="$root.cellHeight"
                                                                :max-cell-rows="$root.maxCellRows"
                                                                :adding-row="addingRow"
                                                                :user="user"
                                                                :behavior="'link_popup'"
                                                                :forbidden-columns="$root.systemFields"
                                                                :available-columns="availableColumns"
                                                                :del-restricted="!canDelete"
                                                                :link_popup_conditions="link_rules"
                                                                :link_popup_tablerow="metaRow"
                                                                :show_rows_sum="!!link.show_sum"
                                                                @added-row="insertRowFromTable"
                                                                @updated-row="updateRowFromTable"
                                                                @delete-row="deleteRow"
                                                                @show-src-record="showSrcRecord"
                                                                @row-index-clicked="changeManyRow"
                                                            ></custom-table-with-popup>

                                                            <board-table
                                                                v-if="popupViewType === viewTypeBoards"
                                                                :board-settings="globalMeta"
                                                                :global-meta="linkTableMeta"
                                                                :table-meta="linkTableMeta"
                                                                :all-rows="linkRecordRows"
                                                                :rows-count="linkRecordRows.length"
                                                                :cell-height="$root.cellHeight"
                                                                :max-cell-rows="$root.maxCellRows"
                                                                :user="user"
                                                                :behavior="'link_popup'"
                                                                :forbidden-columns="$root.systemFields"
                                                                :available-columns="availableColumns"
                                                                @added-row="insertRowFromTable"
                                                                @updated-row="updateRowFromTable"
                                                                @delete-row="deleteRow"
                                                                @show-src-record="showSrcRecord"
                                                                @selected-row="changeManyRow"
                                                            ></board-table>

                                                        </div>
                                                    </div>
                                                    <history-elem
                                                            v-if="open_history && linkRecordSelIdx > -1 && popupViewType === viewTypeListing"
                                                            class="popup-tab"
                                                            :user="user"
                                                            :table-meta="tableMeta"
                                                            :table_field="history_header"
                                                            :row_id="linkRecordRows[linkRecordSelIdx].id"
                                                    ></history-elem>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex__elem-remain popup-tab" v-show="activeTab === 'attachments'">
                                            <div class="flex__elem__inner" v-if="linkTableMeta">
                                                <attachments-block
                                                        :table-meta="linkTableMeta"
                                                        :table-row="linkRecordSelIdx > -1 ? linkRecordRows[linkRecordSelIdx] : objectForAdd"
                                                        :role="linkRecordSelIdx > -1 ? 'update' : 'add'"
                                                        :user="user"
                                                        :behavior="'link_popup'"
                                                        :forbidden-columns="forbiddenColumns"
                                                        :available-columns="availableColumns"
                                                ></attachments-block>
                                            </div>
                                        </div>

                                        <div class="popup-buttons" v-if="popupViewType === viewTypeTable || popupViewType === viewTypeBoards">
                                            <table-pagination
                                                    :page="curPage"
                                                    :table-meta="linkTableMeta"
                                                    :rows-count="rowsCount"
                                                    :is_link="true"
                                                    @change-page="changePage"
                                            ></table-pagination>
                                        </div>
                                        <div class="popup-buttons" v-show="popupViewType === viewTypeListing">
                                            <button v-if="canAddvialink" v-show="linkRecordSelIdx > -1" class="btn btn-success btn-sm" @click="addMode(true)">Add New Record</button>

                                            <button class="btn btn-success btn-sm pull-right" v-if="linkRecordSelIdx === -1 && canAddvialink" @click="insertRow(objectForAdd, true)">Save</button>
                                            <button class="btn btn-default btn-sm pull-right" v-if="linkRecordSelIdx === -1 && canAddvialink" @click="addMode(false)">Cancel</button>
                                            <button class="btn btn-danger btn-sm pull-right" v-if="linkRecordSelIdx > -1 && canDelete" @click="deleteRow(linkRecordRows[linkRecordSelIdx])">Delete</button>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <!--Add Select Option Popup-->
        <add-option-popup
                v-if="addOptionPopup.show"
                :table-header="addOptionPopup.tableHeader"
                :table-row="addOptionPopup.tableRow"
                :table-meta="linkTableMeta"
                :settings-meta="settingsMeta"
                :user="user"
                @updated-row="checkRowAutocomplete"
                @hide="addOptionPopup.show = false"
                @show-src-record="showSrcRecord"
        ></add-option-popup>


        <!--All Table Settings  -->
        <table-settings-all-popup
                :table-meta="linkTableMeta"
                :settings-meta="settingsMeta"
                :cell-height="$root.cellHeight"
                :max-cell-rows="$root.maxCellRows"
                :user="$root.user"
                :uid="sett_uid"
        ></table-settings-all-popup>
    </div>
</template>

<script>
    import {eventBus} from '../../app';

    import {SpecialFuncs} from '../../classes/SpecialFuncs';

    import CanEditMixin from '../_Mixins/CanViewEditMixin';
    import LinkEmptyObjectMixin from "../_Mixins/LinkEmptyObjectMixin";
    import CheckRowBackendMixin from "../_Mixins/CheckRowBackendMixin";
    import PopupAnimationMixin from '../_Mixins/PopupAnimationMixin';

    import VerticalTable from '../CustomTable/VerticalTable';
    import AttachmentsBlock from '../CommonBlocks/AttachmentsBlock';
    import AddOptionPopup from "./AddOptionPopup";
    import HistoryElem from "../CommonBlocks/HistoryElem";
    import BoardTable from "../CustomTable/BoardTable";
    import TablePagination from "../CustomTable/Pagination/TablePagination";
    import CustomTableWithPopup from "../CustomTable/CustomTableWithPopup";
    import TableSettingsAllPopup from "./TableSettingsAllPopup";

    export default {
        name: "LinkPopUp",
        mixins: [
            CanEditMixin,
            LinkEmptyObjectMixin,
            CheckRowBackendMixin,
            PopupAnimationMixin,
        ],
        components: {
            TableSettingsAllPopup,
            CustomTableWithPopup,
            TablePagination,
            BoardTable,
            HistoryElem,
            AddOptionPopup,
            VerticalTable,
            AttachmentsBlock
        },
        data: function () {
            return {
                sett_uid: uuidv4(),
                viewTypeListing: 'Listing',
                viewTypeTable: 'Table',
                viewTypeBoards: 'Boards',

                addOptionPopup: {
                    show: false,
                    tableHeader: null,
                    tableRow: null,
                },
                activeTab: 'details',
                show_popup: false,
                open_history: 0,
                history_header: null,
                linkRecordRows: [],
                linkRecordSelIdx: -1,
                linkTableMeta: null,
                listing_field: '',
                activePop: true,
                popupViewType: this.link.popup_display,
                link_rules: null,
                is_small_spacing: localStorage.getItem('is_small_spacing') || 'no',

                curPage: 1,
                rowsCount: 0,
            };
        },
        props:{
            idx: String|Number,
            globalMeta: Object,
            settingsMeta: {
                type: Object,
                default: function () {
                    return {};
                }
            },
            user: Object,
            link: Object,
            metaHeader: Object,
            metaRow: Object,
            cellHeight: Number,
            maxCellRows: Number,
            popupKey: String|Number,
            forbiddenColumns: {
                type: Array,
                default: function () {
                    return [];
                }
            },
            availableColumns: Array,
            shiftObject: Object,
            no_animation: Boolean,
            view_authorizer: Object,
        },
        computed: {
            canAddvialink() {
                return this.canAdd && this.limitLinkAvail;
            },
            limitLinkAvail() {
                return isNaN(this.link.add_record_limit) || (to_float(this.rowsCount) < to_float(this.link.add_record_limit));
            },
            viewTypeIcon() {
                switch (this.popupViewType) {
                    case this.viewTypeListing: return ['fa-th'];
                    case this.viewTypeTable: return ['fa-bars'];
                    case this.viewTypeBoards: return ['fa-th-list'];
                }
                return [''];
            },
            viewTypeTooltip() {
                switch (this.popupViewType) {
                    case this.viewTypeListing: return 'Switch to Table display';
                    case this.viewTypeTable: return 'Switch to Board display';
                    case this.viewTypeBoards: return 'Switch to List display';
                }
                return '';
            },
            addingRow() {
                return {
                    active: this.canAddvialink,
                    position: 'top'
                };
            },
            getPopupWidth() {
                if (this.popupViewType === this.viewTypeBoards) {
                    return 768;
                }
                let add_pixel = this.linkRecordRows && this.linkRecordRows.length > 1 ? 220 : 0;
                add_pixel += this.open_history ? 305 : 0;
                add_pixel = (this.popupViewType === this.viewTypeListing ? add_pixel : 305+220);
                return 768 + add_pixel;
            },
            canSeeHistory() {
                return this.linkTableMeta && this.linkRecordSelIdx > -1 &&
                    (
                        this.linkTableMeta._is_owner
                        ||
                        (this.linkTableMeta._current_right && this.linkTableMeta._current_right.can_see_history)
                    );
            },
            hasUnits() {
                let has = false;
                if (this.linkTableMeta) {
                    _.each(this.linkTableMeta._fields, function (el) {
                        if (el.unit) {
                            has = true;
                        }
                    });
                }
                return has;
            },
            hasAttachments() {
                return this.linkTableMeta && _.findIndex(this.linkTableMeta._fields, {f_type: 'Attachment'}) > -1;
            },
            imgCount() {
                let res = 0;
                for (let key in this.metaRow) {
                    if (key && key.indexOf('_images_for_') > -1 && this.metaRow[key]) {
                        res += this.metaRow[key].length;
                    }
                }
                return res;
            },
            fileCount() {
                let res = 0;
                for (let key in this.metaRow) {
                    if (key && key.indexOf('_files_for_') > -1 && this.metaRow[key]) {
                        res += this.metaRow[key].length;
                    }
                }
                return res;
            }
        },
        methods: {
            //sys methods
            hideMenu(e) {
                console.log(e.keyCode === 27 , !this.$root.e__used);
                if (e.keyCode === 27 && !this.$root.e__used) {
                    this.startClose();
                    this.$root.set_e__used(this);
                }
            },
            showListingManyRows(i) {
                if (this.listing_field) {
                    let header = _.find(this.linkTableMeta._fields, {id: Number(this.link.listing_field_id)});
                    let val = this.linkRecordRows[i-1][this.listing_field];
                    if (val && header && this.$root.isMSEL(header.input_type)) {
                        let arr = JSON.parse(val);
                        val = '';
                        _.each(arr, (el) => {
                            val += '<span class="is_select">'+el+'</span> ';
                        });
                    }
                    if (header && $.inArray(header.input_type, this.$root.ddlInputTypes) > -1) {
                        val = this.$root.rcShow(this.linkRecordRows[i-1], this.listing_field);
                    }
                    return val;
                } else {
                    return i;
                }
            },
            //src record and tables function
            showSrcRecord(lnk, header, tableRow) {
                this.$emit('show-src-record', lnk, header, tableRow, 'link');
            },
            changeManyRow(idx) {
                if (this.open_history > 0) {
                    this.leftPos += 152;
                }
                this.linkRecordSelIdx = idx;
                this.open_history = 0;
            },
            changeListingField(fld) {
                this.listing_field = fld;
            },
            toggleHistory(tableHeader) {
                if (this.open_history !== tableHeader.id && this.linkRecordSelIdx > -1) {
                    if (this.open_history === 0) {
                        this.leftPos -= 152;
                    }
                    this.open_history = tableHeader.id;
                    this.history_header = tableHeader;
                } else {
                    this.open_history = 0;
                    this.leftPos += 152;
                }
            },
            setActivePopup(e) {
                let container = $(this.$refs.popup_wrapper);
                this.activePop = container.has(e.target).length !== 0;
            },
            changeView() {
                if (this.popupViewType === this.viewTypeListing) {
                    this.popupViewType = this.viewTypeTable;
                    this.leftPos -= (this.open_history ? 0 : 152) + (this.linkRecordRows.length > 1 ? 0 : 110);
                } else
                if (this.popupViewType === this.viewTypeTable) {
                    this.popupViewType = this.viewTypeBoards;
                    this.leftPos += 152 + 110;
                } else {
                    this.popupViewType = this.viewTypeListing;
                    this.leftPos -= (this.open_history ? 0 : 152) + (this.linkRecordRows.length > 1 ? 0 : 110);
                }
                this.activeTab = 'details';
            },
            getPopupHeader() {
                let linkTableName = (this.linkTableMeta ? '['+this.linkTableMeta.name+'] - ' : '');

                let headers = this.linkTableMeta ? this.linkTableMeta._fields : [];
                let row = this.linkRecordRows[Math.max(this.linkRecordSelIdx, 0)];
                let globalShows = [];
                _.each(headers, (hdr) => {
                    if (hdr.popup_header) {
                        globalShows.push('{' + this.$root.uniqName(hdr.name) + '}: ' + (row ? (row[hdr.field] || '') : ''));
                    }
                });
                globalShows = globalShows.length ? globalShows.join('<br>') : '';

                return linkTableName + globalShows;
            },

            //row edit functions
            insertRowFromTable(tableRow) {
                this.insertRow(tableRow, true);
            },
            updateRowFromTable(tableRow) {
                this.updateRow(tableRow, this.linkTableMeta.id);
            },
            insertRow(tableRow, no_close) {
                let fields = _.cloneDeep(tableRow);//copy object

                this.$root.sm_msg_type = 1;
                axios.post('/ajax/table-data', {
                    table_id: this.linkTableMeta.id,
                    fields: fields,
                    get_query: {
                        table_id: this.linkTableMeta.id,
                        page: 1,
                        rows_per_page: 0,
                    },
                    from_link_id: this.link.id,
                }).then(({data}) => {
                    if (data.rows && data.rows.length) {
                        this.linkRecordRows.splice(0, 0, data.rows[0]);
                        this.rowsCount = this.linkRecordRows.length;
                    }
                    this.addMode(false);
                    this.link.already_added_records = to_float(this.link.already_added_records) + 1;
                    if (!no_close) {
                        this.startClose(true);
                    }
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
            updateRow(tableRow, table_id) {
                let row_id = tableRow.id;
                let fields = _.cloneDeep(tableRow);//copy object
                this.$root.deleteSystemFields(fields);

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
                }).catch(errors => {
                    Swal('', getErrors(errors));
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
                        table_id: this.linkTableMeta.id,
                        row_id: tableRow.id,
                    }
                }).then(({ data }) => {
                    let idx = _.findIndex(this.linkRecordRows, {id: tableRow.id});
                    if (idx > -1) {
                        this.linkRecordRows.splice(idx, 1);
                        this.rowsCount = this.linkRecordRows.length;
                        this.linkRecordSelIdx = 0;
                    }
                    if (!this.linkRecordRows.length) {
                        this.startClose(true);
                    }
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
            addMode(val) {
                this.linkRecordSelIdx = val ? -1 : 0;
                if (val) {
                    this.createObjectForAdd();
                    this.checkRowAutocomplete();
                } else {
                    if (!this.linkRecordRows.length) {
                        this.startClose();
                    }
                }
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
                let tableRow = this.linkRecordSelIdx > -1 ? this.linkRecordRows[this.linkRecordSelIdx] : this.objectForAdd;
                let link_params = this.getLinkParams(this.link_rules, this.metaRow);
                this.checkRowOnBackend(this.linkTableMeta.id, tableRow, link_params).then((data) => {
                    tableRow.id ? this.updateRow(tableRow, this.linkTableMeta.id) : null;
                });
            },

            //
            startClose(should_update) {
                //this.updateRow(this.metaRow, this.metaHeader.table_id, true, should_update);
                this.closePopup(should_update);
            },
            closePopup(should_update) {
                let $id = this.linkTableMeta ? this.linkTableMeta.id : null;
                this.$emit('link-popup-close', this.popupKey, should_update, $id);
            },

            smallSpace() {
                this.is_small_spacing = (this.is_small_spacing == 'yes' ? 'no' : 'yes');
                localStorage.setItem('is_small_spacing', this.is_small_spacing);
            },
            anotherRow(is_next) {
                if (is_next && this.linkRecordSelIdx < this.linkRecordRows.length) {
                    this.linkRecordSelIdx++;
                }
                if (!is_next && this.linkRecordSelIdx > 0) {
                    this.linkRecordSelIdx--;
                }
            },

            //Get Data
            getHeaders() {
                return axios.post('/ajax/table-data/get-headers', {
                    ref_cond_id: this.link.table_ref_condition_id,
                    user_id: !this.$root.user.see_view ? this.$root.user.id : null,
                    special_params: this.view_authorizer || {},
                }).then(({ data }) => {
                    this.linkTableMeta = data;

                    let listing_field = _.find(this.linkTableMeta._fields, {id: Number(this.link.listing_field_id)});
                    this.listing_field = (listing_field ? listing_field.field : '');
                }).catch(errors => {
                    Swal('', getErrors(errors));
                });
            },
            getRows() {
                let mRow = _.cloneDeep(this.metaRow);
                mRow[this.metaHeader.field] = this.link._c_value;

                let params = SpecialFuncs.tableMetaRequest(null, this.link.table_ref_condition_id);
                params.field = this.metaHeader.field;
                params.link = this.link;
                params.table_row = mRow;
                params.page = this.curPage;

                return axios.post('/ajax/table-data/field/get-rows', params).then(({ data }) => {
                    this.link_rules = data.references;
                    this.linkRecordRows = data.rows;
                    this.rowsCount = Number(data.rows_count);
                }).catch(errors => {
                    Swal('', getErrors(errors));
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
            rhUpdate() {
                this.metaRow.row_hash = uuidv4();
            },
        },
        mounted() {
            eventBus.$on('table-settings-all-popup__closed', this.rhUpdate);
            eventBus.$on('global-keydown', this.hideMenu);
            eventBus.$on('global-click', this.setActivePopup);

            $.LoadingOverlay('show');
            Promise.all([
                this.getHeaders(),
                this.getRows(),
            ]).then(() => {
                $.LoadingOverlay('hide');
                if (!this.linkRecordRows.length) {
                    if (this.canAddvialink) {
                        Swal({
                            title: '',
                            text: 'No records found. Add a new record?',
                            showCancelButton: true,
                            confirmButtonText: 'Yes',
                            cancelButtonText: 'No',
                        }).then((result) => {
                            if (result.value) {
                                this.addMode(true);
                                this.show_popup = true;
                                this.$nextTick(() => {
                                    this.noAnimation({anim_transform:'none',transition_ms:0});
                                    //(this.no_animation ? this.noAnimation() : this.runAnimation());
                                });
                            } else {
                                this.closePopup();
                            }
                        });
                    } else {
                        Swal('', 'No records found');
                        this.closePopup();
                    }
                } else {
                    this.linkRecordSelIdx = 0;
                    this.show_popup = true;
                    this.$nextTick(() => {
                        this.noAnimation({anim_transform:'none',transition_ms:0});
                        //(this.no_animation ? this.noAnimation() : this.runAnimation());
                    });
                }
            });
        },
        beforeDestroy() {
            eventBus.$off('table-settings-all-popup__closed', this.rhUpdate);
            eventBus.$off('global-keydown', this.hideMenu);
            eventBus.$off('global-click', this.setActivePopup);
        }
    }
</script>

<style lang="scss" scoped>
    @import "CustomEditPopUp";

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
    .active-popup {
        z-index: 1700;
    }
    .passive-popup {
        z-index: 1600;
    }
</style>
