<template>
    <div>
        <div class="popup-wrapper" @click.self="$emit('popup-close', false)"></div>
        <div class="popup" :style="getPopupStyle()" :class="[!opened_tb ? 'collapsed' : '']">
            <div class="flex flex--col">
                <div class="popup-header">
                    <div class="drag-bkg" draggable="true" @dragstart="dragPopSt()" @drag="dragPopup()"></div>
                    <div class="flex">
                        <div class="flex__elem-remain">{{ replacement ? 'Copy with Replacement' : 'Copy' }}</div>
                        <div class="" style="position: relative">
                            <span class="glyphicon glyphicon-remove pull-right header-btn" @click="$emit('popup-close')"></span>
                        </div>
                    </div>
                </div>
                <div class="popup-content flex__elem-remain">
                    <div class="flex__elem__inner popup-main">

                        <div class="flex flex--col">
                            <div class="flex flex--center-v flex--space" style="margin-bottom: 5px;">
                                <div class="flex flex--center-v">
                                    <label class="checkbox-container" :style="{paddingLeft: leftPad+'px'}">
                                        <span class="indeterm_check__wrap">
                                            <span class="indeterm_check" @click="checkAll()">
                                                <i v-if="allChecked == 2" class="glyphicon glyphicon-ok group__icon"></i>
                                                <i v-if="allChecked == 1" class="glyphicon glyphicon-minus group__icon"></i>
                                            </span>
                                        </span>
                                        <span> Check/Uncheck All Columns</span>
                                    </label>
                                    <button class="btn btn-default btn-sm ml5"
                                            style="font-size: 24px;line-height: 18px;"
                                            @click="opened_tb = !opened_tb"
                                    >
                                        <span>{{ opened_tb ? '-' : '+' }}</span>
                                    </button>
                                </div>

                                <div class="flex flex--center-v">
                                    <label>Replacement:</label>
                                    <label class="switch_t ml5">
                                        <input type="checkbox" v-model="replacement">
                                        <span class="toggler round"></span>
                                    </label>
                                </div>
                            </div>
                            <div class="full-frame tb_wrap" v-show="opened_tb">
                                <table class="table">
                                    <thead>
                                    <tr>
                                        <th :width="getWi('copy')">
                                            <span>Copy</span>
                                            <header-resizer :table-header="tb_headers.copy" :user="{id:0}"></header-resizer>
                                        </th>
                                        <th :width="getWi('col')">
                                            <span>Column</span>
                                            <header-resizer :table-header="tb_headers.col" :user="{id:0}"></header-resizer>
                                        </th>
                                        <th v-show="replacement" :width="getWi('findv')">
                                            <span>Present Value</span>
                                            <header-resizer :table-header="tb_headers.findv" :user="{id:0}"></header-resizer>
                                        </th>
                                        <th v-show="replacement" :width="getWi('newv')">
                                            <span>New Value</span>
                                            <header-resizer :table-header="tb_headers.newv" :user="{id:0}"></header-resizer>
                                        </th>
                                    </tr>
                                    </thead>
                                    <tr v-for="(hdr,i) in fieldsForCopy">
                                        <td>
                                            <span class="indeterm_check__wrap">
                                                <span class="indeterm_check" @click="hdr.checked = !hdr.checked">
                                                    <i v-if="hdr.checked" class="glyphicon glyphicon-ok group__icon"></i>
                                                </span>
                                            </span>
                                        </td>
                                        <td><span class="name_td">{{ $root.uniqName(hdr.name) }}</span></td>
                                        <td v-show="replacement">
                                            <single-td-field
                                                    :table-meta="tableMeta"
                                                    :table-header="hdr.object"
                                                    :td-value="hdr.repl_val"
                                                    :with_edit="true"
                                                    :style="{width: '100%'}"
                                                    :ext-row="firstSelected"
                                                    @updated-td-val="(val) => {hdr.repl_val = val}"
                                            ></single-td-field>
                                        </td>
                                        <td v-show="replacement">
                                            <single-td-field
                                                    :table-meta="tableMeta"
                                                    :table-header="hdr.object"
                                                    :td-value="hdr.new_val"
                                                    :with_edit="true"
                                                    :style="{width: '100%'}"
                                                    :ext-row="firstSelected"
                                                    @updated-td-val="(val) => {hdr.new_val = val}"
                                            ></single-td-field>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <div class="popup-buttons">
                                <div class="pull-right full-height flex flex--center-v">
                                    <button class="btn btn-success btn-sm" @click="popupCopy()">To New Rows</button>
                                    <button class="btn btn-success btn-sm ml5" @click="copyToClipboard(true)">To Clipboard</button>
                                    <button class="btn btn-info btn-sm ml5" @click="$emit('popup-close')">Cancel</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!--Hidden Table For Copy-->
        <div class="copy-table">
            <table ref="copy_table">
                <thead>
                <tr>
                    <th v-for="hdr in fieldsForCopy" v-if="hdr.checked" :rowspan="(hdr.object.unit || hdr.object.unit_display) ? 1 : 2">
                        {{ hdr.name }}
                    </th>
                </tr>
                <tr>
                    <th v-for="hdr in fieldsForCopy" v-if="hdr.checked && (hdr.object.unit || hdr.object.unit_display)">
                        {{ (hdr.object.unit_display || hdr.object.unit) }}
                    </th>
                </tr>
                </thead>
                <tbody ref="copy_body">
                <tr v-for="tableRow in allRows" v-if="tableRow && tableRow._checked_row">
                    <td v-for="hdr in fieldsForCopy" v-if="hdr.checked">
                        {{ tableRow[hdr.field] }}
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script>
    import {eventBus} from '../../app';

    import {Endpoints} from "../../classes/Endpoints";

    import PopupAnimationMixin from './../_Mixins/PopupAnimationMixin';

    import HeaderResizer from "../CustomTable/Header/HeaderResizer";

    export default {
        name: "CopyAndReplacePopUp",
        mixins: [
            PopupAnimationMixin,
        ],
        components: {
            HeaderResizer,
        },
        data: function () {
            return {
                tb_headers: {
                    copy: {min_width:10, width: 80, max_width: 500},
                    col: {min_width:10, width: 340, max_width: 500},
                    findv: {min_width:10, width: 290, max_width: 500},
                    newv: {min_width:10, width: 290, max_width: 500},
                },
                opened_tb: false,
                replacement: false,
                fieldsForCopy: [],
                //PopupAnimationMixin
                getPopupWidth: 750,
                idx: 0,
            };
        },
        computed: {
            allChecked() {
                let fld_hidden = _.findIndex(this.fieldsForCopy, (el) => { return !el.checked; }) > -1;
                let fld_showed = _.findIndex(this.fieldsForCopy, (el) => { return el.checked; }) > -1;
                return !fld_hidden ? 2 : (fld_showed ? 1 : 0);
            },
            leftPad() {
                let sum_wi = this.replacement
                    ? _.sum( _.map(this.tb_headers,'width') )
                    : this.tb_headers.copy.width + this.tb_headers.col.width;
                let left = (this.tb_headers.copy.width / sum_wi) * (this.getPopupWidth - 50);
                return (left / 2) - 7;
            },
            firstSelected() {
                return _.find(this.allRows, {_checked_row: true}) || {};
            },
        },
        props:{
            tableMeta: Object,
            request_params: Object,
            allRows: Array,
            availFields: Array,
            forceColumns: Array,
        },
        methods: {
            getWi(key) {
                let all_sum = _.sum( _.map(this.tb_headers,'width') );
                return ((this.tb_headers[key].width / all_sum) * 100) + '%';
            },
            checkAll() {
                let status = !this.allChecked;
                _.each(this.fieldsForCopy, (el) => {
                    el.checked = status;
                });
            },
            popupCopy() {
                let check_obj = this.$root.checkedRowObject(this.allRows);
                //avail if allrows are more than 1 page.
                check_obj.all_checked = this.allRows.length >= this.tableMeta.rows_per_page ? check_obj.all_checked : false;

                $.LoadingOverlay('show');

                let request_params = _.cloneDeep(this.request_params);
                request_params.page = 1;
                request_params.rows_per_page = 0;

                let replaces = [];
                _.each(this.fieldsForCopy, (hdr) => {
                    if (hdr.repl_val || hdr.new_val) {
                        replaces.push({
                            field: hdr.field,
                            old_val: hdr.repl_val,
                            new_val: hdr.new_val,
                        });
                    }
                });

                let only_cols = _.filter(this.fieldsForCopy, (el) => { return !!el.checked; });
                only_cols = _.map(only_cols, 'field');
                if (only_cols.length === this.fieldsForCopy.length) {
                    only_cols = [];
                } else {
                    only_cols = only_cols.concat(this.forceColumns);
                }

                if (check_obj.rows_ids || check_obj.all_checked) {
                    Endpoints.massCopyRows(
                        this.tableMeta.id,
                        (check_obj.all_checked ? null : check_obj.rows_ids),
                        (check_obj.all_checked ? request_params : null),
                        replaces,
                        only_cols
                    ).then((data) => {
                        this.$emit('after-copied', data, check_obj.all_checked);
                    });
                } else {
                    Swal('Info','No record selected!');
                }
            },
            copyToClipboard(with_headers) {
                this.$root.copyToClipboard(with_headers ? this.$refs.copy_table : this.$refs.copy_body);
                Swal('Info','Copied to Clipboard!');
            },
        },
        mounted() {
            this.runAnimation();
            let fields = _.filter(this.tableMeta._fields, (el) => {
                return !this.availFields
                    || this.availFields.indexOf(el.field) > -1;
            });
            this.fieldsForCopy = _.map(fields, (el) => {
                return {
                    object: el,
                    field: el.field,
                    name: el.name,
                    checked: true,
                    repl_val: null,
                    new_val: null,
                }
            });
        },
    }
</script>

<style lang="scss" scoped>
    @import "CustomEditPopUp";
    @import "./../CustomTable/Table";

    .collapsed {
        height: 190px;
    }

    .popup {
        font-size: initial;
        cursor: auto;

        .popup-header {

            .head-buttons {
            }
        }

        .popup-content {
            .popup-main {
                padding: 20px 20px 0 20px;

                label {
                    margin: 0;
                }

                .tb_wrap {
                    border: 1px solid #CCC;
                }

                .popup-buttons {
                    height: 90px;

                    button {
                        margin-top: 0;
                    }
                }
            }
        }
    }

    .ml5 {
        margin-left: 5px;
    }

    .table {
        width: 100%;
        table-layout: fixed;

        th {
            position: sticky;
            top: 0;
        }
        .name_td {
            display: block;
            max-width: 100%;
            overflow: hidden;
        }
    }

    .copy-table {
        position: absolute;
        z-index: -1;
        top: 0;
    }
</style>