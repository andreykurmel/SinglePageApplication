<template>
    <div class="popup-wrapper" v-show="show_this" @click.self="hide()" :style="{zIndex: zIdx}">
        <div class="popup" :style="getPopupStyle()">
            <div class="flex flex--col">
                <div class="popup-header" style="height: 38px;">
                    <div class="drag-bkg" draggable="true" @dragstart="dragPopSt()" @drag="dragPopup()"></div>
                    <div class="flex">
                        <div class="flex__elem-remain" style="top: 5px; position: relative;">
                            <span>{{ directRow ? 'Overview of CFs for Fields of this Record' : 'General Overview of CFs for Fields' }}</span>
                        </div>
                        <div class="flex flex--center-v" style="position: absolute; right: 0; z-index: 500;">
                            <span class="btn btn-primary btn-sm blue-gradient flex flex--center mr5"
                                  @click="showHideEmptyCF()"
                                  style="padding: 5px 3px; font-size: 14px;"
                                  :style="$root.themeButtonStyle">
                                <span>{{ hideEmptyCF ? 'Show' : 'Hide' }} Empty CFs</span>
                            </span>
                            <select-block
                                :options="availOpts()"
                                :sel_value="showfield"
                                style="width: 135px; cursor: pointer; margin-right: 5px;"
                                @option-select="setOpt"
                            ></select-block>
                            <span v-if="directRow"
                                  class="glyphicon glyphicon glyphicon-arrow-left pointer mr5"
                                  :class="[idxOfTheRow == 0 ? 'gray' : 'white']"
                                  @click="setNextPrevOpt(false)"></span>
                            <span v-if="directRow"
                                  class="glyphicon glyphicon glyphicon-arrow-right pointer mr5"
                                  :class="[idxOfTheRow == totRowsLen ? 'gray' : 'white']"
                                  @click="setNextPrevOpt(true)"></span>
                            <span class="glyphicon glyphicon-remove pointer white"
                                  style=""
                                  @click="hide()"></span>
                        </div>
                    </div>
                </div>
                <div class="popup-content flex__elem-remain">
                    <div class="flex__elem__inner">
                        <div class="flex flex--col full-height">
                            <custom-table
                                v-if="cfHeader && overviewMeta"
                                :cell_component_name="'custom-cell-cond-format'"
                                :global-meta="tableMeta"
                                :table-meta="overviewMeta"
                                :all-rows="overviewRows"
                                :rows-count="overviewRows.length"
                                :cell-height="1"
                                :max-cell-rows="0"
                                :is-full-width="true"
                                :show_rows_sum="true"
                                :user="$root.user"
                                :behavior="'cond_format_overview'"
                                :use_theme="true"
                                :with_edit="false"
                                :parent-row="cfHeader"
                                :special_extras="{
                                    direct_row: directRow,
                                }"
                            ></custom-table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="resizer" draggable="true" @dragstart="dragResizeStart" @drag="dragResizeDo"></div>
        </div>
    </div>
</template>

