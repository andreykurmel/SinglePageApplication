<script>
    import {SpecialFuncs} from '../../classes/SpecialFuncs';

    export default {
        data: function () {
            return {}
        },
        methods: {
            //backend autocomplete
            checkRowOnBackend(table_id, row_object, linked_params, specialParams, always, dcrLinkedRows) {
                if (table_id && row_object && (!parseInt(row_object.id) || always)) {
                    let updrow = _.cloneDeep(row_object);
                    if (always) {
                        updrow.id = null;
                    }
                    let dcrLinkedId = specialParams ? specialParams.dcr_linked_id : '';
                    let dcrParentRow = specialParams ? specialParams.dcr_parent_row : '';
                    return new Promise((resolve) => {
                        axios.post('/ajax/table-data/check-row-on-backend', {
                            table_id: table_id,
                            updated_row: updrow,
                            linked_params: linked_params,
                            dcr_permission_id: specialParams ? specialParams.dcr_uid : null,
                            dcr_rows_linked: specialParams ? specialParams.dcr_rows_linked : null,
                            special_params: SpecialFuncs.specialParams('', dcrLinkedId, dcrParentRow),
                        }).then(({data}) => {
                            this.$set(row_object, '_check_sign', uuidv4());
                            this.$root.assignObject(data.row, row_object);
                            resolve(data);
                        }).catch(errors => {
                            Swal('Info', getErrors(errors));
                        });
                    });
                }
            },
        },
    }
</script>