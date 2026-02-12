<template>
    <div class="full-height">
        <div class="full-height permissions-tab p5" :style="$root.themeMainBgStyle">

            <div class="permissions-panel full-height" :style="$root.themeMainBgStyle">

                <div class="permissions-menu-header">
                    <button class="btn btn-default btn-sm" :style="textSysStyle" :class="{active : activeTab === 'list'}" @click="changeTab('list')">
                        List
                    </button>
                    <button class="btn btn-default btn-sm" :style="textSysStyle" :class="{active : activeTab === 'general'}" @click="changeTab('general')">
                        General
                    </button>
                    <button class="btn btn-default btn-sm" :style="textSysStyle" :class="{active : activeTab === 'specific'}" @click="changeTab('specific')">
                        Field Specific
                    </button>

                    <div v-if="selectedKanbanSett" class="flex flex--center-v flex--end" style="position: absolute; top: 0; right: 0; height: 32px; width: 50%;">
                        <div class="flex flex--center-v ml15 mr0" v-show="activeTab !== 'list'">
                            <button class="btn btn-default btn-sm blue-gradient full-height"
                                    :style="$root.themeButtonStyle"
                                    @click="copyKanbSett()"
                            >Copy</button>
                            <select class="form-control full-height" v-model="knb_for_copy" style="width: 150px">
                                <option v-for="knb in ActiveKanbanFields"
                                        v-if="knb.id != selectedKanbanSett.id"
                                        :value="knb.id"
                                >{{ kbName(knb) }}</option>
                            </select>
                        </div>

                        <label class="flex flex--center ml15 mr0" style="margin: 0 0 0 5px;white-space: nowrap;" :style="textSysStyleSmart">
                            Loaded Settings for Kanban:&nbsp;
                            <select-block
                                :options="kbsettOpts()"
                                :sel_value="selectedKanbanSett.id"
                                :style="{ width:'150px', height:'32px', }"
                                @option-select="kbsettChange"
                            ></select-block>
                        </label>
                    </div>
                </div>
                <div class="permissions-menu-body" style="border: 1px solid #CCC;">

                    <div class="full-height permissions-panel no-padding" v-show="activeTab === 'list'" :style="$root.themeMainBgStyle">
                        <custom-table
                            :cell_component_name="'custom-cell-kanban-sett'"
                            :global-meta="tableMeta"
                            :table-meta="$root.settingsMeta['table_kanban_settings']"
                            :settings-meta="$root.settingsMeta"
                            :all-rows="ActiveKanbanFields"
                            :adding-row="addingRowFields"
                            :rows-count="ActiveKanbanFields.length"
                            :cell-height="1.5"
                            :max-cell-rows="0"
                            :is-full-width="true"
                            :selected-row="selectedCol"
                            :user="$root.user"
                            :behavior="'settings_kanban_add'"
                            :available-columns="['table_field_id','kanban_field_name','kanban_data_range','kanban_field_description','kanban_active']"
                            :use_theme="true"
                            @added-row="addKanban"
                            @delete-row="deleteKanban"
                            @updated-row="updateKanban"
                            @row-index-clicked="rowIndexClickedColumn"
                        ></custom-table>
                    </div>

                    <div class="full-height permissions-panel no-padding overflow-auto" v-show="activeTab === 'general'" :style="$root.themeMainBgStyle">
                        <div class="kanban_additionals" v-if="selectedKanbanSett" :style="textSysStyleSmart">
                            <div class="form-group flex flex--center-v" style="white-space: nowrap;">
                                <div class="flex flex--center">
                                    <span class="indeterm_check__wrap" style="color: #555">
                                        <span
                                            class="indeterm_check"
                                            @click="selectedKanbanSett.kanban_form_table = selectedKanbanSett.kanban_form_table ? 0 : 1;updKanb('kanban_form_table')"
                                        >
                                            <i v-if="selectedKanbanSett.kanban_form_table" class="glyphicon glyphicon-ok group__icon"></i>
                                        </span>
                                    </span>
                                    <label>&nbsp;Form table display</label>
                                </div>

                                <div class="flex flex--center">
                                    <label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</label>
                                    <span class="indeterm_check__wrap" style="color: #555">
                                        <span
                                            class="indeterm_check"
                                            @click="selectedKanbanSett.kanban_center_align = selectedKanbanSett.kanban_center_align ? 0 : 1;updKanb('kanban_center_align')"
                                        >
                                            <i v-if="selectedKanbanSett.kanban_center_align" class="glyphicon glyphicon-ok group__icon"></i>
                                        </span>
                                    </span>
                                    <label>&nbsp;Center alignment</label>
                                    <label>&nbsp;&nbsp;&nbsp;Spacing of rows in cards:&nbsp;</label>
                                    <input
                                        type="number"
                                        :style="textSysStyle"
                                        class="form-control w-sm"
                                        v-model="selectedKanbanSett.kanban_row_spacing"
                                        @change="updKanb('kanban_row_spacing')"
                                    />
                                    <label>px</label>
                                </div>
                            </div>
                            <div class="form-group flex flex--center-v" style="white-space: nowrap;">
                                <div class="flex flex--center">
                                    <label>Card, header color:&nbsp;</label>
                                    <div class="color_wrap w-sm">
                                        <tablda-colopicker
                                            :init_color="selectedKanbanSett.kanban_header_color"
                                            :avail_null="true"
                                            @set-color="(clr) => {selectedKanbanSett.kanban_header_color = clr; updKanb('kanban_header_color')}"
                                        ></tablda-colopicker>
                                    </div>
                                </div>

                                <label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Width:&nbsp;</label>
                                <input
                                    :style="textSysStyle"
                                    class="form-control w-sm"
                                    v-model="selectedKanbanSett.kanban_card_width"
                                    @change="updKanb('kanban_card_width')"
                                />
                                <label>px</label>
                                <label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Height:&nbsp;</label>
                                <input
                                    :style="textSysStyle"
                                    :disabled="!selectedKanbanSett.kanban_card_height"
                                    class="form-control w-sm"
                                    v-model="selectedKanbanSett.kanban_card_height"
                                    @change="updKanb('kanban_card_height')"
                                />
                                <label>px&nbsp;&nbsp;</label>
                                <span class="indeterm_check__wrap" style="color: #555">
                                    <span
                                        class="indeterm_check"
                                        @click="selectedKanbanSett.kanban_card_height = selectedKanbanSett.kanban_card_height ? null : 300;updKanb('kanban_card_height')"
                                    >
                                        <i v-if="!selectedKanbanSett.kanban_card_height" class="glyphicon glyphicon-ok group__icon"></i>
                                    </span>
                                </span>
                                <label>&nbsp;Auto</label>
                            </div>
                            <div class="form-group flex flex--center-v" style="white-space: nowrap;">
                                <label>Board sorting (left to right):&nbsp;</label>
                                <select
                                    :style="textSysStyle"
                                    class="form-control w-lg"
                                    v-model="selectedKanbanSett.kanban_sort_type"
                                    @change="updKanb('kanban_sort_type')"
                                >
                                    <option value="asc">Sort A(0)->Z(9)</option>
                                    <option value="desc">Sort Z(9)->A(0)</option>
                                    <option value="custom">Custom</option>
                                </select>

                                <span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
                                <span class="indeterm_check__wrap" style="color: #555">
                                    <span
                                        class="indeterm_check"
                                        @click="selectedKanbanSett.kanban_hide_empty_tab = selectedKanbanSett.kanban_hide_empty_tab ? 0 : 1;updKanb('kanban_hide_empty_tab')"
                                    >
                                        <i v-if="selectedKanbanSett.kanban_hide_empty_tab" class="glyphicon glyphicon-ok group__icon"></i>
                                    </span>
                                </span>
                                <label>&nbsp;Hide boards with no cards</label>
                            </div>
                            <div class="form-group flex flex--center-v" style="white-space: nowrap;">
                                <label>Image field:&nbsp;</label>
                                <select
                                    :style="textSysStyle"
                                    style="min-width: 104px;"
                                    class="form-control w-md"
                                    v-model="selectedKanbanSett.kanban_picture_field"
                                    @change="updKanb('kanban_picture_field')"
                                >
                                    <option></option>
                                    <option
                                        v-for="header in tableMeta._fields"
                                        v-if="header.f_type === 'Attachment'"
                                        :value="header.id"
                                    >{{ header.name }}</option>
                                </select>
                                <label>&nbsp;&nbsp;&nbsp;Position:&nbsp;</label>
                                <select
                                    :style="textSysStyle"
                                    class="form-control w-md"
                                    v-model="selectedKanbanSett.kanban_picture_position"
                                    @change="updKanb('kanban_picture_position')"
                                >
                                    <option value="left">Left</option>
                                    <option value="right">Right</option>
                                </select>
                                <template v-if="selectedKanbanSett.kanban_picture_field">
                                    <label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Width:&nbsp;</label>
                                    <input
                                        :style="textSysStyle"
                                        class="form-control w-sm"
                                        v-model="selectedKanbanSett.kanban_picture_width"
                                        @change="updKanb('kanban_picture_width')"
                                    />
                                    <label>%</label>
                                </template>
                            </div>

                            <div class="form-group flex flex--center-v" style="white-space: nowrap;">
                                <label>Select and add row(s) to show STATS at the card header:</label>
                            </div>

                            <div class="group-params-wrapper" v-if="selectedKanbanSett" :style="$root.themeMainBgStyle">
                                <custom-table
                                    :cell_component_name="'custom-cell-kanban-sett'"
                                    :global-meta="tableMeta"
                                    :table-meta="$root.settingsMeta['table_kanban_group_params']"
                                    :settings-meta="$root.settingsMeta"
                                    :all-rows="selectedKanbanSett._group_params"
                                    :adding-row="addingRowFields"
                                    :rows-count="selectedKanbanSett._group_params.length"
                                    :cell-height="1.5"
                                    :max-cell-rows="0"
                                    :is-full-width="true"
                                    :user="$root.user"
                                    :behavior="'settings_kanban_add'"
                                    :available-columns="['table_field_id','stat']"
                                    :use_theme="true"
                                    @added-row="addGroupParam"
                                    @updated-row="updateGroupParam"
                                    @delete-row="deleteGroupParam"
                                ></custom-table>
                            </div>
                        </div>
                    </div>

                    <div class="full-height permissions-panel no-padding" v-show="activeTab === 'specific'" :style="$root.themeMainBgStyle">
                        <custom-table
                            v-if="selectedKanbanSett"
                            :cell_component_name="'custom-cell-kanban-sett'"
                            :global-meta="tableMeta"
                            :table-meta="$root.settingsMeta['table_kanban_settings_2_table_fields']"
                            :all-rows="allFields"
                            :rows-count="allFields.length"
                            :cell-height="1"
                            :max-cell-rows="0"
                            :is-full-width="true"
                            :with_edit="withEdit"
                            :user="$root.user"
                            :behavior="'table_kanban_settings'"
                            :parent-row="selectedKanbanSett"
                            :available-columns="availPivotFields"
                            :redraw_table="specificRedraw"
                            :use_theme="true"
                            @check-row="kanbanSettCheck"
                        ></custom-table>
                    </div>

                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import {eventBus} from "../../../../../app";

    import CellStyleMixin from "../../../../_Mixins/CellStyleMixin";

    import CustomTable from "../../../../CustomTable/CustomTable";
    import TabldaColopicker from "../../../../CustomCell/InCell/TabldaColopicker";
    import SelectBlock from "../../../../CommonBlocks/SelectBlock";

    export default {
        name: "KanbanSettings",
        components: {
            SelectBlock,
            TabldaColopicker,
            CustomTable,
        },
        mixins: [
            CellStyleMixin,
        ],
        data: function () {
            return {
                knb_for_copy: null,
                withEdit: true,
                specificRedraw: 0,
                activeTab: 'list',
                selectedCol: 0,
                addingRowFields: {
                    active: true,
                    position: 'bottom'
                },
                availPivotFields: ['_name','is_header_show','is_header_value','table_show_name','table_show_value','cell_border',
                    'picture_style','picture_fit','is_start_table_popup','is_table_field_in_popup','is_hdr_lvl_one_row','width_of_table_popup'],
            }
        },
        props:{
            table_id: Number,
            tableMeta: Object,
        },
        computed: {
            ActiveKanbanFields() {
                return this.tableMeta._kanban_settings;
            },
            selectedKanbanSett() {
                return this.ActiveKanbanFields[this.selectedCol];
            },
            allFields() {
                return _.filter(this.tableMeta._fields, (fld) => {
                    return this.$root.systemFieldsNoId.indexOf(fld.field) === -1;
                });
            },
        },
        watch: {
            table_id(val) {
                this.setSelCol(0);
            }
        },
        methods: {
            changeTab(key) {
                this.activeTab = key;
                this.$emit('set-sub-tab', key);
            },
            kbName(knb) {
                return knb.kanban_field_name || _.find(this.tableMeta._fields, {id: Number(knb.table_field_id)}).name;
            },
            copyKanbSett() {
                if (this.knb_for_copy && this.selectedKanbanSett) {
                    this.$root.sm_msg_type = 2;
                    axios.post('/ajax/settings/kanban/copy', {
                        from_kanban_id: this.knb_for_copy,
                        to_kanban_id: this.selectedKanbanSett.id,
                        field_pivot: this.activeTab === 'specific' ? 1 : 0,
                    }).then(({ data }) => {
                        let selCol = this.selectedCol;
                        this.ActiveKanbanFields[selCol] = data;
                        eventBus.$emit('redraw-kanban', this.selectedKanbanSett.id, 'with_rows');
                        //redraw
                        this.selectedCol = -1;
                        this.$nextTick(() => {
                            this.selectedCol = selCol;
                        });
                    }).catch(errors => {
                        Swal('Info', getErrors(errors));
                    }).finally(() => {
                        this.$root.sm_msg_type = 0;
                    });
                }
            },
            kbsettOpts() {
                return _.map(this.ActiveKanbanFields, (knb) => {
                    return {
                        val: knb.id,
                        show: knb.kanban_field_name
                            || _.find(this.tableMeta._fields, {id: Number(this.selectedKanbanSett.table_field_id)}).name,
                    };
                });
            },
            kbsettChange(opt) {
                this.setSelCol( _.findIndex(this.ActiveKanbanFields, {id: Number(opt.val)}) );
            },
            rowIndexClickedColumn(index) {
                this.setSelCol(index);
            },
            setSelCol(value) {
                this.selectedCol = -1;
                this.$nextTick(() => {
                    this.selectedCol = value;
                });
            },
            //Kanban
            addKanban(tableRow) {
                let fields = _.cloneDeep(tableRow);//copy object
                this.$root.deleteSystemFields(fields);

                $.LoadingOverlay('show');
                axios.post('/ajax/addon-kanban', {
                    table_id: this.tableMeta.id,
                    fields: fields
                }).then(({ data }) => {
                    this.tableMeta._kanban_settings = data;
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
            updateKanban(tableRow) {
                let fields = _.cloneDeep(tableRow);//copy object
                this.$root.deleteSystemFields(fields);

                $.LoadingOverlay('show');
                axios.put('/ajax/addon-kanban', {
                    model_id: tableRow.id,
                    fields: fields
                }).then(({ data }) => {
                    this.tableMeta._kanban_settings = data;
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
            deleteKanban(tableRow) {
                $.LoadingOverlay('show');
                axios.delete('/ajax/addon-kanban', {
                    params: {
                        model_id: tableRow.id
                    }
                }).then(({ data }) => {
                    this.tableMeta._kanban_settings = data;
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
            //Kanban
            addGroupParam(groupParam) {
                let fields = _.cloneDeep(groupParam);//copy object
                this.$root.deleteSystemFields(fields);

                $.LoadingOverlay('show');
                axios.post('/ajax/settings/kanban/group-param', {
                    kanban_id: this.selectedKanbanSett.id,
                    fields: fields
                }).then(({ data }) => {
                    this.selectedKanbanSett._group_params = data;
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
            updateGroupParam(groupParam) {
                let fields = _.cloneDeep(groupParam);//copy object
                this.$root.deleteSystemFields(fields);

                $.LoadingOverlay('show');
                axios.put('/ajax/settings/kanban/group-param', {
                    kanban_id: this.selectedKanbanSett.id,
                    param_id: groupParam.id,
                    fields: fields
                }).then(({ data }) => {
                    this.selectedKanbanSett._group_params = data;
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
            deleteGroupParam(groupParam) {
                $.LoadingOverlay('show');
                axios.delete('/ajax/settings/kanban/group-param', {
                    params: {
                        kanban_id: this.selectedKanbanSett.id,
                        param_id: groupParam.id
                    }
                }).then(({ data }) => {
                    this.selectedKanbanSett._group_params = data;
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
            //
            updKanb(field) {
                let wiwi = parseFloat(this.selectedKanbanSett.kanban_picture_width);
                wiwi = (wiwi <= 1 ? wiwi*100 : wiwi);
                wiwi = Math.min(wiwi, 100);
                wiwi = Math.max(wiwi, 0);
                this.selectedKanbanSett.kanban_picture_width = wiwi;

                this.axiosPut('/ajax/settings/kanban', {
                    table_id: this.tableMeta.id,
                    kanban_id: this.selectedKanbanSett.id,
                    field: field,
                    val: this.selectedKanbanSett[field],
                }, field);
            },
            kanbanSettCheck(field, val) {
                //see CustomCellKanbanSett.vue
                this.axiosPut('/ajax/settings/kanban-column', {
                    table_id: this.tableMeta.id,
                    kanban_id: this.selectedKanbanSett.id,
                    field_id: field,
                    setting: val.setting,
                    val: val.val,
                });
            },
            axiosPut(url, param, field) {
                this.$root.sm_msg_type = 1;
                this.withEdit = false;
                axios.put(url, param).then(({ data }) => {
                    this.withEdit = true;
                    this.selectedKanbanSett.kanban_group_field_id = data.kanban_group_field_id;
                    this.selectedKanbanSett._fields_pivot = data._fields_pivot;
                    this.specificRedraw++;
                    let $with_rows = ['kanban_sort_type'].indexOf(field) > -1;
                    eventBus.$emit('redraw-kanban', this.selectedKanbanSett.id, $with_rows);
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
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

    .overflow-auto {
        overflow: auto;
    }

    .kanban_additionals {
        padding: 15px;
        max-width: 725px;

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

        .w-sm {
            width: 60px;
        }
        .w-md {
            width: 90px;
        }
        .w-lg {
            width: 150px;
        }
    }
    .group-params-wrapper {
        height: 200px;
        overflow: auto;
    }
</style>