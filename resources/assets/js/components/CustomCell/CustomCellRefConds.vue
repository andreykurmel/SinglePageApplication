<template>
    <td :style="getCustomCellStyle"
        class="td-custom"
        ref="td"
        @click="showEdit()"
    >
        <single-td-field
                v-if="tableHeader.field === 'compared_value' && tableRow.item_type === 'S2V' && refTbHeader"
                :table-meta="ref_tb_from_refcond"
                :table-header="refTbHeader"
                :td-value="tableRow.compared_value"
                :with_edit="true"
                :force_edit="true"
                :style="{width: '100%'}"
                @updated-td-val="updateSingle"
        ></single-td-field>

        <div v-else="" class="td-wrapper" :style="getTdWrappStyle">

            <div class="wrapper-inner" :style="getWrapperStyle">

                <div v-if="tableHeader.field === 'ref_table_id'" class="inner-content">
                    <a target="_blank" :href="showField('link')" @click.stop="">{{ showField() }}</a>
                </div>

                <div v-else="" class="inner-content">{{ showField() }}</div>
            </div>

        </div>



        <!-- ABSOLUTE EDITINGS -->
        <div v-if="isEditing()" class="cell-editing">

            <!--Reference Table for Ref Conditions-->
            <select-with-folder-structure
                    v-if="tableHeader.field === 'ref_table_id'"
                    :for_single_select="true"
                    :cur_val="tableRow[tableHeader.field]"
                    :available_tables="$root.settingsMeta.available_tables"
                    :user="user"
                    @sel-changed="(val) => {tableRow[tableHeader.field] = val;}"
                    @sel-closed="hideEdit();updateValue();"
                    class="form-control full-height"
                    :style="getEditStyle">
            </select-with-folder-structure>

            <!--Conditions Cells-->
            <tablda-select-simple
                    v-else-if="tableHeader.field === 'table_field_id'"
                    :options="nameFields()"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :fixed_pos="reactive_provider.fixed_ddl_pos"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
            ></tablda-select-simple>

            <tablda-select-simple
                    v-else-if="tableHeader.field === 'item_type'"
                    :options="[
                        {val: 'P2S', show: 'P2S'},
                        {val: 'S2V', show: 'S2V'},
                    ]"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :fixed_pos="reactive_provider.fixed_ddl_pos"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
            ></tablda-select-simple>

            <tablda-select-simple
                    v-else-if="inArray(tableHeader.field, ['logic_operator', 'group_logic'])"
                    :options="[
                        {val: 'AND', show: 'AND'},
                        {val: 'OR', show: 'OR'},
                    ]"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :fixed_pos="reactive_provider.fixed_ddl_pos"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
            ></tablda-select-simple>

            <tablda-select-simple
                    v-else-if="tableHeader.field === 'compared_operator'"
                    :options="[
                        {val: '<', show: '<'},
                        {val: '=', show: '='},
                        {val: '>', show: '>'},
                        {val: '!=', show: '!='},
                        {val: 'Include', show: 'Include'},
                    ]"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :fixed_pos="reactive_provider.fixed_ddl_pos"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
            ></tablda-select-simple>

            <tablda-select-simple
                    v-else-if="tableHeader.field === 'group_clause'"
                    :options="[
                        {val: 'A', show: 'A'},
                        {val: 'B', show: 'B'},
                        {val: 'C', show: 'C'},
                        {val: 'D', show: 'D'},
                        {val: 'E', show: 'E'},
                    ]"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :fixed_pos="reactive_provider.fixed_ddl_pos"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
            ></tablda-select-simple>

            <tablda-select-simple
                    v-else-if="tableHeader.field === 'compared_field_id'"
                    :options="refTbNameFields()"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :can_empty="true"
                    :fixed_pos="reactive_provider.fixed_ddl_pos"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
            ></tablda-select-simple>

            <!--<tablda-select-simple-->
                    <!--v-else-if="tableHeader.field === 'compared_value'-->
                        <!--&& inArray(tableRow.compared_operator, ['=', '!='])-->
                        <!--&& behavior !== 'sett_usergroup_conds'-->
                        <!--&& compared_values.length !== 0"-->
                    <!--:options="comparedVals()"-->
                    <!--:table-row="tableRow"-->
                    <!--:hdr_field="tableHeader.field"-->
                    <!--:can_empty="true"-->
                    <!--:fixed_pos="reactive_provider.fixed_ddl_pos"-->
                    <!--:style="getEditStyle"-->
                    <!--@selected-item="updateCheckedDDL"-->
                    <!--@hide-select="hideEdit"-->
            <!--&gt;</tablda-select-simple>-->

            <input
                    v-else-if="tableHeader.field === 'compared_value'"
                    v-model="tableRow[tableHeader.field]"
                    @blur="hideEdit();updateValue()"
                    ref="inline_input"
                    class="form-control full-height"
                    :style="getEditStyle">

            <!--Other Cells-->
            <input
                    v-else-if="inArray(tableHeader.field, ['name', 'notes'])"
                    v-model="tableRow[tableHeader.field]"
                    @blur="hideEdit();updateValue()"
                    ref="inline_input"
                    class="form-control full-height"
                    :style="getEditStyle">

            <div v-else="">{{ hideEdit() }}</div>
            
        </div>
        <!-- ABSOLUTE EDITINGS -->
        
    </td>
