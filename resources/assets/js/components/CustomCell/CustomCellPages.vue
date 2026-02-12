<template>
    <td :style="getCustomCellStyle"
        class="td-custom"
        ref="td"
        @click="showEdit()"
    >
        <div class="td-wrapper" :style="getTdWrappStyle()">

            <div class="wrapper-inner" :style="getWrapperStyle()">
                <div class="inner-content">
                    <span v-html="showField()"></span>
                    <a v-if="tableHeader.field === 'table_view_id' && tableRow.table_view_id"
                       @click.prevent.stop="showViewSettingsPopup"
                       style="cursor: pointer;"
                    >
                        (Settings)
                    </a>
                </div>
            </div>

        </div>



        <!-- ABSOLUTE EDITINGS -->
        <div v-if="isEditing()" class="cell-editing">

            <select-with-folder-structure
                v-if="tableHeader.field === 'table_id'"
                :for_single_select="true"
                :empty_val="true"
                :cur_val="editValue"
                :available_tables="availTables()"
                :user="user"
                :is_obj_attr="'id'"
                @sel-changed="(val) => {this.editValue = val;}"
                @sel-closed="hideEdit();updateValue();"
                class="form-control full-height"
                :style="getEditStyle"
            ></select-with-folder-structure>

            <tablda-select-simple
                v-else-if="tableHeader.field === 'table_view_id'"
                :options="availViews()"
                :table-row="tableRow"
                :hdr_field="tableHeader.field"
                :fixed_pos="true"
                :style="getEditStyle"
                @selected-item="updateCheckedDDL"
                @hide-select="hideEdit"
            ></tablda-select-simple>

            <tablda-select-simple
                v-else-if="tableHeader.field === 'view_part'"
                :options="availViewParts()"
                :table-row="tableRow"
                :hdr_field="tableHeader.field"
                :can_empty="true"
                :fixed_pos="true"
                :style="getEditStyle"
                @selected-item="updateCheckedDDL"
                @hide-select="hideEdit"
            ></tablda-select-simple>

            <input
                v-else=""
                v-model="editValue"
                @blur="hideEdit();updateValue()"
                ref="inline_input"
                class="form-control full-height"
                :style="getEditStyle"/>

        </div>
        <!-- ABSOLUTE EDITINGS -->

    </td>
</template>

<script>
import {SpecialFuncs} from '../../classes/SpecialFuncs';

import CellStyleMixin from '../_Mixins/CellStyleMixin.vue';

import TabldaSelectSimple from "./Selects/TabldaSelectSimple";
import SelectWithFolderStructure from "./InCell/SelectWithFolderStructure.vue";

export default {
    components: {
        SelectWithFolderStructure,
        TabldaSelectSimple,
    },
    name: "CustomCellPages",
    mixins: [
        CellStyleMixin,
    ],
    data: function () {
        return {
            editing: false,
            oldValue: this.tableRow[this.tableHeader.field],
            editValue: null,
        }
    },
    props:{
        tableMeta: Object,
        tableHeader: Object,
        tableRow: Object,
        rowIndex: Number,
        cellHeight: Number,
        maxCellRows: Number,
        isAddRow: Boolean,
        user: Object,
        cellValue: String|Number,
    },
    watch: {
        cellValue(val) {
            this.editValue = val;
        }
    },
    computed: {
        getCustomCellStyle() {
            let obj = this.getCellStyle();
            obj.textAlign = (this.inArray(this.tableHeader.f_type, ['Boolean', 'Radio']) ? 'center' : '');
            return obj;
        },
        canCellEdit() {
            return (this.tableHeader.field !== 'table_view_id' || this.tableRow.table_id);
        },
    },
    methods: {
        inArray(item, array) {
            return $.inArray(item, array) > -1;
        },
        isEditing() {
            return this.editing && this.canCellEdit && !this.$root.global_no_edit;
        },
        showEdit() {
            //edit cell
            this.editing = true;
            if (this.isEditing()) {
                this.oldValue = this.editValue;
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
        updateCheckedDDL(item) {
            this.editValue = item;
            this.updateValue();
        },
        updateValue() {
            if (this.editValue !== this.oldValue) {
                this.tableRow[this.tableHeader.field] = this.editValue;
                this.tableRow._changed_field = this.tableHeader.field;
                this.$emit('updated-cell', this.tableRow);
            }
        },
        showField() {
            let res = '';
            if (this.tableHeader.field === 'table_id') {
                let tb = _.find(this.availTables(), {id: Number(this.tableRow.table_id)});
                res = tb ? tb.name : this.tableRow.table_id;
            }
            else
            if (this.tableHeader.field === 'table_view_id') {
                let vv = _.find(this.availViews(), {val: Number(this.tableRow.table_view_id)});
                res = vv ? vv.show : this.tableRow.table_view_id;
            }
            else
            if (this.tableHeader.field === 'view_part') {
                let vv = _.find(this.availViewParts(), {val: this.tableRow.view_part});
                res = vv ? vv.show : this.tableRow.view_part;
            }
            else {
                res = this.editValue;
            }
            return this.$root.strip_danger_tags(res);
        },
        availTables() {
            return this.$root.settingsMeta.available_tables;
        },
        availViews() {
            let tb = _.find(this.$root.settingsMeta.available_tables, {id: Number(this.tableRow.table_id)}) || {};
            return _.map(tb._views, (vv) => {
                return { val: vv.id, show: vv.name, }
            });
        },
        availViewParts() {
            let tb = _.find(this.$root.settingsMeta.available_tables, {id: Number(this.tableRow.table_id)}) || {};
            let view = _.find(tb._views, {id: Number(this.tableRow.table_view_id)}) || {};
            let parts = view && view.parts_avail ? (JSON.parse(view.parts_avail) || []) : [];
            return _.map(parts, (p) => {
                return this.$root.getViewPartOption(p);
            });
        },
        showViewSettingsPopup() {
            this.$emit('show-add-ddl-option', this.tableRow.table_id, this.tableRow.table_view_id);
        },
    },
    mounted() {
        this.editValue = this.cellValue;
    },
    beforeDestroy() {
    }
}
</script>

<style lang="scss" scoped>
@import "./CustomCell.scss";
</style>