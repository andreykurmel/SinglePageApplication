<template>
    <div>
        <div class="popup-wrapper" @click.self="emitClose(false)"></div>
        <div class="popup" :style="getPopupStyle()">
            <div class="flex flex--col">
                <div class="popup-header">
                    <div class="drag-bkg" draggable="true" @dragstart="dragPopSt()" @drag="dragPopup()"></div>
                    <div class="flex">
                        <div class="flex__elem-remain">
                            [{{ tableMeta.name }}] <span v-html="getPopUpHeader()"></span>
                        </div>
                        <div class="" style="position: relative">
                            <span class="fa fa-cog pull-right header-btn" @click="showSettingsCustPopup()" style="right: 25px;"></span>
                            <span class="glyphicon glyphicon-remove pull-right header-btn" @click="emitClose(false)"></span>
                        </div>
                    </div>
                </div>
                <div class="popup-content flex__elem-remain">
                    <div class="flex__elem__inner popup-main">
                        <div class="flex flex--col">
                            <div class="popup-menu">
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
                                    <srv-block
                                        :table-meta="tableMeta"
                                        :table-row="tableRow"
                                        :with-delimiter="false"
                                        style="font-size: 16px; margin-right: 5px;"
                                    ></srv-block>

                                    <button class="btn btn-sm btn-primary blue-gradient"
                                            @click="smallSpace()"
                                            :disabled="no_clicks"
                                            :style="$root.themeButtonStyle">
                                        <img v-if="is_small_spacing === 'no'" src="/assets/img/elevator-dn1.png" width="15" height="15"/>
                                        <img v-else="" src="/assets/img/elevator-up1.png" width="15" height="15"/>
                                    </button>
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
                            <div class="flex__elem-remain popup-tab" v-show="activeTab === 'details'">
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
                                        :available-columns="availableColumns"
                                        :can-see-history="canSeeHistory"
                                        :is-add-row="role === 'add'"
                                        :is_small_spacing="is_small_spacing"
                                        class="vert-table"
                                        @updated-cell="checkRowAutocomplete"
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
                                            @updated-cell="checkRowAutocomplete"
                                    ></attachments-block>
                                </div>
                            </div>
                            <div class="popup-buttons">
                                <button class="btn btn-success btn-sm"
                                        v-if="canAdd && role === 'add' && inArray(behavior, ['list_view','favorite'])"
                                        :style="$root.themeButtonStyle"
                                        :disabled="no_clicks"
                                        @click="popupInsert"
                                >Add</button>
                                <button class="btn btn-danger btn-sm"
                                        v-if="canDeleteRow(tableRow) && role === 'update' && inArray(behavior, ['list_view','favorite','link_popup'])"
                                        :disabled="no_clicks"
                                        @click="popupDelete"
                                >Delete</button>

                                <!--<button class="btn btn-info btn-sm pull-right"-->
                                        <!--v-if="(canSomeEdit || behavior === 'settings_display') && role === 'update'"-->
                                        <!--@click="popupUpdate"-->
                                <!--&gt;Update</button>-->
                                <button class="btn btn-success btn-sm pull-right"
                                        v-if="canAdd && role === 'update' && inArray(behavior, ['list_view','link_popup'])"
                                        :disabled="no_clicks"
                                        @click="popupCopy"
                                >Copy to New</button>
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

    import CanEditMixin from '../_Mixins/CanViewEditMixin';
    import CheckRowBackendMixin from '../_Mixins/CheckRowBackendMixin';
    import PopupAnimationMixin from '../_Mixins/PopupAnimationMixin';

    import AttachmentsBlock from '../CommonBlocks/AttachmentsBlock';
    import AddOptionPopup from './AddOptionPopup';
    import InfoPopup from "./InfoPopup";
    import VerticalTableWithHistory from "../CustomTable/VerticalTableWithHistory";
    import SrvBlock from "../CommonBlocks/SrvBlock";

    export default {
        name: "CustomEditPopUp",
        mixins: [
            CanEditMixin,
            CheckRowBackendMixin,
            PopupAnimationMixin,
        ],
        components: {
            SrvBlock,
            VerticalTableWithHistory,
            InfoPopup,
            AddOptionPopup,
            AttachmentsBlock,
        },
        data: function () {
            return {
                //
                show_add_warning: false,
                addOptionPopup: {
                    show: false,
                    tableHeader: null,
                    tableRow: null,
                },
                activeTab: 'details',
                open_history: false,
                is_small_spacing: readLocalStorage('is_small_spacing') || 'no',
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
                return 768 + add_pixel;
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
        methods: {
            getPopUpHeader() {
                let headers = this.tableMeta._fields;
                let row = this.tableRow;
                let res = [];
                _.each(headers, (hdr) => {
                    if (hdr.popup_header) {
                        res.push('{' + this.$root.uniqName(hdr.name) + '}: ' + (row ? row[hdr.field] : '') );
                    }
                });
                return res.length ? ' - '+res.join('<br>') : '';
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
                    //this.emitClose(true);
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
            emitClose(were_changes) {
                if (!were_changes && this.role === 'add') {
                    this.show_add_warning = true;
                } else {
                    this.forceClose(were_changes);
                }
            },
            forceClose(were_changes) {
                this.$emit('popup-close', were_changes);
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
            checkRowAutocomplete() {
                if (this.tableRow.id) {
                    this.popupUpdate();
                } else {
                    let promis = this.checkRowOnBackend(this.tableMeta.id, this.tableRow);
                    if (promis) {
                        promis.then((data) => {
                            this.$emit('backend-row-checked', this.tableRow, data); //STIM 3D APP
                        });
                    }
                }
            },

            smallSpace() {
                this.is_small_spacing = (this.is_small_spacing == 'yes' ? 'no' : 'yes');
                setLocalStorage('is_small_spacing', this.is_small_spacing);
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
        },
        mounted() {
            this.$root.tablesZidx += 10;
            this.zIdx = this.$root.tablesZidx;
            this.runAnimation({anim_transform:'none'});

            if (this.role === 'add') {
                this.tableRow._temp_id = this.tableRow._temp_id || uuidv4();
                this.checkRowAutocomplete();
            }

            eventBus.$on('global-keydown', this.hideMenu);
            eventBus.$on('table-settings-all-popup__closed', this.rhUpdate);
        },
        beforeDestroy() {
            eventBus.$off('global-keydown', this.hideMenu);
            eventBus.$off('table-settings-all-popup__closed', this.rhUpdate);
        }
    }
</script>

<style lang="scss" scoped>
    @import "./CustomEditPopUp";
</style>