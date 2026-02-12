<template>
    <td ref="cell_tb_data"
        :class="[isEditing() ? 'fix_ddl' : '']"
        :style="getCustomCellStyle()"
        class="td-custom"
        @contextmenu="rightMenu"
        @mouseenter="show_expand = $root.inArray(tableHeader.f_type, ['String', 'Text', 'Long Text', 'Auto String'])"
        @mouseleave="show_expand = false"
        @click.stop=""
    >
        <div :style="getTdWrappStyle()"
             class="td-wrapper"
             @mouseenter="(e) => { isVertTable ? null : $root.showHoverTooltip(e, tableHeader) }"
             @mouseleave="$root.leaveHoverTooltip"
             @mousedown.prevent="showEdit()"
             @mouseup.prevent="endSquareSelect()"
        >

            <div :class="td_wrap_special" :style="getWrapperStyle()" class="wrapper-inner">
                <div :class="td_wrap_special" :style="getInnerStyle()" class="inner-content">

                    <span v-if="hidden_by_format" class="by_format_hidden"></span>

                    <span v-else-if="tableHeader.id === tableMeta.multi_link_app_fld_id">
                        {{ showMultiAppLink() }}
                        <a v-if="showMultiAppLink()"
                           :href="asPopup || isPayment ? null : app_link"
                           :title="link ? link.tooltip : ''"
                           target="_blank"
                           @mousedown.stop=""
                           @mouseup.stop=""
                           @click.stop="asPopup ? handleOpenAppAsPopup(tb_app, app_link) : (isPayment ? newTabPopup() : null)"
                        >
                            <i class="glyphicon glyphicon-play"></i>
                        </a>
                    </span>

                    <boolean-elem
                        v-else-if="tableHeader.f_type === 'Boolean' && headerInputType !== 'Formula'"
                        :table-header="tableHeader"
                        :edit-value="editValue"
                        :can-cell-edit="canCellEdit"
                        @update-checkbox="updateCheckBox"
                    ></boolean-elem>

                    <stars-elems
                        v-else-if="tableHeader.f_type === 'Rating' && headerInputType !== 'Formula'"
                        :can_edit="canCellEdit"
                        :cur_val="parseFloat(editValue)"
                        :style="{height: (maxCellHGT < 25 ? maxCellHGT : 25)+'px', lineHeight: (maxCellHGT-2 < 25 ? maxCellHGT-2 : 25)+'px'}"
                        :table_header="tableHeader"
                        @set-star="fromCompoSet"
                    ></stars-elems>

                    <progress-bar
                        v-else-if="tableHeader.f_type === 'Progress Bar' && headerInputType !== 'Formula'"
                        :can_edit="canCellEdit"
                        :pr_val="parseFloat(editValue)"
                        :style="{height: (maxCellHGT < lineHeight+2 ? maxCellHGT : lineHeight+2)+'px'}"
                        :table_header="tableHeader"
                        @set-val="fromCompoSet"
                    ></progress-bar>

                    <vote-element
                        v-else-if="tableHeader.f_type === 'Vote' && headerInputType !== 'Formula'"
                        :can_edit="canCellEdit"
                        :cell_val="editValue"
                        :is_def_cell="!!tableRow._is_def_cell"
                        :small_padding="maxCellRowCnt < 2"
                        :table_header="tableHeader"
                        :user_info_settings="tableMeta._owner_settings"
                        @set-val="fromCompoSet"
                    ></vote-element>

                    <a v-else-if="tableHeader.f_type === 'Connected Clouds'"
                       title="Open clouds in popup."
                       @click.stop="showCloudPopup(tableRow.fetch_cloud_id)"
                    >{{ showConnCloud() }}</a>

                    <a v-else-if="tableHeader.f_type === 'Table'"
                       :href="getTableLink('url')"
                       :title="getTableLink('path')"
                       target="_blank"
                       @click.stop=""
                    >{{ getTableLink('name') }}</a>

                    <div v-else-if="tableHeader.f_type === 'Table Field'">{{ getTableField() }}</div>

                    <email-phone-element
                        v-else-if="inArray(tableHeader.f_type, ['Email', 'Phone Number'])"
                        :can_edit="canCellEdit"
                        :cell_val="editValue"
                        :table_header="tableHeader"
                        :table_meta="tableMeta"
                        :table_row="tableRow"
                        @element-update="updateValue"
                        @show-src-record="showSrcRecord"
                        @open-app-as-popup="handleOpenAppAsPopup"
                    ></email-phone-element>

                    <tablda-colopicker
                        v-else-if="tableHeader.f_type === 'Color' && !tableHeader.ddl_id && headerInputType !== 'Formula'"
                        :avail_null="true"
                        :can_edit="canCellEdit"
                        :fixed_pos="true"
                        :init_menu="open_edit"
                        :init_color="tableRow[tableHeader.field]"
                        @set-color="updateColor"
                    ></tablda-colopicker>

                    <tablda-select-ddl
                        v-else-if="isVertTable
                                    && inArray(headerInputType, $root.ddlInputTypes)
                                    && tableHeader.ddl_id
                                    && tableHeader.ddl_style === 'panel'
                                    && headerInputType !== 'Formula'"
                        :abstract_values="!!is_td_single"
                        :ddl_applied_field_id="tableHeader.id"
                        :ddl_id="tableHeader.ddl_id"
                        :ddl_type_panel="true"
                        :disabled="!canCellEdit"
                        :fixed_pos="true"
                        :fld_input_type="headerInputType"
                        :hdr_field="tableHeader.field"
                        :style="getEditStyle"
                        :table-row="tableRow"
                        :table_id="tableMeta.id"
                        @selected-item="updateCheckedDDL"
                        @hide-select="hideEdit"
                    ></tablda-select-ddl>

                    <show-attachments-block
                        v-else-if="tableHeader.f_type === 'Attachment'"
                        :can-edit="canEditHdr(tableHeader)"
                        :ext-thumb="isVertTable ? 'lg' : 'md'"
                        :image-fit="attachImageFit"
                        :is-grid-view="!isVertTable"
                        :show-type="attachmentsShowStyle"
                        :table-header="tableHeader"
                        :table-meta="tableMeta"
                        :table-row="tableRow"
                        @update-signal="sendUpdateSignal"
                    ></show-attachments-block>

                    <cell-table-content
                        v-else=""
                        :behavior="behavior"
                        :can_edit="canCellEdit"
                        :cur-width="tableHeader.width"
                        :edit-value="editValue"
                        :font-size="themeTextFontSize"
                        :global-meta="globalMeta"
                        :is-vert-table="isVertTable"
                        :is_def_fields="is_def_fields"
                        :is_td_single="is_td_single"
                        :max-cell-rows="maxCellRows"
                        :no_height_limit="no_height_limit"
                        :table-header="tableHeader"
                        :table-meta="tableMeta"
                        :table-row="tableRow"
                        :user="user"
                        @show-src-record="showSrcRecord"
                        @open-app-as-popup="handleOpenAppAsPopup"
                        @unselect-val="updateCheckedDDL"
                        @full-size-image="imgFromEmit"
                    ></cell-table-content>

                </div>
            </div>


            <cell-table-data-expand
                v-if="show_expand && String(editValue).length*themeTextFontSize > tableHeader.width"
                :can-edit="canCellEdit"
                :table-header="tableHeader"
                :table-meta="tableMeta"
                :table-row="tableRow"
                :uniqid="getuniqid()"
                :user="user"
                style="background-color: #FFF;"
            ></cell-table-data-expand>

        </div>


        <!-- ABSOLUTE EDITINGS -->
        <div v-if="isEditing()" :style="{backgroundColor: headerInputType === 'Formula' ? 'transparent' : ''}"
             class="cell-editing">

            <tablda-select-simple
                v-if="tableHeader.id === tableMeta.multi_link_app_fld_id"
                :can_empty="true"
                :fixed_pos="true"
                :hdr_field="tableHeader.field"
                :options="multiAppOpts()"
                :style="getEditStyle"
                :table-row="tableRow"
                @selected-item="updateCheckedDDL"
                @hide-select="hideEdit"
            ></tablda-select-simple>

            <formula-helper
                v-else-if="headerInputType === 'Formula'"
                :can-edit="canCellEdit"
                :fixed_pos="true"
                :foreign_element="$refs.cell_tb_data"
                :header-key="is_def_fields || is_td_single ? tableHeader.field : tableHeader.field+'_formula'"
                :is_td_single="is_td_single"
                :no_uniform="!!is_def_fields"
                :pop_width="'100%'"
                :table-header="tableHeader"
                :table-meta="tableMeta"
                :table-row="tableRow"
                :user="user"
                @close-formula="hideEdit"
                @set-formula="updateRow"
            ></formula-helper>

            <tablda-user-select
                v-else-if="tableHeader.f_type === 'User'"
                :can_empty="true"
                :edit_value="editValue"
                :extra_vals="is_td_single || is_def_fields ? ['user','group'] : []"
                :fixed_pos="true"
                :multiselect="$root.isMSEL(headerInputType)"
                :style="getEditStyle"
                :table_field="tableHeader"
                @selected-item="userSelUpdated"
                @hide-select="hideEdit"
            ></tablda-user-select>

            <select-with-folder-structure
                v-else-if="tableHeader.f_type === 'Table'"
                :available_tables="$root.settingsMeta.available_tables"
                :cur_val="editValue"
                :for_single_select="true"
                :style="getEditStyle"
                :user="user"
                class="form-control full-height"
                @sel-changed="(val) => {editValue = val;}"
                @sel-closed="hideEdit();updateValue();">
            </select-with-folder-structure>

            <tablda-select-simple
                v-else-if="tableHeader.f_type === 'Table Field'"
                :can_empty="true"
                :fixed_pos="true"
                :hdr_field="tableHeader.field"
                :options="tableFieldColumns()"
                :style="getEditStyle"
                :table-row="tableRow"
                @selected-item="updateCheckedDDL"
                @hide-select="hideEdit"
            ></tablda-select-simple>

            <ck-editor-helper
                v-else-if="tableHeader.f_type === 'HTML'"
                :table-header="tableHeader"
                :table-meta="globalMeta"
                :table-row="tableRow"
                @close-ck-editor="fromCompoSetAndHide"
            ></ck-editor-helper>

            <tablda-select-simple
                v-else-if="tableHeader.f_type === 'Connected Clouds'"
                :can_empty="true"
                :fixed_pos="true"
                :hdr_field="tableHeader.field"
                :options="metaClouds()"
                :style="getEditStyle"
                :table-row="tableRow"
                @selected-item="updateCheckedDDL"
                @hide-select="hideEdit"
            ></tablda-select-simple>

            <google-address-viewer
                v-else-if="tableHeader.f_type === 'Address'
                        && ($root.checkAvailable($root.user, 'can_google_autocomplete') || $root.is_dcr_page)"
                :table-header="tableHeader"
                :table-meta="tableMeta"
                :table-row="tableRow"
                @update-row="updateRow"
                @hide-elem="hideEdit"
            ></google-address-viewer>

            <tablda-select-ddl
                v-else-if="inArray(headerInputType, $root.ddlInputTypes)
                            && tableHeader.ddl_id
                            && (tableHeader.ddl_style === 'ddl' || !isVertTable)"
                :abstract_values="!!is_td_single"
                :ddl_applied_field_id="tableHeader.id"
                :ddl_id="tableHeader.ddl_id"
                :fixed_pos="true"
                :fld_input_type="headerInputType"
                :has_embed_func="tableHeader.ddl_add_option == 1 && !no_ddl_colls"
                :hdr_field="tableHeader.field"
                :style="getEditStyle"
                :table-row="tableRow"
                :table_id="tableMeta.id"
                @selected-item="updateCheckedDDL"
                @hide-select="hideEdit"
                @embed-func="showAddDDLOption"
            ></tablda-select-ddl>

            <input
                v-else-if="inArray(tableHeader.f_type, ['Attachment'])"
                ref="inline_input"
                :style="getEditStyle"
                class="form-control full-height"
                placeholder="Ctrl + V to Paste File"
                type="text"
                @blur="hideEdit()"
                @paste="pasteAttachment"/>

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
                   v-model="editValue"
                   :style="getEditStyle"
                   class="form-control full-height"
                   @blur="hideDuration()"/>

            <input v-else-if="inArray(tableHeader.f_type, ['Date', 'Date Time', 'Time'])"
                   ref="inline_input"
                   :style="getEditStyle"
                   class="form-control full-height no_CF_for_date"
                   @blur="hideDatePicker"
                   @keyup.stop=""
                   @keydown.stop=""
                   @keypress.stop=""/>

            <textarea
                v-else-if="inArray(tableHeader.f_type, ['String', 'Text', 'Long Text', 'Email', 'Phone Number'])"
                ref="inline_input"
                v-model="editValue"
                :placeholder="!tableHeader.placeholder_only_form || isVertTable ? tableHeader.placeholder_content : null"
                :style="getEditStyle"
                class="form-control full-height"
                @blur="hideEdit();updateValue()"
                @resize="hideEdit();updateValue()"
            ></textarea>

            <input
                v-else-if="inArray(tableHeader.f_type, ['Integer', 'Decimal', 'Currency', 'Percentage', 'Address', 'Auto String', 'Auto Number', 'Gradient Color'])"
                ref="inline_input"
                v-model="editValue"
                :placeholder="!tableHeader.placeholder_only_form || isVertTable ? tableHeader.placeholder_content : null"
                :style="getEditStyle"
                class="form-control full-height"
                @blur="hideEdit();updateValue()"
                @keydown="preventNonNumber"/>

            <div v-else="">{{ hideEdit() }}</div>

        </div>
        <!-- ABSOLUTE EDITINGS -->


        <!-- Full-size img for attachments -->
        <full-size-img-block
            v-if="overImages && overImages.length"
            :file_arr="overImages"
            :file_idx="overImageIdx"
            :table_header="tableHeader"
            :table_meta="tableMeta"
            :table_row="tableRow"
            @close-full-img="overImages = null"
        ></full-size-img-block>


        <!-- Open Popup with Application in iframe -->
        <custom-application-pop-up
            v-if="iframe_app_link"
            :app_path="iframe_app_link"
            :tb_app="iframe_tb_app"
            @close-app="closeAppReloadRows"
        ></custom-application-pop-up>

    </td>
