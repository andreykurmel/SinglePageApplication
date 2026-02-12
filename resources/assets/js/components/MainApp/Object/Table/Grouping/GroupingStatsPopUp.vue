<template>
    <div v-if="groupingRow">
        <div class="popup-wrapper" @click.self="hide()"></div>

        <div class="popup" :style="getPopupStyle()">
            <div class="flex flex--col">
                <div class="popup-header">
                    <div class="drag-bkg" draggable="true" @dragstart="dragPopSt()" @drag="dragPopup()"></div>
                    <div class="flex">
                        <div class="flex__elem-remain">STATS</div>
                        <div class="" style="position: relative">
                            <span class="glyphicon glyphicon-remove pull-right header-btn" @click="hide()"></span>
                        </div>
                    </div>
                </div>
                <div class="popup-content flex__elem-remain">
                    <div class="flex__elem__inner popup-main">

                        <div class="flex flex--col">
                            <div class="full-frame tb_wrap flex flex--col">

                                <div class="rel-header">
                                    <button class="btn btn-default btn-sm"
                                            :style="textSysStyle"
                                            :class="{active : activeTab === 'list'}"
                                            @click="changeTabs('list', activeSubLevel)"
                                    >
                                        List
                                    </button>
                                    <button class="btn btn-default btn-sm"
                                            :style="textSysStyle"
                                            :class="{active : activeTab === 'levels'}"
                                            @click="changeTabs('levels', activeSubLevel || (groupingRow._settings && groupingRow._settings.length ? groupingRow._settings[0].id : null))"
                                    >
                                        Levels
                                    </button>
                                </div>
                                <div class="full-height bg-white" style="border-top: 1px solid #ccc; z-index: 10;">
                                    <div class="full-height" v-show="activeTab === 'list'">
                                        <custom-table
                                            v-if="tableMeta"
                                            :cell_component_name="'custom-cell-addon'"
                                            :global-meta="tableMeta"
                                            :table-meta="$root.settingsMeta['table_grouping_stats']"
                                            :settings-meta="$root.settingsMeta"
                                            :all-rows="groupingRow._global_stats"
                                            :adding-row="{ active: true, position: 'bottom' }"
                                            :rows-count="groupingRow._global_stats.length"
                                            :parent-row="groupingRow"
                                            :cell-height="1"
                                            :max-cell-rows="0"
                                            :is-full-width="true"
                                            :with_edit="canPermisEdit()"
                                            :user="$root.user"
                                            :behavior="'settings_grouping_stat'"
                                            :use_theme="true"
                                            @added-row="(row) => addGroupStat(row, 'global')"
                                            @updated-row="(row) => updateGroupStat(row, 'global')"
                                            @delete-row="(row) => deleteGroupStat(row, 'global')"
                                        ></custom-table>
                                    </div>
                                    <div class="full-height flex flex--col" v-show="activeTab === 'levels'">
                                        <div class="rel-header">
                                            <button v-for="(lvl, idx) in groupingRow._settings"
                                                    class="btn btn-default btn-sm"
                                                    :style="textSysStyle"
                                                    :class="{active: activeSubLevel === lvl.id, ml5: idx > 0}"
                                                    @click="changeTabs('levels', lvl.id)"
                                            >
                                                {{ fldName(lvl.field_id) }}
                                            </button>
                                        </div>
                                        <div class="full-height bg-white" style="border-top: 1px solid #ccc; z-index: 15;">
                                            <div class="full-height" v-show="activeTab === 'levels'">
                                                <custom-table
                                                    v-if="tableMeta && selectedGroupField && selectedGroupField._field_stats"
                                                    :cell_component_name="'custom-cell-addon'"
                                                    :global-meta="tableMeta"
                                                    :table-meta="$root.settingsMeta['table_grouping_field_stats']"
                                                    :settings-meta="$root.settingsMeta"
                                                    :all-rows="selectedGroupField._field_stats"
                                                    :adding-row="{ active: true, position: 'bottom' }"
                                                    :rows-count="selectedGroupField._field_stats.length"
                                                    :parent-row="groupingRow"
                                                    :cell-height="1"
                                                    :max-cell-rows="0"
                                                    :is-full-width="true"
                                                    :with_edit="canPermisEdit()"
                                                    :user="$root.user"
                                                    :behavior="'settings_grouping_stat'"
                                                    :use_theme="true"
                                                    @added-row="(row) => addGroupStat(row, 'field')"
                                                    @updated-row="(row) => updateGroupStat(row, 'field')"
                                                    @delete-row="(row) => deleteGroupStat(row, 'field')"
                                                ></custom-table>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</template>

