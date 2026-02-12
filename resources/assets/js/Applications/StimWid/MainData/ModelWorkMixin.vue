<script>
    export default {
        methods: {
            /**
             *
             * @param {Object} found_model
             * @param {String} app_table
             * @param {Array} additional_tables // [ {to_copy:Boolean, table:String}, ... ]
             * @param {String} user_id
             * @returns {Promise<unknown>}
             */
            copyModelMixin(found_model, app_table, additional_tables, user_id) {
                let filtered = _.filter(additional_tables, (at) => {
                    return !!at.to_copy;
                });

                $.LoadingOverlay('show');
                return axios.post('?method=copy_model', {
                    id: found_model._id,
                    main_table: app_table,
                    additional_tables: _.map(filtered, 'table'),
                    new_owner: user_id,
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
            /**
             *
             * @param {Object} found_model
             * @param {String} app_table
             * @param {Array} additional_tables // [ {to_del:Boolean, table:String}, ... ]
             * @returns {Promise<unknown>}
             */
            deleteModelMixin(found_model, app_table, additional_tables) {
                let filtered = _.filter(additional_tables, (at) => {
                    return !!at.to_del;
                });

                $.LoadingOverlay('show');
                return axios.post('?method=delete_model', {
                    id: found_model._id,
                    main_table: app_table,
                    additional_tables: _.map(filtered, 'table'),
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
        },
    }
</script>