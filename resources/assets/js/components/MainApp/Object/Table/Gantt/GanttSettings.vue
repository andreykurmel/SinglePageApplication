<template>
    <div class="container-fluid full-height">
        <div class="row full-height permissions-tab">
            <!--LEFT SIDE-->
            <div class="col-xs-12 full-height">

                <div class="top-text top-text--height">
                    <span>Select a Columns for Gantt</span>
                </div>
                <div class="permissions-panel no-padding" style="height: calc(70% - 35px);">
                    <custom-table
                            :cell_component_name="'custom-cell-settings-display'"
                            :global-meta="tableMeta"
                            :table-meta="$root.settingsMeta['table_fields']"
                            :settings-meta="$root.settingsMeta"
                            :all-rows="tableMeta._fields"
                            :rows-count="tableMeta._fields.length"
                            :cell-height="$root.cellHeight"
                            :user="$root.user"
                            :behavior="'settings_display'"
                            :available-columns="$root.availableGanttColumns"
                            @updated-row="updateRow"
                            @row-index-clicked="rowIndexClicked"
                    ></custom-table>
                    <for-settings-pop-up
                            v-if="$root.settingsMeta['table_fields'] && editDisplayPopUpRow"
                            :global-meta="tableMeta"
                            :table-meta="$root.settingsMeta['table_fields']"
                            :settings-meta="$root.settingsMeta"
                            :table-row="editDisplayPopUpRow"
                            :ext-avail-tabs="['gantt_tab']"
                            :user="$root.user"
                            :cell-height="$root.cellHeight"
                            @popup-update="updateRow"
                            @popup-close="closePopUp"
                            @another-row="anotherRowPopup"
                    ></for-settings-pop-up>
                </div>
                <div class="top-text top-text--height">
                    <span>Additional</span>
                </div>
                <div class="permissions-panel no-padding" style="height: calc(30% - 30px);">
                    <div class="additionals_sett flex flex--center-v">
                        <div class="form-group flex flex--center-v">
                            <label>Date info for top / horizontal axis:&nbsp;</label>
                            <select class="form-control" v-model="tableMeta.gantt_info_type" @change="emitSave()" style="width: 100px;">
                                <option :value="null">Auto</option>
                                <option value="year">Year</option>
                                <option value="month">Month</option>
                                <option value="week">Week</option>
                                <option value="day">Day</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Navigation:&nbsp;</label>
                            <span class="indeterm_check__wrap">
                                <span class="indeterm_check" @click="tableMeta.gantt_navigation = tableMeta.gantt_navigation ? 0 : 1;emitSave()">
                                    <i v-if="tableMeta.gantt_navigation" class="glyphicon glyphicon-ok group__icon"></i>
                                </span>
                            </span>
                        </div>
                        <div class="form-group">
                            <label>Show Item Name:&nbsp;</label>
                            <span class="indeterm_check__wrap">
                                <span class="indeterm_check" @click="tableMeta.gantt_show_names = tableMeta.gantt_show_names ? 0 : 1;emitSave()">
                                    <i v-if="tableMeta.gantt_show_names" class="glyphicon glyphicon-ok group__icon"></i>
                                </span>
                            </span>
                        </div>
                        <div v-if="presentGrouping" class="form-group">
                            <label>Highlight on Hover:&nbsp;</label>
                            <span class="indeterm_check__wrap">
                                <span class="indeterm_check" @click="tableMeta.gantt_highlight = tableMeta.gantt_highlight ? 0 : 1;emitSave()">
                                    <i v-if="tableMeta.gantt_highlight" class="glyphicon glyphicon-ok group__icon"></i>
                                </span>
                            </span>
                        </div>
                        <div class="form-group flex flex--center-v">
                            <label>Weekday format:&nbsp;</label>
                            <select class="form-control" v-model="tableMeta.gantt_day_format" @change="emitSave()" style="width: 175px;">
                                <option :value="null">M</option>
                                <option value="part">Mon</option>
                                <option value="full">Monday</option>
                                <option value="extend">Monday, {{ today }}</option>
                            </select>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</template>

<script>
    import CustomTable from "../../../../CustomTable/CustomTable";
    import ForSettingsPopUp from "../../../../CustomPopup/ForSettingsPopUp";

    export default {
        name: "GanttSettings",
        components: {
            ForSettingsPopUp,
            CustomTable,
        },
        data: function () {
            return {
                editDisplayPopUpRow: null,
            }
        },
        props:{
            tableMeta: Object,
        },
        computed: {
            presentGrouping() {
                return _.find(this.tableMeta._fields, (fld) => {
                    return fld.is_gantt_main_group || fld.is_gantt_parent_group;
                });
            },
            today() {
                return moment().format('M/D/Y');
            },
        },
        watch: {
        },
        methods: {
            updateRow(tableRow) {
                this.$root.updateSettingsColumn(this.tableMeta, tableRow);
            },
            emitSave() {
                this.$emit('save-backend');
            },

            //popup for headers
            rowIndexClicked(index) {
                this.editDisplayPopUpRow = this.tableMeta._fields[index];
            },
            closePopUp() {
                this.editDisplayPopUpRow = null;
            },
            anotherRowPopup(is_next) {
                let row_id = (this.editDisplayPopUpRow ? this.editDisplayPopUpRow.id : null);
                this.$root.anotherPopup(this.tableMeta._fields, row_id, is_next, this.rowIndexClicked);
            },
        },
        mounted() {
        },
        beforeDestroy() {
        }
    }
</script>

<style lang="scss" scoped>
    @import "./../SettingsModule/TabSettingsPermissions";

    .additionals_sett {
        padding: 15px;
        width: 400px;
        white-space: nowrap;

        label {
            margin: 0;
        }
        .form-group {
            margin-right: 30px;
        }
    }
</style>