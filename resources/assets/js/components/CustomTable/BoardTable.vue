<template>
    <div class="full-frame flex flex--wrap"
         ref="bordTb"
         :style="{height: activeHeightWatcher ? 'min-content' :''}"
         :draw_handler="fullTableHeight()"
    >
        <div v-for="(tableRow, r_idx) in allRows"
             v-if="show_delay"
             class="board-wrapper flex"
             :style="boStyle(tableRow)"
             @click="selectedBoard(r_idx)"
        >
            <div class="board-border" :style="{border: withBorder ? '1px solid rgb(119, 119, 119)' : null}">

                <div v-if="withHeader && getPopUpHeader(tableRow)" class="border_header">{{ getPopUpHeader(tableRow) }}</div>
                <div v-else-if="withHeader" class="border_topbar"></div>

                <div class="flex" :class="{'flex--col': inArray(getBoardSetts('board_display_position'), ['top','bottom'])}">
                    <div v-if="inArray(getBoardSetts('board_display_position'), ['left','top']) && getBoardSetts('board_image_width')"
                         class="attach-table mr"
                         :style="{
                            width: inArray(getBoardSetts('board_display_position'), ['top','bottom',null]) ? '100%' : getBoardImageWidth(),
                            aspectRatio: boardVertImage('top') && !boardImageHeight('top') ? '3/1' : null,
                            margin: boardVertImage('top') ? '0 0 10px 0' : null,
                            height: boardVertImage('top') ? boardImageHeight('top') : null,
                         }"
                    >
                        <i v-if="canDelete && canDeleteRow(tableRow)" :class="{'fa-dis': !with_edit}" class="fas fa-trash fa-l" @click="deletedRow(tableRow, r_idx)"></i>
                        <carousel-block :images="getBoardImages(tableRow)" :can_click="true" @img-clicked="imageClick"></carousel-block>
                    </div>

                    <div class="board-table"
                         :style="{
                            width: inArray(getBoardSetts('board_display_position'), ['top','bottom',null]) ? '100%' : getBoardImageWidth(true),
                            paddingRight: inArray(getBoardSetts('board_display_position'), ['top','bottom']) ? 0 : '10px',
                         }"
                         @click="ctlgAmountField ? null : showPopupHandler(tableRow)"
                    >
                        <vertical-table
                            :td="$root.tdCellComponent(tableMeta.is_system)"
                            :global-meta="tableMeta"
                            :table-meta="tableMeta"
                            :settings-meta="$root.settingsMeta"
                            :table-row="tableRow"
                            :cell-height="cellHeight"
                            :max-cell-rows="maxCellRows"
                            :with_edit="with_edit"
                            :user="user"
                            :behavior="behavior"
                            :disabled_sel="true"
                            :available-columns="boardFields"
                            :forbidden-columns="forbiddenColumns"
                            :foreign-title-width="boardTitleWidth"
                            :ctlg-amount-field="ctlgAmountField"
                            @updated-cell="updatedRow"
                            @show-src-record="showSrcRecord"
                            @total-tb-height-changed="(h) => { totalTbHeightChanged(r_idx+1, h) }"
                            @updated-ctlg="$emit('updated-ctlg')"
                        ></vertical-table>
                    </div>

                    <div v-if="inArray(getBoardSetts('board_display_position'), ['right','bottom']) && getBoardSetts('board_image_width')"
                         class="attach-table"
                         :style="{
                            width: inArray(getBoardSetts('board_display_position'), ['top','bottom',null]) ? '100%' : getBoardImageWidth(),
                            aspectRatio: boardVertImage('bottom') && !boardImageHeight('bottom') ? '3/1' : null,
                            margin: boardVertImage('bottom') ? '10px 0 0 0' : null,
                            height: boardVertImage('bottom') ? boardImageHeight('bottom') : null,
                         }"
                    >
                        <i v-if="canDelete && canDeleteRow(tableRow)" :class="{'fa-dis': !with_edit}" class="fas fa-trash fa-r" @click="deletedRow(tableRow, r_idx)"></i>
                        <carousel-block :images="getBoardImages(tableRow)" :can_click="true" @img-clicked="imageClick"></carousel-block>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="add_mode" class="board-wrapper flex" :style="boStyle(objectForAdd)">
            <div
                class="board-border flex"
                :class="{'flex--col': inArray(getBoardSetts('board_display_position'), ['top','bottom'])}"
                :style="{border: withBorder ? '1px solid rgb(119, 119, 119)' : null}"
            >
                <div v-if="inArray(getBoardSetts('board_display_position'), ['left','top']) && getBoardSetts('board_image_width')"
                     class="attach-table mr"
                     :style="{
                        width: inArray(getBoardSetts('board_display_position'), ['top','bottom',null]) ? '100%' : getBoardImageWidth(),
                            aspectRatio: boardVertImage('top') && !boardImageHeight('top') ? '3/1' : null,
                            margin: boardVertImage('top') ? '0 0 10px 0' : null,
                            height: boardVertImage('top') ? boardImageHeight('top') : null,
                     }"
                >
                    <carousel-block :images="getBoardImages(objectForAdd)" :can_click="true" @img-clicked="imageClick"></carousel-block>
                </div>

                <div class="board-table"
                     :style="{
                        width: inArray(getBoardSetts('board_display_position'), ['top','bottom',null]) ? '100%' : getBoardImageWidth(true),
                        paddingRight: inArray(getBoardSetts('board_display_position'), ['top','bottom']) ? 0 : '10px',
                     }"
                     @click="showPopupHandler(objectForAdd)"
                >
                    <vertical-table
                        :td="$root.tdCellComponent(tableMeta.is_system)"
                        :global-meta="tableMeta"
                        :table-meta="tableMeta"
                        :settings-meta="$root.settingsMeta"
                        :table-row="objectForAdd"
                        :cell-height="cellHeight"
                        :max-cell-rows="maxCellRows"
                        :user="user"
                        :with_edit="with_edit"
                        :behavior="behavior"
                        :disabled_sel="true"
                        :available-columns="boardFields"
                        :forbidden-columns="forbiddenColumns"
                        :is-link="isLink"
                        @updated-cell="checkRowAutocomplete"
                        @show-src-record="showSrcRecord"
                        @total-tb-height-changed="(h) => { totalTbHeightChanged(0, h) }"
                    ></vertical-table>
                </div>

                <div v-if="inArray(getBoardSetts('board_display_position'), ['right','bottom']) && getBoardSetts('board_image_width')"
                     class="attach-table"
                     :style="{
                        width: inArray(getBoardSetts('board_display_position'), ['top','bottom',null]) ? '100%' : getBoardImageWidth(),
                        aspectRatio: boardVertImage('bottom') && !boardImageHeight('bottom') ? '3/1' : null,
                        margin: boardVertImage('bottom') ? '10px 0 0 0' : null,
                        height: boardVertImage('bottom') ? boardImageHeight('bottom') : null,
                     }"
                >
                    <carousel-block :images="getBoardImages(objectForAdd)" :can_click="true" @img-clicked="imageClick"></carousel-block>
                </div>
            </div>
        </div>

        <div v-if="availableAdd">
            <button v-show="! add_mode" :disabled="!with_edit" class="btn btn-success btn-sm" @click="enterAddMode()">Add New Record</button>

            <button class="btn btn-success btn-sm pull-right" v-if="add_mode" :disabled="!with_edit" @click="insertRow(objectForAdd)">Save</button>
            <button class="btn btn-default btn-sm pull-right mr5" v-if="add_mode" :disabled="!with_edit" @click="cancelRow()">Cancel</button>
        </div>

        <custom-edit-pop-up
            v-if="tableMeta && editPopUpRow"
            :idx="1"
            :global-meta="tableMeta"
            :table-meta="tableMeta"
            :table-row="editPopUpRow"
            :settings-meta="$root.settingsMeta"
            :role="'update'"
            :input_component_name="$root.tdCellComponent(tableMeta.is_system)"
            :behavior="behavior"
            :user="user"
            :cell-height="cellHeight"
            :max-cell-rows="maxCellRows"
            :forbidden-columns="forbiddenColumns"
            :available-columns="availableColumns"
            :is-link="isLink"
            :with_edit="with_edit"
            @popup-insert="addedRow"
            @popup-copy="addedRow"
            @popup-update="updatedRow"
            @popup-delete="deletedRow"
            @popup-close="closePopUp"
            @show-src-record="showSrcRecord"
            @another-row="anotherRowPopup"
        ></custom-edit-pop-up>

        <full-size-img-block
            v-if="overImages && overImages.length"
            :file_arr="overImages"
            :file_idx="overImageIdx"
            @close-full-img="overImages = null"
        ></full-size-img-block>

        <label v-if="labelsWidthText" ref="labels_width_text" :style="tableMeta.is_system ? textSysStyle : textStyle">
            {{ labelsWidthText }}
        </label>
    </div>
