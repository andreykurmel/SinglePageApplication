<template>
    <div class="full-height">
        <div class="full-height permissions-tab p5" :style="$root.themeMainBgStyle">

            <div class="permissions-panel full-height" :style="$root.themeMainBgStyle">

                <div class="permissions-menu-header">
                    <button class="btn btn-default btn-sm" :style="textSysStyle" :class="{active : activeTab === 'list'}" @click="changeTab('list')">
                        List
                    </button>
                    <button class="btn btn-default btn-sm" :style="textSysStyle" :class="{active : activeTab === 'settings'}" @click="changeTab('settings')">
                        Levels
                    </button>

                    <div v-if="selectedGroup" class="flex flex--center-v flex--end" style="position: absolute; top: 0; right: 0; height: 32px; width: 50%;">
                        <label class="flex flex--center ml15 mr0" style="margin: 0 0 0 5px;white-space: nowrap;" :style="textSysStyleSmart">
                            Loaded settings for:&nbsp;
                            <select-block
                                :options="groupOpts()"
                                :sel_value="selectedGroup.id"
                                :style="{ width:'150px', height:'32px', }"
                                @option-select="rowIndexClicked"
                            ></select-block>
                        </label>
                    </div>
                </div>
                <div class="permissions-menu-body" style="border: 1px solid #CCC;">

                    <div class="full-height permissions-panel no-padding" v-show="activeTab === 'list'" :style="$root.themeMainBgStyle">
                        <custom-table
                            :cell_component_name="'custom-cell-addon'"
                            :global-meta="tableMeta"
                            :table-meta="$root.settingsMeta['table_groupings']"
                            :settings-meta="$root.settingsMeta"
                            :all-rows="tableMeta._groupings"
                            :adding-row="{ active: true, position: 'bottom' }"
                            :rows-count="tableMeta._groupings.length"
                            :cell-height="1"
                            :max-cell-rows="0"
                            :is-full-width="true"
                            :with_edit="canAddonEdit()"
                            :selected-row="selIdx"
                            :user="$root.user"
                            :behavior="'settings_groupings'"
                            :use_theme="true"
                            @added-row="addGroup"
                            @updated-row="updateGroup"
                            @delete-row="deleteGroup"
                            @row-index-clicked="rowIndexClicked"
                            @show-def-val-popup="showStatsPop"
                        ></custom-table>
                        <grouping-stats-pop-up
                            v-if="selectedGroupSt"
                            :table-meta="tableMeta"
                            :grouping-row="selectedGroup"
                            @popup-close="selectedGroupSt = null"
                            @should-redraw="$emit('should-redraw')"
                        ></grouping-stats-pop-up>
                    </div>

                    <div class="full-height permissions-panel no-padding" v-show="activeTab === 'settings'" :style="$root.themeMainBgStyle">
                        <custom-table
                            v-if="selectedGroup"
                            :cell_component_name="'custom-cell-addon'"
                            :global-meta="tableMeta"
                            :table-meta="$root.settingsMeta['table_grouping_fields']"
                            :settings-meta="$root.settingsMeta"
                            :all-rows="selectedGroup._settings"
                            :adding-row="addingRow"
                            :rows-count="selectedGroup._settings.length"
                            :cell-height="1"
                            :max-cell-rows="0"
                            :is-full-width="true"
                            :with_edit="canPermisEdit()"
                            :user="$root.user"
                            :behavior="'settings_grouping_fields'"
                            :use_theme="true"
                            @added-row="addGroupField"
                            @updated-row="updateGroupField"
                            @delete-row="deleteGroupField"
                            @reorder-rows="rowsReordered"
                            @show-def-val-popup="showFieldStatsPop"
                        ></custom-table>
                        <grouping-stats-pop-up
                            v-if="selectedGroupFieldId"
                            :table-meta="tableMeta"
                            :grouping-row="selectedGroup"
                            :selected-group-field-id="selectedGroupFieldId"
                            @popup-close="selectedGroupFieldId = null"
                            @should-redraw="$emit('should-redraw')"
                        ></grouping-stats-pop-up>
                    </div>

                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import {ChartFunctions} from "../ChartAddon/ChartFunctions";

    import CustomTable from "../../../../CustomTable/CustomTable";
    import SelectBlock from "../../../../CommonBlocks/SelectBlock";
    import GroupingStatsPopUp from "./GroupingStatsPopUp.vue";

    import CellStyleMixin from "../../../../_Mixins/CellStyleMixin";
    import {eventBus} from "../../../../../app";

    export default {
        name: "GroupingSettings",
        components: {
            GroupingStatsPopUp,
            SelectBlock,
            CustomTable,
        },
        mixins: [
            CellStyleMixin,
        ],
        data: function () {
            return {
                selectedGroupSt: null,
                selectedGroupFieldId: null,
                selIdx: 0,
                activeTab: 'list',
            }
        },
        props: {
            tableMeta: Object,
        },
        computed: {
            selectedGroup() {
                return this.tableMeta._groupings[this.selIdx];
            },
            addingRow() {
                return {
                    active: this.selectedGroup._settings.length <= ChartFunctions.maxLVL(),
                    position: 'bottom'
                };
            },
        },
        watch: {
        },
        methods: {
            canPermisEdit() {
                return this.$root.addonCanPermisEdit(this.tableMeta, this.selectedGroup, '_group_rights');
            },
            canAddonEdit() {
                return this.$root.addonCanEditGeneral(this.tableMeta, 'group');
            },
            changeTab(key) {
                this.activeTab = key;
                this.$emit('set-sub-tab', key);
            },
            groupOpts() {
                return _.map(this.tableMeta._groupings, (fld) => {
                    return { val:fld.id, show:fld.name, };
                });
            },
            rowIndexClicked(optOrIdx) {
                if (typeof optOrIdx === 'object') {
                    this.selIdx = _.findIndex(this.tableMeta._groupings, {id: Number(optOrIdx.val || optOrIdx.id)});
                } else {
                    this.selIdx = optOrIdx;
                }
            },

            //Validation popup
            showStatsPop(tableRow) {
                this.rowIndexClicked(tableRow);
                this.selectedGroupSt = tableRow;
            },
            showFieldStatsPop(tableRow) {
                this.selectedGroupFieldId = tableRow.id;
            },
            hideStatsPop() {
                this.selectedGroupFieldId = null;
            },

            //Group
            addGroup(tableRow) {
                let fields = _.cloneDeep(tableRow);//copy object
                this.$root.deleteSystemFields(fields);

                $.LoadingOverlay('show');
                axios.post('/ajax/addon-grouping', {
                    table_id: this.tableMeta.id,
                    fields: fields
                }).then(({ data }) => {
                    this.tableMeta._groupings = data;
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
            updateGroup(tableRow) {
                let fields = _.cloneDeep(tableRow);//copy object
                this.$root.deleteSystemFields(fields);

                $.LoadingOverlay('show');
                axios.put('/ajax/addon-grouping', {
                    table_grouping_id: tableRow.id,
                    fields: fields
                }).then(({ data }) => {
                    this.tableMeta._groupings = data;
                    this.$emit('should-redraw');
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
            deleteGroup(tableRow) {
                $.LoadingOverlay('show');
                axios.delete('/ajax/addon-grouping', {
                    params: {
                        table_grouping_id: tableRow.id
                    }
                }).then(({ data }) => {
                    this.tableMeta._groupings = data;
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
            //GroupField
            addGroupField(tableRow) {
                let fields = _.cloneDeep(tableRow);//copy object
                this.$root.deleteSystemFields(fields);

                $.LoadingOverlay('show');
                axios.post('/ajax/addon-grouping/field', {
                    table_grouping_id: this.selectedGroup.id,
                    fields: fields
                }).then(({ data }) => {
                    this.tableMeta._groupings = data;
                    this.$emit('should-redraw');
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
            updateGroupField(tableRow) {
                let fields = _.cloneDeep(tableRow);//copy object
                this.$root.deleteSystemFields(fields);

                $.LoadingOverlay('show');
                axios.put('/ajax/addon-grouping/field', {
                    table_grouping_field_id: tableRow.id,
                    fields: fields
                }).then(({ data }) => {
                    this.tableMeta._groupings = data;
                    this.$emit('should-redraw');
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
            deleteGroupField(tableRow) {
                $.LoadingOverlay('show');
                axios.delete('/ajax/addon-grouping/field', {
                    params: {
                        table_grouping_field_id: tableRow.id
                    }
                }).then(({ data }) => {
                    this.tableMeta._groupings = data;
                    this.$emit('should-redraw');
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
            rowsReordered() {
                this.$root.sm_msg_type = 2;
                axios.get('/ajax/addon-grouping/fields', {
                    params: {
                        table_grouping_id: this.selectedGroup.id,
                    }
                }).then(({ data }) => {
                    this.tableMeta._groupings = data;
                    this.$emit('should-redraw');
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
        },
        mounted() {
            eventBus.$on('grouping-settings__update-grouping-level', this.updateGroupField);
            eventBus.$on('grouping-settings__reorder-grouping-level', this.rowsReordered);
        },
        beforeDestroy() {
            eventBus.$off('grouping-settings__update-grouping-level', this.updateGroupField);
            eventBus.$off('grouping-settings__reorder-grouping-level', this.rowsReordered);
        }
    }
</script>

<style lang="scss" scoped>
    @import "./../SettingsModule/TabSettingsPermissions";
</style>