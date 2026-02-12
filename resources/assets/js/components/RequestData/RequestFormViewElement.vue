<template>
    <div :class="[scrlGroups ? '' : 'flex__elem-remain']">
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
                    <div v-if="partHasAttachments(availGr.fields) || Object.keys(availGr.fieldTabs).length > 1">
                        <button
                            v-for="(tab, key) in availGr.fieldTabs"
                            class="btn btn-default mr5"
                            :class="{active: availGr.activetab === key}"
                            @click="availGr.activetab = key"
                        >
                            {{ key }}
                        </button>
                        <button v-if="partHasAttachments(availGr.fields)"
                                class="btn btn-default"
                                :class="{active: availGr.activetab === 'attachments'}"
                                @click="availGr.activetab = 'attachments'"
                        >Attachments (P: {{ imgCount }}, F: {{ fileCount }})</button>
                    </div>
                    <div v-for="(tab, key) in availGr.fieldTabs"
                         v-show="availGr.activetab === key"
                         :class="[scrlGroups ? '' : 'flex__elem-remain']"
                         class="form-tab"
                         :ref="'scroll_elem'"
                         @scroll="scrollEvent"
                    >
                        <div class="flex__elem__inner form-inner">
                            <vertical-table
                                v-if="tab.fields && tab.fields.length"
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
                                :available-columns="tab.fields"
                                :forbidden-columns="$root.systemFields"
                                :dcr-object="dcrObject"
                                :dcr-linked-rows="dcrLinkedRows"
                                :extra-pivot-fields="dcrObject._fields_pivot"
                                :can-see-history="false"
                                :visible="availGr.activetab === key"
                                style="background-color: transparent"
                                :style="{color: txtClr}"
                                @linked-update="emitLinkedChanges"
                                @updated-cell="checkRowAutocomplete"
                                @show-src-record="showSrcRecord"
                                @show-add-ddl-option="showAddDDLOption"
                                @showed-elements="changeShows"
                            ></vertical-table>
                            <template v-if="tab.dcr_lnks && tab.dcr_lnks.length">
                                <div class="full-height" v-for="dcrLnk in tab.dcr_lnks" :key="dcrLnk.id">
                                    <label v-if="dcrLnk.header" style="color: black; font-size: 16px;">{{ dcrLnk.header }}</label>
                                    <div class="full-height relative pb5">
                                        <vertical-linked-table
                                            :parent-meta="$root.tableMeta"
                                            :parent-row="tableRow"
                                            :linked-rows-object="dcrLinkedRows"
                                            :dcr-linked-table="dcrLnk"
                                            :with_edit="!!with_edit"
                                            :is-visible="availGr.activetab === key"
                                            @linked-update="emitLinkedChanges"
                                        ></vertical-linked-table>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                    <div v-show="availGr.activetab === 'attachments'" :class="[scrlGroups ? '' : 'flex__elem-remain']" class="form-tab">
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
</template>

<script>
    import {SpecialFuncs} from "../../classes/SpecialFuncs";

    import AttachmentsBlock from "./../CommonBlocks/AttachmentsBlock";

    import CanEditMixin from "../_Mixins/CanViewEditMixin";
    import VerticalLinkedTable from "../CustomTable/VerticalLinkedTable.vue";

    export default {
        name: "RequestFormViewElement",
        mixins: [
            CanEditMixin,
        ],
        components: {
            VerticalLinkedTable,
            AttachmentsBlock,
        },
        data: function () {
            return {
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
            availGr: Object,
            user: Object,
            tableRow: Object|null,
            settingsMeta: Object,
            cellHeight: Number,
            dcrObject: Object,
            dcrLinkedRows: Object,
            frm_color: String,
            box_shad: String,
            scrlGroups: Boolean,
            with_edit: Boolean,
        },
        watch: {
        },
        methods: {
            emitLinkedChanges() {
                this.$emit('linked-rows-changed');
            },
            scrollEvent(e) {
                this.$emit('scroll-fields');
            },
            showAddDDLOption(tableHeader, tableRow) {
                this.$emit('show-add-ddl', tableHeader, tableRow);
            },
            checkRowAutocomplete() {
                this.$emit('check-row-autocomplete');
            },
            showSrcRecord(lnk, field, tableRow) {
                this.$emit('show-src-record', lnk, field, tableRow);
            },
            changeShows(val) {
                // Later can be removed. Temporary left for:
                // <div v-show="!!availGr.showthis" :class="[scrlGroups ? '' : 'flex__elem-remain']">
                this.availGr.showthis = val;
            },
        },
        mounted() {
        }
    }
</script>

<style lang="scss" scoped>
    .new-row {
        padding: 10px 10px 0 10px;
        background-color: #fff;

        .form-tab {
            height: 100%;
            position: relative;
            padding-top: 5px;
            /*border-top: 1px solid #CCC;*/
            /*border-bottom: 1px solid #CCC;*/
            overflow: auto;
            border-radius: 4px;
            //padding: 5px;
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

    .flex__elem-remain {
        overflow: visible;
    }

    @media all and (max-width: 767px) {
        .new-row {
            display: block;
        }
    }
</style>