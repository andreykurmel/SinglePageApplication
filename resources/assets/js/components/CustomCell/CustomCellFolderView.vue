<template>
    <td :style="getCustomCellStyle"
        class="td-custom"
        ref="td"
        @click="showEdit()"
    >
        <div class="td-wrapper" :style="getTdWrappStyle">

            <div class="wrapper-inner" :class="[tableHeader.field === 'hash' ? 'o-visible' : '']" :style="getWrapperStyle">
                <div class="inner-content">

                    <!-- ADD ROW -->
                    <input  v-if="isAddRow && inArray(tableHeader.field, ['name'])"
                            v-model="tableRow[tableHeader.field]"
                            @blur="hideEdit()"
                            @change="updateValue()"
                            ref="inline_input"
                            class="form-control full-height"
                            :style="getEditStyle">

                    <span v-else-if="isAddRow"></span>

                    <!-- EDIT ROW -->
                    <label class="switch_t" v-else-if="!isAddRow && tableHeader.f_type === 'Boolean'" :style="{height: Math.min(maxCellHGT, 17)+'px'}">
                        <input type="checkbox" :disabled="!!tableRow.is_system" v-model="tableRow[tableHeader.field]" @change="updateValue()">
                        <span class="toggler round"></span>
                    </label>

                    <embed-button v-else-if="!isAddRow && tableHeader.field === 'hash'"
                                  :is-folder="true"
                                  :hash="tableRow.hash"
                                  class="inline_embed"
                    ></embed-button>

                    <button v-else-if="!isAddRow && tableHeader.field === 'user_link'"
                            class="btn btn-default paa-btn flex flex--center"
                            :disabled="!tableRow.is_active"
                            title="Public access address"
                    >
                        <a :target="tableRow.is_active ? '_blank' : ''" :href="tableRow.is_active ? getLink() : '#'" class="link_btn">
                            <span v-if="tableRow.user_link">{{ tableRow.user_link }}</span>
                            <i v-else="" class="glyphicon glyphicon-share"></i>
                        </a>
                    </button>

                    <div v-else="">{{ showField() }}</div>

                </div>
            </div>

        </div>



        <!-- ABSOLUTE EDITINGS -->
        <div v-if="isEditing()" class="cell-editing">

            <input  v-if="!isAddRow && inArray(tableHeader.field, ['lock_pass'])"
                    v-model="tableRow[tableHeader.field]"
                    :disabled="!tableRow.is_locked"
                    @blur="hideEdit()"
                    @change="updateValue()"
                    ref="inline_input"
                    class="form-control full-height"
                    :style="getEditStyle">

            <input  v-else-if="!isAddRow && inArray(tableHeader.field, ['name','user_link'])"
                    v-model="tableRow[tableHeader.field]"
                    @blur="hideEdit()"
                    @change="updateValue()"
                    ref="inline_input"
                    class="form-control full-height"
                    :style="getEditStyle">

            <tablda-select-simple
                    v-else-if="tableHeader.field === 'def_table_id'"
                    :options="checkedTables()"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :fixed_pos="reactive_provider.fixed_ddl_pos"
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
                    :fixed_pos="reactive_provider.fixed_ddl_pos"
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
    import CellStyleMixin from '../_Mixins/CellStyleMixin.vue';

    import EmbedButton from './../Buttons/EmbedButton.vue';
    import TabldaSelectSimple from "./Selects/TabldaSelectSimple";

    export default {
        name: "CustomCellFolderView",
        mixins: [
            CellStyleMixin,
        ],
        components: {
            TabldaSelectSimple,
            EmbedButton,
        },
        inject: {
            reactive_provider: {
                from: 'reactive_provider',
                default: () => { return {} }
            }
        },
        data: function () {
            return {
                editing: false
            }
        },
        props:{
            globalMeta: {
                type: Object,
                default: function () {
                    return {};
                }
            },
            tableHeader: Object,
            tableRow: Object,
            cellHeight: Number,
            maxCellRows: Number,
            isAddRow: Boolean,
            user: Object,
        },
        computed: {
            getCustomCellStyle() {
                let obj = this.getCellStyle;
                obj.textAlignt = this.tableHeader.f_type === 'Boolean' || this.tableHeader.field === 'hash' ? 'center' : '';
                return obj;
            },
        },
        methods: {
            inArray(item, array) {
                return $.inArray(item, array) > -1;
            },
            isEditing() {
                return this.editing
                    && this.globalMeta._is_owner
                    && !this.inArray(this.tableHeader.field, this.$root.systemFields)
                    && !this.inArray(this.tableHeader.field, ['side_left_menu','side_left_filter','user_link'])
                    && !this.$root.global_no_edit;
            },
            showEdit() {
                if (this.isAddRow || this.inArray(this.tableHeader.f_type, ['Boolean'])) {
                    return;
                }
                //edit cell
                this.editing = true;
                if (this.isEditing()) {
                    this.oldValue = this.tableRow[this.tableHeader.field];
                    this.$nextTick(() => {
                        (this.$refs.inline_input ? this.$refs.inline_input.focus() : null);
                    });
                } else {
                    this.editing = false;
                }
            },
            hideEdit() {
                this.editing = false;
            },
            showField() {
                let res = '';
                if (this.tableHeader.field === 'def_table_id' && this.tableRow.def_table_id) {
                    let chk_tb = _.find(this.tableRow._checked_tables, {id: Number(this.tableRow.def_table_id)});
                    res = chk_tb ? chk_tb.name : this.tableRow.def_table_id;
                }
                else
                if (this.inArray(this.tableHeader.field, ['side_top','side_left_menu','side_left_filter','side_right'])) {
                    switch (this.tableRow[this.tableHeader.field]) {
                        case 'na': res = 'N/A'; break;
                        case 'hidden': res = 'Hidden'; break;
                        case 'show': res = 'Show'; break;
                    }
                }
                else {
                    res = this.tableRow[this.tableHeader.field];
                }
                return this.$root.strip_tags(res);
            },
            updateCheckedDDL(item) {
                this.tableRow[this.tableHeader.field] = item;
                this.updateValue();
            },
            updateValue() {
                if (this.tableRow[this.tableHeader.field] !== this.oldValue) {
                    this.$emit('updated-cell', this.tableRow);
                }
            },
            getLink() {
                return this.$root.clear_url
                    +'/view/'
                    + (this.tableRow.user_link ? this.tableRow.hash+'/'+this.globalMeta.name+'/'+this.tableRow.user_link : this.tableRow.hash);
            },
            checkedTables() {
                return _.map(this.tableRow._checked_tables, (tp) => {
                    return { val: tp.id, show: tp.name, }
                });
            },
        }
    }
</script>

<style lang="scss" scoped>
    @import "CustomCell.scss";

    .inline_embed {
        //display: inline-block;
        max-height: inherit;
        margin: 0 auto;
    }
    .link_btn {
        max-height: inherit;

        .btn {
            max-height: inherit;
            height: 31px;
            padding: 0px;
        }
    }
    .paa-btn {
        padding: 0 3px;
        line-height: inherit;
        margin: 0 auto;
    }
    .o-visible {
        overflow: visible !important;
    }
</style>