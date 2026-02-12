<template>
    <td :style="getCustomCellStyle"
        class="td-custom"
        ref="td"
        @click="showEdit()"
    >
        <single-td-field
                v-if="inArray(behavior, ['alert_notif', 'alert_notif_clicktoupdate'])
                    && inArray(tableHeader.field, ['new_value'])
                    && tbField
                    && canCellEdit"
                :table-meta="globalMeta"
                :table-header="tbField"
                :td-value="tableRow[tableHeader.field]"
                :style="{width: '100%'}"
                :with_edit="!!canCellEdit"
                :force_edit="!!canCellEdit"
                @updated-td-val="updateSingle"
        ></single-td-field>

        <single-td-field
                v-else-if="behavior === 'alert_ufv' && inArray(tableHeader.field, ['input']) && globalMeta && referTbHeader && canCellEdit"
                :table-meta="globalMeta"
                :table-header="referTbHeader"
                :td-value="tableRow['show_'+tableHeader.field] || tableRow[tableHeader.field]"
                :style="{width: '100%'}"
                :with_edit="!!canCellEdit"
                :force_edit="!!canCellEdit"
                @updated-td-val="updateSingle"
        ></single-td-field>

        <single-td-field
                v-else-if="behavior === 'alert_anr' && inArray(tableHeader.field, ['input','temp_input']) && canCellEdit && ref_tb_from_refcond && conditionHeader"
                :table-meta="ref_tb_from_refcond"
                :table-header="conditionHeader"
                :td-value="tableRow['show_'+tableHeader.field] || tableRow[tableHeader.field]"
                :style="{width: '100%'}"
                :with_edit="!!canCellEdit"
                :force_edit="!!canCellEdit"
                @updated-td-val="updateSingle"
        ></single-td-field>

        <div v-else="" class="td-wrapper" :style="getTdWrappStyle()">

            <div class="wrapper-inner" :style="getWrapperStyle()">
                <div class="inner-content">

                    <label class="switch_t" v-if="tableHeader.f_type === 'Boolean'" :style="{height: Math.min(maxCellHGT, 17)+'px'}">
                        <input type="checkbox" ref="inline_input" v-model="tableRow[tableHeader.field]" @change="updateValue()">
                        <span class="toggler round"></span>
                    </label>

                    <div v-else-if="tableHeader.field === 'table_id'" class="inner-content">
                        <a target="_blank"
                           title="Open the “Visiting” MRV in a new tab."
                           :href="showField('__visiting_url')"
                           @click.stop=""
                           v-html="showField()"></a>
                        <a v-if="refIsOwner(tableRow.table_id)"
                           title="Open the source table in a new tab."
                           target="_blank"
                           :href="showField('__url')"
                           @click.stop="">(Table)</a>
                    </div>

                    <a v-else-if="tableHeader.field === 'mail_col_group_id'"
                       title="Open column group in popup."
                       @click.stop="showGroupsPopup('col', tableRow.mail_col_group_id)"
                    >{{ showField() }}</a>

                    <a v-else-if="tableHeader.field === 'table_ref_cond_id'"
                       title="Open ref condition in popup."
                       @click.stop="showSelectedPopup()"
                    >{{ showField() }}</a>

                    <div v-else="" class="content" v-html="showField()"></div>

                </div>
            </div>

        </div>



        <!-- ABSOLUTE EDITINGS -->
        <div v-if="isEditing()" class="cell-editing">

            <!--ANR-->
            <tablda-select-simple
                    v-if="(behavior === 'alert_anr' && inArray(tableHeader.field, ['source','temp_source']))
                        || (behavior === 'alert_ufv' && inArray(tableHeader.field, ['source']))"
                    :options="[
                        {val: 'Input', show: 'Input'},
                        {val: 'Inherit', show: 'Inherit'},
                    ]"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :fixed_pos="true"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
            ></tablda-select-simple>

            <select-with-folder-structure
                    v-else-if="behavior === 'alert_anr' && inArray(tableHeader.field, ['table_id','temp_table_id'])"
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
                    v-else-if="(behavior === 'alert_anr' && inArray(tableHeader.field, ['inherit_field_id','temp_inherit_field_id']))
                        || (behavior === 'alert_ufv' && inArray(tableHeader.field, ['inherit_field_id']))"
                    :options="nameFields(globalMeta)"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :can_empty="true"
                    :fixed_pos="true"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
            ></tablda-select-simple>

            <tablda-select-simple
                    v-else-if="behavior === 'alert_anr' && inArray(tableHeader.field, ['table_field_id','temp_table_field_id'])"
                    :options="nameFields(ref_tb_from_refcond)"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :can_empty="true"
                    :fixed_pos="true"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
            ></tablda-select-simple>
            <!--^^^ANR^^^-->

            <!--SNAPSHOT-->
            <tablda-select-simple
                    v-else-if="(behavior === 'alert_snapshot' && inArray(tableHeader.field, ['current_field_id']))
                        || (behavior === 'alert_notif_clicktoupdate' && inArray(tableHeader.field, ['table_field_id']))"
                    :options="nameFields(globalMeta)"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :fixed_pos="true"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
            ></tablda-select-simple>

            <tablda-select-simple
                    v-else-if="behavior === 'alert_snapshot' && inArray(tableHeader.field, ['source_field_id'])"
                    :options="nameFields(ref_tb_from_refcond)"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :fixed_pos="true"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
            ></tablda-select-simple>
            <!--^^^ANR^^^-->

            <tablda-select-simple
                    v-else-if="tableHeader.field === 'mail_col_group_id'"
                    :options="globalColGroups()"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :can_empty="true"
                    :fixed_pos="true"
                    :embed_func_txt="'Add New'"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
                    @embed-func="showGroupsPopup('col')"
            ></tablda-select-simple>

            <tablda-select-simple
                    v-else-if="tableHeader.field === 'logic'"
                    :options="[
                        {val: 'and', show: 'AND'},
                        {val: 'or', show: 'OR'},
                    ]"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :fixed_pos="true"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
            ></tablda-select-simple>

            <tablda-select-simple
                    v-else-if="tableHeader.field === 'condition'"
                    :options="[
                        {val: '<', show: '<'},
                        {val: '=', show: '='},
                        {val: '>', show: '>'},
                        {val: '!=', show: '!='},
                    ]"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :fixed_pos="true"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
            ></tablda-select-simple>

            <tablda-select-simple
                    v-else-if="tableHeader.field === 'table_field_id'"
                    :options="behavior === 'alert_ufv' ? nameFields(referTable) : nameFields(globalMeta)"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :can_empty="true"
                    :fixed_pos="true"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
            ></tablda-select-simple>

            <select-block
                    v-else-if="tableHeader.field === 'table_ref_cond_id'"
                    :options="globalRefCondsSpecial()"
                    :sel_value="tableRow[tableHeader.field]"
                    :fixed_pos="true"
                    :auto_open="true"
                    :style="getEditStyle"
                    style="height: 100%"
                    @option-select="updateFromOption"
                    @hide-select="hideEdit"
                    @button-click="showAlertAddNew"
            ></select-block>

            <textarea
                    v-else-if="inArray(tableHeader.f_type, ['description'])"
                    v-model="tableRow[tableHeader.field]"
                    @blur="hideEdit();updateValue()"
                    @resize="hideEdit();updateValue()"
                    ref="inline_input"
                    class="form-control full-height"
                    :style="getEditStyle"
            ></textarea>

            <input v-else-if="inArray(tableHeader.f_type, ['Date', 'Date Time', 'Time'])"
                   ref="inline_input"
                   @blur="hideDatePicker()"
                   class="form-control full-height"
                   :style="getEditStyle"/>

            <input
                    v-else-if="inArray(tableHeader.field, ['name','qty','temp_name','temp_qty'])"
                    v-model="tableRow[tableHeader.field]"
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
import {eventBus} from './../../app';

