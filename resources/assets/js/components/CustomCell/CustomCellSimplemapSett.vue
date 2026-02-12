<template>
    <td :style="getCustomCellStyle"
        class="td-custom"
        ref="td"
        @click="showEdit()"
    >
        <div class="td-wrapper" :style="getTdWrappStyle()">

            <div class="wrapper-inner" :style="tableHeader.f_type === 'Boolean' ? {} : getWrapperStyle()">
                <div class="inner-content" :style="{textAlign: ['Radio','Boolean'].indexOf(tableHeader.f_type) > -1 ? 'center' : ''}">

                    <label v-if="tableHeader.f_type === 'Boolean'" class="switch_t">
                        <input type="checkbox" :checked="is_show_checked" :disabled="!canEdit || !with_edit" @click="is_show_click">
                        <span class="toggler round" :class="{'disabled': !canEdit || !with_edit}"></span>
                    </label>

                    <a v-else-if="tableHeader.f_type === 'DataRange'"
                       title="Open data range in popup."
                       @click.stop="showRG(tableRow[tableHeader.field], globalMeta)"
                    >{{ showField() }}</a>

                    <div v-else="">{{ showField() }}</div>

                </div>
            </div>

        </div>

        <!-- ABSOLUTE EDITINGS -->
        <div v-if="editing" class="cell-editing">

            <tablda-select-simple
                v-if="$root.inArray(tableHeader.field, fieldColumns)"
                :options="nameFields()"
                :table-row="tableRow"
                :hdr_field="tableHeader.field"
                :fixed_pos="true"
                :style="getEditStyle"
                @selected-item="updateCheckedDDL"
                @hide-select="hideEdit"
            ></tablda-select-simple>

            <tablda-select-simple
                v-else-if="tableHeader.field === 'map'"
                :options="mapOpts()"
                :table-row="tableRow"
                :hdr_field="tableHeader.field"
                :style="getEditStyle"
                @selected-item="updateCheckedDDL"
                @hide-select="hideEdit"
            ></tablda-select-simple>

            <tablda-select-simple
                v-else-if="tableHeader.field === 'multirec_style'"
                :options="mrStyleOpts()"
                :table-row="tableRow"
                :hdr_field="tableHeader.field"
                :style="getEditStyle"
                @selected-item="updateCheckedDDL"
                @hide-select="hideEdit"
            ></tablda-select-simple>

            <tablda-select-simple
                v-else-if="tableHeader.f_type === 'DataRange'"
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
                v-else-if="tableHeader.field === 'picture_style'"
                :options="[
                        {val: 'scroll', show: 'Scroll'},
                        {val: 'slide', show: 'Slide'},
                    ]"
                :table-row="tableRow"
                :hdr_field="tableHeader.field"
                :style="getEditStyle"
                @selected-item="updateCheckedDDL"
                @hide-select="hideEdit"
            ></tablda-select-simple>

            <tablda-select-simple
                v-else-if="tableHeader.field === 'picture_fit'"
                :options="[
                        {val: 'fill', show: 'Fill'},
                        {val: 'width', show: 'Width'},
                        {val: 'height', show: 'Height'},
                    ]"
                :table-row="tableRow"
                :hdr_field="tableHeader.field"
                :style="getEditStyle"
                @selected-item="updateCheckedDDL"
                @hide-select="hideEdit"
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

            <input
                v-else-if="['name'].indexOf(tableHeader.field) > -1"
                v-model="tableRow[tableHeader.field]"
                @blur="updateRow()"
                ref="inline_input"
                class="form-control full-height"
                :style="getEditStyle">

            <div v-else="">{{ hideEdit() }}</div>

        </div>
        
    </td>
</template>

<script>
import CellStyleMixin from '../_Mixins/CellStyleMixin.vue';
import DataRangeMixin from '../_Mixins/DataRangeMixin.vue';

import TabldaSelectSimple from "./Selects/TabldaSelectSimple";

