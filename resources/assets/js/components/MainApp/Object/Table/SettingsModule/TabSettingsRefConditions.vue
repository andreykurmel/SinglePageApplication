<template>
    <div class="container-fluid full-height">
        <div class="row full-height permissions-tab flex flex--col">
            <!--LEFT SIDE-->
            <div class="col-xs-12" :style="{height: '65%', position: 'relative'}">
                <div class="top-text top-text--height" :style="textSysStyle">
                    <span>Referencing Conditions (RCs)</span>

                    <info-sign-link
                            class="right-elem"
                            :app_sett_key="'help_link_settings_refconds'"
                            :hgt="26"
                    ></info-sign-link>
                </div>
                <div class="permissions-panel" style="height: calc(100% - 40px); padding: 0 0 22px 0;">
                    <div class="full-frame">
                        <custom-table
                                :cell_component_name="'custom-cell-ref-conds'"
                                :global-meta="tableMeta"
                                :table-meta="settingsMeta['table_ref_conditions']"
                                :all-rows="tableMeta._ref_conditions"
                                :rows-count="tableMeta._ref_conditions.length"
                                :cell-height="1"
                                :max-cell-rows="0"
                                :is-full-width="true"
                                :behavior="'data_sets'"
                                :user="user"
                                :adding-row="addingRow"
                                :selected-row="selectedRefGroup"
                                :settings-meta="settingsMeta"
                                :forbidden-columns="$root.systemFields.concat(['notes'])"
                                :use_theme="true"
                                :widths_div="3"
                                @added-row="addRefGroup"
                                @updated-row="updateRefGroup"
                                @delete-row="deleteRefGroup"
                                @row-index-clicked="rowIndexClickedRefGroup"
                        ></custom-table>
                    </div>
                </div>
                <span class="noter">* Tables shared by others need to have permission "Referencing for Sharing" in the sharing to be available for selecting in "Source Table".</span>
            </div>

            <!--CENTER-->

            <div class="col-xs-12" :style="{height: '35%'}">

                <div class="full-height">
                    <div class="top-text top-text--height" :style="textSysStyle">
                        <span v-if="!tableMeta._ref_conditions[selectedRefGroup]">You should select RC</span>
                        <span v-else="">Logic Condition(s) of RC:
                            <span>{{ tableMeta._ref_conditions[selectedRefGroup].name || '' }}</span>
                        </span>

                        <div v-if="tableMeta._ref_conditions[selectedRefGroup]" class="right-elem">
                            <button class="btn btn-default btn-sm blue-gradient full-height"
                                    :style="$root.themeButtonStyle"
                                    @click="copyLCs()"
                            >Copy</button>
                            <select class="form-control full-height" v-model="rc_for_copy">
                                <option v-for="ref in tableMeta._ref_conditions"
                                        v-if="ref.id != tableMeta._ref_conditions[selectedRefGroup].id"
                                        :value="ref.id"
                                >{{ ref.name }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="permissions-panel no-padding">
                        <div class="full-frame">
                            <custom-table
                                    v-if="tableMeta._ref_conditions[selectedRefGroup]"
                                    :cell_component_name="'custom-cell-ref-conds'"
                                    :global-meta="tableMeta"
                                    :table-meta="settingsMeta['table_ref_condition_items']"
                                    :all-rows="selectedRefCondItems"
                                    :rows-count="selectedRefCondItems.length"
                                    :cell-height="1"
                                    :max-cell-rows="0"
                                    :is-full-width="true"
                                    :user="user"
                                    :adding-row="addingRowRC"
                                    :behavior="'data_sets_ref_condition_items'"
                                    :forbidden-columns="$root.systemFields"
                                    :selected-row="selectedRefItem"
                                    :use_theme="true"
                                    :widths_div="2"
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
</template>

<script>
    import RefConditionsMixin from '../../../../_Mixins/RefConditionsMixin';
    import CellStyleMixin from "../../../../_Mixins/CellStyleMixin";

    import CustomTable from '../../../../CustomTable/CustomTable';
    import VerticalTable from '../../../../CustomTable/VerticalTable';
    import InfoSignLink from "../../../../CustomTable/Specials/InfoSignLink";

    import {eventBus} from '../../../../../app';

    export default {
        name: "TabSettingsRefConditions",
        mixins: [
            RefConditionsMixin,
            CellStyleMixin,
        ],
        components: {
            InfoSignLink,
            CustomTable,
            VerticalTable
        },
        data: function () {
            return {
                addingRow: {
                    active: true,
                    position: 'bottom'
                },
            }
        },
        props:{
            tableMeta: Object,
            settingsMeta: Object,
            table_id: Number|null,
            type: String, //['table' or 'folder']
            user:  Object,
        },
        watch: {
            table_id: function(val) {
                this.selectedRefGroup = 0;
                this.selectedRefItem = 0;
            }
        },
        methods: {
        },
        mounted() {
        },
        beforeDesctroy() {
        }
    }
</script>

<style lang="scss" scoped>
    @import "TabSettingsPermissions";

    .right-elem {
        button {
            padding: 0 6px;
        }
        select {
            padding: 0;
        }
    }
    .noter {
        position: absolute;
        bottom: 10px;
        right: 0;
        left: 10px;
        font-size: 1.5rem;
    }
</style>