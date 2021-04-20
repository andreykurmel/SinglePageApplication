<template>
    <div class="kanban_card"
         @dragover.prevent="stopper"
         @dragenter="stopper"
         :style="{width: tableMeta.kanban_card_width+'px', borderColor: cardSelected ? '#A55' : null}"
    >
        <div class="card_wrap">
            <div class="card_header"
                 :style="{backgroundColor: tableMeta.kanban_header_color}"
                 @click="cardClick"
            >
                <div class="drag-bkg" draggable="true" @drag="$emit('drag-move')"></div>
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
                 :style="{ height: tableMeta.kanban_card_height+'px', overflowY: tableMeta.kanban_card_height ? 'auto' : 'hidden' }"
                 @click="$emit('show-popup', tableRow)"
            >
                <vertical-table
                        :td="$root.tdCellComponent(tableMeta.is_system)"
                        :global-meta="tableMeta"
                        :table-meta="tableMeta"
                        :settings-meta="$root.settingsMeta"
                        :table-row="tableRow"
                        :cell-height="$root.cellHeight"
                        :max-cell-rows="$root.maxCellRows"
                        :user="$root.user"
                        :behavior="'kanban_view'"
                        :can-see-history="false"
                        :disabled_sel="true"
                        :is_small_spacing="'yes'"
                        :available-columns="cardFields"
                        :widths="{ name: '35%', col: '65%', history: 0, unit: 0, }"
                        style="table-layout: auto"
                        @show-src-record="showSrcRecord"
                        @updated-cell="singleTdUpdate"
                ></vertical-table>
            </div>
        </div>
    </div>
</template>

<script>
    import VerticalTable from "../../../../CustomTable/VerticalTable";
    import SingleTdField from "../../../../CommonBlocks/SingleTdField";

    export default {
        name: "KanbanCard",
        components: {
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
            cardFields() {
                return _.map(this.kanbanSett._columns, (map) => {
                    return map.field;
                });
            },
            headerFld() {
                return _.find(this.tableMeta._fields, {id: Number(this.kanbanSett.kanban_group_field_id)});
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
                overflow-y: auto;
                overflow-x: hidden;
            }
            .glyphicon {
                margin: 0 3px;
            }
        }
    }
</style>