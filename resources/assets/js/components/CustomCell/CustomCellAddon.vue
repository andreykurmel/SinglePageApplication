<template>
    <td :style="getCustomCellStyle"
        class="td-custom"
        ref="td"
        @click="showEdit()"
        @mouseenter="show_expand = $root.inArray(tableHeader.f_type, ['String', 'Text', 'Long Text', 'Auto String'])"
        @mouseleave="show_expand = false"
    >
        <div class="td-wrapper" :style="getTdWrappStyle()">

            <div v-if="tableHeader.f_type === 'Button' && !isAddRow">
                <button class="btn btn-default btn-sm blue-gradient"
                        style="padding: 2px 7px"
                        :style="$root.themeButtonStyle"
                        @click="$emit('show-def-val-popup', tableRow)"
                >{{ tableHeader.name }}</button>
            </div>
            <div v-else class="wrapper-inner" :style="getWrapperStyle()">
                <div class="inner-content select2-transparent" :style="{textAlign: tableHeader.col_align}">

                    <div v-if="headerFtype() === 'Boolean'">
                        <label class="switch_t" :style="{height: Math.min(maxCellHGT, 17)+'px'}">
                            <input type="checkbox" :disabled="disabledCheckBox" v-model="rowValue" @change="updateValue()">
                            <span class="toggler round" :class="[disabledCheckBox ? 'disabled' : '']"></span>
                        </label>
                    </div>

                    <div v-else-if="tableHeader.f_type === 'RefTable'" class="inner-content">
                        <div v-for="tbId in editValueAsMsel()">
                            <a target="_blank"
                               title="Open the “Visiting” view in a new tab."
                               :href="getTableLink(tbId, '__visiting_url')"
                               @click.stop=""
                               v-html="getTableLink(tbId)"></a>
                            <a v-if="refIsOwner(tbId)"
                               title="Open the source table in a new tab."
                               target="_blank"
                               :href="getTableLink(tbId, '__url')"
                               @click.stop="">(Table)</a>
                        </div>
                    </div>

                    <input
                        v-else-if="tableHeader.f_type === 'Radio' && !isAddRow"
                        :checked="rowValue"
                        :disabled="disabledCheckBox"
                        @click="updateRadio()"
                        type="radio"
                        ref="inline_input"
                        class="checkbox-input"/>

                    <show-attachments-block
                        v-else-if="headerFtype() === 'Attachment'"
                        :show-type="'scroll'"
                        :table-header="tableHeader"
                        :table-meta="tableMeta"
                        :table-row="tableRow"
                        :can-edit="true"
                        :is-grid-view="!isVertTable"
                        :clear-before="1"
                        @update-signal="sendUpdateSignal"
                    ></show-attachments-block>

                    <tablda-colopicker
                        v-else-if="tableHeader.f_type === 'Color'"
                        :init_color="rowValue"
                        :can_edit="canCellEdit"
                        :avail_null="true"
                        @set-color="updateCheckedDDL"
                    ></tablda-colopicker>

                    <a v-else-if="tableHeader.field === 'rg_colgroup_id'"
                       title="Open column group in popup."
                       @click.stop="showGroupsPopup('col', tableRow.rg_colgroup_id)"
                    >{{ showField() }}</a>

                    <a v-else-if="tableHeader.f_type === 'DataRange'"
                       title="Open data range in popup."
                       @click.stop="showRG(tableRow[tableHeader.field], globalMeta)"
                    >{{ showField() }}</a>

                    <div v-else-if="tableHeader.field === '_make_reports' && !isAddRow">
                        <span @click="callMakeReports()" class="glyphicon glyphicon-triangle-right" style="color: #00F; cursor: pointer;"></span>
                    </div>

                    <div v-else-if="tableHeader.field === 'additional_attributes'"
                         style="cursor: pointer"
                         @click="showAttrPopup()"
                    >{{ showField() }}&nbsp;</div>

                    <div v-else v-html="showField()"></div>

                </div>
            </div>


            <cell-table-data-expand
                v-if="show_expand && ((tableRow.template_source === 'URL' && tableHeader.field === 'template_file') || tableHeader.field === 'cloud_report_folder')"
                style="background-color: #FFF;"
                :table-meta="tableMeta"
                :table-row="tableRow"
                :table-header="tableHeader"
                :uniqid="uuid"
                :can-edit="!!canCellEdit"
                :user="user"
            ></cell-table-data-expand>

        </div>



        <!-- ABSOLUTE EDITINGS -->
        <div v-if="isEditing()" class="cell-editing">

            <formula-helper
                v-if="tableHeader.input_type === 'Formula'"
                :user="$root.user"
                :table-meta="globalMeta"
                :table-row="tableRow"
                :table-header="tableHeader"
                :header-key="tableHeader.field"
                :can-edit="canCellEdit"
                :fixed_pos="true"
                :foreign_element="$refs.td"
                :pop_width="'100%'"
                :no_uniform="true"
                @close-formula="hideEdit"
                @set-formula="updateRow"
            ></formula-helper>

            <select-with-folder-structure
                v-else-if="tableHeader.f_type === 'RefTable'"
                :cur_val="rowValue || []"
                :available_tables="$root.settingsMeta.available_tables"
                :user="user"
                :is_obj_attr="'id'"
                :is_multiple="$root.isMSEL(tableHeader.input_type)"
                :for_single_select="true"
                @sel-changed="(val) => {rowValue = val;}"
                @sel-closed="hideEdit();updateValue();"
                class="form-control full-height"
                :style="getEditStyle">
            </select-with-folder-structure>

            <tablda-select-simple
                v-else-if="tableHeader.field === 'field_id' && behavior === 'settings_grouping_stat'"
                :options="availStatFields()"
                :table-row="tableRow"
                :hdr_field="tableHeader.field"
                :fixed_pos="true"
                :style="getEditStyle"
                @selected-item="updateCheckedDDL"
                @hide-select="hideEdit"
            ></tablda-select-simple>

            <tablda-select-simple
                v-else-if="tableHeader.f_type === 'DataRange'"
                :options="getRGr(globalMeta)"
                :table-row="tableRow"
                :hdr_field="tableHeader.field"
                :fixed_pos="true"
                :embed_func_txt="'Add New'"
                :style="getEditStyle"
                @selected-item="updateCheckedDDL"
                @hide-select="hideEdit"
                @embed-func="showRG(null, globalMeta)"
            ></tablda-select-simple>

            <tablda-select-simple
                v-else-if="tableHeader.field === 'report_template_id'"
                :options="availTemplates()"
                :table-row="tableRow"
                :hdr_field="tableHeader.field"
                :fixed_pos="true"
                :style="getEditStyle"
                @selected-item="updateCheckedDDL"
                @hide-select="hideEdit"
            ></tablda-select-simple>

            <tablda-select-simple
                v-else-if="headerFtype() === 'RefField' || (tableHeader.field === 'var_object_id' && tableRow.variable_type === 'field')"
                :options="nameFields()"
                :table-row="tableRow"
                :hdr_field="tableHeader.field"
                :fixed_pos="true"
                :style="getEditStyle"
                @selected-item="updateCheckedDDL"
                @hide-select="hideEdit"
            ></tablda-select-simple>

            <tablda-select-simple
                v-else-if="tableHeader.field === 'var_object_id' && tableRow.variable_type === 'bi'"
                :options="nameCharts()"
                :table-row="tableRow"
                :hdr_field="tableHeader.field"
                :fixed_pos="true"
                :style="getEditStyle"
                @selected-item="updateCheckedDDL"
                @hide-select="hideEdit"
            ></tablda-select-simple>

            <tablda-select-simple
                v-else-if="headerFtype() === 'RefRefLink'"
                :options="refLinks()"
                :table-row="tableRow"
                :hdr_field="tableHeader.field"
                :fixed_pos="true"
                :style="getEditStyle"
                @selected-item="updateCheckedDDL"
                @hide-select="hideEdit"
            ></tablda-select-simple>

            <tablda-select-simple
                v-else-if="tableHeader.field === 'rg_alignment'"
                :options="availAlignments()"
                :table-row="tableRow"
                :hdr_field="tableHeader.field"
                :fixed_pos="true"
                :style="getEditStyle"
                @selected-item="updateCheckedDDL"
                @hide-select="hideEdit"
            ></tablda-select-simple>

            <tablda-select-simple
                v-else-if="tableHeader.field === 'connected_cloud_id'"
                :options="userClouds()"
                :table-row="tableRow"
                :hdr_field="tableHeader.field"
                :fixed_pos="true"
                :can_empty="true"
                :style="getEditStyle"
                @selected-item="updateCheckedDDL"
                @hide-select="hideEdit"
            ></tablda-select-simple>

            <tablda-select-simple
                v-else-if="tableHeader.field === 'openai_key_id'"
                :options="globalAIs()"
                :table-row="tableRow"
                :hdr_field="tableHeader.field"
                :fixed_pos="true"
                :style="getEditStyle"
                @selected-item="updateCheckedDDL"
                @hide-select="hideEdit"
            ></tablda-select-simple>

            <tablda-select-simple
                v-else-if="tableHeader.field === 'default_state'"
                :options="[
                    {val:'collapsed', show:'Collapsed'},
                    {val:'expanded', show:'Expanded'},
                ]"
                :table-row="tableRow"
                :hdr_field="tableHeader.field"
                :fixed_pos="true"
                :style="getEditStyle"
                @selected-item="updateCheckedDDL"
                @hide-select="hideEdit"
            ></tablda-select-simple>

            <tablda-select-simple
                v-else-if="tableHeader.field === 'template_source'"
                :options="[
                    {val:'Upload', show:'Upload'},
                    {val:'URL', show:'URL'},
                ]"
                :table-row="tableRow"
                :hdr_field="tableHeader.field"
                :fixed_pos="true"
                :style="getEditStyle"
                @selected-item="updateCheckedDDL"
                @hide-select="hideEdit"
            ></tablda-select-simple>

            <tablda-select-simple
                v-else-if="tableHeader.field === 'doc_type'"
                :options="[
                    {val:'ms_word', show:'MS Word'},
                    {val:'gdoc', show:'Gdoc'},
                    {val:'txt', show:'TXT'},
                    {val:'data', show:'Data'},
                ]"
                :table-row="tableRow"
                :hdr_field="tableHeader.field"
                :fixed_pos="true"
                :style="getEditStyle"
                @selected-item="updateCheckedDDL"
                @hide-select="hideEdit"
            ></tablda-select-simple>

            <tablda-select-simple
                v-else-if="tableHeader.field === 'rg_colgroup_id'"
                :options="availColumns()"
                :table-row="tableRow"
                :hdr_field="tableHeader.field"
                :fixed_pos="true"
                :style="getEditStyle"
                @selected-item="updateCheckedDDL"
                @hide-select="hideEdit"
            ></tablda-select-simple>

            <tablda-select-simple
                v-else-if="tableHeader.field === 'sorting'"
                :options="[
                    {val:null, show:''},
                    {val:'asc', show:'A-Z'},
                    {val:'desc', show:'Z-A'},
                ]"
                :table-row="tableRow"
                :hdr_field="tableHeader.field"
                :fixed_pos="true"
                :style="getEditStyle"
                @selected-item="updateCheckedDDL"
                @hide-select="hideEdit"
            ></tablda-select-simple>

            <tablda-select-simple
                v-else-if="tableHeader.field === 'stat_fn'"
                :options="availRowSumFormulas()"
                :table-row="tableRow"
                :hdr_field="tableHeader.field"
                :fixed_pos="true"
                :can_empty="true"
                :style="getEditStyle"
                @selected-item="updateCheckedDDL"
                @hide-select="hideEdit"
            ></tablda-select-simple>

            <tablda-select-simple
                v-else-if="tableHeader.field === 'variable_type'"
                :options="[
                    {val:'field', show:'Field'},
                    {val:'rows', show:'Rows'},
                    {val:'bi', show:'BI'},
                ]"
                :table-row="tableRow"
                :hdr_field="tableHeader.field"
                :fixed_pos="true"
                :style="getEditStyle"
                @selected-item="updateCheckedDDL"
                @hide-select="hideEdit"
            ></tablda-select-simple>

            <tablda-select-simple
                v-else-if="tableHeader.field === 'variable'"
                :options="templateVariables()"
                :table-row="tableRow"
                :hdr_field="tableHeader.field"
                :fixed_pos="true"
                :allowed_search="true"
                :style="getEditStyle"
                @selected-item="updateCheckedDDL"
                @hide-select="hideEdit"
            ></tablda-select-simple>

            <input
                v-else-if="inArray(headerFtype(), ['Attachment'])"
                type="text"
                @blur="hideEdit()"
                @paste="pasteAttachment"
                ref="inline_input"
                class="form-control full-height"
                placeholder="Ctrl + V to Paste File"
                :style="getEditStyle"/>

            <input
                v-else
                v-model="rowValue"
                @blur="updateRow()"
                ref="inline_input"
                class="form-control full-height"
                :placeholder="getPlaceholdr()"
                :style="getEditStyle">

        </div>
        <!-- ABSOLUTE EDITINGS -->

    </td>
