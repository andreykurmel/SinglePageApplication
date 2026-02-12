<template>
    <div id="tab-list-view" v-if="tableMeta" class="full-height">
        <listing-view
            v-if="draw_table && tableMeta.primary_view === 'list_view'"
            :table-meta="tableMeta"
            :all-rows="$root.listTableRows"
            :user="user"
            :page="page"
            :rows-count="tableMeta._view_rows_count || 0"
            :cell-height="$root.cellHeight"
            :max-cell-rows="$root.maxCellRows"
            :full-width-cell="fullWidthCell"
            :is-pagination="isPagination"
            :behavior="'list_view'"
            :with-border="false"
            @added-row="insertRow"
            @updated-row="updateRow"
            @delete-row="deleteRow"
            @show-src-record="showSrcRecord"
            @show-add-ddl-option="showAddDDLOption"
            @change-page="changePage"
        ></listing-view>
        <board-view
            v-if="draw_table && tableMeta.primary_view === 'board_view'"
            :table-meta="tableMeta"
            :all-rows="$root.listTableRows"
            :user="user"
            :page="page"
            :rows-count="tableMeta._view_rows_count || 0"
            :cell-height="$root.cellHeight"
            :max-cell-rows="$root.maxCellRows"
            :full-width-cell="fullWidthCell"
            :is-pagination="isPagination"
            :behavior="'list_view'"
            :with-border="false"
            @added-row="insertRow"
            @updated-row="updateRow"
            @delete-row="deleteRow"
            @show-src-record="showSrcRecord"
            @show-add-ddl-option="showAddDDLOption"
            @change-page="changePage"
        ></board-view>
        <custom-table
            v-if="draw_table && tableMeta.primary_view === 'grid_view' && inArray(tableMeta.table_engine, ['default', 'virtual'])"
            :tb_id="'table_list_view'"
            :cell_component_name="$root.tdCellComponent(tableMeta.is_system)"
            :table_id="table_id"
            :global-meta="tableMeta"
            :table-meta="tableMeta"
            :settings-meta="settingsMeta"
            :all-rows="$root.listTableRows"
            :page="page"
            :rows-count="tableMeta._view_rows_count || 0"
            :cell-height="$root.cellHeight"
            :max-cell-rows="$root.maxCellRows"
            :full-width-cell="fullWidthCell"
            :is-pagination="isPagination"
            :sort="sort"
            :user="user"
            :behavior="'list_view'"
            :adding-row="addingRow"
            :is_visible="isVisible"
            :headers-with-check="user.is_admin ? tableMeta._js_headersWithCheck : []"
            @added-row="insertRow"
            @updated-row="updateRow"
            @delete-row="deleteRow"
            @copy-row="copyRow"
            @mass-updated-rows="massUpdatedRows"
            @delete-selected-rows="deleteSelectedRowsHandler"
            @check-row="toggleAllCheckBoxes"
            @change-page="changePage"
            @sort-by-field="sortByField"
            @sub-sort-by-field="subSortByField"
            @toggle-favorite-row="toggleFavoriteRow"
            @toggle-all-favorites="toggleAllFavorites"
            @row-index-clicked="rowIndexClicked"
            @show-src-record="showSrcRecord"
            @reorder-rows="reorderRows"
            @show-add-ddl-option="showAddDDLOption"
        ></custom-table>
        <custom-table-v2
            v-if="draw_table && tableMeta.primary_view === 'grid_view' && inArray(tableMeta.table_engine, ['vue_virtual'])"
            :tb_id="'table_list_view'"
            :table-meta="tableMeta"
            :all-rows="$root.listTableRows"
            :page="page"
            :rows-count="tableMeta._view_rows_count || 0"
            :cell-height="$root.cellHeight"
            :sort="sort"
            :behavior="'list_view'"
            :adding-row="addingRow"
            :visible="isVisible"
            @added-row="insertRow"
            @updated-row="updateRow"
            @delete-row="deleteRow"
            @copy-row="copyRow"
            @mass-updated-rows="massUpdatedRows"
            @delete-selected-rows="deleteSelectedRowsHandler"
            @check-row="toggleAllCheckBoxes"
            @change-page="changePage"
            @sort-by-field="sortByField"
            @sub-sort-by-field="subSortByField"
            @toggle-favorite-row="toggleFavoriteRow"
            @toggle-all-favorites="toggleAllFavorites"
            @row-index-clicked="rowIndexClicked"
            @show-src-record="showSrcRecord"
            @reorder-rows="reorderRows"
            @show-add-ddl-option="showAddDDLOption"
        ></custom-table-v2>

        <custom-edit-pop-up
            v-if="tableMeta && editPopUpRow"
            :global-meta="tableMeta"
            :table-meta="tableMeta"
            :table-row="editPopUpRow"
            :settings-meta="settingsMeta"
            :role="popUpRole"
            :input_component_name="$root.tdCellComponent(tableMeta.is_system)"
            :behavior="'list_view'"
            :user="user"
            :cell-height="$root.cellHeight"
            :max-cell-rows="$root.maxCellRows"
            @popup-insert="insertRow"
            @popup-update="updateRow"
            @popup-delete="deleteRow"
            @popup-copy="copyRow"
            @popup-close="closePopUp"
            @show-src-record="showSrcRecord"
            @another-row="anotherRowPopup"
        ></custom-edit-pop-up>
    </div>
