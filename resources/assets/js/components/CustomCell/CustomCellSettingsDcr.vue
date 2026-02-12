<template>
    <td :style="getCustomCellStyle"
        class="td-custom"
        ref="td"
        @click="showEdit()"
    >

        <div class="td-wrapper" :style="getTdWrappStyle()">

            <div v-if="tableHeader.field === 'link_hash'" class="flex flex--center">
                <embed-button v-if="!isAddRow"
                              class="embed_button btn btn-default embed__btn"
                              :is-disabled="!tableRow.active"
                              :is-dcr="true"
                              :is-fixed="true"
                              :hash="tableRow.link_hash || '#'"
                              :style="textStyle"
                ></embed-button>
            </div>

            <div v-else class="wrapper-inner" :style="getWrapperStyle()">
                <div class="inner-content" :style="getInnerStyle()">

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
                           v-else-if="tableMeta.db_name === 'table_data_requests_2_table_fields' && tableHeader.f_type === 'Boolean'"
                           :style="{height: Math.min(maxCellHGT, 17)+'px'}"
                    >
                        <input type="checkbox" :checked="pivotChecked" :disabled="!with_edit" @click="pivotClick">
                        <span class="toggler round" :class="{'disabled': !with_edit}"></span>
                    </label>

                    <label class="switch_t"
                           v-else-if="tableHeader.f_type === 'Boolean'"
                           :style="{height: Math.min(maxCellHGT, 17)+'px'}"
                    >
                        <input type="checkbox" :disabled="!with_edit" v-model="tableRow[tableHeader.field]" @change="updateValue()">
                        <span class="toggler round" :class="[with_edit ? '' : 'disabled']"></span>
                    </label>

                    <div v-else-if="inArray(tableHeader.field, ['linked_table_id','ctlg_table_id'])" class="inner-content">
                        <a target="_blank"
                           title="Open the “Visiting” MRV in a new tab."
                           :href="showField('__visiting_url')"
                           @click.stop=""
                           v-html="showField()"></a>
                        <a v-if="refIsOwner()"
                           title="Open the source table in a new tab."
                           target="_blank"
                           :href="showField('__url')"
                           @click.stop="">(Table)</a>
                    </div>

                    <a v-else-if="tableHeader.field === 'ctlg_data_range'"
                       title="Open data range in popup."
                       @click.stop="showRG(tableRow[tableHeader.field], globalMeta)"
                    >{{ showField() }}</a>

                    <div v-else-if="inArray(tableHeader.field, ['ctlg_visible_field_ids', 'ctlg_filter_field_ids'])">
                        <span v-for="el in showVisFields()" :class="{'is_select': is_sel, 'm_sel__wrap': is_multisel}">
                            <span v-html="el.show"></span>
                            <span v-if="is_sel && is_multisel && with_edit"
                                  class="m_sel__remove"
                                  @click.prevent.stop=""
                                  @mousedown.prevent.stop="updateCheckedDDL(el.val)"
                                  @mouseup.prevent.stop=""
                            >&times;</span>
                        </span>
                    </div>

                    <a v-else-if="inArray(tableHeader.field, ['linked_permission_id'])"
                       title="Open permissions in popup."
                       @click.stop="showPermis()"
                    >{{ showField() }}</a>

                    <a v-else-if="inArray(tableHeader.field, ['related_col_group_id'])"
                       title="Open column group in popup."
                       @click.stop="showRelColGroup()"
                    >{{ showField() }}</a>

                    <a v-else-if="inArray(tableHeader.field, ['permission_dcr_id'])"
                       title="Open permissions in popup."
                       @click.stop="showCurrentPermis()"
                    >{{ showField() }}</a>

                    <a v-else-if="inArray(tableHeader.field, ['passed_ref_cond_id'])"
                       title="Open RC in popup."
                       @click.stop="showRCP(tableRow[tableHeader.field])"
                    >{{ showField() }}</a>

                    <a v-else-if="inArray(tableHeader.field, ['table_column_group_id'])"
                       title="Open row/column group in popup."
                       @click.stop="showGroupsPopup('col', tableRow[tableHeader.field])"
                    >{{ showField() }}</a>

                    <a v-else-if="inArray(tableHeader.field, ['link_id'])"
                       title="Open link in popup."
                       @click.stop="showLinksPopup()"
                    >{{ showField() }}</a>

                    <a v-else-if="!isAddRow && inArray(tableHeader.field, ['name'])"
                       title="Open the DCR in a new tab."
                       :disabled="!tableRow.active"
                       :target="(tableRow.active ? '_blank' : '')"
                       :href="(tableRow.active ? getLinkHash() : '#')"
                    >{{ showField() }}</a>

                    <a v-else-if="!isAddRow && inArray(tableHeader.field, ['custom_url'])"
                       title="Open the DCR in a new tab."
                       :disabled="!tableRow.active"
                       :target="(tableRow.active ? '_blank' : '')"
                       :href="(tableRow.active ? getCustomPath() : '#')"
                    >{{ showField() }}</a>

                    <div class="content" v-else="">{{ showField() }}</div>

                </div>
            </div>

        </div>


        <!-- ABSOLUTE EDITINGS -->
        <div v-if="isEditing()" class="cell-editing">

            <!-- Request tables -->
            <input  v-if="inArray(tableHeader.field, ['name', 'pass', 'row_request', 'user_link', 'header', 'description', 'custom_url'])"
                    v-model="tableRow[tableHeader.field]"
                    @blur="hideEdit();updateValue()"
                    ref="inline_input"
                    class="form-control full-height"
                    :disabled="!canInput"
                    :style="getEditStyle">

            <input  v-else-if="inArray(tableHeader.field, ['dcr_section_name']) && neededPivot"
                    v-model="neededPivot[tableHeader.field]"
                    @blur="hideEdit();updatePivot()"
                    ref="inline_input"
                    class="form-control full-height"
                    :disabled="!canInput"
                    :style="getEditStyle">

            <tablda-select-simple
                v-else-if="inArray(tableHeader.field, ['board_image_fld_id'])"
                :options="globalSelFields()"
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
                v-else-if="tableHeader.field === 'width_of_table_popup'"
                :options="tableWidths()"
                :table-row="neededPivot || tableRow"
                :hdr_field="tableHeader.field"
                :fixed_pos="true"
                :style="getEditStyle"
                @selected-item="updateCheckedDDL"
                @hide-select="hideEdit"
            ></tablda-select-simple>

            <!-- LINKED DCR -->
            <select-with-folder-structure
                    v-else-if="inArray(tableHeader.field, ['linked_table_id','ctlg_table_id'])"
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
                v-else-if="tableHeader.field === 'ctlg_data_range'"
                :options="getRGr(globalMeta)"
                :table-row="tableRow"
                :hdr_field="tableHeader.field"
                :fixed_pos="true"
                :embed_func_txt="'Add New'"
                :style="getEditStyle"
                @selected-item="updateCheckedDDL"
                @hide-select="hideEdit"
                @embed-func="showRG(null, globalMeta)"
            ></tablda-select-simple>

            <tablda-select-simple
                v-else-if="inArray(tableHeader.field, ['ctlg_distinct_field_id','ctlg_parent_link_field_id',
                    'ctlg_parent_quantity_field_id','ctlg_visible_field_ids','ctlg_filter_field_ids'])"
                :options="linkFields('ctlg')"
                :table-row="tableRow"
                :hdr_field="tableHeader.field"
                :fld_input_type="tableHeader.input_type"
                :fixed_pos="true"
                :style="getEditStyle"
                @selected-item="updateCheckedDDL"
                @hide-select="hideEdit"
            ></tablda-select-simple>

            <tablda-select-simple
                v-else-if="inArray(tableHeader.field, ['ctlg_display_option'])"
                :options="[
                    {val: 'inline', show: 'Inline'},
                    {val: 'popup', show: 'Popup'},
                ]"
                :table-row="tableRow"
                :hdr_field="tableHeader.field"
                :fixed_pos="true"
                :style="getEditStyle"
                @selected-item="updateCheckedDDL"
                @hide-select="hideEdit"
            ></tablda-select-simple>

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
                    v-else-if="tableHeader.field === 'position_field_id' || tableHeader.field === 'listing_field_id'"
                    :options="linkFields()"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :fixed_pos="true"
                    :can_empty="true"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
            ></tablda-select-simple>

            <tablda-select-simple
                    v-else-if="tableHeader.field === 'link_id'"
                    :options="availLinks()"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :fixed_pos="true"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
            ></tablda-select-simple>

            <tablda-select-simple
                    v-else-if="tableHeader.field === 'related_format'"
                    :options="relatedFormats()"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :fixed_pos="true"
                    :can_empty="true"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
            ></tablda-select-simple>

            <tablda-select-simple
                    v-else-if="tableHeader.field === 'related_col_group_id'"
                    :options="relatedColGroups()"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :fixed_pos="true"
                    :can_empty="true"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
            ></tablda-select-simple>

            <tablda-select-simple
                    v-else-if="tableHeader.field === 'permission_dcr_id'"
                    :options="currentPermissions()"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :fixed_pos="true"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
            ></tablda-select-simple>

            <tablda-select-simple
                    v-else-if="tableHeader.field === 'embd_table_align'"
                    :options="alignOptions()"
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
                    v-else-if="tableHeader.field === 'default_display'"
                    :options="[
                            {show: 'Table', val: 'Table', disabled: !tableRow.embd_table},
                            {show: 'Listing', val: 'Listing', disabled: !tableRow.embd_listing},
                            {show: 'Boards', val: 'Boards', disabled: !tableRow.embd_board},
                        ]"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :fixed_pos="true"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
            ></tablda-select-simple>

            <tablda-select-simple
                v-else-if="tableHeader.field === 'fld_display_header_type'"
                :options="[
                        {val: 'default', show: 'Default'},
                        {val: 'symbol', show: 'Symbol'},
                        {val: 'tooltip', show: 'Tooltip'},
                        {val: 'placeholder', show: 'Placeholder'},
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

            <input  v-else-if="inArray(tableHeader.field,['max_height_inline_embd','max_nbr_rcds_embd','listing_rows_width',
                        'listing_rows_min_width','one_per_submission','ctlg_columns_number','placement_tab_name','placement_tab_order'])"
                    v-model="tableRow[tableHeader.field]"
                    @blur="hideEdit();updateValue()"
                    :type="inArray(tableHeader.field, ['max_nbr_rcds_embd','one_per_submission','ctlg_columns_number','placement_tab_order']) ? 'number' : 'text'"
                    ref="inline_input"
                    class="form-control full-height"
                    :disabled="!canInput"
                    :style="getEditStyle">

            <input  v-else-if="inArray(tableHeader.field, ['description'])"
                    v-model="tableRow[tableHeader.field]"
                    @blur="hideEdit();updatePivot()"
                    ref="inline_input"
                    class="form-control full-height"
                    :disabled="!canInput"
                    :style="getEditStyle">

            <div v-else="">{{ hideEdit() }}</div>

        </div>
        <!-- ABSOLUTE EDITINGS! -->

    </td>
