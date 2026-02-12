<template>
    <td :style="getCustomCellStyle()"
        class="td-custom"
        ref="td"
        @click="showEdit()"
    >
        <div class="td-wrapper" :style="getTdWrappStyle()">

            <div class="wrapper-inner" :style="customInnerStyle()">
                <div class="inner-content">

                    <img v-if="tableHeader.field === 'qr_mrv_link'" :src="tableRow.qr_mrv_link" width="300" height="300">

                    <label class="switch_t" v-else-if="tableHeader.f_type === 'Boolean'" :style="{height: Math.min(maxCellHGT, 17)+'px'}">
                        <input type="checkbox" :disabled="!canEdit" v-model="editValue" @change="updateValue()">
                        <span class="toggler round" :class="[!canEdit ? 'disabled' : '']"></span>
                    </label>

                    <a v-else-if="!isAddRow && inArray(tableHeader.field, ['name'])"
                       title="Open the view in a new tab."
                       target="_blank"
                       :href="getLink()"
                       @click.stop="">{{ tableRow.name }}</a>

                    <a v-else-if="!isAddRow && inArray(tableHeader.field, ['custom_path'])"
                       title="Open the view in a new tab."
                       target="_blank"
                       :href="getCustomPath()"
                       @click.stop="">{{ tableRow.custom_path }}</a>

                    <div v-else-if="!isAddRow && inArray(tableHeader.field, ['user_link'])" class="flex flex--center">
                        <a :target="tableRow.is_active ? '_blank' : ''"
                           :href="tableRow.is_active ? getLink() : '#'"
                           title="Open the view in a new tab."
                        >
                            <button class="btn btn-default"
                                    :disabled="!tableRow.is_active"
                                    title="Public access address"
                            >
                                <!--<span v-if="tableRow.user_link">{{ tableRow.user_link }}</span>-->
                                <i class="glyphicon glyphicon-share"></i>
                            </button>
                        </a>
                    </div>

                    <div v-else-if="inArray(tableHeader.field, ['_embd'])" class="flex flex--center">
                        <embed-button class="btn btn-default embed__btn"
                                      :style="{visibility: !isAddRow && tableRow.user_id === $root.user.id ? 'visible' : 'hidden'}"
                                      :is-disabled="!tableRow.is_active"
                                      :hash="tableRow.hash"
                                      :is-fixed="true"
                        ></embed-button>
                    </div>

                    <a v-else-if="inArray(tableHeader.field, ['col_group_id', 'row_group_id']) && editValue"
                       @click.stop="showinlineGroupsPopup()"
                    >
                        <span :class="{'is_select': is_sel}">{{ showField() }}</span>
                    </a>

                    <a v-else-if="inArray(tableHeader.field, ['access_permission_id']) && editValue"
                       @click.stop="showinlinePermisPopup()"
                    >
                        <span :class="{'is_select': is_sel}">{{ showField() }}</span>
                    </a>

                    <div v-else-if="inArray(tableHeader.field, ['parts_avail','parts_default'])">
                        <span v-for="el in showParts()" :class="{'is_select': is_sel, 'm_sel__wrap': is_multisel}">
                            <span v-html="el.show"></span>
                            <span v-if="is_sel && is_multisel && with_edit"
                                  class="m_sel__remove"
                                  @click.prevent.stop=""
                                  @mousedown.prevent.stop="updateCheckedDDL(el.val)"
                                  @mouseup.prevent.stop=""
                            >&times;</span>
                        </span>
                    </div>

                    <div v-else="">
                        <span v-if="showField()" :class="{'is_select': is_sel}">{{ showField() }}</span>
                    </div>

                </div>
            </div>

        </div>



        <!-- ABSOLUTE EDITINGS -->
        <div v-if="isEditing()" class="cell-editing">

            <tablda-select-simple
                    v-if="tableHeader.field === 'access_permission_id'"
                    :options="globalPermis()"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :fixed_pos="true"
                    :embed_func_txt="'Add New'"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
                    @embed-func="showPermisPopup()"
            ></tablda-select-simple>

            <tablda-select-simple
                    v-else-if="tableHeader.field === 'col_group_id'"
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
                    v-else-if="tableHeader.field === 'row_group_id'"
                    :options="globalRowGroups()"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :can_empty="true"
                    :fixed_pos="true"
                    :embed_func_txt="'Add New'"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
                    @embed-func="showGroupsPopup('row')"
            ></tablda-select-simple>

            <tablda-select-simple
                    v-else-if="tableHeader.field === 'field_id'"
                    :options="globalFields()"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :fixed_pos="true"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
            ></tablda-select-simple>

            <tablda-select-simple
                    v-else-if="tableHeader.field === 'criteria'"
                    :options="[
                        {val: 'match', show: 'Exactly matchs'},
                        {val: 'contain', show: 'Contained In'},
                        {val: 'less', show: 'Less Than'},
                        {val: 'equal', show: 'Equal To'},
                        {val: 'more', show: 'Greater Than'},
                    ]"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :fixed_pos="true"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
            ></tablda-select-simple>

            <tablda-select-simple
                    v-else-if="inArray(tableHeader.field, ['parts_avail','parts_default'])"
                    :options="availParts()"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :fld_input_type="tableHeader.input_type"
                    :can_empty="true"
                    :fixed_pos="true"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
            ></tablda-select-simple>

            <tablda-select-simple
                    v-else-if="inArray(tableHeader.field, ['side_top','side_left_menu','side_left_filter','side_right'])"
                    :options="[
                        {val: 'na', show: 'N/A'},
                        {val: 'hidden', show: 'Hidden'},
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
                    v-else-if="inArray(tableHeader.field, ['srv_fltrs_ontop_pos'])"
                    :options="[
                        {val: 'start', show: 'Left'},
                        {val: 'center', show: 'Center'},
                        {val: 'end', show: 'Right'},
                    ]"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :fixed_pos="true"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
            ></tablda-select-simple>

            <input
                    v-else-if="tableHeader.f_type === 'String' || tableHeader.f_type === 'Password'"
                    v-model="editValue"
                    @blur="hideEdit();updateValue()"
                    ref="inline_input"
                    class="form-control full-height"
                    :disabled="!canInput"
                    :style="getEditStyle"/>

            <div v-else="">{{ hideEdit() }}</div>

        </div>
        <!-- ABSOLUTE EDITINGS -->

    </td>
