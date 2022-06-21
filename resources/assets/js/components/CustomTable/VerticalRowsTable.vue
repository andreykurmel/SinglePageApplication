    <template>
        <div class="full-frame">
            <table>
                <tr>
                    <!--Index-->
                    <th>#</th>
                    <!--Present Row-->
                    <td v-for="(tableRow, index) in allRows">
                        <a v-if="inArray(behavior, linkArray)" @click.prevent="rowIndexClicked(index)">
                            <span>{{ ((page-1)*(tableMeta.rows_per_page || rowsCount))+index+1 }}</span>
                            <i v-if="index === hover_row && inArray(behavior, popupArray)" class="glyphicon glyphicon-resize-full"></i>
                        </a>
                        <span v-else="">{{ ((page-1)*(tableMeta.rows_per_page || rowsCount))+index+1 }}</span>
                    </td>
                    <!--New Row-->
                    <td></td>
                </tr>

                <tr v-for="tableHeader in showMetaFields">
                    <!--Index-->
                    <th>
                        <span class="head-content">{{ tableHeader.name }}</span>
                    </th>
                    <!--Present Row-->
                    <template v-for="(tableRow, index) in allRows">
                        <td :is="cell_component_name"
                            :global-meta="globalMeta"
                            :table-meta="tableMeta"
                            :row-index="index"
                            :rows-count="rowsCount"
                            :ref_tb_from_refcond="ref_tb_from_refcond"
                            :table_id="table_id"
                            :table-row="tableRow"
                            :table-header="tableHeader"
                            :cell-value="tableRow[tableHeader.field]"
                            :cell-height="cellHeight"
                            :max-cell-rows="maxCellRows"
                            :selected-cell="selectedCell"
                            :is-selected="selectedCell.is_selected(tableMeta, tableHeader, index)"
                            :behavior="behavior"
                            :user="user"
                            :condition-array="conditionArray"
                            :with_edit="with_edit"
                            :is-add-row="false"
                            :no_width="no_width"
                            :parent-row="parentRow"
                            :link_popup_conditions="link_popup_conditions"
                            :use_theme="use_theme"
                            @updated-cell="updatedRow"
                            @check-clicked="checkClicked"
                            @show-src-record="showSrcRecord"
                            @radio-checked="radioChecked"
                            @show-add-ddl-option="showAddDDLOption"
                            @show-def-val-popup="showDefValPopup"
                            @show-add-ref-cond="showAddRefCond"
                        ></td>
                    </template>
                    <!--New Row-->
                    <td :is="cell_component_name"
                        :global-meta="globalMeta"
                        :table-meta="tableMeta"
                        :settings-meta="settingsMeta"
                        :rows-count="rowsCount"
                        :ref_tb_from_refcond="ref_tb_from_refcond"
                        :table-row="objectForAdd"
                        :table-header="tableHeader"
                        :cell-value="objectForAdd[tableHeader.field]"
                        :cell-height="cellHeight"
                        :max-cell-rows="maxCellRows"
                        :behavior="behavior"
                        :user="user"
                        :is-add-row="true"
                        :no_width="no_width"
                        :with_edit="with_edit"
                        :parent-row="parentRow"
                        :link_popup_conditions="link_popup_conditions"
                        :use_theme="use_theme"
                        @show-add-ddl-option="showAddDDLOption"
                    ></td>
                </tr>

                <tr v-if="inArray(behavior, actionArray)">
                    <!--Index-->
                    <th></th>
                    <!--Present Row-->
                    <td v-for="(tableRow, index) in allRows" class="centered-cell" :style="textStyle">
                        <button v-if="inArray(behavior, actionDelArray)
                                    && (!tableRow.is_system || inArray(behavior, actionDelSystem))
                                    && (behavior != 'cond_format' || tableRow.user_id == user.id)
                                    && !delRestricted"
                                class="blue-gradient"
                                :style="(use_theme ? $root.themeButtonStyle : null)"
                                :disabled="!with_edit"
                                @click="rowIndexClicked(index);deleteRow(tableRow, index)"
                        >
                            <i class="glyphicon glyphicon-trash"></i>
                        </button>
                        <template v-if="inArray(behavior, ['invite_module']) && tableRow.status != 2">
                            | <button :style="(use_theme ? $root.themeButtonStyle : null)" @click="$emit('resend-action', tableRow)">Resend</button>
                        </template>
                    </td>
                    <!--New Row-->
                    <td v-if="inArray(behavior, actionArray)" class="centered-cell" :style="textStyle">
                        <button class="btn btn-primary btn-sm blue-gradient add-btn"
                                :style="addBtnStyle"
                                :disabled="!with_edit"
                                @click="addRow()"
                        >Add</button>
                    </td>
                </tr>
            </table>
        </div>
    </template>

    <script>
    //    TODO: not finished
        import {SelectedCells} from './../../classes/SelectedCells';

        import {eventBus} from './../../app';

        import CustomHeadCellTableData from '../CustomCell/CustomHeadCellTableData.vue';
        import CustomCellTableData from '../CustomCell/CustomCellTableData.vue';
        import CustomCellSystemTableData from '../CustomCell/CustomCellSystemTableData.vue';
        import CustomCellCorrespTableData from '../CustomCell/CustomCellCorrespTableData.vue';
        import CustomCellSettingsDisplay from '../CustomCell/CustomCellSettingsDisplay.vue';
        import CustomCellDisplayLinks from '../CustomCell/CustomCellDisplayLinks.vue';
        import CustomCellSettingsDdl from '../CustomCell/CustomCellSettingsDdl.vue';
        import CustomCellSettingsPermission from '../CustomCell/CustomCellSettingsPermission.vue';
        import CustomCellSettingsDcr from '../CustomCell/CustomCellSettingsDcr.vue';
        import CustomCellColRowGroup from '../CustomCell/CustomCellColRowGroup.vue';
        import CustomCellKanbanSett from '../CustomCell/CustomCellKanbanSett.vue';
        import CustomCellRefConds from '../CustomCell/CustomCellRefConds.vue';
        import CustomCellCondFormat from '../CustomCell/CustomCellCondFormat.vue';
        import CustomCellAlertNotif from '../CustomCell/CustomCellAlertNotif.vue';
        import CustomCellPlans from '../CustomCell/CustomCellPlans.vue';
        import CustomCellConnection from '../CustomCell/CustomCellConnection.vue';
        import CustomCellUserGroups from '../CustomCell/CustomCellUserGroups.vue';
        import CustomCellInvitations from '../CustomCell/CustomCellInvitations.vue';
        import CustomCellFolderView from '../CustomCell/CustomCellFolderView.vue';
        import CustomCellTableView from '../CustomCell/CustomCellTableView.vue';
        import CustomCellStimAppView from '../CustomCell/CustomCellStimAppView.vue';
        import SettingsDisplayNameCell from '../CustomCell/SettingsDisplayNameCell.vue';
        import HeaderMenuElem from './Header/HeaderMenuElem.vue';
        import HeaderResizer from './Header/HeaderResizer.vue';

        import IsShowFieldMixin from '../_Mixins/IsShowFieldMixin.vue';
        import TestRowColMixin from './../_Mixins/TestRowColMixin.vue';
        import CanEditMixin from '../_Mixins/CanViewEditMixin.vue';
        import CheckRowBackendMixin from './../_Mixins/CheckRowBackendMixin.vue';
        import CellStyleMixin from './../_Mixins/CellStyleMixin.vue';
        import LinkEmptyObjectMixin from './../_Mixins/LinkEmptyObjectMixin.vue';

        export default {
            name: "VerticalRowsTable",
            mixins: [
                IsShowFieldMixin,
                TestRowColMixin,
                CanEditMixin,
                CheckRowBackendMixin,
                CellStyleMixin,
                LinkEmptyObjectMixin,
            ],
            components: {
                CustomCellTableData,
                CustomCellSystemTableData,
                CustomCellCorrespTableData,
                CustomCellSettingsDisplay,
                CustomCellDisplayLinks,
                CustomCellSettingsDdl,
                CustomCellSettingsPermission,
                CustomCellSettingsDcr,
                CustomHeadCellTableData,
                CustomCellColRowGroup,
                CustomCellKanbanSett,
                CustomCellRefConds,
                CustomCellCondFormat,
                CustomCellAlertNotif,
                CustomCellPlans,
                CustomCellConnection,
                CustomCellUserGroups,
                CustomCellInvitations,
                CustomCellFolderView,
                CustomCellTableView,
                CustomCellStimAppView,
                SettingsDisplayNameCell,
                HeaderMenuElem,
                HeaderResizer,
            },
            data: function () {
                return {
                    tableSizes: {
                        headerHgt: 0,
                    },
                    selectedCell: new SelectedCells(),

                    hover_row: null,
                    overHeader: null,
                    overRow: null,
                }
            },
            props:{
                globalMeta: Object,
                tableMeta: Object,
                settingsMeta: Object,
                allRows: Object|null,
                user: Object,
                cellHeight: Number,
                maxCellRows: Number,
                page: Number,
                rowsCount: Number,
                cell_component_name: String,
                behavior: String,
                sort: Array,
                forbiddenColumns: Array, // for IsShowFieldMixin.vue
                availableColumns: Array, // for IsShowFieldMixin.vue
                addingRow: Object,
                selectedRow: Number,
                conditionArray: Array|null,
                headersWithCheck: Array,
                ref_tb_from_refcond: Object|null,
                with_edit: Boolean,
                table_id: Number,
                delRestricted: Boolean,
                parentRow: Object,
                link_popup_conditions: Object|Array,
                use_theme: Boolean,
                no_width: Boolean,
            },
            computed: {
                addBtnStyle() {
                    let style = _.cloneDeep(this.textStyle);
                    style.maxHeight = this.tdCellHGT+'px';
                    if (this.use_theme) {
                        Object.assign(style, this.$root.themeButtonStyle);
                    }
                    return style;
                },
                showMetaFields() {
                    return _.filter(this.tableMeta._fields, (hdr) => {
                        return this.isShowFieldElem(hdr);
                    });
                },
            },
            methods: {
                //additional functions
                checkClicked(type, status, arr) {
                    let ids = arr.filter((el) => {
                        return !this.inArray(el.code, ['q_tables', 'row_table']);
                    });
                    ids = _.map(ids, 'id');

                    this.$emit('check-row', type, status, ids);
                },

                //rows changing
                showAddDDLOption(tableHeader, tableRow) {
                    this.$emit('show-add-ddl-option', tableHeader, tableRow);
                },
                addRow() {
                    if (this.$root.setCheckRequired(this.tableMeta, this.objectForAdd)) {
                        let obj = Object.assign({}, this.objectForAdd);
                        this.newObject();

                        this.$emit('added-row', obj);
                    }
                    this.$forceUpdate();
                },
                updatedRow(params, hdr) {
                    if (this.$root.setCheckRequired(this.tableMeta, params)) {
                        this.$emit('updated-row', params, hdr);
                    }
                    this.$forceUpdate();
                },
                deleteRow(tableRow, index) {
                    this.$emit('delete-row', tableRow, index);
                    this.$forceUpdate();
                },
                rowIndexClicked(index) {
                    this.selectedCell.clear();
                    this.$emit('row-index-clicked', index);
                },
                radioChecked(index) {
                    this.$emit('radio-checked', index);
                },
                showHeaderSettings(header) {
                    this.$emit('show-header-settings', header);
                },

                //sorting
                sortByField(tableHeader, $dir) {
                    let spec_key = window.event.ctrlKey || window.event.altKey || window.event.shiftKey;
                    this.$emit(spec_key ? 'sub-sort-by-field' : 'sort-by-field', tableHeader, $dir);
                },
                getSortLvl(tableHeader) {
                    let idx = _.findIndex(this.sort, {field: tableHeader.field});
                    return idx > -1 ? idx+1 : '';
                },

                //src record and tables function
                showSrcRecord(lnk, header, tableRow) {
                    this.$emit('show-src-record', lnk, header, tableRow);
                },
                showAddRefCond(refId) {
                    this.$emit('show-add-ref-cond', refId);
                },
                showDefValPopup(tableRow) {
                    this.$emit('show-def-val-popup', tableRow);
                },

                //backend autocomplete
                checkRowAutocomplete() {
                    this.checkRowOnBackend( this.tableMeta.id, this.objectForAdd );
                },
                newObject() {
                    this.createObjectForAdd();
                    this.checkRowAutocomplete();
                },

                //keys
                keydownHandler(e) {
                    if (e.keyCode == 27) {
                        this.overRow = null;
                        this.overHeader = null;
                        this.$forceUpdate();
                    }
                },

                //external scroll//
                externalScroll(table_id, row_id) {
                    if (this.tableMeta.id == table_id) {
                        let refs = this.$refs['cttr_'+table_id+'_'+row_id];
                        let rrow = _.first(refs);
                        rrow ? rrow.scrollIntoView({block: 'center'}) : null;
                    }
                },
            },
            mounted() {
                eventBus.$on('global-keydown', this.keydownHandler);
                eventBus.$on('scroll-tabldas-to-row', this.externalScroll);
            },
            beforeDestroy() {
                eventBus.$off('global-keydown', this.keydownHandler);
                eventBus.$off('scroll-tabldas-to-row', this.externalScroll);
            }
        }
    </script>

    <style lang="scss" scoped>
        @import "./CustomTable.scss";

        .indeterm_check__wrap {
            top: 2px;
            position: relative;

            .indeterm_check {
                .group__icon {
                    font-size: 0.9em;
                    top: 0;
                }
            }
        }

        input[type="radio"], input[type="checkbox"] {
            margin: 0;
        }
    </style>