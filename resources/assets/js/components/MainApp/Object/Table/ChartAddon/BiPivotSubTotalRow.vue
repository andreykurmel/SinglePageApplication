<template>
    <!-- sub total rows (Level) -->
    <tr :class="cursubtot" class="sub_tot_wrap">

        <!-- vertical headers -->
        <td v-if="gr_level > 1 && l1_fld_v && (!pivot_spanned || isFirstCell(i1, i2, i3, i4, i5, 1))"
            :rowspan="(pivot_spanned ? lenForSpan('vert', v_l1) : 1)">
            <bi-pivot-header
                    :table-meta="table_meta"
                    :edit-vals="showHead('vert', v_l1)"
                    :corr-db="corr_dbs.vert_l1"
                    :can-links="pivot_vert.l1_show_links"
                    :vert_widths="vert_widths[1]"
                    @show-src-record="showSrcRecord"
                    @start-resize="startResizes()"
                    @resize-finished="saveResizes()"
            ></bi-pivot-header>
        </td>
        <td v-if="gr_level > 2 && l2_fld_v && (!pivot_spanned || isFirstCell(i1, i2, i3, i4, i5, 2))"
            :rowspan="(pivot_spanned ? lenForSpan('vert', v_l1, v_l2) : 1)">
            <bi-pivot-header
                    :table-meta="table_meta"
                    :edit-vals="showHead('vert', v_l1, v_l2)"
                    :corr-db="corr_dbs.vert_l2"
                    :can-links="pivot_vert.l2_show_links"
                    :vert_widths="vert_widths[2]"
                    @show-src-record="showSrcRecord"
                    @start-resize="startResizes()"
                    @resize-finished="saveResizes()"
            ></bi-pivot-header>
        </td>
        <td v-if="gr_level > 3 && l3_fld_v && (!pivot_spanned || isFirstCell(i1, i2, i3, i4, i5, 3))"
            :rowspan="(pivot_spanned ? lenForSpan('vert', v_l1, v_l2, v_l3) : 1)">
            <bi-pivot-header
                    :table-meta="table_meta"
                    :edit-vals="showHead('vert', v_l1, v_l2, v_l3)"
                    :corr-db="corr_dbs.vert_l3"
                    :can-links="pivot_vert.l3_show_links"
                    :vert_widths="vert_widths[3]"
                    @show-src-record="showSrcRecord"
                    @start-resize="startResizes()"
                    @resize-finished="saveResizes()"
            ></bi-pivot-header>
        </td>
        <td v-if="gr_level > 4 && l4_fld_v && (!pivot_spanned || isFirstCell(i1, i2, i3, i4, i5, 4))"
            :rowspan="(pivot_spanned ? lenForSpan('vert', v_l1, v_l2, v_l3, v_l4) : 1)">
            <bi-pivot-header
                    :table-meta="table_meta"
                    :edit-vals="showHead('vert', v_l1, v_l2, v_l3, v_l4)"
                    :corr-db="corr_dbs.vert_l4"
                    :can-links="pivot_vert.l4_show_links"
                    :vert_widths="vert_widths[4]"
                    @show-src-record="showSrcRecord"
                    @start-resize="startResizes()"
                    @resize-finished="saveResizes()"
            ></bi-pivot-header>
        </td>
        <!-- ^^^^^ -->

        <td v-for="i in (spanned ? 1 : c_s_loc)"
            :colspan="(spanned ? c_s_loc : 1)"
            :class="cursubtot"
            style="text-align: right"
        >
            <span v-if="gr_level === 1">Grand Total</span>
            <span v-else="">Sub Total</span>
        </td>
        <template v-for="(l1_h) in sorted_values.hor_l1" v-if="showVal('hor',l1_h)">
            <template v-for="(l2_h) in sorted_values.hor_l2" v-if="showVal('hor',l1_h,l2_h)">
                <template v-for="(l3_h) in sorted_values.hor_l3" v-if="showVal('hor', l1_h, l2_h, l3_h)">

                    <template v-if="isSubTot(l1_h) ? showVal('hor', l1_h)
                                    : isSubTot(l2_h) ? showVal('hor', l1_h, l2_h)
                                    : isSubTot(l3_h) ? showVal('hor', l1_h, l2_h, l3_h) : false">
                        <td class="data-cell ctrn"
                            v-for="ma in multi_about"
                            :class="cursubtot"
                            :style="getStyleCell(v_l1, v_l2, l1_h, l2_h)"
                        >
                            <div>
                                <span v-if="isSubTot(l3_h)" class="subtotal-clr--3">{{ subTotalCell(ma, 'vert_l1', v_l1, 'vert_l2', v_l2, 'vert_l3', v_l3, 'vert_l4', v_l4, 'hor_l1', l1_h, 'hor_l2', l2_h) }}</span>
                                <span v-else-if="isSubTot(l2_h)" class="subtotal-clr--2">{{ subTotalCell(ma, 'vert_l1', v_l1, 'vert_l2', v_l2, 'vert_l3', v_l3, 'vert_l4', v_l4, 'hor_l1', l1_h) }}</span>
                                <span v-else-if="isSubTot(l1_h)" class="subtotal-clr--1">{{ subTotalCell(ma, 'vert_l1', v_l1, 'vert_l2', v_l2, 'vert_l3', v_l3, 'vert_l4', v_l4) }}</span>
                            </div>
                            <div v-if="about_2 && all_settings.pivot_table.stack_about">
                                <span v-if="isSubTot(l3_h)" class="subtotal-clr--3">{{ subTotalCell(2, 'vert_l1', v_l1, 'vert_l2', v_l2, 'vert_l3', v_l3, 'vert_l4', v_l4, 'hor_l1', l1_h, 'hor_l2', l2_h) }}</span>
                                <span v-else-if="isSubTot(l2_h)" class="subtotal-clr--2">{{ subTotalCell(2, 'vert_l1', v_l1, 'vert_l2', v_l2, 'vert_l3', v_l3, 'vert_l4', v_l4, 'hor_l1', l1_h) }}</span>
                                <span v-else-if="isSubTot(l1_h)" class="subtotal-clr--1">{{ subTotalCell(2, 'vert_l1', v_l1, 'vert_l2', v_l2, 'vert_l3', v_l3, 'vert_l4', v_l4) }}</span>
                            </div>
                            <div v-if="about_3 && all_settings.pivot_table.stack_about">
                                <span v-if="isSubTot(l3_h)" class="subtotal-clr--3">{{ subTotalCell(3, 'vert_l1', v_l1, 'vert_l2', v_l2, 'vert_l3', v_l3, 'vert_l4', v_l4, 'hor_l1', l1_h, 'hor_l2', l2_h) }}</span>
                                <span v-else-if="isSubTot(l2_h)" class="subtotal-clr--2">{{ subTotalCell(3, 'vert_l1', v_l1, 'vert_l2', v_l2, 'vert_l3', v_l3, 'vert_l4', v_l4, 'hor_l1', l1_h) }}</span>
                                <span v-else-if="isSubTot(l1_h)" class="subtotal-clr--1">{{ subTotalCell(3, 'vert_l1', v_l1, 'vert_l2', v_l2, 'vert_l3', v_l3, 'vert_l4', v_l4) }}</span>
                            </div>
                        </td>
                    </template>

                    <template v-if="!isSubTot(l1_h) && !isSubTot(l2_h) && !isSubTot(l3_h)">
                        <td class="data-cell ctrn"
                            v-for="ma in multi_about"
                            :class="cursubtot"
                            :style="getStyleCell(v_l1, v_l2, l1_h, l2_h)"
                        >
                            <div>
                                <span :class="curcolor">{{ subTotalCell(ma, 'vert_l1', v_l1, 'vert_l2', v_l2, 'vert_l3', v_l3, 'vert_l4', v_l4, 'hor_l1', l1_h, 'hor_l2', l2_h, 'hor_l3', l3_h) }}</span>
                            </div>
                            <div v-if="about_2 && all_settings.pivot_table.stack_about">
                                <span :class="curcolor">{{ subTotalCell(2, 'vert_l1', v_l1, 'vert_l2', v_l2, 'vert_l3', v_l3, 'vert_l4', v_l4, 'hor_l1', l1_h, 'hor_l2', l2_h, 'hor_l3', l3_h) }}</span>
                            </div>
                            <div v-if="about_3 && all_settings.pivot_table.stack_about">
                                <span :class="curcolor">{{ subTotalCell(3, 'vert_l1', v_l1, 'vert_l2', v_l2, 'vert_l3', v_l3, 'vert_l4', v_l4, 'hor_l1', l1_h, 'hor_l2', l2_h, 'hor_l3', l3_h) }}</span>
                            </div>
                        </td>
                    </template>

                </template>
            </template>
        </template>

        <!-- Actions -->
        <td v-if="gr_level <= 2 && pivot_spanned" :class="cursubtot"></td>
        <td v-else-if="pivot_spanned && isFirstCell(i1, i2, i3, i4, i5, 2)" :rowspan="lenForSpan('vert', v_l1, v_l2)">
            <span class="indeterm_check__wrap">
                <span class="indeterm_check" @click="toggleActive('row', v_l1, v_l2)">
                    <i v-if="!isExcluded('row', v_l1, v_l2)" class="glyphicon glyphicon-ok group__icon"></i>
                </span>
            </span>
        </td>
        <!-- ^^^^^ -->
    </tr>
    <!-- ^^^^^ -->
