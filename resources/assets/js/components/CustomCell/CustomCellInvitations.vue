<template>
    <td :style="getCellStyle"
        class="td-custom"
        ref="td"
    >
        <div class="td-wrapper" :style="getTdWrappStyle">

            <div class="wrapper-inner" :style="getWrapperStyle">
                <div class="inner-content">
                    <div v-html="showField()"></div>
                </div>
            </div>

        </div>
    </td>
</template>

<script>
    import CellStyleMixin from '../_Mixins/CellStyleMixin.vue';

    export default {
        name: "CustomCellInvitations",
        mixins: [
            CellStyleMixin,
        ],
        inject: {
            reactive_provider: {
                from: 'reactive_provider',
                default: () => { return {} }
            }
        },
        data: function () {
            return {
                editing: false,
                statuses: [
                    {txt:'Added', cls: 'red'},
                    {txt:'Invited', cls: 'yellow'},
                    {txt:'Accepted', cls: 'green'},
                ]
            }
        },
        props:{
            tableHeader: Object,
            tableRow: Object,
            cellHeight: Number,
            maxCellRows: Number,
            isAddRow: Boolean,
            user: Object,
        },
        methods: {
            inArray(item, array) {
                return $.inArray(item, array) > -1;
            },
            showField() {
                let res = '';

                if (this.tableHeader.field === 'rewarded') {
                    res = (this.tableRow.status == 2 ? '$'+this.tableRow.rewarded : '$0');
                }
                else {
                    res = this.tableRow[this.tableHeader.field];
                }

                return this.$root.strip_tags(res);
            },
        },
    }
</script>

<style lang="scss" scoped>
    @import "CustomCell";
</style>