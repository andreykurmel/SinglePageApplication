<template>
    <div>
        <div v-if="currentTablePosition" :style="getLinePosition('currentTablePosition', 'horStart')"
             class="rc-line"></div>
        <div v-if="currentTablePosition" :style="getLinePosition('currentTablePosition', 'vert')"
             class="rc-line"></div>
        <div v-if="currentTablePosition" :style="getLinePosition('currentTablePosition', 'horEnd')"
             class="rc-line"></div>

        <div :style="getPosition()" class="map-refcond" @click="rcClicked">
            <div :style="{color: mapElem.id == tableMeta.id ? 'blue' : 'black'}"
                 class="maprefcond-title"
                 draggable="true"
                 @drag="dragMapDo"
                 @dragend="dragMapEnd"
                 @dragstart="dragMapStart"
            >
                {{ mapElem.refCond.name }}
            </div>
        </div>

        <div v-if="refTablePosition" :style="getLinePosition('refTablePosition', 'horStart')"
             class="rc-line"></div>
        <div v-if="refTablePosition" :style="getLinePosition('refTablePosition', 'vert')"
             class="rc-line"></div>
        <div v-if="refTablePosition" :style="getLinePosition('refTablePosition', 'horEnd')"
             class="rc-line"></div>
    </div>
</template>

<script>
import {eventBus} from "../../../../../../app";

import {MapRefCond} from "./MapRefCond";

import RcMapMixin from './RcMapMixin.vue';

export default {
    name: "RcMapObject",
    mixins: [
        RcMapMixin,
    ],
    components: {},
    data() {
        return {
            initX: 0,
            initY: 0,
            dragOffsetX: 0,
            dragOffsetY: 0,
            currentTablePosition: null,
            refTablePosition: null,
        }
    },
    props: {
        tableMeta: Object,
        mapElem: MapRefCond,
        canvas_x: Number,
        canvas_y: Number,
        boundings: DOMRect,
    },
    methods: {
        initCalculations(key) {
            let tbPosition = this.mapElem[key];

            this[key] = {};
            this[key].hasPosition = !! tbPosition;

            if (tbPosition) {
                this[key].rcPosX = (this.canvas_x * this.mapElem.position.pos_x / 100) + 10;
                this[key].rcPosY = (this.canvas_y * this.mapElem.position.pos_y / 100) + 10;
                this[key].tbPosX = (this.canvas_x * tbPosition.pos_x / 100) + 10;
                this[key].tbPosY = (this.canvas_y * tbPosition.pos_y / 100) + 10;
                this[key].middleX = (this[key].rcPosX + this[key].tbPosX) / 2;
                if (this.mapElem.refCond.table_id != this.mapElem.refCond.ref_table_id) {
                    this[key].middleX += (this[key].rcPosX > this[key].tbPosX ? -1 : 1) * (this.canvas_x / 7 * this.percentOffsetFromMiddle());
                    this[key].middleX += (this[key].rcPosX > this[key].tbPosX ? 75 : 0); // Move left "vert-lines" a bit to the right
                }

                this[key].relFld = this.findRelatedField(tbPosition.object_id);
                this[key].ln_color = this.mapElem.position.__ln_color;
            }
        },
        getLinePosition(key, direction) {
            let calcs = this[key];
            let style = {};

            if (calcs.hasPosition && direction === 'vert') {
                let t = calcs.rcPosY;
                let b = calcs.relFld ? calcs.relFld.y : calcs.tbPosY;
                style = {
                    left: calcs.middleX + 'px',
                    top: (t < b ? t : b) + 'px',
                    width: 0,
                    height: (t < b ? b - t : t - b) + 1 + 'px',
                };
            }

            if (calcs.hasPosition && direction === 'horStart') {
                let l = calcs.tbPosX;
                let r = calcs.middleX;
                style = {
                    left: (l < r ? l : r) + 'px',
                    top: (calcs.relFld ? calcs.relFld.y : calcs.tbPosY) + 'px',
                    width: (l < r ? r - l : l - r) + 1 + 'px',
                    height: 0,
                };
            }

            if (calcs.hasPosition && direction === 'horEnd') {
                let l = calcs.rcPosX;
                let r = calcs.middleX;
                style = {
                    left: (l < r ? l : r) + 'px',
                    top: calcs.rcPosY + 'px',
                    width: (l < r ? r - l : l - r) + 1 + 'px',
                    height: 0,
                };
            }

            let borderColor = calcs.ln_color || '#000'; // Connection line color
            if (this.mapElem.refCond.table_id == this.mapElem.refCond.ref_table_id) {
                style.border = '1px dashed ' + borderColor;
            } else {
                style.border = '1px solid ' + borderColor;
            }

            return style;
        },
        findRelatedField(table_id) {
            let fldId = null;
            _.each(this.mapElem.refCond._items, (it) => {
                if (this.mapElem.refCond.table_id == table_id && !fldId) {
                    fldId = it.table_field_id;
                }
                if (this.mapElem.refCond.ref_table_id == table_id && !fldId) {
                    fldId = it.compared_field_id;
                }
            });

            let elem = document.getElementById('rcmp_' + table_id + '_fld_' + fldId);
            let bounds = elem ? elem.getBoundingClientRect() : null;

            return bounds && bounds.left && bounds.top ? {
                x: ((bounds.left + bounds.right) / 2) - this.boundings.left,
                y: ((bounds.top + bounds.bottom) / 2) - this.boundings.top,
            } : null;
        },
        rcClicked() {
            eventBus.$emit('show-ref-conditions-popup', this.tableMeta.db_name, this.mapElem.id);
        },
    },
    mounted() {
        this.setSizes();
        this.$nextTick(() => {
            if (this.mapElem.currentTablePosition) {
                this.initCalculations('currentTablePosition');
            }
            if (this.mapElem.refTablePosition) {
                this.initCalculations('refTablePosition');
            }
        });
    },
    beforeDestroy() {
    }
}
</script>

<style lang="scss" scoped>
.map-refcond {
    position: absolute;
    z-index: 150;
    background-color: #CCEEEE;
    padding: 5px 10px;
    border-radius: 5px;

    .maprefcond-title {
        cursor: pointer;
        white-space: nowrap;
        width: 100%;
        text-overflow: ellipsis;
        overflow: hidden;
        font-weight: bold;
    }
}

.rc-line {
    position: absolute;
    z-index: 50;
}
</style>