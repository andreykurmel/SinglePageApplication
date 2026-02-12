<template>
    <div class="full-height relative">
        <div class="block-header">
            <button class="btn btn-default btn-sm" :style="textSysStyle" :class="{active : activeTab === 'individuals'}" @click="activeTab = 'individuals'">
                Individuals
            </button>
            <button class="btn btn-default btn-sm" :style="textSysStyle" :class="{active : activeTab === 'combos'}" @click="activeTab = 'combos'">
                Combos
            </button>
        </div>
        <div class="block-body">
            <div class="full-height" v-show="activeTab === 'individuals'">
                <filters-block
                    v-if="tableMeta && input_filters"
                    :table-meta="tableMeta"
                    :input_filters="input_filters"
                    @changed-filter="changedFilter"
                ></filters-block>
            </div>
            <div class="full-height p5" v-show="activeTab === 'combos'">
                <filters-combo-block
                    v-if="tableMeta && input_filters"
                    :table-meta="tableMeta"
                    :input_filters="input_filters"
                    :curr-changed="currChanged"
                    @curr-updated="(val) => { currChanged = val; }"
                    @applied-saved-filter="(filters) => { $emit('applied-saved-filter', filters); }"
                ></filters-combo-block>
            </div>
        </div>
    </div>
</template>

<script>
    import CellStyleMixin from "../_Mixins/CellStyleMixin";

    import FiltersBlock from "./FiltersBlock";
    import SelectBlock from "./SelectBlock";
    import {eventBus} from "../../app";
    import FiltersComboBlock from "./FiltersComboBlock";

    export default {
        name: 'FiltersBlockWithCombos',
        components: {
            FiltersComboBlock,
            SelectBlock,
            FiltersBlock
        },
        mixins: [
            CellStyleMixin,
        ],
        data() {
            return {
                currChanged: false,
                activeTab: 'individuals',
            }
        },
        props: {
            tableMeta: Object,
            input_filters: Array,
            availableColumns: Array,
            no_right_click: Boolean,
        },
        computed: {
        },
        methods: {
            changedFilter() {
                this.currChanged = true;
                this.$emit('changed-filter');
            },
        },
    }
</script>

<style lang="scss" scoped>
    .block-header {
        position: absolute;
    }
    .block-body {
        position: absolute;
        top: 27px;
        bottom: 0;
        left: 0;
        right: 0;
        background: white;
        border: 1px solid #ccc;
    }
</style>