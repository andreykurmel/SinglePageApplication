<template>
    <div id="tab-list-view" v-if="tableMeta" class="full-height">
        <custom-table
            v-if="draw_table"
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
            :search-object="searchObject"
            :user="user"
            :behavior="'list_view'"
            :adding-row="addingRow"
            :headers-with-check="user.is_admin ? tableMeta._js_headersWithCheck : []"
            :redraw_table="redraw_table"
            @added-row="insertRow"
            @updated-row="updateRow"
            @delete-row="deleteRow"
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
        ></custom-table>
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
            @popup-copy="copyRow"
            @popup-delete="deleteRow"
            @popup-close="closePopUp"
            @show-src-record="showSrcRecord"
            @another-row="anotherRowPopup"
        ></custom-edit-pop-up>
    </div>
</template>

<script>
    import {eventBus} from './../../../../app';

    import {SpecialFuncs} from '../../../../classes/SpecialFuncs';

    import CheckRowBackendMixin from './../../../_Mixins/CheckRowBackendMixin.vue';
    import TableSortMixin from './../../../_Mixins/TableSortMixin.vue';
    import TableCheckboxesMixin from './../../../_Mixins/TableCheckboxesMixin.vue';
    import IsShowFieldMixin from './../../../_Mixins/IsShowFieldMixin.vue';

    import CustomTable from './../../../CustomTable/CustomTable';
    import CustomEditPopUp from '../../../CustomPopup/CustomEditPopUp';
    import LinkPopUp from '../../../CustomPopup/LinkPopUp.vue';

    export default {
        name: "TabListView",
        mixins: [
            CheckRowBackendMixin,
            TableSortMixin,
            TableCheckboxesMixin,
            IsShowFieldMixin,
        ],
        components: {
            CustomTable,
            CustomEditPopUp,
            LinkPopUp,
        },
        data: function () {
            return {
                draw_table: true,
                redraw_table: 0,
                editPopUpRow: null,
                popUpRole: null,
                page: this.queryPreset.page || 1,
                addObject: this.$root.emptyObject(this.tableMeta),
                radius_search: {km: 0},
                first_init_view: null,
            }
        },
        props:{
            tableMeta: Object,
            settingsMeta: Object,
            fullWidthCell: Boolean,
            isPagination: Boolean,
            cellHeight: Number,
            maxCellRows: Number,
            table_id: Number,
            user: Object,
            addingRow: Object,
            searchObject: Object,
            queryPreset: Object,
            isVisible: Boolean,
            has_filters_url_preset: Boolean,
        },
        watch: {
            table_id: function(val) {
                this.page = 1;
                this.sort = [];
                this.first_init_view = this.tableMeta._cur_settings ? this.tableMeta._cur_settings.initial_view_id : null;

                if (val) {
                    this.getTableData('load');
                }
            },
            isVisible(val) {
                if (val) {
                    this.draw_table = true;
                    this.redraw_table++;
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
                        temp_filters: this.$root.temp_filters,
                        hidden_row_ids: this.tableMeta.__hidden_row_ids
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

                if (this.radius_search.km) {
                    let lat_header = _.find(this.tableMeta._fields, {is_lat_field: 1});
                    let long_header = _.find(this.tableMeta._fields, {is_long_field: 1});
                    if (lat_header && long_header) {
                        request.radius_search = {
                            km: this.radius_search.km,
                            center_lat: this.radius_search.center_lat,
                            center_long: this.radius_search.center_long,
                            field_lat: lat_header.field,
                            field_long: long_header.field,
                        };
                    }
                }

                request.first_init_view = this.first_init_view;

                if (this.$root.request_view_filtering) {
                    request.search_view = this.$root.request_view_filtering;
                }

                return request;
            },
            getTableData(change_type) {
                if (this.isVisible) {
                    if ($.inArray(change_type, ['collaborator']) === -1) {
                        $.LoadingOverlay('show');
                    }
                } else {
                    this.draw_table = false;
                }

                //goto First Page on filtering or searching
                if ($.inArray(change_type, ['filter','search','circle']) > -1) {
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
                    this.$root.listTableRows = data.rows;
                    this.$root.listTableRows_state = uuidv4();
                    this.$root.filters = SpecialFuncs.prepareFilters(this.$root.filters, data.filters);
                    this.$emit('update-meta-params', data);

                    //check visibility of row Groups
                    _.each(this.tableMeta._row_groups, (rg) => {
                        let data_rg = _.find(data.row_group_statuses, {id: Number(rg.id)});
                        rg._show_status = data_rg ? data_rg.show_status : 2;
                        rg._search_hidden = data_rg ? data_rg.search_hidden : false;
                        rg._filter_disabled = data_rg ? data_rg.filter_disabled : false;
                    });
                    this.tableMeta.__hidden_row_ids = data.hidden_row_ids;

                    //show that request params are changed
                    eventBus.$emit('new-request-params', change_type);

                    //save current table status for current user
                    this.saveTableStatus();
                    eventBus.$emit('page-reloaded', change_type);

                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
            copyRow(tableRow) {
                this.insertRow(tableRow, true);
            },
            insertRow(tableRow, no_close, no_new) {
                let fields = _.cloneDeep(tableRow);//copy object

                this.$root.sm_msg_type = 1;
                axios.post('/ajax/table-data', {
                    table_id: this.tableMeta.id,
                    fields: fields,
                    get_query: this.newRequestParams(true),
                }).then(({data}) => {
                    if (no_new) {
                        this.getTableData('rows-changed');
                        return;
                    }

                    if (data.rows && data.rows.length) {
                        data.rows[0]._is_new = 1;
                        this.$root.listTableRows.splice(0, 0, data.rows[0]);
                        this.editPopUpRow = no_close && data.rows[0] ? data.rows[0] : null;
                    }
                    this.$root.listTableRows_state = uuidv4();
                    this.$root.filters = SpecialFuncs.prepareFilters(this.$root.filters, data.filters);

                    data.rows_count = this.tableMeta._view_rows_count+1;
                    data.global_rows_count = this.tableMeta._global_rows_count+1;
                    this.$emit('update-meta-params', data);

                    eventBus.$emit('new-request-params', 'rows-changed');
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
            updateRow(tableRow) {
                let row_id = tableRow.id;
                let fields = _.cloneDeep(tableRow);//copy object
                this.$root.deleteSystemFields(fields);

                this.$root.sm_msg_type = 1;
                this.$root.prevent_cell_edit = true;
                axios.put('/ajax/table-data', {
                    table_id: this.tableMeta.id,
                    row_id: row_id,
                    fields: fields,
                    get_query: this.newRequestParams(true),
                }).then(({ data }) => {
                    let idx = _.findIndex(this.$root.listTableRows, {id: row_id});
                    if (idx > -1) {
                        if (data.rows && data.rows.length) {
                            this.$root.listTableRows[idx] = data.rows[0];
                            this.redraw_table++;
                        } else {
                            this.$root.listTableRows.splice(idx, 1);
                        }
                    }
                    this.$root.listTableRows_state = uuidv4();
                    this.$root.filters = SpecialFuncs.prepareFilters(this.$root.filters, data.filters);

                    data.rows_count = undefined;
                    data.global_rows_count = undefined;
                    this.$emit('update-meta-params', data);

                    eventBus.$emit('new-request-params', 'rows-changed');
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    eventBus.$emit('sync-favorites-update', tableRow);
                    this.$root.sm_msg_type = 0;
                    this.$root.prevent_cell_edit = false;
                });
            },
            deleteRow(tableRow) {
                this.$root.sm_msg_type = 1;
                axios.delete('/ajax/table-data', {
                    params: {
                        table_id: this.tableMeta.id,
                        row_id: tableRow.id,
                    }
                }).then(({ data }) => {
                    eventBus.$emit('sync-favorites-delete', tableRow);
                    let idx = _.findIndex(this.$root.listTableRows, {id: tableRow.id});
                    if (idx > -1) {
                        this.$root.listTableRows.splice(idx, 1);
                    }
                    this.$root.listTableRows_state = uuidv4();

                    data.rows_count = this.tableMeta._view_rows_count-1;
                    data.global_rows_count = this.tableMeta._global_rows_count-1;
                    this.$emit('update-meta-params', data);

                    eventBus.$emit('new-request-params', 'rows-changed');
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
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
                    Swal('', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
            toggleAllFavorites(status) {
                let rows_ids = [];
                let all_rows_checked = true;
                _.each(this.$root.listTableRows, (row) => {
                    if (row._checked_row) {
                        rows_ids.push(row.id);
                    } else {
                        all_rows_checked = false;
                    }
                });

                this.$root.sm_msg_type = 1;

                let request_params = _.cloneDeep(this.$root.request_params);
                request_params.page = 1;
                request_params.rows_per_page = 0;

                axios.put('/ajax/table-data/favorite/all', {
                    table_id: this.tableMeta.id,
                    rows_ids: (all_rows_checked ? null : rows_ids),
                    request_params: (all_rows_checked ? request_params : null),
                    status: (status ? 1 : 0)
                }).then(({ data }) => {
                    eventBus.$emit('reload-page');
                }).catch(errors => {
                    Swal('', getErrors(errors));
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
                    return this.isShowField(el) && !el.is_showed;
                });
                hidden_columns = _.map(hidden_columns, 'field');

                let order_columns = _.map(this.tableMeta._fields, (el) => {
                    return {
                        id: el.id,
                        field: el.field,
                        order: el.order,
                        width: el.width,
                    };
                });

                let panels_preset = {
                    top: $('#main_navbar').is(':visible'),
                    right: this.$root.isRightMenu,
                    left: this.$root.isLeftMenu,
                };

                let res = JSON.stringify({
                    user_id: this.user.id,
                    table_id: this.table_id,
                    page: this.page,
                    rows_per_page: this.tableMeta.rows_per_page,
                    sort: this.sort,
                    search_words: !no_search ? this.searchObject.keyWords : null,
                    search_columns: this.searchObject.columns,
                    row_id: this.searchObject.direct_row_id || null,
                    applied_filters: this.$root.filters,
                    hidden_columns: hidden_columns,
                    order_columns: order_columns,
                    hidden_row_ids: this.tableMeta.__hidden_row_ids,
                    panels_preset: panels_preset,
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
                    && !this.user.selected_view
                    && !this.user._is_folder_view
                    && !this.has_filters_url_preset
                    && (this.tableMeta._cur_settings && this.tableMeta._cur_settings.initial_view_id === 0) //and initial loading = default
                ) {
                    axios.post('/ajax/table/statuse', {
                        table_id: this.table_id,
                        status_data: this.getViewDataObject('no_search')
                    }).catch(errors => {
                        Swal('', getErrors(errors));
                    });
                }
            },

            //EVENT BUS HANDLERS
            syncListViewUpdateHandler(row) {
                let idx = _.findIndex(this.$root.listTableRows, {id: row.id});
                if (idx > -1) {
                    Object.assign(this.$root.listTableRows[idx], row);
                }
            },
            syncListViewDeleteHandler(row) {
                let idx = _.findIndex(this.$root.listTableRows, {id: row.id});
                if (idx > -1) {
                    this.tableMeta._view_rows_count--;
                    this.tableMeta._global_rows_count--;
                    this.$root.listTableRows.splice(idx, 1);
                }
            },
            changedPresetHandler() {
                this.page = this.queryPreset.page || 1;
                this.sort = this.queryPreset.sort || [];
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
            newSearchCircleHandler(radius_search) {
                let reload = this.radius_search.km !== radius_search.km
                || this.radius_search.center_lat !== radius_search.center_lat
                || this.radius_search.center_long !== radius_search.center_long;
                this.radius_search = radius_search;
                if (reload) {
                    this.getTableData('circle');
                }
            },
            collaboratorChangedListDataHandler() {
                this.getTableData('collaborator');
            },
            reorderRows() {
                eventBus.$emit('reload-page');
            },
        },
        mounted() {
            this.first_init_view = this.tableMeta._cur_settings ? this.tableMeta._cur_settings.initial_view_id : null;

            if (this.table_id) {
                this.$nextTick(() => {
                    this.getTableData('load');
                    this.changedPresetHandler();
                });
            }
            //sync data
            eventBus.$on('sync-list-view-update', this.syncListViewUpdateHandler);
            eventBus.$on('sync-list-view-delete', this.syncListViewDeleteHandler);

            //table preset
            eventBus.$on('changed-preset', this.changedPresetHandler);

            //modify data
            eventBus.$on('add-popup-clicked', this.addPopupClickedHandler);

            //change query for getting data
            eventBus.$on('changed-filter', this.changedFilterHandler);
            eventBus.$on('search-word-changed', this.searchWordChangedHandler);
            eventBus.$on('row-per-page-changed', this.reloadPageHandler);
            eventBus.$on('reload-page', this.reloadPageHandler);
            eventBus.$on('reload-filters', this.reloadFiltersHandler);

            //save table status
            eventBus.$on('save-table-status', this.saveTableStatus);

            //update row and formulas after selected LinkPopup.
            eventBus.$on('list-view-copy-row', this.copyRow);
            eventBus.$on('list-view-insert-row', this.insertRow);
            eventBus.$on('list-view-update-row', this.updateRow);
            eventBus.$on('list-view-delete-row', this.deleteRow);
            eventBus.$on('list-view-another-row', this.anotherRowPopup);

            //show searched row from Map
            eventBus.$on('show-popup', this.showPopupHandler);

            //table views
            eventBus.$on('global-get-view-object', this.getViewDataObject);

            //from maps tab
            eventBus.$on('new-search-circle', this.newSearchCircleHandler);

            //sync datas with collaborators
            eventBus.$on('collaborator-changed-list-data', this.collaboratorChangedListDataHandler);

        },
        beforeDestroy() {
            //sync data
            eventBus.$off('sync-list-view-update', this.syncListViewUpdateHandler);
            eventBus.$off('sync-list-view-delete', this.syncListViewDeleteHandler);

            //table preset
            eventBus.$off('changed-preset', this.changedPresetHandler);

            //modify data
            eventBus.$off('add-popup-clicked', this.addPopupClickedHandler);

            //change query for getting data
            eventBus.$off('changed-filter', this.changedFilterHandler);
            eventBus.$off('search-word-changed', this.searchWordChangedHandler);
            eventBus.$off('row-per-page-changed', this.reloadPageHandler);
            eventBus.$off('reload-page', this.reloadPageHandler);
            eventBus.$off('reload-filters', this.reloadFiltersHandler);

            //save table status
            eventBus.$off('save-table-status', this.saveTableStatus);

            //update row and formulas after selected LinkPopup.
            eventBus.$on('list-view-update-row', this.updateRow);
            eventBus.$on('list-view-another-row', this.anotherRowPopup);

            //show searched row from Map
            eventBus.$off('show-popup', this.showPopupHandler);

            //table views
            eventBus.$off('global-get-view-object', this.getViewDataObject);

            //from maps tab
            eventBus.$off('new-search-circle', this.newSearchCircleHandler);

            //sync datas with collaborators
            eventBus.$off('collaborator-changed-list-data', this.collaboratorChangedListDataHandler);
        }
    }
</script>