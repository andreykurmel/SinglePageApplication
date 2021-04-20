<script>
    export default {
        methods: {
            deleteSelectedRowsMix(tableRows, requestParams, tableId, listViewRowsCount) {
                let rows_ids = [];
                let all_rows_checked = true;
                _.each(tableRows, (row) => {
                    if (row._checked_row) {
                        rows_ids.push(row.id);
                    } else {
                        all_rows_checked = false;
                    }
                });

                Swal({
                    title: 'Confirm to delete data for '+(all_rows_checked ? listViewRowsCount : rows_ids.length)+' records?',
                    confirmButtonClass: 'btn-danger',
                    confirmButtonText: 'Yes',
                    showCancelButton: true,
                    animation: 'slide-from-top'
                }).then(response => {
                    if (response.value) {
                        $.LoadingOverlay('show');

                        let request_params = _.cloneDeep(requestParams);
                        request_params.page = 1;
                        request_params.rows_per_page = 0;

                        axios.post('/ajax/table-data/delete-selected', {
                            table_id: tableId,
                            rows_ids: (all_rows_checked ? null : rows_ids),
                            request_params: (all_rows_checked ? request_params : null)
                        }).then(({ data }) => {
                            this.getTableData('page');
                        }).catch(errors => {
                            Swal('', getErrors(errors));
                        }).finally(() => $.LoadingOverlay('hide'));
                    }
                });
            },
            toggleAllCheckMix(tableRows, tableMeta, field, status, ids) {
                status = status ? status : null;
                let idx = _.findIndex(tableMeta._fields, {field: field});
                if (idx > -1) {
                    let tbHeader = tableMeta._fields[idx];
                    $.LoadingOverlay('show');
                    axios.put('/ajax/table-data/mass-check-box', {
                        table_id: tableMeta.id,
                        field_id: tbHeader.id,
                        row_ids: ids,
                        status: status
                    }).then(({ data }) => {
                        _.each(tableRows, (row) => {
                            if ($.inArray(row.id, ids) > -1) {
                                row[field] = status;
                            }
                        });
                    }).catch(errors => {
                        Swal('', getErrors(errors));
                    }).finally(() => {
                        $.LoadingOverlay('hide');
                    });
                }
            },
        }
    }
</script>