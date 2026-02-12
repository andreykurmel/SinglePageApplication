<template>
    <div class="smp_card" :style="cardStl">
        <div class="card_wrap" @click="cardClick">
            <div class="card_header"
                 :style="hdrBgClr"
                 @click="$emit('show-popup', currentTbRow)"
            >
                <span v-if="!redraw" v-html="getCardHeader()"></span>
                <span class="glyphicon glyphicon-remove pull-right header-btn pointer" @click="$emit('close-clicked')"></span>
            </div>
            <div class="card_body"
                 ref="cardbody"
                 :class="tableRows.length > 1 && $root.inArray(selectedSimplemap.multirec_style, ['listing', 'tabs']) ? 'flex' : ''"
                 :style="cardBodyStl"
                 @click="$emit('show-popup', currentTbRow)"
            >
                <div v-if="tableRows.length > 1 && selectedSimplemap.multirec_style === 'listing'"
                     class="smp-rows mr5"
                     :style="mrListingStyle()"
                >
                    <div v-for="(row,i) in tableRows.length"
                         class="no-wrap"
                         :class="[(i === selIdx ? 'active' : '')]"
                         @click="() => {selIdx = i}"
                    >
                        <label v-html="sectionName(tableRows[i])"></label>
                    </div>
                </div>
                <div v-if="tableRows.length > 1 && selectedSimplemap.multirec_style === 'tabs'" :style="mrTabsStyle()">
                    <div class="tabs-wrap">
                        <div v-for="(row,i) in tableRows.length"
                             class="tabs-wrap__btn flex flex--center no-wrap"
                             :class="[(i === selIdx ? 'active' : '')]"
                             @click="() => {selIdx = i}"
                        >
                            <label class="no-margin" v-html="sectionName(tableRows[i])"></label>
                        </div>
                    </div>
                </div>
                <div v-if="selectedSimplemap.multirec_style !== 'sections'" :style="mainBodyStyle()" :class="tHeader ? 'flex' : ''">
                    <div v-if="tHeader && selectedSimplemap.smp_picture_position === 'left'"
                         class="attach_part"
                         :style="{width: (selectedSimplemap.smp_picture_width)+'%'}"
                    >
                        <show-attachments-block
                            :image-fit="cardAttachImageFit"
                            :show-type="cardAttachShowType"
                            :table-header="tHeader"
                            :table-meta="tableMeta"
                            :table-row="currentTbRow"
                            :just-first="true"
                            :can-edit="canEdit"
                        ></show-attachments-block>
                    </div>

                    <div class="table_part" :style="tablePartStyle">
                        <vertical-table
                                :td="$root.tdCellComponent(tableMeta.is_system)"
                                :global-meta="tableMeta"
                                :table-meta="tableMeta"
                                :settings-meta="$root.settingsMeta"
                                :table-row="currentTbRow"
                                :cell-height="1"
                                :max-cell-rows="0"
                                :user="$root.user"
                                :behavior="'simplemap_view'"
                                :disabled_sel="true"
                                :with_edit="canEdit"
                                :hide-borders="fieldsHideBorders"
                                :available-columns="cardFields"
                                :hide-names="fieldsHideNames"
                                :parent-row="selectedSimplemap"
                                :extra-pivot-fields="selectedSimplemap._fields_pivot"
                                :widths="{ name: '35%', col: '65%', history: 0, unit: 0, }"
                                style="table-layout: auto"
                                @show-src-record="showSrcRecord"
                                @updated-cell="singleTdUpdate"
                        ></vertical-table>
                    </div>

                    <div v-if="tHeader && selectedSimplemap.smp_picture_position === 'right'"
                         class="attach_part"
                         :style="{width: (selectedSimplemap.smp_picture_width)+'%'}"
                    >
                        <show-attachments-block
                            :image-fit="cardAttachImageFit"
                            :show-type="cardAttachShowType"
                            :table-header="tHeader"
                            :table-meta="tableMeta"
                            :table-row="currentTbRow"
                            :just-first="true"
                            :can-edit="canEdit"
                        ></show-attachments-block>
                    </div>
                </div>

                <template v-for="(tbRow, ix) in tableRows" v-if="selectedSimplemap.multirec_style === 'sections'">
                    <div class="card_header">
                        <span v-html="sectionName(tbRow)"></span>
                    </div>
                    <div :style="mainBodyStyle()" :class="tHeader ? 'flex' : ''">
                        <div v-if="tHeader && selectedSimplemap.smp_picture_position === 'left'"
                             class="attach_part"
                             :style="{width: (selectedSimplemap.smp_picture_width)+'%'}"
                        >
                            <show-attachments-block
                                :image-fit="cardAttachImageFit"
                                :show-type="cardAttachShowType"
                                :table-header="tHeader"
                                :table-meta="tableMeta"
                                :table-row="tbRow"
                                :just-first="true"
                                :can-edit="canEdit"
                            ></show-attachments-block>
                        </div>

                        <div class="table_part" :style="tablePartStyle">
                            <vertical-table
                                :td="$root.tdCellComponent(tableMeta.is_system)"
                                :global-meta="tableMeta"
                                :table-meta="tableMeta"
                                :settings-meta="$root.settingsMeta"
                                :table-row="tbRow"
                                :cell-height="1"
                                :max-cell-rows="0"
                                :user="$root.user"
                                :behavior="'simplemap_view'"
                                :disabled_sel="true"
                                :with_edit="canEdit"
                                :hide-borders="fieldsHideBorders"
                                :available-columns="cardFields"
                                :hide-names="fieldsHideNames"
                                :parent-row="selectedSimplemap"
                                :extra-pivot-fields="selectedSimplemap._fields_pivot"
                                :widths="{ name: '35%', col: '65%', history: 0, unit: 0, }"
                                style="table-layout: auto"
                                @show-src-record="showSrcRecord"
                                @updated-cell="singleTdUpdate"
                            ></vertical-table>
                        </div>

                        <div v-if="tHeader && selectedSimplemap.smp_picture_position === 'right'"
                             class="attach_part"
                             :style="{width: (selectedSimplemap.smp_picture_width)+'%'}"
                        >
                            <show-attachments-block
                                :image-fit="cardAttachImageFit"
                                :show-type="cardAttachShowType"
                                :table-header="tHeader"
                                :table-meta="tableMeta"
                                :table-row="tbRow"
                                :just-first="true"
                                :can-edit="canEdit"
                            ></show-attachments-block>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
