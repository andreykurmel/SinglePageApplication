<template>
    <div>
        <div class="popup-wrapper" @click.self="$emit('popup-close', false)"></div>
        <div class="popup" :style="getPopupStyle()">
            <div class="flex flex--col">
                <div class="popup-header">
                    <div class="drag-bkg" draggable="true" @dragstart="dragPopSt()" @drag="dragPopup()"></div>
                    <div class="flex">
                        <div class="flex__elem-remain">
                            [Settings/Basics] - {Name}: {{ $root.uniqName(tableRow.name) }}
                        </div>
                        <div class="" style="position: relative">
                            <span class="glyphicon glyphicon-remove pull-right header-btn" @click="$emit('popup-close', false)"></span>
                        </div>
                    </div>
                </div>
                <div class="popup-content flex__elem-remain">
                    <div class="flex__elem__inner popup-main">
                        <div class="flex flex--col">
                            <div class="popup-menu">
                                <button v-if="isAvail('calendar_tab')"
                                        class="btn btn-default"
                                        :class="{active: activeTab === 'calendar_tab'}"
                                        @click="activeTab = 'calendar_tab';redraw_tab=true;"
                                >
                                    <span>Basics</span>
                                </button>
                                <button v-if="isAvail('gantt_tab')"
                                        class="btn btn-default"
                                        :class="{active: activeTab === 'gantt_tab'}"
                                        @click="activeTab = 'gantt_tab';redraw_tab=true;"
                                >
                                    <span>Basics</span>
                                </button>
                                <button v-if="isAvail('map_tab')"
                                        class="btn btn-default"
                                        :class="{active: activeTab === 'map_tab'}"
                                        @click="activeTab = 'map_tab';redraw_tab=true;"
                                >
                                    <span>Basics</span>
                                </button>
                                <button v-if="isAvail('inps')"
                                        class="btn btn-default"
                                        :class="{active: activeTab === 'inps'}"
                                        @click="activeTab = 'inps';redraw_tab=true;"
                                >
                                    <span>Input</span>
                                </button>
                                <button v-if="isAvail('standard')"
                                        class="btn btn-default"
                                        :class="{active: activeTab === 'standard'}"
                                        @click="activeTab = 'standard';redraw_tab=true;"
                                >
                                    <span>Standard</span>
                                </button>
                                <button v-if="isAvail('customizable')"
                                        class="btn btn-default"
                                        :class="{active: activeTab === 'customizable'}"
                                        @click="activeTab = 'customizable';redraw_tab=true;"
                                >
                                    <span>Customizable</span>
                                </button>
                                <button v-if="isAvail('bas_popup')"
                                        class="btn btn-default"
                                        :class="{active: activeTab === 'bas_popup'}"
                                        @click="activeTab = 'bas_popup';redraw_tab=true;"
                                >
                                    <span>Pop-up</span>
                                </button>

                                <div class="right-icons flex flex--automargin pull-right">
                                    <button class="btn btn-sm btn-primary blue-gradient" @click="smallSpace()" :style="$root.themeButtonStyle">
                                        <img v-if="is_small_spacing === 'no'" src="/assets/img/elevator-dn1.png" width="15" height="15"/>
                                        <img v-else="" src="/assets/img/elevator-up1.png" width="15" height="15"/>
                                    </button>
                                    <button class="btn btn-sm btn-primary blue-gradient" @click="anotherRow(false)" :style="$root.themeButtonStyle">
                                        <i class="fas fa-arrow-left"></i>
                                    </button>
                                    <button class="btn btn-sm btn-primary blue-gradient" @click="anotherRow(true)" :style="$root.themeButtonStyle">
                                        <i class="fas fa-arrow-right"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="flex__elem-remain popup-tab" v-if="!redraw_tab">

                                <div class="flex__elem__inner">
                                    <vertical-table
                                            class="vert-table"
                                            :td="'custom-cell-settings-display'"
                                            :global-meta="globalMeta"
                                            :table-meta="tableMeta"
                                            :settings-meta="settingsMeta"
                                            :table-row="tableRow"
                                            :user="user"
                                            :cell-height="1"
                                            :max-cell-rows="0"
                                            :behavior="'settings_display'"
                                            :available-columns="getAvaCols"
                                            :forbidden-columns="forbiddenColumns"
                                            :is_small_spacing="is_small_spacing"
                                            @updated-cell="checkRowAutocomplete"
                                            @show-add-ddl-option="showAddDDLOption"
                                            @show-src-record="showSrcRecord"
                                    ></vertical-table>
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
                :table-meta="tableMeta"
                :settings-meta="settingsMeta"
                :user="user"
                @updated-row="checkRowAutocomplete"
                @hide="addOptionPopup.show = false"
                @show-src-record="showSrcRecord"
        ></add-option-popup>
    </div>