</template>

<script>
import {eventBus} from './../../app';

import {SpecialFuncs} from './../../classes/SpecialFuncs';

import CellStyleMixin from '../_Mixins/CellStyleMixin.vue';

import TabldaSelectSimple from "./Selects/TabldaSelectSimple";
import EmbedButton from "../Buttons/EmbedButton";

export default {
        name: "CustomCellTableView",
        mixins: [
            CellStyleMixin,
        ],
        components: {
            EmbedButton,
            TabldaSelectSimple,
        },
        data: function () {
            return {
                editing: false,
                oldVal: null,
                editValue: null,
            }
        },
        props:{
            globalMeta: Object,
            tableMeta: Object,
            tableHeader: Object,
            tableRow: Object,
            cellHeight: Number,
            maxCellRows: Number,
            user: Object,
            isAddRow: Boolean,
            no_height_limit: Boolean,
            with_edit: {
                type: Boolean|Number,
                default: true
            },
        },
        watch: {
        },
        computed: {
            canInput() {
                return this.tableHeader.field !== 'custom_path' || this.$root.user.subdomain;
            },
            canEdit() {
                if (this.tableMeta.db_name === 'table_view_filtering') {
                    return true;
                }
                let can = !this.inArray(this.tableHeader.field, this.$root.systemFields)
                    && (this.user.id == this.tableRow.user_id || this.isAddRow);
                if (this.isAddRow) {
                    return can && (!this.inArray(this.tableHeader.field, ['_embd']))
                } else {
                    return can
                        && (!this.inArray(this.tableHeader.field, ['_embd']))
                        && (!this.inArray(this.tableHeader.field, ['user_link','_emdb','is_locked','lock_pass']) || this.tableRow.is_active)
                        && (!this.inArray(this.tableHeader.field, ['lock_pass']) || this.tableRow.is_locked);
                }
            },
            is_sel() {
                return this.$root.issel(this.tableHeader.input_type);
            },
            is_multisel() {
                return this.$root.isMSEL(this.tableHeader.input_type);
            },
        },
        methods: {
            getCustomCellStyle() {
                let obj = this.getCellStyle();
                if (this.tableHeader.field === 'lock_pass' && !this.tableRow.is_locked) {
                    obj.backgroundColor = '#EEE';
                }
                return obj;
            },
            customInnerStyle() {
                let obj = this.getWrapperStyle();
                if (this.tableHeader.field === '_embd') {
                    obj.overflow = 'visible';
                    obj.maxHeight = 'initial';
                }
                return obj;
            },
            getLink() {
                let view = this.tableRow;
                return this.$root.clear_url + '/mrv/' + view.hash;
            },
            getCustomPath() {
                let view = this.tableRow;
                return view.custom_path ? this.$root.app_url + '/mrv/' + view.custom_path : '#';
            },
            inArray(item, array) {
                return $.inArray(item, array) > -1;
            },
            isEditing: function () {
                return this.editing && this.canEdit && !this.$root.global_no_edit;
            },
            showEdit: function () {
                if (!this.canEdit || this.inArray(this.tableHeader.f_type, ['Boolean'])) {
                    return;
                }
                this.editing = true;
                if (this.isEditing()) {
                    this.oldValue = this.tableRow[this.tableHeader.field];
                    this.$nextTick(function () {
                        if (this.$refs.inline_input) {
                            this.$refs.inline_input.focus();
                        }
                    });
                } else {
                    this.editing = false;
                }
            },
            hideEdit: function () {
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
            updateValue() {
                let newVal = this.convEditValue(this.editValue, true);
                if (newVal !== this.oldValue) {
                    this.tableRow[this.tableHeader.field] = newVal;
                    this.tableRow._changed_field = this.tableHeader.field;
                    if (this.tableHeader.field === 'side_left_filter' && newVal === 'show') {
                        this.tableRow['srv_fltrs_on_top'] = false;
                    }
                    if (this.tableHeader.field === 'srv_fltrs_on_top' && newVal === true) {
                        this.tableRow['side_left_filter'] = 'na';
                    }
                    this.$emit('updated-cell', this.tableRow);
                }
            },

            //convert
            convEditValue(res, reverse) {
                if (this.tableHeader.field === 'custom_path') {
                    res = res ? _.last(String(res).split('/')) : null;
                }

                if (!reverse && this.tableHeader.field === 'access_permission_id' && this.isAddRow) {
                    res = this.tableRow.access_permission_id = this.getVisitorPermis();
                }
                if (!reverse && this.tableHeader.field === 'parts_avail' && this.isAddRow) {
                    res = this.tableRow.parts_avail = '["tab-list-view","tab-settings","tab-map-add","tab-bi-add","tab-dcr-add"]';
                }

                if (this.is_multisel) {
                    if (reverse) {
                        return (Array.isArray(res) ? JSON.stringify(this.filterParts(res)) : res);
                    } else {
                        return this.filterParts( this.$root.parseMsel(res) );
                    }
                }
                return res;
            },
            getVisitorPermis() {
                let permis = _.find(this.globalMeta._table_permissions, {is_system: 1});
                if (!permis) {
                    permis = this.globalMeta._table_permissions[0];
                }
                return permis.id;
            },

            //show
            showParts() {
                let res = [];
                if (this.inArray(this.tableHeader.field, ['parts_avail','parts_default'])) {
                    let vals = SpecialFuncs.parseMsel(this.tableRow[this.tableHeader.field]);
                    vals = this.tableHeader.field === 'parts_avail' ? this.filterParts(vals) : vals;
                    _.each(vals, (el) => {
                        res.push(this.$root.getViewPartOption(el));
                    });
                }
                return res;
            },
            showField() {
                let res = '';
                if (this.tableHeader.f_type === 'Password' && this.tableRow[this.tableHeader.field]) {
                    res = '*'.repeat( this.tableRow[this.tableHeader.field].length );
                }
                else
                if (this.tableHeader.field === 'access_permission_id') {
                    let tb_perm = _.find(this.globalMeta._table_permissions, {id: Number(this.tableRow.access_permission_id)});
                    res = tb_perm ? tb_perm.name : '';
                }
                else
                if (this.tableHeader.field === 'col_group_id') {
                    let col_gr = _.find(this.globalMeta._column_groups, {id: Number(this.tableRow.col_group_id)});
                    res = col_gr ? col_gr.name : '';
                }
                else
                if (this.tableHeader.field === 'row_group_id') {
                    let row_gr = _.find(this.globalMeta._row_groups, {id: Number(this.tableRow.row_group_id)});
                    res = row_gr ? row_gr.name : '';
                }
                else
                if (this.tableHeader.field === 'field_id') {
                    let fld = _.find(this.globalMeta._fields, {id: Number(this.tableRow.field_id)});
                    res = fld ? fld.name : '';
                }
                else
                if (this.tableHeader.field === 'lock_pass' && this.tableRow.lock_pass) {
                    res = this.globalMeta._is_owner ? this.tableRow.lock_pass : String(this.tableRow.lock_pass).replace(/./g, '*');
                }
                else
                if (this.inArray(this.tableHeader.field, ['side_top','side_left_menu','side_left_filter','side_right'])) {
                    switch (this.tableRow[this.tableHeader.field]) {
                        case 'na': res = 'N/A'; break;
                        case 'hidden': res = 'Hidden'; break;
                        case 'show': res = 'Show'; break;
                    }
                }
                else
                if (this.inArray(this.tableHeader.field, ['criteria'])) {
                    switch (this.tableRow[this.tableHeader.field]) {
                        case 'match': res = 'Exactly matchs'; break;
                        case 'contain': res = 'Contained In'; break;
                        case 'less': res = 'Less Than'; break;
                        case 'equal': res = 'Equal To'; break;
                        case 'more': res = 'Greater Than'; break;
                    }
                }
                else
                if (this.inArray(this.tableHeader.field, ['srv_fltrs_ontop_pos'])) {
                    switch (this.tableRow[this.tableHeader.field]) {
                        case 'start': res = 'Start'; break;
                        case 'center': res = 'Center'; break;
                        case 'end': res = 'Right'; break;
                    }
                }
                else {
                    res = this.editValue;
                }
                return this.$root.strip_danger_tags(res);
            },

            //additional popups
            showPermisPopup() {
                eventBus.$emit('show-permission-settings-popup', this.globalMeta.db_name);
            },
            showinlinePermisPopup() {
                eventBus.$emit('show-permission-settings-popup', this.globalMeta.db_name, this.tableRow.access_permission_id);
            },
            showGroupsPopup(type) {
                eventBus.$emit('show-grouping-settings-popup', this.globalMeta.db_name, type);
            },
            showinlineGroupsPopup() {
                let type = '';
                switch (this.tableHeader.field) {
                    case 'col_group_id': type = 'col'; break;
                    case 'row_group_id': type = 'row'; break;
                }
                if (type) {
                    eventBus.$emit('show-grouping-settings-popup', this.globalMeta.db_name, type, this.editValue);
                }
            },

            //ddls
            globalPermis() {
                return _.map(this.globalMeta._table_permissions, (tp) => {
                    return { val: tp.id, show: tp.name, }
                });
            },
            globalColGroups() {
                return _.map(this.globalMeta._column_groups, (cg) => {
                    return { val: cg.id, show: cg.name, }
                });
            },
            globalRowGroups() {
                return _.map(this.globalMeta._row_groups, (rg) => {
                    return { val: rg.id, show: rg.name, }
                });
            },
            globalFields() {
                return _.map(this.globalMeta._fields, (rg) => {
                    return { val: rg.id, show: rg.name, }
                });
            },
            filterParts(parts) {
                let notavail = [];
                if (this.tableHeader.field === 'parts_avail') {
                    if (!this.globalMeta.add_bi) {
                        notavail.push('tab-bi-add');
                    }
                    if (!this.globalMeta.add_map) {
                        notavail.push('tab-map-add');
                    }
                    if (!this.globalMeta.add_request) {
                        notavail.push('tab-dcr-add');
                    }
                    if (!this.globalMeta.add_alert) {
                        notavail.push('tab-alert-add');
                    }
                    if (!this.globalMeta.add_kanban) {
                        notavail.push('tab-kanban-add');
                    }
                    if (!this.globalMeta.add_gantt) {
                        notavail.push('tab-gantt-add');
                    }
                    if (!this.globalMeta.add_email) {
                        notavail.push('tab-email-add');
                    }
                    if (!this.globalMeta.add_calendar) {
                        notavail.push('tab-calendar-add');
                    }
                    if (!this.globalMeta.add_twilio) {
                        notavail.push('tab-twilio-add');
                    }
                    if (!this.globalMeta.add_tournament) {
                        notavail.push('tab-tournament-add');
                    }
                    if (!this.globalMeta.add_simplemap) {
                        notavail.push('tab-simplemap-add');
                    }
                    if (!this.globalMeta.add_grouping) {
                        notavail.push('tab-grouping-add');
                    }
                    if (!this.globalMeta.add_report) {
                        notavail.push('tab-report-add');
                    }
                    if (!this.globalMeta.add_ai) {
                        notavail.push('tab-ai-add');
                    }
                }
                return _.filter(parts, (el) => {
                    return notavail.indexOf(el) === -1;
                });
            },
            availParts() {
                let parts = [
                    {val: 'tab-list-view', show: 'Primary View'},
                    {val: 'tab-favorite', show: 'Favorite'},
                    {val: '', show: 'Settings:', isTitle: true, disabled: true},
                    {val: 'tab-settings', html: '&nbsp;&nbsp;&nbsp;General', show: 'General'},
                    {val: 'tab-settings-cust', html: '&nbsp;&nbsp;&nbsp;Customizable', show: 'Customizable'},
                ];
                if (this.$root.anyAddon(this.globalMeta)) {
                    parts.push({val: '', show: 'Addons:', isTitle: true, disabled: true});
                }
                if (this.globalMeta.add_bi) {
                    parts.push({val: 'tab-bi-add', html: '&nbsp;&nbsp;&nbsp;BI', show: 'BI'});
                }
                if (this.globalMeta.add_map) {
                    parts.push({val: 'tab-map-add', html: '&nbsp;&nbsp;&nbsp;GSI', show: 'GSI'});
                }
                if (this.globalMeta.add_request) {
                    parts.push({val: 'tab-dcr-add', html: '&nbsp;&nbsp;&nbsp;DCR', show: 'DCR'});
                }
                if (this.globalMeta.add_alert) {
                    parts.push({val: 'tab-alert-add', html: '&nbsp;&nbsp;&nbsp;ANA', show: 'ANA'});
                }
                if (this.globalMeta.add_kanban) {
                    parts.push({val: 'tab-kanban-add', html: '&nbsp;&nbsp;&nbsp;Kanban', show: 'Kanban'});
                }
                if (this.globalMeta.add_gantt) {
                    parts.push({val: 'tab-gantt-add', html: '&nbsp;&nbsp;&nbsp;Gantt', show: 'Gantt'});
                }
                if (this.globalMeta.add_email) {
                    parts.push({val: 'tab-email-add', html: '&nbsp;&nbsp;&nbsp;Email', show: 'Email'});
                }
                if (this.globalMeta.add_calendar) {
                    parts.push({val: 'tab-calendar-add', html: '&nbsp;&nbsp;&nbsp;Calendar', show: 'Calendar'});
                }
                if (this.globalMeta.add_twilio) {
                    parts.push({val: 'tab-twilio-add', html: '&nbsp;&nbsp;&nbsp;Twilio', show: 'Twilio'});
                }
                if (this.globalMeta.add_tournament) {
                    parts.push({val: 'tab-tournament-add', html: '&nbsp;&nbsp;&nbsp;Brackets', show: 'Brackets'});
                }
                if (this.globalMeta.add_grouping) {
                    parts.push({val: 'tab-grouping-add', html: '&nbsp;&nbsp;&nbsp;Grouping', show: 'Grouping'});
                }
                if (this.globalMeta.add_report) {
                    parts.push({val: 'tab-report-add', html: '&nbsp;&nbsp;&nbsp;Report', show: 'Report'});
                }
                if (this.globalMeta.add_ai) {
                    parts.push({val: 'tab-ai-add', html: '&nbsp;&nbsp;&nbsp;AI', show: 'AI'});
                }
                if (this.globalMeta.add_simplemap) {
                    parts.push({val: 'tab-simplemap-add', html: '&nbsp;&nbsp;&nbsp;TMap', show: 'TMap'});
                }
                return parts;
            },
        },
        mounted() {
            this.editValue = this.convEditValue(this.tableRow[this.tableHeader.field]);
        },
    }
</script>

<style lang="scss" scoped>
    @import "CustomCell.scss";

    .btn-deletable {
        position: absolute;
        top: 10%;
        right: 5%;
        bottom: 10%;
        padding: 0 3px;

        span {
            font-size: 1.4em;
            line-height: 0.7em;
            display: inline-block;
        }
    }
</style>