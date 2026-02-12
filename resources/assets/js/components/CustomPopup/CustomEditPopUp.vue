<template>
    <div>
        <div class="popup-wrapper" @click.self="emitClose(false)"></div>
        <div class="popup" :style="getPopupStyle()">
            <div class="flex flex--col">
                <div class="popup-header">
                    <div class="drag-bkg" draggable="true" @dragstart="dragPopSt()" @drag="dragPopup()"></div>
                    <div class="flex">
                        <div class="flex__elem-remain">
                            <span v-html="getPopUpHeader()"></span>&nbsp;
                        </div>
                        <div class="" style="position: relative">
                            <span class="fa fa-cog pull-right header-btn" @click="showSettingsCustPopup()" style="right: 25px;"></span>
                            <span class="glyphicon glyphicon-remove pull-right header-btn" @click="emitClose(false)"></span>
                        </div>
                    </div>
                </div>
                <div class="popup-content flex__elem-remain">
                    <div class="flex__elem__inner popup-main">
                        <div v-if="fieldTabs" class="flex flex--col">
                            <div class="popup-menu">
                                <button
                                    v-for="(tab, key) in fieldTabs"
                                    class="btn btn-default mr5"
                                    :class="{active: activeTab === key}"
                                    @click="activeTab = key"
                                >
                                    {{ key }}
                                </button>
                                <button class="btn btn-default"
                                        :class="{active: activeTab === 'attachments'}"
                                        @click="activeTab = 'attachments'"
                                        v-if="hasAttachments"
                                >
                                    Attachments (P: {{ imgCount }}, F: {{ fileCount }})
                                </button>

                                <div class="right-icons flex flex--automargin pull-right">
                                    <srv-block
                                        :table-meta="tableMeta"
                                        :table-row="tableRow"
                                        :with-delimiter="false"
                                        style="font-size: 16px; margin-right: 5px;"
                                    ></srv-block>

                                    <button class="btn btn-sm btn-primary blue-gradient"
                                            style="padding: 3px;"
                                            @click="showOverviewCondForm()"
                                            title="Overview of CFs applied to fields of this record"
                                            :disabled="no_clicks"
                                            :style="$root.themeButtonStyle">
                                        <img src="/assets/img/conditional_formatting_small_1.png" width="25" height="25" style="top: 0;"/>
                                    </button>
                                    <row-space-button
                                        :init_size="tableMeta.row_space_size"
                                        :disabled_btn="no_clicks"
                                        @changed-space="smallSpace"
                                    ></row-space-button>
                                    <template v-if="role === 'update'">
                                        <button class="btn btn-sm btn-primary blue-gradient"
                                                @click="anotherRow(false)"
                                                :disabled="no_clicks"
                                                :style="$root.themeButtonStyle">
                                            <i class="fas fa-arrow-left"></i>
                                        </button>
                                        <button class="btn btn-sm btn-primary blue-gradient"
                                                @click="anotherRow(true)"
                                                :disabled="no_clicks"
                                                :style="$root.themeButtonStyle">
                                            <i class="fas fa-arrow-right"></i>
                                        </button>
                                    </template>
                                </div>
                            </div>
                            <div class="flex__elem-remain popup-tab"
                                 v-for="(tab, key) in fieldTabs"
                                 v-show="activeTab === key"
                            >
                                <div class="flex__elem__inner">
                                    <vertical-table-with-history
                                        :td="input_component_name"
                                        :global-meta="globalMeta"
                                        :table-meta="tableMeta"
                                        :settings-meta="settingsMeta"
                                        :table-row="tableRow"
                                        :user="user"
                                        :cell-height="$root.cellHeight"
                                        :max-cell-rows="$root.maxCellRows"
                                        :behavior="behavior"
                                        :forbidden-columns="forbiddenColumns"
                                        :available-columns="tab.fields"
                                        :can-see-history="canSeeHistory"
                                        :is-add-row="role === 'add'"
                                        :with_edit="with_edit"
                                        :can-redefine-width="with_edit"
                                        :visible="activeTab === key"
                                        class="vert-table"
                                        @updated-cell="autocompleteUpdated"
                                        @toggle-history="toggleHistory"
                                        @show-add-ddl-option="showAddDDLOption"
                                        @show-src-record="showSrcRecord"
                                    ></vertical-table-with-history>
                                </div>
                            </div>
                            <div class="flex__elem-remain popup-tab" v-show="activeTab === 'attachments'">
                                <div class="flex__elem__inner">
                                    <attachments-block
                                            :table-meta="tableMeta"
                                            :table-row="tableRow"
                                            :role="role"
                                            :user="user"
                                            :behavior="behavior"
                                            :with_edit="with_edit"
                                            @updated-cell="autocompleteUpdated"
                                    ></attachments-block>
                                </div>
                            </div>
                            <div class="popup-buttons">
                                <button class="btn btn-success btn-sm"
                                        v-if="canAdd && role === 'add'"
                                        :style="$root.themeButtonStyle"
                                        :disabled="no_clicks || !with_edit"
                                        @click="popupInsert"
                                >Add</button>
                                <button class="btn btn-danger btn-sm"
                                        v-if="canDeleteRow(tableRow) && role === 'update'"
                                        :disabled="no_clicks || !with_edit"
                                        @click="popupDelete"
                                >Delete</button>

                                <!--<button class="btn btn-info btn-sm pull-right"-->
                                        <!--v-if="(canSomeEdit || behavior === 'settings_display') && role === 'update'"-->
                                        <!--@click="popupUpdate"-->
                                <!--&gt;Update</button>-->
                                <button class="btn btn-success btn-sm pull-right"
                                        v-if="canAdd && role === 'update' && inArray(behavior, ['list_view','link_popup'])"
                                        :disabled="no_clicks || !with_edit"
                                        @click="popupCopy"
                                >Copy to New</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="resizer" draggable="true" @dragstart="dragResizeStart" @drag="dragResizeDo" @dragend="sizesToBackend"></div>
        </div>


        <!--Add Select Option Popup-->
        <add-option-popup
                v-if="addOptionPopup.show"
                :table-header="addOptionPopup.tableHeader"
                :table-row="addOptionPopup.tableRow"
                :table-meta="tableMeta"
                :settings-meta="settingsMeta"
                :user="user"
                @updated-row="checkRowAutocomplete"
                @hide="addOptionPopup.show = false"
                @show-src-record="showSrcRecord"
        ></add-option-popup>


        <!--if form is for 'add'-->
        <info-popup
                v-if="show_add_warning"
                :title_html="'Info'"
                :content_html="'Record not added yet. Click `Add` to complete adding the record or `Discard` to discard it.'"
                :add_btn="'Add'"
                :cancel_btn="'Discard'"
                @add-click="popupInsert()"
                @cancel-click="forceClose(false)"
                @hide="show_add_warning = false"
        ></info-popup>
    </div>
