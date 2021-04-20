<template>
    <td :style="getCustomCellStyle"
        class="td-custom"
        ref="cell_tb_data"
        @click="showEdit()"
    >
        <div class="td-wrapper" :style="getTdWrappStyle">

            <div class="wrapper-inner" :style="getWrapperStyle">
                <div class="inner-content" :style="{textAlign: no_align ? 'left' : tableHeader.col_align}">

                    <span v-if="hidden_by_format"></span>

                    <div v-else-if="tableMeta.db_name === 'sum_usages' && tableHeader.field === 'table_id'" class="inner-content">
                        <a target="_blank" :href="showTable('link')" @click.stop="">{{ showTable() }}</a>
                    </div>

                    <cell-table-sys-content
                            v-else-if="spec_fld"
                            :table-meta="tableMeta"
                            :table-row="tableRow"
                            :table-header="tableHeader"
                            :edit-value="editValue"
                            :user="user"
                            @unselect-val="updateCheckedDDL"
                    ></cell-table-sys-content>

                    <label class="switch_t"
                           v-else-if="tableHeader.f_type === 'Boolean'"
                           :style="{height: Math.min(maxCellHGT, 17)+'px'}">
                        <input type="checkbox" :disabled="!canEdit" :checked="checkBoxOn" @change.prevent="updateCheckBox()">
                        <span class="toggler round" :class="[!canEdit ? 'disabled' : '']"></span>
                    </label>

                    <span v-else-if="tableMeta.db_name === 'plan_features'
                            && inArray(tableHeader.field, ['add_bi','add_map','add_request','add_alert','add_kanban','add_gantt','add_email','add_calendar','recurrent_pay'])"
                          class="indeterm_check__wrap checkbox-input"
                    >
                        <span class="indeterm_check"
                              :class="{'disabled': !canEdit || tableRow.plan_id === 'basic'}"
                              ref="inline_input"
                              @click.prevent="!canEdit || tableRow.plan_id === 'basic' ? null : updateCheckBox()"
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
                            @unselect-val="updateCheckedDDL"
                    ></cell-table-sys-content>

                </div>
            </div>

        </div>



        <!-- ABSOLUTE EDITINGS -->
        <div v-if="isEditing()" class="cell-editing">

            <formula-helper
                    v-if="tableHeader.input_type === 'Formula'"
                    :user="user"
                    :table-meta="tableMeta"
                    :table-row="tableRow"
                    :table-header="tableHeader"
                    :header-key="tableHeader.field"
                    :can-edit="true"
                    :no-function="true"
                    :uuid="uuid"
                    :pop_width="'100%'"
                    @set-formula="updateRow"
            ></formula-helper>

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

            <input
                    v-else-if="spec_fld"
                    v-model="editValue"
                    @blur="hideEdit();updateValue()"
                    ref="inline_input"
                    class="form-control full-height"
                    :style="getEditStyle"
            />

            <tablda-select-simple
                    v-else-if="tableHeader.field === 'plan_id'"
                    :options="allPlans()"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :fixed_pos="reactive_provider.fixed_ddl_pos"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
            ></tablda-select-simple>

            <tablda-select-ddl
                    v-else-if="inArray(input_type, $root.ddlInputTypes)
                            && tableHeader.ddl_id
                            && tableHeader.ddl_style === 'ddl'"
                    :ddl_id="tableHeader.ddl_id"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :fld_input_type="input_type"
                    :has_embed_func="tableHeader.ddl_add_option == 1 && !no_ddl_colls"
                    :style="getEditStyle"
                    :fixed_pos="reactive_provider.fixed_ddl_pos"
                    :abstract_values="ddl_abstract_values"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
                    @embed-func="showAddDDLOption"
            ></tablda-select-ddl>

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

    import TabldaSelectSimple from "./Selects/TabldaSelectSimple";
    import TabldaSelectDdl from "./Selects/TabldaSelectDdl";
    import CellTableSysContent from "./InCell/CellTableSysContent";
    import FormulaHelper from "./InCell/FormulaHelper";

    export default {
        name: "CustomCellSystemTableData",
        mixins: [
            CanEditMixin,
            Select2DDLMixin,
            CellMoveKeyHandlerMixin,
            CellStyleMixin,
        ],
        components: {
            FormulaHelper,
            CellTableSysContent,
            TabldaSelectDdl,
            TabldaSelectSimple,
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
                cellHeight: 1,
            }
        },
        props:{
            tableMeta: Object,
            tableHeader: Object,
            tableRow: Object,
            rowIndex: Number,
            cellValue: String|Number,
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
            force_edit: Boolean,
            no_ddl_colls: Array,
            isVertTable: Boolean,
            hasFloatColumns: Boolean,
            no_align: Boolean,
            ddl_abstract_values: Boolean,
            isSelected: Boolean,
        },
        watch: {
            table_id: function (val) {
                this.editing = false;
            },
            cellValue(val) {
                this.editValue = this.unitConvert(val);
            },
        },
        computed: {
            getCustomCellStyle() {
                let obj = this.getCellStyle;
                if (this.uc_disabled) {
                    obj.backgroundColor = '#EEE';
                }
                return obj;
            },
            spec_fld() {
                return this.tableMeta.db_name === 'plans_view'
                    && this.inArray(this.tableHeader.field, ['plan_basic','plan_advanced','plan_enterprise'])
                    && this.inArray(this.tableRow.code, ['q_tables','row_table']);
            },
            input_type() {
                //special for 'Add New Option' Popup
                let input_type = this.tableHeader.input_type;
                if (this.inArray(this.tableHeader.field, this.no_ddl_colls) && this.inArray(input_type, this.$root.ddlInputTypes)) {
                    input_type = 'Input';
                }
                return input_type;
            },
            uc_disabled() {
                return (this.tableMeta.db_name === 'unit_conversion' && this.tableRow.operator !== 'Formula' && this.inArray(this.tableHeader.field, ['formula','formula_reverse']))
                    || (this.tableMeta.db_name === 'unit_conversion' && this.tableRow.operator === 'Formula' && this.inArray(this.tableHeader.field, ['factor']));
            },

            //OTHER
            checkBoxOn() {
                return Number(this.tableRow[this.tableHeader.field]);
            },
            canEdit() {
                let res = this.with_edit
                    && ( //edit permissions forced
                        this.force_edit
                        || //can edit only owner OR user with available rights
                        this.canEditCell(this.tableHeader, this.tableRow)
                        || // OR user can add rows AND cell is from new row
                        (this.isAddRow && this.tableMeta._current_right.can_add)
                    )
                    && !this.inArray(this.tableHeader.field, this.$root.systemFields) //cannot edit system fields
                    && !this.freezed_by_format //cannot edit cells freezed by CondFormat
                    && !this.hidden_by_format //cannot edit cells hidden by CondFormat
                    && !(this.behavior === 'request_view' && this.tableRow.id) //if embed request -> can edit only newly added rows
                    && (
                        !this.tableMeta.is_system // PERMISSIONS FOR EACH SYSTEM TABLE --->>>
                        || !this.tableMeta.db_name
                        || (this.tableMeta.db_name === 'fees')
                        || (this.tableMeta.db_name === 'unit_conversion' && !this.uc_disabled)
                        || (this.tableMeta.db_name === 'units')
                        || (this.tableMeta.db_name === 'user_clouds' && !this.inArray(this.tableHeader.field, ['user_id']))
                        || (this.tableMeta.db_name === 'user_connections' && !this.inArray(this.tableHeader.field, ['user_id']))
                        || (this.tableMeta.db_name === 'plans_view' && this.inArray(this.tableHeader.field, ['category1','category2','category3','feature','plan_basic','plan_advanced','plan_enterprise','desc']))
                        || (this.tableMeta.db_name === 'plan_features' && !this.inArray(this.tableHeader.field, ['object_id']))
                        || (this.tableMeta.db_name === 'payments' && this.inArray(this.tableHeader.field, ['notes']))
                        || (this.tableMeta.db_name === 'user_subscriptions' && this.inArray(this.tableHeader.field, ['avail_credit','notes']))
                        || (this.tableMeta.db_name === 'table_fields__for_tooltips' && this.inArray(this.tableHeader.field, ['tooltip','tooltip_show']))
                    );

                return Boolean(res);
            },
        },
        methods: {
            showTable(link) {
                let res = this.tableRow.table_id;
                let tb = _.find(this.$root.settingsMeta.available_tables, {id: Number(this.tableRow.table_id)});
                if (link) {
                    res = tb ? tb.__visiting_url : res;
                }
                return res;
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
                    window.screen.width >= 768
                    && this.selectedCell
                    && !this.selectedCell.is_selected(this.tableMeta, this.tableHeader, this.rowIndex)
                ) {
                    this.selectedCell.single_select(this.tableHeader, this.rowIndex);
                } else {
                    if (!this.spec_fld && this.inArray(this.tableHeader.f_type, ['Color','Boolean','Star Rating','Progress Bar'])) {
                        return;
                    }
                    //edit cell
                    this.editing = true;
                    if (this.isEditing()) {

                        this.$nextTick(function () {
                            if (this.inArray(this.tableHeader.f_type, ['Date Time', 'Date'])) {
                                this.showHideDatePicker();
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
                if (to_standard_val(this.tableRow[this.tableHeader.field]) !== to_standard_val(editVal)) {
                    this.sendUpdateSignal(editVal);
                }
            },
            updateRow() {
                this.editing = false;
                this.$emit('updated-cell', this.tableRow, this.tableHeader);
            },
            sendUpdateSignal(editVal) {
                this.tableRow[this.tableHeader.field] = editVal;
                this.$emit('updated-cell', this.tableRow, this.tableHeader);
            },

            //select arrays
            allPlans() {
                return _.map(this.$root.settingsMeta.all_plans, (plan) => {
                    return { val: plan.code, show: plan.name, }
                });
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
        },
        mounted() {
            eventBus.$on('global-keydown', this.globalKeydownHandler);

            this.editValue = this.unitConvert(this.cellValue);
        },
        beforeDestroy() {
            eventBus.$off('global-keydown', this.globalKeydownHandler);
        }
    }
</script>

<style lang="scss">
    @import "CustomCell";
</style>