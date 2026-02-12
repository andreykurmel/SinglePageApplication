<template>
    <div class="full-height" :style="sysStyleWiBg">
        <div class="full-height permissions-tab">

            <div class="permissions-panel full-height" :style="$root.themeMainBgStyle">

                <div class="permissions-menu-header m-5">
                    <button class="btn btn-default h36" :style="textSysStyle" :class="{active : activeTab === 'list'}" @click="changeTab('list')">
                        List
                    </button>
                    <button class="btn btn-default h36" :style="textSysStyle" :class="{active : activeTab === 'lcs'}" @click="changeTab('lcs')">
                        Details-LCs
                    </button>

                    <div v-if="sel_refcond" class="flex flex--center-v flex--end" style="position: absolute; top: 0; right: 0; height: 32px; width: calc(100% - 205px);">

                        <label v-show="activeTab === 'lcs'" class="flex flex--center" style="margin: 0 0 0 5px;white-space: nowrap;width: 240px;position: absolute;left: 0;" :style="textSysStyleSmart">
                            Base:&nbsp;
                            <select-block
                                :is_disabled="!!sel_refcond.is_system"
                                :options="refcondOpts(true)"
                                :sel_value="sel_refcond.base_refcond_id"
                                :with_links="true"
                                :style="{ maxWidth:'200px', height:'32px', }"
                                @option-select="baseRefChange"
                                @link-click="refcondChange({val: sel_refcond.base_refcond_id})"
                            ></select-block>
                        </label>

                        <div v-show="activeTab === 'lcs'" class="flex flex--center-v ml15 mr0">
                            <button class="btn btn-default btn-sm blue-gradient full-height"
                                    :disabled="!!sel_refcond.is_system"
                                    :style="$root.themeButtonStyle"
                                    @click="copyPopup = true"
                            >Copy</button>
                        </div>

                        <label class="flex flex--center ml15 mr0" style="margin: 0 0 0 5px;white-space: nowrap;width: 300px;" :style="textSysStyleSmart">
                            Loaded RC:&nbsp;
                            <select-block
                                :options="refcondOpts()"
                                :sel_value="sel_refcond.id"
                                :style="{ maxWidth:'200px', height:'32px', }"
                                @option-select="refcondChange"
                            ></select-block>
                        </label>
                    </div>
                </div>
                <div class="permissions-menu-body" style="border: 1px solid #CCC;">

                    <div v-show="activeTab === 'list'" class="permissions-panel full-height flex no-padding">
                        <div class="flex flex--col">
                            <custom-table
                                v-if="draw_table"
                                :cell_component_name="'custom-cell-ref-conds'"
                                :global-meta="tableMeta"
                                :table-meta="settingsMeta['table_ref_conditions']"
                                :all-rows="tbm_refconds"
                                :rows-count="tbm_refconds.length"
                                :cell-height="1"
                                :max-cell-rows="0"
                                :is-full-width="true"
                                :behavior="'ref_conds'"
                                :user="user"
                                :adding-row="addingRow"
                                :selected-row="selectedRefGroup"
                                :settings-meta="settingsMeta"
                                :available-columns="is_incoming
                                ? ['name','ref_table_id','_has_inheritance']
                                : ['name','ref_table_id','rc_static','_out_uses','_create_reverse']"
                                :use_theme="true"
                                :widths_div="3"
                                :with_edit="!is_incoming"
                                :parent-row="is_incoming ? {} : undefined"
                                @added-row="addRefGroup"
                                @updated-row="updateRefGroup"
                                @delete-row="deleteRefGroup"
                                @reorder-rows="rowsReordered"
                                @row-index-clicked="rowIndexClickedRefGroup"
                            ></custom-table>
                            <span class="noter">* Tables shared by others must have item "Referencing for Sharing" checked in the sharing permission to be available for selecting at "Source Table".</span>
                        </div>
                        <div v-if="sel_refcond && sel_refcond._out_uses" style="width: 300px; flex-shrink: 0; padding: 5px; border-left: 1px solid #CCC;">
                            <div class="flex flex--space white p5 mb5" style="background: #444;">
                                <label class="no-margin">Uses of {{ sel_refcond.name }}</label>
                                <i class="glyphicon glyphicon-remove pointer" @click="closeDetails"></i>
                            </div>
                            <vertical-table
                                :td="'custom-cell-ref-conds'"
                                :global-meta="tableMeta"
                                :table-meta="settingsMeta['table_ref_conditions']"
                                :settings-meta="settingsMeta"
                                :table-row="sel_refcond"
                                :cell-height="1"
                                :max-cell-rows="0"
                                :user="user"
                                :behavior="'ref_conds'"
                                :available-columns="['_uses_rows','_uses_links','_uses_ddls','_uses_mirrors','_uses_formulas']"
                                @updated-row="updateRefGroup"
                            ></vertical-table>
                        </div>
                    </div>

                    <div v-show="activeTab === 'lcs'" class="permissions-panel full-height no-padding">
                        <custom-table
                            v-if="sel_refcond"
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
                            :with_edit="!is_incoming && !sel_refcond.is_system"
                            :parent-row="sel_refcond"
                            :special_extras="{ is_incoming }"
                            @added-row="addRefGroupItem"
                            @updated-row="updateRefGroupItem"
                            @delete-row="deleteRefGroupItem"
                            @row-index-clicked="rowIndexClickedRefItem"
                        ></custom-table>
                    </div>
                </div>
            </div>

        </div>

        <slot-popup
            v-if="copyPopup"
            :popup_width="400"
            :popup_height="175"
            @popup-close="copyPopup = false"
        >
            <template v-slot:title>
                <span>Copy RC Details (LCs)</span>
            </template>
            <template v-slot:body>
                <div class="p10">
                    <div class="flex flex--center-v" style="margin: 15px 0;">
                        <label class="no-margin no-wrap">Select source RC:&nbsp;</label>
                        <select-block
                            :options="lcsOpts()"
                            :sel_value="rc_for_copy"
                            :fixed_pos="true"
                            style="height: 32px"
                            @option-select="(opt) => { rc_for_copy = opt.val; }"
                        ></select-block>
                    </div>
                    <button class="btn btn-default btn-sm blue-gradient pull-right"
                            :disabled="!!sel_refcond.is_system"
                            :style="$root.themeButtonStyle"
                            @click="copyLCs()"
                    >Copy</button>
                </div>
            </template>
        </slot-popup>
    </div>
