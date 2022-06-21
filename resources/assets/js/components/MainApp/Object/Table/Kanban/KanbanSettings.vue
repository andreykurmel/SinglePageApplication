<template>
    <div class="container-fluid full-height">
        <div class="row full-height permissions-tab">
            <!--MAIN-->
            <div class="col-xs-12" style="height: 40%">
                <!--LEFT SIDE-->
                <div class="col-xs-5 full-height" style="padding-right: 0;">
                    <div class="top-text top-text--height" :style="textSysStyle">
                        <span>List of Views</span>
                    </div>
                    <div class="permissions-panel no-padding">
                        <custom-table
                                :cell_component_name="'custom-cell-settings-display'"
                                :global-meta="tableMeta"
                                :table-meta="$root.settingsMeta['table_fields']"
                                :settings-meta="$root.settingsMeta"
                                :all-rows="ActiveKanbanFields"
                                :adding-row="addingRowFields"
                                :rows-count="ActiveKanbanFields.length"
                                :cell-height="1"
                                :max-cell-rows="0"
                                :is-full-width="true"
                                :selected-row="selectedCol"
                                :user="$root.user"
                                :behavior="'settings_kanban_add'"
                                :available-columns="availableColumns"
                                :headers-changer="{'name':'Field for board names'}"
                                :use_theme="true"
                                :no_width="true"
                                @added-row="activeField"
                                @delete-row="inactiveField"
                                @updated-row="updateName"
                                @row-index-clicked="rowIndexClickedColumn"
                        ></custom-table>
                    </div>
                </div>
                <!--ADDITIONAL-->
                <div class="col-xs-7 full-height">
                    <div class="top-text top-text--height" :style="textSysStyle">
                        <span>Additional Settings for </span>
                        <span>View "<span>{{ geCard() }}</span>"</span>
                    </div>
                    <div class="permissions-panel no-padding overflow-auto">
                        <div class="kanban_additionals" v-if="selectedKanbanSett" :style="textSysStyle">
                            <div class="form-group flex flex--center-v" style="white-space: nowrap;">
                                <div class="flex flex--center">
                                    <span class="indeterm_check__wrap">
                                        <span class="indeterm_check" @click="tableMeta.kanban_form_table = tableMeta.kanban_form_table ? 0 : 1;emitSave()">
                                            <i v-if="tableMeta.kanban_form_table" class="glyphicon glyphicon-ok group__icon"></i>
                                        </span>
                                    </span>
                                    <label>&nbsp;Form table display</label>
                                </div>

                                <div class="flex flex--center">
                                    <label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</label>
                                    <span class="indeterm_check__wrap">
                                        <span class="indeterm_check" @click="tableMeta.kanban_center_align = tableMeta.kanban_center_align ? 0 : 1;emitSave()">
                                            <i v-if="tableMeta.kanban_center_align" class="glyphicon glyphicon-ok group__icon"></i>
                                        </span>
                                    </span>
                                    <label>&nbsp;Center align</label>
                                </div>
                            </div>
                            <div class="form-group flex flex--center-v" style="white-space: nowrap;">
                                <div class="flex flex--center">
                                    <label>Card, header color:&nbsp;</label>
                                    <div class="color_wrap w-sm">
                                        <tablda-colopicker
                                            :init_color="tableMeta.kanban_header_color"
                                            :avail_null="true"
                                            @set-color="(clr) => {tableMeta.kanban_header_color = clr; emitSave()}"
                                        ></tablda-colopicker>
                                    </div>
                                </div>

                                <label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Width:&nbsp;</label>
                                <input :style="textSysStyle" class="form-control w-sm" v-model="tableMeta.kanban_card_width" @change="emitSave()"/>
                                <label>px</label>
                                <label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Height:&nbsp;</label>
                                <input :style="textSysStyle" class="form-control w-sm" v-model="tableMeta.kanban_card_height" @change="emitSave()"/>
                                <label>px</label>
                            </div>
                            <div class="form-group flex flex--center-v" style="white-space: nowrap;">
                                <label>Board sorting (left to right):&nbsp;</label>
                                <select :style="textSysStyle" class="form-control w-lg" v-model="tableMeta.kanban_sort_type" @change="emitSave()">
                                    <option value="asc">Sort A(0)->Z(9)</option>
                                    <option value="desc">Sort Z(9)->A(0)</option>
                                    <option value="custom">Custom</option>
                                </select>

                                <span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
                                <span class="indeterm_check__wrap">
                                <span class="indeterm_check" @click="tableMeta.kanban_hide_empty_tab = tableMeta.kanban_hide_empty_tab ? 0 : 1;emitSave()">
                                    <i v-if="tableMeta.kanban_hide_empty_tab" class="glyphicon glyphicon-ok group__icon"></i>
                                </span>
                            </span>
                                <label>&nbsp;Hide boards with no cards</label>
                            </div>
                            <div class="form-group flex flex--center-v" style="white-space: nowrap;">
                                <label>Image on Right:&nbsp;</label>
                                <select :style="textSysStyle" class="form-control w-md" v-model="tableMeta.kanban_picture_field" @change="emitSave()">
                                    <option></option>
                                    <option
                                        v-for="header in tableMeta._fields"
                                        v-if="header.f_type === 'Attachment'"
                                        :value="header.id"
                                    >{{ header.name }}</option>
                                </select>

                                <template v-if="tableMeta.kanban_picture_field">
                                    <label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Image width:&nbsp;</label>
                                    <input :style="textSysStyle" class="form-control w-sm" v-model="tableMeta.kanban_picture_width" @change="emitSave()"/>
                                    <label>%</label>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!--RIGHT SIDE-->
            <div class="col-xs-12" style="height: 60%">
                <div class="top-text top-text--height" :style="textSysStyle">
                    <span>Basic Settings for </span>
                    <span>View "<span>{{ geCard() }}</span>"</span>
                </div>
                <div class="permissions-panel no-padding">
                    <custom-table
                        v-if="selectedKanbanSett"
                        :cell_component_name="'custom-cell-kanban-sett'"
                        :global-meta="tableMeta"
                        :table-meta="$root.settingsMeta['table_kanban_settings']"
                        :all-rows="tableMeta._fields"
                        :rows-count="tableMeta._fields.length"
                        :cell-height="1"
                        :max-cell-rows="0"
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

    import CellStyleMixin from "../../../../_Mixins/CellStyleMixin";

    export default {
        name: "KanbanSettings",
        components: {
            TabldaColopicker,
            CustomTable,
        },
        mixins: [
            CellStyleMixin,
        ],
        data: function () {
            return {
                selectedCol: 0,
                availableColumns: ['name','kanban_field_name'],
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
                return this.ActiveKanbanFields[this.selectedCol]
                    ? this.ActiveKanbanFields[this.selectedCol]._kanban_setting
                    : null;
            },
        },
        watch: {
            table_id(val) {
                this.selectedCol = 0;
            }
        },
        methods: {
            geCard() {
                return this.ActiveKanbanFields[this.selectedCol]
                    ? (this.ActiveKanbanFields[this.selectedCol].kanban_field_name || this.ActiveKanbanFields[this.selectedCol].name)
                    : '';
            },
            rowIndexClickedColumn(index) {
                this.selectedCol = index;
            },
            updateName(tableRow) {
                this.$root.updateSettingsColumn(this.tableMeta, tableRow);
            },
            activeField(tableRow) {
                this.toggleField(tableRow, 1);
            },
            inactiveField(tableRow) {
                this.toggleField(tableRow, 0);
            },
            toggleField(tableRow, status) {
                let kanb_name = tableRow.kanban_field_name;
                this.$root.sm_msg_type = 1;
                axios.put('/ajax/settings/data', {
                    table_field_id: tableRow.id,
                    field: 'kanban_group',
                    val: status,
                }).then(({ data }) => {
                    let idx = _.findIndex(this.tableMeta._fields, {id: Number(tableRow.id)});
                    if (idx > -1 && data.fld) {
                        this.tableMeta._fields.splice(idx, 1, data.fld);
                        //work with Kanban Name.
                        if (kanb_name) {
                            let tRow = this.tableMeta._fields[idx];
                            tRow._changed_field = 'kanban_field_name';
                            tRow.kanban_field_name = kanb_name;
                            this.$root.updateSettingsColumn(this.tableMeta, tRow);
                        }
                    }
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
            kanbanSettCheck(field, val) {
                //see CustomCellKanbanSett.vue
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
                        setting: val.setting,
                        val: val.val,
                    });
                }
            },
            axiosPost(url, param) {
                this.$root.sm_msg_type = 1;
                axios.put(url, param).then(({ data }) => {
                    this.selectedKanbanSett.kanban_group_field_id = data.kanban_group_field_id;
                    this.selectedKanbanSett._fields_pivot = data._fields_pivot;
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
            emitSave() {
                let val = parseFloat(this.tableMeta.kanban_picture_width);
                val = (val <= 1 ? val*100 : val);
                val = Math.min(val, 100);
                val = Math.max(val, 0);
                this.tableMeta.kanban_picture_width = val;

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

    .overflow-auto {
        overflow: auto;
    }

    .kanban_additionals {
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
</style>