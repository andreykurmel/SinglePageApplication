<template>
    <td :style="getCustomCellStyle"
        class="td-custom"
        ref="cell_tb_data"
        @click.stop=""
        @mouseenter="show_expand = $root.inArray(tableHeader.f_type, ['String', 'Text', 'Long Text', 'Auto String'])"
        @mouseleave="show_expand = false"
    >
        <single-td-field
            v-if="tableHeader.field === 'f_default' && tableRow.f_type !== 'Attachment'"
            :table-meta="globalMeta"
            :table-header="tableRow"
            :td-value="tableRow.f_default"
            :with_edit="true"
            :force_edit="true"
            :style="stfStyle"
            @updated-td-val="updateSingle"
        ></single-td-field>

        <div v-else
             class="td-wrapper"
             @mousedown.prevent="showEdit()"
             @mouseup.prevent="endSquareSelect()"
             :style="getTdWrappStyle()"
        >

            <div class="wrapper-inner" :style="getWrapperStyle()">
                <div class="inner-content" :style="{textAlign: tableHeader.col_align}">

                    <div v-if="tableHeader.f_type === 'Boolean' && tableHeader.field === 'header_unit_ddl'">
                        <label class="switch_t" :style="{height: Math.min(maxCellHGT, 17)+'px'}">
                            <input type="checkbox"
                                   v-model="editValue"
                                   @click="header_unit_ddl_click"
                                   @change="disabledCheckBox ? '' : updateValue()">
                            <span class="toggler round" :class="[disabledCheckBox ? 'disabled' : '']"></span>
                        </label>
                        <info-popup v-if="radio_help"
                                    :title_html="'Info'"
                                    :content_html="'User needs to “Activate Unit Conversion” in Settings/Basics/General prior to turning on “DDL in Header” for unit conversion'"
                                    :extra_style="{top:'calc(50% - 75px)'}"
                                    @hide="radio_help = false"
                        ></info-popup>
                    </div>

                    <div v-else-if="tableHeader.f_type === 'Boolean'">
                        <label class="switch_t" :style="{height: Math.min(maxCellHGT, 17)+'px'}">
                            <input type="checkbox" :disabled="disabledCheckBox" v-model="editValue" @change="updateValue()">
                            <span class="toggler round" :class="[disabledCheckBox ? 'disabled' : '']"></span>
                        </label>
                    </div>

                    <input
                            v-else-if="tableHeader.f_type === 'Radio' && !isAddRow"
                            :checked="editValue"
                            :disabled="disabledCheckBox"
                            @click="updateRadio()"
                            type="radio"
                            ref="inline_input"
                            class="checkbox-input"/>

                    <a v-else-if="tableHeader.field === 'mirror_rc_id'"
                       title="Open ref condition in popup."
                       @mousedown.stop="showRefSettingsPopup(tableRow.mirror_rc_id)"
                    >{{ showField() }}</a>

                    <a v-else-if="tableHeader.field === 'ddl_id' || tableHeader.field === 'unit_ddl_id'"
                       title="Open DDL settings in popup."
                       @mousedown.stop="showDdlSettingsPopup()"
                    >
                        <i v-if="isSharedDDL()" class="fa fa-share-alt-square" style="color: #444;"></i>
                        {{ showField() }}
                    </a>

                    <a v-else-if="inArray(tableHeader.field, ['twilio_google_acc_id','twilio_sendgrid_acc_id','twilio_sms_acc_id','twilio_voice_acc_id'])"
                       title="Open settings in popup."
                       @mousedown.stop="showUserSettings()"
                    >{{ showField() }}</a>

                    <div v-else-if="tableHeader.field === 'validation_rules'"
                         style="cursor: pointer"
                         @click="$emit('show-add-ddl-option', tableHeader, tableRow)"
                    >{{ showField() }}&nbsp;</div>

                    <div v-else="" :title="getTitle" ref="sett_content_elem">{{ showField() }}</div>

                </div>
            </div>

            <cell-table-data-expand
                    v-if="show_expand"
                    style="background-color: #FFF;"
                    :table-meta="globalMeta"
                    :table-row="tableRow"
                    :table-header="tableHeader"
                    :uniqid="getuniqid()"
                    :can-edit="canCellEdit"
                    :user="user"
            ></cell-table-data-expand>

        </div>



        <!-- ABSOLUTE EDITINGS -->
        <div v-if="tableHeader.f_type === 'Color'" class="cell-editing">
            <tablda-colopicker
                    :init_color="editValue"
                    :avail_null="true"
                    @set-color="updateColor"
            ></tablda-colopicker>
        </div>

        <div v-else-if="tableHeader.field === 'col_align'" class="cell-editing">
            <align-of-column
                    :table-row="tableRow"
                    :table-meta="tableMeta"
                    @set-align="updateAlign"
            ></align-of-column>
        </div>

        <div v-else-if="isEditing()" class="cell-editing">

            <formula-helper
                    v-if="tableHeader.input_type === 'Formula'"
                    :user="user"
                    :table-meta="globalMeta"
                    :table-row="tableRow"
                    :table-header="tableRow"
                    :header-key="'f_formula'"
                    :can-edit="canCellEdit"
                    :pop_width="'100%'"
                    @close-formula="hideEdit"
                    @set-formula="updateRow(true)"
            ></formula-helper>

            <tablda-select-simple
                    v-else-if="tableHeader.field === 'f_default' && tableRow.f_type === 'Attachment'"
                    :options="attachmentDefaultMethods()"
                    :table-row="tableRow"
                    :hdr_field="'f_default'"
                    :fixed_pos="true"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
            ></tablda-select-simple>

            <tablda-select-simple
                    v-else-if="tableHeader.field === 'width_of_table_popup'"
                    :options="tableWidths()"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :fixed_pos="true"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
            ></tablda-select-simple>

            <tablda-select-simple
                    v-else-if="tableHeader.field === 'name' && isAddRow"
                    :options="nameFields()"
                    :table-row="tableRow"
                    :hdr_field="'active_links'"
                    :fixed_pos="true"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
            ></tablda-select-simple>

            <tablda-select-simple
                v-else-if="tableHeader.field === 'fetch_source_id' || tableHeader.field === 'fetch_by_row_cloud_id'"
                :options="metaFields(tableHeader.field === 'fetch_source_id' ? 'String' : 'Connected Clouds')"
                :table-row="tableRow"
                :hdr_field="tableHeader.field"
                :can_empty="true"
                :fixed_pos="true"
                :style="getEditStyle"
                @selected-item="updateCheckedDDL"
                @hide-select="hideEdit"
            ></tablda-select-simple>

            <tablda-select-simple
                v-else-if="tableHeader.field === 'fetch_one_cloud_id'"
                :options="availClouds()"
                :table-row="tableRow"
                :hdr_field="tableHeader.field"
                :can_empty="true"
                :fixed_pos="true"
                :style="getEditStyle"
                @selected-item="updateCheckedDDL"
                @hide-select="hideEdit"
            ></tablda-select-simple>

            <tablda-select-simple
                    v-else-if="inArray(tableHeader.field, ['twilio_google_acc_id','twilio_sendgrid_acc_id','twilio_sms_acc_id','twilio_voice_acc_id'])"
                    :options="globalTwilios()"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :can_empty="true"
                    :fixed_pos="true"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
            ></tablda-select-simple>

            <tablda-select-simple
                    v-else-if="tableHeader.field === 'mirror_rc_id'"
                    :options="globalMetaRefConds()"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :can_empty="true"
                    :fixed_pos="true"
                    :embed_func_txt="'Add New'"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
                    @embed-func="showRefSettingsPopup(tableRow.mirror_rc_id)"
            ></tablda-select-simple>

            <tablda-select-simple
                    v-else-if="tableHeader.field === 'mirror_field_id'"
                    :options="globalMetaRefCondFields()"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :can_empty="true"
                    :fixed_pos="true"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
            ></tablda-select-simple>

            <tablda-select-simple
                    v-else-if="tableHeader.field === 'mirror_part'"
                    :options="[
                        {val: 'id', show: 'ID'},
                        {val: 'value', show: 'Value'},
                        {val: 'show', show: 'Show'},
                    ]"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :fixed_pos="true"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
            ></tablda-select-simple>

            <tablda-select-simple
                    v-else-if="tableHeader.field === 'fld_display_header_type'"
                    :options="[
                        {val: 'default', show: 'Default'},
                        {val: 'symbol', show: 'Symbol'},
                        {val: 'tooltip', show: 'Tooltip'},
                        {val: 'placeholder', show: 'Placeholder'},
                    ]"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :fixed_pos="true"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
            ></tablda-select-simple>

            <tablda-select-simple
                    v-else-if="tableHeader.field === 'image_fitting'"
                    :options="[
                        {val: 'fill', show: 'Fill'},
                        {val: 'width', show: 'Width'},
                        {val: 'height', show: 'Height'},
                    ]"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :fixed_pos="true"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
            ></tablda-select-simple>

            <tablda-select-simple
                v-else-if="tableHeader.field === 'section_font'"
                :options="[
                    {val: 'Normal', show: 'Normal'},
                    {val: 'Italic', show: 'Italic'},
                    {val: 'Bold', show: 'Bold'},
                    {val: 'Strikethrough', show: 'Strikethrough'},
                    {val: 'Overline', show: 'Overline'},
                    {val: 'Underline', show: 'Underline'},
                ]"
                :table-row="tableRow"
                :hdr_field="tableHeader.field"
                :fld_input_type="tableHeader.input_type"
                :fixed_pos="true"
                :style="getEditStyle"
                @selected-item="updateCheckedDDL"
                @hide-select="hideEdit"
            ></tablda-select-simple>

            <tablda-select-simple
                v-else-if="tableHeader.field === 'section_align_h'"
                :options="[
                    {val: 'Left', show: 'Left'},
                    {val: 'Center', show: 'Center'},
                    {val: 'Right', show: 'Right'},
                ]"
                :table-row="tableRow"
                :hdr_field="tableHeader.field"
                :fld_input_type="tableHeader.input_type"
                :fixed_pos="true"
                :style="getEditStyle"
                @selected-item="updateCheckedDDL"
                @hide-select="hideEdit"
            ></tablda-select-simple>

            <tablda-select-simple
                v-else-if="tableHeader.field === 'section_align_v'"
                :options="[
                    {val: 'Top', show: 'Top'},
                    {val: 'Middle', show: 'Middle'},
                    {val: 'Bottom', show: 'Bottom'},
                ]"
                :table-row="tableRow"
                :hdr_field="tableHeader.field"
                :fld_input_type="tableHeader.input_type"
                :fixed_pos="true"
                :style="getEditStyle"
                @selected-item="updateCheckedDDL"
                @hide-select="hideEdit"
            ></tablda-select-simple>

            <tablda-select-simple
                    v-else-if="tableHeader.field === 'unit_ddl_id'"
                    :options="globalMetaDdls(true)"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :can_empty="true"
                    :fixed_pos="true"
                    :embed_func_txt="'Add New'"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
                    @embed-func="showDdlSettingsPopup()"
            ></tablda-select-simple>

            <tablda-select-simple
                    v-else-if="tableHeader.field === 'ddl_id' && inArray(rowInputType, $root.ddlInputTypes)"
                    :options="globalMetaDdls(true)"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :can_empty="true"
                    :embed_func_txt="'Add New'"
                    :fixed_pos="true"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
                    @embed-func="showDdlSettingsPopup()"
            ></tablda-select-simple>

            <tablda-select-simple
                    v-else-if="tableHeader.field === 'default_stats'"
                    :fld_input_type="tableHeader.input_type"
                    :options="availRowSumFormulas()"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :can_empty="true"
                    :fixed_pos="true"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
            ></tablda-select-simple>

            <tablda-select-simple
                    v-else-if="inArray(tableHeader.field, ['input_type','mirror_edit_component'])"
                    :options="[
                        {show: 'Auto', val: 'Auto'},
                        {show: 'Input', val: 'Input'},
                        {show: 'S-Select', val: 'S-Select'},
                        {show: 'S-Search', val: 'S-Search'},
                        {show: 'S-SS', val: 'S-SS'},
                        {show: 'M-Select', val: 'M-Select'},
                        {show: 'M-Search', val: 'M-Search'},
                        {show: 'M-SS', val: 'M-SS'},
                        {show: 'Formula', val: 'Formula', disabled: tableRow.input_type === 'Mirror' && tableHeader.field === 'mirror_edit_component'},
                        {show: 'Mirror', val: 'Mirror', disabled: tableRow.input_type === 'Mirror' && tableHeader.field === 'mirror_edit_component'},
                        {show: 'Fetch', val: 'Fetch', disabled: tableRow.f_type !== 'Attachment'},
                    ]"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :fixed_pos="true"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
            ></tablda-select-simple>

            <tablda-select-simple
                    v-else-if="tableHeader.field === 'verttb_cell_height'"
                    :options="[
                        {show: 'Small', val: 1},
                        {show: 'Medium', val: 2},
                        {show: 'Large', val: 3},
                        {show: 'Ex-Large', val: 5},
                    ]"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :fixed_pos="true"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
            ></tablda-select-simple>

            <tablda-select-simple
                    v-else-if="tableHeader.field === 'filter_type'
                            && tableRow.filter
                            && inArray(tableRow.f_type, availRangeTypes)"
                    :options="[
                        {val: 'value', show: 'Value'},
                        {val: 'range', show: 'Range'},
                    ]"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :fixed_pos="true"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
            ></tablda-select-simple>

            <tablda-select-simple
                    v-else-if="tableHeader.field === 'ddl_style'
                        && tableRow.ddl_id
                        && inArray(rowInputType, $root.ddlInputTypes)"
                    :options="[
                        {val: 'ddl', show: 'DDL'},
                        {val: 'panel', show: 'Panel'},
                    ]"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :fixed_pos="true"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
            ></tablda-select-simple>

            <tablda-select-ddl
                    v-else-if="tableHeader.field === 'unit_display' || (tableHeader.field === 'unit' && tableRow.unit_ddl_id)"
                    :ddl_id="tableRow.unit_ddl_id"
                    :can_empty="true"
                    :table-row="tableRow"
                    :table_id="globalMeta.id"
                    :hdr_field="tableHeader.field"
                    :fixed_pos="true"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
            ></tablda-select-ddl>

            <input
                    v-else-if="inArray(tableHeader.field, ownerInputs) && !inArray(tableHeader.field, fieldsWithSpecialRules)"
                    v-model="editValue"
                    @blur="updateRow()"
                    ref="inline_input"
                    class="form-control full-height"
                    :type="tableHeader.f_type === 'Integer' ? 'number' : 'text'"
                    :style="getEditStyle">

            <input
                    v-else-if="inArray(tableHeader.field, fieldsForUser) && !inArray(tableHeader.field, fieldsWithSpecialRules)"
                    v-model="editValue"
                    @blur="updateRow()"
                    ref="inline_input"
                    class="form-control full-height"
                    :type="tableHeader.f_type === 'Integer' ? 'number' : 'text'"
                    :style="getEditStyle">

            <div v-else="">{{ hideEdit() }}</div>

        </div>
        <!-- ABSOLUTE EDITINGS -->

    </td>