</template>

<script>
import {OptionsHelper} from "../../classes/helpers/OptionsHelper";
import {JsFomulaParser} from '../../classes/JsFomulaParser';
import {UnitConversion} from '../../classes/UnitConversion';
import {SelectedCells} from '../../classes/SelectedCells';
import {SpecialFuncs} from '../../classes/SpecialFuncs';
import {Endpoints} from "../../classes/Endpoints";
import {FileHelper} from "../../classes/helpers/FileHelper";
import {LinkHelper} from "../../classes/helpers/LinkHelper";

import {eventBus} from '../../app';

import CanEditMixin from '../_Mixins/CanViewEditMixin.vue';
import Select2DDLMixin from './../_Mixins/Select2DDLMixin.vue';
import CellMoveKeyHandlerMixin from './../_Mixins/CellMoveKeyHandlerMixin.vue';
import CellStyleMixin from '../_Mixins/CellStyleMixin.vue';
import LinkAppFunctionsMixin from '../_Mixins/LinkAppFunctionsMixin.vue';

import CellTableDataExpand from './InCell/CellTableDataExpand.vue';
import CellTableContent from './InCell/CellTableContent.vue';
import TabldaColopicker from './InCell/TabldaColopicker.vue';
import TextareaAutosize from './InCell/TextareaAutosize.vue';
import SelectWithFolderStructure from './InCell/SelectWithFolderStructure.vue';
import FullSizeImgBlock from './../CommonBlocks/FullSizeImgBlock.vue';
import CustomApplicationPopUp from "../CustomPopup/CustomApplicationPopUp.vue";
import StarsElems from "./InCell/StarsElems.vue";
import ProgressBar from "./InCell/ProgressBar.vue";
import TabldaSelectDdl from "./Selects/TabldaSelectDdl.vue";
import TabldaUserSelect from "./Selects/TabldaUserSelect.vue";
import GoogleAddressViewer from "./InCell/GoogleAddressViewer";
import VoteElement from "./InCell/VoteElement";
import CarouselBlock from "../CommonBlocks/CarouselBlock";
import ShowAttachmentsBlock from "../CommonBlocks/ShowAttachmentsBlock";
import TabldaSelectSimple from "./Selects/TabldaSelectSimple";
import EmailPhoneElement from "./InCell/EmailPhoneElement";
import CkEditorHelper from "./InCell/CkEditorHelper.vue";
import BooleanElem from "./InCell/BooleanElem.vue";