<script>
    import {eventBus} from '../../app';

    import PopupAnimationMixin from './../_Mixins/PopupAnimationMixin';
    import TestRowColMixin from './../_Mixins/TestRowColMixin';

    import SelectBlock from "../CommonBlocks/SelectBlock";
    import CustomTable from "../CustomTable/CustomTable";

    export default {
        name: "OverviewFormatsPopup",
        components: {
            CustomTable,
            SelectBlock
        },
        mixins: [
            PopupAnimationMixin,
            TestRowColMixin,
        ],
        data: function () {
            return {
                hideEmptyCF: false,
                directRow: null,
                overviewMeta: null,
                showfield: 'bkgd_color',
                show_this: false,
                //PopupAnimationMixin
                getPopupWidth: 900,
                getPopupHeight: (window.innerHeight * 0.8)+'px',
                idx: 0,
            }
        },
        props: {
            tableMeta: Object,
        },
        computed: {
            cfHeader() {
                return this.$root.settingsMeta['cond_formats']
                    ? _.find(this.$root.settingsMeta['cond_formats']._fields, {field: this.showfield})
                    : null;
            },
            overviewRows() {
                return _.filter(this.tableMeta._fields, (fld) => {
                    return this.$root.systemFields.indexOf(fld.field) === -1;
                });
            },
            idxOfTheRow() {
                return this.directRow && this.tableMeta.id == this.$root.tableMeta.id
                    ? _.findIndex(this.$root.listTableRows, (r) => {
                        return r.id === this.directRow.id;
                    })
                    : -1;
            },
            totRowsLen() {
                return this.$root.listTableRows.length - 1;
            },
        },
        methods: {
            storeSizes(width_px, height_px) {
                this.getPopupWidth = width_px;
                this.getPopupHeight = height_px+'px';
            },
            createMeta(directRow) {
                let meta = {
                    id: null,
                    name: 'Cond Format Overview',
                    _is_owner: true,
                    _fields: [
                        {
                            id: null,
                            name: directRow ? 'Fields,Name' : 'Field Name',
                            field: '_of_name',
                            is_showed: true,
                            width: directRow ? 100 : 150,
                            is_floating: 1,
                        },
                    ],
                };

                if (directRow) {
                    meta._fields.push(
                        {
                            id:  null,
                            name: 'Fields,Value',
                            field: '_of_value',
                            is_showed: true,
                            width: 100,
                            is_floating: 1,
                        }
                    );
                }

                let empties = [];
                _.each(this.tableMeta._cond_formats, (cf) => {
                    let notEmptyCF = _.find(this.overviewRows, (tbFld) => {
                        return cf[this.cfHeader.field] && this.cfIsApplied(tbFld, cf);
                    });
                    let availCF = ! this.hideEmptyCF || notEmptyCF;

                    if (availCF) {
                        let object = {
                            id: null,
                            name: 'Conditional Formattings (CFs),' + cf.name,
                            field: '_ov:' + cf.id,
                            is_showed: true,
                            width: 100,
                            is_floating: 0,
                            _cf_id: cf.id,
                            _cf_row_group_id: cf.table_row_group_id,
                            _cf_col_group_id: cf.table_column_group_id,
                        };
                        notEmptyCF ? meta._fields.push(object) : empties.push(object);
                    }
                });
                meta._fields = _.concat(meta._fields, empties);
                this.overviewMeta = meta;
            },
            setPopWi() {
                this.getPopupWidth = _.sumBy(this.overviewMeta._fields, 'width') + 60;
                this.getPopupWidth = Math.max(this.getPopupWidth, window.innerWidth*0.8);
                this.getPopupWidth = Math.min(this.getPopupWidth, this.directRow ? 730 : 620);
            },
            availOpts(onlyVals) {
                let res = [
                    { val:'bkgd_color', show:'BCGD Color' },
                    { val:'', show:'Text Font', isTitle:true, style:{background:'#EEE'} },
                    { val:'font_size', show:'Size', style:{paddingLeft: '15px'}, },
                    { val:'color', show:'Color', style:{paddingLeft: '15px'}, },
                    { val:'font', show:'Style', style:{paddingLeft: '15px'}, },
                    { val:'activity', show:'Editability' },
                    { val:'', show:'Visibility', isTitle:true, style:{background:'#EEE'} },
                    { val:'show_table_data', show:'Grid View', style:{paddingLeft: '15px'}, },
                    { val:'show_form_data', show:'Form', style:{paddingLeft: '15px'}, },
                ];
                if (onlyVals) {
                    res = _.map(res, 'val');
                    res = _.filter(res);
                }
                return res;
            },
            setNextPrevOpt(next) {
                if (this.idxOfTheRow == (next ? this.totRowsLen : 0)) {
                    return;
                }
                eventBus.$emit('overview-formats__open-another-row', this.tableMeta.db_name, this.directRow.id, next);
                this.$root.overviewFormatWaiting = true;
                this.hide();
            },
            setOpt(opt) {
                this.showfield = '';
                this.$nextTick(() => {
                    this.showfield = opt.val;
                });
            },
            showCF(cf) {
                return cf.id;
            },
            showHideEmptyCF() {
                this.hideEmptyCF = ! this.hideEmptyCF;
                this.overviewMeta = null;
                this.$nextTick(() => {
                    this.createMeta(this.directRow);
                    this.setPopWi();
                });
            },
            hideMenu(e) {
                if (this.show_this && e.keyCode === 27 && this.$root.tablesZidx == this.zIdx) {
                    this.hide();
                }
            },
            hide() {
                this.show_this = false;
                this.$root.tablesZidxDecrease();

                this.overviewMeta = null;
                this.directRow = null;

                this.$emit('popup-close');
            },
            showCondFormatsPopupHandler(db_table, directRow) {
                if (!db_table || db_table === this.tableMeta.db_name) {
                    this.directRow = directRow;
                    this.createMeta(directRow);
                    this.setPopWi();
                    this.show_this = true;
                    this.$root.tablesZidxIncrease();
                    this.zIdx = this.$root.tablesZidx;

                    if (this.$root.overviewFormatWaiting) {
                        this.$root.overviewFormatWaiting = false;
                    } else {
                        this.runAnimation();
                    }
                }
            },
            cfIsApplied(tableHeader, condFrm) {
                return condFrm.status == 1
                    &&
                    (!this.directRow || this.testRow(this.directRow, condFrm.id))
                    &&
                    (!condFrm.table_column_group_id || this.testColumn(tableHeader, condFrm.table_column_group_id, this.tableMeta));
            },
        },
        created() {
            eventBus.$on('global-keydown', this.hideMenu);
            eventBus.$on('show-overview-format-popup', this.showCondFormatsPopupHandler);
        },
        beforeDestroy() {
            eventBus.$off('global-keydown', this.hideMenu);
            eventBus.$off('show-overview-format-popup', this.showCondFormatsPopupHandler);
        }
    }
</script>

<style lang="scss" scoped>
    @import "./CustomEditPopUp";
    @import './../CommonBlocks/TabldaLike';

    .table-container {
        height: 100%;
        padding: 5px;
        overflow: auto;
        border-right: 2px solid #AAA;
    }
    .tablda-like {
        td, th {
            padding: 0 10px;
        }
    }
</style>