</template>

<script>
import {OptionsHelper} from "../../classes/helpers/OptionsHelper";
import {VerticalTableFldObject} from "../CustomTable/VerticalTableFldObject";

import {eventBus} from '../../app';

import CellStyleMixin from '../_Mixins/CellStyleMixin.vue';
import DataRangeMixin from '../_Mixins/DataRangeMixin.vue';

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
            CellStyleMixin,
            DataRangeMixin,
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
            parentRow: Object, //TableDataRequest object with '_fields_pivot'
        },
        computed: {
            canInput() {
                return this.tableHeader.field !== 'custom_url' || this.$root.user.subdomain;
            },
            neededPivot() {
                if (this.parentRow && this.parentRow._fields_pivot) {
                    return _.find(this.parentRow._fields_pivot, {table_field_id: Number(this.tableRow.id)});
                }
                return null;
            },
            pivotChecked() {
                return this.neededPivot && this.neededPivot[this.tableHeader.field];
            },
            getCustomCellStyle() {
                let obj = this.getCellStyle();
                obj.textAlign = this.inArray(this.tableHeader.field, ['view','edit']) ? 'center' : '';
                if (!this.canCellEdit) {
                    obj.backgroundColor = '#EEE';
                }
                return obj;
            },
            canCellEdit() {
                return this.with_edit
                    && this.$root.ownerOf(this.globalMeta)
                    && !this.inArray(this.tableHeader.field, this.$root.systemFields)
                    && this.tableHeader.field !== 'linked_table_id'
                    && (this.tableHeader.field !== 'related_col_group_id' || this.tableRow.link_id)
                    && (this.tableHeader.field !== 'related_format' || this.tableRow.link_id);
            },
            linkTb() {
                return _.find(this.$root.settingsMeta.available_tables, {id: Number(this.tableRow.linked_table_id)});
            },
            ctlgTb() {
                return _.find(this.$root.settingsMeta.available_tables, {id: Number(this.tableRow.ctlg_table_id)});
            },
            is_sel() {
                return this.$root.issel(this.tableHeader.input_type);
            },
            is_multisel() {
                return this.$root.isMSEL(this.tableHeader.input_type);
            },
        },
        methods: {
            tableWidths() {
                return [
                    { val:'full', show:'Full', },
                    { val:'field', show:'Field', },
                ];
            },
            pivotClick() {
                this.$emit('check-clicked', this.tableRow.id, {
                    setting: this.tableHeader.field,
                    val: !this.pivotChecked,
                }, []);
            },
            updatePivot() {
                this.$emit('check-clicked', this.tableRow.id, {
                    setting: this.tableHeader.field,
                    val: this.neededPivot[this.tableHeader.field],
                }, []);
            },
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
                    || this.inArray(this.tableHeader.field, ['active', 'download_pdf', 'download_png'])
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
                if (this.tableRow[this.tableHeader.field] !== this.oldValue || passed || this.is_multisel) {
                    this.tableRow._changed_field = this.tableHeader.field;
                    if (this.tableHeader.field === 'passed_ref_cond_id') {
                        let rc = _.find(this.globalMeta._ref_conditions, {id: Number(this.tableRow.passed_ref_cond_id)});
                        this.tableRow.linked_table_id = rc ? rc.ref_table_id : this.tableRow.linked_table_id;
                    }
                    if (this.$root.inArray(this.tableHeader.field, ['listing_rows_width','listing_rows_min_width'])) {
                        this.tableRow[this.tableHeader.field] = Number(this.tableRow[this.tableHeader.field]);
                        if (this.tableRow[this.tableHeader.field] < 1) {
                            this.tableRow[this.tableHeader.field] = Math.max(this.tableRow[this.tableHeader.field], 0.1);
                            this.tableRow[this.tableHeader.field] = Math.min(this.tableRow[this.tableHeader.field], 0.7);
                        } else {
                            this.tableRow[this.tableHeader.field] = Math.max(this.tableRow[this.tableHeader.field], 70);
                            this.tableRow[this.tableHeader.field] = Math.min(this.tableRow[this.tableHeader.field], 1000);
                        }
                    }
                    if (this.tableHeader.field === 'position_field_id' && this.tableRow[this.tableHeader.field]) {
                        this.tableRow.placement_tab_name = null;
                        this.tableRow.placement_tab_order = null;
                    }
                    if (this.tableHeader.field === 'placement_tab_name' && this.tableRow[this.tableHeader.field]) {
                        let foundOrder = _.find(this.globalMeta._fields, (fld) => {
                            return fld.pop_tab_name && fld.pop_tab_order;
                        });
                        this.tableRow.position_field_id = null;
                        this.tableRow.placement_tab_order = foundOrder ? foundOrder.pop_tab_order : null;
                    }
                    this.$emit('updated-cell', this.tableRow, this.tableHeader);
                }
            },
            updateCheckedDDL(item) {
                let tbRow = this.neededPivot || this.tableRow;

                if (this.is_multisel) {
                    tbRow[this.tableHeader.field] = Array.isArray(tbRow[this.tableHeader.field])
                        ? tbRow[this.tableHeader.field]
                        : [];

                    if (tbRow[this.tableHeader.field].indexOf(item) > -1) {
                        tbRow[this.tableHeader.field].splice( tbRow[this.tableHeader.field].indexOf(item), 1 );
                    } else {
                        tbRow[this.tableHeader.field].push(item);
                    }
                } else {
                    tbRow[this.tableHeader.field] = item;
                }

                this.neededPivot ? this.updatePivot() : this.updateValue();
            },
            showField(link) {
                let res = '';

                if (this.isAddRow && this.inArray(this.tableHeader.field, this.$root.systemFields)) {
                    res = 'Auto'
                }
                else
                if (this.inArray(this.tableHeader.field, ['width_of_table_popup'])) {
                    let tbRow = this.neededPivot || this.tableRow;
                    let cloud = _.find(this.tableWidths(), {val: tbRow[this.tableHeader.field]});
                    res = cloud ? cloud.show : res;
                }
                else
                if (this.inArray(this.tableHeader.field, ['board_image_fld_id'])) {
                    let tbRow = this.neededPivot || this.tableRow;
                    let sel = _.find(this.globalSelFields(), {val: Number(tbRow[this.tableHeader.field])});
                    res = sel ? sel.show : tbRow[this.tableHeader.field];
                }
                else
                if (this.inArray(this.tableHeader.field, ['dcr_section_name'])) {
                    res = this.neededPivot ? this.neededPivot[this.tableHeader.field] : '';
                }
                else
                if (this.inArray(this.tableHeader.field, ['table_column_group_id'])) {
                    let group = _.find(this.metaColGroups(), {id: Number(this.tableRow.table_column_group_id)});
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
                if (this.inArray(this.tableHeader.field, ['ctlg_table_id'])) {
                    if (this.ctlgTb) {
                        res = link
                            ? this.ctlgTb[link]
                            : this.ctlgTb.name;
                    } else {
                        res = this.tableRow.ctlg_table_id;
                    }
                }
                else
                if (this.tableHeader.field === 'ctlg_data_range') {
                    let val = this.tableRow[this.tableHeader.field];
                    res = val === null ? '' : this.rgName(val, this.globalMeta);
                }
                else
                if (this.tableHeader.field === 'ctlg_display_option') {
                    res = ucFirst(this.tableRow[this.tableHeader.field]);
                }
                else
                if (this.inArray(this.tableHeader.field, ['ctlg_distinct_field_id','ctlg_parent_link_field_id','ctlg_parent_quantity_field_id'])) {
                    let fld = _.find(this.linkFields('ctlg'), {val: Number(this.tableRow[this.tableHeader.field])});
                    res = fld ? fld.show : this.tableRow[this.tableHeader.field];
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
                if (this.inArray(this.tableHeader.field, ['position_field_id', 'listing_field_id'])) {
                    let table = this.tableHeader.field === 'position_field_id' ? this.globalMeta : this.linkTb;
                    let fld = _.find(table._fields || [], {id: Number(this.tableRow[this.tableHeader.field])});
                    res = fld ? fld.name : this.tableRow[this.tableHeader.field];
                }
                else
                if (this.inArray(this.tableHeader.field, ['link_id'])) {
                    let lnk = _.find(this.availLinks(), {val: Number(this.tableRow[this.tableHeader.field])});
                    res = lnk ? lnk.show : this.tableRow[this.tableHeader.field];
                }
                else
                if (this.inArray(this.tableHeader.field, ['related_format'])) {
                    let lnk = _.find(this.relatedFormats(), {val: this.tableRow[this.tableHeader.field]});
                    res = lnk ? lnk.show : this.tableRow[this.tableHeader.field];
                }
                else
                if (this.inArray(this.tableHeader.field, ['related_col_group_id'])) {
                    let lnk = _.find(this.relatedColGroups(), {val: Number(this.tableRow[this.tableHeader.field])});
                    res = lnk ? lnk.show : this.tableRow[this.tableHeader.field];
                }
                else
                if (this.inArray(this.tableHeader.field, ['permission_dcr_id'])) {
                    let permis = _.find(this.currentPermissions(), {val: Number(this.tableRow[this.tableHeader.field])});
                    res = permis ? permis.show : this.tableRow[this.tableHeader.field];
                }
                else
                if (this.inArray(this.tableHeader.field, ['embd_table_align'])) {
                    let permis = _.find(this.alignOptions(), {val: this.tableRow[this.tableHeader.field]});
                    res = permis ? permis.show : this.tableRow[this.tableHeader.field];
                }
                else
                if (this.inArray(this.tableHeader.field, ['fld_display_header_type'])) {
                    switch (VerticalTableFldObject.fieldSetting('fld_display_header_type', this.tableRow, null, this.behavior)) {
                        case 'symbol': res = 'Symbol'; break;
                        case 'tooltip': res = 'Tooltip'; break;
                        case 'placeholder': res = 'Placeholder'; break;
                        default: res = 'Default'; break;
                    }
                }
                else
                if (this.inArray(this.tableHeader.field, ['_name'])) {
                    res = this.tableRow.name;
                }
                else {
                    res = this.tableRow[this.tableHeader.field];
                }

                return this.$root.strip_danger_tags(res);
            },
            getLinkHash() {
                let domain = this.$root.clear_url + '/dcr/';
                return this.tableRow.link_hash ? domain+this.tableRow.link_hash : '#';
            },
            getCustomPath() {
                return this.tableRow.custom_url ? this.$root.app_url + '/dcr/' + this.tableRow.custom_url : '#';
            },
            showGroupsPopup(type, id) {
                eventBus.$emit('show-grouping-settings-popup', this.globalMeta.db_name, type, id);
            },
            showLinksPopup() {
                eventBus.$emit('show-display-links-settings-popup', this.tableRow.link_id);
            },
            showUsergroupPopup(id) {
                let idx = _.findIndex(this.user._user_groups, {id: id});
                eventBus.$emit('open-resource-popup', 'users', idx);
            },
            showRCP(id) {
                eventBus.$emit('show-ref-conditions-popup', this.globalMeta.db_name, id);
            },
            showPermis() {
                this.$emit('show-add-ref-cond', this.tableRow);
            },
            showRelColGroup() {
                this.$emit('show-add-ddl-option', this.notifLinkdTb().id, this.tableRow.related_col_group_id);
            },
            showCurrentPermis() {
                eventBus.$emit('show-permission-settings-popup', this.globalMeta.db_name, this.tableRow[this.tableHeader.field]);
            },

            //SELECTS
            globalColGroups() {
                let fields = _.filter(this.metaColGroups(), (col_gr) => { return this.notPresent(col_gr) });
                return _.map(fields, (cg) => {
                    return { val: cg.id, show: cg.name, }
                });
            },
            currentPermissions() {
                return _.map(this.globalMeta._table_permissions || [], (permis) => {
                    return { val: permis.id, show: permis.name, }
                });
            },
            alignOptions() {
                return [
                    {val: 'start', show: 'Left'},
                    {val: 'center', show: 'Center'},
                    {val: 'end', show: 'Right'},
                ];
            },
            globalSelFields() {
                let refTable = _.find(this.$root.settingsMeta.available_tables, (tb) => {
                    return tb.id == this.tableRow.linked_table_id;
                });

                let fields = refTable ? refTable._fields : this.globalMeta._fields;
                fields = _.filter(fields, (fld) => { return !this.$root.inArraySys(fld.field); });

                return _.map(fields, (cg) => {
                    return { val: cg.id, show: cg.name, }
                });
            },
            //linked tables
            linkRefConds() {
                /*let avail = _.filter(this.globalMeta._ref_conditions, (rc) => {
                    return rc.ref_table_id == this.tableRow.linked_table_id;
                });*/
                return _.map(this.globalMeta._ref_conditions, (rc) => {
                    return { val: rc.id, show: rc.name, }
                });
            },
            linkPermissions() {
                return _.map(this.linkTb ? this.linkTb._table_permissions : [], (permis) => {
                    return { val: permis.id, show: permis.name, }
                });
            },
            linkFields(ctlg) {
                let table;
                if (ctlg) {
                    table = this.inArray(this.tableHeader.field, ['ctlg_distinct_field_id','ctlg_visible_field_ids','ctlg_filter_field_ids'])
                        ? this.ctlgTb
                        : this.linkTb;
                } else {
                    table = this.tableHeader.field === 'position_field_id' ? this.globalMeta : this.linkTb;
                }

                let fields = table
                    ? _.filter(table._fields, (hdr) => { return !this.inArray(hdr.field, this.$root.systemFields) })
                    : [];
                return _.map(fields, (hdr) => {
                    return { val: hdr.id, show: this.$root.uniqName(hdr.name), }
                });
            },
            showVisFields() {
                let fields = this.linkFields('ctlg');
                return _.filter(fields, (fld) => {
                    return this.inArray(fld.val, this.tableRow[this.tableHeader.field]);
                });
            },
            availLinks() {
                return OptionsHelper.linksGrouped(this.globalMeta);
            },
            refIsOwner() {
                let tb = _.find(this.$root.settingsMeta.available_tables, {id: Number(this.tableRow[this.tableHeader.field])});
                return tb && tb.user_id == this.user.id;
            },
            notifLinkdTb() {
                let relTableId = null;
                _.each(this.globalMeta._fields, (fl) => {
                    _.each(fl._links, (lnk) => {
                        if (lnk.id == this.tableRow.link_id) {
                            let ref = _.find(this.globalMeta._ref_conditions, {id: Number(lnk.table_ref_condition_id)});
                            relTableId = ref ? ref.ref_table_id : relTableId;
                        }
                    });
                });
                return _.find(this.$root.settingsMeta.available_tables, {id: Number(relTableId)}) || {};
            },
            relatedFormats() {
                return [
                    { val: 'table', show: 'Table' },
                    { val: 'list', show: 'List' },
                    { val: 'vertical', show: 'Boards' },
                ];
            },
            relatedColGroups() {
                return _.map(this.notifLinkdTb()._column_groups || [], (cf) => {
                    return { val: cf.id, show: cf.name, }
                });
            },
            metaColGroups() {
                return this.globalMeta._column_groups && this.globalMeta._column_groups.length > 0
                    ? this.globalMeta._column_groups
                    : this.globalMeta._gen_col_groups;
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