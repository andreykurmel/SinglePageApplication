<template>
    <td :style="getCellStyle"
        class="td-custom"
        :class="[isEditing() ? 'fix_ddl' : '']"
        ref="cell_tb_data"
        @click.stop=""
        @dragenter="drEnter"
        @dragover.prevent=""
        @dragleave="drLeave"
        @drop.prevent.stop="attachDrop"
    >
        <div class="td-wrapper"
             :style="getTdWrappStyle"
             @mouseenter="(e) => { $root.showHoverTooltip(e, tableHeader) }"
             @mouseleave="$root.leaveHoverTooltip"
             @mousedown.prevent="showEdit()"
             @mouseup.prevent="endSquareSelect()"
        >

            <div class="wrapper-inner" :style="getWrapperStyle">
                <div class="inner-content" :style="getInnerStyle">

                    <span v-if="hidden_by_format"></span>

                    <template v-else-if="tableHeader.f_type === 'Boolean'">
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
                            v-else-if="tableHeader.f_type === 'Star Rating'"
                            :cur_val="parseFloat(editValue)"
                            :can_edit="canCellEdit"
                            @set-star="fromCompoSet"
                            :style="{height: (maxCellHGT < 25 ? maxCellHGT : 25)+'px', lineHeight: (maxCellHGT-2 < 25 ? maxCellHGT-2 : 25)+'px'}"
                    ></stars-elems>

                    <progress-bar
                            v-else-if="tableHeader.f_type === 'Progress Bar'"
                            :pr_val="parseFloat(editValue)"
                            :can_edit="canCellEdit"
                            :is_selected="isSelected"
                            :table_header="tableHeader"
                            @set-val="fromCompoSet"
                            :style="{height: (maxCellHGT < lineHeight+2 ? maxCellHGT : lineHeight+2)+'px'}"
                    ></progress-bar>

                    <vote-element
                            v-else-if="tableHeader.f_type === 'Vote'"
                            :cell_val="editValue"
                            :can_edit="canCellEdit"
                            :table_header="tableHeader"
                            :user_info_settings="tableMeta._owner_settings"
                            :small_padding="maxCellRowCnt < 2"
                            :is_def_cell="!!tableRow._is_def_cell"
                            @set-val="fromCompoSet"
                    ></vote-element>

                    <tablda-select-ddl
                            v-else-if="isVertTable
                                    && inArray(input_type, $root.ddlInputTypes)
                                    && tableHeader.ddl_id
                                    && tableHeader.ddl_style === 'panel'"
                            :ddl_id="tableHeader.ddl_id"
                            :ddl_type_panel="true"
                            :table-row="tableRow"
                            :hdr_field="tableHeader.field"
                            :fld_input_type="input_type"
                            :disabled="!canCellEdit"
                            :fixed_pos="reactive_provider.fixed_ddl_pos"
                            :abstract_values="is_td_single"
                            :style="getEditStyle"
                            @selected-item="updateCheckedDDL"
                            @hide-select="hideEdit"
                    ></tablda-select-ddl>

                    <div v-else-if="tableHeader.f_type === 'Attachment' && attachImages" class="absolute-frame no-wrap" :class="textAlign">
                        <template v-if="image_carousel">
                            <carousel-block :images="attachImages" @img-clicked="imgClick"></carousel-block>
                        </template>
                        <template v-else="">
                            <template v-for="(image, idx) in attachImages">
                                <a target="_blank" class="img_a has-deleter" @click="dwnFile(image)" :href="dwnPath(image)">
                                    <img class="_img_preview"
                                         :src="$root.fileUrl(image)"
                                         @click="imgClick(attachImages, idx)">
                                    <span class="img--deleter"
                                          @click.stop.prevent="deleteFile(attachImages, idx, tableHeader)">&times;</span>
                                </a>
                                <!--<br/>-->
                            </template>
                        </template>
                    </div>

                    <div v-else-if="tableHeader.f_type === 'Attachment' && attachFiles" style="white-space: nowrap;">
                        <template v-for="(file, idx) in attachFiles">
                            <a target="_blank" class="has-deleter" @click="dwnFile(file)" :href="dwnPath(file)">
                                <span>{{ file.filename }}</span>
                                <span class="img--deleter"
                                      @click.stop.prevent="deleteFile(attachFiles, idx, tableHeader)">&times;</span>
                            </a>
                            <br/>
                        </template>
                    </div>

                    <cell-table-content
                            v-else=""
                            :global-meta="globalMeta"
                            :table-meta="tableMeta"
                            :table-row="tableRow"
                            :table-header="tableHeader"
                            :edit-value="editValue"
                            :max-cell-rows="$root.maxCellRows"
                            :font-size="$root.themeTextFontSize"
                            :cur-width="tableHeader.width"
                            :user="user"
                            :can_edit="canCellEdit"
                            :is-vert-table="isVertTable"
                            @changed-cont-size="changedContSize"
                            @show-src-record="showSrcRecord"
                            @open-app-as-popup="openAppAsPopup"
                            @unselect-val="updateCheckedDDL"
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
        <div v-if="isEditing()" class="cell-editing">

            <formula-helper
                    v-if="tableHeader.input_type === 'Formula' && !is_td_single"
                    :user="user"
                    :table-meta="tableMeta"
                    :table-row="tableRow"
                    :table-header="tableHeader"
                    :header-key="tableHeader.field+'_formula'"
                    :can-edit="canCellEdit"
                    :uuid="uuid"
                    :pop_width="'100%'"
                    @set-formula="updateRow"
            ></formula-helper>

            <tablda-colopicker
                    v-else-if="tableHeader.f_type === 'Color' && !tableHeader.ddl_id"
                    :init_color="tableRow[tableHeader.field]"
                    :can_edit="canCellEdit"
                    :saved_colors="$root.color_palette"
                    :init_menu="true"
                    :avail_null="true"
                    @set-color="updateColor"
            ></tablda-colopicker>

            <tablda-user-select
                    v-else-if="tableHeader.f_type === 'User'"
                    :edit_value="editValue"
                    :table_meta="tableMeta"
                    :can_empty="true"
                    :fixed_pos="reactive_provider.fixed_ddl_pos || !!tableHeader.is_floating"
                    :multiselect="$root.isMSEL(input_type)"
                    :extra_vals="is_td_single || reactive_provider.is_def_fields"
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
                    :hdr_field="tableHeader.field"
                    :fld_input_type="input_type"
                    :has_embed_func="tableHeader.ddl_add_option == 1 && !no_ddl_colls"
                    :style="getEditStyle"
                    :fixed_pos="reactive_provider.fixed_ddl_pos"
                    :abstract_values="is_td_single"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
                    @embed-func="showAddDDLOption"
            ></tablda-select-ddl>

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
                   @blur="hideDatePicker()"
                   class="form-control full-height"
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
    import {UnitConversion} from './../../classes/UnitConversion';
    import {SelectedCells} from './../../classes/SelectedCells';
    import {SpecialFuncs} from './../../classes/SpecialFuncs';

    import {eventBus} from './../../app';

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

    export default {
        name: "CustomCellTableData",
        mixins: [
            CanEditMixin,
            Select2DDLMixin,
            CellMoveKeyHandlerMixin,
            CellStyleMixin,
        ],
        components: {
            CarouselBlock, VoteElement,
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
        inject: {
            reactive_provider: {
                from: 'reactive_provider',
                default: () => { return {} }
            }
        },
        data: function () {
            return {
                cont_height: 0,
                cont_width: 0,
                cont_html: null,

                overImages: null,
                overImageIdx: null,
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

                iframe_tb_app: null,
                iframe_app_link: null,
                attach_overed: false,
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
            image_carousel() {
                return this.behavior === 'kanban_view' && this.tableMeta.kanban_picture_style === 'slide';
            },
            textAlign() {
                switch (this.tableHeader.col_align) {
                    case 'left' : return 'txt--left';
                    case 'right' : return 'txt--right';
                    default : return 'txt--center';
                }
            },
            attachImages() {
                return this.tableRow['_images_for_'+this.tableHeader.field] && this.tableRow['_images_for_'+this.tableHeader.field].length
                    ? this.tableRow['_images_for_'+this.tableHeader.field]
                    : null;
            },
            attachFiles() {
                return this.tableRow['_files_for_'+this.tableHeader.field] && this.tableRow['_files_for_'+this.tableHeader.field].length
                    ? this.tableRow['_files_for_'+this.tableHeader.field]
                    : null;
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

            //OTHER/
            underlinedLink() {
                return _.find(this.tableHeader._links, {icon: 'Underlined'});
            },
            canCellEdit() {
                let res = this.with_edit
                    && !this.inArray(this.tableHeader.f_type, ['Attachment']) //special rules and editing in another place for 'Attachments'
                    && ( //edit permissions forced
                        this.force_edit
                        || //can edit only owner OR user with available rights
                        this.canEditCell(this.tableHeader, this.tableRow)
                        || // OR user can add rows and cell is from new row
                        this.isAddRow
                    )
                    && !this.inArray(this.tableHeader.field, this.$root.systemFields) //cannot edit system fields
                    && !this.freezed_by_format //cannot edit cells freezed by CondFormat
                    && !this.hidden_by_format //cannot edit cells hidden by CondFormat
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
            showEdit() {
                if (window.event.button != 0 && this.selectedCell) {
                    this.selectedCell.single_select(this.tableHeader, this.rowIndex);
                    return;
                }
                if (window.event.ctrlKey && this.selectedCell) {
                    this.selectedCell.square_select(this.tableHeader, this.rowIndex);
                    return;
                }
                //focus on cell (only on desctop)
                if (
                    window.screen.width >= 768
                    && this.selectedCell
                    && !this.selectedCell.disabled
                    && !this.selectedCell.is_selected(this.tableMeta, this.tableHeader, this.rowIndex)
                ) {
                    this.selectedCell.single_select(this.tableHeader, this.rowIndex);
                } else {
                    let listing_pop_edit = this.isVertTable && this.inArray(this.input_type, this.$root.ddlInputTypes) && this.tableHeader.ddl_id && this.tableHeader.ddl_style === 'panel';
                    let notedit_type = this.inArray(this.tableHeader.f_type, ['Boolean','Star Rating','Progress Bar']);
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
            updateCheckedDDL(item) {
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
                let val = (!isNaN(this.editValue) ? parseFloat(this.editValue) : this.editValue);
                this.sendUpdateSignal(val ? 0 : 1);
            },
            fromCompoSet(val) {
                this.editValue = val;
                this.updateValue();
            },
            updateValue(ediVal) {
                let editVal = SpecialFuncs.applySetMutator(this.tableHeader, ediVal || this.editValue);
                if (to_standard_val(this.tableRow[this.tableHeader.field]) !== to_standard_val(editVal)) {
                    this.sendUpdateSignal(editVal);
                }
            },
            updateColor(clr, save) {
                if (save) {
                    this.$root.color_palette.unshift(clr);
                    localStorage.setItem('color_palette', this.$root.color_palette.join(','));
                }
                this.sendUpdateSignal(clr);
            },
            sendUpdateSignal(editVal) {
                this.tableRow[this.tableHeader.field] = editVal;
                this.tableRow._changed_field = this.tableHeader.field;
                if (this.tableHeader.input_type === 'Formula') {
                    this.tableRow[this.tableHeader.field+'_formula'] = editVal;
                }
                this.$emit('updated-cell', this.tableRow, this.tableHeader);
            },
            updateRow() {
                this.editing = false;
                this.globEditingFalse();
                this.$emit('updated-cell', this.tableRow, this.tableHeader);
            },
            hideDatePicker() {
                this.hideEdit();
                this.updateValue( $(this.$refs.inline_input).val() );
            },

            //FILES
            deleteFile(files, idx, tableHeader) {
                Swal({
                    title: 'File deleted cannot be recovered!',
                    text: 'Are you sure?',
                    showCancelButton: true,
                }).then((result) => {
                    if (result.value) {
                        let file = files[idx];
                        this.$root.sm_msg_type = 1;
                        axios.delete('/ajax/files', {
                            params: {
                                id: file.id,
                                table_id: file.table_id,
                                table_field_id: file.table_field_id,
                                row_id: this.tableRow.id,
                            }
                        }).then(({ data }) => {
                            files.splice(idx, 1);
                            this.tableRow[tableHeader.field] = '';
                        }).catch(errors => {
                            Swal('', getErrors(errors));
                        }).finally(() => {
                            this.$root.sm_msg_type = 0;
                        });
                    }
                });
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
                let container = $(this.$refs.cell_tb_data);
                if (this.editing && container.has(e.target).length === 0) {
                    this.hideEdit();
                }
            },
            //drag&drop to the cell directly
            drEnter(e) {
                this.attach_overed = true;
            },
            drLeave(e) {
                if ($(this.$refs.cell_tb_data).has(e.fromElement).length === 0) {
                    this.attach_overed = false;
                }
            },
            attachDrop(ev) {
                if (this.tableHeader.f_type === 'Attachment') {
                    let file = ev.dataTransfer.items && ev.dataTransfer.items[0] && ev.dataTransfer.items[0].kind === 'file'
                        ? ev.dataTransfer.items[0].getAsFile()
                        : null;

                    let data = new FormData();
                    data.append('table_id', this.tableMeta.id);
                    data.append('table_field_id', this.tableHeader.id);
                    data.append('row_id', this.tableRow.id);
                    data.append('file', file);

                    axios.post('/ajax/files', data, {
                        headers: {
                            'Content-Type': 'multipart/form-data'
                        },
                    }).then(({ data }) => {
                        if (data.filepath && data.filename) {
                            this.sendUpdateSignal(data.filepath + data.filename);
                        }
                    });
                }
                this.attach_overed = false;
            },
            dwnFile(file) {
                if (window.event.ctrlKey) {
                    window.event.preventDefault();
                    window.location = this.dwnPath(file)+'?dwn=true';
                }
            },
            dwnPath(file) {
                return this.$root.fileUrl(file);
            },
            imgClick(images, idx) {
                if (!window.event.ctrlKey) {
                    this.overImages = images;
                    this.overImageIdx = idx;
                    window.event.stopPropagation();
                    window.event.preventDefault();
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

<style lang="scss" scoped="">
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
</style>