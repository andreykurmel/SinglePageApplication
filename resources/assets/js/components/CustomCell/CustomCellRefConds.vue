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
                :td-value="tableRow.spec_show || tableRow.compared_value"
                :with_edit="true"
                :force_edit="true"
                :style="{width: '100%'}"
                @updated-td-val="updateSingle"
        ></single-td-field>

        <div v-else="" class="td-wrapper" :style="getTdWrappStyle">

            <div class="wrapper-inner" :style="getWrapperStyle">

                <div v-if="tableHeader.field === 'ref_table_id'" class="inner-content">
                    <a target="_blank"
                       title="Open the “Visiting” view in a new tab."
                       :href="showField('__visiting_url')"
                       @click.stop=""
                       v-html="showField()"></a>
                    <a v-if="refIsOwner()"
                       title="Open the source table in a new tab."
                       target="_blank"
                       :href="showField('__url')"
                       @click.stop="">(Table)</a>
                </div>

                <div v-else-if="tableHeader.field === '_uses_rows'" class="inner-content">
                    <span v-for="rg in tableRow._uses_rows" class="is_select m_sel__wrap">
                        <a title="Open row group in popup." @click.stop="showGrPopup('row', rg.id)">{{ rg.name }}</a>
                        <span class="m_sel__remove"
                              @click.prevent.stop=""
                              @mousedown.prevent.stop="usesChecked(rg.id, rg.name)"
                              @mouseup.prevent.stop=""
                        >&times;</span>
                    </span>
                </div>

                <div v-else-if="tableHeader.field === '_uses_links'" class="inner-content">
                    <span v-for="lnk in tableRow._uses_links" class="is_select m_sel__wrap">
                        <a title="Open link in popup." @click.stop="showLnkPopup(lnk.table_field_id, lnk.id)">{{ lnk.name }}</a>
                        <span class="m_sel__remove"
                              @click.prevent.stop=""
                              @mousedown.prevent.stop="usesChecked(lnk.id, lnk.name)"
                              @mouseup.prevent.stop=""
                        >&times;</span>
                    </span>
                </div>

                <div v-else-if="tableHeader.field === '_uses_ddls'" class="inner-content">
                    <span v-for="ddl in tableRow._uses_ddls" class="is_select m_sel__wrap">
                        <a title="Open ddl in popup." @click.stop="showDdlPopup(ddl.id)">{{ ddl.name }}</a>
                        <span class="m_sel__remove"
                              @click.prevent.stop=""
                              @mousedown.prevent.stop="usesChecked(ddl.id, ddl.name)"
                              @mouseup.prevent.stop=""
                        >&times;</span>
                    </span>
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

            <tablda-select-simple
                v-else-if="inArray(tableHeader.field, ['_uses_rows','_uses_links','_uses_ddls'])"
                :options="usesOptions()"
                :table-row="tableRow"
                :hdr_field="tableHeader.field"
                :fixed_pos="true"
                :fld_input_type="tableHeader.input_type"
                :embed_func_txt="'Add New'"
                :style="getEditStyle"
                @selected-item="usesChecked"
                @hide-select="hideEdit"
                @embed-func="usesAddNew"
            ></tablda-select-simple>

            <!--Conditions Cells-->
            <tablda-select-simple
                    v-else-if="tableHeader.field === 'table_field_id'"
                    :options="nameFields()"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :fixed_pos="true"
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
                    :fixed_pos="true"
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
                    :fixed_pos="true"
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
                    :fixed_pos="true"
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
                    :fixed_pos="true"
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
                    :fixed_pos="true"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
            ></tablda-select-simple>

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
import {eventBus} from '../../app';