</template>

<script>
import {SelectedCells} from '../../classes/SelectedCells';
import {SpecialFuncs} from "../../classes/SpecialFuncs";
import {CellSettingsDisplayHelper} from "./CellSettingsDisplayHelper";
import {Validator} from "../../classes/Validator";
import {VerticalTableFldObject} from "../CustomTable/VerticalTableFldObject";

import {eventBus} from '../../app';

import Select2DDLMixin from './../_Mixins/Select2DDLMixin.vue';
import CellStyleMixin from '../_Mixins/CellStyleMixin.vue';
import CellMoveKeyHandlerMixin from './../_Mixins/CellMoveKeyHandlerMixin.vue';
import ExpandIconMixin from './../_Mixins/ExpandIconMixin.vue';

import TabldaColopicker from "./InCell/TabldaColopicker.vue";
import AlignOfColumn from "./InCell/AlignOfColumn.vue";
import TabldaSelectSimple from "./Selects/TabldaSelectSimple.vue";
import TabldaSelectDdl from "./Selects/TabldaSelectDdl.vue";
import HoverBlock from "../CommonBlocks/HoverBlock.vue";
import InfoPopup from "../CustomPopup/InfoPopup.vue";
import CellTableDataExpand from "./InCell/CellTableDataExpand";

export default {
    name: "CustomCellSettingsDisplay",
    components: {
        CellTableDataExpand,
        InfoPopup,
        HoverBlock,
        TabldaSelectDdl,
        TabldaSelectSimple,
        AlignOfColumn,
        TabldaColopicker,
    },
    mixins: [
        Select2DDLMixin,
        CellStyleMixin,
        CellMoveKeyHandlerMixin,
        ExpandIconMixin,
    ],
    data: function () {
        return {
            show_expand: false,
            oldVal: null,
            editValue: null,
            radio_top: 0,
            radio_left: 0,
            radio_help: false,
            editing: false,
            ownerInputs: [
                'formula_symbol','tooltip','verttb_he_auto','verttb_cell_height','verttb_row_height','pop_tab_order',
                'notes','placeholder_content','kanban_field_name','kanban_field_description','form_row_spacing',
                'twilio_sender_name','twilio_sender_email','copy_prefix_value','copy_suffix_value','pop_tab_name',
                'section_header','section_font','section_size','section_align_h','section_align_v','section_height',
            ],
            fieldsWithSpecialRules: [
                'filter_type',
            ],
            fieldsForUser: this.$root.availableNotOwnerDisplayColumns,
            selectParam: {
                tags: true,
                maximumInputLength: 5
            },
            availRangeTypes: ['String','Integer','Decimal','Currency','Percentage','Date','Date Time'],
            table_header_field: (this.tableHeader.field === 'name' ? 'id' : this.tableHeader.field),
            no_key_handler: false,
        }
    },
    props:{
        globalMeta: {
            type: Object,
            default: function () {
                return {};
            }
        },
        cellValue: String|Number,
        selectedCell: SelectedCells,
        tableMeta: Object,
        tableHeader: Object,
        tableRow: Object,
        allRows: Object|null,
        rowIndex: Number,
        cellHeight: Number,
        maxCellRows: Number,
        user: Object,
        isAddRow: Boolean,
        isVertTable: Boolean,
        isSelectedExt: Boolean,
        behavior: String,
        is_visible: Boolean,
        parentRow: Object,
    },
    watch: {
        cellValue: {
            handler(val) {
                this.initEditFill();
            },
            immediate: true,
        },
    },
    computed: {
        rowInputType() {
            return this.tableRow.input_type === 'Mirror' && this.tableRow.mirror_rc_id
                ? this.tableRow.mirror_edit_component
                : this.tableRow.input_type;
        },
        stfStyle() {
            return {
                width: '100%',
                backgroundColor: this.$root.systemFields.indexOf(this.tableRow.field) > -1 ? '#EEE' : null,
            };
        },
        isSelected() {
            return this.isSelectedExt;
        },
        getCustomCellStyle() {
            let obj = this.getCellStyle();
            obj.textAlign = (this.inArray(this.tableHeader.f_type, ['Boolean', 'Radio']) ? 'center' : '');
            obj.backgroundColor = this.isSelected ? '#CFC' : 'inherit';

            if (this.tableHeader.field === 'f_default' && this.tableRow.f_type === 'Attachment') {
                return obj;
            }

            let has_unit = this.tableRow.unit && this.tableRow.unit_ddl_id && this.globalMeta.unit_conv_is_active;// && this.globalMeta.__unit_convers && this.globalMeta.__unit_convers.length;
            if (
                //In Settings/Links
                this.tableRow.link_type === 'Web' && this.inArray(this.tableHeader.field, ['table_ref_condition_id', 'listing_field_id'])
                ||
                this.tableRow.link_type === 'Table' && this.inArray(this.tableHeader.field, ['listing_field_id'])
                ||
                //In Settings/Basics
                (!this.tableRow.ddl_id || !this.inArray(this.rowInputType, this.$root.ddlInputTypes)) && this.inArray(this.tableHeader.field, ['ddl_style'])
                ||
                !this.inArray(this.rowInputType, this.$root.ddlInputTypes) && this.inArray(this.tableHeader.field, ['ddl_id'])
                ||
                !this.tableRow.unit_ddl_id && this.inArray(this.tableHeader.field, ['unit'])
//                        ||
//                        (this.tableRow.f_type === 'User' && this.inArray(this.tableHeader.field, ['ddl_id','ddl_add_option','ddl_auto_fill']))
                ||
                (this.inArray(this.tableHeader.field, ['header_unit_ddl','unit_display']) && !has_unit)
                ||
                (this.tableHeader.field === 'is_default_show_in_popup' && !this.$root.checkAvailable(this.$root.user, 'form_visibility'))
                ||
                (this.tableHeader.field === 'f_formula' && this.rowInputType !== 'Formula')
                ||
                (this.tableHeader.field === 'verttb_row_height' && this.tableRow.verttb_he_auto)
                ||
                (this.tableHeader.field === 'pop_tab_order' && this.tableRow._poptaborder_disabled)
                ||
                (
                    this.tableHeader.field === 'filter_type'
                    &&
                    (!this.inArray(this.tableRow.f_type, this.availRangeTypes) || !this.tableRow.filter)
                )
                ||
                this.disabledCheckBox
            ) {
                obj.backgroundColor = '#EEE';
            }

            if (this.tableHeader.field === 'input_type' && this.$root.systemFields.indexOf(this.tableRow.field) !== -1) {
                obj.backgroundColor = '#EEE';
            }
            return obj;
        },
        disabledCheckBox() {
            return CellSettingsDisplayHelper.disabledCheckBox(this.globalMeta, this.tableHeader, this.tableRow, this.behavior);
        },
        getTitle() {
            let title = '';
            switch (this.tableHeader.field) {
                case 'filter_type': title = 'Available column types: '+this.availRangeTypes.join(',');
                    break;
                case 'ddl_style': title = 'Not recommended for long DDL';
                    break;
            }
            return title;
        },
        canCellEdit() {
            return (this.tableHeader.field === 'f_default' && this.tableRow.f_type === 'Attachment')
                || CellSettingsDisplayHelper.canCellEdit(this.globalMeta, this.tableHeader, this.tableRow, true, this.behavior);
        },
    },
    methods: {
        header_unit_ddl_click(e) {
            if (this.disabledCheckBox) {
                this.show_radio_help(e);
                e.preventDefault();
            }
        },
        show_radio_help(e) {
            if (!this.globalMeta.unit_conv_is_active && this.tableHeader.field === 'header_unit_ddl') {
                this.radio_help = true;
                this.radio_left = e.clientX;
                this.radio_top = e.clientY;
            }
        },
        inArray(item, array) {
            return $.inArray(item, array) > -1;
        },
        isEditing() {
            return this.editing && this.canCellEdit && !this.$root.global_no_edit;
        },
        showEdit() {
            if (window.event.button != 0 && this.selectedCell) {
                this.selectedCell.single_select(this.tableHeader, this.rowIndex);
                return;
            }
            let cmdOrCtrl = window.event.metaKey || window.event.ctrlKey;
            if (cmdOrCtrl && this.selectedCell) {
                this.selectedCell.square_select(this.tableHeader, this.rowIndex);
                return;
            }
            //edit cell
            if (
                window.innerWidth >= 768
                && this.selectedCell
                && !this.selectedCell.is_selected(this.globalMeta, this.tableHeader, this.rowIndex)
            ) {
                this.selectedCell.single_select(this.tableHeader, this.rowIndex);
            } else {
                if (!this.canCellEdit || this.inArray(this.tableHeader.f_type, ['Boolean','Color'])) {
                    return;
                }
                this.editing = true;
                if (this.isEditing()) {
                    this.$nextTick(function () {
                        if (this.$refs.inline_input) {
                            if (this.$refs.inline_input && this.$refs.inline_input.nodeName === 'SELECT') {
                                this.showHideDDLs(this.$root.selectParam);
                                this.ddl_cached = false;
                            } else {
                                this.$refs.inline_input.focus();
                            }
                        }
                    });
                } else {
                    this.no_key_handler = true;
                    this.editing = false;
                }
            }
        },
        endSquareSelect() {
            if (this.selectedCell && !this.selectedCell.is_selected(this.tableMeta, this.tableHeader, this.rowIndex)) {
                this.selectedCell.square_select(this.tableHeader, this.rowIndex);
            }
        },
        hideEdit() {
            this.editing = false;
        },
        updateCheckedDDL(item) {
            if (this.tableHeader.field === 'name') {
                this.tableRow.id = item;
            } else if (this.$root.isMSEL(this.tableHeader.input_type)) {
                this.editValue = Array.isArray(this.editValue) ? this.editValue : [String(this.editValue)];
                if (this.editValue.indexOf(item) > -1) {
                    this.editValue.splice( this.editValue.indexOf(item), 1 );
                } else {
                    this.editValue.push(item);
                }
            } else {
                this.editValue = item;
            }
            this.updateValue();
        },
        updateSingle(val, header, ddl_option) {
            this.editValue = val;
            if (ddl_option) {
                this.editValue = ddl_option.show;
            }
            this.updateValue();
        },
        updateValue(force) {
            let row = this.getCurrentRow();
            let editVal = SpecialFuncs.applySetMutator(this.tableHeader, this.editValue);
            if (row[this.tableHeader.field] !== editVal || force) {
                row[this.tableHeader.field] = editVal;
                row._changed_field = this.tableHeader.field;

                if (this.tableHeader.field === 'name' && !row.name) {
                    let fld = _.find(this.globalMeta._fields, {id: Number(row.id)});
                    row.name = fld ? fld.name : row.id;
                }
                if (this.tableHeader.field === 'unit_ddl_id') {
                    row.unit = null;
                    row.unit_display = null;
                }

                //3rd party Twilio
                if (this.tableHeader.field === 'twilio_google_acc_id') {
                    row.twilio_sendgrid_acc_id = null;
                }
                if (this.tableHeader.field === 'twilio_sendgrid_acc_id') {
                    row.twilio_google_acc_id = null;
                }

                if (this.tableHeader.f_type === 'Boolean') {
                    row[this.tableHeader.field] = editVal ? 1 : 0;
                    if (this.tableHeader.field === 'filter' && !row.filter) {
                        row.filter_search = 0;
                    }
                }
                if (this.tableHeader.field === 'verttb_row_height') {
                    row.verttb_row_height = Number(row.verttb_row_height);
                    row.verttb_row_height = Math.max(1, row.verttb_row_height);
                    row.verttb_row_height = Math.min(20, row.verttb_row_height);//max_he_for_auto_rows: 20
                }
                if (this.tableHeader.field === 'fetch_one_cloud_id') {
                    row.fetch_by_row_cloud_id = null;
                }
                if (this.tableHeader.field === 'fetch_by_row_cloud_id') {
                    row.fetch_one_cloud_id = null;
                }

                this.$emit('updated-cell', row, this.tableHeader);
                this.$nextTick(() => {
                    this.changedContSize();
                });
            }
        },
        updateAlign(val) {
            this.editValue = val;
            this.updateValue();
        },
        updateRow(fromFormula) {
            if (this.tableHeader.field == 'pop_tab_order') {
                _.each(this.globalMeta._fields, (fld) => {
                    if (fld.id != this.tableRow.id && fld.pop_tab_name == this.tableRow.pop_tab_name && !fld.pop_tab_order) {
                        fld._poptaborder_disabled = !! this.editValue;
                    }
                });
            }
            if (fromFormula) {
                this.initEditFill();
            }
            this.hideEdit();
            this.updateValue(fromFormula);
        },
        updateRadio() {
            this.editValue = this.editValue ? 0 : 1;
            this.updateValue();
        },
        updateColor(clr, save) {
            if (save) {
                this.$root.saveColorToPalette(clr);
            }
            this.editValue = clr;
            this.hideEdit();
            this.updateValue();
        },
        showField() {
            let res = '';
            if (this.tableHeader.f_type === 'User') {
                res = this.$root.smallUserStr(this.tableRow, this.tableHeader, this.editValue);
            }
            else
            if (this.tableHeader.field === 'name') {
                res = this.$root.uniqName( this.editValue );
            }
            else
            if (this.tableHeader.field === 'f_default' && this.tableRow.f_type === 'Attachment') {
                let meth = _.find(this.attachmentDefaultMethods(), {val: this.tableRow.f_default});
                res = meth ? meth.show : this.tableRow.f_default;
            }
            else
            if (this.tableHeader.field === 'validation_rules') {
                res = Validator.rulesPreview( this.tableRow );
            }
            else
            if (this.tableHeader.field === 'filter_type') {
                switch (this.editValue) {
                    case 'value': res = 'Values'; break;
                    case 'range': res = 'Range'; break;
                }
            }
            else
            if (this.tableHeader.field === 'ddl_style') {
                switch (this.editValue) {
                    case 'ddl': res = 'DDL'; break;
                    case 'panel': res = 'Panel'; break;
                }
            }
            else
            if (this.tableHeader.field === 'verttb_cell_height') {
                switch (this.editValue) {
                    case 1: res = 'Small'; break;
                    case 2: res = 'Medium'; break;
                    case 3: res = 'Large'; break;
                    case 5: res = 'Ex-Large'; break;
                }
            }
            else
            if (this.tableHeader.field === 'verttb_row_height') {
                res = this.tableRow.verttb_he_auto ? '' : this.tableRow.verttb_row_height;
            }
            else
            if (this.tableHeader.field === 'default_stats') {
                res = this.editValue
                    ? String(this.editValue).toUpperCase()
                    : '';
            }
            else
            if (this.tableHeader.field === 'unit_ddl_id' && this.tableRow.unit_ddl_id) {
                let ddl = _.find(this.globalMetaDdls(true), {val: Number(this.tableRow.unit_ddl_id)});
                res = ddl ? ddl.show : this.tableRow.unit_ddl_id;
            }
            else
            if (this.tableHeader.field === 'unit' || this.tableHeader.field === 'unit_display') {
                res = this.$root.rcShow(this.tableRow, this.tableHeader.field);
            }
            else
            if (this.tableHeader.field === 'ddl_id' && this.tableRow.ddl_id) {
                let ddl = _.find(this.globalMetaDdls(true), {val: Number(this.tableRow.ddl_id)});
                res = ddl ? ddl.show : this.tableRow.ddl_id;
            }
            else
            if (
                this.inArray(this.tableHeader.field,
                ['is_lat_field','is_long_field','is_addr_field','is_info_header_field','is_info_header_value','fetch_source_id','fetch_by_row_cloud_id'])
            ) {
                let field = _.find(this.globalMeta._fields, {id: Number(this.editValue)});
                res = field ? field.name : '';
            }
            else
            if (this.inArray(this.tableHeader.field, ['fetch_one_cloud_id'])) {
                let cloud = _.find(this.availClouds(), {val: Number(this.editValue)});
                res = cloud ? cloud.show : '';
            }
            else
            if (this.inArray(this.tableHeader.field, ['width_of_table_popup'])) {
                let cloud = _.find(this.tableWidths(), {val: this.editValue});
                res = cloud ? cloud.show : res;
            }
            else
            if (this.inArray(this.tableHeader.field, ['mirror_rc_id'])) {
                let rc = _.find(this.globalMeta._ref_conditions, {id: Number(this.tableRow.mirror_rc_id)});
                res = rc ? rc.name : this.tableRow.mirror_rc_id;
            }
            else
            if (this.inArray(this.tableHeader.field, ['twilio_google_acc_id','twilio_sendgrid_acc_id','twilio_sms_acc_id','twilio_voice_acc_id'])) {
                let tw = _.find(this.globalTwilios(), {val: Number(this.editValue)});
                res = tw ? tw.show : this.editValue;
            }
            else
            if (this.inArray(this.tableHeader.field, ['mirror_field_id'])) {
                let reffld = _.find(this.globalMetaRefCondFields(), {val: Number(this.tableRow.mirror_field_id)});
                res = reffld ? reffld.show : this.tableRow.mirror_field_id;
            }
            else
            if (this.inArray(this.tableHeader.field, ['mirror_part'])) {
                if (this.tableRow.mirror_rc_id) {
                    switch (this.tableRow.mirror_part) {
                        case 'id': res = 'ID'; break;
                        case 'value': res = 'Value'; break;
                        case 'show': res = 'Show'; break;
                    }
                } else {
                    res = '';
                }
            }
            else
            if (this.inArray(this.tableHeader.field, ['fld_display_header_type'])) {
                switch (VerticalTableFldObject.fieldSetting('fld_display_header_type', this.tableRow, null, this.behavior)) {
                    case 'symbol': res = 'Symbol'; break;
                    case 'tooltip': res = 'Tooltip'; break;
                    case 'placeholder': res = 'Placeholder'; break;
                    default: res = 'Default'; break;
                }
            }
            else
            if (this.tableHeader.field === 'image_fitting') {
                switch (this.tableRow.image_fitting) {
                    case 'fill': res = 'Fill'; break;
                    case 'width': res = 'Width'; break;
                    case 'height': res = 'Height'; break;
                }
            }
            else {
                res = this.editValue;
            }
            return this.$root.strip_danger_tags(res);
        },
        radioSettingsCheckedHandler(column, checkedFieldId) {
            if (
                this.tableRow.id !== checkedFieldId
                &&
                this.tableHeader.field === column
                &&
                this.editValue === 1
            ) {
                this.editValue = 0;
                this.updateValue();
            }
        },

        //select arrays
        attachmentDefaultMethods() {
            return [
                {val: 'file', show: 'Browse'},
                {val: 'link', show: 'Link'},
                {val: 'drag', show: 'Drag & Drop'},
                {val: 'photo', show: 'Camera (photo)'},
                {val: 'video', show: 'Camera (video)'},
                {val: 'audio', show: 'Microphone'},
                {val: 'paste', show: 'Paste'},
            ];
        },
        tableWidths() {
            return [
                { val:'full', show:'Full', },
                { val:'field', show:'Field', },
            ];
        },
        nameFields() {
            let fields = _.filter(this.globalMeta._fields, (hdr) => { return !hdr.active_links });
            return _.map(fields, (hdr) => {
                return { val: hdr.id, show: this.$root.uniqName(hdr.name), }
            });
        },
        metaFields(ftype) {
            let flds = _.filter(this.globalMeta._fields, {f_type: ftype || 'String'});
            return _.map(flds, (hdr) => {
                return { val: hdr.id, show: hdr.name, }
            });
        },
        isSharedDDL() {
            return _.find(this.$root.settingsMeta.shared_ddls || [], {id: Number(this.tableRow[this.tableHeader.field])});
        },
        globalMetaDdls(withShared) {
            let ddls = _.map(this.globalMeta._ddls, (hdr) => {
                return { val: hdr.id, show: hdr.name, }
            });
            if (withShared) {
                let shared = [];

                let tbids = _.orderBy(this.$root.settingsMeta.shared_ddls || [], 'admin_public');
                tbids = _.uniq( _.map(tbids, 'table_id') );

                let sharedgroups = _.groupBy(this.$root.settingsMeta.shared_ddls || [], 'table_id');
                _.each(tbids, (id) => {
                    let group = sharedgroups[id];
                    let firstDDL = _.first(group);
                    if (firstDDL) {
                        try {
                            let htmlVal = 'Fr <span class="fas fa-table"></span> ' + firstDDL._table.name;
                            htmlVal = firstDDL.admin_public
                                ? htmlVal + ' (public)'
                                : '<a target="_blank" href="' + firstDDL.__url + '">' + htmlVal + '</a>';
                            let url = new URL(firstDDL.__url) || {pathname: ''};
                            shared.push({
                                val: null,
                                html: htmlVal,
                                isTitle: true,
                                style: {'backgroundColor': '#dfd', 'cursor': 'not-allowed'},
                                hover: url.pathname.replace('/data/', '')
                            });

                            _.each(group, (ddl) => {
                                shared.push({val: ddl.id, show: ddl.name,});
                            });
                        } catch (e) {}
                    }
                });

                ddls = _.concat(ddls, shared);
            }
            return ddls;
        },
        globalMetaRefConds() {
            return _.map(this.globalMeta._ref_conditions, (rc) => {
                return { val: rc.id, show: rc.name, }
            });
        },
        globalMetaRefCondFields() {
            let rc = _.find(this.globalMeta._ref_conditions, {id: Number(this.tableRow.mirror_rc_id)});
            let fields = rc && rc._ref_table
                ? _.filter(rc._ref_table._fields, (fld) => { return !this.inArray(fld.field, this.$root.systemFields) })
                : [];
            return _.map(fields, (fld) => {
                    return { val: fld.id, show: fld.name, }
                });
        },
        availRowSumFormulas() {
            return _.map(this.$root.availRowSumFormulas, (frm) => {
                return { val: frm, show: String(frm).toUpperCase(), }
            });
        },
        availClouds() {
            return _.map(this.$root.settingsMeta.user_clouds_data, (cloud) => {
                return { val: cloud.id, show: cloud.name, }
            });
        },
        globalTwilios() {
            let userkey = '';
            switch (this.tableHeader.field) {
                case 'twilio_google_acc_id': userkey = '_google_email_accs'; break;
                case 'twilio_sendgrid_acc_id': userkey = '_sendgrid_api_keys'; break;
                default: userkey = '_twilio_api_keys';
            }
            return _.map(this.$root.user[userkey], (twilio, key) => {
                return { val: twilio.id, show: twilio.name || twilio.email || '#'+(key+1), }
            });
        },
        initEditFill() {
            let row = this.getCurrentRow();
            this.editValue = SpecialFuncs.getEditValue(this.tableHeader, row[this.tableHeader.field]);
        },
        getCurrentRow() {
            let row = this.tableRow;
            if (this.parentRow && this.parentRow._map_field_settings) {
                row = _.find(this.parentRow._map_field_settings, {table_field_id: Number(this.tableRow.id)}) || {
                    table_map_id: Number(this.parentRow.id),
                    table_field_id: Number(this.tableRow.id),
                };
            }
            return row;
        },

        //Emits
        showDdlSettingsPopup() {
            eventBus.$emit('show-ddl-settings-popup', this.globalMeta.db_name, this.tableRow[this.tableHeader.field]);
        },
        showRefSettingsPopup(rcid) {
            eventBus.$emit('show-ref-conditions-popup', this.globalMeta.db_name, rcid);
        },
        showUserSettings() {
            let key = this.tableHeader.field === 'twilio_google_acc_id' ? 'google' : 'twilio_tab';
            eventBus.$emit('open-resource-popup', 'connections', 0, key);
        },
    },
    mounted() {
        this.initEditFill();
        eventBus.$on('global-click', this.globalClick);
        eventBus.$on('global-keydown', this.globalKeydownHandler);
        eventBus.$on('table-data-string-popup__update', this.tableDataStringUpdateHandler);
    },
    beforeDestroy() {
        eventBus.$off('global-click', this.globalClick);
        eventBus.$off('global-keydown', this.globalKeydownHandler);
        eventBus.$off('table-data-string-popup__update', this.tableDataStringUpdateHandler);
    }
}
</script>

<style lang="scss" scoped>
    @import "CustomCell.scss";
</style>