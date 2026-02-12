<template>
    <div class="custom-table-wrapper full-frame flex" :style="tbAlign">

        <div v-if="cannot_draw_error" class="flex flex--center full-height bold">{{ cannot_draw_error }}</div>

        <div v-else-if="max_cell_len">

            <table ref="pivot_table" class="pivot-table">
                <colgroup>
                    <col v-for="i in c_s" :style="{width: vert_widths[i].width+'px'}"/>
                    <col v-for="i in spanLen()" :style="{width: '5px'}"/>
                    <col v-if="pivot_spanned && pivot_activness" :style="{width: '5px'}"/>
                </colgroup>
                <tr>
                    <!-- Main title -->
                    <th v-for="i in (pivot_spanned ? 1 : titleColSpan())" :colspan="(pivot_spanned ? titleColSpan() : 1)">
                        <span>{{ pivot_labels.general || '' }}</span>
                    </th>
                </tr>
                <tr>
                    <!-- Row/Col title -->
                    <th v-for="i in (pivot_spanned ? 1 : c_s)"
                        :colspan="(pivot_spanned ? c_s : 1)"
                        :rowspan="(pivot_spanned ? r_s+hasRowNames(true) : 1)"
                    >
                        <span>{{ pivot_labels.x_label || 'Row Description' }}</span>
                    </th>
                    <th v-for="i in noSpanLen()" :colspan="spanLen()">
                        <span>{{ pivot_labels.y_label || 'Column Description' }}</span>
                    </th>
                    <th v-if="pivot_spanned && pivot_activness" :rowspan="r_s+1">
                        <span>Active</span>
                    </th>
                </tr>
                <tr>
                    <!-- Rows names -->
                    <th v-if="!h_l1_fld && !h_l2_fld && !h_l3_fld && multi_about <= 1 && hasRowNames() && !hasAboutUnits()"
                        v-for="i in c_s"
                        :style="{border: overLVL == i ? '2px dashed #000' : null}"
                        :draggable="canEdit"
                        @mousedown.stop=""
                        @click.stop=""
                        @dragstart.stop="fromLVL = i"
                        @dragover.stop=""
                        @dragenter.stop="overLVL = i"
                        @dragend.stop="reorderLevels('vertical')"
                        style="cursor: pointer"
                    >
                        <span>{{ all_settings.pivot_table.vertical['l'+i+'_lvl_fname'] || '' }}</span>
                    </th>
                    <!-- lvl1 head -->
                    <template v-for="(l1_h) in sorted_values.hor_l1" v-if="showVal('hor', l1_h)">
                        <th v-for="i in noSpanLen(l1_h)"
                            :colspan="spanLen(l1_h)"
                            :rowspan="(pivot_spanned && isSubTot(l1_h) ? r_s : 1)"
                            :style="{ background: sorted_keys.key === 'hor_l1' && sorted_keys.val === l1_h ? '#aaa' : '' }"
                            class="sorted_th"
                            @click="sortByTh('hor_l1', l1_h)"
                        >
                            <bi-pivot-header
                                    :table-meta="table_meta"
                                    :edit-vals="showHead('hor', l1_h)"
                                    :corr-db="corr_dbs.hor_l1"
                                    :can-links="pivot_hor.l1_show_links"
                                    :vert_widths="dataWidths(l1_h)"
                                    @resize-finished="saveResizes(true)"
                                    @show-src-record="showSrcRecord"
                            ></bi-pivot-header>
                        </th>
                    </template>
                </tr>
                <tr v-if="h_l1_fld">
                    <!-- Rows names -->
                    <th v-if="!h_l2_fld && !h_l3_fld && multi_about <= 1 && hasRowNames() && !hasAboutUnits()"
                        v-for="i in c_s"
                        :style="{border: overLVL == i ? '2px dashed #000' : null}"
                        :draggable="canEdit"
                        @mousedown.stop=""
                        @click.stop=""
                        @dragstart.stop="fromLVL = i"
                        @dragover.stop=""
                        @dragenter.stop="overLVL = i"
                        @dragend.stop="reorderLevels('vertical')"
                        style="cursor: pointer"
                    >
                        <span>{{ all_settings.pivot_table.vertical['l'+i+'_lvl_fname'] || '' }}</span>
                    </th>
                    <th v-if="!pivot_spanned" v-for="i in c_s">
                        <span>{{ pivot_labels.x_label || 'Row Description' }}</span>
                    </th>
                    <!-- Checkboxes -->
                    <template v-for="(l1_h) in sorted_values.hor_l1" v-if="!isSubTot(l1_h) && showVal('hor',l1_h)">
                        <template v-for="(l2_h) in sorted_values.hor_l2" v-if="showVal('hor',l1_h,l2_h)">
                            <th :colspan="spanLen(l1_h, l2_h)" :rowspan="(pivot_spanned && isSubTot(l2_h) ? r_s-1 : 1)">
                                <span v-if="spanLen() > 1 && !isSubTot(l2_h)" class="indeterm_check__wrap">
                                    <span class="indeterm_check" @click="toggleActive('col', l1_h, l2_h)">
                                        <i v-if="!isExcluded('col', l1_h, l2_h)" class="glyphicon glyphicon-ok group__icon"></i>
                                    </span>
                                </span>
                                <bi-pivot-header
                                        v-if="isSubTot(l2_h)"
                                        :table-meta="table_meta"
                                        :edit-vals="showHead('hor', l1_h, l2_h)"
                                        :corr-db="corr_dbs.hor_l2"
                                        :can-links="pivot_hor.l2_show_links"
                                        @show-src-record="showSrcRecord"
                                ></bi-pivot-header>
                            </th>
                        </template>
                    </template>
                </tr>
                <tr v-if="h_l2_fld">
                    <!-- Rows names -->
                    <th v-if="!h_l3_fld && multi_about <= 1 && hasRowNames()"
                        v-for="i in c_s"
                        :style="{border: overLVL == i ? '2px dashed #000' : null}"
                        :draggable="canEdit"
                        @mousedown.stop=""
                        @click.stop=""
                        @dragstart.stop="fromLVL = i"
                        @dragover.stop=""
                        @dragenter.stop="overLVL = i"
                        @dragend.stop="reorderLevels('vertical')"
                        style="cursor: pointer"
                    >
                        <span>{{ all_settings.pivot_table.vertical['l'+i+'_lvl_fname'] || '' }}</span>
                    </th>
                    <th v-if="!pivot_spanned" v-for="i in c_s">
                        <span>{{ pivot_labels.x_label || 'Row Description' }}</span>
                    </th>
                    <!-- lvl2 head -->
                    <template v-for="(l1_h) in sorted_values.hor_l1" v-if="!isSubTot(l1_h) && showVal('hor',l1_h)">
                        <template v-for="(l2_h) in sorted_values.hor_l2" v-if="!isSubTot(l2_h) && showVal('hor',l1_h,l2_h)">
                            <th v-for="i in noSpanLen(l1_h, l2_h)"
                                :colspan="spanLen(l1_h, l2_h)"
                                :style="{ background: sorted_keys.key === 'hor_l2' && sorted_keys.val === l2_h ? '#aaa' : '' }"
                                class="sorted_th"
                                @click="sortByTh('hor_l2', l2_h)"
                            >
                                <bi-pivot-header
                                        :table-meta="table_meta"
                                        :edit-vals="showHead('hor', l1_h, l2_h)"
                                        :corr-db="corr_dbs.hor_l2"
                                        :can-links="pivot_hor.l2_show_links"
                                        :vert_widths="dataWidths(l1_h, l2_h)"
                                        @resize-finished="saveResizes(true)"
                                        @show-src-record="showSrcRecord"
                                ></bi-pivot-header>
                            </th>
                        </template>
                    </template>
                </tr>
                <tr v-if="h_l3_fld">
                    <!-- Rows names -->
                    <th v-if="multi_about <= 1 && hasRowNames() && !hasAboutUnits()"
                        v-for="i in c_s"
                        :style="{border: overLVL == i ? '2px dashed #000' : null}"
                        :draggable="canEdit"
                        @mousedown.stop=""
                        @click.stop=""
                        @dragstart.stop="fromLVL = i"
                        @dragover.stop=""
                        @dragenter.stop="overLVL = i"
                        @dragend.stop="reorderLevels('vertical')"
                        style="cursor: pointer"
                    >
                        <span>{{ all_settings.pivot_table.vertical['l'+i+'_lvl_fname'] || '' }}</span>
                    </th>
                    <th v-if="!pivot_spanned" v-for="i in c_s">
                        <span>{{ pivot_labels.x_label || 'Row Description' }}</span>
                    </th>
                    <!-- lvl3 head -->
                    <template v-for="(l1_h) in sorted_values.hor_l1" v-if="!isSubTot(l1_h) && showVal('hor',l1_h)">
                        <template v-for="(l2_h) in sorted_values.hor_l2" v-if="!isSubTot(l2_h) && showVal('hor',l1_h,l2_h)">
                            <template v-for="(l3_h) in sorted_values.hor_l3" v-if="showVal('hor', l1_h, l2_h, l3_h)">

                                <th :colspan="multi_about"
                                    :rowspan="(pivot_spanned && isSubTot(l3_h) ? r_s-3 : 1)"
                                    :style="{ background: sorted_keys.key === 'hor_l3' && sorted_keys.val === l3_h ? '#aaa' : '' }"
                                    class="sorted_th"
                                    @click="sortByTh('hor_l3', l3_h)"
                                >
                                    <bi-pivot-header
                                            :table-meta="table_meta"
                                            :edit-vals="showHead('hor', l1_h, l2_h, l3_h)"
                                            :corr-db="corr_dbs.hor_l3"
                                            :can-links="pivot_hor.l3_show_links"
                                            :vert_widths="dataWidths(l1_h, l2_h, l3_h)"
                                            @resize-finished="saveResizes(true)"
                                            @show-src-record="showSrcRecord"
                                    ></bi-pivot-header>
                                </th>

                            </template>
                        </template>
                    </template>
                </tr>
                <tr v-if="multi_about > 1">
                    <!-- Rows names -->
                    <th v-if="hasRowNames() && !hasAboutUnits()"
                        v-for="i in c_s"
                        :style="{border: overLVL == i ? '2px dashed #000' : null}"
                        :draggable="canEdit"
                        @mousedown.stop=""
                        @click.stop=""
                        @dragstart.stop="fromLVL = i"
                        @dragover.stop=""
                        @dragenter.stop="overLVL = i"
                        @dragend.stop="reorderLevels('vertical')"
                        style="cursor: pointer"
                    >
                        <span>{{ all_settings.pivot_table.vertical['l'+i+'_lvl_fname'] || '' }}</span>
                    </th>
                    <th v-if="!pivot_spanned" v-for="i in c_s">
                        <span>{{ pivot_labels.x_label || 'Row Description' }}</span>
                    </th>
                    <!-- Multi about -->
                    <template v-for="(l1_h) in sorted_values.hor_l1" v-if="!isSubTot(l1_h) && showVal('hor',l1_h)">
                        <template v-for="(l2_h) in sorted_values.hor_l2" v-if="!isSubTot(l2_h) && showVal('hor',l1_h,l2_h)">
                            <template v-for="(l3_h) in sorted_values.hor_l3" v-if="!isSubTot(l3_h) && showVal('hor', l1_h, l2_h, l3_h)">

                                <th v-for="ma in multi_about"
                                    :style="{border: aboutOverLVL == ma ? '2px dashed #000' : null}"
                                    :draggable="canEdit"
                                    @mousedown.stop=""
                                    @click.stop=""
                                    @dragstart.stop="aboutFromLVL = ma"
                                    @dragover.stop=""
                                    @dragenter.stop="aboutOverLVL = ma"
                                    @dragend.stop="reorderLevels('about')"
                                    style="cursor: pointer"
                                >
                                    <span @mouseover="(e) => {showCMP(e, ma, l1_h, l2_h, l3_h)}">{{ aboutName(ma) }}</span>
                                    <hover-block v-if="helpCMP(ma, l1_h, l2_h, l3_h)"
                                                 :html_str="stringCMP(ma)"
                                                 :p_left="help_left"
                                                 :p_top="help_top"
                                                 @tooltip-blur="hideCMP()"
                                    ></hover-block>
                                </th>

                            </template>
                        </template>
                    </template>
                </tr>
                <tr v-if="hasAboutUnits()">
                    <!-- Rows names -->
                    <th v-if="hasRowNames()"
                        v-for="i in c_s"
                        :style="{border: overLVL == i ? '2px dashed #000' : null}"
                        :draggable="canEdit"
                        @mousedown.stop=""
                        @click.stop=""
                        @dragstart.stop="fromLVL = i"
                        @dragover.stop=""
                        @dragenter.stop="overLVL = i"
                        @dragend.stop="reorderLevels('vertical')"
                        style="cursor: pointer"
                    >
                        <span>{{ all_settings.pivot_table.vertical['l'+i+'_lvl_fname'] || '' }}</span>
                    </th>
                    <th v-if="!pivot_spanned" v-for="i in c_s">
                        <span>{{ pivot_labels.x_label || 'Row Description' }}</span>
                    </th>
                    <!-- Units of abouts -->
                    <template v-for="(l1_h) in sorted_values.hor_l1" v-if="!isSubTot(l1_h) && showVal('hor',l1_h)">
                        <template v-for="(l2_h) in sorted_values.hor_l2" v-if="!isSubTot(l2_h) && showVal('hor',l1_h,l2_h)">
                            <template v-for="(l3_h) in sorted_values.hor_l3" v-if="!isSubTot(l3_h) && showVal('hor', l1_h, l2_h, l3_h)">

                                <th v-for="ma in multi_about" :style="aboutStyle(ma)">
                                    <span>{{ aboutUnit(ma) }}</span>
                                </th>

                            </template>
                        </template>
                    </template>
                </tr>
                <!-- end head -->

                <!-- start records -->
                <template v-for="(v_l1,i1) in filterVals(sorted_values.vert_l1, pivot.rows)">
                    <template v-for="(v_l2,i2) in filterVals(sorted_values.vert_l2, pivot.rows[v_l1])">
                        <template v-for="(v_l3,i3) in filterVals(sorted_values.vert_l3, pivot.rows[v_l1][v_l2])">
                            <template v-for="(v_l4,i4) in filterVals(sorted_values.vert_l4, pivot.rows[v_l1][v_l2][v_l3])">
                                <template v-for="(v_l5,i5) in filterVals(sorted_values.vert_l5, pivot.rows[v_l1][v_l2][v_l3][v_l4])">

                                    <!-- sub total rows (L1) -->
                                    <bi-pivot-sub-total-row v-if="isSubTot(v_l1) ? showVal('vert', v_l1)
                                                                : isSubTot(v_l2) ? showVal('vert', v_l1, v_l2)
                                                                : isSubTot(v_l3) ? showVal('vert', v_l1, v_l2, v_l3)
                                                                : isSubTot(v_l4) ? showVal('vert', v_l1, v_l2, v_l3, v_l4)
                                                                : isSubTot(v_l5) ? showVal('vert', v_l1, v_l2, v_l3, v_l4, v_l5) : false"
                                                            :i1="i1" :i2="i2" :i3="i3" :i4="i4" :i5=i5
                                                            :v_l1="v_l1" :v_l2="v_l2" :v_l3="v_l3" :v_l4="v_l4" :v_l5=v_l5
                                                            :corr_dbs="corr_dbs" :spanned="spanned" :sorted_values="sorted_values"
                                                            :gr_level="grlvl(v_l1, v_l2, v_l3, v_l4, v_l5)" :all_settings="all_settings"
                                                            :table_meta="table_meta" :all_variants="all_variants" :pivot="pivot"
                                                            :about_1="about_1" :about_2="about_2" :about_3="about_3" :about_4="about_4" :about_5="about_5"
                                                            @show-src-record="showSrcRecord" @start-resize="startResizes()" @resize-finished="saveResizes()"
                                    ></bi-pivot-sub-total-row>
                                    <!-- ^^^^^ -->

                                    <tr v-if="!isSubTot(v_l1) && !isSubTot(v_l2) && !isSubTot(v_l3) && !isSubTot(v_l4) && !isSubTot(v_l5)">
                                        <!-- vertical headers -->
                                        <td v-if="(!pivot_spanned || isFirstCell(i1, i2, i3, i4, i5, 1))"
                                            :rowspan="(pivot_spanned ? lenForSpan('vert', v_l1) : 1)">
                                            <bi-pivot-header
                                                    v-if="l1_fld_v"
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
                                        <td v-if="l2_fld_v && (!pivot_spanned || isFirstCell(i1, i2, i3, i4, i5, 2))"
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
                                        <td v-if="l3_fld_v && (!pivot_spanned || isFirstCell(i1, i2, i3, i4, i5, 3))"
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
                                        <td v-if="l4_fld_v && (!pivot_spanned || isFirstCell(i1, i2, i3, i4, i5, 4))"
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
                                        <td v-if="l5_fld_v">
                                            <bi-pivot-header
                                                    :table-meta="table_meta"
                                                    :edit-vals="showHead('vert', v_l1, v_l2, v_l3, v_l4, v_l5, true)"
                                                    :corr-db="corr_dbs.vert_l5"
                                                    :can-links="pivot_vert.l5_show_links"
                                                    :vert_widths="vert_widths[5]"
                                                    @show-src-record="showSrcRecord"
                                                    @start-resize="startResizes()"
                                                    @resize-finished="saveResizes()"
                                            ></bi-pivot-header>
                                        </td>
                                        <!-- ^^^^^ -->

                                        <!-- data cells -->
                                        <template v-for="(l1_h) in sorted_values.hor_l1" v-if="showVal('hor',l1_h)">
                                            <template v-for="(l2_h) in sorted_values.hor_l2" v-if="showVal('hor',l1_h,l2_h)">
                                                <template v-for="(l3_h) in sorted_values.hor_l3" v-if="showVal('hor', l1_h, l2_h, l3_h)">

                                                    <template v-if="isSubTot(l1_h) ? showVal('hor', l1_h)
                                                                : isSubTot(l2_h) ? showVal('hor', l1_h, l2_h)
                                                                : isSubTot(l3_h) ? showVal('hor', l1_h, l2_h, l3_h) : false">
                                                        <td class="data-cell"
                                                            v-for="ma in multi_about"
                                                            :class="{'inner-subtotal--1': isSubTot(l1_h), 'inner-subtotal--2': isSubTot(l2_h), 'inner-subtotal--3': isSubTot(l3_h)}"
                                                            :style="getStyleCell(v_l1, v_l2, l1_h, l2_h)"
                                                        >
                                                            <div>
                                                                <span v-if="isSubTot(l1_h)" class="subtotal-clr--1">{{ subTotalCell(ma, 'vert_l1', v_l1, 'vert_l2', v_l2, 'vert_l3', v_l3, 'vert_l4', v_l4, 'vert_l5', v_l5) }}</span>
                                                                <span v-else-if="isSubTot(l2_h)" class="subtotal-clr--2">{{ subTotalCell(ma, 'vert_l1', v_l1, 'vert_l2', v_l2, 'vert_l3', v_l3, 'vert_l4', v_l4, 'vert_l5', v_l5, 'hor_l1', l1_h) }}</span>
                                                                <span v-else-if="isSubTot(l3_h)" class="subtotal-clr--3">{{ subTotalCell(ma, 'vert_l1', v_l1, 'vert_l2', v_l2, 'vert_l3', v_l3, 'vert_l4', v_l4, 'hor_l1', 'vert_l5', v_l5, l1_h, 'hor_l2', l2_h) }}</span>
                                                            </div>
                                                            <div v-if="about_2 && all_settings.pivot_table.stack_about">
                                                                <span v-if="isSubTot(l1_h)" class="subtotal-clr--1">{{ subTotalCell(2, 'vert_l1', v_l1, 'vert_l2', v_l2, 'vert_l3', v_l3, 'vert_l4', v_l4, 'vert_l5', v_l5) }}</span>
                                                                <span v-else-if="isSubTot(l2_h)" class="subtotal-clr--2">{{ subTotalCell(2, 'vert_l1', v_l1, 'vert_l2', v_l2, 'vert_l3', v_l3, 'vert_l4', v_l4, 'vert_l5', v_l5, 'hor_l1', l1_h) }}</span>
                                                                <span v-else-if="isSubTot(l3_h)" class="subtotal-clr--3">{{ subTotalCell(2, 'vert_l1', v_l1, 'vert_l2', v_l2, 'vert_l3', v_l3, 'vert_l4', v_l4, 'hor_l1', 'vert_l5', v_l5, l1_h, 'hor_l2', l2_h) }}</span>
                                                            </div>
                                                            <div v-if="about_3 && all_settings.pivot_table.stack_about">
                                                                <span v-if="isSubTot(l1_h)" class="subtotal-clr--1">{{ subTotalCell(3, 'vert_l1', v_l1, 'vert_l2', v_l2, 'vert_l3', v_l3, 'vert_l4', v_l4, 'vert_l5', v_l5) }}</span>
                                                                <span v-else-if="isSubTot(l2_h)" class="subtotal-clr--2">{{ subTotalCell(3, 'vert_l1', v_l1, 'vert_l2', v_l2, 'vert_l3', v_l3, 'vert_l4', v_l4, 'vert_l5', v_l5, 'hor_l1', l1_h) }}</span>
                                                                <span v-else-if="isSubTot(l3_h)" class="subtotal-clr--3">{{ subTotalCell(3, 'vert_l1', v_l1, 'vert_l2', v_l2, 'vert_l3', v_l3, 'vert_l4', v_l4, 'hor_l1', 'vert_l5', v_l5, l1_h, 'hor_l2', l2_h) }}</span>
                                                            </div>
                                                            <div v-if="about_4 && all_settings.pivot_table.stack_about">
                                                                <span v-if="isSubTot(l1_h)" class="subtotal-clr--1">{{ subTotalCell(4, 'vert_l1', v_l1, 'vert_l2', v_l2, 'vert_l3', v_l3, 'vert_l4', v_l4, 'vert_l5', v_l5) }}</span>
                                                                <span v-else-if="isSubTot(l2_h)" class="subtotal-clr--2">{{ subTotalCell(4, 'vert_l1', v_l1, 'vert_l2', v_l2, 'vert_l3', v_l3, 'vert_l4', v_l4, 'vert_l5', v_l5, 'hor_l1', l1_h) }}</span>
                                                                <span v-else-if="isSubTot(l3_h)" class="subtotal-clr--3">{{ subTotalCell(4, 'vert_l1', v_l1, 'vert_l2', v_l2, 'vert_l3', v_l3, 'vert_l4', v_l4, 'hor_l1', 'vert_l5', v_l5, l1_h, 'hor_l2', l2_h) }}</span>
                                                            </div>
                                                            <div v-if="about_5 && all_settings.pivot_table.stack_about">
                                                                <span v-if="isSubTot(l1_h)" class="subtotal-clr--1">{{ subTotalCell(5, 'vert_l1', v_l1, 'vert_l2', v_l2, 'vert_l3', v_l3, 'vert_l4', v_l4, 'vert_l5', v_l5) }}</span>
                                                                <span v-else-if="isSubTot(l2_h)" class="subtotal-clr--2">{{ subTotalCell(5, 'vert_l1', v_l1, 'vert_l2', v_l2, 'vert_l3', v_l3, 'vert_l4', v_l4, 'vert_l5', v_l5, 'hor_l1', l1_h) }}</span>
                                                                <span v-else-if="isSubTot(l3_h)" class="subtotal-clr--3">{{ subTotalCell(5, 'vert_l1', v_l1, 'vert_l2', v_l2, 'vert_l3', v_l3, 'vert_l4', v_l4, 'hor_l1', 'vert_l5', v_l5, l1_h, 'hor_l2', l2_h) }}</span>
                                                            </div>
                                                        </td>
                                                    </template>

                                                    <template v-if="!isSubTot(l1_h) && !isSubTot(l2_h) && !isSubTot(l3_h)">
                                                        <td class="data-cell"
                                                            v-for="ma in multi_about"
                                                            :class="{'inner-subtotal--1': isSubTot(l1_h), 'inner-subtotal--2': isSubTot(l2_h), 'inner-subtotal--3': isSubTot(l3_h)}"
                                                            :style="getStyleCell(v_l1, v_l2, l1_h, l2_h)"
                                                        >
                                                            <div :style="dataStyle(ma)">
                                                                <span v-html="findValue(ma, v_l1, v_l2, v_l3, v_l4, v_l5, l1_h, l2_h, l3_h)"></span>
                                                            </div>
                                                            <div v-if="about_2 && all_settings.pivot_table.stack_about" :style="dataStyle(2)">
                                                                <span v-html="findValue(2, v_l1, v_l2, v_l3, v_l4, v_l5, l1_h, l2_h, l3_h)"></span>
                                                            </div>
                                                            <div v-if="about_3 && all_settings.pivot_table.stack_about" :style="dataStyle(3)">
                                                                <span v-html="findValue(3, v_l1, v_l2, v_l3, v_l4, v_l5, l1_h, l2_h, l3_h)"></span>
                                                            </div>
                                                            <div v-if="about_4 && all_settings.pivot_table.stack_about" :style="dataStyle(4)">
                                                                <span v-html="findValue(3, v_l1, v_l2, v_l3, v_l4, v_l5, l1_h, l2_h, l3_h)"></span>
                                                            </div>
                                                            <div v-if="about_5 && all_settings.pivot_table.stack_about" :style="dataStyle(5)">
                                                                <span v-html="findValue(3, v_l1, v_l2, v_l3, v_l4, v_l5, l1_h, l2_h, l3_h)"></span>
                                                            </div>
                                                        </td>
                                                    </template>

                                                </template>
                                            </template>
                                        </template>

                                        <!-- Actions -->
                                        <td v-if="pivot_spanned && pivot_activness && isFirstCell(i1, i2, i3, i4, i5, 2)"
                                            :rowspan="lenForSpan('vert', v_l1, v_l2)"
                                        >
                                            <span class="indeterm_check__wrap">
                                                <span class="indeterm_check" @click="toggleActive('row', v_l1, v_l2)">
                                                    <i v-if="!isExcluded('row', v_l1, v_l2)" class="glyphicon glyphicon-ok group__icon"></i>
                                                </span>
                                            </span>
                                        </td>
                                        <!-- ^^^^^ -->
                                    </tr>
                                </template>
                            </template>
                        </template>
                    </template>
                </template>
                <!--end records-->
            </table>

            <div v-show="download_png" class="popup-wrapper">
                <div class="png-file" ref="png_file"></div>
            </div>
        </div>

        <div v-else="" class="flex flex--center full-height bold">All zero. Table not displayed.</div>

    </div>
