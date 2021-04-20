<template>
    <div class="basic-data" style="background-color: inherit;">
        <div v-if="tableMeta._is_owner" class="pull-right flex flex--center flex--automargin" :style="{height: '30px'}">
            <div class="basic_checks">
                <span>Settings:</span>
                <select class="form-control" v-model="fill_field">
                    <option v-for="tableHeader in settingsMeta['table_fields']._fields"
                            v-if="filterMasser(tableHeader)"
                            :value="tableHeader.field"
                    >{{ tableHeader.name }}</option>
                </select>
                <label class="switch_t">
                    <input type="checkbox" v-model="fill_status" @change="sendMassStatus">
                    <span class="toggler round"></span>
                </label>
            </div>

            <button v-show="activeTab === 'columns'"
                    class="btn btn-default btn-sm blue-gradient"
                    :style="$root.themeButtonStyle"
                    @click="importTooltips = true"
            >Tooltips Import</button>

            <info-sign-link :app_sett_key="'help_link_settings_basics'"></info-sign-link>
        </div>

        <div class="basic-menu">
            <button v-if="isOwnerOrCanEdit" class="btn btn-default" :class="{active: activeTab === 'general'}" @click="activeTab = 'general'">
                General
            </button>
            <button v-if="tableMeta._is_owner" class="btn btn-default" :class="{active: activeTab === 'inps'}" @click="activeTab = 'inps';redraw_table++;">
                Input
            </button>
            <button v-if="tableMeta._is_owner" class="btn btn-default" :class="{active: activeTab === 'columns'}" @click="activeTab = 'columns';redraw_table++;">
                Standard
            </button>
            <button class="btn btn-default" :class="{active: activeTab === 'customizable'}" @click="activeTab = 'customizable';redraw_table++;">
                Customizable
            </button>
        </div>

        <!--Tab with table settings-->
        <div class="basic-tab table-settings" v-show="activeTab === 'general'">
            <table-settings-module
                    :tb_meta="tableMeta"
                    :tb_theme="tableMeta._theme || {}"
                    :tb_views="tableMeta._views || []"
                    :sunc_settings="tableMeta._owner_settings || {}"
                    :tb_cur_settings="tableMeta._cur_settings || {}"
                    :filter_for_field="filter_for_field"
                    class="settings-module"
                    @prop-changed="updateTable"
            ></table-settings-module>
        </div>

        <!--Tab with inps settings-->
        <div class="basic-tab" v-show="activeTab === 'inps'" style="background-color: inherit;">
            <custom-table
                    :cell_component_name="'custom-cell-settings-display'"
                    :global-meta="tableMeta"
                    :table-meta="settingsMeta['table_fields']"
                    :settings-meta="settingsMeta"
                    :all-rows="tableMeta._fields"
                    :rows-count="tableMeta._fields.length"
                    :cell-height="cellHeight"
                    :max-cell-rows="maxCellRows"
                    :user="user"
                    :behavior="'settings_display'"
                    :available-columns="inpsAvailCols"
                    :forbidden-columns="forbiddenColumns"
                    :use_theme="true"
                    :redraw_table="redraw_table"
                    :is_visible="isVisible && activeTab === 'inps'"
                    @updated-row="updateRow"
                    @row-index-clicked="rowIndexClickedDisplay"
            ></custom-table>
            <for-settings-list-pop
                    v-if="settingsMeta['table_fields'] && editDisplayPopUpRow"
                    :global-meta="tableMeta"
                    :table-meta="settingsMeta['table_fields']"
                    :settings-meta="settingsMeta"
                    :table-row="editDisplayPopUpRow"
                    :user="user"
                    :cell-height="cellHeight"
                    :max-cell-rows="maxCellRows"
                    :forbidden-columns="forbiddenColumns"
                    :init_active="'columns'"
                    @popup-update="updateRow"
                    @popup-close="closePopUp"
                    @another-row="anotherRowPopup"
                    @select-another-row="selAnotherPop"
            ></for-settings-list-pop>
        </div>

        <!--Tab with columns settings-->
        <div class="basic-tab" v-show="activeTab === 'columns'" style="background-color: inherit;">
            <custom-table
                    :cell_component_name="'custom-cell-settings-display'"
                    :global-meta="tableMeta"
                    :table-meta="settingsMeta['table_fields']"
                    :settings-meta="settingsMeta"
                    :all-rows="tableMeta._fields"
                    :rows-count="tableMeta._fields.length"
                    :cell-height="cellHeight"
                    :max-cell-rows="maxCellRows"
                    :user="user"
                    :behavior="'settings_display'"
                    :available-columns="standardAvailCols"
                    :forbidden-columns="forbiddenColumns"
                    :use_theme="true"
                    :redraw_table="redraw_table"
                    :is_visible="isVisible && activeTab === 'columns'"
                    @updated-row="updateRow"
                    @row-index-clicked="rowIndexClickedDisplay2"
            ></custom-table>
            <for-settings-list-pop
                    v-if="settingsMeta['table_fields'] && editDisplayPopUpRow2"
                    :global-meta="tableMeta"
                    :table-meta="settingsMeta['table_fields']"
                    :settings-meta="settingsMeta"
                    :table-row="editDisplayPopUpRow2"
                    :user="user"
                    :cell-height="cellHeight"
                    :max-cell-rows="maxCellRows"
                    :forbidden-columns="forbiddenColumns"
                    :init_active="'columns'"
                    @popup-update="updateRow"
                    @popup-close="closePopUp"
                    @another-row="anotherRowPopup2"
                    @select-another-row="selAnotherPop2"
            ></for-settings-list-pop>
        </div>

        <!--Tab with customizable settings-->
        <div class="basic-tab" v-show="activeTab === 'customizable'" style="background-color: inherit;">
            <custom-table
                    :cell_component_name="'custom-cell-settings-display'"
                    :global-meta="tableMeta"
                    :table-meta="settingsMeta['table_fields']"
                    :settings-meta="settingsMeta"
                    :all-rows="tableMeta._fields"
                    :rows-count="tableMeta._fields.length"
                    :cell-height="cellHeight"
                    :max-cell-rows="maxCellRows"
                    :user="user"
                    :behavior="'settings_display'"
                    :available-columns="customizAvailCols"
                    :forbidden-columns="forbiddenColumns"
                    :use_theme="true"
                    :redraw_table="redraw_table"
                    :is_visible="isVisible && activeTab === 'customizable'"
                    @updated-row="updateRow"
                    @row-index-clicked="rowIndexClickedDisplay3"
            ></custom-table>
            <for-settings-list-pop
                    v-if="settingsMeta['table_fields'] && editDisplayPopUpRow3"
                    :global-meta="tableMeta"
                    :table-meta="settingsMeta['table_fields']"
                    :settings-meta="settingsMeta"
                    :table-row="editDisplayPopUpRow3"
                    :user="user"
                    :cell-height="cellHeight"
                    :max-cell-rows="maxCellRows"
                    :forbidden-columns="forbiddenColumns"
                    :init_active="'customizable'"
                    @popup-update="updateRow"
                    @popup-close="closePopUp"
                    @another-row="anotherRowPopup3"
                    @select-another-row="selAnotherPop3"
            ></for-settings-list-pop>
        </div>

        <!--Import tooltips for settings/basics-->
        <div v-show="importTooltips" class="modal-wrapper">
            <div class="modal">
                <div class="modal-dialog modal-sm">
                    <div class="modal-content">
                        <div class="modal-body">
                            <label>Paste tooltips below:</label>
                            <textarea v-model="parseTooltipOptions" class="form-control" rows="7"></textarea>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-success" @click="parseTooltip()">Parse Tooltips</button>
                            <button type="button" class="btn btn-default" @click="importTooltips = false">Cancel</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</template>

