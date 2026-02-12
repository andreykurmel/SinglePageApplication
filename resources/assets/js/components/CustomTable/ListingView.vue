<template>
    <div v-if="tableMeta" class="full-height flex bg-white"
         :style="{
            border: noBorders ? 'none' : null,
            padding: noBorders ? '0' : '5px'
         }"
    >
        <div v-if="listingPanelStatus === 'opened'"
             class="flex flex--col many-rows no-fb relative"
             :style="manyRowsWi"
        >
            <div class="full-height">
                <div class="many-rows--height" :style="{height: !currentLinkSimple ? 'calc(100% - 30px)' : '100%'}">
                    <select class="form-control" v-model="listing_field" @change="saveListing">
                        <option
                            v-for="fld in tableMeta._fields"
                            v-if="!$root.inArray(fld.field, $root.systemFields)"
                            :value="fld"
                        >{{ $root.uniqName(fld.name) }}</option>
                    </select>
                    <div class="many-rows-content full-width">
                        <div v-for="i in allRows.length" :class="[((i-1) === selIdx ? 'active' : '')]" @click="() => {selIdx = i-1}">
                            <label v-html="showListingManyRows(i)"></label>
                        </div>
                        <div v-if="selIdx === -1" class="active" @click="() => {selIdx = -1}">
                            <label>&nbsp;</label>
                        </div>
                    </div>
                </div>

                <table-pagination
                    v-if="!currentLinkSimple"
                    :page="page"
                    :table-meta="tableMeta"
                    :rows-count="rowsCount"
                    :is_link="isLink"
                    :compact="true"
                    :style="{ position: 'relative', top: '38px' }"
                    @change-page="changePage"
                ></table-pagination>
            </div>
            <header-resizer :table-header="listingRows" @resize-finished="saveRowsWidth"></header-resizer>
        </div>

        <div v-if="listingPanelStatus === 'collapsed'"
             class="many-rows-collapsed"
             @click="listingPanelStatus = listingPanelStatus === 'opened' ? 'collapsed' : 'opened'"
        ></div>
        
        <div class="flex flex--col">
            <div class="popup-menu" v-if="!currentLinkSimple">
                <button class="btn btn-default" :class="{active: activeTab === 'details'}" @click="activeTab = 'details'">
                    Fields
                </button>
                <button class="btn btn-default"
                        :class="{active: activeTab === 'attachments'}"
                        @click="activeTab = 'attachments'"
                        v-if="hasAttachments"
                >
                    Attachments (P: {{ imgCount }}, F: {{ fileCount }})
                </button>

                <div class="right-icons flex flex--automargin pull-right">
                    <row-space-button
                        :init_size="tableMeta.row_space_size"
                        @changed-space="smallSpace"
                    ></row-space-button>
                    <template v-if="selIdx > -1">
                        <button class="btn btn-sm btn-primary blue-gradient" @click="anotherRow(false)" :style="$root.themeButtonStyle">
                            <i class="fas fa-arrow-left"></i>
                        </button>
                        <button class="btn btn-sm btn-primary blue-gradient" @click="anotherRow(true)" :style="$root.themeButtonStyle">
                            <i class="fas fa-arrow-right"></i>
                        </button>
                    </template>
                </div>
            </div>

            <div v-show="activeTab === 'details'" class="popup-tab flex__elem-remain">
                <vertical-table-with-history
                    :td="$root.tdCellComponent(tableMeta.is_system)"
                    :global-meta="tableMeta"
                    :table-meta="tableMeta"
                    :settings-meta="$root.settingsMeta"
                    :table-row="selRow"
                    :user="user"
                    :cell-height="cellHeight"
                    :max-cell-rows="maxCellRows"
                    :behavior="behavior"
                    :can-see-history="canSeeHistory"
                    :is-add-row="selIdx === -1"
                    :with_edit="with_edit"
                    :available-columns="availableColumns"
                    :forbidden-columns="forbiddenColumns"
                    :can-redefine-width="canSeeHistory"
                    :active-height-watcher="activeHeightWatcher"
                    :visible="isVisible"
                    @updated-cell="vertUpdated"
                    @show-add-ddl-option="showAddDDLOption"
                    @show-src-record="showSrcRecord"
                    @toggle-history="toggleHistory"
                    @total-tb-height-changed="totalTbHeightChanged"
                ></vertical-table-with-history>
            </div>
            <div v-show="activeTab === 'attachments'" class="popup-tab flex__elem-remain">
                <div class="flex__elem__inner" v-if="tableMeta">
                    <attachments-block
                        :table-meta="tableMeta"
                        :table-row="selRow"
                        :role="selIdx > -1 ? 'update' : 'add'"
                        :user="$root.user"
                        :behavior="behavior"
                        :with_edit="with_edit"
                        :forbidden-columns="forbiddenColumns || []"
                        :available-columns="availableColumns"
                    ></attachments-block>
                </div>
            </div>
            
            <div v-if="availableAdd || canDeleteRow(selRow)" style="margin-top: 7px;">
                <button v-if="availableAdd" :disabled="!with_edit" v-show="selIdx > -1" class="btn btn-success btn-sm" @click="addMode(true)">Add New Record</button>

                <button class="btn btn-success btn-sm pull-right" v-if="selIdx === -1 && availableAdd" :disabled="!with_edit" @click="insertRow(objectForAdd)">Save</button>
                <button class="btn btn-default btn-sm pull-right mr5" v-if="selIdx === -1 && availableAdd" :disabled="!with_edit" @click="addMode(false)">Cancel</button>
                <button class="btn btn-danger btn-sm pull-right" v-if="selIdx > -1 && canDeleteRow(selRow)" :disabled="!with_edit" @click="deleteRow(selRow, selIdx)">Delete</button>
            </div>
        </div>
    </div>