</template>

<script>
    import {eventBus} from '../../../../../app';

    import BiPivotMixin from "./BiPivotMixin";

    import {SpecialFuncs} from '../../../../../classes/SpecialFuncs';
    import {UnitConversion} from "../../../../../classes/UnitConversion";
    import {ChartFunctions} from './ChartFunctions';

    import HeaderResizer from "../../../../CustomTable/Header/HeaderResizer";
    import HoverBlock from "../../../../CommonBlocks/HoverBlock";
    import BiPivotHeader from "./BiPivotHeader";
    import BiPivotSubTotalRow from "./BiPivotSubTotalRow";

    export default {
        components: {
            BiPivotSubTotalRow,
            BiPivotHeader,
            HoverBlock,
            HeaderResizer,
        },
        mixins: [
            BiPivotMixin,
        ],
        name: "BiPivotTable",
        data: function () {
            return {
                about_1: [],
                about_2: null,
                about_3: null,
                about_4: null,
                about_5: null,
                help_show: '',
                help_left: 0,
                help_top: 0,

                syskeys: ['__show','__span_len','__sub_total'],
                pivot: { rows: null, columns: null, },
                show_matrix: { vert: {}, hor: {}, },
                fld_keys: [],//'vert_l1', 'hor_l1', ...
                sorted_values: {},//vert_l1: [], ...
                corr_dbs: {},//vert_l1: '', ...
                all_variants: {
                    usrs: {},//vert_l1: {}, ...
                    rcs: {},//vert_l1: {}, ...
                },
                spanned: true, // for printing and copying to clipboard

                dec: 0,
                currency: false,
                download_png: false,
                max_cell_len: 0,
                cannot_draw_error: '',
                sorted_keys: { key: '', val: '' },
                fromLVL: null,
                overLVL: null,
                aboutFromLVL: null,
                aboutOverLVL: null,
            }
        },
        props: {
            table_meta: Object,
            table_data: Array,
            table_data_2: Array,
            table_data_3: Array,
            table_data_4: Array,
            table_data_5: Array,
            all_settings: Object,
            request_params: Object,
            chart_uuid: String,
            gridStack: Object,
            extFilters: Array,
            canEdit: Boolean,
        },
        computed: {
            tbAlign() {
                return {
                    alignItems: this.all_settings.vert_align,
                    justifyContent: this.all_settings.hor_align,
                };
            },
        },
        methods: {
            reorderLevels(type) {
                if (type === 'about' && this.aboutFromLVL != this.aboutOverLVL) {
                    this.all_settings = ChartFunctions.reorderSettings(this.all_settings, type, this.aboutFromLVL, this.aboutOverLVL);
                    this.$emit('active-change', 'about_reorder');
                }
                if (type === 'vertical' && this.fromLVL != this.overLVL) {
                    this.all_settings = ChartFunctions.reorderSettings(this.all_settings, type, this.fromLVL, this.overLVL);
                    this.$emit('active-change', 'vertical_reorder');
                }
                this.fromLVL = null;
                this.aboutFromLVL = null;
                this.overLVL = null;
                this.aboutOverLVL = null;
            },
            grlvl(l1, l2, l3, l4, l5) {
                return this.isSubTot(l1) ? 1 : this.isSubTot(l2) ? 2 : this.isSubTot(l3) ? 3 : this.isSubTot(l4) ? 4 : this.isSubTot(l5) ? 5 : 0;
            },
            //Mult-About
            showCMP(e, ma, l1_h, l2_h, l3_h) {
                this.help_show = ma+'_'+l1_h+'_'+l2_h+'_'+l3_h;
                this.help_left = e.clientX;
                this.help_top = e.clientY;
            },
            helpCMP(ma, l1_h, l2_h, l3_h) {
                return (this.about_2 || this.about_3 || this.about_4 || this.about_5)
                    && this.help_show
                    && this.help_show === ma+'_'+l1_h+'_'+l2_h+'_'+l3_h;
            },
            stringCMP(ma) {
                let abo = ma > 1 ? this.all_settings.pivot_table['about_'+ma] : this.all_settings.pivot_table.about;
                let str = '';
                switch (String(abo.calc_val)) {
                    case '1': str = abo.group_function; break;
                    case '0': str = 'Count'; break;
                    case '-1': str = 'Countunique'; break;
                }
                let fld = _.find(this.table_meta._fields, {field: abo.field});
                return str + (fld ? ' of '+fld.name : '');
            },
            hideCMP() {
                this.help_show = '';
            },
            aboutName(ma) {
                let abo = ma > 1 ? this.all_settings.pivot_table['about_'+ma] : this.all_settings.pivot_table.about;
                let fname = abo ? abo.lvl_fname : '';
                return fname || ('#'+ma);
            },
            aboutUnit(ma) {
                let fld = this.aboFld(ma);
                return UnitConversion.showUnit(fld, this.table_meta)
            },
            aboutStyle(ma) {
                let fld = this.aboFld(ma);
                return {
                    color: fld.unit == fld.unit_display ? '#55F' : null,
                };
            },
            startResizes() {
            },
            saveResizes(isdata) {
                let key = isdata ? 'pivot.data_widths' : 'pivot.vert_widths';
                this.$emit('active-change', key);
            },

            //EXPORT FUNCTIONS
            copyTable() {
                this.spanned = false;
                this.$nextTick(() => {
                    let arr = this.buildDataArray();
                    let stringtable = _.map(arr.h, (hh) => { return hh.name }).join('\t')
                        + '\r\n'
                        + _.map(arr.r, (row) => { return row.join('\t') }).join('\r\n');
                    SpecialFuncs.strToClipboard( stringtable );
                    Swal('Info','Table Copied to Clipboard!');
                    this.spanned = true;
                });
            },
            printTable() {
                this.spanned = false;

                this.$nextTick(() => {
                    let arr = this.buildDataArray();
                    let tbHeaders = arr.h;
                    let tbRows = arr.r;

                    if (tbHeaders.length) {
                        eventBus.$emit('print-table-data', tbHeaders, tbRows, true, 'print');
                    }

                    this.spanned = true;
                });
            },
            exportToPng(export_name) {
                let elem = this.$refs.pivot_table;
                export_to_png(elem, export_name || 'chart');
            },
            dwnFromServerTable(type, export_name) {
                this.spanned = false;

                this.$nextTick(() => {
                    let arr = this.buildDataArray();
                    let tbHeaders = arr.h;
                    let tbRows = arr.r;

                    if (tbHeaders.length) {
                        $('#dwn_chart_headers').val( JSON.stringify(tbHeaders) );
                        $('#dwn_chart_rows').val( JSON.stringify(tbRows) );
                        $('#dwn_chart_format_type').val( type );
                        $('#dwn_filename').val( export_name );
                        $('#dwn_chart').submit();
                    }

                    this.spanned = true;
                });
            },
            buildDataArray() {
                let tbHeaders = [];
                let tbRows = [];
                let rows = $(this.$refs.pivot_table).find('tr');

                if (rows.length) {

                    _.each(rows, (row) => {
                        let rr = [];
                        _.each(row.children, (td, idx) => {
                            if (td.nodeName.toLowerCase() === 'th') {

                                if (tbHeaders[idx]) {
                                    tbHeaders[idx].name.push(td.innerText);
                                } else {
                                    tbHeaders.push({
                                        name: [td.innerText],
                                        field: idx
                                    });
                                }

                            } else {
                                rr.push(td.innerText);
                            }
                        });
                        if (rr.length) {
                            tbRows.push(rr);
                        }
                    });

                    _.each(tbHeaders, (hdr) => {
                        hdr.name = hdr.name
                            .filter((n) => {return !!n;})
                            .join(' / ');
                    });

                }

                return {
                    h: tbHeaders,
                    r: tbRows
                }
            },

            chartExportButtonHandler(uuid, key, export_prefix) {
                let ftrname = this.$root.oneFilterSelected(this.extFilters, 'show') || '';
                let crtlabel = this.all_settings.pivot_table.labels.general || 'chart';
                let arr = [export_prefix, ftrname, crtlabel, moment().format('MMDDYYYY_HHmmss')];
                let export_name = _.filter(arr)
                    .map((el) => {
                        return _.trim( SpecialFuncs.strip_tags(el, '<none>') )
                    })
                    .join('_');
                if (this.chart_uuid === uuid) {
                    switch (key) {
                        case 'copy':
                            this.copyTable();
                            break;
                        case 'print':
                            this.printTable();
                            break;
                        case 'png':
                            this.exportToPng(export_name+'.png');
                            break;
                        case 'pdf':
                            this.dwnFromServerTable('pdf', export_name+'.pdf');
                            break;
                        case 'csv':
                            this.dwnFromServerTable('csv', export_name+'.csv');
                            break;
                        default: Swal('Info','Not working!');
                    }
                }
            },
            sortByTh(corr_key, corr_value) {
                this.cannot_draw_error = 'sorting...';

                if (this.sorted_keys.key === corr_key && this.sorted_keys.val === corr_value) {
                    this.sorted_keys.key = '';
                    this.sorted_keys.val = '';
                } else {
                    this.sorted_keys.key = corr_key;
                    this.sorted_keys.val = corr_value;
                }

                let verts = ['vert_l1', 'vert_l2', 'vert_l3', 'vert_l4', 'vert_l5'];
                _.each(this.fld_keys, (key) => {
                    if (this.sorted_keys.key && verts.indexOf(key) > -1) {
                        let arr = [];
                        _.each(_.uniq(_.map(this.table_data, key)), (vert) => {
                            arr.push({
                                val: vert,
                                sorted: this.subTotalCell(1, key, vert, corr_key, corr_value),
                            });
                        });
                        this.sorted_values[key] = _.map(arr.sort((a, b) => {
                            return a.sorted - b.sorted;
                        }), 'val');
                    } else {
                        this.sorted_values[key] = this.order_values(_.uniq(_.map(this.table_data, key)), this.corr_dbs[key]);
                    }
                });

                this.subtotalToSorts();
                this.cannot_draw_error = '';
            },

            //INITS
            prepareDataKeys(input) {
                //ORDER IN THIS FUNCTION IS IMPORTANT!

                let objarr = {}, objstr = {}, objobj = {};
                for (let i = 1; i <= ChartFunctions.maxLVL(); i++) {
                    this.fld_keys.push('vert_l'+i);
                    this.fld_keys.push('hor_l'+i);
                    objarr['vert_l'+i] = [];
                    objarr['hor_l'+i] = [];
                    objstr['vert_l'+i] = '';
                    objstr['hor_l'+i] = '';
                    objobj['vert_l'+i] = {};
                    objobj['hor_l'+i] = {};
                }
                this.sorted_values = _.cloneDeep(objarr);
                this.corr_dbs = _.cloneDeep(objstr);
                this.all_variants.usrs = _.cloneDeep(objobj);
                this.all_variants.rcs = _.cloneDeep(objobj);

                _.each(this.fld_keys, (key) => {
                    this.corr_dbs[key] = _.first(input)['_db_'+key] || '';
                    this.sorted_values[key] = this.order_values( _.uniq( _.map(input, key) ), this.corr_dbs[key]);

                    let usrs = {};
                    let rcs = {};
                    _.each(input, (row) => {
                        usrs = {
                            ...usrs,
                            ...this.$root.getUsrObj(row, key),
                        };
                        rcs = {
                            ...rcs,
                            ...SpecialFuncs.rcObj(row, key),
                        };
                    });
                    this.all_variants.usrs[key] = usrs;
                    this.all_variants.rcs[key] = rcs;
                });

                let allvert = this.about_1.concat(this.about_2 || [], this.about_3 || [], this.about_4 || [], this.about_5 || []);
                allvert = this.addZeroValues(allvert, 'vert');
                let allhor = this.about_1.concat(this.about_2 || [], this.about_3 || [], this.about_4 || [], this.about_5 || []);
                allhor = this.addZeroValues(allhor, 'hor');

                this.subtotalToSorts();

                this.pivot.rows = this.groupViewedData(allvert, 'vert');
                this.pivot.columns = this.groupViewedData(allhor, 'hor');
            },
            subtotalToSorts() {
                //Add computed columns 'SubTotal'
                for (let i = 1; i <= ChartFunctions.maxLVL(); i++) {
                    if (this.sorted_values['vert_l' + i].length) {
                        this.pos_subtot_is_top
                            ? this.sorted_values['vert_l' + i].unshift('__sub_total')
                            : this.sorted_values['vert_l' + i].push('__sub_total');
                    }
                    if (this.sorted_values['hor_l' + i].length) {
                        this.front_subtotal
                            ? this.sorted_values['hor_l' + i].unshift('__sub_total')
                            : this.sorted_values['hor_l' + i].push('__sub_total');
                    }
                }
            },
            addZeroValues(allinput, key) {
                key = key === 'vert' ? 'vert' : 'hor';
                //APPLY OR NOT "Hide Col If Empty" - For showing combination of all L1/L2/L3/L4/L5 levels. (Add zero values).
                let sett = key === 'vert' ? this.pivot_vert : this.pivot_hor;
                _.each(this.sorted_values[key+'_l1'], (v1) => {

                    if (!sett.l1_hide_empty || this.subTotalApp(1, key+'_l1', v1)) {

                        _.each(this.sorted_values[key + '_l2'], (v2) => {

                            if (!sett.l2_hide_empty || this.subTotalApp(1, key + '_l1', v1, key + '_l2', v2)) {
                                _.each(this.sorted_values[key + '_l3'], (v3) => {

                                    if (!sett.l3_hide_empty || this.subTotalApp(1, key + '_l1', v1, key + '_l2', v2, key + '_l3', v3)) {
                                        _.each(this.sorted_values[key + '_l4'], (v4) => {

                                            if (!sett.l4_hide_empty || this.subTotalApp(1, key + '_l1', v1, key + '_l2', v2, key + '_l3', v3, key + '_l4', v4)) {
                                                _.each(this.sorted_values[key + '_l5'], (v5) => {

                                                    if (!sett.l5_hide_empty || this.subTotalApp(1, key + '_l1', v1, key + '_l2', v2, key + '_l3', v3, key + '_l4', v4, key + '_l5', v5)) {
                                                        let empt = { y:0 };
                                                        empt[key + '_l1'] = v1;
                                                        empt[key + '_l2'] = v2;
                                                        empt[key + '_l3'] = v3;
                                                        empt[key + '_l4'] = v4;
                                                        empt[key + '_l5'] = v5;
                                                        allinput.push(empt);
                                                    }
                                                });
                                            }
                                        });
                                    }
                                });
                            }
                        });
                    }
                });
                return allinput;
            },
            groupViewedData(input, key, lvl, allshow) {
                let empobj = {
                    __show: true,
                    __span_len: 1,
                };
                lvl = lvl || 1;
                key = key === 'vert' ? 'vert' : 'hor';
                let sett = key === 'vert' ? this.pivot_vert : this.pivot_hor;
                let grouped = _.groupBy(input, key+'_l'+lvl);

                let len = 0;
                let resu = { __show: true };
                //Group Data
                _.each(grouped, (sub_group,idx) => {
                    resu[idx] = (lvl >= ChartFunctions.maxLVL() ? _.clone(empobj) : this.groupViewedData(sub_group, key, lvl + 1));
                    len += resu[idx]['__span_len'] || 1;
                });

                //Add computed: 'SubTotal'
                let tmp = {}; tmp[key+'_l'+(lvl+1)] = '__sub_total';
                resu['__sub_total'] = (lvl >= ChartFunctions.maxLVL() ? _.clone(empobj) : this.groupViewedData([tmp], key, lvl+1, true));
                resu['__sub_total']['__show'] = allshow || !!sett['l'+lvl+'_sub_total'];// || lvl==1 First subtotal always active
                len += resu['__sub_total']['__show'] ? 1 : 0;

                //For computed 'SubTotal' Col/Row span=1
                if (this.dataKeys(resu).length === 0 && resu['__sub_total']) {
                    len = 1;
                }

                resu['__span_len'] = len;
                return resu;
            },
            order_values(array, fieldname) {
                if (!this.request_params) {
                    return array;
                }
                let desc = fieldname && _.find(this.request_params.sort, {direction: 'desc', field: fieldname});
                return array.sort((a, b) => {
                    if (!isNaN(a) && !isNaN(b)) {
                        a = to_float(a);
                        b = to_float(b);
                    }
                    return desc
                        ? a > b ? -1 : a < b ? 1 : 0
                        : a < b ? -1 : a > b ? 1 : 0;
                });
            },
            maxCellLength(input_data) {
                let res = 0;
                _.each(input_data, (row) => {
                    res = String(row.y).length > res ? String(row.y).length : res;
                    let maxI = 0;
                    for (let i = 1; i <= ChartFunctions.maxLVL(); i++) {
                        maxI = this.pivot_hor['l'+i+'_field'] ? i : maxI;
                    }
                    res = String(row['hor_l'+maxI]).length > res ? String(row['hor_l'+maxI]).length : res;
                });
                this.max_cell_len = res;
            },
            filterData(input) {
                return _.filter(input, (el) => {
                    return to_float(el.y);
                });
            },
            setDataWidths() {
                _.each(this.sorted_values.hor_l1, (l1_h) => {
                    if (this.sorted_values.hor_l2.length) {
                        _.each(this.sorted_values.hor_l2, (l2_h) => {
                            if (this.sorted_values.hor_l3.length) {
                                _.each(this.sorted_values.hor_l3, (l3_h) => {
                                    this.setOneDataWi(l1_h, l2_h, l3_h);
                                });
                            } else {
                                this.setOneDataWi(l1_h, l2_h);
                            }
                        });
                    } else {
                        this.setOneDataWi(l1_h);
                    }
                });
            },
            setOneDataWi(l1_h, l2_h, l3_h) {
                let key = this.getDataWiKey(l1_h, l2_h, l3_h);
                if (!this.pivot_t.data_widths[key]) {
                    this.$set(this.pivot_t.data_widths, key, {width: this.colWidthNum});
                }
            },
        },
        mounted() {
            this.about_1 = this.table_data ? this.filterData(this.table_data) : null;
            this.about_2 = this.table_data_2 ? this.filterData(this.table_data_2) : null;
            this.about_3 = this.table_data_3 ? this.filterData(this.table_data_3) : null;
            this.about_4 = this.table_data_4 ? this.filterData(this.table_data_4) : null;
            this.about_5 = this.table_data_5 ? this.filterData(this.table_data_5) : null;
            if (this.table_data.length) {
                this.prepareDataKeys(this.table_data);
                this.maxCellLength(this.about_1);
                this.setDataWidths();
            } else {
                this.max_cell_len = 0;
            }

            if (this.pivot.rows && this.pivot.columns && (this.pivot.rows.__span_len > 500 || this.pivot.columns.__span_len > 50)) {
                this.cannot_draw_error = 'Reached Max Rows (500) or Max Columns (50) limit for table!';
            }

            let y_axis_col = _.find(this.table_meta._fields, {field: this.all_settings.pivot_table.about.field});
            if (y_axis_col) {
                this.currency = y_axis_col.f_type === 'Currency';
                this.dec = y_axis_col.f_size - parseInt(y_axis_col.f_size);
            }

            eventBus.$on('chart-export-button-click', this.chartExportButtonHandler);
        },
        beforeDestroy() {
            eventBus.$off('chart-export-button-click', this.chartExportButtonHandler);
        }
    }
</script>

<style lang="scss" scoped>
    @import "BiModule";

    @import "../../../../CustomTable/CustomTable";
    @import "../../../../CustomPopup/CustomEditPopUp";

    .custom-table-wrapper {
        .pivot-table {
            table-layout: fixed;
            background-color: inherit !important;
            border-top: 2px solid #d3e0e9;
            border-left: 2px solid #d3e0e9;

            .sorted_th {
                cursor: pointer;
            }

            td {
                padding: 0 2px;
                overflow: hidden;
            }
            .limited {
                display: block;
                overflow: hidden;
            }
            .data-cell {
                text-align: center;
            }
        }
    }

    .bold {
        font-weight: bold;
    }

    .grand-checkbox {
        display: flex;
        align-items: center;
        margin: 0 10px;

        .grand-checkbox__input {
            width: 20px;
            display: inline-block;
            margin: 0;
        }
        .grand-checkbox__label {
            padding-left: 5px;
        }
    }

    /*.popup-wrapper {
        position: fixed;
    }*/
</style>
<style>
    .bi_pivot_select {
        padding: 0px 5px;
        background-color: #DDD;
        border-radius: 7px;
    }
</style>