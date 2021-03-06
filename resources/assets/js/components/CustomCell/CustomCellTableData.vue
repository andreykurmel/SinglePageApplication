<template>
    <td :style="getCellStyle"
        class="td-custom"
        :class="[isEditing() ? 'fix_ddl' : '']"
        ref="cell_tb_data"
        @click.stop=""
        @contextmenu="rightMenu"
    >
        <div class="td-wrapper"
             :style="getTdWrappStyle"
             @mouseenter="(e) => { $root.showHoverTooltip(e, tableHeader) }"
             @mouseleave="$root.leaveHoverTooltip"
             @mousedown.prevent="showEdit()"
             @mouseup.prevent="endSquareSelect()"
        >

            <div class="wrapper-inner" :class="td_wrap_special" :style="getWrapperStyle">
                <div class="inner-content" :class="td_wrap_special" :style="getInnerStyle">

                    <span v-if="hidden_by_format"></span>

                    <template v-else-if="tableHeader.f_type === 'Boolean' && tableHeader.input_type !== 'Formula'">
                        <template v-if="!tableHeader._links || !tableHeader._links.length" class="indeterm_check__wrap">
                            <label v-if="tableHeader.f_format === 'Slider'" class="switch_t" :style="{height: Math.min(maxCellHGT, 17)+'px'}">
                                <input type="checkbox"
                                       :disabled="!canCellEdit"
                                       v-model="tableRow[tableHeader.field]"
                                       @click="updateCheckBox()">
                                <span class="toggler round" :class="[!canCellEdit ? 'disabled' : '']"></span>
                            </label>
                            <span v-else="" class="indeterm_check__wrap">
                                <span class="indeterm_check checkbox-input"
                                      :class="{'disabled': !canCellEdit}"
                                      @click="updateCheckBox()"
                                      ref="inline_input"
                                      :style="$root.checkBoxStyle"
                                >
                                    <i v-if="!isNaN(editValue) ? parseFloat(editValue) : editValue" class="glyphicon glyphicon-ok group__icon"></i>
                                </span>
                            </span>
                        </template>
                        <i v-else="" class="glyphicon glyphicon-play" @click="updateCheckBox()"></i>
                    </template>

                    <stars-elems
                            v-else-if="tableHeader.f_type === 'Rating' && tableHeader.input_type !== 'Formula'"
                            :cur_val="parseFloat(editValue)"
                            :can_edit="canCellEdit"
                            :table_header="tableHeader"
                            @set-star="fromCompoSet"
                            @remove-icon="() => { $emit('remove-icon') }"
                            :style="{height: (maxCellHGT < 25 ? maxCellHGT : 25)+'px', lineHeight: (maxCellHGT-2 < 25 ? maxCellHGT-2 : 25)+'px'}"
                    ></stars-elems>

                    <progress-bar
                            v-else-if="tableHeader.f_type === 'Progress Bar' && tableHeader.input_type !== 'Formula'"
                            :pr_val="parseFloat(editValue)"
                            :can_edit="canCellEdit"
                            :is_selected="isSelected"
                            :table_header="tableHeader"
                            @set-val="fromCompoSet"
                            :style="{height: (maxCellHGT < lineHeight+2 ? maxCellHGT : lineHeight+2)+'px'}"
                    ></progress-bar>

                    <vote-element
                            v-else-if="tableHeader.f_type === 'Vote' && tableHeader.input_type !== 'Formula'"
                            :cell_val="editValue"
                            :can_edit="canCellEdit"
                            :table_header="tableHeader"
                            :user_info_settings="tableMeta._owner_settings"
                            :small_padding="maxCellRowCnt < 2"
                            :is_def_cell="!!tableRow._is_def_cell"
                            @set-val="fromCompoSet"
                    ></vote-element>

                    <tablda-colopicker
                            v-else-if="tableHeader.f_type === 'Color' && !tableHeader.ddl_id && tableHeader.input_type !== 'Formula'"
                            :init_color="tableRow[tableHeader.field]"
                            :can_edit="canCellEdit"
                            :avail_null="true"
                            :fixed_pos="true"
                            @set-color="updateColor"
                    ></tablda-colopicker>

                    <tablda-select-ddl
                            v-else-if="isVertTable
                                    && inArray(input_type, $root.ddlInputTypes)
                                    && tableHeader.ddl_id
                                    && tableHeader.ddl_style === 'panel'
                                    && tableHeader.input_type !== 'Formula'"
                            :ddl_id="tableHeader.ddl_id"
                            :ddl_type_panel="true"
                            :table-row="tableRow"
                            :table_id="tableMeta.id"
                            :hdr_field="tableHeader.field"
                            :fld_input_type="input_type"
                            :disabled="!canCellEdit"
                            :fixed_pos="true"
                            :style="getEditStyle"
                            @selected-item="updateCheckedDDL"
                            @hide-select="hideEdit"
                    ></tablda-select-ddl>

                    <show-attachments-block
                        v-else-if="tableHeader.f_type === 'Attachment'"
                        :image-fit="attachImageFit"
                        :show-type="attachmentsShowStyle"
                        :table-header="tableHeader"
                        :table-meta="tableMeta"
                        :table-row="tableRow"
                        :can-edit="canEditHdr(tableHeader)"
                        @update-signal="sendUpdateSignal"
                    ></show-attachments-block>

                    <cell-table-content
                            v-else=""
                            :global-meta="globalMeta"
                            :table-meta="tableMeta"
                            :table-row="tableRow"
                            :table-header="tableHeader"
                            :edit-value="editValue"
                            :max-cell-rows="$root.maxCellRows"
                            :font-size="themeTextFontSize"
                            :cur-width="tableHeader.width"
                            :user="user"
                            :behavior="behavior"
                            :is_def_fields="is_def_fields"
                            :is_td_single="is_td_single"
                            :no_height_limit="no_height_limit"
                            :can_edit="canCellEdit"
                            :is-vert-table="isVertTable"
                            @changed-cont-size="changedContSize"
                            @show-src-record="showSrcRecord"
                            @open-app-as-popup="openAppAsPopup"
                            @unselect-val="updateCheckedDDL"
                            @full-size-image="imgFromEmit"
                    ></cell-table-content>

                </div>
            </div>


            <cell-table-data-expand
                    v-if="cont_height > maxCellHGT+cell_top_padding"
                    style="background-color: #FFF;"
                    :cont_height="cont_height"
                    :cont_width="cont_width"
                    :table-meta="tableMeta"
                    :table-row="tableRow"
                    :table-header="tableHeader"
                    :html="cont_html"
                    :uniqid="getuniqid()"
                    :can-edit="canCellEdit"
                    :user="user"
            ></cell-table-data-expand>

        </div>



        <!-- ABSOLUTE EDITINGS -->
        <div v-if="isEditing()" class="cell-editing" :style="{backgroundColor: tableHeader.input_type === 'Formula' ? 'transparent' : ''}">

            <formula-helper
                    v-if="tableHeader.input_type === 'Formula'"
                    :user="user"
                    :table-meta="tableMeta"
                    :table-row="tableRow"
                    :table-header="tableHeader"
                    :header-key="is_def_fields ? tableHeader.field : tableHeader.field+'_formula'"
                    :can-edit="canCellEdit"
                    :uuid="uuid"
                    :fixed_pos="true"
                    :foreign_element="$refs.cell_tb_data"
                    :pop_width="'100%'"
                    :no_uniform="!!is_def_fields"
                    @set-formula="updateRow"
            ></formula-helper>

            <tablda-user-select
                    v-else-if="tableHeader.f_type === 'User'"
                    :edit_value="editValue"
                    :table_meta="tableMeta"
                    :can_empty="true"
                    :fixed_pos="true"
                    :multiselect="$root.isMSEL(input_type)"
                    :extra_vals="is_td_single || is_def_fields ? ['user','group'] : []"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
            ></tablda-user-select>

            <google-address-viewer
                    v-else-if="tableHeader.f_type === 'Address'
                        && ($root.checkAvailable($root.user, 'can_google_autocomplete') || $root.is_dcr_page)"
                    :table-meta="tableMeta"
                    :table-row="tableRow"
                    :table-header="tableHeader"
                    @update-row="updateRow"
                    @hide-elem="hideEdit"
            ></google-address-viewer>

            <tablda-select-ddl
                    v-else-if="inArray(input_type, $root.ddlInputTypes)
                            && tableHeader.ddl_id
                            && (tableHeader.ddl_style === 'ddl' || !isVertTable)"
                    :ddl_id="tableHeader.ddl_id"
                    :table-row="tableRow"
                    :table_id="tableMeta.id"
                    :hdr_field="tableHeader.field"
                    :fld_input_type="input_type"
                    :has_embed_func="tableHeader.ddl_add_option == 1 && !no_ddl_colls"
                    :style="getEditStyle"
                    :fixed_pos="true"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
                    @embed-func="showAddDDLOption"
            ></tablda-select-ddl>

            <input
                v-else-if="inArray(tableHeader.f_type, ['Attachment'])"
                type="text"
                @blur="hideEdit()"
                @paste="pasteAttachment"
                ref="inline_input"
                class="form-control full-height"
                placeholder="Ctrl + V to Paste File"
                :style="getEditStyle"/>

            <!--<input v-else-if="tableHeader.f_type === 'Time'"-->
                   <!--v-model="editValue"-->
                   <!--v-mask="'##:##:##'"-->
                   <!--@blur="hideEdit();updateValue()"-->
                   <!--ref="inline_input"-->
                   <!--class="form-control full-height"-->
                   <!--:style="getEditStyle"-->
                   <!--placeholder="hh:mm:ss"/>-->

            <input v-else-if="inArray(tableHeader.f_type, ['Duration'])"
                   ref="inline_input"
                   :value="getDuration()"
                   @blur="hideDuration()"
                   class="form-control full-height"
                   :style="getEditStyle"/>

            <input v-else-if="inArray(tableHeader.f_type, ['Date', 'Date Time', 'Time'])"
                   ref="inline_input"
                   @blur="hideDatePicker"
                   class="form-control full-height no_CF_for_date"
                   :style="getEditStyle"/>

            <textarea
                    v-else-if="inArray(tableHeader.f_type, ['String', 'Text', 'Long Text'])"
                    v-model="editValue"
                    @blur="hideEdit();updateValue()"
                    @resize="hideEdit();updateValue()"
                    ref="inline_input"
                    class="form-control full-height"
                    :placeholder="!tableHeader.placeholder_only_form || isVertTable ? tableHeader.placeholder_content : null"
                    :style="getEditStyle"
            ></textarea>

            <input
                    v-else-if="inArray(tableHeader.f_type, ['Integer', 'Decimal', 'Currency', 'Percentage', 'Address'])"
                    type="text"
                    v-model="editValue"
                    @blur="hideEdit();updateValue()"
                    ref="inline_input"
                    class="form-control full-height"
                    :placeholder="!tableHeader.placeholder_only_form || isVertTable ? tableHeader.placeholder_content : null"
                    :style="getEditStyle"/>

            <div v-else="">{{ hideEdit() }}</div>

        </div>
        <!-- ABSOLUTE EDITINGS -->



        <!-- Full-size img for attachments -->
        <full-size-img-block
                v-if="overImages && overImages.length"
                :file_arr="overImages"
                :file_idx="overImageIdx"
                @close-full-img="overImages = null"
        ></full-size-img-block>


        <!-- Open Popup with Application in iframe -->
        <custom-application-pop-up
                v-if="iframe_app_link"
                :tb_app="iframe_tb_app"
                :app_path="iframe_app_link"
                @close-app="closeAppReloadRows"
        ></custom-application-pop-up>

    </td>
