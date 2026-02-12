<template>
    <div class="full-frame canvas_wrap" ref="rcmap_canvas">
        <div class="visible_map_button flex">
            <refresh-layout-rc-map-button @refresh-layout="refreshLayout" class="mr5"></refresh-layout-rc-map-button>

            <show-hide-rc-map-button
                :table-meta="tableMeta"
                @updated-elements="storeMapPositions"
            ></show-hide-rc-map-button>
        </div>

        <template v-for="mt in mapTables" v-if="canDraw">
            <rc-map-table
                v-if="mt.position.visible"
                :table-meta="tableMeta"
                :map-elem="mt"
                :canvas_x="canvas_x"
                :canvas_y="canvas_y"
                :sel-field-id="selTableId == mt.id ? selFieldId : null"
                @position-was-updated="redraw()"
                @selected-field="createRefCond"
            ></rc-map-table>
        </template>
        <template v-for="rc in mapRefConds" v-if="canDraw">
            <rc-map-object
                v-if="rc.position.visible"
                :table-meta="tableMeta"
                :map-elem="rc"
                :canvas_x="canvas_x"
                :canvas_y="canvas_y"
                :boundings="boundings"
                @position-was-updated="redraw()"
            ></rc-map-object>
        </template>
    </div>
</template>

<script>
    import {eventBus} from '../../../../../../app';

    import {MapTable} from "./MapTable";
    import {MapRefCond} from "./MapRefCond";
    import {MapPosition} from "./MapPosition";
    import {RefCondEndpoints} from "../../../../../../classes/RefCondEndpoints";

    import RcMapTable from "./RcMapTable.vue";
    import RcMapObject from "./RcMapObject.vue";
    import ShowHideRcMapButton from "./ShowHideRcMapButton.vue";
    import RefreshLayoutRcMapButton from "./RefreshLayoutRcMapButton.vue";

    export default {
        name: "TableSettingsRefCondMaps",
        mixins: [
        ],
        components: {
            RefreshLayoutRcMapButton,
            ShowHideRcMapButton,
            RcMapObject,
            RcMapTable
        },
        data() {
            return {
                selTableId: null,
                selFieldId: null,
                canDraw: false,
                boundings: null,
                mapTables: [],
                mapRefConds: [],
                canvas_x: 0,
                canvas_y: 0,
            }
        },
        props: {
            tableMeta: Object,
        },
        watch: {
        },
        methods: {
            fullRedraw() {
                this.makeMapTables();
                this.makeMapRefConds();
                this.redraw();
            },
            redraw() {
                this.canDraw = false;
                this.$nextTick(() => {
                    this.canDraw = true;
                });
            },
            orderedRCs() {
                let fldsOrder = _.map(this.tableMeta._fields, 'id');
                return _.sortBy(this.tableMeta._ref_conditions, (rc) => {
                    let rcFlds = _.filter(_.map(rc._items, 'table_field_id'));
                    let rcFldsOrder = rcFlds.length
                        ? _.map(rcFlds, (id) => fldsOrder.indexOf(id) + 1)
                        : [0];
                    return _.min(rcFldsOrder);
                });
            },
            makeMapTables() {
                let rcs = this.orderedRCs();
                this.mapTables = [
                    new MapTable(this.tableMeta, this.tableMeta, rcs)
                ];
                _.each(rcs, (refCond) => {
                    if (
                        refCond._ref_table
                        &&
                        ! _.find(this.mapTables, {id: Number(refCond._ref_table.id)})
                    ) {
                        this.mapTables.push(
                            new MapTable(this.tableMeta, refCond._ref_table, rcs)
                        );
                    }
                });
            },
            makeMapRefConds() {
                let rcs = this.orderedRCs();
                this.mapRefConds = [];
                _.each(rcs, (refCond) => {
                    this.mapRefConds.push(
                        new MapRefCond(this.tableMeta, refCond, rcs)
                    );
                });
            },
            createRefCond(table_id, field_id) {
                //first select
                if (!this.selTableId && !this.selFieldId) {
                    this.selTableId = table_id;
                    this.selFieldId = field_id;
                } else
                //unselect
                if (this.selTableId == table_id && this.selFieldId == field_id) {
                    this.selTableId = null;
                    this.selFieldId = null;
                } else
                //select another field of a table
                if (this.selTableId == table_id && this.selFieldId != field_id) {
                    this.selFieldId = field_id;
                }

                //do action between two tables
                if (this.selTableId && this.selFieldId && table_id && field_id
                    && this.selTableId != table_id && this.selFieldId != field_id
                ) {
                    //create an RC if THIS table is selected
                    if (this.selTableId == this.tableMeta.id || table_id == this.tableMeta.id) {
                        let otherTbId = this.selTableId == this.tableMeta.id ? table_id : this.selTableId;
                        let thisFldId = this.selTableId == this.tableMeta.id ? this.selFieldId : field_id;
                        let otherFldId = this.selTableId == this.tableMeta.id ? field_id : this.selFieldId;

                        let thisFld = _.find(this.tableMeta._fields, {id: Number(thisFldId)}) || {};
                        let otherTable = _.find(this.$root.settingsMeta.available_tables, {id: Number(otherTbId)}) || {};
                        let otherField = _.find(otherTable._fields, {id: Number(otherFldId)}) || {};

                        RefCondEndpoints.addRefGroup(this.tableMeta, {
                            table_id: this.tableMeta.id,
                            ref_table_id: otherTbId,
                            name: this.tableMeta.name + thisFld.name + 'To' + otherTable.name + otherField.name,
                        }).then((refCond) => {
                            RefCondEndpoints.addRefGroupItem(refCond, {
                                compared_field_id: otherFldId,
                                compared_operator: '=',
                                table_field_id: thisFldId,
                                group_clause: 'A',
                                item_type: 'P2S',
                                table_ref_condition_id: refCond.id,
                            }).then(() => {
                                this.selTableId = null;
                                this.selFieldId = null;
                                this.fullRedraw();
                            });
                        });
                    }
                    //select another table if THIS is not selected
                    else {
                        this.selTableId = table_id;
                        this.selFieldId = field_id;
                    }
                }
            },
            storeMapPositions(positions) {
                _.each(positions, (position) => {
                    MapPosition.storePosition(position);
                });
            },
            refreshLayout(column) {
                MapPosition.deleteLayout(this.tableMeta.id, column).then((data) => {
                    this.tableMeta._rcmap_positions = data;
                    this.fullRedraw();
                });
            },
        },
        mounted() {
            this.canvas_x = this.$refs.rcmap_canvas.clientWidth;
            this.canvas_y = this.$refs.rcmap_canvas.clientHeight;
            this.boundings = this.$refs.rcmap_canvas.getBoundingClientRect();
            this.fullRedraw();

            eventBus.$on('ref-conditions-popup-closed', this.fullRedraw);
        },
        beforeDestroy() {
            eventBus.$off('ref-conditions-popup-closed', this.fullRedraw);
        }
    }
</script>

<style lang="scss" scoped>
.canvas_wrap {
    background-color: white;
    overflow: hidden;
}
.visible_map_button {
    position: absolute;
    top: 10px;
    right: 10px;
    z-index: 200;
}
</style>