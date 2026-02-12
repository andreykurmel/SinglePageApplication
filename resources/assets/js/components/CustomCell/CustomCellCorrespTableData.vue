<template>
    <td :style="getCellStyle()"
        :class="{'top_zi': isEditing()}"
        class="td-custom"
        ref="td"
        @click="showEdit()"
    >
        <single-td-field
            v-if="tableHeader.field === 'stimvis_value' && stimvisTable && stimvisField"
            :table-meta="stimvisTable"
            :table-header="stimvisField"
            :td-value="editValue"
            :with_edit="with_edit"
            :force_edit="true"
            :style="{width: '100%'}"
            @updated-td-val="updateCheckedDDL"
        ></single-td-field>

        <div v-else class="td-wrapper" :style="getTdWrappStyle()">

            <div class="wrapper-inner" :style="getWrapperStyle()">
                <div class="inner-content" :style="{textAlign: no_align ? 'left' : tableHeader.col_align}">

                    <span v-if="hidden_by_format" class="by_format_hidden"></span>

                    <label class="switch_t"
                           v-else-if="tableHeader.f_type === 'Boolean'"
                           :style="{height: Math.min(maxCellHGT, 17)+'px'}">
                        <input type="checkbox" :disabled="!canEdit" :checked="checkBoxOn" @change.prevent="updateCheckBox()">
                        <span class="toggler round" :class="[!canEdit ? 'disabled' : '']"></span>
                    </label>

                    <span v-else-if="tableHeader.f_type === 'Boolean'" class="indeterm_check__wrap checkbox-input">
                        <span class="indeterm_check"
                              :class="{'disabled': !canEdit}"
                              ref="inline_input"
                              @click.prevent="!canEdit ? null : updateCheckBox()"
                        >
                            <i v-if="checkBoxOn" class="glyphicon glyphicon-ok group__icon"></i>
                        </span>
                    </span>

                    <cell-table-sys-content
                            v-else=""
                            :table-meta="tableMeta"
                            :table-row="tableRow"
                            :table-header="tableHeader"
                            :edit-value="editValue"
                            :user="user"
                            :behavior="behavior"
                            @unselect-val="updateCheckedDDL"
                            @show-src-record="showSrcRecord"
                    ></cell-table-sys-content>

                </div>
            </div>

        </div>



        <!-- ABSOLUTE EDITINGS -->
        <div v-if="isEditing()" class="cell-editing">

            <tablda-user-select
                v-if="tableHeader.f_type === 'User' && tableMeta.db_name === 'correspondence_stim_3d'"
                :edit_value="editValue"
                :table_field="tableHeader"
                :can_empty="true"
                :fixed_pos="true"
                :multiselect="$root.isMSEL(input_type)"
                :style="getEditStyle"
                :extra_vals="['visitor']"
                @selected-item="updateCheckedDDL"
                @hide-select="hideEdit"
            ></tablda-user-select>

            <tablda-select-simple
                v-else-if="tableHeader.field === 'data_field'"
                :options="not_present_corr_fields"
                :refilter_options="refilter_options"
                :table-row="tableRow"
                :hdr_field="tableHeader.field"
                :can_empty="true"
                :fixed_pos="true"
                :style="getEditStyle"
                @selected-item="updateCheckedDDL"
                @hide-select="hideEdit"
            ></tablda-select-simple>

            <select
                    v-else-if="tableHeader.f_type === 'User'"
                    v-model="tableRow[tableHeader.field]"
                    @blur="hideEdit()"
                    @change="updateValue()"
                    ref="inline_input"
                    class="form-control full-height"
                    :style="getEditStyle"
            >
                <option :value="tableRow[tableHeader.field]" selected="selected">{{ editValue }}</option>
            </select>

            <!--APP CORRESPONDENCES-->
            <select-with-folder-structure
                    v-else-if="tableHeader.f_type === 'RefTable'"
                    :for_single_select="true"
                    :empty_val="true"
                    :cur_val="editValue"
                    :available_tables="getAvailTables()"
                    :user="user"
                    :is_obj_attr="tableHeader.field == 'stimvis_table_id' ? 'id' : 'db_name'"
                    :only_item="tableHeader.field == 'stimvis_table_id' ? 'public' : ''"
                    @sel-changed="(val) => {this.editValue = val;}"
                    @sel-closed="hideEdit();updateValue();"
                    class="form-control full-height"
                    :style="getEditStyle">
            </select-with-folder-structure>

            <tablda-select-simple
                v-else-if="tableHeader.f_type === 'RefField'"
                :options="nameFields()"
                :table-row="tableRow"
                :hdr_field="tableHeader.field"
                :fixed_pos="true"
                :style="getEditStyle"
                @selected-item="updateCheckedDDL"
                @hide-select="hideEdit"
            ></tablda-select-simple>

            <tablda-select-simple
                    v-else-if="tableHeader.field === 'link_field_db'"
                    :options="linkFields()"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :can_empty="true"
                    :fixed_pos="true"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
            ></tablda-select-simple>

            <tablda-select-simple
                    v-else-if="['correspondence_app_id', 'on_change_app_id'].indexOf(tableHeader.field) > -1"
                    :options="corrApps()"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :can_empty="true"
                    :fixed_pos="true"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
            ></tablda-select-simple>

            <tablda-select-simple
                    v-else-if="tableHeader.field === 'correspondence_table_id'"
                    :options="corrTables(false)"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :can_empty="true"
                    :fixed_pos="true"
                    :allowed_search="true"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
            ></tablda-select-simple>

            <!--STIM 3D-->
            <tablda-select-simple
                v-else-if="tableHeader.field === 'db_table'"
                :options="corrTables(true)"
                :table-row="tableRow"
                :hdr_field="tableHeader.field"
                :can_empty="true"
                :fixed_pos="true"
                :style="getEditStyle"
                @selected-item="updateCheckedDDL"
                @hide-select="hideEdit"
            ></tablda-select-simple>

            <tablda-select-simple
                    v-else-if="tableHeader.field === 'style'"
                    :options="styleVariants"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :can_empty="true"
                    :fixed_pos="true"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
            ></tablda-select-simple>

            <tablda-select-simple
                    v-else-if="tableHeader.field === 'model_3d'"
                    :options="model3dVariants"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :can_empty="true"
                    :fixed_pos="true"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
            ></tablda-select-simple>

            <tablda-select-simple
                    v-else-if="tableHeader.field === 'type_tablda'"
                    :options="tabldaTypeVariants"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :can_empty="true"
                    :fixed_pos="true"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
            ></tablda-select-simple>

            <tablda-select-simple
                    v-else-if="tableHeader.field === 'stimvis_status'"
                    :options="stimvisStatus"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :can_empty="true"
                    :fixed_pos="true"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
            ></tablda-select-simple>

            <tablda-select-simple
                    v-else-if="tableHeader.field === 'stimvis_operator'"
                    :options="stimvisOperator"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :can_empty="true"
                    :fixed_pos="true"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
            ></tablda-select-simple>
            <!--STIM 3D-->
            <!--APP CORRESPONDENCES-->

            <tablda-select-ddl
                    v-else-if="inArray(input_type, $root.ddlInputTypes)
                            && tableHeader.ddl_id
                            && tableHeader.ddl_style === 'ddl'"
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
                v-else-if="inArray(tableHeader.field, ['row_order'])"
                type="number"
                v-model="editValue"
                @blur="hideEdit();updateValue()"
                ref="inline_input"
                class="form-control full-height"
                :style="getEditStyle"/>

            <textarea
                    v-else-if="tableHeader.f_type === 'String'"
                    v-model="editValue"
                    @blur="hideEdit();updateValue()"
                    @resize="hideEdit();updateValue()"
                    ref="inline_input"
                    class="form-control full-height"
                    :style="getEditStyle"
            ></textarea>

            <input
                    v-else-if="inArray(tableHeader.f_type, ['Integer', 'Decimal', 'Currency', 'Percentage'])"
                    type="text"
                    v-model="editValue"
                    @blur="hideEdit();updateValue()"
                    ref="inline_input"
                    class="form-control full-height"
                    :style="getEditStyle"/>

            <div v-else="">{{ hideEdit() }}</div>

        </div>
        <!-- ABSOLUTE EDITINGS -->

    </td>