</template>

<script>
import {UnitConversion} from '../../classes/UnitConversion';
import {SelectedCells} from '../../classes/SelectedCells';
import {SpecialFuncs} from '../../classes/SpecialFuncs';
import {Endpoints} from "../../classes/Endpoints";
import {FileHelper} from "../../classes/helpers/FileHelper";

import {eventBus} from '../../app';

import {mask} from 'vue-the-mask';

import CanEditMixin from '../_Mixins/CanViewEditMixin.vue';
import Select2DDLMixin from './../_Mixins/Select2DDLMixin.vue';
import CellMoveKeyHandlerMixin from './../_Mixins/CellMoveKeyHandlerMixin.vue';
import CellStyleMixin from '../_Mixins/CellStyleMixin.vue';

import CellTableDataExpand from './InCell/CellTableDataExpand.vue';
import CellTableContent from './InCell/CellTableContent.vue';
import TabldaColopicker from './InCell/TabldaColopicker.vue';
import TextareaAutosize from './InCell/TextareaAutosize.vue';
import SelectWithFolderStructure from './InCell/SelectWithFolderStructure.vue';
import FullSizeImgBlock from './../CommonBlocks/FullSizeImgBlock.vue';
import FormulaHelper from "./InCell/FormulaHelper.vue";
import CustomApplicationPopUp from "../CustomPopup/CustomApplicationPopUp.vue";
import StarsElems from "./InCell/StarsElems.vue";
import ProgressBar from "./InCell/ProgressBar.vue";
import TabldaSelectDdl from "./Selects/TabldaSelectDdl.vue";
import TabldaUserSelect from "./Selects/TabldaUserSelect.vue";
import GoogleAddressViewer from "./InCell/GoogleAddressViewer";
import VoteElement from "./InCell/VoteElement";
import CarouselBlock from "../CommonBlocks/CarouselBlock";
import ShowAttachmentsBlock from "../CommonBlocks/ShowAttachmentsBlock";

