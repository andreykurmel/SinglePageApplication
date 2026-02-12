<script>
/**
 *
 */
export default {
    data: function () {
        return {
            duplicator: null,
            total_histories: 0,
            histories: [],
            field_history: [],
            current_history: null,
        }
    },
    methods: {
        histUpdated() {
            this.$emit('hist-updated');
        },
        delHistory(hist) {
            $.LoadingOverlay('show');
            axios.delete('/ajax/history', {
                params: {
                    history_id: hist.id
                }
            }).then(({ data }) => {
                let idx = _.findIndex(this.field_history, {id: hist.id});
                if (idx > -1) {
                    this.field_history.splice(idx, 1);
                }
                this.histUpdated();
            }).catch(errors => {
                Swal('Info', getErrors(errors));
            }).finally(() => {
                $.LoadingOverlay('hide');
            });
        },
        loadOneHistory(table_id, field_id, row_id) {
            axios.get('/ajax/history', {
                params: {
                    table_id: table_id,
                    table_field_id: field_id,
                    row_id: row_id
                }
            }).then(({ data }) => {
                this.field_history = [];
                this.current_history = null;
                this.$nextTick(() => {
                    this.field_history = data.history;
                    this.current_history = data.current;
                    this.duplicator = null;
                });
            }).catch(errors => {
                Swal('Info', getErrors(errors));
            }).finally(() => {
                $.LoadingOverlay('hide');
            });
        },
        loadAllHistories(table_id, row_id, page) {
            axios.get('/ajax/history/all', {
                params: {
                    table_id: table_id,
                    row_id: row_id,
                    page: page || 1,
                }
            }).then(({ data }) => {
                this.histories = [];
                this.total_histories = 0;
                this.$nextTick(() => {
                    if (page > 1) {
                        this.histories = _.concat(this.histories, data.histories);
                    } else {
                        this.histories = data.histories;
                    }
                    this.total_histories = data.count;
                    this.duplicator = null;
                });
            }).catch(errors => {
                Swal('Info', getErrors(errors));
            }).finally(() => {
                $.LoadingOverlay('hide');
            });
        }
    },
}
</script>