</template>

<script>
import {SelectedCells} from '../../classes/SelectedCells';

import IsShowFieldMixin from '../_Mixins/IsShowFieldMixin.vue';
import CellStyleMixin from './../_Mixins/CellStyleMixin.vue';
import CheckRowBackendMixin from "../_Mixins/CheckRowBackendMixin.vue";
import LinkEmptyObjectMixin from "../_Mixins/LinkEmptyObjectMixin";
import CanViewEditMixin from "../_Mixins/CanViewEditMixin.vue";

import CustomCellTableData from '../CustomCell/CustomCellTableData.vue';
import CustomCellSystemTableData from '../CustomCell/CustomCellSystemTableData.vue';
import CustomCellCorrespTableData from '../CustomCell/CustomCellCorrespTableData.vue';
import CustomCellSettingsDisplay from '../CustomCell/CustomCellSettingsDisplay.vue';
import CustomCellDisplayLinks from '../CustomCell/CustomCellDisplayLinks.vue';
import CustomCellSettingsDdl from '../CustomCell/CustomCellSettingsDdl.vue';
import CustomCellSettingsPermission from '../CustomCell/CustomCellSettingsPermission.vue';
import CustomCellColRowGroup from '../CustomCell/CustomCellColRowGroup.vue';
import CustomCellKanbanSett from '../CustomCell/CustomCellKanbanSett.vue';
import CustomCellSimplemapSett from '../CustomCell/CustomCellSimplemapSett.vue';
import CustomCellRefConds from '../CustomCell/CustomCellRefConds.vue';
import CustomCellCondFormat from '../CustomCell/CustomCellCondFormat.vue';
import CustomCellPlans from '../CustomCell/CustomCellPlans.vue';
import CustomCellConnection from '../CustomCell/CustomCellConnection.vue';
import CustomCellPages from '../CustomCell/CustomCellPages.vue';
import CustomCellUserGroups from '../CustomCell/CustomCellUserGroups.vue';
import CustomCellInvitations from '../CustomCell/CustomCellInvitations.vue';
import CustomCellTwilio from '../CustomCell/CustomCellTwilio.vue';