</template>

<script>
    import {eventBus} from '../../../../../app';

    import RefConditionsMixin from '../../../../_Mixins/RefConditionsMixin';
    import CellStyleMixin from "../../../../_Mixins/CellStyleMixin";

    import CustomTable from '../../../../CustomTable/CustomTable';
    import SelectBlock from "../../../../CommonBlocks/SelectBlock";

    export default {
        name: "TabSettingsRefConditions",
        mixins: [
            RefConditionsMixin,
            CellStyleMixin,
        ],
        components: {
            SelectBlock,
            CustomTable,
        },
        data: function () {
            return {
                copyPopup: false,
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
            extRefGroup: Number,
            is_incoming: Boolean,
            filter_id: Number,
        },
        watch: {
            table_id: function(val) {
                this.chngRefGr(0);
                this.selectedRefItem = 0;
            }
        },
        computed: {
            sysStyleWiBg() {
                return {
                    ...this.textSysStyle,
                    ...this.$root.themeMainBgStyle,
                };
            },
        },
        methods: {
            lcsOpts() {
                if (! this.sel_refcond) {
                    return [];
                }

                let conds = _.filter(this.tbm_refconds, (ref) => {
                    return ref.id != this.sel_refcond.id && ref.ref_table_id == this.sel_refcond.ref_table_id
                });
                return _.map(conds, (rc) => {
                    return { val:rc.id, show:rc.name };
                });
            },
        },
        mounted() {
            if (isNumber(this.extRefGroup)) {
                this.chngRefGr(this.extRefGroup);
            }
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
        flex-shrink: 0;
        padding: 3px 5px 1px 10px;
        font-size: 1.5rem;
        overflow: hidden;
    }
    .permissions-tab {
        padding: 5px;
    }
</style>