</template>

<script>
    import Select2DDLMixin from './../_Mixins/Select2DDLMixin.vue';
    import CellStyleMixin from '../_Mixins/CellStyleMixin.vue';

    import SelectWithFolderStructure from './InCell/SelectWithFolderStructure.vue';
    import TabldaSelectSimple from "./Selects/TabldaSelectSimple.vue";
    import SingleTdField from "../CommonBlocks/SingleTdField";

    export default {
        name: "CustomCellRefConds",
        mixins: [
            Select2DDLMixin,
            CellStyleMixin,
        ],
        components: {
            SingleTdField,
            TabldaSelectSimple,
            SelectWithFolderStructure,
        },
        inject: {
            reactive_provider: {
                from: 'reactive_provider',
                default: () => { return {} }
            }
        },
        data: function () {
            return {
                editing: false,
                compared_values: [],
                source_field_for_values: null
            }
        },
        computed: {
            getCustomCellStyle() {
                let obj = this.getCellStyle;
                let effect_fld = ['logic_operator','group_logic','compared_value','table_field_id'].indexOf(this.tableHeader.field) > -1;
                obj.backgroundColor = (this.canCellEdit || !effect_fld ? 'initial' : '#C7C7C7');
                obj.textAlignt = this.inArray(this.tableHeader.field, ['checked']) ? 'center' : '';
                return obj;
            },
            canCellEdit() {
                let prev_row = this.reactive_provider.allRows ? this.reactive_provider.allRows[this.rowIndex-1] : null;
                let can_logic_operator = prev_row && prev_row.group_clause === this.tableRow.group_clause;
                let can_group_logic = prev_row && prev_row.group_clause !== this.tableRow.group_clause;
                let can_details_value = this.tableRow.item_type === 'S2V';
                let can_details_presentfld = this.tableRow.item_type === 'P2S';

                return this.tableHeader.f_type !== 'Attachment'
                    && this.globalMeta && this.globalMeta._is_owner
                    && !this.inArray(this.tableHeader.field, this.$root.systemFields)
                    && (this.tableHeader.field !== 'logic_operator' || can_logic_operator)
                    && (this.tableHeader.field !== 'group_logic' || can_group_logic)
                    && (this.tableHeader.field !== 'compared_value' || can_details_value)
                    && (this.tableHeader.field !== 'table_field_id' || can_details_presentfld);
            },
            refTbHeader() {
                return _.find(this.ref_tb_from_refcond._fields, {id: Number(this.tableRow.compared_field_id)});
            },
        },
        props:{
            globalMeta: Object,
            tableMeta: Object,
            tableHeader: Object,
            tableRow: Object,
            rowsCount: Number,
            rowIndex: Number,
            cellHeight: Number,
            maxCellRows: Number,
            isAddRow: Boolean,
            behavior: String,
            user: Object,
            ref_tb_from_refcond: Object|null,
        },
        methods: {
            inArray(item, array) {
                return $.inArray(item, array) > -1;
            },
            isEditing: function () {
                return this.editing && this.canCellEdit && !this.$root.global_no_edit;
            },
            showEdit: function () {
                //edit cell
                if (this.canCellEdit) {
                    let field_id = (this.behavior === 'data_sets_ref_condition_items' ? this.tableRow.compared_field_id : this.tableRow.table_field_id);
                    if (this.tableHeader.field === 'compared_value' && this.tableRow.item_type === 'S2V' && this.refTbHeader) {
                        //this.getComparedValues();
                        this.editing = false;
                    }
                    else {
                        this.editing = true;
                        this.oldValue = this.tableRow[this.tableHeader.field];
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
                    }
                } else {
                    this.editing = false;
                }
            },
            hideEdit: function () {
                this.editing = false;
            },
            updateSingle(val) {
                this.tableRow.compared_value = val;
                this.$emit('updated-cell', this.tableRow);
            },
            updateValue() {
                if (this.tableRow[this.tableHeader.field] !== this.oldValue) {
                    this.tableRow._changed_field = this.tableHeader.field;
                    if (this.tableHeader.field === 'table_field_id') {
                        this.tableRow.compared_value = null;
                    }
                    this.$emit('updated-cell', this.tableRow);
                }
            },
            updateCheckedDDL(item, show) {
                this.tableRow[this.tableHeader.field] = item;
                if (show && this.tableHeader.field === 'compared_value') {
                    this.tableRow.spec_show = show;
                }
                this.updateValue();
            },
            showField(link) {
                let res = '';

                if (this.isAddRow && this.inArray(this.tableHeader.field, this.$root.systemFields)) {
                    res = 'Auto'
                }
                else
                if (this.tableHeader.f_type === 'User') {
                    res = this.$root.smallUserStr(this.tableRow, this.tableHeader, this.tableRow[this.tableHeader.field]);
                }
                else
                if (this.tableHeader.field === 'ref_table_id' && this.tableRow.ref_table_id) {
                    let tb = _.find(this.$root.settingsMeta.available_tables, {id: Number(this.tableRow.ref_table_id)});
                    if (tb) {
                        if (link) {
                            res = tb.__visiting_url;
                        } else {
                            res = (tb._referenced ? ('@' + tb._referenced + '/') : '') + tb.name;
                        }
                    }
                }
                else
                if (this.tableHeader.field === 'compared_field_id' && this.tableRow.compared_field_id) {
                    let ref_tb = _.find(this.ref_tb_from_refcond._fields, {id: Number(this.tableRow.compared_field_id)});
                    res = ref_tb ? this.$root.uniqName( ref_tb.name ) : '';
                }
                else
                if (this.tableHeader.field === 'compared_value') {
                    res = this.tableRow.spec_show || this.tableRow.compared_value;
                }
                else
                if (this.tableHeader.field === 'table_field_id') {
                    let fld = _.find(this.globalMeta._fields, {id: Number(this.tableRow.table_field_id)});
                    res = fld ? this.$root.uniqName( fld.name ) : '';
                }
                else
                if (this.tableHeader.field === 'username') {
                    res = this.$root.getUserSimple(this.tableRow, this.tableMeta._owner_settings);
                }
                else {
                    res = this.tableRow[this.tableHeader.field];
                }

                return this.$root.strip_tags(res);
            },
            getComparedValues() {
                this.$root.sm_msg_type = 1;
                let table_id = (this.behavior === 'data_sets_ref_condition_items' ? this.ref_tb_from_refcond.id : this.globalMeta.id);
                let field_id = (this.behavior === 'data_sets_ref_condition_items' ? this.tableRow.compared_field_id : this.tableRow.table_field_id);
                axios.get('/ajax/table-data/field/get-distinctive', {
                    params: {
                        table_id: table_id,
                        field_id: field_id,
                    }
                }).then(({ data }) => {
                    this.compared_values = data;
                    this.source_field_for_values = field_id;
                    this.showEdit();
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },

            //arrays for selects
            comparedVals() {
                return _.map(this.compared_values, (value, key) => {
                    return { val: key, show: value, }
                });
            },
            nameFields() {
                let fields = _.filter(this.globalMeta._fields, (hdr) => { return !this.inArray(hdr.field, this.$root.systemFieldsNoId) });
                return _.map(fields, (hdr) => {
                    return { val: hdr.id, show: this.$root.uniqName(hdr.name), }
                });
            },
            refTbNameFields() {
                let fields = _.filter(this.ref_tb_from_refcond._fields, (hdr) => { return !this.inArray(hdr.field, this.$root.systemFieldsNoId) });
                return _.map(fields, (hdr) => {
                    return { val: hdr.id, show: this.$root.uniqName(hdr.name), }
                });
            },
        }
    }
</script>

<style scoped>
    @import "CustomCell.scss";

    .folder-option {
        color: #000000;
        font-weight: bold;
        background-color: #EEEEEE;
    }
</style>