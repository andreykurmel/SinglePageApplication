<template>
    <div class="popup-wrapper" v-show="show_this" @click.self="hide()" :style="{zIndex: zIdx}">
        <div class="popup" :style="getPopupStyle()">
            <div class="flex flex--col">
                <div class="popup-header">
                    <div class="drag-bkg" draggable="true" @dragstart="dragPopSt()" @drag="dragPopup()"></div>
                    <span>Referencing Conditions (RCs)</span>
                    <span class="glyphicon glyphicon-remove pull-right header-btn" @click="hide()"></span>
                </div>
                <div class="flex__elem-remain popup-content">
                    <div class="flex__elem__inner popup-main">

                        <div class="popup-overflow">
                            <div class="section-text">Add New Referencing Condition (RC)</div>
                            <div class="full-frame" style="height: calc(100% - 240px)">
                                <custom-table
                                        :cell_component_name="'custom-cell-ref-conds'"
                                        :global-meta="tableMeta"
                                        :table-meta="$root.settingsMeta['table_ref_conditions']"
                                        :all-rows="tableMeta ? tableMeta._ref_conditions : []"
                                        :rows-count="tableMeta ? tableMeta._ref_conditions.length : 0"
                                        :cell-height="1"
                                        :max-cell-rows="0"
                                        :is-full-width="true"
                                        :behavior="'data_sets'"
                                        :user="user"
                                        :adding-row="addingRow"
                                        :selected-row="selectedRefGroup"
                                        :settings-meta="$root.settingsMeta"
                                        :forbidden-columns="$root.systemFields.concat(['notes'])"
                                        @added-row="addRefGroup"
                                        @updated-row="updateRefGroup"
                                        @delete-row="deleteRefGroup"
                                        @row-index-clicked="rowIndexClickedRefGroup"
                                ></custom-table>
                            </div>
                            <div class="section-text">
                                <span v-if="selectedRefGroup < 0">Select a RC</span>
                                <span v-else="">Logic Conditions (LCs) of selected RC:
                                    <span>{{ tableMeta._ref_conditions[selectedRefGroup] ? tableMeta._ref_conditions[selectedRefGroup].name : '' }}</span>
                                </span>

                                <div v-if="selectedRefGroup > -1 && tableMeta._ref_conditions[selectedRefGroup]" class="right-elem">
                                    <button class="btn btn-default btn-sm blue-gradient full-height"
                                            :style="$root.themeButtonStyle"
                                            @click="copyLCs()"
                                    >Copy</button>
                                    <select class="form-control full-height" v-model="rc_for_copy">
                                        <option v-for="ref in tableMeta._ref_conditions"
                                                v-if="ref && ref.id != tableMeta._ref_conditions[selectedRefGroup].id"
                                                :value="ref.id"
                                        >{{ ref.name }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="full-frame" style="height: 165px">
                                <custom-table
                                        :cell_component_name="'custom-cell-ref-conds'"
                                        :global-meta="tableMeta"
                                        :table-meta="$root.settingsMeta['table_ref_condition_items']"
                                        :all-rows="selectedRefGroup > -1 ? selectedRefCondItems : []"
                                        :rows-count="selectedRefGroup > -1 ? selectedRefCondItems.length : 0"
                                        :cell-height="1"
                                        :max-cell-rows="0"
                                        :is-full-width="true"
                                        :user="user"
                                        :adding-row="addingRowRC"
                                        :behavior="'data_sets_ref_condition_items'"
                                        :forbidden-columns="$root.systemFields"
                                        :selected-row="selectedRefItem"
                                        :ref_tb_from_refcond="ref_tb_from_refcond"
                                        @added-row="addRefGroupItem"
                                        @updated-row="updateRefGroupItem"
                                        @delete-row="deleteRefGroupItem"
                                        @row-index-clicked="rowIndexClickedRefItem"
                                ></custom-table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import RefConditionsMixin from './../_Mixins/RefConditionsMixin';
    import PopupAnimationMixin from './../_Mixins/PopupAnimationMixin';

    import CustomTable from '../CustomTable/CustomTable';
    import VerticalTable from '../CustomTable/VerticalTable';

    import {eventBus} from '../../app';

    export default {
        name: "RefConditionsPopup",
        mixins: [
            RefConditionsMixin,
            PopupAnimationMixin,
        ],
        components: {
            CustomTable,
            VerticalTable,
        },
        data: function () {
            return {
                show_this: false,

                addingRow: {
                    active: true,
                    position: 'bottom'
                },
                selectedRefGroup: -1,
                selectedRefItem: -1,
                //PopupAnimationMixin
                getPopupWidth: 1200,
                idx: 0,
            }
        },
        props:{
            tableMeta: Object,
            table_id: Number|null,
            user:  Object
        },
        watch: {
            table_id: function(val) {
                this.selectedRefGroup = -1;
                this.selectedRefItem = -1;
            }
        },
        methods: {
            hide() {
                this.show_this = false;
                this.$root.tablesZidx -= 10;
                eventBus.$emit('ref-conditions-popup-closed');
            },
            showRefConditionsPopupHandler(db_name, refId) {
                if (!db_name || db_name === this.tableMeta.db_name) {
                    this.selectedRefGroup = _.findIndex(this.tableMeta._ref_conditions, {id: Number(refId)});
                    this.show_this = true;
                    this.$root.tablesZidx += 10;
                    this.zIdx = this.$root.tablesZidx;
                    this.runAnimation();
                }
            }
        },
        mounted() {
            eventBus.$on('global-keydown', this.hideMenu);
            eventBus.$on('show-ref-conditions-popup', this.showRefConditionsPopupHandler);
        },
        beforeDestroy() {
            eventBus.$off('global-keydown', this.hideMenu);
            eventBus.$off('show-ref-conditions-popup', this.showRefConditionsPopupHandler);
        }
    }
</script>

<style lang="scss" scoped>
    @import "CustomEditPopUp";

    .popup-wrapper {

        .popup {
            position: relative;

            .popup-content {

                .popup-main {
                    padding: 10px;
                }
                .section-text {
                    padding: 5px 10px;
                    font-size: 16px;
                    font-weight: bold;
                    background-color: #CCC;
                }
                .section-text:not(:first-child) {
                    margin-top: 5px;
                }

            }

            .right-elem {
                button {
                    padding: 0 6px;
                }
                select {
                    padding: 0;
                }
            }
        }
    }
</style>