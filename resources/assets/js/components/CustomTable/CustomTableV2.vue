<template>
    <div v-if="canDraw"
         class="custom-table-wrapper full-frame"
         ref="scroll_wrapper"
         :class="{'flex flex--col': show_rows_sum}"
    >
        <recycle-scroller
            v-if="virtualScrollFns"
            class="virtualscroller-as-block virtualscroller-before-is-sticky full-height"
            :class="{
                'virtualscroller-center': primaryAlign === 'center',
                'virtualscroller-right': primaryAlign === 'end',
            }"
            :items="virtualScrollFns.virtualRowsHtml"
            :item-size="heightOfCells"
            list-class="scroller-list"
            key-field="_id"
            @scroll-start="hideEdition"
        >
            <template #before>
                <sticky-table-component
                    v-if="tableMeta"
                    :tb_id="tb_id"
                    :cell_component_name="$root.tdCellComponent(tableMeta.is_system)"
                    :settings-meta="$root.settingsMeta"
                    :table_id="tableMeta.id"
                    :global-meta="tableMeta"
                    :table-meta="tableMeta"
                    :adding-row="addingRow"
                    :header-all-rows="allRows"
                    :all-rows="[]"
                    :sort="sort"
                    :behavior="behavior"
                    :user="$root.user"
                    :cell-height="cellHeight"
                    :no_b_margin="true"
                    :no_virtual_scroll="true"
                    :with_edit="with_edit"
                    :forbidden-columns="forbiddenColumns"
                    :available-columns="availableColumns"
                    :widths="widths"
                    :selected-cell="selectedCell"
                    :list-view-actions="listViewActions"
                    :object-for-add="curObjectForAdd"
                    :is-link="isLink"
                    :external_align="external_align"
                    @show-add-ddl-option="showAddDDLOption"
                    @added-row="addRow"
                    @updated-row="updatedRow"
                    @delete-row="deleteRow"
                    @delete-selected-rows="emitDeleteSelected"
                    @row-index-clicked="rowIndexClicked"
                    @check-row="checkClicked"
                    @row-selected="rowSelected"
                    @sort-by-field="sortByField"
                    @sub-sort-by-field="subSortByField"
                    @toggle-favorite-row="toggleFavoriteRow"
                    @toggle-all-favorites="toggleAllFavorites"
                    @show-src-record="showSrcRecord"
                    @show-header-settings="showHeaderSettings"
                    @start-resize="() => { widthWatcher = true; }"
                    @col-resized="() => { widthWatcher = false; redrawTable(); }"
                    @changed-cols-order="() => { redrawTable(); }"
                ></sticky-table-component>
            </template>

            <template v-slot="{ item, index }">
                <div class="item-wrap" :style="receiveRowStyle(item)">
                    <div :style="receiveWidthsStyle(-2, item)" class="full-height flex flex--center icon_on_hover">
                        <span
                            v-if="canDragRow"
                            class="pointer"
                            draggable="true"
                            title="Click&Drag to change order"
                            @dragend="overRow = null"
                            @dragenter="overRow = item._tbRow.id"
                            @dragstart="startChangeRowOrder(item._tbRow)"
                            @drop="endChangeRowOrder(item._tbRow)"
                            @click.prevent="rowIndexClicked(index, item._tbRow)"
                            @dragover.prevent=""
                        >
                            <a>
                                <span>{{ ((page - 1) * (tableMeta.rows_per_page || rowsCount)) + index + 1 }}</span>
                                <i v-if="inArray(behavior, popupArray)" class="glyphicon glyphicon-resize-full target_icon"></i>
                            </a>
                        </span>

                        <a v-else-if="inArray(behavior, linkArray)" @click.prevent="rowIndexClicked(index, item._tbRow)">
                            <span>{{ ((page - 1) * (tableMeta.rows_per_page || rowsCount)) + index + 1 }}</span>
                            <i v-if="inArray(behavior, popupArray)" class="glyphicon glyphicon-resize-full target_icon"></i>
                        </a>

                        <span v-else>{{ ((page - 1) * (tableMeta.rows_per_page || rowsCount)) + index + 1 }}</span>
                    </div>

                    <div v-if="listViewActions"
                        :class="{'action-cell': true, 'sm-font': $root.themeTextFontSize <= 14}"
                        :style="receiveWidthsStyle(-1, item)"
                        class="full-height flex flex--center _no_png"
                    >
                        <template v-if="behavior === 'request_view'">
                            <a @click="deleteRow(item._tbRow, index)"><i class="glyphicon glyphicon-remove hover-red"></i></a>
                        </template>
                        <div v-if="inArray(behavior, ['list_view','favorite'])">
                            <template v-if="notForbidden('i_srv') && canSRV(tableMeta)">
                                <srv-block
                                    :table-meta="tableMeta"
                                    :table-row="item._tbRow"
                                    :with-delimiter="true"
                                    style="font-size: 16px; position: relative; top: 2px;"
                                ></srv-block>
                            </template>
                            <template v-if="notForbidden('i_favorite') && $root.user.id">
                                <a @click.prevent="toggleFavoriteRow(item._tbRow)">
                                    <i :class="[item._tbRow._is_favorite ? 'glyphicon-star': 'glyphicon-star-empty']"
                                       :style="{color: item._tbRow._is_favorite ? $root.themeButtonBgColor : '#FD0'}"
                                       class="glyphicon"
                                       title="Add to Favorite"></i>
                                </a>
                                |
                            </template>
                            <template v-if="notForbidden('i_remove') && canDelete">
                                <a v-if="canDeleteRow(item._tbRow)" :class="{'disabled': !with_edit}" @click="deleteRow(item._tbRow, index)">
                                    <i class="glyphicon glyphicon-remove hover-red" title="Delete"></i>
                                </a>
                                <i v-else="" class="glyphicon glyphicon-remove gl-gray" title="Not Allowed"></i>
                                |
                            </template>
                            <template v-if="notForbidden('i_check')">
                                <span class="indeterm_check__wrap" title="Check Row">
                                    <span class="indeterm_check" @click="ifNeedMassCheck(item._tbRow)">
                                        <i v-if="item._tbRow._checked_row" class="glyphicon glyphicon-ok group__icon"></i>
                                    </span>
                                </span>
                            </template>
                        </div>
                    </div>

                    <!-- VIEW CELLS -->
                    <div v-for="(fld, idx) in virtualScrollFns.showMetaFields"
                         class="cell"
                         :style="receiveCellStyle(item, fld, index)"
                         @contextmenu.prevent="showRowMenu(item._tbRow, index, fld)"
                         @mouseenter="(e) => mouseEnterCell(e, fld)"
                         @mouseleave="(e) => mouseLeaveCell(fld)"
                         @mousedown.prevent="(e) => showEdit(e, fld, item, index)"
                         @mouseup.prevent="(e) => endSquareSelect(e, fld, item, index)"
                    >
                        <div class="cell-inner">
                            <div class="value-wrapper" v-for="htmlValue in item[fld.field].value">

                                <template v-for="lnk in fld._links" v-if="canLink(item._tbRow, lnk, ['before','front'])">
                                    <link-object :table-meta="tableMeta"
                                                 :global-meta="tableMeta"
                                                 :table-header="fld"
                                                 :table-row="item._tbRow"
                                                 :cell-value="htmlValue"
                                                 :link="lnk"
                                                 :user="$root.user"
                                                 :class="[canLink(item._tbRow, lnk, ['front']) ? 'link-absolute link-left' : '']"
                                                 :style="{left: linkAbsPos(fld, item._tbRow, lnk, 'front')}"
                                                 @show-src-record="showSrcRecord"
                                                 @open-app-as-popup="openAppAsPopup"
                                    ></link-object>
                                </template>

                                <template v-if="htmlValue && isAvailLink(item._tbRow) && underlinedLink(fld) && showLink(underlinedLink(fld), underlinedLink(fld).link_type)">
                                    <link-object :table-meta="tableMeta"
                                                 :global-meta="tableMeta"
                                                 :table-header="fld"
                                                 :table-row="item._tbRow"
                                                 :link="underlinedLink(fld)"
                                                 :cell-value="htmlValue"
                                                 :user="$root.user"
                                                 :show-field="htmlValue"
                                                 @show-src-record="showSrcRecord"
                                                 @open-app-as-popup="openAppAsPopup"
                                    ></link-object>
                                </template>
                                <template v-else>
                                    <!-- All View Content Types -->
                                    <span v-if="item[fld.field].style.hidden_by_format" class="by_format_hidden"></span>

                                    <boolean-elem
                                        v-else-if="fld.f_type === 'Boolean' && hdrInputType(fld) !== 'Formula'"
                                        :table-header="fld"
                                        :edit-value="htmlValue"
                                        :can-cell-edit="canCellEdit(fld, item)"
                                        @update-checkbox="(val) => updateFromShow(val, fld, item._tbRow)"
                                    ></boolean-elem>

                                    <stars-elems
                                        v-else-if="fld.f_type === 'Rating' && hdrInputType(fld) !== 'Formula'"
                                        :can_edit="canCellEdit(fld, item)"
                                        :cur_val="parseFloat(htmlValue)"
                                        :style="{lineHeight: $root.themeTextLineHeigh+'px'}"
                                        :table_header="fld"
                                        @set-star="(val) => updateFromShow(val, fld, item._tbRow)"
                                    ></stars-elems>

                                    <progress-bar
                                        v-else-if="fld.f_type === 'Progress Bar' && hdrInputType(fld) !== 'Formula'"
                                        :can_edit="false"
                                        :pr_val="htmlValue"
                                        :style="{height: $root.themeTextLineHeigh+'px'}"
                                        :table_header="fld"
                                        @set-val="(val) => updateFromShow(val, fld, item._tbRow)"
                                    ></progress-bar>

                                    <vote-element
                                        v-else-if="fld.f_type === 'Vote' && hdrInputType(fld) !== 'Formula'"
                                        :can_edit="canCellEdit(fld, item)"
                                        :cell_val="htmlValue"
                                        :table_header="fld"
                                        :user_info_settings="tableMeta._owner_settings"
                                        @set-val="(val) => updateFromShow(val, fld, item._tbRow)"
                                    ></vote-element>

                                    <a v-else-if="fld.f_type === 'Connected Clouds'"
                                       title="Open clouds in popup."
                                       @click.stop="showCloudPopup(item._tbRow.fetch_cloud_id)"
                                    >{{ showConnCloud(fld, item._tbRow) }}</a>

                                    <a v-else-if="fld.f_type === 'Table'"
                                       :href="getTableLink(htmlValue, 'url')"
                                       :title="getTableLink(htmlValue, 'path')"
                                       target="_blank"
                                       @click.stop=""
                                    >{{ getTableLink(htmlValue, 'name') }}</a>

                                    <div v-else-if="fld.f_type === 'Table Field'">{{ getTableField(fld, item._tbRow, htmlValue) }}</div>

                                    <email-phone-element
                                        v-else-if="inArray(fld.f_type, ['Email', 'Phone Number'])"
                                        :can_edit="canCellEdit(fld, item)"
                                        :cell_val="htmlValue"
                                        :table_header="fld"
                                        :table_meta="tableMeta"
                                        :table_row="item._tbRow"
                                        @element-update="(val) => updateFromShow(val, fld, item._tbRow)"
                                        @show-src-record="showSrcRecord"
                                        @open-app-as-popup="openAppAsPopup"
                                    ></email-phone-element>

                                    <!-- Edit handled by showEdit() -->
                                    <tablda-colopicker
                                        v-else-if="fld.f_type === 'Color' && !fld.ddl_id && hdrInputType(fld) !== 'Formula'"
                                        :avail_null="true"
                                        :can_edit="canCellEdit(fld, item)"
                                        :init_color="item._tbRow[fld.field]"
                                        :no_prop_stop="true"
                                    ></tablda-colopicker>

                                    <show-attachments-block
                                        v-else-if="fld.f_type === 'Attachment'"
                                        :can-edit="canCellEdit(fld, item)"
                                        :ext-thumb="'md'"
                                        :image-fit="fld.image_fitting"
                                        :table-header="fld"
                                        :table-meta="tableMeta"
                                        :table-row="item._tbRow"
                                        :is-grid-view="true"
                                        :emit-over-images="true"
                                        @over-images="(images, idx) => showAttachmentImages(fld, item._tbRow, images, idx)"
                                        @update-signal="(val) => updateFromShow(val, fld, item._tbRow)"
                                    ></show-attachments-block>

                                    <template v-else-if="fld.f_type === 'User' && htmlValue">
                                        <a v-if="canShowUser(fld, item._tbRow)"
                                           :target="$root.user.is_admin ?  '_blank' : ''"
                                           :href="$root.user.is_admin ? userHref(fld, item._tbRow) : 'javascript:void(0)'"
                                           :class="{
                                               'is_select': $root.issel(fld.input_type),
                                               'm_sel__wrap': $root.isMSEL(fld.input_type),
                                               'pr5': !canCellEdit(fld, item),
                                           }"
                                           :style="{whiteSpace: 'nowrap'}"
                                        >
                                            <span v-html="htmlValue"></span>
                                            <span v-if="$root.issel(fld.input_type) && canCellEdit(fld, item) && $root.isMSEL(fld.input_type)"
                                                  class="m_sel__remove"
                                                  @click.prevent.stop=""
                                                  @mousedown.prevent.stop="$emit('unselect-val')"
                                                  @mouseup.prevent.stop=""
                                            >&times;</span>
                                        </a>
                                    </template>
                                    <template v-else-if="htmlValue">
                                        <img v-if="$root.issel(fld.input_type) && specObjImgs(fld, item._tbRow).length"
                                             :src="$root.fileUrl(specObjImgs(fld, item._tbRow)[0], 'sm')"
                                             class="item-image"
                                             @click="showFullSizeimages(fld, item._tbRow)"
                                             :height="lineHeight"/>
                                        <span :class="{
                                                'is_select': $root.issel(fld.input_type),
                                                'm_sel__wrap': $root.issel(fld.input_type),
                                                'pr5': !canCellEdit(fld, item)
                                            }"
                                            :style="selectBG(fld, item._tbRow)"
                                        >
                                            <span v-html="htmlValue"></span>
                                            <span v-if="$root.issel(fld.input_type) && canCellEdit(fld, item)"
                                                  class="m_sel__remove"
                                                  @click.prevent.stop=""
                                                  @mousedown.prevent.stop="$emit('unselect-val')"
                                                  @mouseup.prevent.stop=""
                                            >&times;</span>
                                        </span>
                                    </template>
                                    <span v-else-if="placeholderAvail(fld, item._tbRow)" style="color: #CCC">{{ fld.placeholder_content }}</span>
                                </template>

                                <template v-for="lnk in fld._links" v-if="canLink(item._tbRow, lnk, ['after','end'])">
                                    <link-object :table-meta="tableMeta"
                                                 :global-meta="tableMeta"
                                                 :table-header="fld"
                                                 :table-row="item._tbRow"
                                                 :cell-value="htmlValue"
                                                 :link="lnk"
                                                 :user="$root.user"
                                                 :class="[canLink(item._tbRow, lnk, ['end']) ? 'link-absolute link-right' : '']"
                                                 :style="{right: linkAbsPos(fld, item._tbRow, lnk, 'end')}"
                                                 @show-src-record="showSrcRecord"
                                                 @open-app-as-popup="openAppAsPopup"
                                    ></link-object>
                                </template>

                            </div>
                        </div>
                    </div>
                    <!-- ^^^ VIEW CELLS ^^^ -->
                </div>
            </template>
        </recycle-scroller>

        <!-- Edit Cell -->
        <div v-if="editComponent.rect && editComponent.header.f_type === 'Color'" :style="editTdStyle()">
            <tablda-colopicker
                :avail_null="true"
                :can_edit="true"
                :init_menu="true"
                :init_color="editComponent.row[editComponent.header.field]"
                @set-color="updateTdColor"
            ></tablda-colopicker>
        </div>
        <single-td-field
            v-else-if="editComponent.rect"
            :table-meta="tableMeta"
            :table-header="editComponent.header"
            :td-value="editComponent.row[editComponent.header.field]"
            :ext-row="editComponent.row"
            :with_edit="with_edit"
            :open_edit="true"
            :style="editTdStyle()"
            @edit-closed="hideEdition"
            @updated-td-val="updateTdField"
        ></single-td-field>

        <!-- Full-size img for attachments -->
        <full-size-img-block
            v-if="fullSizeImg.images && fullSizeImg.images.length"
            :file_arr="fullSizeImg.images"
            :file_idx="fullSizeImg.idx"
            :table_header="fullSizeImg.header"
            :table_meta="tableMeta"
            :table_row="fullSizeImg.row"
            @close-full-img="fullSizeImg.images = null"
        ></full-size-img-block>

        <!-- Open Popup with Application in iframe -->
        <custom-application-pop-up
            v-if="iframe_app_link"
            :app_path="iframe_app_link"
            :tb_app="iframe_tb_app"
            @close-app="closeAppReloadRows"
        ></custom-application-pop-up>

        <!-- Right Click Row Menu -->
        <div v-if="row_menu_show && row_menu.row && row_menu.idx > -1"
             ref="row_menu"
             :style="rowMenuStyle"
             class="float-rom-menu"
        >
            <a @click="rowIndexClicked(row_menu.idx, row_menu.row);row_menu_show = false;">Popup</a>
            <a v-if="row_menu.hdr" @click="copyCell(row_menu.row, row_menu.hdr);row_menu_show = false;">Copy Cell</a>
            <a :class="!canAdd || !with_edit ? 'disabled' : ''" @click="rowInsertPop(row_menu.row, -1);row_menu_show = false;">Insert Above</a>
            <a :class="!canAdd || !with_edit ? 'disabled' : ''" @click="rowInsertPop(row_menu.row, 0);row_menu_show = false;">Insert Below</a>
            <a :class="!canAdd || !with_edit ? 'disabled' : ''" @click="rowInsertPop(row_menu.row, 0, 'copy');row_menu_show = false;">Duplicate</a>
            <a :class="!row_menu.can_del ? 'disabled' : ''" @click="deleteRow(row_menu.row, row_menu.idx);row_menu_show = false;">Delete</a>
        </div>

        <!--Sum Total Rows-->
        <rows-sum-block
            v-if="show_rows_sum"
            :global-meta="tableMeta"
            :table-meta="tableMeta"
            :all-rows="allRows"
            :widths="widths"
            :list-view-actions="listViewActions"
            :cell-height="cellHeight"
            :behavior="behavior"
            :is-link="isLink"
            :forbidden-columns="forbiddenColumns"
            :available-columns="availableColumns"
            :external_align="external_align"
        ></rows-sum-block>

        <!--Pagination Elements-->
        <table-pagination
            v-if="page"
            :page="page"
            :table-meta="tableMeta"
            :rows-count="rowsCount"
            :vert-scroll="vertScroll"
            :hor-scroll="horScroll"
            @change-page="changePage"
        ></table-pagination>
    </div>
