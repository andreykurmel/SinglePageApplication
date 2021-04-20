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
                    <div class="flex__elem__inner">
                        <div v-if="tableMeta && tableRow" class="full-height basic-data">

                            <div class="basic-menu">
                                <button v-if="globalMeta._is_owner" class="btn btn-default" :class="{active: activeTab === 'inps'}" @click="activeTab = 'inps';redraw_tab=true;">
                                    Input
                                </button>
                                <button v-if="globalMeta._is_owner" class="btn btn-default" :class="{active: activeTab === 'columns'}" @click="activeTab = 'columns';redraw_tab=true;">
                                    Standard
                                </button>
                                <button class="btn btn-default" :class="{active: activeTab === 'customizable'}" @click="activeTab = 'customizable';redraw_tab=true;">
                                    Customizable
                                </button>
                            </div>

                            <div class="flex basic-tab" v-if="!redraw_tab">
                                <div class="flex flex--col many-rows" v-if="tableMeta._fields.length > 1">
                                    <div class="full-height">
                                        <select class="form-control" v-model="columns_field">
                                            <option v-for="fld in tableMeta._fields"
                                                    v-if="$root.systemFields.indexOf(fld.field) === -1"
                                                    :value="fld.field"
                                            >{{ $root.uniqName(fld.name) }}</option>
                                        </select>
                                        <div class="many-rows-content" style="background-color: #FFF;">
                                            <div v-for="f in globalMeta._fields" :class="[(f.id === tableRow.id ? 'active' : '')]" @click="selectAnotherRow(f)">
                                                <label>{{ $root.uniqName(f[columns_field]) }}</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex__elem-remain">
                                    <div class="flex__elem__inner popup-main">
                                        <div class="full-frame">
                                            <vertical-table
                                                    class="vert-table"
                                                    :td="'custom-cell-settings-display'"
                                                    :global-meta="globalMeta"
                                                    :table-meta="tableMeta"
                                                    :settings-meta="settingsMeta"
                                                    :table-row="tableRow"
                                                    :user="user"
                                                    :cell-height="cellHeight"
                                                    :max-cell-rows="maxCellRows"
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
    import {eventBus} from '../../app';

    import CanEditMixin from '../_Mixins/CanViewEditMixin';
    import CheckRowBackendMixin from '../_Mixins/CheckRowBackendMixin';
    import PopupAnimationMixin from '../_Mixins/PopupAnimationMixin';

    import AttachmentsBlock from '../CommonBlocks/AttachmentsBlock';
    import VerticalTable from '../CustomTable/VerticalTable';
    import AddOptionPopup from './AddOptionPopup';

    export default {
        name: "ForSettingsListPop",
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
                activeTab: this.init_active || 'columns',
                columns_field: 'name',
                addOptionPopup: {
                    show: false,
                    tableHeader: null,
                    tableRow: null,
                },
                getPopupWidth: 1000,
                is_small_spacing: localStorage.getItem('is_small_spacing') || 'no',
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
            user: Object,
            cellHeight: Number,
            maxCellRows: Number,
            forbiddenColumns: {
                type: Array,
                default: function () {
                    return [];
                }
            },
            shiftObject: Object,
            init_active: String,
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
        computed: {
            getAvaCols() {
                switch (this.activeTab) {
                    case 'inps': return this.$root.availableInpsColumns;
                    case 'columns': return this.$root.availableSettingsColumns;
                    default: return this.$root.availableNotOwnerDisplayColumns;
                }
            },
        },
        methods: {
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
                this.checkRowOnBackend( this.tableMeta.id, this.tableRow ).then((data) => {
                    this.$emit('backend-row-checked', this.tableRow, data); //STIM 3D APP
                    this.tableRow.id ? this.popupUpdate() : null;
                });
            },

            smallSpace() {
                this.is_small_spacing = (this.is_small_spacing == 'yes' ? 'no' : 'yes');
                localStorage.setItem('is_small_spacing', this.is_small_spacing);
            },
            anotherRow(is_next) {
                this.$emit('another-row', is_next);
            },
            selectAnotherRow(fld) {
                this.$emit('select-another-row', fld);
            },
        },
        mounted() {
            this.runAnimation();
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
    .flex__elem__inner {
        background-color: inherit;
    }

    .basic-data {
        background-color: inherit;
        padding: 5px 5px 7px 5px;

        .basic-menu {
            button {
                background-color: #CCC;
                outline: 0;
            }
            .active {
                background-color: #FFF;
            }
        }

        .basic-tab {
            background-color: inherit;
            height: calc(100% - 30px);
            position: relative;
            top: -3px;
            border: 1px solid #CCC;
            border-radius: 4px;
        }
    }
</style>