export default {
    name: "CustomCellTableData",
    mixins: [
        CanEditMixin,
        Select2DDLMixin,
        CellMoveKeyHandlerMixin,
        CellStyleMixin,
        LinkAppFunctionsMixin,
    ],
    components: {
        BooleanElem,
        CkEditorHelper,
        EmailPhoneElement,
        TabldaSelectSimple,
        ShowAttachmentsBlock,
        CarouselBlock,
        VoteElement,
        GoogleAddressViewer,
        TabldaUserSelect,
        TabldaSelectDdl,
        ProgressBar,
        StarsElems,
        CustomApplicationPopUp,
        CellTableDataExpand,
        CellTableContent,
        TextareaAutosize,
        TabldaColopicker,
        FullSizeImgBlock,
        SelectWithFolderStructure,
    },
    data: function () {
        return {
            show_expand: false,
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
            link: null,
        }
    },
    props: {
        globalMeta: Object,
        tableMeta: Object,
        tableHeader: Object,
        tableRow: Object,
        allRows: Object | null,
        rowIndex: Number,
        cellValue: String | Number,
        cellHeight: Number,
        maxCellRows: Number,
        selectedCell: SelectedCells,
        user: Object,
        with_edit: {
            type: Boolean,
            default: true
        },
        open_edit: Boolean,
        force_edit: Boolean,
        table_id: Number,
        behavior: String,
        isAddRow: Boolean,
        no_ddl_colls: Array,
        isVertTable: Boolean,
        link_popup_conditions: Object | Array,
        isSelectedExt: Boolean,
        hasFloatColumns: Boolean,
        no_align: Boolean,
        is_td_single: Object,
        no_width: Boolean,
        extraStyle: Object,
        is_def_fields: Boolean,
        no_height_limit: Boolean,
        fixedWidth: Boolean,
        parentRow: Object,
        extraPivotFields: Array,
        isLink: Object,//CanViewEditMixin.vue
    },
    watch: {///////////
        table_id: function (val) {
            this.hideEdit();
        },
        cellValue: {
            handler(val) {
                this.editValue = this.unitConvert(val);
                this.fillLink();
            },
            immediate: true,
        },
        headerUnit(val) {
            this.editValue = this.unitConvert(this.cellValue);
        },
        headerUnitDisplay(val) {
            this.editValue = this.unitConvert(this.cellValue);
        },
        'tableMeta.multi_link_app_fld_id': function (val) {
            this.fillLink();
        },
    },
    computed: {
        isSelected() {
            return this.isSelectedExt && this.canCellEdit;
        },
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
            if (this.kanbanPivot) {
                return this.kanbanPivot.picture_fit;
            }
            if (this.inArray(this.behavior, ['list_view', 'favorite'])) {
                return this.tableHeader.image_fitting;
            }
            return '';
        },
        textAlign() {
            switch (this.tableHeader.col_align) {
                case 'left' :
                    return 'txt--left';
                case 'right' :
                    return 'txt--right';
                default :
                    return 'txt--center';
            }
        },
        headerInputType() {
            if (this.tableHeader.input_type === 'Mirror' && this.tableHeader.mirror_rc_id) {
                return this.tableHeader.mirror_edit_component;
            }
            //special for 'Add New Option' Popup
            let inputType = this.tableHeader.input_type;
            if (this.inArray(this.tableHeader.field, this.no_ddl_colls) && this.inArray(inputType, this.$root.ddlInputTypes)) {
                inputType = 'Input';
            }
            return inputType;
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
            let linkNoEdit = false;
            if (this.isLink && !Number(this.isLink.editability_rced_fields)) {
                let rc = _.find(this.$root.tableMeta._ref_conditions, {id: Number(this.isLink.table_ref_condition_id)});
                rc = rc || _.find(this.globalMeta._ref_conditions, {id: Number(this.isLink.table_ref_condition_id)});

                let noEditFields = [];
                _.each(rc ? rc._items : [], (item) => {
                    noEditFields.push(item.table_field_id);
                    noEditFields.push(item.compared_field_id);
                });
                linkNoEdit = noEditFields.indexOf(this.tableHeader.id) > -1;
            }

            let res = this.with_edit
                && !linkNoEdit
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
                && (this.tableHeader.input_type !== 'Mirror' || this.tableHeader.mirror_editable); //cannot edit 'Mirror' cells

            return Boolean(res);
        },
    },
    methods: {
        getCustomCellStyle() {
            let style = this.getCellStyle();
            if (
                this.tableHeader.input_type === 'Mirror'
                && this.tableHeader.mirror_editable
                && this.tableMeta.mirror_edited_underline
                && this.tableRow[this.tableHeader.field + '_mirror']
            ) {
                style.textDecoration = 'underline';
            }
            if (this.tableHeader.f_type === 'Gradient Color') {
                style.backgroundColor = SpecialFuncs.getGradientColor(this.tableHeader, this.editValue);
                style.color = SpecialFuncs.smartTextColorOnBg(style.backgroundColor);
            }
            return style;
        },
        //EDITING
        inArray(item, array) {
            return $.inArray(item, array) > -1;
        },
        isEditing() {
            return this.editing && this.canCellEdit && !this.$root.global_no_edit;
        },
        showEdit(skip) {
            if (!skip && window.event.button != 0 && (this.selectedCell || this.behavior === 'grouping_table')) {
                this.selectedCell ? this.selectedCell.single_select(this.tableHeader, this.rowIndex) : null;
                this.$emit('edit-closed');
                return;
            }
            let cmdOrCtrl = window.event.metaKey || window.event.ctrlKey;
            if (!skip && cmdOrCtrl && this.selectedCell) {
                this.selectedCell.square_select(this.tableHeader, this.rowIndex);
                this.$emit('edit-closed');
                return;
            }
            //focus on cell (only on desktop)
            if (
                window.innerWidth >= 768
                && this.selectedCell
                && !this.selectedCell.disabled
                && !this.selectedCell.is_selected(this.tableMeta, this.tableHeader, this.rowIndex)
            ) {
                this.selectedCell.single_select(this.tableHeader, this.rowIndex);
            } else {

                let listing_pop_edit = this.isVertTable
                    && this.inArray(this.headerInputType, this.$root.ddlInputTypes)
                    && this.tableHeader.ddl_id
                    && this.tableHeader.ddl_style === 'panel';

                let notedit_type = this.inArray(this.tableHeader.f_type, ['Boolean', 'Rating', 'Progress Bar', 'Vote', 'Color', 'Email', 'Phone Number'])
                    && this.headerInputType !== 'Formula';

                if (!this.canCellEdit || listing_pop_edit || notedit_type || this.$root.prevent_cell_edit) {
                    this.$emit('edit-closed');
                    return;
                }
                //edit cell
                if (this.canCellEdit) {
                    window.setTimeout(() => {//Fix: hidden FormulaHelper and other components on edit click
                        this.$emit('edit-opened');
                        this.editing = true;
                        this.$root.prevent_cell_keyup = !this.tableMeta.edit_one_click;
                        this.$nextTick(function () {
                            if (this.inArray(this.headerInputType, this.$root.ddlInputTypes) && this.tableHeader.ddl_id) {
                                this.ddl_cached = false;
                            } else if (this.inArray(this.tableHeader.f_type, ['Date Time', 'Date', 'Time'])) {
                                this.showHideDatePicker();
                            } else if (this.tableHeader.f_type === 'User') {
                                this.showHideUserPicker(true);
                            } else {
                                if (this.$refs.inline_input) {

                                    if (this.tableHeader.f_type === 'String' && this.$refs.inline_input.$el) {
                                        this.$refs.inline_input.$el.focus();
                                    } else {
                                        this.$refs.inline_input.focus();
                                    }

                                }
                            }
                        });
                    }, 100);
                }

            }
        },
        hideEdit() {
            this.no_key_handler = true;
            this.editing = false;
            this.$emit('edit-closed');
        },
        endSquareSelect() {
            if (window.event.button != 0 || this.$root.prevent_cell_keyup) {
                this.$root.prevent_cell_keyup = false;
                return;
            }
            if (this.selectedCell) {
                if (this.tableMeta.edit_one_click && !this.editValue && this.selectedCell.is_selected(this.tableMeta, this.tableHeader, this.rowIndex)) {
                    this.showEdit();
                } else {
                    this.selectedCell.square_select(this.tableHeader, this.rowIndex);
                }
            }
        },

        //CONVERTING
        unitConvert(val) {
            return SpecialFuncs.getEditValue(this.tableHeader, val);
        },

        //DATA UPDATING
        hideDuration() {
            this.editValue = SpecialFuncs.duration2second($(this.$refs.inline_input).val()) || $(this.$refs.inline_input).val();
            this.hideEdit();
            this.updateValue();
        },
        showAddDDLOption() {
            this.$emit('show-add-ddl-option', this.tableHeader, this.tableRow);
        },
        isCheckedDDL(item) {
            if (this.$root.isMSEL(this.headerInputType)) {
                return String(this.editValue).indexOf(item) > -1;
            } else {
                return this.editValue === item;
            }
        },
        userSelUpdated(id, temp_string) {
            if (!this.tableRow['_u_' + this.tableHeader.field]) {
                this.tableRow['_u_' + this.tableHeader.field] = {};
            }
            this.tableRow['_u_' + this.tableHeader.field][_.trim(id)] = {
                username: temp_string,
            };
            this.updateCheckedDDL(id);
        },
        updateCheckedDDL(item, opt) {
            this.ddl_option = opt;
            if (this.$root.isMSEL(this.headerInputType)) {
                this.editValue = Array.isArray(this.editValue) ? this.editValue : [String(this.editValue)];
                if (this.editValue.indexOf(item) > -1) {
                    this.editValue.splice(this.editValue.indexOf(item), 1);
                } else {
                    this.editValue.push(item);
                }
            } else {
                this.editValue = item;
            }
            this.updateValue();
        },
        updateCheckBox(val) {
            if (!this.canCellEdit) {
                return;
            }
            this.sendUpdateSignal(val);
        },
        fromCompoSetAndHide(val) {
            this.fromCompoSet(val);
            this.hideEdit();
        },
        fromCompoSet(val) {
            this.editValue = val;
            this.updateValue();
        },
        updateValue(ediVal) {
            let skip = this.inArray(this.tableHeader.f_type, ['HTML']);
            let editVal = ediVal === undefined ? this.editValue : ediVal;
            editVal = SpecialFuncs.applySetMutator(this.tableHeader, editVal);
            if (skip || to_standard_val(this.tableRow[this.tableHeader.field]) !== to_standard_val(editVal)) {
                this.sendUpdateSignal(editVal);
            }
        },
        preventNonNumber(e) {
            let isNumFld = this.inArray(this.tableHeader.f_type, ['Integer', 'Decimal', 'Currency', 'Percentage', 'Auto Number'])
            let nonNum = e.key && e.key.length == 1 && e.key.match(/[^\d\.,\-+e%]/);
            if (isNumFld && nonNum) {
                e.preventDefault();
            }
        },
        updateColor(clr, save) {
            if (save) {
                this.$root.saveColorToPalette(clr);
            }
            this.sendUpdateSignal(clr);
        },
        sendUpdateSignal(editVal) {
            this.tableRow._old_val = this.tableRow[this.tableHeader.field];
            this.tableRow[this.tableHeader.field] = editVal;
            this.tableRow._changed_field = this.tableHeader.field;
            if (this.headerInputType === 'Formula') {
                this.tableRow[this.tableHeader.field + '_formula'] = editVal;
            }
            if (this.tableHeader.input_type === 'Mirror') {
                this.tableRow[this.tableHeader.field + '_mirror'] = editVal ? 'changed' : '';
            }
            this.$emit('updated-cell', this.tableRow, this.tableHeader, this.ddl_option);
            this.runCalculation();
        },
        updateRow() {
            this.hideEdit();
            this.$emit('updated-cell', this.tableRow, this.tableHeader, this.ddl_option);
            this.runCalculation();
        },
        runCalculation() {
            if (this.headerInputType === 'Formula' && this.tableHeader.is_uniform_formula) {
                eventBus.$emit('table-formulas-recalculate', this.tableMeta.id, this.tableHeader.id, this.tableRow[this.tableHeader.field + '_formula']);
            } else {
                JsFomulaParser.checkRowAndCalculate(this.tableMeta, this.tableRow);
            }
        },
        hideDatePicker() {
            this.hideEdit();
            let value = $(this.$refs.inline_input).val();
            if (SpecialFuncs.isSpecialVar(this.dateTemp)) {
                value = this.dateTemp; //special variables: {{Today}} etc.
            } else {
                switch (this.tableHeader.f_type) {
                    case 'Date':
                        value = moment(value).format('YYYY-MM-DD');
                        break;
                    case 'Date Time':
                        value = moment(value).format('YYYY-MM-DD HH:mm:ss');
                        break;
                    case 'Time':
                        value = moment('0001-01-01 ' + value).format('HH:mm:ss');
                        break;
                }
                if (value === 'Invalid date') {
                    value = '';
                }
            }
            this.updateValue(value);
        },

        //SYSTEMS
        showSrcRecord(link, header, tableRow) {
            this.$emit('show-src-record', link, header, tableRow);
        },
        handleOpenAppAsPopup(tb_app, app_link) {
            this.iframe_app_link = app_link;
            this.iframe_tb_app = tb_app;
        },
        getuniqid() {
            return this.uuid;
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

        //Connected clouds field
        metaClouds() {
            let filtered = _.filter(this.$root.settingsMeta.user_clouds_data, (acc) => {
                return acc.__is_connected;
            });
            return _.map(filtered, (acc) => {
                return {val: acc.id, show: acc.name};
            });
        },
        showConnCloud() {
            let cld = _.find(this.metaClouds(), {val: Number(this.tableRow[this.tableHeader.field])});
            return cld ? cld.show : this.tableRow[this.tableHeader.field];
        },
        showCloudPopup(id) {
            let idx = _.findIndex(this.$root.settingsMeta.user_clouds_data, {id: id});
            eventBus.$emit('open-resource-popup', 'connections', idx, 'cloud');
        },

        //Tables and Fields columns
        getTableLink(type) {
            if (!this.editValue) {
                return '';
            }
            let tb = _.find(this.$root.settingsMeta.available_tables, {id: Number(this.editValue)});
            if (!tb) {
                return '';
            }
            switch (type) {
                case 'name':
                    return tb.name;
                case 'path':
                    return (new URL(tb.__url)).pathname.replace('/data/', '');
                case 'url':
                    return tb.__url;
                default:
                    return '';
            }
        },
        getTableField() {
            if (!this.editValue) {
                return '';
            }
            let tb = this.getOtherTable();
            let thisFld = _.find(tb._fields || [], {id: Number(this.editValue)}) || {};
            return thisFld.name || this.editValue;
        },
        getOtherTable() {
            let fldWithTable = _.find(this.globalMeta._fields, {id: Number(this.tableHeader.f_format)}) || {};
            let tbId = Number(this.tableRow[fldWithTable.field]);
            return _.find(this.$root.settingsMeta.available_tables, {id: tbId}) || {};
        },
        tableFieldColumns() {
            let otherTable = this.getOtherTable();
            let fields = _.filter(otherTable._fields || [], (hdr) => {
                return !this.inArray(hdr.field, this.$root.systemFieldsNoId)
            });
            return _.map(fields, (hdr) => {
                return {val: hdr.id, show: this.$root.uniqName(hdr.name),}
            });
        },

        //KEYBOARD
        tableDataStringUpdateHandler(uniq_id, val) {
            if (uniq_id === this.getuniqid()) {
                this.editValue = val;
                this.updateValue();
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
                        row_id: Number(this.tableRow.id) || this.tableRow._temp_id,
                        file: file,
                    }).then(({data}) => {
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
                Swal('Info', 'Images not found in the Clipboard.');
            }
            this.hideEdit();
        },
        rightMenu(e) {
            if (this.tableHeader.f_type !== 'Attachment') {
                e.preventDefault();
                this.$emit('cell-menu', this.tableRow, this.rowIndex, this.tableHeader);
            }
        },
        tableDataOpenEdit(tbid) {
            if (this.isSelectedExt && tbid == this.tableMeta.id) {
                if (this.$root.prevent_cell_edit) {
                    let waiter = setInterval(() => {
                        if (!this.$root.prevent_cell_edit) {
                            clearInterval(waiter);
                            this.showEdit(true);
                        }
                    }, 300);
                } else {
                    this.showEdit(true);
                }
            }
        },
        multiAppOpts() {
            return OptionsHelper.allLinks(this.tableMeta, 'App');
        },
        showMultiAppLink() {
            let link = _.find(OptionsHelper.allLinks(this.tableMeta, 'App'), {val: this.editValue});
            return link ? link.show : '';
        },
        fillLink() {
            this.link = this.tableHeader.id === this.tableMeta.multi_link_app_fld_id
                ? _.find(LinkHelper.allLinks(this.tableMeta), {id: this.editValue})
                : null;
        },
    },
    mounted() {
        eventBus.$on('global-click', this.globalClick);
        eventBus.$on('global-keydown', this.globalKeydownHandler);
        eventBus.$on('table-data-string-popup__update', this.tableDataStringUpdateHandler);
        eventBus.$on('table-data-open-edit', this.tableDataOpenEdit);

        this.editValue = this.unitConvert(this.cellValue);
        this.fillLink();

        if (this.open_edit) {
            this.$nextTick(() => {
                this.showEdit();
            });
        }
    },
    beforeDestroy() {
        eventBus.$off('global-click', this.globalClick);
        eventBus.$off('global-keydown', this.globalKeydownHandler);
        eventBus.$off('table-data-string-popup__update', this.tableDataStringUpdateHandler);
        eventBus.$off('table-data-open-edit', this.tableDataOpenEdit);
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