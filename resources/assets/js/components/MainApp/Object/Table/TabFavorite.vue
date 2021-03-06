<template>
    <div id="tab-favorite" v-if="tableMeta" class="full-height">
        <custom-table
            v-if="isVisible"
            :cell_component_name="$root.tdCellComponent(tableMeta.is_system)"
            :global-meta="tableMeta"
            :table-meta="tableMeta"
            :settings-meta="settingsMeta"
            :all-rows="$root.favoriteTableRows"
            :page="page"
            :rows-count="tableMeta._fav_rows_count || 0"
            :cell-height="$root.cellHeight"
            :max-cell-rows="$root.maxCellRows"
            :full-width-cell="fullWidthCell"
            :is-pagination="isPagination"
            :sort="sort"
            :user="user"
            :behavior="'favorite'"
            :headers-with-check="headersWithCheck"
            @updated-row="updateRow"
            @delete-row="deleteRow"
            @mass-updated-rows="massUpdatedRows"
            @delete-selected-rows="deleteSelectedRowsHandler"
            @change-page="changePage"
            @sort-by-field="sortByField"
            @sub-sort-by-field="subSortByField"
            @toggle-favorite-row="toggleFavoriteRow"
            @toggle-all-favorites="toggleAllFavorites"
            @row-index-clicked="rowIndexClicked"
            @check-row="toggleAllCheckBoxes"
            @show-add-ddl-option="showAddDDLOption"
        ></custom-table>
        <custom-edit-pop-up
            v-if="tableMeta && editPopUpRow"
            :global-meta="tableMeta"
            :table-meta="tableMeta"
            :table-row="editPopUpRow"
            :settings-meta="settingsMeta"
            :role="popUpRole"
            :input_component_name="$root.tdCellComponent(tableMeta.is_system)"
            :behavior="'favorite'"
            :user="user"
            :cell-height="$root.cellHeight"
            :max-cell-rows="$root.maxCellRows"
            @popup-update="updateRow"
            @popup-delete="deleteRow"
            @popup-close="closePopUp"
        ></custom-edit-pop-up>
    </div>
</template>