<script>
    import PopupAnimationMixin from './../../../../_Mixins/PopupAnimationMixin';
    import CellStyleMixin from "../../../../_Mixins/CellStyleMixin";

    import CustomTable from "../../../../CustomTable/CustomTable.vue";

    export default {
        name: "GroupingStatsPopUp",
        mixins: [
            PopupAnimationMixin,
            CellStyleMixin,
        ],
        components: {
            CustomTable,
        },
        data: function () {
            return {
                activeTab: 'list',
                activeSubLevel: null,
                //PopupAnimationMixin
                getPopupHeight: '400px',
                getPopupWidth: 600,
                idx: 0,
            };
        },
        computed: {
            selectedGroupField() {
                return _.find(this.groupingRow._settings, {id: Number(this.activeSubLevel)});
            },
        },
        props: {
            tableMeta: Object,
            groupingRow: Object,
            selectedGroupFieldId: Number,
        },
        methods: {
            canPermisEdit() {
                return this.$root.addonCanPermisEdit(this.tableMeta, this.groupingRow, '_group_rights');
            },
            fldName(id) {
                return (_.find(this.tableMeta._fields, {id: Number(id)}) || {}).name;
            },
            changeTabs(main, second) {
                this.activeSubLevel = null;
                this.$nextTick(() => {
                    this.activeTab = main;
                    this.activeSubLevel = second;
                });
            },
            //GroupStat
            addGroupStat(tableRow, type) {
                let fields = _.cloneDeep(tableRow);//copy object
                this.$root.deleteSystemFields(fields);

                $.LoadingOverlay('show');
                axios.post('/ajax/addon-grouping/stat', {
                    parent_id: type === 'field' ? this.selectedGroupField.id : this.groupingRow.id,
                    fields: fields,
                    type: type,
                }).then(({ data }) => {
                    this.tableMeta._groupings = data;
                    this.updateStatTable();
                    this.$emit('should-redraw');
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
            updateGroupStat(tableRow, type) {
                let fields = _.cloneDeep(tableRow);//copy object
                this.$root.deleteSystemFields(fields);

                $.LoadingOverlay('show');
                axios.put('/ajax/addon-grouping/stat', {
                    stat_id: tableRow.id,
                    fields: fields,
                    type: type,
                }).then(({ data }) => {
                    this.tableMeta._groupings = data;
                    this.updateStatTable();
                    this.$emit('should-redraw');
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
            deleteGroupStat(tableRow, type) {
                $.LoadingOverlay('show');
                axios.delete('/ajax/addon-grouping/stat', {
                    params: {
                        stat_id: tableRow.id,
                        type: type,
                    }
                }).then(({ data }) => {
                    this.tableMeta._groupings = data;
                    this.updateStatTable();
                    this.$emit('should-redraw');
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
            updateStatTable() {
                this.$forceUpdate();
                if (this.selectedGroupField) {
                    this.$nextTick(() => {
                        let fieldRow = _.find(this.groupingRow._settings, {id: Number(this.selectedGroupField.id)});
                        this.selectedGroupField._field_stats = fieldRow._field_stats;
                    });
                }
            },
            hide() {
                this.$emit('popup-close');
            },
        },
        mounted() {
            this.runAnimation();
            this.$nextTick(() => {
                this.changeTabs(
                    this.selectedGroupFieldId ? 'levels' : 'list',
                    this.selectedGroupFieldId
                );
            });
        },
    }
</script>

<style lang="scss" scoped>
    @import "./../../../../CustomPopup/CustomEditPopUp";
    @import "./../../../../CustomTable/Table";

    .popup {
        font-size: initial;
        cursor: auto;

        .popup-content {
            .popup-main {
                padding: 5px;

                label {
                    margin: 0;
                }

                .tb_wrap {
                    border: 1px solid #CCC;
                }

                .popup-buttons {
                    height: 90px;

                    button {
                        margin-top: 0;
                    }
                }
            }
        }
    }
    .rel-header {
        position: relative;
        top: 3px;
        left: 3px;
        width: 90%;
    }
</style>