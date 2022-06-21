<template>
    <div class="flex flex--col full-height">
        <div v-if="dcrObject.dcr_form_line_top" :style="{
            borderBottom: (dcrObject.dcr_form_line_type == 'line' ? (dcrObject.dcr_form_line_thick || 1)+'px solid '+(dcrObject.dcr_form_line_color || '#d3e0e9') : null),
            marginBottom: (dcrObject.dcr_form_line_type == 'space' ? (dcrObject.dcr_form_line_thick || 1)+'px' : null),
        }"></div>
        <template v-for="avails in availGroups">
            <div v-show="!!avails.showthis" :class="[scrlFlow ? '' : 'flex__elem-remain']">
                <div class="flex__elem__inner">
                    <div class="new-row new-row--main"
                         :style="{
                                backgroundColor: frm_color,
                                boxShadow: box_shad,
                                borderTopLeftRadius: (dcrObject.dcr_form_line_type == 'space' ? dcrObject.dcr_form_line_radius+'px' : ''),
                                borderTopRightRadius: (dcrObject.dcr_form_line_type == 'space' ? dcrObject.dcr_form_line_radius+'px' : ''),
                                borderBottomLeftRadius: (dcrObject.dcr_form_line_type == 'space' ? dcrObject.dcr_form_line_radius+'px' : ''),
                                borderBottomRightRadius: (dcrObject.dcr_form_line_type == 'space' ? dcrObject.dcr_form_line_radius+'px' : ''),
                            }"
                    >
                        <div class="flex flex--col" style="background-color: transparent">
                            <div v-if="partHasAttachments(avails.fields)">
                                <button class="btn btn-default"
                                        :class="{active: avails.activetab === 'details'}"
                                        @click="avails.activetab = 'details'"
                                >Details</button>
                                <button class="btn btn-default"
                                        :class="{active: avails.activetab === 'attachments'}"
                                        @click="avails.activetab = 'attachments'"
                                >Attachments (P: {{ imgCount }}, F: {{ fileCount }})</button>
                            </div>
                            <div v-show="avails.activetab === 'details'"
                                 :class="[scrlFlow ? '' : 'flex__elem-remain']"
                                 class="form-tab"
                                 :ref="'scroll_elem'"
                                 @scroll="scrollEvent"
                            >
                                <div class="flex__elem__inner form-inner">
                                    <vertical-table
                                            :td="$root.tdCellComponent($root.tableMeta.is_system)"
                                            :global-meta="$root.tableMeta"
                                            :table-meta="$root.tableMeta"
                                            :settings-meta="settingsMeta"
                                            :table-row="tableRow"
                                            :user="user"
                                            :cell-height="$root.cellHeight"
                                            :max-cell-rows="$root.maxCellRows"
                                            :behavior="'list_view'"
                                            :with_edit="!!with_edit"
                                            :available-columns="avails.avail_columns"
                                            :forbidden-columns="$root.systemFields"
                                            :dcr-object="dcrObject"
                                            :dcr-linked-rows="dcrLinkedRows"
                                            style="background-color: transparent"
                                            :style="{color: txtClr}"
                                            @linked-update="hasChanges = true"
                                            @updated-cell="checkRowAutocomplete"
                                            @show-src-record="showSrcRecord"
                                            @show-add-ddl-option="showAddDDLOption"
                                            @showed-elements="(val) => {changeShows(avails, val)}"
                                    ></vertical-table>
                                </div>
                            </div>
                            <div v-show="avails.activetab === 'attachments'" :class="[scrlFlow ? '' : 'flex__elem-remain']" class="form-tab">
                                <div class="flex__elem__inner">
                                    <attachments-block
                                            :table-meta="$root.tableMeta"
                                            :table-row="tableRow"
                                            :role="'add'"
                                            :user="user"
                                            :reqest_edit="with_edit"
                                            :behavior="'request_view'"
                                            :tab_style="{ minHeight: 'max-content' }"
                                            :special_params="specialParams"
                                            style="background-color: transparent"
                                    ></attachments-block>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div v-if="dcrObject.dcr_form_line_bot && !!avails.showthis" :style="{
                borderBottom: (dcrObject.dcr_form_line_type == 'line' ? (dcrObject.dcr_form_line_thick || 1)+'px solid '+(dcrObject.dcr_form_line_color || '#d3e0e9') : null),
                marginBottom: (dcrObject.dcr_form_line_type == 'space' ? (dcrObject.dcr_form_line_thick || 1)+'px' : null),
            }"></div>
        </template>
        <div class="new-row popup-buttons flex flex--center-v flex--space"
             :style="{
                    backgroundColor: frm_color,
                    boxShadow: box_shad,
                    borderTopLeftRadius: (dcrObject.dcr_form_line_type == 'space' ? dcrObject.dcr_form_line_radius+'px' : ''),
                    borderTopRightRadius: (dcrObject.dcr_form_line_type == 'space' ? dcrObject.dcr_form_line_radius+'px' : ''),
                    borderBottomLeftRadius: dcrObject.dcr_form_line_radius+'px',
                    borderBottomRightRadius: dcrObject.dcr_form_line_radius+'px',
                }"
        >
            <span class="req-fields">
                <span class="required-wildcart">*</span> Input Required<br>
                Never submit passwords through TablDA DCR Forms.
            </span>

            <div class="buttons-block">
                <button class="btn btn-success"
                        v-if="availSave(tableRow)"
                        :style="$root.themeButtonStyle"
                        @click="addRow('submit', 'Saved')"
                >Save</button>
                <button class="btn btn-success"
                        v-if="availSubmit(tableRow)"
                        :style="$root.themeButtonStyle"
                        :disabled="!canAddRow"
                        @click="addRow('submit', 'Submitted')"
                >Submit</button>
                <button class="btn btn-success"
                        v-if="availUpdate(tableRow)"
                        :disabled="!hasChanges"
                        :style="$root.themeButtonStyle"
                        @click="addRow('submit', 'Updated')"
                >Update</button>
                <button class="btn btn-success"
                        :style="$root.themeButtonStyle"
                        :disabled="!canAddRow"
                        v-if="availAdd(tableRow)"
                        @click="addRow('insert', '')"
                >Add</button>
            </div>
        </div>

        <!--Add Select Option Popup-->
        <add-option-popup
                v-if="addOptionPopup.show"
                :table-header="addOptionPopup.tableHeader"
                :table-row="addOptionPopup.tableRow"
                :table-meta="$root.tableMeta"
                :settings-meta="$root.settingsMeta"
                :user="$root.user"
                :dcr_hash="dcrObject.dcr_hash"
                @updated-row="checkRowAutocomplete"
                @hide="addOptionPopup.show = false"
                @show-src-record="showSrcRecord"
        ></add-option-popup>
    </div>
