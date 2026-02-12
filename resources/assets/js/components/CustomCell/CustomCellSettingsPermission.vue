<template>
    <td :style="getCustomCellStyle"
        class="td-custom"
        ref="td"
        @click="showEdit()"
    >
        <!-- Request tables -->
        <button
                v-if="tableHeader.field === '_dv' && !isAddRow"
                :disabled="disabledPermis"
                class="btn btn-sm btn-default"
                @click="$emit('show-def-val-popup', tableRow)"
                :style="textStyle"
        >DV</button>
        <!-- Request tables -->

        <div v-else="" class="td-wrapper" :style="getTdWrappStyle()">

            <div class="wrapper-inner" :style="getWrapperStyle()">
                <div class="inner-content">

                    <!-- View / Edit Checkbox -->
                    <span v-if="inArray(tableHeader.field, ['view','edit','delete'])" class="indeterm_check__wrap">
                        <span v-if="!isAddRow"
                              class="indeterm_check"
                              :class="{'disabled': !availableCheck(tableHeader.field) || !with_edit}"
                              ref="inline_input"
                              @click.prevent="!availableCheck(tableHeader.field) || !with_edit ? null : updateValue(true)"
                        >
                            <i v-if="tableRow[tableHeader.field]" class="glyphicon glyphicon-ok group__icon"></i>
                        </span>
                    </span>

                    <!-- Folder/Share: Status, Is App - Checkbox -->
                    <label class="switch_t"
                           v-else-if="inArray(tableHeader.field, ['is_f_active','is_f_apps'])"
                           :style="{height: Math.min(maxCellHGT, 17)+'px'}"
                    >
                        <input type="checkbox" :disabled="!(with_edit && hasCheckedTables && !tableRow.is_system)" v-model="tableRow[tableHeader.field]" @change="updateValue()">
                        <span class="toggler round" :class="[with_edit && hasCheckedTables && !tableRow.is_system ? '' : 'disabled']"></span>
                    </label>

                    <label class="switch_t"
                           v-else-if="tableHeader.f_type === 'Boolean'"
                           :style="{height: Math.min(maxCellHGT, 17)+'px'}"
                    >
                        <input type="checkbox" :disabled="!with_edit" v-model="tableRow[tableHeader.field]" @change="updateValue()">
                        <span class="toggler round" :class="[with_edit ? '' : 'disabled']"></span>
                    </label>

                    <a v-else-if="tableHeader.field === 'table_column_group_id'"
                       title="Open column group in popup."
                       @click.stop="showGroupsPopup('col', tableRow.table_column_group_id)"
                    >{{ showField() }}</a>

                    <a v-else-if="tableHeader.field === 'table_row_group_id'"
                       title="Open row group in popup."
                       @click.stop="showGroupsPopup('row', tableRow.table_row_group_id)"
                    >{{ showField() }}</a>

                    <a v-else-if="tableHeader.field === 'user_group_id'"
                       title="Open usergroup settings in popup."
                       @click.stop="showUsergroupPopup(tableRow.user_group_id)"
                    >{{ showField() }}</a>

                    <div class="content" v-else="">{{ showField() }}</div>

                </div>
            </div>

        </div>


        <!-- ABSOLUTE EDITINGS -->

        <div v-if="isEditing()" class="cell-editing">

            <input  v-if="inArray(tableHeader.field, ['name', 'notes'])"
                    v-model="tableRow[tableHeader.field]"
                    @blur="hideEdit();updateValue()"
                    ref="inline_input"
                    class="form-control full-height"
                    :style="getEditStyle">

            <tablda-select-simple
                    v-else-if="tableHeader.field === 'user_group_id' && isAddRow"
                    :options="globalUserGroups()"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :fixed_pos="true"
                    :embed_func_txt="'Add New'"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
                    @embed-func="showUsergroupPopup()"
            ></tablda-select-simple>

            <tablda-select-simple
                    v-else-if="tableHeader.field === 'table_permission_id' && isAddRow"
                    :options="globalPermissions()"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :fixed_pos="true"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
            ></tablda-select-simple>

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

            <tablda-select-simple
                    v-else-if="tableHeader.field === 'table_row_group_id' && isAddRow"
                    :options="globalRowGroups()"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :fixed_pos="true"
                    :embed_func_txt="'Add New'"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
                    @embed-func="showGroupsPopup('row')"
            ></tablda-select-simple>

            <div v-else="">{{ hideEdit() }}</div>

        </div>
        <!-- ABSOLUTE EDITINGS -->

    </td>
</template>

<script>
import {eventBus} from '../../app';

import CellStyleMixin from '../_Mixins/CellStyleMixin.vue';

import TabldaSelectSimple from "./Selects/TabldaSelectSimple";