import Select2DDLMixin from './../_Mixins/Select2DDLMixin.vue';
import CellStyleMixin from '../_Mixins/CellStyleMixin.vue';

import TabldaSelectSimple from "./Selects/TabldaSelectSimple";
import SelectWithFolderStructure from "./InCell/SelectWithFolderStructure";
import SelectBlock from "../CommonBlocks/SelectBlock";

export default {
        components: {
            SelectBlock,
            SelectWithFolderStructure,
            TabldaSelectSimple,
        },
        name: "CustomCellAlertNotif",
        mixins: [
            Select2DDLMixin,
            CellStyleMixin,
        ],
        data: function () {
            return {
                editing: false,
                oldVal: null,
            }
        },
        computed: {
            getCustomCellStyle() {
                let obj = this.getCellStyle();
                if (this.inArray(this.behavior, ['alert_ufv','alert_anr'])) {
                    if (
                        (this.tableRow.source === 'Inherit' && this.tableHeader.field === 'input')
                        || (this.tableRow.source === 'Input' && this.tableHeader.field === 'inherit_field_id')
                        || (this.tableRow.temp_source === 'Inherit' && this.tableHeader.field === 'input')
                        || (this.tableRow.temp_source === 'Input' && this.tableHeader.field === 'temp_inherit_field_id')
                        || (this.tableRow.ufv_source === 'Inherit' && this.tableHeader.field === 'ufv_input')
                        || (this.tableRow.ufv_source === 'Input' && this.tableHeader.field === 'ufv_inherit_field_id')
                    ) {
                        obj.backgroundColor = '#EEE';
                    }
                }
                return obj;
            },
            canCellEdit() {
                if (!this.with_edit) {
                    return false;
                }
                if (this.inArray(this.behavior, ['alert_ufv','alert_anr'])) {
                    return !(this.tableRow.source === 'Inherit' && this.tableHeader.field === 'input')
                        && !(this.tableRow.source === 'Input' && this.tableHeader.field === 'inherit_field_id')
                        && !(this.tableRow.temp_source === 'Inherit' && this.tableHeader.field === 'temp_input')
                        && !(this.tableRow.temp_source === 'Input' && this.tableHeader.field === 'temp_inherit_field_id')
                        && !(this.tableRow.ufv_source === 'Inherit' && this.tableHeader.field === 'ufv_input')
                        && !(this.tableRow.ufv_source === 'Input' && this.tableHeader.field === 'ufv_inherit_field_id');
                }
                return true;
            },
            //alert
            tbField() {
                return _.find(this.globalMeta._fields, {id: Number(this.tableRow.table_field_id)})
            },
            //update automations
            referTable() {
                if (Number(this.parentRow.table_ref_cond_id)) {
                    let rc = _.find(this.globalMeta._ref_conditions, {id: Number(this.parentRow.table_ref_cond_id)});
                    return _.find(this.$root.settingsMeta.available_tables, {id: Number(rc ? rc.ref_table_id : null)});
                }
                return this.globalMeta;
            },
            referTbHeader() {
                return this.referTable
                    ? _.find(this.referTable._fields, {id: Number(this.tableRow.table_field_id)})
                    : null;
            },
            //add automations
            conditionHeader() {
                let fid = this.tableHeader.field.indexOf('temp') > -1 ? this.tableRow.temp_table_field_id : this.tableRow.table_field_id;
                return this.ref_tb_from_refcond
                    ? _.find(this.ref_tb_from_refcond._fields, {id: Number(fid)})
                    : null;
            },
        },
        props:{
            globalMeta: Object,
            tableMeta: Object,
            tableHeader: Object,
            tableRow: Object,
            parentRow: Object,
            cellHeight: Number,
            maxCellRows: Number,
            user: Object,
            behavior: String,
            ref_tb_from_refcond: Object,
            with_edit: Boolean,
        },
        methods: {
            refIsOwner(table_id) {
                let tb = _.find(this.$root.settingsMeta.available_tables, {id: Number(table_id)});
                return tb && tb.user_id == this.user.id;
            },
            inArray(item, array) {
                return $.inArray(item, array) > -1;
            },
            isEditing() {
                return this.editing && this.canCellEdit && !this.$root.global_no_edit;
            },
            showEdit() {
                if (!this.canCellEdit || this.inArray(this.tableHeader.field, ['is_active'])) {
                    return;
                }
                this.editing = true;
                if (this.isEditing()) {
                    this.oldValue = this.tableRow[this.tableHeader.field];
                    this.$nextTick(function () {
                        if (this.$refs.inline_input) {
                            if (this.inArray(this.tableHeader.f_type, ['Date Time', 'Date', 'Time'])) {
                                this.showHideDatePicker(true);
                            } else
                            if (this.$refs.inline_input && this.$refs.inline_input.nodeName === 'SELECT'){
                                this.showHideDDLs(this.$root.selectParam);
                                this.ddl_cached = false;
                            } else {
                                this.$refs.inline_input.focus();
                            }
                        }
                    });
                } else {
                    this.editing = false;
                }
            },
            hideEdit() {
                this.editing = false;
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
                this.tableRow[this.tableHeader.field] = value;
                this.updateValue();
            },
            updateSingle(val, header, ddl_option) {
                this.tableRow[this.tableHeader.field] = val;
                if (ddl_option && this.inArray(this.tableHeader.field, ['input','temp_input'])) {
                    this.tableRow['show_'+this.tableHeader.field] = ddl_option.show;
                }
                this.updateValue();
            },
            updateValue() {
                if (this.tableRow[this.tableHeader.field] !== this.oldValue) {
                    if (this.behavior === 'alert_ufv' && this.tableHeader.field === 'table_ref_cond_id') {
                        this.tableRow.table_id = this.globalMeta.id;
                    }
                    if (this.inArray(this.behavior, ['alert_notif']) && this.tableHeader.field === 'table_field_id') {
                        this.tableRow.new_value = null;
                    }
                    if (this.inArray(this.behavior, ['alert_ufv','alert_anr']) && this.tableHeader.field === 'source') {
                        this.tableRow.input = null;
                        this.tableRow.inherit_field_id = null;
                    }
                    this.$emit('updated-cell', this.tableRow);
                }
            },
            updateFromOption(opt) {
                this.updateCheckedDDL(opt.val);
            },
            updateCheckedDDL(item) {
                this.tableRow[this.tableHeader.field] = item;
                this.updateValue();
            },
            showField(link) {
                let res = '';
                if (this.tableHeader.field === 'table_field_id' && this.tableRow.table_field_id && this.behavior === 'alert_ufv') {
                    res = this.referTbHeader ? this.$root.uniqName(this.referTbHeader.name) : this.tableRow.table_field_id;
                }
                else
                if (
                    this.inArray(this.tableHeader.field, ['table_id','temp_table_id'])
                    && this.tableRow[this.tableHeader.field]
                    && this.inArray(this.behavior, ['alert_anr'])
                ) {
                    let reftb = _.find(this.$root.settingsMeta.available_tables, {id: Number(this.tableRow[this.tableHeader.field])}) || {};
                    if (reftb[link]) {
                        res = reftb[link];
                    } else {
                        res = !reftb.id || reftb.id == this.globalMeta.id
                            ? '<span style="color: #00F;">SELF</span>'
                            : reftb.name;
                    }
                }
                else
                if (
                    this.inArray(this.tableHeader.field, ['table_field_id','temp_table_field_id'])
                    && this.tableRow[this.tableHeader.field]
                    && this.behavior === 'alert_anr'
                ) {
                    let reFld = _.find(this.nameFields(this.ref_tb_from_refcond), {val: Number(this.tableRow[this.tableHeader.field])});
                    res = reFld ? this.$root.uniqName(reFld.show) : this.tableRow[this.tableHeader.field];
                }
                else
                if (this.behavior === 'alert_snapshot' && this.tableRow[this.tableHeader.field]) {
                    let tM = this.tableHeader.field === 'current_field_id' ? this.globalMeta : this.ref_tb_from_refcond;
                    let reFld = _.find(this.nameFields(tM), {val: Number(this.tableRow[this.tableHeader.field])});
                    res = reFld ? this.$root.uniqName(reFld.show) : this.tableRow[this.tableHeader.field];
                }
                else
                if (
                    this.inArray(this.tableHeader.field, ['table_field_id','inherit_field_id','temp_inherit_field_id','ufv_inherit_field_id'])
                    && this.tableRow[this.tableHeader.field]
                ) {
                    let reFld = _.find(this.nameFields(this.globalMeta), {val: Number(this.tableRow[this.tableHeader.field])});
                    res = reFld ? this.$root.uniqName(reFld.show) : this.tableRow[this.tableHeader.field];
                }
                else
                if (this.tableHeader.field === 'table_ref_cond_id') {
                    let selOpt = _.find(this.globalRefCondsSpecial(), {val: this.tableRow.table_ref_cond_id});
                    if (selOpt) {
                        res = selOpt.show;
                    } else {
                        let refCond = _.find(this.globalMeta._ref_conditions, {val: Number(this.tableRow.table_ref_cond_id)});
                        res = refCond ? 'RC:'+refCond.name : 'All';
                    }
                }
                else
                if (this.tableHeader.field === 'mail_col_group_id' && this.tableRow.mail_col_group_id) {
                    let colGr = _.find(this.globalMeta._column_groups, {id: Number(this.tableRow.mail_col_group_id)});
                    res = colGr ? colGr.name : this.tableRow.mail_col_group_id;
                }
                else
                if (this.tableHeader.field === 'mail_format' && this.tableRow.mail_format) {
                    switch (this.tableRow.mail_format) {
                        case 'table': res = 'Tabular'; break;
                        case 'list': res = 'Listing'; break;
                        default: res = ''; break;
                    }
                }
                else
                if (this.tableHeader.field === 'logic' && this.tableRow.logic) {
                    switch (this.tableRow.logic) {
                        case 'or': res = 'OR'; break;
                        default: res = 'AND'; break;
                    }
                }
                else {
                    res = this.tableRow[this.tableHeader.field];
                }
                return this.$root.strip_danger_tags(res);
            },

            //arrays for selects
            globalColGroups() {
                return _.map(this.globalMeta._column_groups, (cg) => {
                    return { val: cg.id, show: cg.name, }
                });
            },
            globalRefCondsSpecial() {
                let title_style =  {
                    color: '#000',
                    backgroundColor: '#eee',
                    cursor: 'auto',
                    textDecoration: 'none'
                };

                let grs = [
                    { val:'this_row', show:'THIS' },
                    { val:null, show:'ALL' },
                ];
                grs.push({
                    val: null,
                    show: '//Row Groups:',
                    style: title_style,
                    isTitle: true,
                });
                _.each(this.globalMeta._row_groups, (cg) => {
                    if (cg.row_ref_condition_id) {
                        grs.push({
                            val: cg.row_ref_condition_id,
                            show: cg.name,
                            style: {color: '#333'},
                            is: 'rowgroup',
                        });
                    }
                });
                grs.push({
                    val: null,
                    show: 'Add New',
                    isButton: 'group',
                });
                _.each(this.globalMeta._fields, (fld) => {
                    if (
                        fld._links.length
                        && _.find(fld._links, (lnk) => { return this.lnkIsRec(lnk) })
                    ) {
                        grs.push({
                            val: null,
                            show: '//Links@Col: '+fld.name,
                            style: title_style,
                            isTitle: true,
                        });
                        _.each(fld._links, (lnk) => {
                            if (this.lnkIsRec(lnk)) {
                                grs.push({
                                    val: lnk.table_ref_condition_id,
                                    show: lnk.name || lnk.icon,
                                    style: {color: '#333'},
                                    is: 'displaylink',
                                });
                            }
                        });
                        grs.push({
                            val: null,
                            show: 'Add New',
                            isButton: 'link',
                        });
                    }
                });
                return grs;
            },
            lnkIsRec(lnk) {
                return $.inArray(lnk.link_type, ['Record']) > -1;
            },
            nameFields(tableMeta) {
                let fields = tableMeta
                    ? _.filter(tableMeta._fields, (hdr) => { return !this.inArray(hdr.field, this.$root.systemFields) })
                    : [];
                return _.map(fields, (hdr) => {
                    return { val: hdr.id, show: this.$root.uniqName(hdr.name), }
                });
            },

            //Group Links
            showGroupsPopup(type, id) {
                eventBus.$emit('show-grouping-settings-popup', this.globalMeta.db_name, type, id);
            },
            showAlertAddNew(type) {
                switch (type) {
                    case 'group':
                        eventBus.$emit('show-grouping-settings-popup', this.globalMeta.db_name, 'row');
                        break;
                    case 'link':
                        eventBus.$emit('show-display-links-settings-popup');
                        break;
                }
            },
            showSelectedPopup() {
                let ref_id = Number(this.tableRow.table_ref_cond_id);
                let selOpt = _.find(this.globalRefCondsSpecial(), {val: ref_id});
                if (selOpt && selOpt.is) {
                    switch (selOpt.is) {
                        case 'rowgroup':
                            let rg = _.find(this.globalMeta._row_groups, {row_ref_condition_id: ref_id}) || {};
                            eventBus.$emit('show-grouping-settings-popup', this.globalMeta.db_name, 'row', rg.id);
                            break;
                        case 'displaylink':
                            let lnk = _.find(this.globalMeta._fields, (fl) => {
                                return _.find(fl._links, {table_ref_condition_id: ref_id});
                            }) || {};
                            eventBus.$emit('show-display-links-settings-popup', lnk.id);
                            break;
                    }
                }
            },
        }
    }
</script>

<style lang="scss" scoped>
    @import "CustomCell.scss";
</style>