<script>
    export default {
        data: function () {
            return {}
        },
        methods: {
            //backend autocomplete
            checkRowOnBackend(table_id, row_object, linked_params, specials) {
                let clear_row = _.cloneDeep(row_object);//copy object
                clear_row.id = null;
                return new Promise((resolve) => {
                    axios.post('/ajax/table-data/check-row-on-backend', {
                        table_id: table_id,
                        updated_row: clear_row,
                        linked_params: linked_params,
                        dcr_permission_id: specials ? specials.dcr_permis_id : null,
                    }).then(({data}) => {
                        this.$set(row_object, '_check_sign', uuidv4());
                        _.each(data.row, (val, key) => {
                            if (key !== 'id') {
                                this.$set(row_object, key, val);
                            }
                        });
                        resolve(data);
                    }).catch(errors => {
                        Swal('', getErrors(errors));
                    });
                });
            },
        },
    }
</script>