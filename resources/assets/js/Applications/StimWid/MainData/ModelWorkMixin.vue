<script>
    export default {
        methods: {
            copyModelMixin(found_model, app_table, additional_tables) {
                $.LoadingOverlay('show');
                return axios.post('?method=copy_model', {
                    id: found_model._id,
                    main_table: app_table,
                    additional_tables: additional_tables,
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
            deleteModelMixin(found_model, app_table, additional_tables) {
                $.LoadingOverlay('show');
                return axios.post('?method=delete_model', {
                    id: found_model._id,
                    main_table: app_table,
                    additional_tables: additional_tables,
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
        },
    }
</script>