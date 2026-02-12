<template>
    <div class="basic-data" style="background-color: inherit;">
        <div v-if="tableMeta._is_owner" class="pull-right flex flex--center flex--automargin" :style="{height: '30px'}">
            <div v-show="activeTab !== 'general'" class="basic_checks" :style="$root.themeMainTxtColor">
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

            <info-sign-link v-show="activeTab === 'general'" :app_sett_key="'help_link_settings_basics'" :txt="'for Settings/Basics/General'"></info-sign-link>
            <info-sign-link v-show="activeTab === 'inps'" :app_sett_key="'help_link_settings_basics_inps'" :txt="'for Settings/Basics/Inputs'"></info-sign-link>
            <info-sign-link v-show="activeTab === 'columns'" :app_sett_key="'help_link_settings_basics_columns'" :txt="'for Settings/Basics/Columns'"></info-sign-link>
            <info-sign-link v-show="activeTab === 'customizable'" :app_sett_key="'help_link_settings_basics_customizable'" :txt="'for Settings/Basics/Customizable'"></info-sign-link>
            <info-sign-link v-show="activeTab === 'bas_popup'" :app_sett_key="'help_link_settings_basics_bas_popup'" :txt="'for Settings/Basics/Popup'"></info-sign-link>
            <info-sign-link v-show="activeTab === 'others'" :app_sett_key="'help_link_settings_basics_others'" :txt="'for Settings/Basics/Others'"></info-sign-link>
        </div>

        <div class="basic-menu flex">
            <button v-if="isOwnerOrCanEdit('tab-settings')" class="btn btn-default mr5" :class="{active: activeTab === 'general'}" :style="btnStyle" @click="setTab('general');">
                General
            </button>
            <button v-if="tableMeta._is_owner" class="btn btn-default mr5" :class="{active: activeTab === 'inps'}" :style="btnStyle" @click="setTab('inps');">
                Input
            </button>
            <button v-if="tableMeta._is_owner" class="btn btn-default mr5" :class="{active: activeTab === 'columns'}" :style="btnStyle" @click="setTab('columns');">
                Standard
            </button>
            <button v-if="isOwnerOrCanEdit('tab-settings-cust')" class="btn btn-default mr5" :class="{active: activeTab === 'customizable'}" :style="btnStyle" @click="setTab('customizable');">
                Customizable
            </button>
            <button v-if="tableMeta._is_owner" class="btn btn-default mr5" :class="{active: activeTab === 'bas_popup'}" :style="btnStyle" @click="setTab('bas_popup');">
                Pop-up
            </button>
            <button v-if="tableMeta._is_owner" class="btn btn-default mr5" :class="{active: activeTab === 'others'}" :style="btnStyle" @click="setTab('others');">
                3rd Party
            </button>
        </div>

        <!--Tab with table settings-->
        <div class="basic-tab table-settings" v-if="activeTab === 'general'">
            <table-settings-module
                :table-meta="tableMeta"
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
        <div class="basic-tab" v-else-if="activeTab" style="background-color: inherit;">
            <custom-table
                :cell_component_name="'custom-cell-settings-display'"
                :global-meta="tableMeta"
                :table-meta="settingsMeta['table_fields']"
                :settings-meta="settingsMeta"
                :all-rows="tableMeta._fields"
                :rows-count="tableMeta._fields.length"
                :cell-height="1"
                :max-cell-rows="0"
                :user="user"
                :behavior="'settings_display'"
                :available-columns="getAvaCols"
                :forbidden-columns="forbiddenColumns"
                :use_theme="true"
                @updated-row="updateRow"
                @mass-updated-rows="massUpdatedRows"
                @row-index-clicked="rowIndexClickedDisplay"
                @show-add-ddl-option="showValidationPop"
            ></custom-table>
            <for-settings-list-pop
                v-if="settingsMeta['table_fields'] && editDisplayPopUpRow"
                :global-meta="tableMeta"
                :table-meta="settingsMeta['table_fields']"
                :settings-meta="settingsMeta"
                :table-row="editDisplayPopUpRow"
                :user="user"
                :cell-height="1"
                :max-cell-rows="0"
                :forbidden-columns="forbiddenColumns"
                :init_active="activeTab"
                @popup-update="updateRow"
                @popup-close="closePopUp"
                @another-row="anotherRowPopup"
                @select-another-row="selAnotherPop"
            ></for-settings-list-pop>
            <validation-settings-pop-up
                v-if="validationHeader"
                :table-header="validationHeader"
                @popup-close="hideValidationPop"
            ></validation-settings-pop-up>
        </div>

        <!--Import tooltips for settings/basics-->
        <div v-show="importTooltips" class="modal-wrapper">
            <div class="modal">
                <div class="modal-dialog modal-sm">
                    <div class="modal-content">
                        <div class="modal-body">
                            <label>Paste tooltips for columns arranged in rows below:</label>
                            <textarea v-model="parseTooltipOptions" class="form-control" rows="7"></textarea>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-success" @click="parseTooltip()">Parse&Import</button>
                            <button type="button" class="btn btn-default" @click="importTooltips = false">Cancel</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</template>