import CustomEditPopUp from "../CustomPopup/CustomEditPopUp";
import CarouselBlock from "../CommonBlocks/CarouselBlock";
import FullSizeImgBlock from "../CommonBlocks/FullSizeImgBlock";

export default {
    name: "BoardTable",
    mixins: [
        IsShowFieldMixin,
        CellStyleMixin,
        CheckRowBackendMixin,
        LinkEmptyObjectMixin,
        CanViewEditMixin,
    ],
    components: {
        FullSizeImgBlock,
        CarouselBlock,
        CustomEditPopUp,

        CustomCellTableData,
        CustomCellSystemTableData,
        CustomCellCorrespTableData,
        CustomCellSettingsDisplay,
        CustomCellDisplayLinks,
        CustomCellSettingsDdl,
        CustomCellSettingsPermission,
        CustomCellColRowGroup,
        CustomCellKanbanSett,
        CustomCellSimplemapSett,
        CustomCellRefConds,
        CustomCellCondFormat,
        CustomCellPlans,
        CustomCellConnection,
        CustomCellPages,
        CustomCellUserGroups,
        CustomCellInvitations,
        CustomCellTwilio,
    },
    data: function () {
        return {
            boardTitleWidth: 0,
            labelsWidthText: '',
            boardHeights: {},
            selectedCell: new SelectedCells(),
            editPopUpRow: null,
            overImages: null,
            overImageIdx: null,
            show_delay: false,
            isVertTable: true,//for IsShowFieldMixin
            add_mode: false,
            totalTbHeight: 0,
        };
    },
    props: {
        tableMeta: Object,
        allRows: Object|null,
        cellHeight: Number,
        maxCellRows: {
            type: Number,
            default: 0
        },
        user: Object,
        forbiddenColumns: {
            type: Array,
            default: function () {
                return [];
            }
        },
        with_edit: {
            type: Boolean,
            default: true
        },
        availableColumns: Array,
        behavior: String,
        isLink: Object,
        dcrLinked: Object,
        withBorder: Boolean,
        withHeader: Boolean,
        activeHeightWatcher: Boolean,
        link_popup_conditions: Object|Array,
        link_popup_tablerow: Object|Array, // for LinkEmptyObjectMixin.vue
        withAdding: Object, //{active: true/false; immediate: true/false}
        columnsNum: {
            type: Number,
            default: 1,
        },
        ctlgAmountField: String,
        isVisible: Boolean,
    },
    watch: {
        isVisible(val) {
            if (val) {
                this.calcLabelsWidth();
                this.fullTableHeight();
            }
        },
    },
    computed: {
        cardWidth() {
            return (100 / this.columnsNum) + '%';
        },
        boardSetts() {
            return this.dcrLinked || this.isLink || this.tableMeta;
        },
        availableAdd() {
            return this.canAdd && (!this.withAdding || this.withAdding.active);
        },
        hasUnits() {
            let has = false;
            if (this.tableMeta) {
                _.each(this.tableMeta._fields, function (el) {
                    if (el.unit) {
                        has = true;
                    }
                });
            }
            return has;
        },
        boardFields() {
            let avail = _.filter(this.tableMeta._fields, (fld) => {
                return this.isShowBoard(fld) && fld.f_type !== 'Attachment';
            });
            return _.map(avail, (map) => {
                return map.field;
            });
        },
    },
    methods: {
        boStyle(tableRow) {
            let wi = 'auto';
            if (Number(this.getBoardSetts('board_view_height'))) {
                wi = Number(this.getBoardSetts('board_view_height')) + 'px';
            }
            let bClr = null;
            if (this.ctlgAmountField && tableRow.__ctlg_amount > 0) {
                bClr = '#7A7';
            }
            return {
                height: wi,
                width: 'calc('+this.cardWidth+' - 5px)',
                borderColor: bClr,
            };
        },
        fullTableHeight() {
            if (this.activeHeightWatcher) {
                window.setTimeout(() => {
                    if (this.$refs.bordTb) {
                        let curHeight = Number(this.$refs.bordTb.clientHeight);
                        if (Math.abs(this.totalTbHeight - curHeight) > 10) {
                            this.totalTbHeight = curHeight;
                            this.$emit('total-tb-height-changed', this.totalTbHeight);
                        }
                    }
                }, 100);
            }
        },
        getBoardImageWidth(rest) {
            let width = this.getBoardSetts('board_image_width');
            if (rest) {
                return width < 1
                    ? ((100 - (width*100)) + '%')
                    : '100%';//('calc(100% - ' + width + 'px)');
            } else {
                return width < 1
                    ? ((width*100) + '%')
                    : (width + 'px');
            }
        },
        getBoardSetts(key) {
            let pref = this.ctlgAmountField ? 'ctlg_' : '';
            return this.boardSetts[pref+key];
        },
        boardVertImage(pos) {
            return this.inArray(this.getBoardSetts('board_display_position'), [pos]);
        },
        boardImageHeight(pos) {
            if (Number(this.getBoardSetts('board_view_height')) && Number(this.getBoardSetts('board_image_height'))) {
                let he = Number(this.getBoardSetts('board_view_height')) * Number(this.getBoardSetts('board_image_height')) / 100;
                return he + 'px';
            }
            return null;
        },
        totalTbHeightChanged(id, height) {
            if (height) {
                this.boardHeights[id] = height;
            }
            let totHeight = 0;
            _.each(this.boardHeights, (he, key) => {
                if (key == 0) {
                    totHeight += this.add_mode ? (he + 30) : 0;
                } else {
                    totHeight += he + 30;
                }
            });
            this.$emit('total-tb-height-changed', totHeight);
        },
        getPopUpHeader(tableRow) {
            return this.$root.getPopUpHeader(this.tableMeta, tableRow);
        },
        checkRowAutocomplete() {
            let promise = this.checkRowOnBackend(
                this.tableMeta.id,
                this.objectForAdd,
                this.getLinkParams(this.link_popup_conditions, this.link_popup_tablerow)
            );
            if (promise) {
                promise.then((data) => {
                    Number(this.objectForAdd.id) ? this.updateRow(this.objectForAdd) : null;
                });
            }
        },
        //sys methods
        inArray(item, array) {
            return $.inArray(item, array) > -1;
        },
        showSrcRecord(lnk, header, tableRow) {
            this.$emit('show-src-record', lnk, header, tableRow);
        },
        showAddRefCond(refId) {
            this.$emit('show-add-ref-cond', refId);
        },
        addedRow(tableRow) {
            this.$emit('added-row', tableRow);
        },
        cancelRow() {
            this.add_mode = false;
            this.totalTbHeightChanged();
        },
        enterAddMode() {
            this.add_mode = true;
            this.createObjectForAdd();
            this.checkRowAutocomplete();
        },
        insertRow(tableRow) {
            if (this.$root.setCheckRequired(this.tableMeta, tableRow)) {
                this.addedRow(tableRow);
            }
            this.add_mode = false;
        },
        updatedRow(tableRow, hdr) {
            this.$emit('updated-row', tableRow, hdr);
        },
        deletedRow(tableRow, index) {
            if (this.with_edit) {
                this.$emit('delete-row', tableRow, index);
            }
        },
        showDefValPopup(tableRow, moreParam) {
            this.$emit('show-def-val-popup', tableRow, moreParam);
        },
        showAddDDLOption(tableHeader, tableRow) {
            this.$emit('show-add-ddl-option', tableHeader, tableRow);
        },
        selectedBoard(r_idx) {
            this.$emit('selected-row', r_idx);
        },

        //showings
        isShowBoard(tableHeader) {
            return this.isShowField(tableHeader) && tableHeader.is_show_on_board;
        },

        //images
        getBoardImages(tableRow) {
            let images = [];
            _.each(this.tableMeta._fields, (fld) => {
                if (fld.id == this.getBoardSetts('board_image_fld_id') && fld.f_type === 'Attachment') {
                    images = images.concat(tableRow['_images_for_'+fld.field] || [])
                }
            });
            return images;
        },
        imageClick(images, idx) {
            this.overImages = images;
            this.overImageIdx = idx;
        },

        //popup functions
        showPopupHandler(tableRow) {
            this.editPopUpRow = tableRow;
        },
        showPopupIndex(idx) {
            this.editPopUpRow = this.allRows[idx];
        },
        closePopUp() {
            this.editPopUpRow = null;
        },
        anotherRowPopup(is_next) {
            let row_id = (this.editPopUpRow ? this.editPopUpRow.id : null);
            this.$root.anotherPopup(this.allRows, row_id, is_next, this.showPopupIndex);
        },

        //Auto ajust labels width
        calcLabelsWidth() {
            this.boardTitleWidth = this.getBoardSetts('board_title_width');

            if (! this.boardTitleWidth) {
                this.labelsWidthText = 'Amount:';
                _.each(this.tableMeta._fields, (fld) => {
                    if (this.inArray(fld.field, this.boardFields) && fld.name.length > this.labelsWidthText.length) {
                        this.labelsWidthText = fld.name;
                    }
                });

                //If inside a Popup component (in initial animation frame it scaled: 0.1)
                let multiplier = 1;
                if (
                    this.$parent && this.$parent.$options && String(this.$parent.$options.name).match('Popup')
                    || this.$parent && this.$parent.$parent && this.$parent.$parent.$options && String(this.$parent.$parent.$options.name).match('Popup')
                ) {
                    multiplier = 10;
                }

                this.$nextTick(() => {
                    let rect = this.$refs.labels_width_text.getBoundingClientRect();
                    this.boardTitleWidth = (rect.width * multiplier) + 5;
                    this.labelsWidthText = '';
                });
            }
        },
    },
    mounted() {
        if (this.withAdding && this.withAdding.immediate && this.availableAdd && this.with_edit) {
            this.enterAddMode();
        }

        setTimeout(() => {
            this.show_delay = true;
        }, 1);

        this.calcLabelsWidth();
    },
    beforeDestroy() {
    }
}
</script>

