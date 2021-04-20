<template>
    <td :style="getCellStyle"
        class="td-custom"
        ref="td"
        draggable="true"
        @dragstart="$emit('drag-start')"
        @dragover.prevent=""
        @dragenter="$emit('drag-enter')"
        @dragend="$emit('drag-end')"
        @drop="$emit('drop-event')"
    >
        <div class="td-wrapper" :style="getTdWrappStyle">

            <div class="wrapper-inner" :style="getWrapperStyle">
                <div class="inner-content">

                    {{ $root.uniqName(tableRow['name']) }}

                </div>
            </div>

        </div>
    </td>
</template>

<script>
    import CellStyleMixin from '../_Mixins/CellStyleMixin.vue';

    export default {
        name: "SettingsDisplayNameCell",
        mixins: [
            CellStyleMixin,
        ],
        components: {
        },
        data: function () {
            return {
            }
        },
        props:{
            tableHeader: Object,
            tableRow: Object,
            cellHeight: Number,
            maxCellRows: Number,
        },
        watch: {
        },
        computed: {
        },
        methods: {
        },
        mounted() {
        },
        beforeDestroy() {
        }
    }
</script>

<style lang="scss" scoped="">
    @import "CustomCell.scss";

    .td-custom {
        cursor: pointer;
    }
</style>