</template>

<script>
import {eventBus} from "../../app";

import {VirtualScrollFns} from "../../classes/VirtualScrollFns";
import {JsFomulaParser} from "../../classes/JsFomulaParser";
import {SpecialFuncs} from "../../classes/SpecialFuncs";

import CheckRowBackendMixin from '../_Mixins/CheckRowBackendMixin.vue';
import LinkEmptyObjectMixin from '../_Mixins/LinkEmptyObjectMixin.vue';
import SelectedCellMixin from '../_Mixins/SelectedCellMixin.vue';
import IsShowFieldMixin from '../_Mixins/IsShowFieldMixin.vue';
import CanViewEditMixin from '../_Mixins/CanViewEditMixin.vue';
import CellStyleMixin from '../_Mixins/CellStyleMixin.vue';
import CellMenuMixin from '../_Mixins/CellMenuMixin.vue';
import ShowLinkMixin from '../_Mixins/ShowLinkMixin.vue';
import SrvMixin from '../_Mixins/SrvMixin.vue';

import StickyTableComponent from "./StickyTableComponent.vue";
import SrvBlock from "../CommonBlocks/SrvBlock.vue";
import RowsSumBlock from "../CommonBlocks/RowsSumBlock.vue";
import TablePagination from "./Pagination/TablePagination.vue";
import LinkObject from "../CustomCell/InCell/LinkObject.vue";
import CustomApplicationPopUp from "../CustomPopup/CustomApplicationPopUp.vue";
import FullSizeImgBlock from "../CommonBlocks/FullSizeImgBlock.vue";
import BooleanElem from "../CustomCell/InCell/BooleanElem.vue";
import StarsElems from "../CustomCell/InCell/StarsElems.vue";
import ProgressBar from "../CustomCell/InCell/ProgressBar.vue";
import ShowAttachmentsBlock from "../CommonBlocks/ShowAttachmentsBlock.vue";
import VoteElement from "../CustomCell/InCell/VoteElement.vue";
import TabldaColopicker from "../CustomCell/InCell/TabldaColopicker.vue";
import EmailPhoneElement from "../CustomCell/InCell/EmailPhoneElement.vue";