<style lang="scss" scoped>
.board-wrapper {
    margin: 15px 5px 0 0;
    border: 2px solid transparent;
    border-radius: 10px;
    transition: all 0.7s;
    background-color: #FFF;

    &:hover {
        background-color: #f0f0f0;
        border-color: #777;

        .border_topbar {
            border-color: #444;
        }
    }

    .border_header {
        color: #FFF;
        background-color: #444;
        padding: 5px 10px;
        border-radius: 5px;
        font-size: 1.2em;
        margin-bottom: 7px;
    }

    .border_topbar {
        background-color: #444;
        padding-top: 2px;
        border-bottom: 3px solid #FFF;
        border-radius: 15px;
        margin-bottom: 3px;
        transition: all 0.7s;
    }

    .board-border {
        padding: 5px;
        overflow: auto;
        width: 100%;
        border-radius: 5px;
    }

    .board-table {
        padding-right: 10px;
        width: 65%;
        overflow-x: hidden;
        overflow-y: auto;

        table {
            background-color: inherit !important;
            width: 100%;

            td {
                padding: 0 3px;
                vertical-align: middle;
                position: relative;
                border: none;

                label {
                    margin: 0;
                }
                .label-padding {
                    padding-left: 5px;
                }
            }
        }
    }

    .attach-table {
        width: 35%;
        background-color: #EEE;
        position: relative;
        overflow: auto;

        .attach-img {
            display: block;
            margin: 0 auto;
            height: 100%;
        }

        .fa-trash {
            position: absolute;
            top: 5px;
            cursor: pointer;
            color: rgba(255, 0, 0, 0.3);
            font-size: 16px;
            z-index: 100;

            &:hover {
                color: #F00;
            }
        }
        .fa-l {
            left: 5px;
        }
        .fa-r {
            right: 5px;
        }
        .fa-dis {
            color: #777 !important;
            cursor: default !important;
        }
    }
}
.mr {
    margin-right: 10px;
}
</style>