</template>

<script>
    import {SpecialFuncs} from "../../../../../classes/SpecialFuncs";

    import CarouselBlock from "../../../../CommonBlocks/CarouselBlock";
    import ShowAttachmentsBlock from "../../../../CommonBlocks/ShowAttachmentsBlock";

    export default {
        name: "SimplemapRowCard",
        components: {
            ShowAttachmentsBlock,
            CarouselBlock,
        },
        data: function () {
            return {
                redraw: false,
                selIdx: 0,
            }
        },
        props: {
            tableMeta: Object,
            tableRows: Array,
            selectedSimplemap: Object,
            canEdit: Boolean,
        },
        computed: {
            currentTbRow() {
                return this.tableRows[this.selIdx];
            },
            cardStl() {
                return {
                    width: this.selectedSimplemap.smp_card_width+'px',
                    maxWidth: (window.innerWidth - 25)+'px',
                };
            },
            cardBodyStl() {
                let h = parseFloat(this.selectedSimplemap.smp_card_max_height);
                if (h >= 100) {
                    h += 'px';
                }
                if (h > 1 && h < 100) {
                    h += '%';
                }
                if (h > 0 && h < 1) {
                    h += (h * 100) + '%';
                }
                return {
                    height: this.selectedSimplemap.smp_card_height+'px',
                    overflowY: this.selectedSimplemap.smp_card_height || h ? 'auto' : 'hidden',
                    maxHeight: h,
                };
            },
            visibleFieldsPivots() {
                return _.filter(this.selectedSimplemap._fields_pivot, (pv) => {
                    return pv.table_show_value;
                });
            },
            attachmentPivot() {
                return _.find(this.visibleFieldsPivots, {table_field_id: Number(this.tHeader.id)});
            },
            cardAttachShowType() {
                return this.attachmentPivot ? this.attachmentPivot.picture_style : '';
            },
            cardAttachImageFit() {
                return this.attachmentPivot ? this.attachmentPivot.picture_fit : '';
            },
            cardFields() {
                return this.mapFromField(this.visibleFieldsPivots);
            },
            fieldsHideNames() {
                let filtered = _.filter(this.visibleFieldsPivots, (pivot) => { return !!pivot.table_show_name; });
                return this.mapFromField(filtered);
            },
            fieldsHideBorders() {
                let filtered = _.filter(this.visibleFieldsPivots, (pivot) => { return !pivot.cell_border; });
                return this.mapFromField(filtered);
            },
            tablePartStyle() {
                if (this.tHeader) {
                    let picPos = this.selectedSimplemap.smp_picture_field && this.selectedSimplemap.smp_picture_width
                        ? this.selectedSimplemap.smp_picture_position
                        : '';
                    return {
                        paddingLeft: picPos === 'left' ? '10px' : null,
                        paddingRight: picPos === 'right' ? '10px' : null,
                        width: (100 - this.selectedSimplemap.smp_picture_width)+'%',
                    };
                } else {
                    return {};
                }
            },
            tHeader() {
                return _.find(this.tableMeta._fields, {id: Number(this.selectedSimplemap.smp_picture_field)});
            },
            hdrBgClr() {
                return {
                    backgroundColor: this.selectedSimplemap.smp_header_color,
                    color: SpecialFuncs.smartTextColorOnBg(this.selectedSimplemap.smp_header_color),
                    fontWeight: 'bold',
                };
            },
        },
        methods: {
            mrListingStyle() {
                let bnd = this.$refs.cardbody ? this.$refs.cardbody.getBoundingClientRect() : {};
                return {
                    width: '30%',
                    height: bnd ? Number(bnd.height - 10) + 'px' : '100%',
                };
            },
            mrTabsStyle() {
                return {
                    width: '32px',
                    position: 'relative',
                };
            },
            mainBodyStyle() {
                let w = '100%';
                if (this.tableRows.length > 1) {
                    if (this.selectedSimplemap.multirec_style === 'listing') {
                        w = '70%';
                    }
                    if (this.selectedSimplemap.multirec_style === 'tabs') {
                        w = 'calc(100% - 32px)';
                    }
                }
                return {
                    width: w,
                };
            },
            sectionName(tbRow) {
                let fld = _.find(this.tableMeta._fields, {id: Number(this.selectedSimplemap.multirec_fld_id)});
                return fld
                    ? (this.$root.uniqName(fld.name) + ': ' + SpecialFuncs.showhtml(fld, tbRow, tbRow[fld.field], this.tableMeta))
                    : this.getCardHeader(tbRow);
            },
            getCardHeader(extRow) {
                let tbRow = extRow || this.currentTbRow;
                let res = [];
                _.each(this.selectedSimplemap._fields_pivot, (pivot) => {
                    if (pivot.is_header_show || pivot.is_header_value) {
                        let hdr = _.find(this.tableMeta._fields, {id: Number(pivot.table_field_id)});
                        if (hdr) {
                            let row_value = tbRow
                                ? SpecialFuncs.showhtml(hdr, tbRow, tbRow[hdr.field], this.tableMeta)
                                : '';
                            let ar = pivot.is_header_show ? [this.$root.uniqName(hdr.name)] : [];
                            if (pivot.is_header_value) {
                                ar.push(row_value)
                            }
                            res.push(ar.join(': '));
                        }
                    }
                });
                return res.length ? res.join(' | ') : '';
            },
            mapFromField(arrData) {
                return _.map(arrData, (pivot) => {
                    let fld = _.find(this.tableMeta._fields, {id: Number(pivot.table_field_id)});
                    return fld.field;
                });
            },
            showSrcRecord(lnk, header, row) {
                this.$emit('show-src-record', lnk, header, row);
            },
            cardClick(e) {
                this.$emit('change-selected', e);
            },
            singleTdUpdate(row, header) {
                this.$emit('row-update', row);
            },
        },
        mounted() {
        },
        beforeDestroy() {
        }
    }
