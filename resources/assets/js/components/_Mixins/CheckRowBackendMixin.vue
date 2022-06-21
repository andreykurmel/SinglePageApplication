<script>
    import {SpecialFuncs} from '../../classes/SpecialFuncs';

    export default {
        data: function () {
            return {}
        },
        methods: {
            //backend autocomplete
            checkRowOnBackend(table_id, row_object, linked_params, specialParams, always) {
                if (table_id && row_object && (!row_object.id || always)) {
                    let updrow = _.cloneDeep(row_object);
                    if (always) {
                        updrow.id = null;
                    }
                    return new Promise((resolve) => {
                        axios.post('/ajax/table-data/check-row-on-backend', {
                            table_id: table_id,
                            updated_row: updrow,
                            linked_params: linked_params,
                            dcr_permission_id: specialParams ? specialParams.dcr_uid : null,
                            special_params: SpecialFuncs.specialParams('', specialParams ? specialParams.dcr_linked_id : ''),
                        }).then(({data}) => {
                            this.$set(row_object, '_check_sign', uuidv4());
                            this.$root.assignObject(data.row, row_object);
                            resolve(data);
                        }).catch(errors => {
                            Swal('', getErrors(errors));
                        });
                    });
                }
            },
        },
    }
</script>