export default {
        name: "CustomCellSettingsPermission",
        components: {
            TabldaSelectSimple,
        },
        mixins: [
            CellStyleMixin,
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
            allRows: Object|null,
            cellHeight: Number,
            maxCellRows: Number,
            isAddRow: Boolean,
            behavior: String,
            user: Object,
            with_edit: {
                type: Boolean,
                default: true
            },
        },
        computed: {
            hasCheckedTables() {
                return this.tableRow._checked_tables && this.tableRow._checked_tables.length;
            },
            getCustomCellStyle() {
                let obj = this.getCellStyle();
                obj.textAlign = this.inArray(this.tableHeader.field, ['view','edit','delete','_dv']) ? 'center' : '';
                if (!this.isAddRow && this.behavior === 'folder_permission_group' && !this.hasCheckedTables) {
                    obj.backgroundColor = '#DFD';
                }
                return obj;
            },
            disabledPermis() {
                let permis = _.find(this.globalMeta._table_permissions, {id: this.tableRow.table_permission_id});
                return !(permis && permis.can_add);
            },
            canCellEdit() {
                return this.with_edit
                    && this.globalMeta._is_owner
                    && !this.inArray(this.tableHeader.field, this.$root.systemFields)
                    && (this.behavior !== 'folder_permission_group' || !this.tableRow.is_system);
            },
        },
        methods: {
            availableCheck(field) {
                return this.user.is_admin
                    || (this.behavior === 'permission_group_col' && field === 'view' && this.$root.checkAvailable(this.user, 'permission_col_view'))
                    || (this.behavior === 'permission_group_col' && field === 'edit' && this.$root.checkAvailable(this.user, 'permission_col_edit'))
                    || (this.behavior === 'permission_group_row' && field === 'view' && this.$root.checkAvailable(this.user, 'permission_row_view'))
                    || (this.behavior === 'permission_group_row' && field === 'edit' && this.$root.checkAvailable(this.user, 'permission_row_edit'))
                    || (this.behavior === 'permission_group_row' && field === 'delete' && this.$root.checkAvailable(this.user, 'permission_row_del'));
            },
            inArray(item, array) {
                return $.inArray(item, array) > -1;
            },
            notPresent(item) {
                return _.findIndex(this.allRows, (el) => {
                        return el[this.tableHeader.field] == item.id && el.view == 1;
                    }) === -1;
            },
            notPresentUG(item) {
                return _.findIndex(this.allRows, (el) => {
                        return el.user_group_id == item.id;
                    }) === -1;
            },
            isEditing: function () {
                return this.editing && this.canCellEdit && !this.$root.global_no_edit;
            },
            showEdit: function () {
                if (!this.canCellEdit
                    || this.inArray(this.tableHeader.field, ['is_apps'])
                    || (this.inArray(this.tableHeader.field, ['view', 'edit', 'delete', '_dv']) && !this.isAddRow)
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
            showField() {
                let res = '';

                if (this.isAddRow && this.inArray(this.tableHeader.field, this.$root.systemFields)) {
                    res = 'Auto'
                }
                else
                if (this.tableHeader.f_type === 'User') {
                    res = this.$root.smallUserStr(this.tableRow, this.tableHeader, this.tableRow[this.tableHeader.field]);
                }
                else
                if (this.inArray(this.tableHeader.field, ['table_column_group_id'])) {
                    let group = _.find(this.globalMeta._column_groups, {id: Number(this.tableRow.table_column_group_id)});
                    res = group ? group.name : this.tableRow.name;
                }
                else
                if (this.inArray(this.tableHeader.field, ['table_row_group_id'])) {
                    let group = _.find(this.globalMeta._row_groups, {id: Number(this.tableRow.table_row_group_id)});
                    res = group ? group.name : this.tableRow.name;
                }
                else
                if (this.inArray(this.tableHeader.field, ['user_group_id'])) {
                    let group = _.find(this.user._user_groups, {id: Number(this.tableRow.user_group_id)});
                    group = group || _.find(this.user._sys_user_groups, {id: Number(this.tableRow.user_group_id)});
                    res = group ? group.name : this.tableRow.user_group_id;
                }
                else
                if (this.inArray(this.tableHeader.field, ['table_permission_id'])) {
                    let group = _.find(this.globalMeta._table_permissions, {id: Number(this.tableRow.table_permission_id)});
                    res = group ? group.name : this.tableRow.table_permission_id;
                }
                else {
                    res = this.tableRow[this.tableHeader.field];
                }

                return this.$root.strip_danger_tags(res);
            },
            showGroupsPopup(type, id) {
                eventBus.$emit('show-grouping-settings-popup', this.globalMeta.db_name, type, id);
            },
            showUsergroupPopup(id) {
                let idx = _.findIndex(this.user._user_groups, {id: id});
                eventBus.$emit('open-resource-popup', 'users', idx);
            },

            //arrays for selects
            globalUserGroups() {
                let fields = _.filter(this.user._user_groups, (usr_gr) => { return this.notPresentUG(usr_gr) });
                return _.map(fields, (ug) => {
                    return { val: ug.id, show: ug.name, }
                });
            },
            globalColGroups() {
                let fields = _.filter(this.globalMeta._column_groups, (col_gr) => { return this.notPresent(col_gr) });
                return _.map(fields, (cg) => {
                    return { val: cg.id, show: cg.name, }
                });
            },
            globalRowGroups() {
                let fields = _.filter(this.globalMeta._row_groups, (row_gr) => { return this.notPresent(row_gr) });
                return _.map(fields, (rg) => {
                    return { val: rg.id, show: rg.name, }
                });
            },
            globalPermissions() {
                return _.map(this.globalMeta._table_permissions, (tp) => {
                    return { val: tp.id, show: tp.name, }
                });
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