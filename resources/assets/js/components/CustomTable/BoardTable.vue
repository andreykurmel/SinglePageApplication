<template>
    <div class="full-frame">
        <div v-for="(tableRow, r_idx) in allRows"
             v-if="show_delay"
             class="board-wrapper flex"
             :style="{height: boardSettings.board_view_height ? (boardSettings.board_view_height+10)+'px' : 'auto'}"
             @click="selectedBoard(r_idx)"
        >
            <div class="board-border">
                <div class="board-table" :style="{width: (100-boardSettings.board_image_width)+'%'}">
                    <vertical-table
                            :td="$root.tdCellComponent(tableMeta.is_system)"
                            :global-meta="globalMeta"
                            :table-meta="tableMeta"
                            :settings-meta="$root.settingsMeta"
                            :table-row="tableRow"
                            :cell-height="cellHeight"
                            :max-cell-rows="maxCellRows"
                            :user="user"
                            :behavior="behavior"
                            :with_edit="false"
                            :can-see-history="false"
                            :disabled_sel="true"
                            :is_small_spacing="'yes'"
                            :available-columns="boardFields"
                            :forbidden-columns="forbiddenColumns"
                    ></vertical-table>
                    <a @click="showPopupHandler(tableRow)">More...</a>
                </div>
                <div v-if="boardSettings.board_image_width" class="attach-table" :style="{width: (boardSettings.board_image_width)+'%'}">
                    <carousel-block :images="getBoardImages(tableRow)" @img-clicked="imageClick"></carousel-block>
                </div>
            </div>
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
                @popup-update="updatedCell"
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
    </div>
</template>

<script>
    import {SelectedCells} from './../../classes/SelectedCells';

    import ReactiveProviderMixin from '../_CommonMixins/ReactiveProviderMixin.vue';
    import IsShowFieldMixin from '../_Mixins/IsShowFieldMixin.vue';
    import CellStyleMixin from './../_Mixins/CellStyleMixin.vue';

    import CustomHeadCellTableData from '../CustomCell/CustomHeadCellTableData.vue';
    import CustomCellTableData from '../CustomCell/CustomCellTableData.vue';
    import CustomCellSystemTableData from '../CustomCell/CustomCellSystemTableData.vue';
    import CustomCellCorrespTableData from '../CustomCell/CustomCellCorrespTableData.vue';
    import CustomCellSettingsDisplay from '../CustomCell/CustomCellSettingsDisplay.vue';
    import CustomCellDisplayLinks from '../CustomCell/CustomCellDisplayLinks.vue';
    import CustomCellSettingsDdl from '../CustomCell/CustomCellSettingsDdl.vue';
    import CustomCellSettingsPermission from '../CustomCell/CustomCellSettingsPermission.vue';
    import CustomCellColRowGroup from '../CustomCell/CustomCellColRowGroup.vue';
    import CustomCellKanbanSett from '../CustomCell/CustomCellKanbanSett.vue';
    import CustomCellRefConds from '../CustomCell/CustomCellRefConds.vue';
    import CustomCellCondFormat from '../CustomCell/CustomCellCondFormat.vue';
    import CustomCellPlans from '../CustomCell/CustomCellPlans.vue';
    import CustomCellConnection from '../CustomCell/CustomCellConnection.vue';
    import CustomCellUserGroups from '../CustomCell/CustomCellUserGroups.vue';
    import CustomCellInvitations from '../CustomCell/CustomCellInvitations.vue';

    import CustomEditPopUp from "../CustomPopup/CustomEditPopUp";
    import CarouselBlock from "../CommonBlocks/CarouselBlock";
    import FullSizeImgBlock from "../CommonBlocks/FullSizeImgBlock";
    import VerticalTable from "./VerticalTable";

    export default {
        name: "BoardTable",
        mixins: [
            ReactiveProviderMixin,
            IsShowFieldMixin,
            CellStyleMixin,
        ],
        components: {
            VerticalTable,
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
            CustomCellRefConds,
            CustomCellCondFormat,
            CustomCellPlans,
            CustomCellConnection,
            CustomCellUserGroups,
            CustomCellInvitations,
        },
        data: function () {
            return {
                selectedCell: new SelectedCells(),
                editPopUpRow: null,
                overImages: null,
                overImageIdx: null,
                show_delay: false,
            };
        },
        props:{
            boardSettings: Object,
            globalMeta: Object,
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
            availableColumns: Array,
            behavior: String,
        },
        computed: {
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
            updatedCell(tableRow, hdr) {
                this.$emit('updated-cell', tableRow, hdr);
            },
            showDefValPopup(tableRow) {
                this.$emit('show-def-val-popup', tableRow);
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
            isShowImage(tableHeader) {
                return this.isShowField(tableHeader) && tableHeader.is_image_on_board;
            },

            //images
            getBoardImages(tableRow) {
                let images = [];
                _.each(this.tableMeta._fields, (fld) => {
                    if (this.isShowImage(fld) && fld.f_type === 'Attachment') {
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
        },
        mounted() {
            setTimeout(() => {
                this.show_delay = true;
            }, 1);
        },
        beforeDestroy() {
        }
    }
</script>

<style lang="scss" scoped>
    .board-wrapper {
        margin: 15px 5px 15px 0;
        border: 2px solid transparent;
        border-radius: 10px;
        transition: all 0.7s;

        &:hover {
            background-color: #f7f7f7;
            border-color: #777;
        }

        .board-border {
            border: 1px solid rgb(119, 119, 119);
            padding: 5px;
            overflow: auto;
            width: 100%;
            border-radius: 5px;
        }

        .board-table {
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
        }
    }
</style>