</template>

<script>
import {SelectedCells} from '../../classes/SelectedCells';
import {SpecialFuncs} from "../../classes/SpecialFuncs";
import {FileHelper} from "../../classes/helpers/FileHelper";
import {Endpoints} from "../../classes/Endpoints";
import {ReportVariable} from "../../classes/ReportVariable";

import {eventBus} from '../../app';

import CellStyleMixin from '../_Mixins/CellStyleMixin.vue';
import DataRangeMixin from '../_Mixins/DataRangeMixin.vue';

import TabldaSelectSimple from "./Selects/TabldaSelectSimple.vue";
import ShowAttachmentsBlock from "../CommonBlocks/ShowAttachmentsBlock";
import CellTableDataExpand from "./InCell/CellTableDataExpand";
import TabldaColopicker from "./InCell/TabldaColopicker.vue";
import SelectWithFolderStructure from "./InCell/SelectWithFolderStructure.vue";

export default {
    name: "CustomCellAddon",
    components: {
        SelectWithFolderStructure,
        TabldaColopicker,
        CellTableDataExpand,
        ShowAttachmentsBlock,
        TabldaSelectSimple,
    },
    mixins: [
        CellStyleMixin,
        DataRangeMixin,
    ],
    data: function () {
        return {
            show_expand: false,
            no_height_limit: true,
            uuid: uuidv4(),
            editing: false,
            oldValue: null,
            emptySpecific: {
                table_gantt_id: this.parentRow.id,
                table_field_id: this.tableRow.id,
                gantt_tooltip: 0,
                gantt_left_header: 0,
                is_gantt_group: 0,
                is_gantt_parent_group: 0,
                is_gantt_main_group: 0,
                is_gantt_name: 0,
                is_gantt_parent: 0,
                is_gantt_start: 0,
                is_gantt_end: 0,
                is_gantt_progress: 0,
                is_gantt_color: 0,
                is_gantt_label_symbol: 0,
                is_gantt_milestone: 0,
            },
        }
    },
    props:{
        globalMeta: {
            type: Object,
            default: function () {
                return {};
            }
        },
        tableMeta: Object,
        tableHeader: Object,
        tableRow: Object,
        allRows: Object|null,
        rowIndex: Number,
        cellHeight: Number,
        maxCellRows: Number,
        user: Object,
        with_edit: {
            type: Boolean,
            default: true
        },
        isAddRow: Boolean,
        isVertTable: Boolean,
        parentRow: {
            type: Object,
            default: function () {
                return {};
            }
        },
        behavior: String,
    },
    computed: {
        stfStyle() {
            return {
                width: '100%',
                backgroundColor: this.$root.systemFields.indexOf(this.tableRow.field) > -1 ? '#EEE' : null,
            };
        },
        getCustomCellStyle() {
            let obj = this.getCellStyle();
            if (
                this.disabledCheckBox
                ||
                (this.tableHeader.field === 'var_object_id' && !this.tableRow.variable_type)
                ||
                (this.tableHeader.field === 'var_object_id' && this.tableRow.variable_type === 'rows')
            ) {
                obj.backgroundColor = '#EEE';
            }
            return obj;
        },
        disabledCheckBox() {
            return !this.canCellEdit
                ||
                (
                    this.inArray(this.tableHeader.field, ['gantt_left_header'])
                    &&
                    (_.find(this.parentRow._specifics, {is_gantt_main_group: 1}) || _.find(this.parentRow._specifics, {is_gantt_parent_group: 1}))
                );
        },
        canCellEdit() {
            return this.with_edit
                && this.tableHeader.field !== 'table_field_id'
                && this.tableHeader.field !== '_make_reports'
                && this.tableHeader.field !== '_button'
                && (this.tableHeader.field !== 'is_gantt_main_group' || _.find(this.parentRow._specifics, {is_gantt_parent_group: 1}))
                && (this.tableHeader.field !== 'is_gantt_parent_group' || _.find(this.parentRow._specifics, {is_gantt_group: 1}))
                && (this.tableHeader.field !== 'var_object_id' || this.tableRow.variable_type)
                && (this.tableHeader.field !== 'var_object_id' || this.tableRow.variable_type !== 'rows')
                && (this.tableHeader.field !== 'cloud_report_folder' || this.tableRow.connected_cloud_id)
                && (this.isAddRow || this.tableHeader.field !== 'additional_attributes');
        },
        linkedSpecific() {
            return this.behavior === 'settings_gantt_specific'
                ? _.find(this.parentRow._specifics, {table_field_id: Number(this.tableRow.id)}) || this.emptySpecific
                : null;
        },
        rowValue: {
            get() {
                switch (this.tableHeader.field) {
                    case 'table_field_id': return this.tableRow.name;
                    case 'gantt_left_header': return this.linkedSpecific.gantt_left_header ? 1 : 0;
                    case 'gantt_tooltip': return this.linkedSpecific.gantt_tooltip ? 1 : 0;
                    case 'is_gantt_group': return this.linkedSpecific.is_gantt_group ? 1 : 0;
                    case 'is_gantt_parent_group': return this.linkedSpecific.is_gantt_parent_group ? 1 : 0;
                    case 'is_gantt_main_group': return this.linkedSpecific.is_gantt_main_group ? 1 : 0;
                    case 'is_gantt_name': return this.linkedSpecific.is_gantt_name ? 1 : 0;
                    case 'is_gantt_parent': return this.linkedSpecific.is_gantt_parent ? 1 : 0;
                    case 'is_gantt_start': return this.linkedSpecific.is_gantt_start ? 1 : 0;
                    case 'is_gantt_end': return this.linkedSpecific.is_gantt_end ? 1 : 0;
                    case 'is_gantt_progress': return this.linkedSpecific.is_gantt_progress ? 1 : 0;
                    case 'is_gantt_color': return this.linkedSpecific.is_gantt_color ? 1 : 0;
                    case 'is_gantt_label_symbol': return this.linkedSpecific.is_gantt_label_symbol ? 1 : 0;
                    case 'is_gantt_milestone': return this.linkedSpecific.is_gantt_milestone ? 1 : 0;
                    default: return this.tableRow[this.tableHeader.field];
                }
            },
            set(newValue) {
                switch (this.tableHeader.field) {
                    case 'table_field_id': break;
                    case 'gantt_left_header': this.linkedSpecific.gantt_left_header = newValue; break;
                    case 'gantt_tooltip': this.linkedSpecific.gantt_tooltip = newValue; break;
                    case 'is_gantt_group': this.linkedSpecific.is_gantt_group = newValue; break;
                    case 'is_gantt_parent_group': this.linkedSpecific.is_gantt_parent_group = newValue; break;
                    case 'is_gantt_main_group': this.linkedSpecific.is_gantt_main_group = newValue; break;
                    case 'is_gantt_name': this.linkedSpecific.is_gantt_name = newValue; break;
                    case 'is_gantt_parent': this.linkedSpecific.is_gantt_parent = newValue; break;
                    case 'is_gantt_start': this.linkedSpecific.is_gantt_start = newValue; break;
                    case 'is_gantt_end': this.linkedSpecific.is_gantt_end = newValue; break;
                    case 'is_gantt_progress': this.linkedSpecific.is_gantt_progress = newValue; break;
                    case 'is_gantt_color': this.linkedSpecific.is_gantt_color = newValue; break;
                    case 'is_gantt_label_symbol': this.linkedSpecific.is_gantt_label_symbol = newValue; break;
                    case 'is_gantt_milestone': this.linkedSpecific.is_gantt_milestone = newValue; break;
                    default: this.tableRow[this.tableHeader.field] = newValue; break;
                }
            }
        }
    },
    methods: {
        headerFtype() {
            return this.tableRow.connected_cloud_id && this.tableRow.template_source === 'URL' && this.tableHeader.field === 'template_file'
                ? 'String'
                : this.tableHeader.f_type;
        },
        getPlaceholdr() {
            switch (this.tableHeader.field) {
                case 'template_file': return 'Template URL in the cloud';
                case 'cloud_report_folder': return 'Folder URL in the cloud';
                default: return '';
            }
        },
        inArray(item, array) {
            return $.inArray(item, array) > -1;
        },
        isEditing() {
            return this.editing && this.canCellEdit && !this.$root.global_no_edit;
        },
        showEdit() {
            if (!this.canCellEdit || this.inArray(this.headerFtype(), ['Boolean', 'Radio', 'Color'])) {
                return;
            }
            this.editing = true;
            if (this.isEditing()) {
                this.oldValue = this.rowValue;
                this.$nextTick(function () {
                    if (this.$refs.inline_input) {
                        this.$refs.inline_input.focus();
                    }
                });
            } else {
                this.no_key_handler = true;
                this.editing = false;
            }
        },
        hideEdit() {
            this.editing = false;
        },
        updateCheckedDDL(item) {
            this.rowValue = item;
            this.updateValue();
        },
        updateRadio() {
            this.rowValue = this.rowValue ? 0 : 1;
            this.updateValue();
        },
        updateValue() {
            if (this.rowValue !== this.oldValue) {
                if (this.headerFtype() === 'Boolean' || this.headerFtype() === 'Radio') {
                    this.rowValue = this.rowValue ? 1 : 0;
                }
                if (this.tableHeader.field === 'variable_type') {
                    this.tableRow.var_object_id = null;
                }

                this.sendUpdateSignal();
            }
        },
        sendUpdateSignal(editVal) {
            let tableRow = this.behavior === 'settings_gantt_specific' ? this.linkedSpecific : this.tableRow;
            tableRow._changed_field = this.tableHeader.field;
            if (editVal) {
                tableRow[this.tableHeader.field] = editVal;
            }
            this.$emit('updated-cell', tableRow, this.tableHeader);
        },
        updateRow() {
            this.hideEdit();
            this.updateValue();
        },
        editValueAsMsel() {
            return SpecialFuncs.parseMsel(this.tableRow[this.tableHeader.field]);
        },
        refIsOwner(tbId) {
            let tb = _.find(this.$root.settingsMeta.available_tables, {id: Number(tbId)});
            return tb && tb.user_id == this.user.id;
        },
        getTableLink(tbId, link) {
            let tb = _.find(this.$root.settingsMeta.available_tables, {id: Number(tbId)}) || {};
            return link
                ? tb[link]
                : (tb.id == this.globalMeta.id
                    ? '<span style="color: #00F;">SELF</span>'
                    : ((tb._referenced ? ('@' + tb._referenced + '/') : '') + tb.name));
        },
        showField() {
            let res = '';
            if (this.tableHeader.f_type === 'DataRange') {
                let val = this.tableRow[this.tableHeader.field];
                res = val === null ? '' : this.rgName(val, this.globalMeta);
            }
            else if (this.tableHeader.field === 'report_template_id') {
                let field = _.find(this.availTemplates(), {val: Number(this.rowValue)});
                res = field ? field.html || field.show : this.rowValue;
            }
            else if (this.tableHeader.field === 'default_state') {
                res = SpecialFuncs.capitalizeFirst(this.rowValue || '');
            }
            else if (this.headerFtype() === 'RefField') {
                let field = _.find(this.nameFields(), {val: Number(this.rowValue)});
                res = field ? field.html || field.show : this.rowValue;
            }
            else if (this.headerFtype() === 'RefRefLink') {
                let field = _.find(this.refLinks(), {val: this.rowValue});
                res = field ? field.html || field.show : this.rowValue;
            }
            else if (this.tableHeader.field === 'template_file' || this.tableHeader.field === 'cloud_report_folder') {
                res = this.rowValue
                    ? '<a href="' + this.rowValue + '">' + this.rowValue + '</a>'
                    : '';
            }
            else if (this.tableHeader.field === 'connected_cloud_id') {
                let field = _.find(this.userClouds(), {val: this.rowValue});
                res = field ? field.show || field.val : this.rowValue;
            }
            else if (this.tableHeader.field === 'openai_key_id') {
                let field = _.find(this.globalAIs(), {val: this.rowValue});
                res = field ? field.show || field.val : this.rowValue;
            }
            else if (this.tableHeader.field === 'doc_type') {
                switch (this.rowValue) {
                    case 'ms_word': res = 'MS Word'; break;
                    case 'gdoc': res = 'Gdoc'; break;
                    case 'txt': res = 'TXT'; break;
                    case 'data': res = 'Data'; break;
                }
            }
            else if (this.tableHeader.field === 'sorting') {
                switch (this.rowValue) {
                    case 'asc': res = 'A-Z'; break;
                    case 'desc': res = 'Z-A'; break;
                }
            }
            else if (this.tableHeader.field === 'stat_fn') {
                let func = _.find(this.availRowSumFormulas(), {val: this.rowValue});
                res = func ? func.show : this.rowValue;
            }
            else if (this.tableHeader.field === 'rg_colgroup_id') {
                let col = _.find(this.availColumns(), {val: Number(this.rowValue)});
                res = col ? col.show : this.rowValue;
            }
            else if (this.tableHeader.field === 'rg_alignment') {
                let col = _.find(this.availAlignments(), {val: this.rowValue});
                res = col ? col.show : this.rowValue;
            }
            else if (this.tableHeader.field === 'field_id' && this.behavior === 'settings_grouping_stat') {
                let fld = _.find(this.availStatFields(), {val: Number(this.rowValue)});
                res = fld ? fld.show : this.rowValue;
            }
            else if (this.tableHeader.field === 'var_object_id') {
                if (this.tableRow.variable_type === 'field') {
                    let field = _.find(this.nameFields(), {val: Number(this.rowValue)});
                    res = field ? field.show || field.html : this.rowValue;
                }
                if (this.tableRow.variable_type === 'bi') {
                    let field = _.find(this.nameCharts(), {val: Number(this.rowValue)});
                    res = field ? field.show || field.html : this.rowValue;
                }
            }
            else if (this.tableHeader.field === 'additional_attributes') {
                res = ReportVariable.attrsPreview( this.tableRow );
            }
            else {
                res = this.rowValue;
            }
            return this.$root.strip_danger_tags(res);
        },
        pasteAttachment(e) {
            let files = this.$root.getDocxFromClipboard(e);
            if (files.length) {
                _.each(files, (file) => {
                    if (!FileHelper.checkFile(file, this.tableHeader.f_format)) {
                        return false;
                    }

                    this.$root.sm_msg_type = 1;
                    Endpoints.fileUpload({
                        table_id: this.tableMeta.id,
                        table_field_id: this.tableHeader.id,
                        row_id: Number(this.tableRow.id) || this.tableRow._temp_id,
                        file: file,
                        clear_before: 1,
                    }).then(({ data }) => {
                        this.$root.attachFileToRow(this.tableRow, this.tableHeader, data);
                        if (data.filepath && data.filename) {
                            this.sendUpdateSignal(data.filepath + data.filename);
                        }
                    }).catch(errors => {
                        Swal('Info', getErrors(errors));
                    }).finally(() => {
                        this.$root.sm_msg_type = 0;
                    });
                });
            } else {
                Swal('Info', 'Doc files not found in the Clipboard.');
            }
            this.hideEdit();
        },
        callMakeReports() {
            this.$emit('show-add-ddl-option', this.tableRow);
        },
        showGroupsPopup(type, id) {
            eventBus.$emit('show-grouping-settings-popup', this.globalMeta.db_name, type, id);
        },

        //select arrays
        getRelatedMeta() {
            let meta = this.globalMeta;
            if (this.behavior === 'settings_report_specific' && this.parentRow && this.parentRow.ref_link_id) {
                let ref_cond_id = null;
                _.each(this.globalMeta._fields, (fld) => {
                    let lnk = _.find(fld._links, {id: Number(this.parentRow.ref_link_id)});
                    if (lnk) {
                        ref_cond_id = lnk.table_ref_condition_id;
                    }
                });
                let refCond = _.find(this.globalMeta._ref_conditions, {id: Number(ref_cond_id)});
                meta = refCond ? refCond._ref_table : meta;
            }
            return meta;
        },
        nameFields() {
            let fltr = '';
            if (this.tableHeader.field === 'report_field_id') {
                fltr = 'Attachment';
            }

            let fields = _.filter(this.getRelatedMeta()._fields, (hdr) => {
                return (!fltr || hdr.f_type === fltr) && this.$root.systemFieldsNoId.indexOf(hdr.field) === -1;
            });
            return _.map(fields, (hdr) => {
                return { val: hdr.id, show: this.$root.uniqName(hdr.name), }
            });
        },
        refLinks() {
            let res = [];
            _.each(this.globalMeta._fields, (fld) => {
                _.each(fld._links, (link,idx) => {
                    if (link.link_type === 'Record') {
                        res.push({val: link.id, show: fld.name + '/#' + (idx + 1),});
                    }
                });
            });
            res.unshift({ val:null, html:'<span class="blue">Self</span>', });
            return res;
        },
        availAlignments() {
            return [
                { val: 'start', show:'Left' },
                { val: 'center', show:'Center' },
                { val: 'end', show:'Right' },
            ];
        },
        nameCharts() {
            return _.map(this.getRelatedMeta()._bi_charts, (ch) => {
                return { val: ch.id, show: ch.name || ch.title || 'Chart_'+ch.id, }
            });
        },
        userClouds() {
            return _.map(this.$root.settingsMeta.user_clouds_data, (cg) => {
                return { val: cg.id, show: cg.name, }
            });
        },
        globalAIs() {
            return _.map(this.$root.user._ai_api_keys, (tw, k) => {
                return { val: tw.id, show: tw.name, }
            });
        },
        availTemplates() {
            return _.map(this.globalMeta._report_templates, (tmplt) => {
                return { val: tmplt.id, show: tmplt.name, }
            });
        },
        availColumns() {
            return _.map(this.globalMeta._column_groups, (col) => {
                return { val: col.id, show: col.name, }
            });
        },
        availRowSumFormulas() {
            return _.map(this.$root.availRowSumFormulas, (frm) => {
                return { val: frm, show: String(frm).toUpperCase(), }
            });
        },
        availStatFields() {
            let colGroup = _.find(this.globalMeta._column_groups, {id: Number(this.parentRow.rg_colgroup_id)}) || {};
            return _.map(colGroup._fields || [], (fld) => {
                return { val: fld.id, show: fld.name, }
            });
        },
        templateVariables() {
            let report = _.find(this.globalMeta._reports, {id: Number(this.parentRow.table_report_id)});
            let presentVars = [];
            _.each(report._sources, (src) => {
                _.each(src._variables, (vr) => {
                    presentVars.push({val:vr.variable});
                });
            });
            let allVariables = this.$root.reportTemplates[report.report_template_id] || [];
            return _.differenceBy(allVariables, presentVars, 'val');
        },
        showAttrPopup() {
            if (!this.isAddRow) {
                this.$emit('show-add-ddl-option', this.tableHeader, this.tableRow);
            }
        },
        //HANDLERS
        templateStringUpdateHandler(uniq_id, val) {
            if (uniq_id === this.uuid) {
                this.rowValue = val;
                this.updateValue();
            }
        },
    },
    mounted() {
        eventBus.$on('table-data-string-popup__update', this.templateStringUpdateHandler);
    },
    beforeDestroy() {
        eventBus.$off('table-data-string-popup__update', this.templateStringUpdateHandler);
    }
}
</script>

<style lang="scss" scoped>
    @import "CustomCell.scss";
</style>