</template>

<script>
    import {eventBus} from '../../app';

    import {SpecialFuncs} from "../../classes/SpecialFuncs";

    import CanEditMixin from '../_Mixins/CanViewEditMixin';
    import CheckRowBackendMixin from '../_Mixins/CheckRowBackendMixin';
    import PopupAnimationMixin from '../_Mixins/PopupAnimationMixin';
    import SortFieldsForVerticalMixin from '../_Mixins/SortFieldsForVerticalMixin';

    import AttachmentsBlock from '../CommonBlocks/AttachmentsBlock';
    import AddOptionPopup from './AddOptionPopup';
    import InfoPopup from "./InfoPopup";
    import VerticalTableWithHistory from "../CustomTable/VerticalTableWithHistory";
    import SrvBlock from "../CommonBlocks/SrvBlock";
    import RowSpaceButton from "../Buttons/RowSpaceButton.vue";

    export default {
        name: "CustomEditPopUp",
        mixins: [
            CanEditMixin,
            CheckRowBackendMixin,
            PopupAnimationMixin,
            SortFieldsForVerticalMixin,
        ],
        components: {
            RowSpaceButton,
            SrvBlock,
            VerticalTableWithHistory,
            InfoPopup,
            AddOptionPopup,
            AttachmentsBlock,
        },
        data: function () {
            return {
                fieldTabs: null,
                was_updated: false,
                show_add_warning: false,
                addOptionPopup: {
                    show: false,
                    tableHeader: null,
                    tableRow: null,
                },
                activeTab: 'details',
                open_history: false,
            };
        },
        props:{
            idx: {
                type: Number,
                default: 0
            },
            globalMeta: {
                type: Object,
                default: function () {
                    return {};
                }
            },
            tableMeta: Object,
            settingsMeta: {
                type: Object,
                default: function () {
                    return {};
                }
            },
            tableRow: Object|null,
            role: String,
            behavior: String,
            input_component_name: {
                type: String,
                default: 'custom-input-table-data'
            },
            forbiddenColumns: {
                type: Array,
                default: function () {
                    return [];
                }
            },
            availableColumns: Array,
            user: Object,
            cellHeight: Number,
            maxCellRows: Number,
            use_theme: Boolean,
            shiftObject: Object,
            no_clicks: Boolean,
            with_edit: {
                type: Boolean,
                default: true
            },
            isLink: Object,//CanViewEditMixin.vue
        },
        computed: {
            canSeeHistory() {
                return this.role !== 'add'
                    &&
                    (
                        this.globalMeta._is_owner
                        ||
                        (this.globalMeta._current_right && this.globalMeta._current_right.can_see_history)
                    );
            },
            getPopupWidth() {
                let add_pixel = this.open_history ? 305 : 0;
                return (to_float(this.tableMeta.vert_tb_width_px) || 768) + add_pixel;
            },
            getPopupHeight() {
                return (to_float(this.tableMeta.vert_tb_height) || 80) + '%';
            },
            imgCount() {
                let res = 0;
                for (let key in this.tableRow) {
                    if (key && key.indexOf('_images_for_') > -1 && this.tableRow[key]) {
                        res += this.tableRow[key].length;
                    }
                }
                return res;
            },
            fileCount() {
                let res = 0;
                for (let key in this.tableRow) {
                    if (key && key.indexOf('_files_for_') > -1 && this.tableRow[key]) {
                        res += this.tableRow[key].length;
                    }
                }
                return res;
            }
        },
        watch: {
            tableRow(row) {
                this.checkOverview();
            },
        },
        methods: {
            storeSizes(width_px, height_px) {
                if (Number(this.tableMeta.vert_tb_width_px_min)) {
                    width_px = Math.max(width_px, Number(this.tableMeta.vert_tb_width_px_min));
                }
                if (Number(this.tableMeta.vert_tb_width_px_max)) {
                    width_px = Math.min(width_px, Number(this.tableMeta.vert_tb_width_px_max));
                }
                this.tableMeta.vert_tb_width_px = width_px;

                let height_perc = Number((height_px / window.innerHeight) * 100).toFixed(0);
                if (Number(this.tableMeta.vert_tb_height_min)) {
                    height_perc = Math.max(height_perc, Number(this.tableMeta.vert_tb_height_min));
                }
                if (Number(this.tableMeta.vert_tb_height_max)) {
                    height_perc = Math.min(height_perc, Number(this.tableMeta.vert_tb_height_max));
                }
                this.tableMeta.vert_tb_height = height_perc;
            },
            sizesToBackend() {
                this.$root.sm_msg_type = 1;
                axios.put('/ajax/table', {
                    table_id: this.tableMeta.id,
                    vert_tb_width_px: this.tableMeta.vert_tb_width_px,
                    vert_tb_height: this.tableMeta.vert_tb_height,
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
            getPopUpHeader() {
                return this.$root.getPopUpHeader(this.tableMeta, this.tableRow);
            },
            //sys methods
            popupInsert() {
                if (this.$root.setCheckRequired(this.tableMeta, this.tableRow)) {
                    this.$emit('popup-insert', this.tableRow);
                    this.emitClose(true);
                }
            },
            popupUpdate() {
                if (this.$root.setCheckRequired(this.tableMeta, this.tableRow)) {
                    this.tableRow.id ? this.$emit('popup-update', this.tableRow) : null;
                }
            },
            popupDelete() {
                this.$emit('popup-delete', this.tableRow);
                this.emitClose(true);
            },
            popupCopy() {
                if (this.$root.setCheckRequired(this.tableMeta, this.tableRow)) {
                    this.$emit('popup-copy', this.tableRow);
                }
            },
            hideMenu(e) {
                if (window.full_size_img_block > 0) {
                    return;
                }

                if (this.is_vis && e.keyCode === 27 && this.$root.tablesZidx <= this.zIdx && !this.$root.e__used) {
                    this.emitClose(false);
                    this.$root.set_e__used(this);
                }
                if (e.target.nodeName === 'BODY' && e.shiftKey && e.keyCode === 191) {//shift + '?'
                    this.emitClose(false);
                }
            },
            toggleHistory(open_history) {
                this.open_history = open_history;
            },
            emitClose(row_added_deleted) {
                //localStorage.setItem('no_ping', '');
                if (!row_added_deleted && this.role === 'add' && this.was_updated) {
                    this.show_add_warning = true;
                } else {
                    this.forceClose(row_added_deleted);
                }
            },
            forceClose(row_added_deleted) {
                this.$root.tablesZidxDecrease();
                this.$emit('popup-close', row_added_deleted);
            },
            //src record and tables function
            showSrcRecord(lnk, field, tableRow) {
                this.$emit('show-src-record', lnk, field, tableRow);
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
            autocompleteUpdated(row, hdr) {
                if (hdr) {
                    this.was_updated = true;
                }
                this.checkRowAutocomplete();
            },
            checkRowAutocomplete() {
                this.tableRow.id = isNaN(this.tableRow.id) ? this.tableRow.id : parseInt(this.tableRow.id);
                if (this.tableRow.id) {
                    this.popupUpdate();
                } else {
                    let promis = this.checkRowOnBackend(this.tableMeta.id, this.tableRow, null, null, true);
                    if (promis) {
                        promis.then((data) => {
                            this.$emit('backend-row-checked', this.tableRow, data); //STIM 3D APP
                        });
                    }
                }
            },

            smallSpace(size) {
                this.tableMeta.row_space_size = size;
                if (this.$root.user.id) {
                    this.$root.updateTable(this.tableMeta, 'row_space_size');
                }
            },
            anotherRow(is_next) {
                this.$emit('another-row', is_next);
            },
            showSettingsCustPopup() {
                eventBus.$emit('show-table-settings-all-popup', {tab:'general', filter:'edit'});
            },
            rhUpdate() {
                this.tableRow.row_hash = uuidv4();
            },
            showOverviewCondForm() {
                eventBus.$emit('show-overview-format-popup', this.tableMeta.db_name, this.tableRow);
            },
            openAnotherRowAndOverview(db_name, row_id, next) {
                if (db_name == this.tableMeta.db_name && row_id == this.tableRow.id) {
                    this.anotherRow(next);
                }
            },
            checkOverview() {
                if (this.$root.overviewFormatWaiting) {
                    this.showOverviewCondForm();
                }
            },
            preparePopTabs() {
                let fields = this.availableColumns && this.availableColumns.length
                    ? _.filter(this.tableMeta._fields, (fld) => this.inArray(fld.field, this.availableColumns))
                    : this.tableMeta._fields;
                fields = _.filter(fields, (fld) => this.canMainView(this.tableMeta, fld, this.tableRow));

                let tabs = SpecialFuncs.getFieldTabs(fields);
                this.fieldTabs = tabs;
                this.activeTab = _.first(Object.keys(tabs));
            },
        },
        mounted() {
            this.preparePopTabs();

            //localStorage.setItem('no_ping', 'true');
            this.$root.tablesZidxIncrease();
            this.zIdx = this.$root.tablesZidx;
            this.runAnimation({anim_transform:'none'});

            if (this.role === 'add') {
                this.tableRow._temp_id = this.tableRow._temp_id || uuidv4();
            }

            this.checkOverview();

            eventBus.$on('global-keydown', this.hideMenu);
            eventBus.$on('table-settings-all-popup__closed', this.rhUpdate);
            eventBus.$on('overview-formats__open-another-row', this.openAnotherRowAndOverview);
        },
        beforeDestroy() {
            eventBus.$off('global-keydown', this.hideMenu);
            eventBus.$off('table-settings-all-popup__closed', this.rhUpdate);
            eventBus.$off('overview-formats__open-another-row', this.openAnotherRowAndOverview);
        }
    }
</script>

<style lang="scss" scoped>
    @import "./CustomEditPopUp";
</style>