<script>
    import {SpecialFuncs} from '../../../../../classes/SpecialFuncs';
    import {DDLHelper} from "../../../../../classes/helpers/DDLHelper";
    import {CellSettingsDisplayHelper} from "../../../../CustomCell/CellSettingsDisplayHelper";

    import {eventBus} from '../../../../../app';

    import CellStyleMixin from "../../../../_Mixins/CellStyleMixin";

    import CustomTable from '../../../../CustomTable/CustomTable.vue';
    import InfoSignLink from "../../../../CustomTable/Specials/InfoSignLink.vue";
    import TableSettingsModule from "../../../../CommonBlocks/TableSettingsModule.vue";
    import ForSettingsPopUp from "../../../../CustomPopup/ForSettingsPopUp";
    import ForSettingsListPop from "../../../../CustomPopup/ForSettingsListPop";
    import ValidationSettingsPopUp from "../../../../CustomPopup/ValidationSettingsPopUp.vue";

    export default {
        name: "TableSettingsBasics",
        components: {
            ValidationSettingsPopUp,
            ForSettingsListPop,
            ForSettingsPopUp,
            TableSettingsModule,
            InfoSignLink,
            CustomTable,
        },
        mixins: [
            CellStyleMixin,
        ],
        data: function () {
            return {
                fill_field: null,
                fill_status: false,

                customizable_field: 'name',
                customizable_idx: 0,
                columns_field: 'name',
                columns_idx: 0,

                activeTab: this.init_tab || (this.tableMeta._is_owner ? 'columns' : 'customizable'),
                importTooltips: false,
                parseTooltipOptions: '',

                validationHeader: null,
                editDisplayPopUpRow: null,
                popUpRole: null,
            }
        },
        computed: {
            forbiddenColumns() {
                return SpecialFuncs.forbiddenCustomizables(this.tableMeta);
            },
            getAvaCols() {
                switch (this.activeTab) {
                    case 'inps': return this.inpsAvailCols;
                    case 'columns': return this.standardAvailCols;
                    case 'customizable': return this.customizAvailCols;
                    case 'bas_popup': return this.popupAvailCols;
                    case 'others': return this.othersAvailCols;
                }
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
                    let fltr = ['header_background','header_unit_ddl','unit_ddl_id','unit','is_search_autocomplete_display','is_unique_collection','f_required','header_triangle','validation_rules'];
                    if (this.filter_for_field !== 'Table') {
                        fltr.push('default_stats');
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
                            'filter_search','filter_type','filter','is_floating','unit_display','min_width','max_width','width'
                        ].indexOf(el) === -1;
                    });
                }
                return cols;
            },
            popupAvailCols() {
                let cols = this.$root.availablePopupDisplayColumns;
                if (this.filter_for_field) {
                    let fltr = [];
                    if (this.filter_for_field !== 'Boards') {
                        fltr.push('is_show_on_board');
                    }
                    cols = _.filter(_.clone(cols), (el) => {
                        return fltr.indexOf(el) === -1;
                    });
                }
                return cols;
            },
            othersAvailCols() {
                return this.$root.availableOthersColumns;
            },
            btnStyle() {
                return {
                    height: '36px',
                    ...this.textSysStyle,
                }
            },
        },
        props:{
            tableMeta: Object,
            settingsMeta: Object,
            user:  Object,
            table_id: Number,
            isVisible: Boolean,
            filter_for_field: String,
            init_tab: String,
        },
        watch: {
            table_id: function(val) {
                this.setTab(this.tableMeta._is_owner ? 'columns' : 'customizable');
                this.$root.oldTbName = this.tableMeta.name;
            },
            isVisible(val) {
                if (val) {
                    this.$root.oldTbName = this.tableMeta.name;
                }
            },
        },
        methods: {
            isOwnerOrCanEdit(key) {
                let permis = this.tableMeta._is_owner
                    || (this.tableMeta._current_right && this.tableMeta._current_right.can_edit_tb)
                    || (this.tableMeta._current_right && this.tableMeta._current_right.can_change_primaryview);

                let canSubTab = this.tableMeta._is_owner
                    || !this.$root.user.view_all
                    || String(this.$root.user.view_all.parts_avail).match('"'+key+'"');

                return permis && canSubTab;
            },
            setTab(key) {
                this.activeTab = key;
            },
            //edit header
            updateRow(tableRow) {
                this.$root.updateSettingsColumn(this.tableMeta, tableRow);
                DDLHelper.setUses(this.tableMeta);
            },
            massUpdatedRows(massRows) {
                _.each(massRows, (row) => {
                    this.updateRow(row);
                });
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
                        Swal('Info', getErrors(errors));
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
            closePopUp() {
                this.editDisplayPopUpRow = null;
            },

            //Validation popup
            showValidationPop(tableHeader, tableRow) {
                this.validationHeader = tableRow;
            },
            hideValidationPop(validationString) {
                if (this.validationHeader.validation_rules !== validationString) {
                    this.validationHeader._changed_field = 'validation_rules';
                    this.validationHeader.validation_rules = validationString;
                    this.$root.updateSettingsColumn(this.tableMeta, this.validationHeader);
                }
                this.validationHeader = null;
            },

            //update table settings
            updateTable(prop_name) {
                this.$root.updateTable(this.tableMeta, prop_name);
            },

            //change rows in popup
            anotherRowPopup(is_next) {
                let row_id = (this.editDisplayPopUpRow ? this.editDisplayPopUpRow.id : null);
                this.$root.anotherPopup(this.tableMeta._fields, row_id, is_next, this.rowIndexClickedDisplay);
            },
            selAnotherPop(row) {
                this.editDisplayPopUpRow = row;
            },

            //mass changer
            sendMassStatus() {
                this.$root.sm_msg_type = 1;

                let globField = _.find(this.settingsMeta['table_fields']._fields, {field: this.fill_field});
                let availFields = _.filter(this.tableMeta._fields, (fld) => {
                    return !CellSettingsDisplayHelper.disabledCheckBox(this.tableMeta, globField, fld, this.behavior);
                });
                availFields = _.map(availFields, 'field');

                axios.put('/ajax/settings/mass-data', {
                    table_id: this.tableMeta.id,
                    field: this.fill_field,
                    val: this.fill_status ? 1 : 0,
                    limit_fields: availFields,
                }).then(({ data }) => {
                    _.each(this.tableMeta._fields, (fld) => {
                        if (availFields.indexOf(fld.field) > -1) {
                            fld[this.fill_field] = this.fill_status ? 1 : 0;
                        }
                    });
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
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
                        ||
                        (this.activeTab === 'bas_popup' && this.$root.availablePopupDisplayColumns.indexOf(hdr.field) > -1)
                    );
            },
        },
        mounted() {
            let availtabs = [];
            if (this.isOwnerOrCanEdit('tab-settings-cust')) availtabs = availtabs.concat(['customizable']);
            if (this.tableMeta._is_owner) availtabs = availtabs.concat(['inps','columns','bas_popup','others']);
            if (this.isOwnerOrCanEdit('tab-settings')) availtabs = availtabs.concat(['general']);
            if (availtabs.indexOf(this.activeTab) === -1) {
                this.setTab(_.first(availtabs));
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
            column-fill: balance;
            column-gap: 5%;
        }
        @media (min-width: 1366px) {
            .settings-module {
                column-count: 2;
            }
        }
    }
</style>