</template>

<script>
    import {SpecialFuncs} from "../../classes/SpecialFuncs";

    import CheckRowBackendMixin from "../_Mixins/CheckRowBackendMixin.vue";
    import LinkEmptyObjectMixin from "../_Mixins/LinkEmptyObjectMixin";
    import CanViewEditMixin from "../_Mixins/CanViewEditMixin.vue";

    import TablePagination from "./Pagination/TablePagination.vue";
    import HeaderResizer from "./Header/HeaderResizer.vue";
    import VerticalTableWithHistory from "./VerticalTableWithHistory.vue";
    import BoardView from "./BoardView.vue";
    import CustomTableWithPopup from "./CustomTableWithPopup.vue";
    import RowSpaceButton from "../Buttons/RowSpaceButton.vue";
    import AttachmentsBlock from "../CommonBlocks/AttachmentsBlock.vue";

    export default {
        name: "ListingView",
        mixins: [
            CheckRowBackendMixin,
            LinkEmptyObjectMixin,
            CanViewEditMixin,
        ],
        components: {
            AttachmentsBlock,
            RowSpaceButton,
            CustomTableWithPopup,
            BoardView,
            VerticalTableWithHistory,
            HeaderResizer,
            TablePagination,
        },
        data: function () {
            return {
                listingPanelStatus: this.isLink.listing_panel_status || 'opened',
                activeTab: 'details',
                selIdx: 0,
                listing_field: null,
                listingRows: {
                    width: Number(this.isLink.listing_rows_width || this.tableMeta.listing_rowswi || 250),
                    min_width: this.dcrLinked
                        ? Number(this.dcrLinked.listing_rows_min_width || 70)
                        : Number(this.isLink.listing_rows_min_width || 70),
                },
            }
        },
        props: {
            tableMeta: {
                type: Object,
                required: true,
            },
            allRows: Object|null,
            user: Object,
            page: {
                type: Number,
                default: 1
            },
            rowsCount: Number,
            cellHeight: Number,
            maxCellRows: {
                type: Number,
                default: 0
            },
            behavior: String,
            forbiddenColumns: {
                type: Array,
                default: function () {
                    return [];
                }
            },
            availableColumns: Array,
            isLink: {
                type: Object,
                default: function () {
                    return {};
                }
            },
            with_edit: {
                type: Boolean,
                default: true
            },
            dcrLinked: Object,
            activeHeightWatcher: Boolean,
            noBorders: Boolean,
            link_popup_conditions: Object|Array,
            link_popup_tablerow: Object|Array, // for LinkEmptyObjectMixin.vue
            withAdding: Object, //{active: true/false; immediate: true/false}
            //can be ignored
            fullWidthCell: Boolean,
            isPagination: Boolean,
            isVisible: Boolean,
        },
        computed: {
            selRow() {
                return this.receiveRow(this.selIdx);
            },
            availableAdd() {
                return this.canAdd && (!this.withAdding || this.withAdding.active);
            },
            mainWi() {
                return Number(this.tableMeta.primary_width) || 70;
            },
            mLeft() {
                let part = 100 - this.mainWi;
                if (this.tableMeta.primary_align === 'end') {
                    return part + '%';
                }
                if (this.tableMeta.primary_align === 'center') {
                    return (part/2) + '%';
                }
                return '0';
            },
            manyRowsWi() {
                let listingWi = Number(this.listingRows.width) || 250;
                let minWi = Number(this.listingRows.min_width) || 70;
                return {
                    width: listingWi < 1 ? ((listingWi*100) + '%') : (listingWi + 'px'),
                    minWidth: minWi < 1 ? ((minWi*100) + '%') : (minWi + 'px'),
                    paddingBottom: !this.currentLinkSimple ? '11px' : '2px',
                    paddingLeft: '0',
                    paddingTop: '0',
                };
            },
            canSeeHistory() {
                return Boolean(this.tableMeta && this.selIdx > -1 &&
                    (
                        this.tableMeta._is_owner
                        ||
                        (this.tableMeta._current_right && this.tableMeta._current_right.can_see_history)
                    ));
            },

            currentLinkSimple() {
                return this.isLink && this.isLink.inline_style === 'simple';
            },
            hasAttachments() {
                return this.tableMeta && _.findIndex(this.tableMeta._fields, {f_type: 'Attachment'}) > -1;
            },
            imgCount() {
                let res = 0;
                for (let key in this.selRow) {
                    if (key && key.indexOf('_images_for_') > -1 && this.selRow[key]) {
                        res += this.selRow[key].length;
                    }
                }
                return res;
            },
            fileCount() {
                let res = 0;
                for (let key in this.selRow) {
                    if (key && key.indexOf('_files_for_') > -1 && this.selRow[key]) {
                        res += this.selRow[key].length;
                    }
                }
                return res;
            },
        },
        methods: {
            smallSpace(size) {
                this.tableMeta.row_space_size = size;
                if (this.$root.user.id) {
                    this.$root.updateTable(this.tableMeta, 'row_space_size');
                }
            },
            anotherRow(is_next) {
                if (is_next && this.selIdx < this.allRows.length-1) {
                    this.selIdx++;
                }
                if (!is_next && this.selIdx > 0) {
                    this.selIdx--;
                }
            },
            saveListing() {
                let fld = _.find(this.tableMeta._fields, {field: this.listing_field}) || {};
                if (this.isLink.id) {
                    this.isLink.listing_field_id = fld.id;
                    let fields = _.cloneDeep(this.isLink);//copy object
                    this.$root.deleteSystemFields(fields);
                    axios.put('/ajax/settings/data/link', {
                        table_link_id: this.link.id,
                        fields: fields,
                    }).catch(errors => {
                        Swal('Info', getErrors(errors));
                    });
                } else
                if (this.user.id) {
                    this.tableMeta.listing_fld_id = fld.id || null;
                    axios.put('/ajax/table', {
                        table_id: this.tableMeta.id,
                        listing_fld_id: this.tableMeta.listing_fld_id,
                    });
                } else {
                    this.$root.guestListingFields[this.tableMeta.id] = fld.id || null;
                }
            },
            saveRowsWidth() {
                if (this.isLink.id) {
                    this.isLink.listing_rows_width = parseInt(this.listingRows.width);
                    axios.put('/ajax/settings/data/link', {
                        table_link_id: this.isLink.id,
                        fields: {
                            listing_rows_width: parseInt(this.listingRows.width),
                            table_ref_condition_id: this.isLink.table_ref_condition_id,
                        },
                    });
                } else
                if (this.user.id) {
                    this.tableMeta.listing_rowswi = parseInt(this.listingRows.width);
                    axios.put('/ajax/table', {
                        table_id: this.tableMeta.id,
                        listing_rowswi: this.tableMeta.listing_rowswi,
                    });
                }
            },
            showListingManyRows(i) {
                if (this.listing_field) {
                    let header = _.find(this.tableMeta._fields, {field: this.listing_field});
                    let val = this.receiveRow(i-1)[this.listing_field];
                    if (val && header && this.$root.isMSEL(header.input_type)) {
                        let arr = SpecialFuncs.parseMsel(val);
                        val = '';
                        _.each(arr, (el) => {
                            val += '<span class="is_select">'+el+'</span> ';
                        });
                    }
                    if (header && this.$root.inArray(header.input_type, this.$root.ddlInputTypes)) {
                        val = this.$root.rcShow(this.receiveRow(i-1), this.listing_field);
                    }
                    return val;
                } else {
                    return i;
                }
            },
            receiveRow(idx) {
                return this.allRows[idx] || this.objectForAdd;
            },

            addMode(val) {
                this.selIdx = val ? -1 : (this.allRows.length-1);
                if (val) {
                    this.createObjectForAdd();
                    this.checkRowAutocomplete();
                }
            },
            checkRowAutocomplete() {
                let tableRow = this.receiveRow(this.selIdx);
                let promise = this.checkRowOnBackend(
                    this.tableMeta.id,
                    tableRow,
                    this.getLinkParams(this.link_popup_conditions, this.link_popup_tablerow)
                );
                if (promise) {
                    promise.then((data) => {
                        Number(tableRow.id) ? this.updateRow(tableRow) : null;
                    });
                }
            },
            vertUpdated(tableRow) {
                if (this.selIdx === -1) {
                    this.checkRowAutocomplete();
                } else {
                    this.updateRow(tableRow);
                }
            },

            insertRow(tableRow) {
                if (this.$root.setCheckRequired(this.tableMeta, tableRow)) {
                    this.$emit('added-row', tableRow);
                }
                this.addMode(false);
            },
            updateRow(tableRow) {
                if (this.$root.setCheckRequired(this.tableMeta, tableRow)) {
                    this.$emit('updated-row', tableRow);
                }
            },
            deleteRow(tableRow, index) {
                this.$emit('delete-row', tableRow, index);
            },
            showAddDDLOption(tableHeader, tableRow) {
                this.$emit('show-add-ddl-option', tableHeader, tableRow);
            },
            showSrcRecord(lnk, header, tableRow) {
                this.$emit('show-src-record', lnk, header, tableRow);
            },
            changePage(page) {
                this.$emit('change-page', page);
            },
            totalTbHeightChanged(height) {
                this.$emit('total-tb-height-changed', height);
            },
            toggleHistory(val) {
                this.$emit('toggle-history', val);
            },
        },
        mounted() {
            let field_id = this.$root.guestListingFields[this.tableMeta.id] || 0;
            field_id = field_id || (this.dcrLinked ? Number(this.dcrLinked.listing_field_id) : 0);
            field_id = field_id || Number(this.isLink.listing_field_id);
            field_id = field_id || Number(this.tableMeta.listing_fld_id);
            let fld = _.find(this.tableMeta._fields, {id: field_id}) || {};
            this.listing_field = fld.field || null;

            if (this.withAdding && this.withAdding.immediate && this.availableAdd && this.with_edit) {
                this.addMode(true);
            }
        },
        beforeDestroy() {
        }
    }
</script>

<style lang="scss" scoped>
@import "../CustomPopup/CustomEditPopUp";

.many-rows-collapsed {
    width: 7px;
    cursor: pointer;
    background: #eee;
    margin: 0;
    flex-shrink: 0;
}
.many-rows {
    flex-shrink: 0;
    flex-grow: 0;
    margin-right: 5px;

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
.vert-table {
    padding: 5px;
    border: 1px solid #CCC;
    border-radius: 5px;
    height: calc(100% - 37px);
}
</style>