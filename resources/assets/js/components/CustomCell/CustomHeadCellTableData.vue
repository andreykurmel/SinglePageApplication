<template>
    <th @click="showEdit()">

        <div class="full-height flex flex--center"
             @mouseenter="(e) => { $root.showHoverTooltip(e, tableHeader) }"
             @mouseleave="$root.leaveHoverTooltip"
        >
            <div :style="cellStl">{{ getVal() }}</div>

            <div v-if="isEditing()" class="cell-editing">
                <tablda-select-ddl
                        :ddl_id="tableHeader.unit_ddl_id"
                        :table-row="tableHeader"
                        :hdr_field="'unit_display'"
                        :fixed_pos="reactive_provider.fixed_ddl_pos"
                        :style="cellStl"
                        :spec_colors="spcClrObj"
                        @selected-item="updateCheckedDDL"
                        @hide-select="hideEdit"
                ></tablda-select-ddl>
            </div>
        </div>

        <!--Error Msg-->
        <div v-if="add_err_msg.show" class="modal-wrapper">
            <div class="modal">
                <div class="modal-dialog modal-sm">
                    <div class="modal-content">
                        <div class="modal-header"><span>{{ add_err_msg.header }}</span></div>
                        <div class="modal-body" v-html="add_err_msg.body"></div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-success" @click.stop="add_err_msg.show = false">{{ add_err_msg.btn }}</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </th>
</template>

<script>
    import {UnitConversion} from './../../classes/UnitConversion';

    import Select2DDLMixin from './../_Mixins/Select2DDLMixin.vue';
    import CellStyleMixin from '../_Mixins/CellStyleMixin.vue';

    import {eventBus} from './../../app';

    import TabldaSelectDdl from "./Selects/TabldaSelectDdl";
    
    export default {
        components: {
            TabldaSelectDdl,
        },
        name: "CustomHeadCellTableData",
        mixins: [
            Select2DDLMixin,
            CellStyleMixin,
        ],
        inject: {
            reactive_provider: {
                from: 'reactive_provider',
                default: () => { return {} }
            }
        },
        data: function () {
            return {
                editing: false,
                oldValue: null,
                editValue: null,
                cellHeight: 1,
                add_err_msg: {
                    show: false,
                    header: 'Info',
                    body: 'Valid conversion not found.<br>Conversion request ignored.',
                    btn: 'OK',
                },
            }
        },
        watch: {
            'tableHeader.unit'(newval, oldval) {
                this.updateConversionRules();
            },
            'tableHeader.unit_display'(newval, oldval) {
                this.updateConversionRules();
            },
            'tableMeta.unit_conv_is_active'(newval, oldval) {
                this.updateConversionRules();
            },
            'tableMeta.unit_conv_by_user'(newval, oldval) {
                this.updateConversionRules();
            },
            'tableMeta.unit_conv_by_system'(newval, oldval) {
                this.updateConversionRules();
            },
            'tableMeta.unit_conv_by_lib'(newval, oldval) {
                this.updateConversionRules();
            },
        },
        props:{
            tableMeta: Object,
            tableHeader: Object,
            maxCellRows: Number,
            user: Object,
        },
        computed: {
            cellStl() {
                let stl = _.cloneDeep(this.getEditStyle);
                if (this.tableHeader.unit == this.tableHeader.unit_display) {
                    stl.color = '#55F';
                }
                return stl;
            },
            spcClrObj() {
                let ob = {_all: '#222'};
                ob[this.tableHeader.unit] = '#55F';
                return ob;
            },
        },
        methods: {
            inArray(item, array) {
                return $.inArray(item, array) > -1;
            },
            isEditing() {
                return this.editing
                    && !this.$root.global_no_edit
                    && this.tableHeader.unit
                    && this.tableHeader.unit_ddl_id
                    && this.tableHeader.header_unit_ddl
                    && this.tableMeta.unit_conv_is_active;
//                    && this.tableMeta.__unit_convers
//                    && this.tableMeta.__unit_convers.length;
            },
            getVal() {
                return UnitConversion.showUnit(this.tableHeader, this.tableMeta);
            },
            showEdit() {
                if (!this.tableHeader.unit) {
                    Swal('Unit|Selection is not specified!');
                    return;
                }

                this.editing = true;
                if (this.isEditing()) {
                    this.oldValue = this.tableHeader.unit_display;
                    this.editValue = this.tableHeader.unit_display;
                    this.$nextTick(function () {
                        if (this.$refs.inline_input && this.$refs.inline_input.nodeName === 'SELECT'){
                            this.showHideDDLs(this.$root.selectParam);
                            this.ddl_cached = false;
                        }
                    });
                } else {
                    this.editing = false;
                }
            },
            hideEdit() {
                this.editing = false;
            },
            //called from Select2DDLMixin
            updateCheckedDDL(item, option) {
                this.editValue = (this.editValue === item ? null : item);
                this.updateValue(option);
            },
            updateValue(option) {
                if (this.editValue !== this.oldValue)
                {
                    this.tableHeader.unit_display = this.editValue;

                    if (!this.$root.checkAvailable(this.$root.user, 'unit_conversion')) {
                        this.tableHeader.unit_display = this.oldValue;
                        Swal('Unit Conversion doesn`t available to your subscription plan.');
                        return;
                    }

                    let obj = {};
                    if (option) {
                        obj['_is_ddlid'] = {show_val: ''};
                        obj[option.value] = {show_val: option ? option.show : ''};
                    }
                    let old_obj = this.tableHeader._rc_unit_display;
                    this.tableHeader._rc_unit_display = obj;
                    this.tableHeader._changed_field = 'unit_display';

                    if (this.tableHeader.unit && this.tableHeader.unit_display && this.tableMeta.unit_conv_is_active) {
                        let conversions = UnitConversion.findConvs(this.tableMeta, this.tableHeader);
                        if (!conversions.length && (this.tableHeader.unit !== this.tableHeader.unit_display)) {
                            this.tableHeader.unit_display = this.oldValue;
                            this.editValue = this.oldValue;
                            this.tableHeader._rc_unit_display = old_obj;
                            this.add_err_msg.show = true;
                            return;
                        }
                        this.$set(this.tableHeader, '__selected_unit_convs', conversions);
                    }

                    eventBus.$emit('header-updated-cell', this.tableHeader);
                }
            },
            updateConversionRules() {
                let conversions = [];
                if (this.tableHeader.unit && this.tableHeader.unit_display && this.tableMeta.unit_conv_is_active) {
                    conversions = UnitConversion.findConvs(this.tableMeta, this.tableHeader);
                    this.$set(this.tableHeader, '__selected_unit_convs', conversions);
                }
                return conversions;
            }
        },
        mounted() {
            this.updateConversionRules();
        }
    }
</script>

<style lang="scss" scoped>
    @import "CustomCell.scss";

    .content {
        display: inline;
    }

    .modal-dialog {
        text-align: left;
        width: 450px;

        .modal-header {
            background-color: #444;
            padding: 5px 10px;
            font-size: 2em;
            line-height: 1.5em;
            font-weight: bold;
            color: #FFF;
        }
        .modal-body {
            font-size: 1.5em;
        }
    }
</style>