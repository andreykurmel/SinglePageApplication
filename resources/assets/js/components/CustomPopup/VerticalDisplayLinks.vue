<template>
    <div class="popup-wrapper" v-if="selectedCol > -1" @click.self="hide()" :style="{zIndex: zIdx}">
        <div class="popup">
            <div class="flex flex--col">
                <div class="popup-header">
                    <span v-if="selectedCol < 0">You should select column</span>
                    <span v-else="">Link(s) at Column: <span>{{ $root.uniqName(tableMeta._fields[selectedCol].name) }}</span></span>
                    <span class="glyphicon glyphicon-remove pull-right header-btn" @click="hide()"></span>
                </div>
                <div class="flex__elem-remain popup-content">
                    <div class="flex__elem__inner popup-main">

                        <div class="popup-overflow">
                            <div>
                                <custom-table
                                        :cell_component_name="'custom-cell-display-links'"
                                        :global-meta="tableMeta"
                                        :table-meta="settingsMeta['table_field_links']"
                                        :settings-meta="settingsMeta"
                                        :all-rows="selectedCol > -1 ? tableMeta._fields[selectedCol]._links : []"
                                        :rows-count="selectedCol > -1 ? tableMeta._fields[selectedCol]._links.length : 0"
                                        :cell-height="1"
                                        :max-cell-rows="0"
                                        :is-full-width="true"
                                        :adding-row="addingRow"
                                        :user="user"
                                        :behavior="'settings_display_links'"
                                        :available-columns="availableLinks"
                                        :selected-row="selectedLink"
                                        @added-row="addLink"
                                        @updated-row="updateLink"
                                        @delete-row="deleteLink"
                                        @row-index-clicked="rowIndexClickedLink"
                                ></custom-table>
                            </div>
                            <div class="section-text">
                                <span v-if="selectedLink < 0 || selectedCol < 0">Select a Link listed above</span>
                                <span v-else="">Details for Link #{{ selectedLink+1 }}</span>
                            </div>
                            <div class="" v-if="selectedLink > -1 && selectedCol > -1">
                                <vertical-table
                                        class="spaced-table__fix"
                                        :td="'custom-cell-display-links'"
                                        :global-meta="tableMeta"
                                        :table-meta="settingsMeta['table_field_links']"
                                        :settings-meta="settingsMeta"
                                        :table-row="linkRow"
                                        :user="user"
                                        :cell-height="1"
                                        :max-cell-rows="0"
                                        :available-columns="availableLinkColumns"
                                        :widths="{name: '35%', col: '65%', history: 0, no_margins: true}"
                                        @show-add-ref-cond="showAddRefCond"
                                        @updated-cell="updateLink"
                                ></vertical-table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import DisplayLinksMixin from '../_Mixins/DisplayLinksMixin';
    import PopupAnimationMixin from '../_Mixins/PopupAnimationMixin';

    import CustomTable from '../CustomTable/CustomTable';
    import VerticalTable from '../CustomTable/VerticalTable';

    import {eventBus} from '../../app';

    export default {
        name: "VerticalDisplayLinks",
        mixins: [
            DisplayLinksMixin,
            PopupAnimationMixin,
        ],
        components: {
            CustomTable,
            VerticalTable
        },
        data: function () {
            return {
                selectedCol: -1,
                //PopupAnimationMixin
                getPopupWidth: 700,
                idx: 0,
            }
        },
        props:{
            tableMeta: Object,
            settingsMeta: Object,
            cellHeight: Number,
            maxCellRows: Number,
            user:  Object,
            table_id: Number|null,
        },
        methods: {
            hide() {
                this.selectedCol = -1;
                this.selectedLink = -1;
                this.$root.tablesZidx -= 10;
            },
            showAddRefCond(refId) {
                eventBus.$emit('show-ref-conditions-popup', this.tableMeta.db_name, refId);
            },
            showVerticalDisplayLinks(col) {
                this.selectedCol = col;
                this.$root.tablesZidx += 10;
                this.zIdx = this.$root.tablesZidx;
                this.runAnimation();
            }
        },
        mounted() {
            eventBus.$on('global-keydown', this.hideMenu);
            eventBus.$on('show-vertical-display-links', this.showVerticalDisplayLinks);
        },
        beforeDestroy() {
            eventBus.$off('global-keydown', this.hideMenu);
            eventBus.$off('show-vertical-display-links', this.showVerticalDisplayLinks);
        }
    }
</script>

<style lang="scss" scoped>
    @import "CustomEditPopUp";

    .popup-wrapper {
        .popup {
            width: 700px;
            position: relative;
            margin: 3% auto;
            transform: initial;
            top: initial;
            left: initial;

            .section-text {
                padding: 5px 10px;
                font-size: 16px;
                font-weight: bold;
                background-color: #CCC;
            }

            .spaced-table__fix {
                margin: 0 10px;
                width: calc(100% - 20px);
            }
        }
    }
</style>