export default {
        name: "CustomCellTableData",
        mixins: [
            CanEditMixin,
            Select2DDLMixin,
            CellMoveKeyHandlerMixin,
            CellStyleMixin,
        ],
        components: {
            ShowAttachmentsBlock,
            CarouselBlock,
            VoteElement,
            GoogleAddressViewer,
            TabldaUserSelect,
            TabldaSelectDdl,
            ProgressBar,
            StarsElems,
            CustomApplicationPopUp,
            FormulaHelper,
            CellTableDataExpand,
            CellTableContent,
            TextareaAutosize,
            TabldaColopicker,
            FullSizeImgBlock,
            SelectWithFolderStructure,
        },
        directives: {
            mask
        },
        data: function () {
            return {
                overImages: null,
                overImageIdx: null,
                cont_height: 0,
                cont_width: 0,
                cont_html: null,

                no_key_handler: false,
                editing: false,
                editValue: null,
                source_field_for_values: null,
                freezed_by_format: false,
                open_color_picker: false,
                clrs: {
                    hex: this.tableRow[this.tableHeader.field]
                },
                show_field_res: null,
                uuid: uuidv4(),
                ddl_option: null,

                iframe_tb_app: null,
                iframe_app_link: null,
                ignore_add_behaviors: ['dcr_linked_tb'],
            }
        },
        props:{
            globalMeta: Object,
            tableMeta: Object,
            tableHeader: Object,
            tableRow: Object,
            rowIndex: Number,
            cellValue: String|Number,
            cellHeight: Number,
            maxCellRows: Number,
            selectedCell: SelectedCells,
            user: Object,
            with_edit: {
                type: Boolean,
                default: true
            },
            force_edit: Boolean,
            table_id: Number,
            behavior: String,
            isAddRow: Boolean,
            no_ddl_colls: Array,
            isVertTable: Boolean,
            link_popup_conditions: Object|Array,
            isSelected: Boolean,
            hasFloatColumns: Boolean,
            no_align: Boolean,
            is_td_single: Boolean,
            no_width: Boolean,
            extraStyle: Object,
            is_def_fields: Boolean,
            no_height_limit: Boolean,
            parentRow: Object,
        },
        watch: {///////////
            table_id: function (val) {
                this.editing = false;
                this.globEditingFalse();
            },
            cellValue: {
                handler(val) {
                    this.editValue = this.unitConvert(val);
                },
                immediate: true,
            },
            headerUnit(val) {
                this.editValue = this.unitConvert(this.cellValue);
            },
            headerUnitDisplay(val) {
                this.editValue = this.unitConvert(this.cellValue);
            },
        },
        computed: {
            kanbanPivot() {
                if (this.behavior === 'kanban_view' && this.parentRow && this.parentRow._fields_pivot) {
                    return _.find(this.parentRow._fields_pivot, {table_field_id: Number(this.tableHeader.id)});
                }
                return null;
            },
            attachmentsShowStyle() {
                return this.kanbanPivot ? this.kanbanPivot.picture_style : '';
            },
            attachImageFit() {
                return this.kanbanPivot ? this.kanbanPivot.picture_fit : '';
            },
            textAlign() {
                switch (this.tableHeader.col_align) {
                    case 'left' : return 'txt--left';
                    case 'right' : return 'txt--right';
                    default : return 'txt--center';
                }
            },
            input_type() {
                //special for 'Add New Option' Popup
                let input_type = this.tableHeader.input_type;
                if (this.inArray(this.tableHeader.field, this.no_ddl_colls) && this.inArray(input_type, this.$root.ddlInputTypes)) {
                    input_type = 'Input';
                }
                return input_type;
            },
            headerUnit() {
                return this.tableHeader.unit;
            },
            headerUnitDisplay() {
                return this.tableHeader.unit_display;
            },
            td_wrap_special() {
                return {
                    'full-height': this.inArray(this.tableHeader.f_type, ['Attachment'])
                };
            },

            //OTHER/
            underlinedLink() {
                return _.find(this.tableHeader._links, {icon: 'Underlined'});
            },
            canCellEdit() {
                let res = this.with_edit
                    //&& !this.inArray(this.tableHeader.f_type, ['Attachment']) //special rules and editing in another place for 'Attachments'
                    && ( //edit permissions forced
                        this.force_edit
                        || //can edit only owner OR user with available rights
                        this.canEditCell(this.tableHeader, this.tableRow)
                        || // OR user can add rows and cell is from new row
                        (this.isAddRow && !this.inArray(this.behavior, this.ignore_add_behaviors))
                    )
                    && !this.inArray(this.tableHeader.field, this.$root.systemFields) //cannot edit system fields
                    && !this.freezed_by_format //cannot edit cells freezed by CondFormat
                    && !this.hidden_by_format //cannot edit cells hidden by CondFormat
                    && this.tableHeader.input_type !== 'Mirror' //cannot edit 'Mirror' cells
                    && !(this.behavior === 'request_view' && this.tableRow.id); //if embed request -> can edit only newly added rows

                return Boolean(res);
            },
        },
        methods: {
            globEditingFalse() {
                this.$root.data_is_editing = false;
            },
            //EDITING
            inArray(item, array) {
                return $.inArray(item, array) > -1;
            },
            isEditing() {
                return this.editing && this.canCellEdit && !this.$root.global_no_edit;
            },
            showEdit(skip) {
                if (!skip && window.event.button != 0 && this.selectedCell) {
                    this.selectedCell.single_select(this.tableHeader, this.rowIndex);
                    return;
                }
                if (!skip && window.event.ctrlKey && this.selectedCell) {
                    this.selectedCell.square_select(this.tableHeader, this.rowIndex);
                    return;
                }
                //focus on cell (only on desktop)
                if (
                    window.screen.width >= 768
                    && this.selectedCell
                    && !this.selectedCell.disabled
                    && !this.selectedCell.is_selected(this.tableMeta, this.tableHeader, this.rowIndex)
                ) {
                    this.selectedCell.single_select(this.tableHeader, this.rowIndex);
                } else {
                    let listing_pop_edit = this.isVertTable && this.inArray(this.input_type, this.$root.ddlInputTypes) && this.tableHeader.ddl_id && this.tableHeader.ddl_style === 'panel';
                    let notedit_type = this.inArray(this.tableHeader.f_type, ['Boolean','Rating','Progress Bar','Vote','Color']) && this.tableHeader.input_type !== 'Formula';
                    if (!this.canCellEdit || listing_pop_edit || notedit_type || this.$root.prevent_cell_edit) {
                        return;
                    }
                    //edit cell
                    if (this.canCellEdit) {
                        this.editing = true;
                        this.$root.prevent_cell_keyup = !this.tableMeta.edit_one_click;
                        this.$root.data_is_editing = true;
                        this.$nextTick(function () {
                            if (this.inArray(this.input_type, this.$root.ddlInputTypes) && this.tableHeader.ddl_id ) {
                                this.ddl_cached = false;
                            }
                            else
                            if (this.inArray(this.tableHeader.f_type, ['Date Time', 'Date', 'Time'])) {
                                this.showHideDatePicker();
                            }
                            else
                            if (this.tableHeader.f_type === 'User') {
                                this.showHideUserPicker(true);
                            }
                            else {
                                if (this.$refs.inline_input) {

                                    if (this.tableHeader.f_type === 'String' && this.$refs.inline_input.$el) {
                                        this.$refs.inline_input.$el.focus();
                                    } else {
                                        this.$refs.inline_input.focus();
                                    }

                                }
                            }
                        });
                    }

                }
            },
            hideEdit() {
                this.no_key_handler = true;
                this.editing = false;
                this.globEditingFalse();
            },
            endSquareSelect() {
                if (window.event.button != 0 || this.$root.prevent_cell_keyup) {
                    this.$root.prevent_cell_keyup = false;
                    return;
                }
                if (this.selectedCell) {
                    if (this.tableMeta.edit_one_click && this.selectedCell.is_selected(this.tableMeta, this.tableHeader, this.rowIndex)) {
                        this.showEdit();
                    } else {
                        this.selectedCell.square_select(this.tableHeader, this.rowIndex);
                    }
                }
            },

            //CONVERTING
            unitConvert(val) {
                //active unit converter
                if (this.tableHeader.unit && this.tableHeader.unit_display && !this.tableHeader.__selected_unit_convs && this.tableMeta.unit_conv_is_active) {
                    let conversions = UnitConversion.findConvs(this.tableMeta, this.tableHeader);
                    this.$set(this.tableHeader, '__selected_unit_convs', conversions);
                }
                return SpecialFuncs.getEditValue(this.tableHeader, val);
            },

            //DATA UPDATING
            getDuration() {
                return SpecialFuncs.second2duration(this.editValue, this.tableHeader, true);
            },
            hideDuration() {
                this.editValue = SpecialFuncs.duration2second( $(this.$refs.inline_input).val() );
                this.hideEdit();
                this.updateValue();
            },
            showAddDDLOption() {
                this.$emit('show-add-ddl-option', this.tableHeader, this.tableRow);
            },
            isCheckedDDL(item) {
                if (this.$root.isMSEL(this.tableHeader.input_type)) {
                    return String(this.editValue).indexOf(item) > -1;
                } else {
                    return this.editValue === item;
                }
            },
            updateCheckedDDL(item, opt) {
                this.ddl_option = opt;
                if (this.$root.isMSEL(this.tableHeader.input_type)) {
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
            updateCheckBox() {
                if (!this.canCellEdit) {
                    return;
                }
                let val = (isNumber(this.editValue) ? parseFloat(this.editValue) : this.editValue);
                this.sendUpdateSignal(val ? 0 : 1);
            },
            fromCompoSet(val) {
                this.editValue = val;
                this.updateValue();
            },
            updateValue(ediVal) {
                let editVal = ediVal === undefined ? this.editValue : ediVal;
                editVal = SpecialFuncs.applySetMutator(this.tableHeader, editVal);
                if (to_standard_val(this.tableRow[this.tableHeader.field]) !== to_standard_val(editVal)) {
                    this.sendUpdateSignal(editVal);
                }
            },
            updateColor(clr, save) {
                if (save) {
                    this.$root.saveColorToPalette(clr);
                }
                this.sendUpdateSignal(clr);
            },
            sendUpdateSignal(editVal) {
                this.tableRow[this.tableHeader.field] = editVal;
                this.tableRow._changed_field = this.tableHeader.field;
                if (this.tableHeader.input_type === 'Formula') {
                    this.tableRow[this.tableHeader.field+'_formula'] = editVal;
                }
                this.$emit('updated-cell', this.tableRow, this.tableHeader, this.ddl_option);
            },
            updateRow() {
                this.editing = false;
                this.globEditingFalse();
                this.$emit('updated-cell', this.tableRow, this.tableHeader, this.ddl_option);
            },
            hideDatePicker() {
                this.hideEdit();
                let value = $(this.$refs.inline_input).val();
                switch (this.tableHeader.f_type) {
                    case 'Date': value = moment( value ).format('YYYY-MM-DD'); break;
                    case 'Date Time': value = moment( value ).format('YYYY-MM-DD HH:mm:ss'); break;
                    case 'Time': value = moment( '0001-01-01 '+value ).format('HH:mm:ss'); break;
                }
                if (value === 'Invalid date') {
                    value = '';
                }
                this.updateValue( value );
            },

            //SYSTEMS
            showSrcRecord(link, header, tableRow) {
                this.$emit('show-src-record', link, header, tableRow);
            },
            openAppAsPopup(tb_app, app_link) {
                this.iframe_app_link = app_link;
                this.iframe_tb_app = tb_app;
            },
            getuniqid() {
                return this.uuid;
            },
            changedContSize(height, width, cont_html) {
                //if (!this.isVertTable) {
                    this.cont_height = Number(height);
                    this.cont_width = Number(width);
                    this.cont_html = String(cont_html);
                //}
            },
            closeAppReloadRows(app_name) {
                this.iframe_app_link = null;
                this.iframe_tb_app = null;
                eventBus.$emit('reload-page', this.tableMeta.id);
                eventBus.$emit('cell-app-has-been-closed', this.tableMeta.id, app_name);
            },
            imgFromEmit(images, idx) {
                this.overImages = images;
                this.overImageIdx = idx;
            },

            //KEYBOARD
            changeCol(is_next) {
                if (this.editing) {
                    this.hideEdit();
                    this.updateValue();
                    if (this.$refs.inline_input && $(this.$refs.inline_input).hasClass('select2-hidden-accessible')) {
                        $(this.$refs.inline_input).select2('destroy');
                    }
                }
                this.$nextTick(() => {
                    this.selectedCell.next_col(this.tableMeta, is_next, this.isVertTable);
                });
            },
            tableDataStringUpdateHandler(uniq_id, val) {
                if (uniq_id === this.getuniqid()) {
                    this.editValue = val;
                    this.updateValue();
                }
            },
            globalClick(e) {
                let excluded = this.inArray(this.tableHeader.f_type, ['Address']);
                let container = $(this.$refs.cell_tb_data);
                if (!excluded && this.editing && container.has(e.target).length === 0) {
                    this.hideEdit();
                }
            },
            pasteAttachment(e) {
                let images = this.$root.getImagesFromClipboard(e);
                if (images.length) {
                    _.each(images, (file) => {
                        if (!FileHelper.checkFile(file, this.tableHeader.f_format)) {
                            return false;
                        }

                        this.$root.sm_msg_type = 1;
                        Endpoints.fileUpload({
                            table_id: this.tableMeta.id,
                            table_field_id: this.tableHeader.id,
                            row_id: this.tableRow.id,
                            file: file,
                        }).then(({ data }) => {
                            this.$root.attachFileToRow(this.tableRow, this.tableHeader, data);
                            if (data.filepath && data.filename) {
                                this.sendUpdateSignal(data.filepath + data.filename);
                            }
                        }).catch(errors => {
                            Swal('', getErrors(errors));
                        }).finally(() => {
                            this.$root.sm_msg_type = 0;
                        });
                    });
                } else {
                    Swal('', 'Images not found in the Clipboard.');
                }
                this.hideEdit();
            },
            rightMenu(e) {
                if (this.tableHeader.f_type !== 'Attachment') {
                    e.preventDefault();
                    this.$emit('cell-menu', this.tableRow, this.rowIndex, this.tableHeader);
                }
            },
        },
        mounted() {
            eventBus.$on('global-click', this.globalClick);
            eventBus.$on('global-keydown', this.globalKeydownHandler);
            eventBus.$on('table-data-string-popup__update', this.tableDataStringUpdateHandler);

            this.editValue = this.unitConvert(this.cellValue);
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

    .glyphicon-play {
        color: #3097D1;
        cursor: pointer;
    }
    .absolute-frame {
        overflow-y: hidden;
        overflow-x: auto;
    }
    .fix_ddl {
        z-index: 500 !important; //Fix position for DDLs in 'Sticky cells'
    }
    .no_CF_for_date {
        color: initial;
    }
</style>