<template>
    <div :style="getPosition()" class="map-table">
        <div :style="hdrColor()"
             class="maptable-header"
             draggable="true"
             @click="toggleMap()"
             @drag="dragMapDo"
             @dragend="dragMapEnd"
             @dragstart="dragMapStart"
        >
            <i class="fas fa-table"></i> {{ mapElem.meta.name }}
        </div>
        <div v-show="mapElem.position.opened" class="maptable-fields">
            <div style="position: absolute; right: 0;">
                <input v-model="mapElem.position.used_only"
                       class="no-margin pointer"
                       title="Used only"
                       type="checkbox"
                       @change="storePosition()"
                >
            </div>
            <div v-for="fld in mapElem.meta._fields"
                 v-if="showFld(fld)"
                 :id="'rcmp_'+mapElem.meta.id+'_fld_'+fld.id"
                 :style="{backgroundColor: fld.id == selFieldId ? '#CFC' : null}"
                 class="fld-wrapper"
                 @click="selectField(fld)"
            >
                {{ fld.name }}
            </div>
        </div>
    </div>
</template>

<script>
import {MapTable} from "./MapTable";

import RcMapMixin from './RcMapMixin.vue';

export default {
    name: "RcMapTable",
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
        }
    },
    props: {
        tableMeta: Object,
        mapElem: MapTable,
        canvas_x: Number,
        canvas_y: Number,
        selFieldId: Number,
    },
    methods: {
        hdrColor() {
            let stl = {
                color: 'black',
            };

            if (this.mapElem.meta.user_id != this.$root.user.id) {
                stl.color = 'darkgreen';
            }
            if (this.mapElem.meta.is_public) {
                stl.color = 'orangered';
            }
            if (this.mapElem.id == this.tableMeta.id) {
                stl.color = 'blue';
            }

            return stl;
        },
        toggleMap() {
            this.mapElem.position.opened = !this.mapElem.position.opened;
            this.storePosition();
        },
        storePosition() {
            this.mapElem.positionToBackend(1);
            this.$emit('position-was-updated');
        },
        showFld(fld) {
            return this.$root.systemFieldsNoId.indexOf(fld.field) === -1
                && (
                    !this.mapElem.position.used_only
                    ||
                    this.fieldIsUsedInRefCond(fld)
                );
        },
        fieldIsUsedInRefCond(fld) {
            return _.find(this.tableMeta._ref_conditions, (rc) => {
                return (rc.table_id == this.mapElem.meta.id || rc.ref_table_id == this.mapElem.meta.id)
                    &&
                    _.find(rc._items, (it) => {
                        return it.table_field_id == fld.id || it.compared_field_id == fld.id;
                    });
            });
        },
        selectField(fld) {
            this.$emit('selected-field', this.mapElem.meta.id, fld.id);
        },
    },
    mounted() {
        this.setSizes();
    },
    beforeDestroy() {
    }
}
</script>

<style lang="scss" scoped>
.map-table {
    position: absolute;
    z-index: 100;
    background-color: #EEEEEE;
    padding: 5px 10px;
    border-radius: 5px;
    width: 150px;

    .maptable-header {
        cursor: pointer;
        white-space: nowrap;
        width: 100%;
        text-overflow: ellipsis;
        overflow: hidden;
        font-weight: bold;
    }

    .maptable-fields {
        overflow-x: hidden;
        overflow-y: auto;
        max-height: 200px;
        position: relative;
    }

    .fld-wrapper {
        margin-bottom: 3px;
        background: white;
        padding: 0 3px;
        cursor: pointer;
    }
}
</style>