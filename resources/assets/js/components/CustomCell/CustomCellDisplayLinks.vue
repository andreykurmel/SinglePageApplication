<template>
    <td :style="getCustomCellStyle"
        class="td-custom"
        ref="td"
        @click="showEdit()"
    >
        <div class="td-wrapper" :style="getTdWrappStyle">

            <div class="wrapper-inner" :style="getWrapperStyle">
                <div class="inner-content">

                    <label class="switch_t" v-if="tableHeader.field === 'show_sum'">
                        <input type="checkbox"
                               v-model="editValue"
                               :disabled="!inArray(fld_link_type, ['Record'])"
                               @change="updateValue()">
                        <span class="toggler round"></span>
                    </label>

                    <label class="switch_t" v-else-if="tableHeader.f_type === 'Boolean'" :style="{height: Math.min(maxCellHGT, 17)+'px'}">
                        <input type="checkbox" :disabled="!canCellEdit" :checked="checkBoxOn" @change.prevent="updateCheckBox()">
                        <span class="toggler round" :class="[!canCellEdit ? 'disabled' : '']"></span>
                    </label>

                    <a v-else-if="tableHeader.field === 'table_ref_condition_id'"
                       title="Open ref condition in popup."
                       @click.stop="showAddRefCond(tableRow.table_ref_condition_id)"
                    >{{ showField() }}</a>

                    <a v-else-if="['payment_paypal_keys_id','payment_stripe_keys_id'].indexOf(tableHeader.field) > -1"
                       title="Open user settings in popup."
                       @click.stop="showUserSettings(tableRow[tableHeader.field])"
                    >{{ showField() }}</a>

                    <a v-else-if="tableHeader.field === 'popup_display' && tableRow.popup_display === 'Boards'"
                       title="Open general settings in popup."
                       @click.stop="showBoardSett()"
                    >{{ showField() }}</a>

                    <div v-else-if="is_multisel">
                        <span v-for="str in editValue" v-if="str" :class="{'is_select': is_sel, 'm_sel__wrap': is_multisel}">
                            <span v-html="showMselPart(str)"></span>
                            <span v-if="is_sel && is_multisel" class="m_sel__remove" @click.prevent.stop="updateCheckedDDL(str)">&times;</span>
                        </span>
                    </div>

                    <div v-else="">{{ showField() }}</div>

                </div>
            </div>

        </div>



        <!-- ABSOLUTE EDITINGS -->
        <div v-if="isEditing()" class="cell-editing">

            <tablda-select-simple
                    v-if="tableHeader.field === 'table_field_link_id'"
                    :options="allFieldsLinks()"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :can_empty="true"
                    :fixed_pos="true"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
            ></tablda-select-simple>

            <tablda-select-simple
                    v-else-if="inArray(tableHeader.field, ['listing_field_id','link_preview_fields','email_addon_fields']) && tableRow.table_ref_condition_id"
                    :options="listingsFields()"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :can_empty="tableHeader.field === 'listing_field_id'"
                    :fixed_pos="true"
                    :fld_input_type="tableHeader.input_type"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
            ></tablda-select-simple>

            <tablda-select-simple
                    v-else-if="tableHeader.field === 'table_app_id' && fld_link_type === 'App'"
                    :options="tableApps()"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :can_empty="true"
                    :fixed_pos="true"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
            ></tablda-select-simple>

            <tablda-select-simple
                    v-else-if="tableHeader.field === 'link_type'"
                    :options="[
                        {val: 'Record', show: 'Record', disabled:!$root.checkAvailable($root.user, 'link_type_record')},
                        {val: 'Web', show: 'Web', disabled:!$root.checkAvailable($root.user, 'link_type_web')},
                        {val: 'App', show: 'App', disabled:!$root.checkAvailable($root.user, 'link_type_app')},
                        {val: 'GMap', show: 'GMap'},
                        {val: 'GEarth', show: 'GEarth'},
                    ]"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :fixed_pos="true"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
            ></tablda-select-simple>

            <tablda-select-simple
                    v-else-if="tableHeader.field === 'link_display'"
                    :options="[
                        {val: 'Popup', show: 'Pop-up'},
                        {val: 'Table', show: 'New Tab Table'},
                        {val: 'RorT', show: 'Choose at Opening'},
                    ]"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :fixed_pos="true"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
            ></tablda-select-simple>

            <tablda-select-simple
                    v-else-if="tableHeader.field === 'link_pos'"
                    :options="[
                        {val: 'before', show: 'Before'},
                        {val: 'after', show: 'After'},
                    ]"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :fixed_pos="true"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
            ></tablda-select-simple>

            <tablda-select-simple
                    v-else-if="tableHeader.field === 'icon'"
                    :options="[
                        {val: 'Arrow', show: 'Arrow'},
                        {val: 'Delete', show: 'Delete'},
                        {val: 'PDF', show: 'PDF'},
                        {val: 'Doc', show: 'Doc'},
                        {val: 'Record', show: 'Record'},
                        {val: 'Table', show: 'Table'},
                        {val: 'Web', show: 'Web'},
                        {val: 'Underlined', show: 'Underlined'},
                    ]"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :can_empty="true"
                    :allowed_tags="true"
                    :fixed_pos="true"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
            ></tablda-select-simple>

            <tablda-select-simple
                    v-else-if="tableHeader.field === 'popup_display'"
                    :options="[
                        {val: 'Table', show: 'Table'},
                        {val: 'Listing', show: 'Listing'},
                        {val: 'Boards', show: 'Boards'},
                    ]"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :fixed_pos="true"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
            ></tablda-select-simple>

            <tablda-select-simple
                    v-else-if="tableHeader.field === 'table_ref_condition_id' && inArray(fld_link_type, ['Record', 'App'])"
                    :options="globalRefConds()"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :can_empty="true"
                    :embed_func_txt="isVertTable ? 'Add New' : ''"
                    :fixed_pos="true"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
                    @embed-func="showAddRefCond(tableRow.table_ref_condition_id)"
            ></tablda-select-simple>

            <tablda-select-simple
                    v-else-if="inArray(tableHeader.field, ['payment_paypal_keys_id','payment_stripe_keys_id'])"
                    :options="userKeys(tableHeader.field)"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :can_empty="true"
                    :fixed_pos="true"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
            ></tablda-select-simple>

            <tablda-select-simple
                    v-else-if="inArray(tableHeader.field, [
                        'address_field_id','link_field_lat','link_field_lng','link_field_address','payment_amount_fld_id',
                        'payment_history_payee_fld_id','payment_history_amount_fld_id','payment_history_date_fld_id',
                        'payment_method_fld_id','payment_description_fld_id','payment_customer_fld_id',
                    ])"
                    :options="nameFields()"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :can_empty="true"
                    :fixed_pos="true"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
            ></tablda-select-simple>

            <tablda-select-simple
                    v-else-if="inArray(tableHeader.field, ['column_id'])"
                    :options="nameFields('id')"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :can_empty="true"
                    :fixed_pos="true"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
            ></tablda-select-simple>

            <input
                    v-else-if="inArray(tableHeader.field, [
                        'add_limit','add_record_limit','tooltip','param','value','web_prefix',
                    ])"
                    v-model="editValue"
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