</template>

<script>
    import {SpecialFuncs} from '../../classes/SpecialFuncs';

    import {eventBus} from '../../app';

    import CanEditMixin from '../_Mixins/CanViewEditMixin';
    import CheckRowBackendMixin from '../_Mixins/CheckRowBackendMixin';
    import PopupAnimationMixin from '../_Mixins/PopupAnimationMixin';

    import AttachmentsBlock from '../CommonBlocks/AttachmentsBlock';
    import VerticalTable from '../CustomTable/VerticalTable';
    import AddOptionPopup from './AddOptionPopup';

    export default {
        name: "ForSettingsPopUp",
        mixins: [
            CanEditMixin,
            CheckRowBackendMixin,
            PopupAnimationMixin,
        ],
        components: {
            AddOptionPopup,
            AttachmentsBlock,
            VerticalTable,
        },
        data: function () {
            return {
                redraw_tab: false,
                addOptionPopup: {
                    show: false,
                    tableHeader: null,
                    tableRow: null,
                },
                activeTab: null,
                getPopupWidth: 768,
                is_small_spacing: readLocalStorage('is_small_spacing') || 'no',
            };
        },
        watch: {
            redraw_tab(val) {
                if (val) {
                    this.$nextTick(() => {
                        this.redraw_tab = false;
                    });
                }
            },
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
            user: Object,
            cellHeight: Number,
            maxCellRows: Number,
            shiftObject: Object,
            extAvailTabs: Array,
        },
        computed: {
            forbiddenColumns() {
                return SpecialFuncs.forbiddenCustomizables(this.globalMeta);
            },
            getAvaCols() {
                switch (this.activeTab) {
                    case 'calendar_tab': return this.$root.availableCalendarColumns;
                    case 'gantt_tab': return this.$root.availableGanttColumns;
                    case 'map_tab': return this.$root.availableMapColumns;
                    case 'inps': return this.$root.availableInpsColumns;
                    case 'standard': return this.$root.availableSettingsColumns;
                    case 'bas_popup': return this.$root.availablePopupDisplayColumns;
                    default: return this.$root.availableNotOwnerDisplayColumns;
                }
            },
            availTabs() {
                return this.extAvailTabs
                    || (this.globalMeta._is_owner ? ['inps','standard','customizable','bas_popup'] : ['customizable']);
            },
        },
        methods: {
            isAvail(tab) {
                return this.availTabs.indexOf(tab) > -1;
            },
            //sys methods
            popupInsert() {
                if (this.$root.setCheckRequired(this.tableMeta, this.tableRow)) {
                    this.$emit('popup-insert', this.tableRow);
                    this.$emit('popup-close', true);
                }
            },
            popupUpdate() {
                if (this.$root.setCheckRequired(this.tableMeta, this.tableRow)) {
                    this.tableRow.id ? this.$emit('popup-update', this.tableRow) : null;
                    //this.$emit('popup-close', true);
                }
            },
            popupDelete() {
                this.$emit('popup-delete', this.tableRow);
                this.$emit('popup-close', true);
            },
            popupCopy() {
                if (this.$root.setCheckRequired(this.tableMeta, this.tableRow)) {
                    this.$emit('popup-copy', this.tableRow);
                }
            },
            hideMenu(e) {
                if (this.is_vis && e.keyCode === 27 && !this.$root.e__used) {
                    this.$emit('popup-close', false);
                    this.$root.set_e__used(this);
                }
                if (e.target.nodeName === 'BODY' && e.shiftKey && e.keyCode === 191) {//shift + '?'
                    this.$emit('popup-close', false);
                }
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
                    let promise = this.checkRowOnBackend(this.tableMeta.id, this.tableRow);
                    if (promise) {
                        promise.then((data) => {
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
            }
        },
        mounted() {
            this.runAnimation();
            this.activeTab = _.first(this.availTabs) === 'inps' ? 'standard' : _.first(this.availTabs);
            eventBus.$on('global-keydown', this.hideMenu);
        },
        beforeDestroy() {
            eventBus.$off('global-keydown', this.hideMenu);
        }
    }
</script>

<style lang="scss" scoped>
    @import "./CustomEditPopUp";

    .popup-wrapper {
        z-index: 1300;
    }
    .popup {
        z-index: 1350;
    }
</style>