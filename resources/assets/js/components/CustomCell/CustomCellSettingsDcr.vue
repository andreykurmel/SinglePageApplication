<template>
    <td :style="getCustomCellStyle"
        class="td-custom"
        ref="td"
        @click="showEdit()"
    >

        <div class="td-wrapper" :style="getTdWrappStyle">

            <div class="wrapper-inner" :style="getWrapperStyle">
                <div class="inner-content">

                    <!-- View / Edit Checkbox -->
                    <span v-if="inArray(tableHeader.field, ['view','edit','delete'])" class="indeterm_check__wrap">
                        <span v-if="!isAddRow"
                              class="indeterm_check"
                              :class="{'disabled': !with_edit}"
                              ref="inline_input"
                              @click.prevent="!with_edit ? null : updateValue(true)"
                        >
                            <i v-if="tableRow[tableHeader.field]" class="glyphicon glyphicon-ok group__icon"></i>
                        </span>
                    </span>

                    <label class="switch_t"
                           v-else-if="tableHeader.f_type === 'Boolean'"
                           :style="{height: Math.min(maxCellHGT, 17)+'px'}"
                    >
                        <input type="checkbox" :disabled="!with_edit" v-model="tableRow[tableHeader.field]" @change="updateValue()">
                        <span class="toggler round" :class="[with_edit ? '' : 'disabled']"></span>
                    </label>

                    <div v-if="tableHeader.field === 'linked_table_id'" class="inner-content">
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

                    <a v-else-if="inArray(tableHeader.field, ['linked_permission_id'])"
                       title="Open the source table in a new tab."
                       :href="showField('__url')"
                    >{{ showField() }}</a>

                    <a v-else-if="inArray(tableHeader.field, ['table_column_group_id'])"
                       title="Open row/column group in popup."
                       @click.stop="showGroupsPopup('col', tableRow[tableHeader.field])"
                    >{{ showField() }}</a>

                    <a v-else-if="!isAddRow && inArray(tableHeader.field, ['name'])"
                       title="Open the DCR in a new tab."
                       :disabled="!tableRow.active"
                       :target="(tableRow.active ? '_blank' : '')"
                       :href="(tableRow.active ? getLinkHash() : '#')"
                    >{{ showField() }}</a>

                    <div class="content" v-else="">{{ showField() }}</div>

                </div>
            </div>

        </div>


        <!-- ABSOLUTE EDITINGS -->
        <div v-if="isEditing()" class="cell-editing">

            <!-- Request tables -->
            <input  v-if="inArray(tableHeader.field, ['name', 'pass', 'row_request', 'user_link', 'header'])"
                    v-model="tableRow[tableHeader.field]"
                    @blur="hideEdit();updateValue()"
                    ref="inline_input"
                    class="form-control full-height"
                    :style="getEditStyle">

            <tablda-select-simple
                v-else-if="tableHeader.field === 'table_column_group_id' && isAddRow"
                :options="globalColGroups()"
                :table-row="tableRow"
                :hdr_field="tableHeader.field"
                :fixed_pos="true"
                :embed_func_txt="'Add New'"
                :style="getEditStyle"
                @selected-item="updateCheckedDDL"
                @hide-select="hideEdit"
                @embed-func="showGroupsPopup('col')"
            ></tablda-select-simple>

            <!-- LINKED DCR -->
            <select-with-folder-structure
                    v-else-if="tableHeader.field === 'linked_table_id'"
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
                    v-else-if="tableHeader.field === 'passed_ref_cond_id'"
                    :options="linkRefConds()"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :can_empty="true"
                    :fixed_pos="true"
                    :embed_func_txt="'Add New'"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
            ></tablda-select-simple>

            <tablda-select-simple
                    v-else-if="tableHeader.field === 'position_field_id'"
                    :options="linkFields()"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :fixed_pos="true"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
            ></tablda-select-simple>

            <tablda-select-simple
                    v-else-if="tableHeader.field === 'position'"
                    :options="[
                            {show: 'After', val: 'After'},
                        ]"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :fixed_pos="true"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
            ></tablda-select-simple>

            <tablda-select-simple
                    v-else-if="tableHeader.field === 'style'"
                    :options="[
                            {show: 'Default', val: 'Default'},
                            {show: 'Top/Bot', val: 'Top/Bot'},
                        ]"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :fixed_pos="true"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
            ></tablda-select-simple>

            <tablda-select-simple
                v-else-if="tableHeader.field === 'linked_permission_id'"
                :options="linkPermissions()"
                :table-row="tableRow"
                :hdr_field="tableHeader.field"
                :can_empty="true"
                :fixed_pos="true"
                :style="getEditStyle"
                @selected-item="updateCheckedDDL"
                @hide-select="hideEdit"
            ></tablda-select-simple>

            <div v-else="">{{ hideEdit() }}</div>

        </div>
        <!-- ABSOLUTE EDITINGS -->

    </td>