export default {
        name: "CustomCellSimplemapSett",
        mixins: [
            CellStyleMixin,
            DataRangeMixin,
        ],
        components: {
            TabldaSelectSimple
        },
        data: function () {
            return {
                editing: false,
                fieldColumns: ['level_fld_id', 'multirec_fld_id','locations_name_fld_id','locations_lat_fld_id','locations_long_fld_id','locations_descr_fld_id','locations_icon_shape_fld_id','locations_icon_color_fld_id'],
            }
        },
        computed: {
            getCustomCellStyle() {
                let obj = this.getCellStyle();
                if (!this.canEdit) {
                    obj.backgroundColor = '#EFEFEF';
                }
                return obj;
            },
            neededPivot() {
                if (this.parentRow && this.parentRow._fields_pivot) {
                    return _.find(this.parentRow._fields_pivot, {table_field_id: Number(this.tableRow.id)});
                }
                return null;
            },
            canEdit() {
                if (this.tableMeta.db_name === 'table_simplemaps_2_table_fields') {
                    return ['_name','is_header_show','is_header_value','table_show_value'].indexOf(this.tableHeader.field) > -1
                        || (this.neededPivot && this.neededPivot.table_show_value);
                }
                return true;
            },
            is_show_checked() {
                if (this.tableMeta.db_name === 'table_simplemaps_2_table_fields') {
                    return this.neededPivot && this.neededPivot[this.tableHeader.field];
                } else {
                    return this.tableRow[this.tableHeader.field];
                }
            },
            pivotHeader() {
                return _.find(this.globalMeta._fields, {id: Number(this.tableRow.id)}) || {};
            },
        },
        props:{
            globalMeta: Object,
            tableMeta: Object,
            tableHeader: Object,
            tableRow: Object, //TableField object
            cellHeight: Number,
            maxCellRows: Number,
            user: Object,
            parentRow: Object, //TableKanbanSettings object with '_fields_pivot'
            isAddRow: Boolean,
            with_edit: {
                type: Boolean,
                default: true
            },
        },
        methods: {
            inArray(item, array) {
                return $.inArray(item, array) > -1;
            },
            tableWidths() {
                return [
                    { val:'full', show:'Full', },
                    { val:'field', show:'Field', },
                ];
            },
            mapOpts() {
                return [
                    { val: 'states', show: 'US States' },
                    { val: 'counties', show: 'US Counties' },
                ];
            },
            mrStyleOpts() {
                return [
                    { val: 'listing', show: 'Listing' },
                    { val: 'tabs', show: 'Tabs' },
                    { val: 'sections', show: 'Sections' },
                ];
            },
            iconShapes() {
                return [
                    { val: 'circle', show: 'Circle' },
                    { val: 'square', show: 'Square' },
                    { val: 'marker', show: 'Marker' },
                    { val: 'triangle', show: 'Triangle' },
                    { val: 'heart', show: 'Heart' },
                    { val: 'star', show: 'Star' },
                    { val: 'diamond', show: 'Diamond' },
                ];
            },
            nameFields() {
                let meta = this.$root.inArray(this.tableHeader.field, ['level_fld_id', 'multirec_fld_id'])
                    ? this.globalMeta
                    : _.find(this.$root.settingsMeta.available_tables, {id: Number(this.tableRow.locations_table_id)});
                if (! meta) {
                    meta = this.globalMeta;
                }

                let fields = this.tableHeader.field === 'locations_icon_color_fld_id'
                    ? _.filter(meta._fields, {f_type: 'Color'})
                    : meta._fields;

                return _.map(fields, (hdr) => {
                    return { val: hdr.id, show: this.$root.uniqName(hdr.name), }
                });
            },
            is_show_click() {
                if (this.tableMeta.db_name === 'table_simplemaps_2_table_fields') {
                    this.$emit('check-clicked', this.tableRow.id, {
                        setting: this.tableHeader.field,
                        val: !this.is_show_checked,
                    }, []);
                } else {
                    this.tableRow[this.tableHeader.field] = this.tableRow[this.tableHeader.field] ? 0 : 1;
                    this.updateRow();
                }
            },
            is_header_click() {
                this.$emit('check-clicked', 'is_header', this.tableRow.id, []);
            },
            showEdit() {
                if (this.tableMeta.db_name === 'table_simplemaps_2_table_fields') {
                    if (
                        this.neededPivot && this.neededPivot.table_show_value
                        && this.pivotHeader && this.pivotHeader.f_type === 'Attachment'
                        && this.$root.inArray(this.tableHeader.field, ['picture_style','picture_fit'])
                    ) {
                        this.editing = true;
                    }
                    if (
                        this.neededPivot && this.neededPivot.table_show_value
                        && this.$root.inArray(this.tableHeader.field, ['width_of_table_popup'])
                    ) {
                        this.editing = true;
                    }
                } else {
                    this.editing = true;
                    this.$nextTick(function () {
                        if (this.$refs.inline_input) {
                            this.$refs.inline_input.focus();
                        }
                    });
                }
            },
            hideEdit() {
                this.editing = false;
            },
            updateRow() {
                this.hideEdit();
                this.$emit('updated-cell', this.tableRow, this.tableHeader);
            },
            updateCheckedDDL(item) {
                if (this.neededPivot) {
                    this.neededPivot[this.tableHeader.field] = item;

                    this.$emit('check-clicked', this.tableRow.id, {
                        setting: this.tableHeader.field,
                        val: item,
                    }, []);
                } else {
                    this.tableRow[this.tableHeader.field] = item;
                    this.updateRow();
                }
            },
            showField() {
                let res = '';
                if (this.tableMeta.db_name === 'table_simplemaps_2_table_fields') {

                    if (this.tableHeader.field === 'picture_style') {
                        let sw = this.neededPivot && this.pivotHeader.f_type === 'Attachment' ? this.neededPivot.picture_style : '';
                        switch (sw) {
                            case 'scroll': res = 'Scroll'; break;
                            case 'slide': res = 'Slide'; break;
                        }
                    }
                    else
                    if (this.inArray(this.tableHeader.field, ['width_of_table_popup'])) {
                        let tbRow = this.neededPivot || this.tableRow;
                        let cloud = _.find(this.tableWidths(), {val: tbRow[this.tableHeader.field]});
                        res = cloud ? cloud.show : res;
                    }
                    else
                    if (this.tableHeader.field === 'picture_fit') {
                        let sw = this.neededPivot && this.pivotHeader.f_type === 'Attachment' ? this.neededPivot.picture_fit : '';
                        switch (sw) {
                            case 'fill': res = 'Fill'; break;
                            case 'width': res = 'Width'; break;
                            case 'height': res = 'Height'; break;
                        }
                    }
                    else {
                        res = this.$root.uniqName( this.tableRow.name );
                    }

                }
                if (this.tableMeta.db_name === 'table_simplemaps') {

                    if (this.tableHeader.f_type === 'DataRange') {
                        let val = this.tableRow[this.tableHeader.field];
                        res = val === null ? '' : this.rgName(val, this.globalMeta);
                    }
                    else if (this.$root.inArray(this.tableHeader.field, this.fieldColumns)) {
                        let hdr = _.find(this.nameFields(), {val: Number(this.tableRow[this.tableHeader.field])});
                        res = hdr ? hdr.show : this.tableRow[this.tableHeader.field];
                    }
                    else if (this.tableHeader.field === 'map') {
                        let hdr = _.find(this.mapOpts(), {val: this.tableRow[this.tableHeader.field]});
                        res = hdr ? hdr.show : this.tableRow[this.tableHeader.field];
                    }
                    else if (this.tableHeader.field === 'multirec_style') {
                        let hdr = _.find(this.mrStyleOpts(), {val: this.tableRow[this.tableHeader.field]});
                        res = hdr ? hdr.show : this.tableRow[this.tableHeader.field];
                    }
                    else {
                        res = this.tableRow[this.tableHeader.field];
                    }

                }
                return res;
            },
        }
    }
</script>

<style lang="scss" scoped>
    @import "CustomCell.scss";

    .td-wrapper {
        input {
            margin: 0;
        }
    }
</style>