export default {
        name: "CustomTableV2",
        mixins: [
            CheckRowBackendMixin,
            LinkEmptyObjectMixin,
            SelectedCellMixin,
            IsShowFieldMixin,
            CanViewEditMixin,
            CellStyleMixin,
            CellMenuMixin,
            ShowLinkMixin,
            SrvMixin,
        ],
        components: {
            EmailPhoneElement,
            TabldaColopicker,
            VoteElement,
            ShowAttachmentsBlock,
            ProgressBar,
            StarsElems,
            BooleanElem,
            FullSizeImgBlock,
            CustomApplicationPopUp,
            LinkObject,
            StickyTableComponent,
            TablePagination,
            RowsSumBlock,
            SrvBlock,
        },
        data: function () {
            return {
                virtualScrollFns: null,
                canDraw: true,

                widths: {},
                widthWatcher: false,
                vertScroll: false,
                horScroll: false,
                heightOfCells: 0,
                draggedRow: null,
                overRow: null,
                show_expand: false,
                editComponent: {
                    rect: null,
                    header: null,
                    row: null,
                },
                avail_behave_links: ['list_view','favorite','link_popup','bi_module','map_view','single-record-view',
                    'kanban_view','request_view','grouping_table'],
                iframe_tb_app: null,
                iframe_app_link: null,
                fullSizeImg: {
                    images: null,
                    idx: 0,
                    header: null,
                    row: null,
                },
                isLinkNoEditFields: [],
            }
        },
        props: {
            tb_id: String,
            tableMeta: {
                type: Object,
                required: true,
            },
            allRows: Array|null,
            page: {
                type: Number,
                default: 1
            },
            sort: {
                type: Array,
                default: function () { return []; }
            },
            rowsCount: {
                type: Number,
                required: true,
            },
            behavior: {
                type: String,
                required: true,
            },
            cellHeight: {
                type: Number,
                default: 1
            },
            addingRow: {
                type: Object,
                default: function () { return { active: false }; }
            },
            availableColumns: Array,
            forbiddenColumns: Array,
            show_rows_sum: Boolean,
            externalObjectForAdd: Object,
            visible: {
                type: Boolean,
                default: true,
            },
            with_edit: {
                type: Boolean,
                default: true
            },
            external_align: String,
            isLink: Object,//CanViewEditMixin.vue
            link_popup_conditions: Object|Array,
            link_popup_tablerow: Object|Array, // for LinkEmptyObjectMixin.vue
            foreignSpecial: Object,
        },
        computed: {
            primaryAlign() {
                return this.external_align
                    || (this.tableMeta ? this.tableMeta.primary_align : 'start');
            },
            canDragRow() {
                let avail = this.$root.checkAvailable(this.$root.user, 'drag_rows');
                let hasRight = in_array(this.behavior, ['list_view'])
                    && (
                        this.tableMeta._is_owner
                        ||
                        (this.tableMeta._current_right && this.tableMeta._current_right.can_drag_rows)
                    );

                return avail
                    && hasRight
                    && !this.sort.length;
            },
            listViewActions() {
                let res = this.inArray(this.behavior, ['list_view','favorite','request_view']);
                return Boolean(res);
            },
            curObjectForAdd() {
                return this.externalObjectForAdd || this.objectForAdd;
            },
        },
        watch: {
            visible: {
                handler(val) {
                    this.redrawTable();
                },
                immediate: true,
            },
            allRows: {
                handler(val) {
                    this.redrawTable();
                },
                deep: true,
            },
        },
        methods: {
            editTdStyle() {
                return this.editComponent && this.editComponent.rect
                    ? {
                        position: 'fixed',
                        width: this.editComponent.rect.width + 'px',
                        height: this.editComponent.rect.height + 'px',
                        left: this.editComponent.rect.x + 'px',
                        top: this.editComponent.rect.y + 'px',
                    }
                    : {
                        display: 'none',
                    };
            },
            receiveRowStyle(item) {
                let style = item._presentStyle || {};
                style.height = this.heightOfCells + 'px';
                return style;
            },
            receiveWidthsStyle(idx, item) {
                if (item && item._presentStyle && item._presentStyle.backgroundColor) {
                    let style = _.clone(this.virtualScrollFns.widthsStyle[idx]);
                    style.backgroundColor = item._presentStyle.backgroundColor;
                    return style;
                } else {
                    return this.virtualScrollFns.widthsStyle[idx];
                }
            },
            receiveCellStyle(item, fld, rowIdx) {
                let cellStyle = this.widthWatcher
                    ? {
                        ...item[fld.field].style,
                        ...{width: fld.width+'px'},
                    }
                    : _.clone(item[fld.field].style);

                if (fld.f_type !== 'Color') {
                    let isCopy = this.selectedCell.is_sel_copy(this.tableMeta, fld, rowIdx);
                    let isSel = this.selectedCell.is_selected(this.tableMeta, fld, rowIdx);
                    let selectedBorder = isCopy ? '2px dashed #8A8' : '2px solid #8A8';
                    cellStyle.backgroundColor = isSel ? '#CFC' : cellStyle.backgroundColor;
                    cellStyle.borderTop = isSel || isCopy ? selectedBorder : '2px solid rgba(0,0,0,0)';
                    cellStyle.borderBottom = isSel || isCopy ? selectedBorder : '2px solid rgba(0,0,0,0)';
                    cellStyle.borderLeft = isSel || isCopy ? selectedBorder : '2px solid rgba(0,0,0,0)';
                    cellStyle.borderRight = isSel || isCopy ? selectedBorder : cellStyle.borderRight;
                }

                return cellStyle;
            },
            notForbidden(i_col) {
                return !this.inArray(i_col, this.forbiddenColumns || []);
            },
            hideEdition() {
                this.editComponent.rect = null;
            },
            
            // View Cells Functions
            hdrInputType(tableHeader) {
                return tableHeader.input_type === 'Mirror' && tableHeader.mirror_rc_id
                    ? tableHeader.mirror_edit_component
                    : tableHeader.input_type;
            },
            placeholderAvail(tableHeader, tableRow) {
                return tableHeader.placeholder_content
                    && !tableHeader.placeholder_only_form
                    && (!tableRow.id || !tableHeader._links.length);//hide placeholder for saved record with links.
            },
            underlinedLink(tableHeader) {
                return _.find(tableHeader._links, {icon: 'Underlined'});
            },
            isAvailLink(tableRow) {
                return $.inArray(this.behavior, this.avail_behave_links) > -1
                    && (tableRow.id || this.$root.is_dcr_page);
            },
            specObjImgs(tableHeader, tableRow) {
                let imgs = [];
                if (this.$root.issel(tableHeader.input_type)) {
                    let realVal = tableRow[tableHeader.field];
                    imgs = SpecialFuncs.rcObj(tableRow, tableHeader.field, realVal).img_vals || [];
                }
                return _.map(imgs, (el) => { return {url:el} });
            },
            selectBG(tableHeader, tableRow) {
                let realVal = tableRow[tableHeader.field];
                let bg = this.$root.issel(tableHeader.input_type) ? SpecialFuncs.rcObj(tableRow, tableHeader.field, realVal).ref_bg_color : '';
                return bg
                    ? {
                        backgroundColor: bg,
                        color: SpecialFuncs.smartTextColorOnBg(bg),
                    }
                    : {};
            },
            linkAbsPos(tableHeader, tableRow, lnk, pos) {
                if (! this.canLink(tableRow, lnk, pos)) {
                    return null;
                }

                let offset = 0;
                let idx = 0;
                _.each(tableHeader._links, (ll, ii) => {
                    if (ll.id == lnk.id || idx < ii) {
                        idx = ii - 1;
                        return;
                    }
                    if (this.canLink(tableRow, ll, pos) && lnk.icon.length) {
                        offset += Number(lnk.icon.length) * Number(this.$root.themeTextFontSize) * 0.5 + 6;
                    }
                });
                if (offset) {
                    offset += 4;
                }
                return offset + 'px';
            },
            canLink(tableRow, lnk, needed_positions) {
                return this.isAvailLink(tableRow)
                    && lnk.icon !== 'Underlined'
                    && this.inArray(lnk.link_pos, needed_positions);
            },
            canShowUser(tableHeader, tableRow) {
                let realVal = tableRow[tableHeader.field];
                return !this.$root.isMSEL(tableHeader.input_type) //no mselect
                    || this.tableMeta.user_id == this.$root.user.id //owner
                    || SpecialFuncs.managerOfRow(this.tableMeta, tableRow) //manager
                    || String(realVal).charAt(0) === '_' //group
                    || realVal == this.$root.user.id; //same user as current
            },
            userHref(tableHeader, tableRow) {
                let usr = this.$root.smallUserStr(tableRow, tableHeader, tableRow[tableHeader.field], 'object');
                return usr && usr.id && !isNaN(usr.id)
                    ? '/profile/'+usr.id
                    : 'javascript:void(0)';
            },
            canCellEdit(tableHeader, item) {
                if (! tableHeader || ! item) {
                    return false;
                }
                let canEdit = this.tableMeta._is_owner || (tableHeader.__can_edit && item._tbRow.__can_edit);
                let linkNoEdit = this.isLink && this.isLinkNoEditFields.indexOf(tableHeader.id) > -1;
                let style = item[tableHeader.field].style || {};
                
                return this.with_edit
                    && canEdit
                    && !linkNoEdit
                    && !this.inArray(tableHeader.field, this.$root.systemFields) //cannot edit system fields
                    && !style.freezed_by_format //cannot edit cells freezed by CondFormat
                    && !style.hidden_by_format //cannot edit cells hidden by CondFormat
                    && (tableHeader.input_type !== 'Mirror' || tableHeader.mirror_editable); //cannot edit 'Mirror' cells
            },
            openAppAsPopup(tb_app, app_link) {
                this.iframe_app_link = app_link;
                this.iframe_tb_app = tb_app;
            },
            closeAppReloadRows(app_name) {
                this.iframe_app_link = null;
                this.iframe_tb_app = null;
                eventBus.$emit('reload-page', this.tableMeta.id);
                eventBus.$emit('cell-app-has-been-closed', this.tableMeta.id, app_name);
            },
            showFullSizeimages(tableHeader, tableRow) {
                window.event.stopPropagation();
                window.event.preventDefault();

                this.fullSizeImg.images = this.specObjImgs(tableHeader, tableRow);
                this.fullSizeImg.idx = 0;
                this.fullSizeImg.header = tableHeader;
                this.fullSizeImg.row = tableRow;
            },
            showAttachmentImages(tableHeader, tableRow, images, idx) {
                this.fullSizeImg.images = images;
                this.fullSizeImg.idx = idx;
                this.fullSizeImg.header = tableHeader;
                this.fullSizeImg.row = tableRow;
            },

            // Change order of the rows
            startChangeRowOrder(tableRow) {
                this.draggedRow = tableRow;
            },
            endChangeRowOrder(tableRow) {
                if (this.draggedRow) {
                    if (this.$root.user.id && !this.$root.user.view_hash) {
                        axios.put('/ajax/settings/change-row-order', {
                            table_id: this.tableMeta.id,
                            from_order: this.draggedRow.row_order,
                            to_order: tableRow.row_order,
                            from_id: this.draggedRow.id,
                            to_id: tableRow.id,
                        }).then(({data}) => {
                            this.$emit('reorder-rows');
                        }).catch(errors => {
                            Swal('Info', getErrors(errors));
                        }).finally(() => {
                            this.draggedRow = null;
                        });
                    }
                }
            },

            //Connected clouds field
            metaClouds() {
                let filtered = _.filter(this.$root.settingsMeta.user_clouds_data, (acc) => {
                    return acc.__is_connected;
                });
                return _.map(filtered, (acc) => {
                    return {val: acc.id, show: acc.name};
                });
            },
            showConnCloud(tableHeader, tableRow) {
                let cld = _.find(this.metaClouds(), {val: Number(tableRow[tableHeader.field])});
                return cld ? cld.show : tableRow[tableHeader.field];
            },
            showCloudPopup(id) {
                let idx = _.findIndex(this.$root.settingsMeta.user_clouds_data, {id: id});
                eventBus.$emit('open-resource-popup', 'connections', idx, 'cloud');
            },

            //Tables and Fields columns
            getTableLink(htmlValue, type) {
                if (!htmlValue) {
                    return '';
                }
                let tb = _.find(this.$root.settingsMeta.available_tables, {id: Number(htmlValue)});
                if (!tb) {
                    return '';
                }
                switch (type) {
                    case 'name':
                        return tb.name;
                    case 'path':
                        return (new URL(tb.__url)).pathname.replace('/data/', '');
                    case 'url':
                        return tb.__url;
                    default:
                        return '';
                }
            },
            getTableField(tableHeader, tableRow, htmlValue) {
                if (!htmlValue) {
                    return '';
                }
                let tb = this.getOtherTable(tableHeader, tableRow);
                let thisFld = _.find(tb._fields || [], {id: Number(htmlValue)}) || {};
                return thisFld.name || htmlValue;
            },
            getOtherTable(tableHeader, tableRow) {
                let fldWithTable = _.find(this.tableMeta._fields, {id: Number(tableHeader.f_format)}) || {};
                let tbId = Number(tableRow[fldWithTable.field]);
                return _.find(this.$root.settingsMeta.available_tables, {id: tbId}) || {};
            },

            // Handlers
            showEdit(e, hdr, item, rowIdx) {
                //Not left click
                if (window.event.button != 0 && (this.selectedCell || this.behavior === 'grouping_table')) {
                    this.selectedCell ? this.selectedCell.single_select(hdr, rowIdx) : null;
                    return;
                }
                //Select a few cells
                let cmdOrCtrl = window.event.metaKey || window.event.ctrlKey;
                if (cmdOrCtrl && this.selectedCell) {
                    this.selectedCell.square_select(hdr, rowIdx);
                    return;
                }
                //focus on cell (only on desktop, not for 'Color')
                if (
                    window.innerWidth >= 768
                    && this.selectedCell
                    && !this.selectedCell.disabled
                    && !this.selectedCell.is_selected(this.tableMeta, hdr, rowIdx)
                    && hdr.f_type !== 'Color'
                ) {
                    this.selectedCell.single_select(hdr, rowIdx);
                } else {
                    let notedit_type = this.inArray(hdr.f_type, ['Boolean', 'Rating', 'Progress Bar', 'Vote', 'Email', 'Phone Number'])
                        && this.hdrInputType(hdr) !== 'Formula';

                    if (this.canCellEdit(hdr, item) && ! notedit_type) { //edit cell
                        let html = e ? e.target : null;
                        while (html && html.className != 'cell') {
                            html = html.parentElement;
                        }

                        this.editComponent.rect = html ? html.getBoundingClientRect() : null;
                        this.editComponent.header = hdr;
                        this.editComponent.row = item ? item._tbRow : null;
                    }
                }
            },
            endSquareSelect(e, hdr, item, rowIndex) {
                if (this.selectedCell) {
                    let row = item ? item._tbRow : {};
                    if (this.tableMeta.edit_one_click && !row[hdr.field] && this.selectedCell.is_selected(this.tableMeta, hdr, rowIndex)) {
                        this.showEdit(e, hdr, item, rowIndex);
                    } else {
                        this.selectedCell.square_select(hdr, rowIndex);
                    }
                }
            },
            showRowMenu(tableRow, index, tableHeader) {
                this.row_menu_show = true;
                this.row_menu_left = window.event.clientX;
                this.row_menu_top = window.event.clientY;
                this.row_menu.hdr = tableHeader || null;
                this.row_menu.row = tableRow;
                this.row_menu.idx = index;
                this.row_menu.can_del = this.canDeleteRow && this.with_edit ? this.canDeleteRow(tableRow) : false;
            },
            mouseEnterCell(e, tableHeader) {
                this.show_expand = this.inArray(tableHeader.f_type, ['String', 'Text', 'Long Text', 'Auto String']);
                this.$root.showHoverTooltip(e, tableHeader);
            },
            mouseLeaveCell(tableHeader) {
                this.show_expand = false;
                this.$root.leaveHoverTooltip();
            },
            externalScroll(table_id, row_id) {
                if (this.tableMeta.id == table_id) {
                    let refs = this.$refs['cttr_' + table_id + '_' + row_id];
                    let rrow = _.first(refs);
                    if (!!window.chrome && rrow) {
                        rrow.scrollIntoView({block: 'center'});
                    }
                }
            },
            addInlineClickedHandler() {
                if (this.visible && this.inArray(this.behavior, ['list_view', 'request_view'])) {
                    this.addRow();
                }
            },
            calculateAllTable(tbid, headerId, rowFormula) {
                if (tbid === this.tableMeta.id) {
                    _.each(this.allRows, (row) => {
                        _.each(this.tableMeta._fields, (fld) => {
                            if (fld.id == headerId) {
                                row[fld.field+'_formula'] = rowFormula;
                            }
                        });
                        JsFomulaParser.checkRowAndCalculate(this.tableMeta, row);
                    });
                }
            },
            glbClickHandler(e) {
                this.unselectCellOutside(e);
                this.clickHandler(e);
            },
            glbKeyHandler(e) {
                if (e.keyCode == 27) {
                    this.overRow = null;
                }
                this.globalKeyHandler(e);
            },
            //backend autocomplete
            checkRowAutocomplete() {
                if (!this.tableMeta.is_system) {
                    this.$nextTick(() => {
                        let promise = this.checkRowOnBackend(
                            this.tableMeta.id,
                            this.curObjectForAdd,
                            this.getLinkParams(this.link_popup_conditions, this.link_popup_tablerow),
                            this.foreignSpecial
                        );
                        if (promise) {
                            promise.then((data) => {
                                this.$emit('backend-row-checked', this.curObjectForAdd, data); //STIM 3D APP
                            });
                        }
                    });
                }
            },

            // Emits
            newObject() {
                this.createObjectForAdd();
                this.checkRowAutocomplete();
            },
            showAddDDLOption(tableHeader, tableRow) {
                this.$emit('show-add-ddl-option', tableHeader, tableRow);
            },
            rowInsertPop(tableRow, dir, copy) {
                if (this.canAdd && this.with_edit) {
                    let copy_row = copy ? tableRow : null;
                    if (copy_row) {
                        if (this.$root.setCheckRequired(this.tableMeta, copy_row)) {
                            this.$emit('copy-row', copy_row);
                        }
                    } else {
                        this.newObject();
                        this.curObjectForAdd.row_order = to_float(tableRow.row_order + dir);
                        this.addRow(tableRow, 'Set Default Values (DVs) for the fields with “Required” input, or use “Add” button for adding a new record.');
                        this.curObjectForAdd.row_order = null;
                    }
                }
            },
            addRow(selected_row, spec_error_message) {
                if (this.$root.setCheckRequired(this.tableMeta, this.curObjectForAdd, spec_error_message)) {
                    let obj = _.cloneDeep(this.curObjectForAdd);
                    this.newObject();
                    this.$emit('added-row', obj, false, selected_row);
                }
            },
            updateRowValue(value, tableHeader, tableRow) {
                tableRow._old_val = tableRow[tableHeader.field];
                tableRow[tableHeader.field] = value;
                tableRow._changed_field = tableHeader.field;
                if (tableHeader.input_type === 'Formula') {
                    tableRow[tableHeader.field + '_formula'] = value;
                }
                if (tableHeader.input_type === 'Mirror') {
                    tableRow[tableHeader.field + '_mirror'] = value ? 'changed' : '';
                }
            },
            updateFromShow(value, header, row) {
                this.updateRowValue(value, header, row);
                this.updatedRow(row, header);
            },
            updateTdField(value) {
                this.updateRowValue(value, this.editComponent.header, this.editComponent.row);
                this.updatedRow(this.editComponent.row, this.editComponent.header);
            },
            updateTdColor(value) {
                this.updateTdField(value);
                this.hideEdition();
            },
            updatedRow(row, hdr) {
                if (this.$root.setCheckRequired(this.tableMeta, row)) {
                    this.$emit('updated-row', row, hdr);
                }
            },
            deleteRow(tableRow, index) {
                if (this.with_edit) {
                    this.$emit('delete-row', tableRow, index);
                }
            },
            toggleFavoriteRow(tableRow) {
                this.$set(tableRow, '_is_favorite', !tableRow._is_favorite);
                this.$emit('toggle-favorite-row', tableRow);
            },
            ifNeedMassCheck(tableRow) {
                tableRow._checked_row = !tableRow._checked_row;
                this.$root.actionForMassCheckRows(tableRow);
                this.$emit('row-selected');
            },
            rowIndexClicked(index, row) {
                this.selectedCell.clear();
                this.$emit('row-index-clicked', index, row);
            },
            showHeaderSettings(header) {
                this.$emit('show-header-settings', header);
            },
            changePage(page) {
                this.$emit('change-page', page);
            },
            checkClicked(type, status, arr) {
                this.$emit('check-row', type, status, arr);
            },
            rowSelected() {
                this.$emit('row-selected');
            },
            sortByField(tableHeader, $dir) {
                this.$emit('sort-by-field', tableHeader, $dir);
            },
            subSortByField(tableHeader, $dir) {
                this.$emit('sub-sort-by-field', tableHeader, $dir);
            },
            toggleAllFavorites(status) {
                this.$emit('toggle-all-favorites', status);
            },
            emitDeleteSelected() {
                this.$emit('delete-selected-rows');
            },
            showSrcRecord(lnk, header, tableRow) {
                this.$emit('show-src-record', lnk, header, tableRow);
            },

            // On-draw functions
            fillWidths() {
                let countStrLen = this.tableMeta.rows_per_page
                    ? String(this.page * this.tableMeta.rows_per_page).length - 1
                    : String(this.rowsCount).length - 1;

                let index_c = this.tableMeta.is_system == 1
                    ? 35
                    : 45 + (countStrLen * 6);

                let fz = Number(this.$root.themeTextFontSize) * (this.$root.themeTextFontFamily === 'monospace' ? 2 : 1.5);
                let fav_c = 2*fz + 25;
                if (this.canDelete) {
                    fav_c += fz;
                }
                if (this.canSRV(this.tableMeta)) {
                    fav_c += fz;
                }

                let act_c = 55 + (this.behavior === 'invite_module' ? 65 : 0);

                this.widths = {
                    index_col: index_c,
                    favorite_col: this.listViewActions ? fav_c : 0,
                    action_col: act_c,
                }
            },
            fillLinkNoEditFields() {
                this.isLinkNoEditFields = [];
                if (this.isLink && !Number(this.isLink.editability_rced_fields)) {
                    let rc = _.find(this.tableMeta._ref_conditions, {id: Number(this.isLink.table_ref_condition_id)});
                    rc = rc || _.find(this.$root.tableMeta._ref_conditions, {id: Number(this.isLink.table_ref_condition_id)});

                    _.each(rc ? rc._items : [], (item) => {
                        this.isLinkNoEditFields.push(item.table_field_id);
                        this.isLinkNoEditFields.push(item.compared_field_id);
                    });
                }
            },
            calcHeight() {
                this.heightOfCells = ((this.cellHeight + 0.5) * this.$root.themeTextLineHeigh) + 4;
            },
            checkScrolls() {
                if (this.$refs.scroll_wrapper && this.virtualScrollFns) {
                    let colWidth = _.sumBy(this.virtualScrollFns.showMetaFields, 'width')
                        + this.widths.index_col + this.widths.favorite_col;
                    let rowHeight = (this.allRows.length + (this.addingRow.active ? 2 : 1)) * this.heightOfCells;
                    this.vertScroll = rowHeight > this.$refs.scroll_wrapper.offsetHeight;
                    this.horScroll = colWidth > this.$refs.scroll_wrapper.offsetWidth;
                }
            },
            redrawTable() {
                if (!this.visible) {
                    return;
                }
                let t1 = Date.now();

                //this.canDraw = false;

                this.$nextTick(() => {
                    this.fillWidths();
                    this.fillLinkNoEditFields();
                    this.calcHeight();

                    this.virtualScrollFns = new VirtualScrollFns(
                        this.tableMeta,
                        this.allRows,
                        this.widths,
                        this.behavior,
                        this.availableColumns,
                        this.forbiddenColumns
                    );
                    //this.canDraw = true;

                    this.$nextTick(() => {
                        this.checkScrolls();
                        console.log('CustomTableV2 mounted', Date.now() - t1, this.virtualScrollFns);
                    });
                });
            },
            redrawIfSameDbname(dbName) {
                if (this.tableMeta.db_name == dbName) {
                    this.redrawTable();
                }
            },
        },
        mounted() {
            eventBus.$on('change-table-meta', this.redrawTable);
            eventBus.$on('show-hide-button-has-been-changed', this.redrawIfSameDbname);

            eventBus.$on('global-click', this.glbClickHandler);
            eventBus.$on('global-keydown', this.glbKeyHandler);
            eventBus.$on('add-inline-clicked', this.addInlineClickedHandler);
            eventBus.$on('table-formulas-recalculate', this.calculateAllTable);
        },
        beforeDestroy() {
            eventBus.$off('change-table-meta', this.redrawTable);
            eventBus.$off('show-hide-button-has-been-changed', this.redrawIfSameDbname);

            eventBus.$off('global-click', this.glbClickHandler);
            eventBus.$off('global-keydown', this.glbKeyHandler);
            eventBus.$off('add-inline-clicked', this.addInlineClickedHandler);
            eventBus.$off('table-formulas-recalculate', this.calculateAllTable);
        }
    }
