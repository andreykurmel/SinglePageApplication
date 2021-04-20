<template>
    <td :style="getCustomCellStyle"
        class="td-custom"
        ref="td"
        @click="showEdit()"
    >
        <div class="td-wrapper" :style="getTdWrappStyle">

            <div class="wrapper-inner" :style="customInnerStyle">
                <div class="inner-content">

                    <label class="switch_t" v-if="tableHeader.f_type === 'Boolean'" :style="{height: Math.min(maxCellHGT, 17)+'px'}">
                        <input type="checkbox" :disabled="!canEdit" v-model="editValue" @change="updateValue()">
                        <span class="toggler round" :class="[!canEdit ? 'disabled' : '']"></span>
                    </label>

                    <a v-else-if="!isAddRow && inArray(tableHeader.field, ['name'])"
                       target="_blank"
                       ref="sett_content_elem"
                       :href="getLink()">{{ tableRow.name }}</a>

                    <a v-else-if="!isAddRow && inArray(tableHeader.field, ['source_string'])"
                       target="_blank"
                       ref="sett_content_elem"
                       :href="'?'+tableRow.source_string">{{ tableRow.source_string }}</a>

                    <div v-else="">
                        <span v-if="showField()" :class="{'is_select': is_sel}" ref="sett_content_elem">{{ showField() }}</span>
                    </div>

                </div>
            </div>

            <cell-table-data-expand
                    v-if="cont_height > maxCellHGT+cell_top_padding || cont_width > tableHeader.width"
                    style="background-color: #FFF;"
                    :cont_height="cont_height"
                    :cont_width="cont_width"
                    :table-meta="globalMeta"
                    :table-row="tableRow"
                    :table-header="tableHeader"
                    :html="cont_html"
                    :uniqid="getuniqid()"
                    :can-edit="canEdit"
                    :user="user"
            ></cell-table-data-expand>

        </div>



        <!-- ABSOLUTE EDITINGS -->
        <div v-if="isEditing()" class="cell-editing">

            <tablda-select-simple
                    v-if="inArray(tableHeader.field, ['side_top','side_left','side_right'])"
                    :options="[
                        {val: 'na', show: 'N/A'},
                        {val: 'hidden', show: 'Hidden'},
                        {val: 'show', show: 'Show'},
                    ]"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :fixed_pos="reactive_provider.fixed_ddl_pos"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
            ></tablda-select-simple>

            <input v-else-if="tableHeader.f_type === 'String'"
                    v-model="editValue"
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

    import CellStyleMixin from '../_Mixins/CellStyleMixin.vue';
    import ExpandIconMixin from './../_Mixins/ExpandIconMixin.vue';

    import TabldaSelectSimple from "./Selects/TabldaSelectSimple";
    import CellTableDataExpand from "./InCell/CellTableDataExpand";

    export default {
        name: "CustomCellStimAppView",
        mixins: [
            CellStyleMixin,
            ExpandIconMixin,
        ],
        components: {
            CellTableDataExpand,
            TabldaSelectSimple,
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
            is_visible: Boolean,
        },
        watch: {
        },
        computed: {
            canEdit() {
                let can = !this.inArray(this.tableHeader.field, this.$root.systemFields)
                    && (this.user.id == this.tableRow.user_id || this.isAddRow);
                if (this.isAddRow) {
                    return can && (!this.inArray(this.tableHeader.field, ['source_string']))
                } else {
                    return can
                        && (!this.inArray(this.tableHeader.field, ['user_link','is_locked','lock_pass']) || this.tableRow.is_active)
                        && (!this.inArray(this.tableHeader.field, ['lock_pass']) || this.tableRow.is_locked);
                }
            },
            getCustomCellStyle() {
                let obj = this.getCellStyle;
                if (this.tableHeader.field === 'lock_pass' && !this.tableRow.is_locked) {
                    obj.backgroundColor = '#EEE';
                }
                return obj;
            },
            customInnerStyle() {
                let obj = this.getWrapperStyle;
                if (this.tableHeader.field === '_embd') {
                    obj.overflow = 'visible';
                }
                return obj;
            },
            is_sel() {
                return this.$root.issel(this.tableHeader.input_type);
            },
        },
        methods: {
            getLink() {
                return '?view='+ this.tableRow.hash;
            },
            inArray(item, array) {
                return $.inArray(item, array) > -1;
            },
            isEditing() {
                return this.editing && this.canEdit && !this.$root.global_no_edit;
            },
            showEdit() {
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
            hideEdit() {
                this.editing = false;
            },
            updateCheckedDDL(item) {
                this.editValue = item;
                this.updateValue();
            },
            updateValue() {
                let newVal = this.editValue;
                if (newVal !== this.oldValue) {
                    this.tableRow[this.tableHeader.field] = newVal;
                    this.tableRow._changed_field = this.tableHeader.field;
                    this.$emit('updated-cell', this.tableRow);
                    this.$nextTick(() => {
                        this.changedContSize();
                    });
                }
            },

            //show
            showField() {
                let res = '';
                if (this.inArray(this.tableHeader.field, ['side_top','side_left','side_right'])) {
                    switch (this.tableRow[this.tableHeader.field]) {
                        case 'na': res = 'N/A'; break;
                        case 'hidden': res = 'Hidden'; break;
                        case 'show': res = 'Show'; break;
                    }
                }
                else {
                    res = this.editValue;
                }
                return this.$root.strip_tags(res);
            },
        },
        mounted() {
            this.editValue = this.tableRow[this.tableHeader.field];
            eventBus.$on('table-data-string-popup__update', this.tableDataStringUpdateHandler);
        },
        beforeDestroy() {
            eventBus.$off('table-data-string-popup__update', this.tableDataStringUpdateHandler);
        }
    }
</script>

<style lang="scss" scoped>
    @import "CustomCell.scss";
</style>