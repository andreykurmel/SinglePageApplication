<template>
    <div class="kanban_card"
         @dragover.prevent="stopper"
         @dragenter="stopper"
         :style="{width: tableMeta.kanban_card_width+'px', borderColor: cardSelected ? '#A55' : null}"
    >
        <div class="card_wrap" @click="cardClick">
            <div class="card_header"
                 :style="hdrBgClr"
                 @click="$emit('show-popup', tableRow)"
            >
                <div class="drag-bkg"
                     draggable="true"
                     @dragstart="cardClick"
                     @drag="$emit('drag-move')"
                ></div>
                <single-td-field
                        v-if="!redraw"
                        :table-meta="tableMeta"
                        :table-header="headerFld"
                        :td-value="tableRow[headerFld.field]"
                        :ext-row="tableRow"
                        :no_width="true"
                        :with_edit="false"
                        style="background-color: transparent;"
                        class="single-td"
                ></single-td-field>
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
                 :style="{ height: tableMeta.kanban_card_height+'px', overflowY: tableMeta.kanban_card_height ? 'auto' : 'hidden' }"
                 @click="$emit('show-popup', tableRow)"
            >
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
                            :hide-borders="fieldsHideBorders"
                            :is_small_spacing="'yes'"
                            :available-columns="cardFields"
                            :hide-names="fieldsHideNames"
                            :parent-row="kanbanSett"
                            :widths="{ name: '35%', col: '65%', history: 0, unit: 0, }"
                            style="table-layout: auto"
                            @show-src-record="showSrcRecord"
                            @updated-cell="singleTdUpdate"
                    ></vertical-table>
                </div>

                <div v-if="tHeader" class="attach_part" :style="{width: (tableMeta.kanban_picture_width)+'%'}">
                    <show-attachments-block
                        :image-fit="cardAttachImageFit"
                        :show-type="cardAttachShowType"
                        :table-header="tHeader"
                        :table-meta="tableMeta"
                        :table-row="tableRow"
                        :just-first="true"
                    ></show-attachments-block>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import {SpecialFuncs} from "../../../../../classes/SpecialFuncs";

    import VerticalTable from "../../../../CustomTable/VerticalTable";
    import SingleTdField from "../../../../CommonBlocks/SingleTdField";
    import CarouselBlock from "../../../../CommonBlocks/CarouselBlock";
    import ShowAttachmentsBlock from "../../../../CommonBlocks/ShowAttachmentsBlock";

    export default {
        name: "KanbanCard",
        components: {
            ShowAttachmentsBlock,
            CarouselBlock,
            SingleTdField,
            VerticalTable,
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
            dragRow: Object,
        },
        computed: {
            fieldsPivots() {
                return this.kanbanSett._fields_pivot;
            },
            attachmentPivot() {
                return _.find(this.fieldsPivots, {table_field_id: Number(this.tHeader.id)});
            },
            cardAttachShowType() {
                return this.attachmentPivot ? this.attachmentPivot.picture_style : '';
            },
            cardAttachImageFit() {
                return this.attachmentPivot ? this.attachmentPivot.picture_fit : '';
            },
            cardFields() {
                return this.mapFromField(this.fieldsPivots);
            },
            fieldsHideNames() {
                let filtered = _.filter(this.fieldsPivots, (pivot) => { return !!pivot.table_show_name; });
                return this.mapFromField(filtered);
            },
            fieldsHideBorders() {
                let filtered = _.filter(this.fieldsPivots, (pivot) => { return !pivot.cell_border; });
                return this.mapFromField(filtered);
            },
            headerFld() {
                return _.find(this.tableMeta._fields, {id: Number(this.kanbanSett.kanban_group_field_id)});
            },
            tablePartStyle() {
                if (this.tHeader) {
                    return {
                        paddingRight: '10px',
                        width: (100 - this.tableMeta.kanban_picture_width)+'%',
                    };
                } else {
                    return {};
                }
            },
            tHeader() {
                return _.find(this.tableMeta._fields, {id: Number(this.tableMeta.kanban_picture_field)});
            },
            hdrBgClr() {
                return {
                    backgroundColor: this.tableMeta.kanban_header_color,
                    color: SpecialFuncs.smartTextColorOnBg(this.tableMeta.kanban_header_color)
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
                padding: 0 5px;
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