</template>

<script>
    import {RefCondHelper} from "../../classes/helpers/RefCondHelper";
    import {SpecialFuncs} from "../../classes/SpecialFuncs";
    import {RequestFuncs} from "./RequestFuncs";

    import RequestMixin from "./RequestMixin.vue";
    import CanEditMixin from './../_Mixins/CanViewEditMixin';
    import CheckRowBackendMixin from './../_Mixins/CheckRowBackendMixin';
    import SortFieldsForVerticalMixin from './../_Mixins/SortFieldsForVerticalMixin.vue';

    import VerticalTable from './../CustomTable/VerticalTable';
    import AttachmentsBlock from "./../CommonBlocks/AttachmentsBlock";
    import AddOptionPopup from "../CustomPopup/AddOptionPopup";

    export default {
        name: "RequestFormView",
        mixins: [
            RequestMixin,
            CanEditMixin,
            CheckRowBackendMixin,
            SortFieldsForVerticalMixin,
        ],
        components: {
            AddOptionPopup,
            AttachmentsBlock,
            VerticalTable,
        },
        data: function () {
            return {
                addOptionPopup: {
                    show: false,
                    tableHeader: null,
                    tableRow: null,
                },
                availGroups: [],
                hasChanges: false,
            };
        },
        computed: {
            specialParams() {
                return SpecialFuncs.specialParams();
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
            },
            txtClr() {
                return SpecialFuncs.smartTextColorOnBg(this.dcrObject.dcr_form_bg_color);
            },
        },
        props:{
            user: Object,
            tableRow: Object|null,
            settingsMeta: Object,
            cellHeight: Number,
            canAddRow: Boolean|Number,
            dcrObject: Object,
            dcrLinkedRows: Object,
            footer_height: Number,
            frm_color: String,
            box_shad: String,
            scrlFlow: Boolean,
            with_edit: Boolean,
        },
        watch: {
            'tableRow.id': function (val) {
                this.checkRowAutocomplete();
            }
        },
        methods: {
            //sys methods
            inArray(item, array) {
                return $.inArray(item, array) > -1;
            },
            addRow(param, new_status) {
                if (this.$root.setCheckRequired(this.$root.tableMeta, this.tableRow)) {
                    this.hasChanges = false;

                    let status_hdr = RequestFuncs.recordUrlHeader(this.$root.tableMeta, this.dcrObject, 'dcr_record_status_id');
                    if (status_hdr) {
                        this.tableRow[status_hdr.field] = new_status;
                    }

                    this.tableRow._new_status = new_status;
                    this.$emit(param, this.tableRow);
                }
            },
            scrollEvent(e) {
                this.$emit('scroll-fields');
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
                //front-end RowGroups and CondFormats
                this.hasChanges = true;
                RefCondHelper.updateRGandCFtoRow(this.$root.tableMeta, this.tableRow);

                this.checkRowOnBackend( this.$root.tableMeta.id, this.tableRow, null, this.specialParams );
            },
            //src record and tables function
            showSrcRecord(lnk, field, tableRow) {
                this.$emit('show-src-record', lnk, field, tableRow, 'list_view');
            },
            //
            makeGroups(tableMeta, tableRow) {
                let fld_objects = this.sortAndFilterFields(tableMeta, tableMeta._fields, tableRow, true);
                this.availGroups = [];
                let ava = [];
                _.each(fld_objects, (fld) => {
                    if (fld.is_dcr_section && this.scrlFlow) {
                        this.availGroups.push({
                            fields: ava,
                            avail_columns: _.map(ava, 'field'),
                            showthis:1,
                            activetab:'details'
                        });
                        ava = [];
                    }
                    ava.push(fld);
                });
                if (ava && ava.length) {
                    this.availGroups.push({
                        fields: ava,
                        avail_columns: _.map(ava, 'field'),
                        showthis:1,
                        activetab:'details'
                    });
                }
            },
            changeShows(avails, val) {
                avails.showthis = val;
            },
        },
        mounted() {
            this.makeGroups(this.$root.tableMeta, this.tableRow);
            this.checkRowOnBackend( this.$root.tableMeta.id, this.tableRow, null, this.specialParams );
            this.$emit('set-form-elem', _.first(this.$refs.scroll_elem));
        }
    }
</script>

<style lang="scss" scoped>
    .new-row {
        padding: 10px;
        background-color: #fff;

        .req-fields {
            font-size: 14px;
            font-style: italic;
            color: #F00;
        }

        .form-tab {
            height: 100%;
            position: relative;
            top: -3px;
            /*border-top: 1px solid #CCC;*/
            /*border-bottom: 1px solid #CCC;*/
            overflow: auto;
            border-radius: 4px;
            padding: 5px;
            background-color: transparent;

            .form-inner {
                padding: 0 10px;
            }
        }
    }
    .new-row--main {
        border-radius: 2px;
        height: 100%;
    }

    .popup-buttons {
        padding-top: 3px;
    }

    .flex__elem-remain {
        overflow: visible;
    }

    @media all and (max-width: 767px) {
        .new-row {
            display: block;
        }
        .buttons-block {
            text-align: right;
        }
    }
</style>