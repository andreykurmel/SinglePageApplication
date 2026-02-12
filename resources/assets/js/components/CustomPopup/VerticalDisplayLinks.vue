<template>
    <div class="popup-wrapper" v-if="selHeader" @click.self="hide()" :style="{zIndex: zIdx}">
        <div class="popup" :style="getPopupStyle()">
            <div class="flex flex--col">
                <div class="popup-header">
                    <div class="drag-bkg" draggable="true" @dragstart="dragPopSt()" @drag="dragPopup()"></div>
                    <span v-if="selHeader">You should select column</span>
                    <span v-else="">Link(s) at Column: <span>{{ $root.uniqName(selHeader.name) }}</span></span>
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
                                        :all-rows="selHeader ? selHeader._links : []"
                                        :rows-count="selHeader ? selHeader._links.length : 0"
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
                                        @show-add-ddl-option="showLinkedPermissions"
                                ></custom-table>
                            </div>
                            <div class="section-text">
                                <span v-if="selectedLink < 0 || !selHeader">Select a Link listed above</span>
                                <span v-else="">Details for Link #{{ selectedLink+1 }}</span>
                            </div>
                            <div class="pt5" v-if="selectedLink > -1 && selHeader">
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
                                        :headers-changer="headersChang"
                                        :widths="{name: '35%', col: '65%', history: 0, no_margins: true}"
                                        @show-add-ref-cond="showAddRefCond"
                                        @updated-cell="updateLink"
                                ></vertical-table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="resizer" draggable="true" @dragstart="dragResizeStart" @drag="dragResizeDo"></div>
        </div>

        <permissions-settings-popup
            v-if="linkedMeta"
            :table-meta="linkedMeta"
            :user="$root.user"
            :init_show="true"
            @hidden-form="linkedPermisClose()"
        ></permissions-settings-popup>
    </div>
</template>

<script>
    import DisplayLinksMixin from '../_Mixins/DisplayLinksMixin';
    import PopupAnimationMixin from '../_Mixins/PopupAnimationMixin';

    import CustomTable from '../CustomTable/CustomTable';

    import {eventBus} from '../../app';
    import PermissionsSettingsPopup from "./PermissionsSettingsPopup.vue";

    export default {
        name: "VerticalDisplayLinks",
        mixins: [
            DisplayLinksMixin,
            PopupAnimationMixin,
        ],
        components: {
            PermissionsSettingsPopup,
            CustomTable,
        },
        data: function () {
            return {
                linkedMeta: null,
                selHeader: null,
                selectedLink: -1,
                //PopupAnimationMixin
                getPopupHeight: '80%',
                getPopupWidth: 820,
                idx: 0,
            }
        },
        props:{
            tableMeta: Object,
            settingsMeta: Object,
            cellHeight: Number,
            maxCellRows: Number,
            user: Object,
            table_id: Number|null,
        },
        methods: {
            storeSizes(width_px, height_px) {
                this.getPopupWidth = width_px;
                this.getPopupHeight = height_px+'px';
            },
            hide() {
                this.selHeader = null;
                this.selectedLink = -1;
                this.$root.tablesZidxDecrease();
            },
            showAddRefCond(refId) {
                eventBus.$emit('show-ref-conditions-popup', this.tableMeta.db_name, refId);
            },
            showVerticalDisplayLinks(tableHeader) {
                this.selHeader = tableHeader;
                this.$root.tablesZidxIncrease();
                this.zIdx = this.$root.tablesZidx;
                this.runAnimation();
            },
            showLinkedPermissions(refTbId) {
                $.LoadingOverlay('show');
                axios.post('/ajax/table-data/get-headers', {
                    table_id: refTbId,
                    user_id: this.$root.user.id,
                }).then(({ data }) => {
                    this.linkedMeta = data;
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
            linkedPermisClose() {
                this.linkedMeta = null;
            },
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