<script>
    export default {
        methods: {
            deleteSelectedRowsMix(tableRows, requestParams, tableId, listViewRowsCount) {
                let check_obj = this.$root.checkedRowObject(tableRows);

                Swal({
                    title: 'Confirm to delete data for '+(check_obj.all_checked ? listViewRowsCount : check_obj.rows_ids.length)+' records?',
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
                            rows_ids: (check_obj.all_checked ? null : check_obj.rows_ids),
                            request_params: (check_obj.all_checked ? request_params : null)
                        }).then(({ data }) => {
                            if (check_obj.all_checked) {
                                Swal('Deletion process is started. Table will be reloaded after finishing.');
                            } else {
                                this.getTableData('page');
                            }
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