<script>
    import CustomTable from '../../../../CustomTable/CustomTable.vue';
    import InfoSignLink from "../../../../CustomTable/Specials/InfoSignLink.vue";
    import TableSettingsModule from "../../../../CommonBlocks/TableSettingsModule.vue";
    import ForSettingsPopUp from "../../../../CustomPopup/ForSettingsPopUp";

    import {eventBus} from '../../../../../app';
    import VerticalTable from "../../../../CustomTable/VerticalTable";
    import ForSettingsListPop from "../../../../CustomPopup/ForSettingsListPop";

    export default {
        name: "TableSettingsBasics",
        components: {
            ForSettingsListPop,
            VerticalTable,
            ForSettingsPopUp,
            TableSettingsModule,
            InfoSignLink,
            CustomTable,
        },
        mixins: [
        ],
        data: function () {
            return {
                fill_field: null,
                fill_status: false,

                customizable_field: 'name',
                customizable_idx: 0,
                columns_field: 'name',
                columns_idx: 0,

                redraw_table: 0,
                activeTab: this.init_tab || (this.tableMeta._is_owner ? 'columns' : 'customizable'),
                importTooltips: false,
                parseTooltipOptions: '',

                editDisplayPopUpRow: null,
                editDisplayPopUpRow2: null,
                editDisplayPopUpRow3: null,
                popUpRole: null,
                oldTbName: this.tableMeta.name,
            }
        },
        computed: {
            forbiddenColumns() {
                let forbid = [];
                if (this.tableMeta._current_right) {
                    _.each(this.tableMeta._current_right.forbidden_col_settings, (db_name) => {
                        forbid.push(db_name);
                    });
                }
                return forbid;
            },
            isOwnerOrCanEdit() {
                return this.tableMeta._is_owner
                    || (this.tableMeta._current_right && this.tableMeta._current_right.can_edit_tb);
            },
            inpsAvailCols() {
                let cols = this.tableMeta.user_id === this.$root.user.id ? this.$root.availableInpsColumns : this.$root.availableNotOwnerDisplayColumns;
                if (this.filter_for_field) {
                    cols = _.filter(_.clone(cols), (el) => {
                        return [
                            'input_type','f_formula','is_uniform_formula','ddl_id','ddl_add_option','ddl_auto_fill'
                        ].indexOf(el) === -1;
                    });
                }
                return cols;
            },
            standardAvailCols() {
                let cols = this.tableMeta.user_id === this.$root.user.id ? this.$root.availableSettingsColumns : this.$root.availableNotOwnerDisplayColumns;
                if (this.filter_for_field) {
                    let fltr = ['header_background','header_unit_ddl','unit_ddl_id','unit','is_search_autocomplete_display','is_unique_collection','f_required'];
                    if (this.filter_for_field !== 'Table') {
                        fltr.push('default_stats');
                    }
                    if (this.filter_for_field !== 'Boards') {
                        fltr.push('is_show_on_board');
                        fltr.push('is_image_on_board');
                    }
                    cols = _.filter(_.clone(cols), (el) => {
                        return fltr.indexOf(el) === -1;
                    });
                }
                return cols;
            },
            customizAvailCols() {
                let cols = this.$root.availableNotOwnerDisplayColumns;
                if (this.filter_for_field) {
                    cols = _.filter(_.clone(cols), (el) => {
                        return [
                            'filter_type','filter','is_floating','unit_display','min_width','max_width','width'
                        ].indexOf(el) === -1;
                    });
                }
                return cols;
            },
        },
        props:{
            tableMeta: Object,
            settingsMeta: Object,
            cellHeight: Number,
            maxCellRows: Number,
            user:  Object,
            table_id: Number,
            isVisible: Boolean,
            filter_for_field: String,
            init_tab: String,
        },
        watch: {
            table_id: function(val) {
                this.activeTab = this.tableMeta._is_owner ? 'columns' : 'customizable';
                this.oldTbName = this.tableMeta.name;
            },
            isVisible(val) {
                if (val) {
                    this.redraw_table++;
                }
            },
        },
        methods: {
            //edit header
            updateRow(tableRow) {
                this.$root.updateSettingsColumn(this.tableMeta, tableRow);
            },

            //tooltips
            parseTooltip() {
                if (this.parseTooltipOptions) {
                    this.$root.sm_msg_type = 1;
                    axios.post('/ajax/settings/import-tooltips', {
                        options: this.parseTooltipOptions.split(/\r\n|\r|\n/gi),
                        table_id: this.table_id,
                    }).then(({ data }) => {
                        this.tableMeta._fields = data;
                        this.importTooltips = false;
                    }).catch(errors => {
                        Swal('', getErrors(errors));
                    }).finally(() => {
                        this.$root.sm_msg_type = 0;
                    });
                }
            },

            //popup for headers
            rowIndexClickedDisplay(index) {
                this.editDisplayPopUpRow = this.tableMeta._fields[index];
                this.popUpRole = 'update';
            },
            rowIndexClickedDisplay2(index) {
                this.editDisplayPopUpRow2 = this.tableMeta._fields[index];
                this.popUpRole = 'update';
            },
            rowIndexClickedDisplay3(index) {
                this.editDisplayPopUpRow3 = this.tableMeta._fields[index];
                this.popUpRole = 'update';
            },
            closePopUp() {
                this.editDisplayPopUpRow = null;
                this.editDisplayPopUpRow2 = null;
                this.editDisplayPopUpRow3 = null;
            },

            //update table settings
            updateTable(prop_name) {
                this.$root.sm_msg_type = 1;

                let data = {
                    table_id: this.tableMeta.id,
                };
                Object.assign(data, this.tableMeta);
                Object.assign(data, this.tableMeta._theme);
                Object.assign(data, this.tableMeta._cur_settings);

                axios.put('/ajax/table', data).then(({ data }) => {
                    if (in_array(prop_name, [
                            'unit_conv_is_active','unit_conv_by_user','unit_conv_table_id','unit_conv_from_fld_id',
                            'unit_conv_to_fld_id','unit_conv_operator_fld_id','unit_conv_factor_fld_id',
                            'unit_conv_formula_fld_id','unit_conv_formula_reverse_fld_id',
                        ])) {
                        this.tableMeta.__unit_convers = data.__unit_convers || [];
                    }
                    if (prop_name === 'name') {
                        eventBus.$emit('event-reload-menu-tree');

                        $('head title').html(this.$root.app_name+': '+this.tableMeta.name);

                        let path = window.location.href.replace(this.oldTbName, this.tableMeta.name);
                        window.history.pushState(this.tableMeta.name, this.tableMeta.name, path);
                        this.oldTbName = this.tableMeta.name;
                    }
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },

            //change rows in popup
            anotherRowPopup(is_next) {
                let row_id = (this.editDisplayPopUpRow ? this.editDisplayPopUpRow.id : null);
                this.$root.anotherPopup(this.tableMeta._fields, row_id, is_next, this.rowIndexClickedDisplay);
            },
            anotherRowPopup2(is_next) {
                let row_id = (this.editDisplayPopUpRow2 ? this.editDisplayPopUpRow2.id : null);
                this.$root.anotherPopup(this.tableMeta._fields, row_id, is_next, this.rowIndexClickedDisplay2);
            },
            anotherRowPopup3(is_next) {
                let row_id = (this.editDisplayPopUpRow2 ? this.editDisplayPopUpRow2.id : null);
                this.$root.anotherPopup(this.tableMeta._fields, row_id, is_next, this.rowIndexClickedDisplay3);
            },
            selAnotherPop(row) {
                this.editDisplayPopUpRow = row;
            },
            selAnotherPop2(row) {
                this.editDisplayPopUpRow2 = row;
            },
            selAnotherPop3(row) {
                this.editDisplayPopUpRow3 = row;
            },

            //mass changer
            sendMassStatus() {
                this.$root.sm_msg_type = 1;
                axios.put('/ajax/settings/mass-data', {
                    table_id: this.tableMeta.id,
                    field: this.fill_field,
                    val: this.fill_status ? 1 : 0,
                }).then(({ data }) => {
                    _.each(this.tableMeta._fields, (fld) => {
                        fld[this.fill_field] = this.fill_status ? 1 : 0;
                    });
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
            filterMasser(hdr) {
                return hdr.f_type === 'Boolean' && (
                        (this.activeTab === 'inps' && this.$root.availableInpsColumns.indexOf(hdr.field) > -1)
                        ||
                        (this.activeTab === 'columns' && this.$root.availableSettingsColumns.indexOf(hdr.field) > -1)
                        ||
                        (this.activeTab === 'customizable' && this.$root.availableNotOwnerDisplayColumns.indexOf(hdr.field) > -1)
                    );
            },
        },
        mounted() {
            let availtabs = ['customizable'];
            if (this.tableMeta._is_owner) availtabs = availtabs.concat(['inps','columns']);
            if (this.isOwnerOrCanEdit) availtabs = availtabs.concat(['general']);
            if (availtabs.indexOf(this.activeTab) === -1) {
                this.activeTab = _.first(availtabs);
            }
            eventBus.$on('header-updated-cell', this.updateRow);
        },
        beforeDestroy() {
            eventBus.$off('header-updated-cell', this.updateRow);
        }
    }
</script>

<style lang="scss" scoped>
    @import "./../../../../CustomPopup/CustomEditPopUp";

    .basic-data {
        height: 100%;
        padding: 5px 5px 7px 5px;

        .basic_checks {
            color: white;
            padding: 0px 5px;
            white-space: nowrap;
            border-radius: 5px;

            select {
                display: inline-block;
                width: 150px;
                height: 28px;
                padding: 3px;
            }

            label {
                display: inline-block;
                position: relative;
                top: 4px;
            }
        }

        .basic-menu {
            button {
                background-color: #CCC;
                outline: 0;
            }
            .active {
                background-color: #FFF;
            }
        }

        .basic-tab {
            height: calc(100% - 30px);
            position: relative;
            top: -3px;
            border: 1px solid #CCC;
            border-radius: 4px;
        }

        .table-settings {
            padding: 15px;
            background-color: #FFF;
            overflow: auto;
        }

        .settings-module {
            column-count: 1;
            column-fill: auto;
            column-gap: 5%;
        }
        @media (min-width: 1366px) {
            .settings-module {
                column-count: 2;
            }
        }
    }
</style>