</script>

<style lang="scss" scoped>
    @import "./CustomTable.scss";

    .item-wrap {
        width: fit-content;
        display: flex;
        flex-direction: row;
        flex-wrap: nowrap;
        white-space: nowrap;
        box-sizing: border-box;
        border-bottom: 1px solid #d3e0e9;

        .cell {
            flex-shrink: 0;
            flex-grow: 0;
            overflow: hidden;
            white-space: normal;
            display: flex;
            align-items: center;
            position: relative;
            padding: 0 2px;

            .cell-inner {
                margin: auto 0;

                span {
                    text-decoration: inherit;
                }
                i {
                    cursor: pointer;
                }
                p, ul, ol, h1, h2, h3 {
                    margin-bottom: 16px;
                }
                .link-absolute {
                    position: absolute;
                    //z-index: 10; - creates "web link popup overflow issue"
                    background: white;
                    top: 0;
                }
                .link-left {
                    left: 0;
                }
                .link-right {
                    right: 0;
                }
            }
        }
    }

    a {
        cursor: pointer;
    }

    .indeterm_check__wrap {
        top: 2px;
        position: relative;

        .indeterm_check {
            .group__icon {
                font-size: 0.9em;
                top: 0;
            }
        }
    }

    input[type="radio"], input[type="checkbox"] {
        margin: 0;
    }

    .float-rom-menu {
        position: fixed;
        z-index: 5000;
        text-align: left;
        background-color: #333;

        a {
            display: block;
            color: #FFF;
            padding: 1px 5px;
            cursor: pointer;
            text-decoration: none;

            &:hover {
                background-color: #555;
            }
        }
    }

    .icon_on_hover {
        border-left: 1px solid #d3e0e9;

        .target_icon {
            display: none;
        }

        &:hover {
            .target_icon {
                display: inline;
            }
        }
    }
</style>