import Select2DDLMixin from './../_Mixins/Select2DDLMixin.vue';
import CellStyleMixin from '../_Mixins/CellStyleMixin.vue';

import TabldaSelectSimple from "./Selects/TabldaSelectSimple";

export default {
        components: {
            TabldaSelectSimple,
        },
        name: "CustomCellDisplayLinks",
        mixins: [
            Select2DDLMixin,
            CellStyleMixin,
        ],
        data: function () {
            return {
                editValue: null,
                editing: false,
                oldVal: null,
                selectParamIcon: {
                    tags: true,
                    maximumInputLength: 10
                }
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
            cellValue: String|Number,
            cellHeight: Number,
            maxCellRows: Number,
            user: Object,
            isVertTable: Boolean,
            isAddRow: Boolean,
            no_width: Boolean,
        },
        watch: {
            cellValue: {
                handler(val) {
                    this.editValue = this.convEditValue(val);
                },
                immediate: true,
            },
        },
        computed: {
            fld_link_type() {
                return this.tableRow.link_type;
            },
            getCustomCellStyle() {
                let obj = this.getCellStyle;
                if (this.no_width) {
                    obj.width = null;
                }
                obj.textAlignt = (this.tableHeader.f_type === 'Boolean' ? 'center' : '');
                if (this.tableHeader.field === 'icon' && this.inArray(this.fld_link_type, ['GMap', 'GEarth'])) {
                    obj.backgroundColor = '#EEE';
                }
                return obj;
            },
            canCellEdit() {
                if (this.tableMeta.db_name === 'table_field_link_to_dcr') {
                    return true;
                }
                return this.globalMeta._is_owner
                    && !this.inArray(this.tableHeader.field, this.$root.systemFields)
                    && !(this.tableHeader.field === 'icon' && this.inArray(this.fld_link_type, ['GMap', 'GEarth']))
                    && !(this.tableHeader.field === 'popup_display' && !this.inArray(this.fld_link_type, ['Record']));
            },
            checkBoxOn() {
                return Number(this.editValue);
            },
            is_sel() {
                return this.$root.issel(this.tableHeader.input_type);
            },
            is_multisel() {
                return this.$root.isMSEL(this.tableHeader.input_type);
            },
            payment_keys() {
                switch (this.tableHeader.field) {
                    case 'payment_paypal_keys_id': return this.$root.user._paypal_payment_keys;
                    case 'payment_stripe_keys_id': return this.$root.user._stripe_payment_keys;
                    default: return [];
                }
            },
        },
        methods: {
            convEditValue(res, reverse) {
                if (this.is_multisel) {
                    if (reverse) {
                        return (Array.isArray(res) ? JSON.stringify(res) : res);
                    } else {
                        let arr = this.$root.parseMsel(res);
                        arr = _.map(arr, (id) => { return Number(id); });
                        return arr;
                    }
                }
                return res;
            },
            inArray(item, array) {
                return $.inArray(item, array) > -1;
            },
            isEditing() {
                return this.editing && this.canCellEdit && !this.$root.global_no_edit;
            },
            showEdit() {
                if (!this.canCellEdit
                    || this.inArray(this.tableHeader.f_type, ['Boolean'])
                    || this.inArray(this.tableHeader.field, ['show_sum'])) {
                    return;
                }
                //edit cell
                this.editing = true;
                if (this.isEditing()) {
                    this.oldValue = this.editValue;
                    this.$nextTick(function () {
                        if (this.$refs.inline_input) {
                            if (this.tableHeader.field === 'icon') {
                                this.showHideDDLs(this.selectParamIcon);
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
            updateCheckedDDL(item) {
                if (this.is_multisel) {
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
                this.editValue = !Boolean(this.editValue);
                this.updateValue();
            },
            updateValue() {
                let newVal = this.convEditValue(this.editValue, true);
                if (newVal !== this.oldValue) {
                    this.tableRow[this.tableHeader.field] = newVal;
                    if (this.tableHeader.field === 'table_ref_condition_id') {
                        this.tableRow.listing_field_id = null;
                        this.tableRow.link_preview_fields = '';
                        this.tableRow.email_addon_fields = '';
                    }
                    if (this.tableHeader.field === 'link_field_lat' || this.tableHeader.field === 'link_field_lng') {
                        this.tableRow.link_field_address = null;
                    }
                    if (this.tableHeader.field === 'link_field_address') {
                        this.tableRow.link_field_lat = null;
                        this.tableRow.link_field_lng = null;
                    }

                    if (this.tableHeader.field === 'column_id' && String(this.tableRow['value']).substr(0,2) !== '{$') {
                        this.tableRow.value = null;
                    }
                    if (this.tableHeader.field === 'value' && String(this.tableRow['value']).substr(0,2) !== '{$') {
                        this.tableRow.column_id = null;
                    }
                    if (this.tableHeader.field === 'link_type') {
                        let val = this.tableRow[this.tableHeader.field];
                        this.tableRow.link_display = this.inArray( val, ['Record'] ) ? 'Popup' : null;
                        this.tableRow.popup_display = this.inArray( val, ['Record'] ) ? 'Listing' : null;
                        this.tableRow.show_sum = !!this.inArray( val, ['Record'] );
                    }
                    if (this.tableHeader.field === 'add_record_limit') {
                        this.tableRow.already_added_records = 0;
                    }

                    this.$emit('updated-cell', this.tableRow);
                }
            },
            showMselPart(id) {
                let ref_cond = _.find(this.globalMeta._ref_conditions, {id: Number(this.tableRow.table_ref_condition_id)});
                let res = id;
                if (ref_cond && ref_cond._ref_table && ref_cond._ref_table._fields) {
                    let field = _.find(ref_cond._ref_table._fields, {id: Number(id)});
                    res = field ? this.$root.uniqName(field.name) : '';
                }
                return res;
            },
            showField() {
                let res = '';
                if (this.tableHeader.field === 'table_ref_condition_id' && this.tableRow.table_ref_condition_id) {
                    let ref_cond = _.find(this.globalMeta._ref_conditions, {id: Number(this.tableRow.table_ref_condition_id)});
                    res = ref_cond ? ref_cond.name : '';
                }
                else
                if ($.inArray(this.tableHeader.field, ['payment_paypal_keys_id','payment_stripe_keys_id']) > -1 && this.tableRow[this.tableHeader.field]) {
                    let keysobj = _.find(this.payment_keys, {id: Number(this.tableRow[this.tableHeader.field])});
                    res = keysobj ? keysobj.name : res;
                }
                else
                if (this.tableHeader.field === 'listing_field_id' && this.tableRow.listing_field_id) {
                    let ref_cond = _.find(this.globalMeta._ref_conditions, {id: Number(this.tableRow.table_ref_condition_id)});
                    if (ref_cond && ref_cond._ref_table && ref_cond._ref_table._fields) {
                        let field = _.find(ref_cond._ref_table._fields, {id: Number(this.tableRow.listing_field_id)});
                        res = field ? this.$root.uniqName(field.name) : '';
                    }
                }
                else
                if (this.tableHeader.field === 'link_display' && this.tableRow.link_display) {
                    switch (this.tableRow.link_display) {
                        case 'Popup': res = 'Pop-up'; break;
                        case 'Table': res = 'New Tab Table'; break;
                        case 'RorT': res = 'Choose at Opening'; break;
                    }
                }
                else
                if (this.tableHeader.field === 'link_pos' && this.tableRow.link_pos) {
                    switch (this.tableRow.link_pos) {
                        case 'before': res = 'Before'; break;
                        case 'after': res = 'After'; break;
                    }
                }
                else
                if (this.tableHeader.field === 'table_field_link_id' && this.tableRow.table_field_link_id) {
                    _.each(this.globalMeta._fields, (fld) => {
                        _.each(fld._links, (link,idx) => {
                            if (link.id == this.tableRow.table_field_link_id) {
                                res = fld.name + '/#' + (idx+1);
                            }
                        });
                    });
                }
                else
                if (
                    this.inArray(this.tableHeader.field, [
                        'address_field_id','link_field_lat','link_field_lng','link_field_address','payment_amount_fld_id',
                        'payment_history_payee_fld_id','payment_history_amount_fld_id','payment_history_date_fld_id',
                        'payment_method_fld_id','payment_description_fld_id','payment_customer_fld_id',
                    ])
                    &&
                    this.editValue
                ) {
                    let idx = _.findIndex(this.globalMeta._fields, {id: Number(this.editValue)});
                    res = idx > -1 ? this.$root.uniqName( this.globalMeta._fields[idx].name ) : '';
                }
                else
                if (this.tableHeader.field === 'table_app_id' && this.tableRow.table_app_id) {
                    let idx = _.findIndex(this.$root.settingsMeta.table_public_apps_data, {id: Number(this.tableRow.table_app_id)});
                    res = idx > -1 ? this.$root.settingsMeta.table_public_apps_data[idx].name : '';
                }
                else {
                    res = to_standard_val(this.editValue);
                }
                return this.$root.strip_tags( String(res) );
            },
            showAddRefCond(refId) {
                this.$emit('show-add-ref-cond', refId)
            },
            showBoardSett() {
                eventBus.$emit('show-general-settings-popup');
            },
            showUserSettings() {
                eventBus.$emit('open-resource-popup', 'connections', 0, 'payment');
            },

            //arrays for selects
            nameFields(id) {
                let fields = _.filter(this.globalMeta._fields, (hdr) => {
                    return hdr.field === id || !this.inArray(hdr.field, this.$root.systemFields)
                });
                return _.map(fields, (hdr) => {
                    return { val: hdr.id, show: this.$root.uniqName(hdr.name), }
                });
            },
            userKeys() {
                return _.map(this.payment_keys, (key) => {
                    return { val: key.id, show: key.name, }
                });
            },
            globalRefConds() {
                return _.map(this.globalMeta._ref_conditions, (rc) => {
                    return { val: rc.id, show: rc.name, }
                });
            },
            getLiFi() {
                let refCond = _.find(this.globalMeta._ref_conditions, {id: Number(this.tableRow.table_ref_condition_id)});
                return (refCond && refCond._ref_table && refCond._ref_table._fields ? refCond._ref_table._fields : []);
            },
            listingsFields() {
                let fields = _.filter(this.getLiFi(), (hdr) => {
                    return !this.inArray(hdr.field, this.$root.systemFields)
                });
                return _.map(fields, (hdr) => {
                    return { val: hdr.id, show: this.$root.uniqName(hdr.name), }
                });
            },
            allFieldsLinks() {
                let res = [];
                _.each(this.globalMeta._fields, (fld) => {
                    _.each(fld._links, (link,idx) => {
                        res.push( { val: link.id, show: fld.name + '/#' + (idx+1), } );
                    });
                });
                return res;
            },
            tableApps(id) {
                return _.map(this.$root.settingsMeta.table_public_apps_data, (app) => {
                    return { val: app.id, show: app.name, }
                });
            },
        },
        mounted() {
            this.editValue = this.convEditValue(this.cellValue);
        },
    }
</script>

<style lang="scss" scoped>
    @import "CustomCell.scss";

    .right-cog {
        position: absolute;
        right: 0;
        top: 50%;
        transform: translateY(-50%);
        padding: 5px;
        cursor: pointer;
    }
</style>