import {SpecialFuncs} from '../../classes/SpecialFuncs';
import {OptionsHelper} from '../../classes/helpers/OptionsHelper';

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
                let prev_row = this.allRows ? this.allRows[this.rowIndex-1] : null;
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
            allRows: Object|null,
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
            updateSingle(val, header, ddl_option) {
                this.tableRow.compared_value = val;
                if (ddl_option && this.tableHeader.field === 'compared_value') {
                    this.tableRow.spec_show = ddl_option.show;
                }
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
                        res = link
                            ? tb[link]
                            : (tb.id == this.globalMeta.id
                                ? '<span style="color: #00F;">SELF</span>'
                                : ((tb._referenced ? ('@' + tb._referenced + '/') : '') + tb.name));
                    }
                }
                else
                if (this.tableHeader.field === 'compared_field_id' && this.tableRow.compared_field_id) {
                    let ref_tb = _.find(this.ref_tb_from_refcond._fields, {id: Number(this.tableRow.compared_field_id)});
                    res = ref_tb ? this.$root.uniqName( ref_tb.name ) : '';
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
                        special_params: SpecialFuncs.specialParams(),
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
            refIsOwner() {
                let tb = _.find(this.$root.settingsMeta.available_tables, {id: Number(this.tableRow.ref_table_id)});
                return tb && tb.user_id == this.user.id;
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

            //settings popups
            showGrPopup(type, id) {
                eventBus.$emit('show-grouping-settings-popup', this.globalMeta.db_name, type, id);
            },
            showLnkPopup(table_field_id, id) {
                eventBus.$emit('show-display-links-settings-popup', table_field_id, id);
            },
            showDdlPopup(id) {
                eventBus.$emit('show-ddl-settings-popup', this.globalMeta.db_name, id);
            },

            //Uses
            usesOptions() {
                switch (this.tableHeader.field) {
                    case '_uses_rows': return OptionsHelper.rowGroup(this.globalMeta);
                    case '_uses_links': return OptionsHelper.linksGrouped(this.globalMeta);
                    case '_uses_ddls': return OptionsHelper.ddlsReferences(this.globalMeta);
                }
            },
            usesChecked(key, show) {
                switch (this.tableHeader.field) {
                    case '_uses_rows': this.uRowGroupChecked(key); break;
                    case '_uses_links': this.uLinksChecked(key); break;
                    case '_uses_ddls': this.uDdlsChecked(key); break;
                }
            },
            uRowGroupChecked(key) {
                let rowGroup = _.find(this.globalMeta._row_groups, {id: key});
                let present = _.find(this.tableRow._uses_rows, {id: key});
                rowGroup.row_ref_condition_id = present ? null : this.tableRow.id;
                eventBus.$emit('event-update-row-group', this.globalMeta.id, rowGroup);
            },
            uLinksChecked(key) {
                let fieldLink = null;
                _.each(this.globalMeta._fields, (field) => {
                    fieldLink = fieldLink || _.find(field._links, {id: key});
                });
                let present = _.find(this.tableRow._uses_links, {id: key});
                fieldLink.table_ref_condition_id = present ? null : this.tableRow.id;
                eventBus.$emit('event-update-field-link', this.globalMeta.id, fieldLink);
            },
            uDdlsChecked(key) {
                let ddlElem = _.find(this.globalMeta._ddls, {id: key});
                let present = _.find(this.tableRow._uses_ddls, {id: key});
                _.each(ddlElem._references, (ddlRef) => {
                    if (present) {
                        eventBus.$emit('event-delete-ddl-reference-row', this.globalMeta.id, ddlRef);
                    } else {
                        ddlRef.table_ref_condition_id = this.tableRow.id;
                        eventBus.$emit('event-update-ddl-reference-row', this.globalMeta.id, ddlRef);
                    }
                });
            },
            usesAddNew() {
                switch (this.tableHeader.field) {
                    case '_uses_rows': this.showGrPopup('row'); break;
                    case '_uses_links': this.showLnkPopup(); break;
                    case '_uses_ddls': this.showDdlPopup(); break;
                }
            },
        }
    }
</script>

<style lang="scss" scoped>
    @import "CustomCell.scss";

    .folder-option {
        color: #000000;
        font-weight: bold;
        background-color: #EEEEEE;
    }
</style>