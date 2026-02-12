<template>
    <td :style="getCellStyle()"
        class="td-custom"
        ref="td"
    >
        <div class="td-wrapper" :style="getTdWrappStyle()">

            <div class="wrapper-inner" :style="getWrapperStyle()">
                <div class="inner-content">
                    <div v-html="showField()"></div>
                </div>
            </div>

        </div>
    </td>
</template>

<script>
import CellStyleMixin from '../_Mixins/CellStyleMixin.vue';

import {SpecialFuncs} from "../../classes/SpecialFuncs";

export default {
        name: "CustomCellInvitations",
        mixins: [
            CellStyleMixin,
        ],
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
            tableMeta: Object,//style mixin
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
                if (this.tableHeader.field === 'rewarded') {
                    return (this.tableRow.status == 2 ? '$'+this.tableRow.rewarded : '$0');
                }
                else {
                    return SpecialFuncs.showhtml(this.tableHeader, this.tableRow, this.tableRow[this.tableHeader.field]);
                }
            },
        },
    }
</script>

<style lang="scss" scoped>
    @import "CustomCell";
</style>