</template>

<script>
    import {SpecialFuncs} from '../../../../../classes/SpecialFuncs';

    import BiPivotMixin from "./BiPivotMixin";

    import BiPivotHeader from "./BiPivotHeader";

    export default {
        components: {
            BiPivotHeader,
        },
        mixins: [
            BiPivotMixin,
        ],
        name: "BiPivotSubTotalRow",
        data: function () {
            return {
            }
        },
        props:{
            i1: String|Number,
            i2: String|Number,
            i3: String|Number,
            i4: String|Number,
            i5: String|Number,
            v_l1: String|Number,
            v_l2: String|Number,
            v_l3: String|Number,
            v_l4: String|Number,
            v_l5: String|Number,
            gr_level: Number,
            /* For Mixin */
            spanned: Boolean,
            all_settings: Object,
            table_meta: Object,
            all_variants: Object,
            show_matrix: Object,
            sorted_values: Object,
            corr_dbs: Object,
            about_1: Array,
            about_2: Array,
            about_3: Array,
            pivot: Object,
        },
        computed: {
            subtotlow() {
                return 'inner-subtotal--'+(this.gr_level-1);
            },
            cursubtot() {
                return 'inner-subtotal--'+(this.gr_level);
            },
            curcolor() {
                return 'subtotal-clr--'+(this.gr_level);
            },
            c_s_loc() {
                return this.c_s-(this.gr_level-1);
            },
        },
        methods: {
            subtotspec(l1_h, l2_h, l3_h) {
                return this.cursubtot;
            },
        },
    }
</script>

<style lang="scss" scoped>
    @import "BiModule";

    .sub_tot_wrap {
        td {
            position: relative;
            border-right: 1px solid #d3e0e9;
            border-bottom: 1px solid #d3e0e9;
        }
    }
</style>