</script>

<style lang="scss" scoped>
    .smp_card {
        width: 300px;
        margin-bottom: 10px;
        border: 2px solid transparent;
        border-radius: 5px;
        transition: all 0.7s;

        &:hover {
            border-color: #777;
        }

        .card_wrap {
            border: 1px solid #CCC;
            border-radius: 5px;
            background-color: #FFF;
            cursor: pointer;

            .card_header {
                padding: 3px;
                background-color: #ddd;
                position: relative;
                cursor: auto;

                .single-td {
                    display: inline-block;
                    background-color: transparent;
                    /*position: absolute;
                    left: 5px;
                    top: 3px;
                    z-index: 5;*/
                }
                .glyphicon {
                    z-index: 15;
                }
            }
            .card_body {
                padding: 5px 5px 0 5px;
                overflow: auto;

                .table_part {
                    overflow-x: hidden;
                    overflow-y: auto;
                }
                .attach_part {
                    background-color: #EEE;
                    position: relative;
                    overflow: auto;
                }
            }
            .glyphicon {
                margin: 0 3px;
            }
        }
        .smp-rows {
            height: 100%;
            border: 1px solid #CCC;
            border-radius: 5px;
            padding: 5px;
            overflow: auto;

            label {
                margin: 0;
            }
            div {
                border-bottom: 1px dashed #CCC;
                cursor: pointer;

                &:hover {
                    border: 1px dashed #AAA;
                }
            }
            .active {
                background-color: #FFC;
            }
        }

        .tabs-wrap {
            position: absolute;
            right: 32px;
            transform: rotate(-90deg);
            transform-origin: right top;
            flex-direction: row-reverse;
            display: flex;

            .tabs-wrap__btn {
                max-width: 100px;
                overflow: hidden;
                text-overflow: ellipsis;
                padding: 0 3px;
                border: 1px solid #777;
                border-radius: 10px;
                background-color: #eee;
                color: #444;
                margin-left: 3px;
                cursor: pointer;
            }
            .active {
                background-color: #fff;
            }
        }
    }
</style>