<script>
    import TableSortMixin from './../../../_Mixins/TableSortMixin';
    import TableCheckboxesMixin from './../../../_Mixins/TableCheckboxesMixin';

    import CustomTable from './../../../CustomTable/CustomTable';
    import CustomEditPopUp from '../../../CustomPopup/CustomEditPopUp';

    import {eventBus} from '../../../../app';

    import {SpecialFuncs} from '../../../../classes/SpecialFuncs';
    import {RefCondHelper} from "../../../../classes/helpers/RefCondHelper";

    export default {
        name: "TabFavorite",
        mixins: [
            TableSortMixin,
            TableCheckboxesMixin
        ],
        components: {
            CustomTable,
            CustomEditPopUp
        },
        data: function () {
            return {
                editPopUpRow: null,
                popUpRole: null,
                page: 1,
                headersWithCheck: this.getHeadersCheck()
            }
        },
        props:{
            tableMeta: Object,
            settingsMeta: Object,
            fullWidthCell: Boolean,
            isPagination: Boolean,
            table_id: Number,
            user:  Object,
            searchObject: Object,
            rowsLvCount: Number,
            isVisible: Boolean,
        },
        watch: {
            table_id: function(val) {
                this.page = 1;
                this.sort = [];
                this.headersWithCheck = this.getHeadersCheck();
                if (val) {
                    this.getTableData();
                }
            },
        },
        methods: {
            inArray(item, array) {
                return $.inArray(item, array) > -1;
            },
            getHeadersCheck() {
                return this.tableMeta.db_name === 'plans_view'
                    ? ['plan_basic', 'plan_standard', 'plan_advanced', 'plan_enterprise']
                    : [];
            },
            changePage(page) {
                this.page = page;
                this.getTableData();
            },
            newRequestParams() {
                return {
                    ...SpecialFuncs.tableMetaRequest(this.table_id),
                    ...{
                        page: this.page,
                        rows_per_page: this.tableMeta.rows_per_page,
                        sort: this.sort,
                        search_word: this.searchObject.keyWord,
                        search_columns: this.searchObject.columns,
                        row_id: this.searchObject.direct_row_id || null,
                        applied_filters: this.$root.filters,
                        only_favorites: true,
                    }
                };
            },
            getTableData(){
                this.$root.sm_msg_type = 1;
                axios.post('/ajax/table-data/get', this.newRequestParams()
                ).then(({ data }) => {
                    console.log('FavoriteData', data);
                    this.$root.favoriteTableRows = data.rows;
                    this.$emit('update-meta-params', {
                        fav_rows_count: data.rows_count,
                        version_hash: data.version_hash,
                    });
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
            setDefaultValues(tableRow) {
                if (this.tableMeta._current_right.default_values) {
                    _.each(this.tableMeta._current_right.default_values, (val, key) => {
                        if (!tableRow[key]) {
                            tableRow[key] = val;
                        }
                    });
                }
            },
            updateRow(tableRow) {
                this.massUpdatedRows([tableRow]);
            },
            massUpdatedRows(massTableRows) {
                let row_datas = {};
                _.each(massTableRows, (tableRow) => {
                    this.setDefaultValues(tableRow);

                    let row_id = tableRow.id;
                    let fields = _.cloneDeep(tableRow);//copy object
                    this.$root.deleteSystemFields(fields);
                    this.setDefault(fields);

                    //front-end RowGroups and CondFormats
                    RefCondHelper.updateRGandCFtoRow(this.tableMeta, tableRow);

                    row_datas[row_id] = fields;
                });

                this.$root.sm_msg_type = 1;
                this.$root.prevent_cell_edit = true;
                axios.put('/ajax/table-data/mass', {
                    table_id: this.tableMeta.id,
                    row_datas: row_datas,
                    get_query: this.newRequestParams(),
                }).then(({ data }) => {
                    _.each(row_datas, (row) => {
                        let oldrow = _.find(this.$root.favoriteTableRows, {id: row.id});
                        let newrow = _.find(data.rows, {id: row.id});
                        if (oldrow && newrow) {
                            this.$root.assignObject(newrow, oldrow);
                            this.redraw_table++;
                        }
                    });
                    this.$emit('update-meta-params', {
                        fav_rows_count: data.rows_count,
                        version_hash: data.version_hash,
                    });
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    eventBus.$emit('sync-list-view-update', row_datas);
                    this.$root.sm_msg_type = 0;
                    this.$root.prevent_cell_edit = false;
                });
            },
            deleteRow(tableRow) {
                this.$root.sm_msg_type = 1;
                axios.delete('/ajax/table-data', {
                    params: {
                        table_id: this.tableMeta.id,
                        row_id: tableRow.id
                    }
                }).then(({ data }) => {
                    eventBus.$emit('sync-list-view-delete', tableRow);
                    let idx = _.findIndex(this.$root.favoriteTableRows, {id: tableRow.id});
                    if (idx > -1) {
                        this.$root.favoriteTableRows.splice(idx, 1);
                    }
                    data.fav_rows_count = this.tableMeta._fav_rows_count-1;
                    data.global_rows_count = this.tableMeta._global_rows_count-1;
                    this.$emit('update-meta-params', data);
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
            setDefault(fields) {
                _.each(fields, (fld, key) => {
                    let idx = _.findIndex(this.tableMeta._fields, {field: key});
                    if (idx > -1 && this.tableMeta._fields[idx].f_default && !fld) {
                        fields[key] = this.tableMeta._fields[idx].f_default;
                    }
                });

                if (this.tableMeta.db_name === 'user_connections') {
                    fields['user_id'] = this.user.id;
                }
            },
            toggleFavoriteRow(tableRow) {
                this.$root.sm_msg_type = 1;
                axios.put('/ajax/table-data/favorite', {
                    table_id: this.tableMeta.id,
                    row_id: tableRow.id,
                    change: (tableRow._is_favorite ? 1 : 0)
                }).then(({ data }) => {
                    eventBus.$emit('sync-list-view-update', tableRow);
                    let idx = _.findIndex(this.$root.favoriteTableRows, {id: tableRow.id});
                    if (idx > -1) {
                        this.$root.favoriteTableRows.splice(idx, 1);
                    }
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
            toggleAllFavorites(status) {
                let check_obj = this.$root.checkedRowObject(this.$root.favoriteTableRows);

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
                    Swal('', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
            toggleAllCheckBoxes(field, status, ids) {
                this.toggleAllCheckMix(this.$root.favoriteTableRows, this.tableMeta, field, status, ids);
            },
            rowIndexClicked(index) {
                this.editPopUpRow = this.$root.favoriteTableRows[index];
                this.popUpRole = 'update';
            },
            closePopUp() {
                this.editPopUpRow = null;
            },

            //EVENT BUS HANDLERS
            syncFavoritesUpdateHandler(massTableRows) {
                _.each(massTableRows, (row_data, row_id) => {
                    let oldrow = _.find(this.$root.favoriteTableRows, {id: row_id});
                    if (oldrow) {
                        this.$root.assignObject(row_data, oldrow);
                    }
                });
            },
            syncFavoritesDeleteHandler(row) {
                let idx = _.findIndex(this.$root.favoriteTableRows, {id: row.id});
                if (idx > -1) {
                    this.tableMeta._fav_rows_count--;
                    this.tableMeta._global_rows_count--;
                    this.$root.favoriteTableRows.splice(idx, 1);
                }
            },
            syncFavoritesFavoriteHandler(row) {
                if (row._is_favorite) {
                    this.$root.favoriteTableRows.push(_.cloneDeep(row));
                } else {
                    let idx = _.findIndex(this.$root.favoriteTableRows, {id: row.id});
                    if (idx > -1) {
                        this.$root.favoriteTableRows.splice(idx, 1);
                    }
                }
            },
            deleteSelectedRowsHandler() {
                this.deleteSelectedRowsMix(this.$root.favoriteTableRows, this.$root.request_params, this.table_id, this.tableMeta._fav_rows_count);
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
            collaboratorChangedFavDataHandler() {
                if (this.$root.favoriteTableRows && this.$root.favoriteTableRows.length) {
                    this.changePage(this.page);
                }
            },
            showAddDDLOption(tableHeader, tableRow) {
                this.$emit('show-add-ddl-option', tableHeader, tableRow);
            },
        },
        mounted() {
            if (this.table_id) {
                this.getTableData();
            }

            eventBus.$on('sync-favorites-update', this.syncFavoritesUpdateHandler);
            eventBus.$on('sync-favorites-delete', this.syncFavoritesDeleteHandler);
            eventBus.$on('sync-favorites-favorite', this.syncFavoritesFavoriteHandler);

            eventBus.$on('changed-filter', this.changedFilterHandler);
            eventBus.$on('search-word-changed', this.searchWordChangedHandler);
            eventBus.$on('row-per-page-changed', this.reloadPageHandler);
            eventBus.$on('reload-page', this.reloadPageHandler);

            //sync datas with collaborators
            eventBus.$on('collaborator-changed-fav-data', this.collaboratorChangedFavDataHandler);
        },
        beforeDestroy() {
            eventBus.$off('sync-favorites-update', this.syncFavoritesUpdateHandler);
            eventBus.$off('sync-favorites-delete', this.syncFavoritesDeleteHandler);
            eventBus.$off('sync-favorites-favorite', this.syncFavoritesFavoriteHandler);

            eventBus.$off('changed-filter', this.changedFilterHandler);
            eventBus.$off('search-word-changed', this.searchWordChangedHandler);
            eventBus.$off('row-per-page-changed', this.reloadPageHandler);
            eventBus.$off('reload-page', this.reloadPageHandler);

            //sync datas with collaborators
            eventBus.$off('collaborator-changed-fav-data', this.collaboratorChangedFavDataHandler);
        }
    }
</script>