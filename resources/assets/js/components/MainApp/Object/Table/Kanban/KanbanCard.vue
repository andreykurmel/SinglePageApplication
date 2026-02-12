<template>
    <div class="kanban_card"
         @dragover.prevent="stopper"
         @dragenter="stopper"
         :style="cardStl"
    >
        <div class="card_wrap" @click="cardClick">
            <div class="card_header"
                 :style="hdrBgClr"
                 @click="$emit('show-popup', tableRow)"
            >
                <div class="drag-bkg"
                     :draggable="canEdit"
                     @dragstart="cardClick"
                     @drag="$emit('drag-move')"
                ></div>
                <span v-if="!redraw" v-html="getCardHeader()"></span>
                <span class="glyphicon glyphicon-resize-full"
                      v-if="extCollapse"
                      style="cursor: pointer;position: absolute;right: 25px;top: 5px;"
                      @click.stop="$emit('show-popup', tableRow)"></span>
                <span class="glyphicon"
                      :class="[extCollapse ? 'glyphicon-triangle-top' : 'glyphicon-triangle-bottom']"
                      style="cursor: pointer;position: absolute;right: 5px;top: 5px;"
                      @click.stop="$emit('change-collapsed', tableRow.id)"></span>
            </div>
            <div class="card_body"
                 v-show="!extCollapse"
                 :class="tHeader ? 'flex' : ''"
                 :style="{ height: kanbanSett.kanban_card_height+'px', overflowY: kanbanSett.kanban_card_height ? 'auto' : 'hidden' }"
                 @click="$emit('show-popup', tableRow)"
            >
                <div v-if="tHeader && kanbanSett.kanban_picture_position === 'left'"
                     class="attach_part"
                     :style="{width: (kanbanSett.kanban_picture_width)+'%'}"
                >
                    <show-attachments-block
                        :image-fit="cardAttachImageFit"
                        :show-type="cardAttachShowType"
                        :table-header="tHeader"
                        :table-meta="tableMeta"
                        :table-row="tableRow"
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
                            :table-row="tableRow"
                            :cell-height="1"
                            :max-cell-rows="0"
                            :user="$root.user"
                            :behavior="'kanban_view'"
                            :disabled_sel="true"
                            :with_edit="canEdit"
                            :hide-borders="fieldsHideBorders"
                            :available-columns="cardFields"
                            :hide-names="fieldsHideNames"
                            :parent-row="kanbanSett"
                            :extra-pivot-fields="kanbanSett._fields_pivot"
                            :widths="{ name: '35%', col: '65%', history: 0, unit: 0, }"
                            style="table-layout: auto"
                            @show-src-record="showSrcRecord"
                            @updated-cell="singleTdUpdate"
                    ></vertical-table>
                </div>

                <div v-if="tHeader && kanbanSett.kanban_picture_position === 'right'"
                     class="attach_part"
                     :style="{width: (kanbanSett.kanban_picture_width)+'%'}"
                >
                    <show-attachments-block
                        :image-fit="cardAttachImageFit"
                        :show-type="cardAttachShowType"
                        :table-header="tHeader"
                        :table-meta="tableMeta"
                        :table-row="tableRow"
                        :just-first="true"
                        :can-edit="canEdit"
                    ></show-attachments-block>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import {SpecialFuncs} from "../../../../../classes/SpecialFuncs";

    import CarouselBlock from "../../../../CommonBlocks/CarouselBlock";
    import ShowAttachmentsBlock from "../../../../CommonBlocks/ShowAttachmentsBlock";

    export default {
        name: "KanbanCard",
        components: {
            ShowAttachmentsBlock,
            CarouselBlock,
        },
        data: function () {
            return {
                redraw: false,
            }
        },
        props:{
            tableMeta: Object,
            tableRow: Object,
            kanbanSett: Object,
            extCollapse: Boolean,
            cardSelected: Boolean,
            canEdit: Boolean,
            dragRow: Object,
        },
        computed: {
            cardStl() {
                return {
                    width: this.kanbanSett.kanban_card_width+'px',
                    maxWidth: (window.innerWidth - 25)+'px',
                    borderColor: this.cardSelected ? '#A55' : null,
                };
            },
            visibleFieldsPivots() {
                return _.filter(this.kanbanSett._fields_pivot, (pv) => {
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
                    return {
                        paddingLeft: this.kanbanSett.kanban_picture_position === 'left' ? '10px' : null,
                        paddingRight: this.kanbanSett.kanban_picture_position === 'right' ? '10px' : null,
                        width: (100 - this.kanbanSett.kanban_picture_width)+'%',
                    };
                } else {
                    return {};
                }
            },
            tHeader() {
                return _.find(this.tableMeta._fields, {id: Number(this.kanbanSett.kanban_picture_field)});
            },
            hdrBgClr() {
                return {
                    backgroundColor: this.kanbanSett.kanban_header_color,
                    color: SpecialFuncs.smartTextColorOnBg(this.kanbanSett.kanban_header_color)
                };
            },
        },
        watch: {
            tableRow(val) {
                this.redraw = true;
                this.$nextTick(() => {
                    this.redraw = false;
                });
            },
        },
        methods: {
            getCardHeader() {
                let res = [];
                _.each(this.kanbanSett._fields_pivot, (pivot) => {
                    if (pivot.is_header_show || pivot.is_header_value) {
                        let hdr = _.find(this.tableMeta._fields, {id: Number(pivot.table_field_id)});
                        if (hdr) {
                            let row_value = this.tableRow
                                ? SpecialFuncs.showhtml(hdr, this.tableRow, this.tableRow[hdr.field], this.tableMeta)
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
            showSrcRecord(lnk, header, tableRow) {
                this.$emit('show-src-record', lnk, header, tableRow);
            },
            cardClick(e) {
                this.$emit('change-selected', e);
            },
            stopper(e) {
                if (this.dragRow && this.dragRow.id === this.tableRow.id) {
                    e.stopPropagation();
                }
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
    .kanban_card {
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

                .drag-bkg {
                    position: absolute;
                    left: 0;
                    top: 0;
                    width: 100%;
                    height: 100%;
                    cursor: pointer;
                    z-index: 10;
                }
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
    }
</style>