</template>

<script>
import {eventBus} from '../../app';

import CellStyleMixin from '../_Mixins/CellStyleMixin.vue';

import EmbedButton from "../Buttons/EmbedButton.vue";
import TabldaSelectSimple from "./Selects/TabldaSelectSimple";
import SelectWithFolderStructure from "./InCell/SelectWithFolderStructure";

export default {
        name: "CustomCellSettingsDcr",
        components: {
            SelectWithFolderStructure,
            TabldaSelectSimple,
            EmbedButton
        },
        mixins: [
            CellStyleMixin
        ],
        data: function () {
            return {
                editing: false,
                compared_values: []
            }
        },
        props:{
            globalMeta: {
                type: Object,
                default: function () {
                    return {};
                }
            },
            tableMeta: Object,//style mixin
            tableHeader: Object,
            tableRow: Object,
            cellHeight: Number,
            maxCellRows: Number,
            isAddRow: Boolean,
            behavior: String,
            allRows: Object|null,
            user: Object,
            with_edit: {
                type: Boolean,
                default: true
            },
        },
        computed: {
            getCustomCellStyle() {
                let obj = this.getCellStyle;
                obj.textAlign = this.inArray(this.tableHeader.field, ['view','edit']) ? 'center' : '';
                return obj;
            },
            canCellEdit() {
                return this.with_edit
                    && this.globalMeta._is_owner
                    && !this.inArray(this.tableHeader.field, this.$root.systemFields);
            },
            linkTb() {
                return _.find(this.$root.settingsMeta.available_tables, {id: Number(this.tableRow.linked_table_id)});
            },
        },
        methods: {
            inArray(item, array) {
                return $.inArray(item, array) > -1;
            },
            notPresent(item) {
                return _.findIndex(this.allRows, (el) => {
                  return el[this.tableHeader.field] == item.id && el.view == 1;
                }) === -1;
            },
            isEditing: function () {
                return this.editing && this.canCellEdit && !this.$root.global_no_edit;
            },
            showEdit: function () {
                if (!this.canCellEdit
                    || this.inArray(this.tableHeader.field, ['active', 'one_per_submission'])
                    || (this.inArray(this.tableHeader.field, ['view', 'edit']) && !this.isAddRow)
                ) {
                    return;
                }
                //edit cell
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
            hideEdit() {
                this.editing = false;
            },
            updateValue(passed) {
                if (passed) {
                    this.tableRow[this.tableHeader.field] = !this.tableRow[this.tableHeader.field];
                }
                if (this.tableRow[this.tableHeader.field] !== this.oldValue || passed) {
                    this.$emit('updated-cell', this.tableRow);
                }
            },
            updateCheckedDDL(item) {
                this.tableRow[this.tableHeader.field] = item;
                this.updateValue();
            },
            showField(link) {
                let res = '';

                if (this.isAddRow && this.inArray(this.tableHeader.field, this.$root.systemFields)) {
                    res = 'Auto'
                }
                else
                if (this.inArray(this.tableHeader.field, ['table_column_group_id'])) {
                    let group = _.find(this.globalMeta._column_groups, {id: Number(this.tableRow.table_column_group_id)});
                    res = group ? group.name : this.tableRow.name;
                }
                else
                if (this.inArray(this.tableHeader.field, ['linked_table_id'])) {
                    if (this.linkTb) {
                        res = link
                            ? this.linkTb[link]
                            : this.linkTb.name;
                    } else {
                        res = this.tableRow.linked_table_id;
                    }
                }
                else
                if (this.inArray(this.tableHeader.field, ['linked_table_id'])) {
                    res = this.linkTb ? this.linkTb.name : this.tableRow.linked_table_id;
                }
                else
                if (this.inArray(this.tableHeader.field, ['linked_permission_id'])) {
                    let permis = _.find(this.linkTb ? this.linkTb._table_permissions : [], {id: Number(this.tableRow.linked_permission_id)});
                    res = link && this.linkTb
                        ? this.linkTb[link]
                        : (permis ? permis.name : this.tableRow.linked_permission_id);
                }
                else
                if (this.inArray(this.tableHeader.field, ['passed_ref_cond_id'])) {
                    let rc = _.find(this.globalMeta._ref_conditions, {id: Number(this.tableRow.passed_ref_cond_id)});
                    res = rc ? rc.name : this.tableRow.passed_ref_cond_id;
                }
                else
                if (this.inArray(this.tableHeader.field, ['position_field_id'])) {
                    let fld = _.find(this.globalMeta._fields || [], {id: Number(this.tableRow.position_field_id)});
                    res = fld ? fld.name : this.tableRow.position_field_id;
                }
                else {
                    res = this.tableRow[this.tableHeader.field];
                }

                return this.$root.strip_tags(res);
            },
            getLinkHash() {
                let domain = this.$root.clear_url + '/dcr/';
                return this.tableRow.link_hash ? domain+this.tableRow.link_hash : '#';
            },
            showGroupsPopup(type, id) {
                eventBus.$emit('show-grouping-settings-popup', this.globalMeta.db_name, type, id);
            },
            showUsergroupPopup(id) {
                let idx = _.findIndex(this.user._user_groups, {id: id});
                eventBus.$emit('open-resource-popup', 'users', idx);
            },
            
            //SELECTS
            globalColGroups() {
                let fields = _.filter(this.globalMeta._column_groups, (col_gr) => { return this.notPresent(col_gr) });
                return _.map(fields, (cg) => {
                    return { val: cg.id, show: cg.name, }
                });
            },
            //linked tables
            linkRefConds() {
                let avail = _.filter(this.globalMeta._ref_conditions, (rc) => {
                    return rc.ref_table_id == this.tableRow.linked_table_id;
                });
                return _.map(avail, (rc) => {
                    return { val: rc.id, show: rc.name, }
                });
            },
            linkPermissions() {
                return _.map(this.linkTb ? this.linkTb._table_permissions : [], (permis) => {
                    return { val: permis.id, show: permis.name, }
                });
            },
            linkFields() {
                let fields = this.globalMeta
                    ? _.filter(this.globalMeta._fields, (hdr) => { return !this.inArray(hdr.field, this.$root.systemFields) })
                    : [];
                return _.map(fields, (hdr) => {
                    return { val: hdr.id, show: this.$root.uniqName(hdr.name), }
                });
            },
            refIsOwner() {
                let tb = _.find(this.$root.settingsMeta.available_tables, {id: Number(this.tableRow.linked_table_id)});
                return tb && tb.user_id == this.user.id;
            },
        },
        mounted() {
        }
    }
</script>

<style lang="scss" scoped>
    @import "CustomCell.scss";

    .td-custom {
        .btn-default {
            padding: 0 9px;
        }

        .embed__btn {
            width: 44px;
            height: auto;
            user-select: auto;
        }
    }
</style>