</template>

<script>
    import {eventBus} from '../../../../app';

    import {RowDataHelper} from "../../../../classes/helpers/RowDataHelper";
    import {JsFomulaParser} from "../../../../classes/JsFomulaParser";
    import {RefCondHelper} from '../../../../classes/helpers/RefCondHelper';
    import {SpecialFuncs} from '../../../../classes/SpecialFuncs';
    import {Endpoints} from "../../../../classes/Endpoints";

    import CheckRowBackendMixin from './../../../_Mixins/CheckRowBackendMixin.vue';
    import TableSortMixin from './../../../_Mixins/TableSortMixin.vue';
    import TableCheckboxesMixin from './../../../_Mixins/TableCheckboxesMixin.vue';
    import IsShowFieldMixin from './../../../_Mixins/IsShowFieldMixin.vue';

    import CustomTable from './../../../CustomTable/CustomTable';
    import CustomEditPopUp from '../../../CustomPopup/CustomEditPopUp';
    import BoardView from "../../../CustomTable/BoardView.vue";
    import ListingView from "../../../CustomTable/ListingView.vue";
    import CustomTableV2 from "../../../CustomTable/CustomTableV2.vue";

    export default {
        name: "TabListView",
        mixins: [
            CheckRowBackendMixin,
            TableSortMixin,
            TableCheckboxesMixin,
            IsShowFieldMixin,
        ],
        components: {
            CustomTableV2,
            ListingView,
            BoardView,
            CustomTable,
            CustomEditPopUp,
        },
        data: function () {
            return {
                draw_table: true,
                editPopUpRow: null,
                popUpRole: null,
                page: 1,
                first_init_view: null,
            }
        },
        props:{
            tableMeta: Object,
            settingsMeta: Object,
            fullWidthCell: Boolean,
            isPagination: Boolean,
            table_id: Number,
            user: Object,
            addingRow: Object,
            searchObject: Object,
            preset_page: Number,
            preset_sort: Array,
            isVisible: Boolean,
            has_filters_url_preset: Boolean,
            recalc_job_id: Number,
        },
        watch: {
            table_id: {
                handler(val) {
                    this.page = this.preset_page || 1;
                    this.sort = this.preset_sort || [];
                    this.first_init_view = this.tableMeta._cur_settings ? this.tableMeta._cur_settings.initial_view_id : null;

                    if (val) {
                        this.getTableData('load');
                    }
                },
                immediate: true,
            },
            isVisible(val) {
                if (val) {
                    this.draw_table = true;
                }
            },
            recalc_job_id(val) {
                if (val) {
                    JsFomulaParser.checkEmptyRows(this.tableMeta, this.$root.listTableRows);
                }
            },
        },
        methods: {
            inArray(item, array) {
                return $.inArray(item, array) > -1;
            },
            changePage(page) {
                this.page = page;
                this.getTableData('page');
            },
            newRequestParams(for_list_view) {

                let request = {
                    ...SpecialFuncs.tableMetaRequest(this.table_id, undefined, for_list_view),
                    ...{
                        page: this.page,
                        rows_per_page: this.tableMeta.rows_per_page,
                        sort: this.sort,
                        search_words: this.searchObject.keyWords,
                        search_columns: this.searchObject.columns,
                        row_id: this.searchObject.direct_row_id || null,
                        applied_filters: this.$root.filters,
                        hidden_row_groups: this.tableMeta.__hidden_row_groups
                    }
                };

                let toggled_rg = _.find(this.tableMeta._row_groups, {_toggled: 1});
                if (toggled_rg) {
                    request.row_group_toggled = {
                        id: toggled_rg.id,
                        should_enable: toggled_rg._show_status
                    };
                    toggled_rg._toggled = 0;
                }
                if (this.$root.all_rg_toggled !== null) {
                    request.row_group_toggled = {
                        id: 'all',
                        should_enable: this.$root.all_rg_toggled
                    };
                    this.$root.all_rg_toggled = null;
                }

                request.first_init_view = this.first_init_view;

                if (this.$root.request_view_filtering) {
                    request.search_view = this.$root.request_view_filtering;
                }

                return request;
            },
            getTableData(change_type) {
                if (this.isVisible) {
                    if ($.inArray(change_type, ['collaborator','filter','reload-filter','rows-changed']) === -1) {
                        $.LoadingOverlay('show');
                    }
                } else {
                    this.draw_table = false;
                }

                //goto First Page on filtering or searching
                if ($.inArray(change_type, ['filter','search']) > -1) {
                    this.page = 1;
                }

                //if not first request for getting records -> switch-off initial loading.
                if (this.first_init_view && $.inArray(change_type, ['load', 'page', 'reload-filter']) === -1) {
                    this.first_init_view = null;
                }
                this.$root.request_params = this.newRequestParams();

                let params = _.cloneDeep(this.$root.request_params);
                params.special_params.for_list_view = true;

                axios.post('/ajax/table-data/get', params).then(({ data }) => {

                    console.log('ListViewData', data, 'size about: ', JSON.stringify(data).length);

                    //this.$root.listTableRows
                    RowDataHelper.fillCanEdits(this.tableMeta, data.rows);
                    SpecialFuncs.setRowsSaveNewstatus(this.$root, 'listTableRows', data.rows);
                    this.$root.filters = SpecialFuncs.prepareFilters(this.$root.filters, data.filters);
                    this.$emit('update-meta-params', data);

                    //check visibility of row Groups
                    _.each(this.tableMeta._row_groups, (rg) => {
                        let data_rg = _.find(data.row_group_statuses, {id: Number(rg.id)});
                        rg._show_status = data_rg ? data_rg.show_status : 2;
                        rg._group_hidden = data_rg ? data_rg.group_hidden : false;
                        rg._filter_disabled = data_rg ? data_rg.filter_disabled : false;
                    });
                    this.tableMeta.__hidden_row_groups = data.hidden_row_groups;

                    if (this.editPopUpRow) {
                        let popIdx = _.findIndex(data.rows, {id: Number(this.editPopUpRow.id)});
                        this.rowIndexClicked(popIdx);
                    }

                    //show that request params are changed
                    eventBus.$emit('new-request-params', change_type, 'load');

                    //check empty formulas
                    if (change_type && this.recalc_job_id) {
                        JsFomulaParser.checkEmptyRows(this.tableMeta, this.$root.listTableRows);
                    }

                    //save current table status for current user
                    this.saveTableStatus();
                    eventBus.$emit('page-reloaded', change_type);

                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
            copyRow(tableRow) {
                Endpoints.massCopyRows(this.tableMeta.id, [tableRow.id]).then((data) => {
                    this.thenForInsert(data, true);
                });
            },
            insertRow(tableRow, no_close, selected_row) {
                Endpoints.insertRow(this.tableMeta, tableRow, this.newRequestParams(true)).then((data) => {
                    this.thenForInsert(data, no_close, selected_row);
                });
            },
            thenForInsert(data, no_close, selected_row) {
                _.each(data.rows, (row) => {
                    if (_.findIndex(this.$root.listTableRows, {id: row.id}) === -1) {
                        if (selected_row) {
                            this.getTableData('rows-changed');
                        } else {
                            row._is_new = 1;
                            this.$root.listTableRows.splice(0, 0, row);
                            this.editPopUpRow = no_close && row ? row : null;
                        }
                    }
                });
                if (data.filters && data.filters.length) {
                    this.$root.filters = SpecialFuncs.prepareFilters(this.$root.filters, data.filters);
                }

                data.rows_count = this.tableMeta._view_rows_count+1;
                data.global_rows_count = this.tableMeta._global_rows_count+1;
                this.$emit('update-meta-params', data);

                eventBus.$emit('new-request-params', 'rows-changed', 'insert');
            },
            updateRow(tableRow) {
                this.massUpdatedRows([tableRow]);
            },
            massUpdatedRows(massTableRows) {
                Endpoints.massUpdateRows(this.tableMeta, massTableRows, this.newRequestParams(true)).then((data) => {
                    this.thenForUpdate(data, massTableRows, 'current');
                }).catch((err) => {
                    this.reloadPageHandler();
                }).finally(() => {
                    eventBus.$emit('sync-favorites-update', massTableRows);
                });
            },
            thenForUpdate(data, oldData, current) {
                if (this.rowsReloadNeeded(oldData)) {
                    this.getTableData('rows-changed');
                } else {
                    _.each(oldData, (row) => {
                        let oldrow = _.find(this.$root.listTableRows, {id: row.id});
                        let newrow = _.find(data.rows, {id: row.id});
                        if (oldrow && newrow) {
                            this.$root.assignObject(newrow, oldrow);
                        }
                    });
                    this.$root.filters = SpecialFuncs.prepareFilters(this.$root.filters, data.filters);

                    data.rows_count = undefined;
                    data.global_rows_count = undefined;
                    this.$emit('update-meta-params', data);

                    eventBus.$emit('new-request-params', 'rows-changed', 'update', current);
                }
            },
            deleteRow(tableRow) {
                Endpoints.deleteRow(this.tableMeta, tableRow).then((data) => {
                    this.thenForDelete(data, tableRow.id);
                });
            },
            thenForDelete(data, rowId) {
                eventBus.$emit('sync-favorites-delete', rowId);
                let idx = _.findIndex(this.$root.listTableRows, {id: rowId});
                if (idx > -1) {
                    this.$root.listTableRows.splice(idx, 1);
                }

                data.rows_count = this.tableMeta._view_rows_count-1;
                data.global_rows_count = this.tableMeta._global_rows_count-1;
                this.$emit('update-meta-params', data);

                eventBus.$emit('new-request-params', 'rows-changed', 'delete');
            },
            rowsReloadNeeded(changedRows) {
                let appliedFilters = _.filter(this.$root.filters, (f) => { return f.applied_index > 0; });
                appliedFilters = _.map(appliedFilters, 'field');

                let needed = false;
                _.each(changedRows, (row) => {
                    needed = needed || appliedFilters.indexOf(row._changed_field) > -1;
                });
                return needed;
            },
            toggleFavoriteRow(tableRow) {
                this.$root.sm_msg_type = 1;
                axios.put('/ajax/table-data/favorite', {
                    table_id: this.tableMeta.id,
                    row_id: tableRow.id,
                    change: (tableRow._is_favorite ? 1 : 0)
                }).then(({ data }) => {
                    eventBus.$emit('sync-favorites-favorite', tableRow);
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
            toggleAllFavorites(status) {
                let check_obj = this.$root.checkedRowObject(this.$root.listTableRows);

                this.$root.sm_msg_type = 1;

                let request_params = _.cloneDeep(this.$root.request_params);
                request_params.page = 1;
                request_params.rows_per_page = 0;

                axios.put('/ajax/table-data/favorite/all', {
                    table_id: this.tableMeta.id,
                    rows_ids: (check_obj.all_checked ? null : check_obj.rows_ids),
                    request_params: (check_obj.all_checked ? request_params : null),
                    status: (status ? 1 : 0)
                }).then(({ data }) => {
                    eventBus.$emit('reload-page');
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
            rowIndexClicked(index) {
                this.editPopUpRow = this.$root.listTableRows[index];
                this.popUpRole = 'update';
            },
            closePopUp() {
                this.editPopUpRow = null;
            },
            anotherRowPopup(is_next) {
                let row_id = (this.editPopUpRow ? this.editPopUpRow.id : null);
                this.$root.anotherPopup(this.$root.listTableRows, row_id, is_next, this.rowIndexClicked);
            },
            addedRowPassFilters(tableRow) {
                let search = true, filtered = true, i, j;
                if (this.searchObject) {
                    let regKeyword = new RegExp(searchKeyword, 'gi');
                    search = false;
                    for (i in Obj) {
                        if (Obj[i] && Obj[i].search(regKeyword) > -1) {
                            search = true;
                            break;
                        }
                    }
                }
                if (this.$root.filters.length) {
                    for (i in filtersData) {
                        if (!filtersData[i].checkAll) {
                            filtered = false;
                            var key = filtersData[i].key;
                            for (j in filtersData[i].val) {
                                var val = filtersData[i]['val'][j];
                                if (val.value === Obj[key] && val.checked) {
                                    filtered = true;
                                    break;
                                }
                            }
                            if (filtered === false) {
                                break;
                            }
                        }
                    }
                }
                return search && filtered;
            },
            showSrcRecord(lnk, header, tableRow) {
                this.$emit('show-src-record', lnk, header, tableRow, 'list_view');
            },
            getViewDataObject(no_search, emit) {
                let hidden_columns = _.filter(this.tableMeta._fields, (el) => {
                    return !this.isShowField(el);
                });
                hidden_columns = _.map(hidden_columns, 'field');

                let res = this.$root.getTableViewData(this.tableMeta, {
                    hidden_columns: hidden_columns,
                    page: this.page,
                    sort: this.sort,
                    user_id: this.user.id,
                    search_keywords: !no_search ? this.searchObject.keyWords : null,
                    search_columns: this.searchObject.columns,
                    search_direct_row_id: this.searchObject.direct_row_id || null,
                });
                if (emit) {
                    eventBus.$emit('global-return-view-object', res, emit)
                } else {
                    return res;
                }
            },
            //save table status
            saveTableStatus() {
                //if logged User and is not any View
                if (
                    this.user.id
                    && !this.user.view_hash
                    && !this.user._is_folder_view
                    && !this.has_filters_url_preset
                    && (this.tableMeta._cur_settings && this.tableMeta._cur_settings.initial_view_id == -1) //and initial loading = default
                ) {
                    axios.post('/ajax/table/statuse', {
                        table_id: this.table_id,
                        status_data: this.getViewDataObject('no_search')
                    }).catch(errors => {
                        Swal('Info', getErrors(errors));
                    });
                }
            },

            //EVENT BUS HANDLERS
            syncListViewUpdateHandler(massTableRows) {
                _.each(massTableRows, (row_data) => {
                    let oldrow = _.find(this.$root.listTableRows, {id: row_data.id});
                    if (oldrow) {
                        this.$root.assignObject(row_data, oldrow);
                    }
                });
            },
            syncListViewDeleteHandler(rowId) {
                let idx = _.findIndex(this.$root.listTableRows, {id: rowId});
                if (idx > -1) {
                    this.tableMeta._view_rows_count--;
                    this.tableMeta._global_rows_count--;
                    this.$root.listTableRows.splice(idx, 1);
                }
            },
            addPopupClickedHandler() {
                this.editPopUpRow = this.$root.emptyObject(this.tableMeta);
                this.popUpRole = 'add';
            },
            deleteSelectedRowsHandler() {
                this.deleteSelectedRowsMix(this.$root.listTableRows, this.$root.request_params, this.table_id, this.tableMeta._view_rows_count);
            },
            toggleAllCheckBoxes(field, status, ids) {
                this.toggleAllCheckMix(this.$root.listTableRows, this.tableMeta, field, status, ids);
            },
            changedFilterHandler() {
                this.$nextTick(function () {
                    this.getTableData('filter');
                });
            },
            searchWordChangedHandler() {
                this.getTableData('search');
            },
            reloadPageHandler() {
                this.getTableData('page');
            },
            reloadFiltersHandler() {
                this.getTableData('reload-filter');
            },
            showPopupHandler(tableRow) {
                this.editPopUpRow = tableRow;
                this.popUpRole = 'update';
            },
            collaboratorChangedListDataHandler() {
                this.getTableData('collaborator');
            },
            reorderRows() {
                eventBus.$emit('reload-page');
            },
            showAddDDLOption(tableHeader, tableRow) {
                this.$emit('show-add-ddl-option', tableHeader, tableRow);
            },
        },
        mounted() {
            //sync data
            eventBus.$on('sync-list-view-update', this.syncListViewUpdateHandler);
            eventBus.$on('sync-list-view-delete', this.syncListViewDeleteHandler);

            //modify data
            eventBus.$on('add-popup-clicked', this.addPopupClickedHandler);

            //change query for getting data
            eventBus.$on('changed-filter', this.changedFilterHandler);
            eventBus.$on('search-word-changed', this.searchWordChangedHandler);
            eventBus.$on('row-per-page-changed', this.reloadPageHandler);
            eventBus.$on('reload-page', this.reloadPageHandler);
            eventBus.$on('reload-filters', this.reloadFiltersHandler);
            eventBus.$on('changed-page', this.changePage);

            //save table status
            eventBus.$on('save-table-status', this.saveTableStatus);

            //update row and formulas after selected LinkPopup.
            eventBus.$on('list-view-copy-row-sync', this.thenForInsert);
            eventBus.$on('list-view-insert-row-sync', this.thenForInsert);
            eventBus.$on('list-view-update-row-sync', this.thenForUpdate);
            eventBus.$on('list-view-delete-row-sync', this.thenForDelete);
            eventBus.$on('list-view-another-row', this.anotherRowPopup);

            //show searched row from Map
            eventBus.$on('show-popup', this.showPopupHandler);

            //table views
            eventBus.$on('global-get-view-object', this.getViewDataObject);

            //sync datas with collaborators
            eventBus.$on('collaborator-changed-list-data', this.collaboratorChangedListDataHandler);

        },
        beforeDestroy() {
            //sync data
            eventBus.$off('sync-list-view-update', this.syncListViewUpdateHandler);
            eventBus.$off('sync-list-view-delete', this.syncListViewDeleteHandler);

            //modify data
            eventBus.$off('add-popup-clicked', this.addPopupClickedHandler);

            //change query for getting data
            eventBus.$off('changed-filter', this.changedFilterHandler);
            eventBus.$off('search-word-changed', this.searchWordChangedHandler);
            eventBus.$off('row-per-page-changed', this.reloadPageHandler);
            eventBus.$off('reload-page', this.reloadPageHandler);
            eventBus.$off('reload-filters', this.reloadFiltersHandler);
            eventBus.$off('changed-page', this.changePage);

            //save table status
            eventBus.$off('save-table-status', this.saveTableStatus);

            //update row and formulas after selected LinkPopup.
            eventBus.$off('list-view-copy-row-sync', this.thenForInsert);
            eventBus.$off('list-view-insert-row-sync', this.thenForInsert);
            eventBus.$off('list-view-update-row-sync', this.thenForUpdate);
            eventBus.$off('list-view-delete-row-sync', this.thenForDelete);
            eventBus.$off('list-view-another-row', this.anotherRowPopup);

            //show searched row from Map
            eventBus.$off('show-popup', this.showPopupHandler);

            //table views
            eventBus.$off('global-get-view-object', this.getViewDataObject);

            //sync datas with collaborators
            eventBus.$off('collaborator-changed-list-data', this.collaboratorChangedListDataHandler);
        }
    }
</script>