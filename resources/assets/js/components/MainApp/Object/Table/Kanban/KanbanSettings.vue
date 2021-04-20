<template>
    <div class="container-fluid full-height">
        <div class="row full-height permissions-tab">
            <!--LEFT SIDE-->
            <div class="col-xs-4 full-height" :style="{paddingRight: 0}">

                <div class="top-text top-text--height">
                    <span>Select a Grouping Column</span>
                </div>
                <div class="permissions-panel no-padding" style="height: calc(50% - 35px);">
                    <custom-table
                            :cell_component_name="'custom-cell-settings-display'"
                            :global-meta="tableMeta"
                            :table-meta="$root.settingsMeta['table_fields']"
                            :settings-meta="$root.settingsMeta"
                            :all-rows="ActiveKanbanFields"
                            :adding-row="addingRowFields"
                            :rows-count="ActiveKanbanFields.length"
                            :cell-height="$root.cellHeight"
                            :max-cell-rows="$root.maxCellRows"
                            :is-full-width="true"
                            :selected-row="selectedCol"
                            :user="$root.user"
                            :behavior="'settings_kanban_add'"
                            :available-columns="availableColumns"
                            :use_theme="true"
                            :no_width="true"
                            @added-row="activeField"
                            @delete-row="inactiveField"
                            @row-index-clicked="rowIndexClickedColumn"
                    ></custom-table>
                </div>
                <div class="top-text top-text--height">
                    <span>Additional</span>
                </div>
                <div class="permissions-panel no-padding" style="height: calc(50% - 30px);">
                    <div class="additionals_sett">
                        <div class="form-group flex flex--space">
                            <div class="flex flex--center">
                                <span class="indeterm_check__wrap">
                                    <span class="indeterm_check" @click="tableMeta.kanban_form_table = tableMeta.kanban_form_table ? 0 : 1;emitSave()">
                                        <i v-if="tableMeta.kanban_form_table" class="glyphicon glyphicon-ok group__icon"></i>
                                    </span>
                                </span>
                                <label>&nbsp;Form Table Display</label>
                            </div>
                            <div class="flex flex--center">
                                <label>Header Color:&nbsp;</label>
                                <div class="color_wrap">
                                    <tablda-colopicker
                                            :init_color="tableMeta.kanban_header_color"
                                            :saved_colors="$root.color_palette"
                                            :avail_null="true"
                                            @set-color="(clr) => {tableMeta.kanban_header_color = clr; emitSave()}"
                                    ></tablda-colopicker>
                                </div>
                            </div>
                        </div>
                        <div class="form-group flex flex--center-v" style="white-space: nowrap;">
                            <label>Card Width:&nbsp;</label>
                            <input class="form-control" v-model="tableMeta.kanban_card_width" @change="emitSave()"/>
                            <label>px</label>
                            <label>&nbsp;&nbsp;&nbsp;Height:&nbsp;</label>
                            <input class="form-control" v-model="tableMeta.kanban_card_height" @change="emitSave()"/>
                            <label>px</label>
                        </div>
                        <div class="form-group flex flex--center-v" style="white-space: nowrap;">
                            <label>Group Sorting:&nbsp;</label>
                            <select class="form-control" v-model="tableMeta.kanban_sort_type" @change="emitSave()">
                                <option value="asc">Sort A(0)->Z(9)</option>
                                <option value="desc">Sort Z(9)->A(0)</option>
                                <option value="custom">Custom</option>
                            </select>
                        </div>
                        <div class="form-group flex flex--center-v" style="white-space: nowrap;">
                            <span class="indeterm_check__wrap">
                                    <span class="indeterm_check" @click="tableMeta.kanban_hide_empty_tab = tableMeta.kanban_hide_empty_tab ? 0 : 1;emitSave()">
                                        <i v-if="tableMeta.kanban_hide_empty_tab" class="glyphicon glyphicon-ok group__icon"></i>
                                    </span>
                                </span>
                            <label>&nbsp;Hide panel with no card</label>
                            <label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Picture display style:&nbsp;</label>
                            <select class="form-control" v-model="tableMeta.kanban_picture_style" @change="emitSave()">
                                <option value="scroll">Scroll</option>
                                <option value="slide">Slide</option>
                            </select>
                        </div>
                    </div>
                </div>

            </div>
            <!--RIGHT SIDE-->
            <div class="col-xs-8 full-height">
                <div class="top-text top-text--height">
                    <span>Card "<span>{{ (selectedCol > -1 && ActiveKanbanFields[selectedCol] ? ActiveKanbanFields[selectedCol].name : '') }}</span>"</span>
                </div>
                <div class="permissions-panel no-padding">
                    <custom-table
                            :cell_component_name="'custom-cell-kanban-sett'"
                            :global-meta="tableMeta"
                            :table-meta="$root.settingsMeta['table_kanban_settings']"
                            :all-rows="selectedCol > -1 ? tableMeta._fields : null"
                            :rows-count="selectedCol > -1 ? tableMeta._fields.length : 0"
                            :cell-height="$root.cellHeight"
                            :max-cell-rows="$root.maxCellRows"
                            :is-full-width="true"
                            :user="$root.user"
                            :behavior="'table_kanban_settings'"
                            :parent-row="selectedKanbanSett"
                            :forbidden-columns="$root.systemFields"
                            :use_theme="true"
                            @check-row="kanbanSettCheck"
                    ></custom-table>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import CustomTable from "../../../../CustomTable/CustomTable";
    import TabldaColopicker from "../../../../CustomCell/InCell/TabldaColopicker";

    export default {
        name: "KanbanSettings",
        components: {
            TabldaColopicker,
            CustomTable,
        },
        data: function () {
            return {
                selectedCol: -1,
                availableColumns: ['name'],
                addingRowFields: {
                    active: true,
                    position: 'bottom'
                },
            }
        },
        props:{
            table_id: Number,
            tableMeta: Object,
        },
        computed: {
            ActiveKanbanFields() {
                return _.filter(this.tableMeta._fields, {kanban_group: 1});
            },
            selectedKanbanSett() {
                return this.selectedCol > -1 && this.ActiveKanbanFields[this.selectedCol]
                    ? this.ActiveKanbanFields[this.selectedCol]._kanban_setting
                    : null;
            },
        },
        watch: {
            table_id(val) {
                this.selectedCol = -1;
            }
        },
        methods: {
            rowIndexClickedColumn(index) {
                this.selectedCol = index;
            },
            activeField(tableRow) {
                let col_id = tableRow.id;
                this.toggleField(col_id, 1);
            },
            inactiveField(tableRow) {
                let col_id = tableRow.id;
                this.toggleField(col_id, 0);
            },
            toggleField(col_id, status) {
                this.$root.sm_msg_type = 1;
                axios.put('/ajax/settings/data', {
                    table_field_id: col_id,
                    field: 'kanban_group',
                    val: status,
                }).then(({ data }) => {
                    let idx = _.findIndex(this.tableMeta._fields, {id: Number(col_id)});
                    if (idx > -1 && data.fld) {
                        this.tableMeta._fields.splice(idx, 1, data.fld);
                    }
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
            kanbanSettCheck(field, val) {
                if (field === 'is_header') {
                    this.axiosPost('/ajax/settings/kanban', {
                        table_id: this.tableMeta.id,
                        kanban_id: this.selectedKanbanSett.id,
                        field: 'kanban_group_field_id',
                        val: val,
                    });
                } else {
                    this.axiosPost('/ajax/settings/kanban-column', {
                        table_id: this.tableMeta.id,
                        kanban_id: this.selectedKanbanSett.id,
                        field_id: field,
                        val: val,
                    });
                }
            },
            axiosPost(url, param) {
                this.$root.sm_msg_type = 1;
                axios.put(url, param).then(({ data }) => {
                    this.selectedKanbanSett.kanban_group_field_id = data.kanban_group_field_id;
                    this.selectedKanbanSett._columns = data._columns;
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
            emitSave() {
                this.$emit('save-backend');
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

        label {
            margin: 0;
        }
        .color_wrap {
            width: 150px;
            height: 36px;
            position: relative;
            border: 1px solid #ccc;
            border-radius: 3px;
        }
    }
</style>