</template>

<script>
import {SpecialFuncs} from './../../classes/SpecialFuncs';
import {SelectedCells} from './../../classes/SelectedCells';

import {eventBus} from './../../app';

import CanEditMixin from '../_Mixins/CanViewEditMixin';
import Select2DDLMixin from './../_Mixins/Select2DDLMixin';
import CellMoveKeyHandlerMixin from './../_Mixins/CellMoveKeyHandlerMixin';
import CellStyleMixin from '../_Mixins/CellStyleMixin.vue';

import SelectWithFolderStructure from './InCell/SelectWithFolderStructure';
import TabldaSelectSimple from "./Selects/TabldaSelectSimple";
import TabldaSelectDdl from "./Selects/TabldaSelectDdl";
import CellTableSysContent from "./InCell/CellTableSysContent";
import TabldaUserSelect from "./Selects/TabldaUserSelect";

export default {
        name: "CustomCellCorrespTableData",
        mixins: [
            CanEditMixin,
            Select2DDLMixin,
            CellMoveKeyHandlerMixin,
            CellStyleMixin,
        ],
        components: {
            TabldaUserSelect,
            CellTableSysContent,
            TabldaSelectDdl,
            TabldaSelectSimple,
            SelectWithFolderStructure,
        },
        data: function () {
            return {
                cont_height: 0,
                cont_width: 0,
                cont_html: null,

                overImage: null,
                no_key_handler: false,
                editing: false,
                editValue: null,
                source_field_for_values: null,
                open_color_picker: false,
                clrs: {
                    hex: this.tableRow[this.tableHeader.field]
                },
                show_field_res: null,
                uuid: uuidv4(),
                not_present_corr_fields: [],
                refilter_options: 0,

                //STIM 3D
                styleVariants: [
                    { val: 'vh_tabs', show: 'VH Tabs', },
                    { val: 'accordion', show: 'Accordion', },
                ],
                model3dVariants: [
                    { val: '3d:ma', show: '3D:MA', },
                    { val: '3d:structure', show: '3D:Structure', },
                    { val: '3d:equipment', show: '3D:Equipment', },
                    { val: '2d:canvas', show: '2D:Canvas', },
                ],
                tabldaTypeVariants: [
                    { val: 'vertical', show: 'Vertical Table (Form)', },
                    { val: 'table', show: 'Table', },
                    { val: 'attachment', show: '`Attachment` Fields', },
                    { val: 'chart', show: 'BI Chart', },
                    { val: 'map', show: 'GSI', },
                    { val: 'configurator', show: 'Configurator', },
                ],
                stimvisStatus: [
                    { val: 'Show', show: 'Show', },
                    { val: 'Hide', show: 'Hide', },
                ],
                stimvisOperator: [
                    { val: '=', show: '=', },
                    { val: '!=', show: '!=', },
                    { val: '>', show: '>', },
                    { val: '<', show: '<', },
                ],
            }
        },
        props:{
            tableMeta: Object,
            tableHeader: Object,
            tableRow: Object,
            allRows: Object|null,
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
            table_id: Number,
            behavior: String,
            isAddRow: Boolean,
            no_ddl_colls: Array,
            isVertTable: Boolean,
            hasFloatColumns: Boolean,
            no_align: Boolean,
            isSelectedExt: Boolean,
            extraPivotFields: Array,
        },
        watch: {
            table_id: function (val) {
                this.editing = false;
            },
            cellValue(val) {
                this.setEditVal(val);
            },
        },
        computed: {
            input_type() {
                //special for 'Add New Option' Popup
                let input_type = this.tableHeader.input_type;
                if (this.inArray(this.tableHeader.field, this.no_ddl_colls) && this.inArray(input_type, this.$root.ddlInputTypes)) {
                    input_type = 'Input';
                }
                return input_type;
            },

            //CORRESPONDENCE
            correspondence_table_fields() {
                if (this.tableMeta.db_name === 'correspondence_fields' && this.tableHeader.field === 'data_field') {
                    let db_name = this.tableRow._table ? this.tableRow._table.data_table : this.tableRow.correspondence_table_id;
                    let atable = _.find(this.$root.settingsMeta.available_tables, {db_name: db_name});
                    return atable ? atable._fields : [];
                } else {
                    return [];
                }
            },
            correspondence_apps() {
                if (['correspondence_app_id', 'on_change_app_id'].indexOf(this.tableHeader.field) > -1) {
                    let res = [];
                    _.each(this.$root.settingsMeta.table_apps_data, (row) => {
                        res.push({
                            id: row.id,
                            name: row.name,
                            user_id: row.user_id,
                        });
                    });
                    return res;
                } else {
                    return [];
                }
            },
            correspondence_tables() {
                if (['correspondence_table_id','db_table'].indexOf(this.tableHeader.field) > -1) {
                    let res = [];
                    let app = _.find(this.$root.settingsMeta.table_apps_data, {code: 'stim_3d'});
                    if (app) {
                        _.each(app._tables, (tb) => {
                            res.push({
                                id: tb.id,
                                app_table: tb.app_table,
                                data_table: tb.data_table,
                            });
                        });
                    }
                    return res;
                } else {
                    return [];
                }
            },

            //OTHER
            selectParam() {
                return {
                    minimumResultsForSearch: Infinity,
                    minimumInputLength: 0,
                    width: '100%',
                    dropdownAutoWidth: true,
                    closeOnSelect: true
                }
            },
            checkBoxOn() {
                return Number(this.tableRow[this.tableHeader.field]);
            },
            canEdit() {
                if (this.tableMeta.db_name === 'correspondence_stim_3d' && this.tableHeader.field === 'row_order') {
                    return true;
                }

                let style_stim_3d = this.tableRow.style === 'accordion' ? ['horizontal','vertical'] : ['accordion'];
                let res = this.with_edit
                    && ( //can edit only owner OR user with available rights
                        this.canEditCell(this.tableHeader, this.tableRow)
                        || // OR user can add rows AND cell is from new row
                        (this.isAddRow && this.tableMeta._current_right.can_add)
                    )
                    && !this.inArray(this.tableHeader.field, this.$root.systemFields) //cannot edit system fields
                    && !this.freezed_by_format //cannot edit cells freezed by CondFormat
                    && !this.hidden_by_format //cannot edit cells hiden by CondFormat
                    && (
                        !this.tableMeta.is_system // PERMISSIONS FOR EACH SYSTEM TABLE --->>>
                        || (this.tableMeta.db_name === 'correspondence_apps')
                        || (this.tableMeta.db_name === 'correspondence_tables' && !this.inArray(this.tableHeader.field, ['user_id']))
                        || (this.tableMeta.db_name === 'correspondence_fields' && !this.inArray(this.tableHeader.field, ['user_id','_data_table_id']))
                        || (this.tableMeta.db_name === 'correspondence_stim_3d' && !this.inArray(this.tableHeader.field, style_stim_3d))
                    );

                return Boolean(res);
            },
            isSelected() {
                return this.isSelectedExt;
            },

            stimvisTable() {
                return this.tableRow.stimvis_table_id
                    ? _.find(this.$root.settingsMeta.available_tables, {id: Number(this.tableRow.stimvis_table_id)})
                    : null;
            },
            stimvisField() {
                return this.stimvisTable && this.tableRow.stimvis_field_id
                    ? _.find(this.stimvisTable._fields || [], {id: Number(this.tableRow.stimvis_field_id)})
                    : null;
            },
        },
        methods: {
            getAvailTables() {
                if (this.tableHeader.field == 'stimvis_table_id') {
                    let corrTBS = _.filter(this.$root.settingsMeta.corr_uis || [], (tb) => {
                        return tb.top_tab == this.tableRow.top_tab && tb.select == this.tableRow.select;
                    });
                    corrTBS = _.map(corrTBS, 'db_table');

                    let app = _.find(this.$root.settingsMeta.table_apps_data, {code: 'stim_3d'}) || {};
                    let appTables = _.filter(app._tables || [], (tb) => corrTBS.indexOf(tb.app_table) > -1);
                    appTables = _.map(appTables, 'data_table');

                    return _.filter(this.$root.settingsMeta.available_tables, (tb) => {
                        return this.inArray(tb.db_name, appTables);
                    });
                }

                return this.$root.settingsMeta.available_tables;
            },
            nameFields() {
                let tbCol = _.find(this.tableMeta._fields, {f_type: 'RefTable'}) || {};
                let tb = _.find(this.$root.settingsMeta.available_tables, {id: Number(this.tableRow[tbCol.field])}) || {};
                return _.map(tb._fields || [], (hdr) => {
                    return { val: hdr.id, show: this.$root.uniqName(hdr.name), }
                });
            },
            setEditVal(val) {
                this.editValue = this.unitConvert(val);
            },
            //EDITING
            inArray(item, array) {
                return $.inArray(item, array) > -1;
            },
            isEditing() {
                return this.editing && this.canEdit && !this.$root.global_no_edit;
            },
            showEdit() {
                //focus on cell
                if (
                    window.innerWidth >= 768
                    && this.selectedCell
                    && !this.selectedCell.is_selected(this.tableMeta, this.tableHeader, this.rowIndex)
                ) {
                    this.selectedCell.single_select(this.tableHeader, this.rowIndex);
                } else {
                    if (this.inArray(this.tableHeader.f_type, ['Color','Boolean','Rating','Progress Bar']) || this.$root.prevent_cell_edit) {
                        return;
                    }
                    //edit cell
                    this.editing = true;
                    if (this.isEditing()) {

                        this.$nextTick(function () {
                            if (this.inArray(this.tableHeader.f_type, ['Date Time', 'Date'])) {
                                this.showHideDatePicker();
                            }
                            if (this.tableHeader.field === 'data_field') {
                                this.corrFields();
                            }

                            if (this.tableHeader.f_type === 'User') {
                                this.showHideUserPicker();
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

                    } else {
                        this.editing = false;
                    }
                }
            },
            hideEdit() {
                this.no_key_handler = true;
                this.editing = false;
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
            showAddDDLOption() {
                this.$emit('show-add-ddl-option', this.tableHeader, this.tableRow);
            },
            updateCheckBox() {
                this.tableRow[this.tableHeader.field] = !Number(this.tableRow[this.tableHeader.field]);
                this.$emit('updated-cell', this.tableRow, this.tableHeader);
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
            updateValue() {
                let editVal = SpecialFuncs.applySetMutator(this.tableHeader, this.editValue);
                this.CorrTablesSetter(editVal);
                if (to_standard_val(this.tableRow[this.tableHeader.field]) !== to_standard_val(editVal)) {
                    this.sendUpdateSignal(editVal);
                }
            },
            CorrTablesSetter(editVal) {
                if (this.tableHeader.field === 'link_table_db') {
                    this.tableRow.link_field_db = '';
                }
                if (this.tableHeader.field === 'accordion') {
                    this.tableRow.horizontal = '';
                    this.tableRow.vertical = '';
                }
                if (this.tableHeader.field === 'horizontal') {
                    this.tableRow.accordion = '';
                }
                if (this.tableHeader.field === 'correspondence_app_id') {
                    this.tableRow._app = _.find(this.correspondence_apps, {id: editVal});
                }
                if (this.tableHeader.field === 'correspondence_table_id') {
                    this.tableRow._table = _.find(this.correspondence_tables, {id: editVal});
                    this.tableRow._table_data_id = this.tableRow._table ? this.tableRow._table.data_table : null;
                }
            },
            sendUpdateSignal(editVal) {
                this.tableRow[this.tableHeader.field] = editVal;
                this.$emit('updated-cell', this.tableRow, this.tableHeader);
            },

            //select arrays
            corrApps() {
                let fields = _.filter(this.correspondence_apps, (corr_app) => {
                    return this.$root.user.is_admin || corr_app.user_id == this.$root.user.id;
                });
                return _.map(fields, (corr_app) => {
                    return { val: corr_app.id, show: corr_app.name, }
                });
            },
            corrTables(no_id) {
                return _.map(this.correspondence_tables, (corr_tb) => {
                    return {
                        val: no_id ? corr_tb.app_table : corr_tb.id,
                        show: corr_tb.app_table,
                    }
                });
            },
            corrFields() {
                axios.post('/ajax/correspondence-used-fields', {
                    row: this.tableRow,
                }).then(({ data }) => {
                    let fields = _.filter(this.correspondence_table_fields, (fld) => {
                        return (data.rows.indexOf(fld.field) === -1)
                            &&
                            (fld.field == 'id' || !this.inArray(fld.field, this.$root.systemFields));
                    });
                    this.not_present_corr_fields = _.map(fields, (fld) => {
                        return { val: fld.field, show: this.$root.uniqName(fld.name), }
                    });
                    this.refilter_options++;
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                });
            },
            linkFields() {
                let atable = _.find(this.$root.settingsMeta.available_tables, {db_name: this.tableRow.link_table_db});
                let fields = _.filter(atable ? atable._fields : [], (fld) => {
                    return (fld.field == 'id' || !this.inArray(fld.field, this.$root.systemFields));
                });
                return _.map(fields, (fld) => {
                    return { val: fld.field, show: this.$root.uniqName(fld.name), }
                });
            },
            //show link popup
            showSrcRecord(link, header, tableRow) {
                this.$emit('show-src-record', link, header, tableRow);
            },
        },
        mounted() {
            eventBus.$on('global-keydown', this.globalKeydownHandler);

            this.setEditVal(this.cellValue);
        },
        beforeDestroy() {
            eventBus.$off('global-keydown', this.globalKeydownHandler);
        }
    }
</script>

<style lang="scss">
    @import "CustomCell";

    .top_zi {
        z-index: 250 !important;
    }
</style>