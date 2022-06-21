
<script>
    export default {
        methods: {
            //Api Functions
            _addApiRow(tableRow, array_api_keys) {
                $.LoadingOverlay('show');

                let fields = _.cloneDeep(tableRow);//copy object
                this.$root.deleteSystemFields(fields);

                axios.post('/ajax/user-api-key', {
                    fields: fields
                }).then(({ data }) => {
                    array_api_keys.push( data );
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
            _updateApiRow(tableRow, array_api_keys) {
                $.LoadingOverlay('show');
                axios.put('/ajax/user-api-key', {
                    user_api_id: tableRow.id,
                    fields: tableRow
                }).then(({ data }) => {
                    if (tableRow._changed_field == 'is_active') {
                        _.each(array_api_keys, (el) => {
                            el.is_active = el.id == tableRow.id ? 1 : 0;
                        });
                    }
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
            _deleteApiRow(tableRow, array_api_keys) {
                $.LoadingOverlay('show');
                let row_id = tableRow.id;
                axios.delete('/ajax/user-api-key', {
                    params: {
                        user_api_id: row_id,
                    }
                }).then(({ data }) => {
                    let idx = _.findIndex(array_api_keys, {id: Number(tableRow.id)});
                    if (idx > -1) {
                        array_api_keys.splice(idx, 1);
                    }
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },

            addGoogleApiRow(tableRow) {
                tableRow['type'] = 'google';
                this._addApiRow(tableRow, this.$root.user._google_api_keys);
            },
            updateGoogleApiRow(tableRow) {
                tableRow['type'] = 'google';
                this._updateApiRow(tableRow, this.$root.user._google_api_keys);
            },
            deleteGoogleApiRow(tableRow) {
                this._deleteApiRow(tableRow, this.$root.user._google_api_keys);
            },

            addGridApiRow(tableRow) {
                tableRow['type'] = 'sendgrid';
                this._addApiRow(tableRow, this.$root.user._sendgrid_api_keys);
            },
            updateGridApiRow(tableRow) {
                tableRow['type'] = 'sendgrid';
                this._updateApiRow(tableRow, this.$root.user._sendgrid_api_keys);
            },
            deleteGridApiRow(tableRow) {
                this._deleteApiRow(tableRow, this.$root.user._sendgrid_api_keys);
            },

            addExtracttableApiRow(tableRow) {
                tableRow['type'] = 'extracttable';
                this._addApiRow(tableRow, this.$root.user._extracttable_api_keys);
            },
            updateExtracttableApiRow(tableRow) {
                tableRow['type'] = 'extracttable';
                this._updateApiRow(tableRow, this.$root.user._extracttable_api_keys);
            },
            deleteExtracttableApiRow(tableRow) {
                this._deleteApiRow(tableRow, this.$root.user._extracttable_api_keys);
            },

            addAirtableApiRow(tableRow) {
                tableRow['type'] = 'airtable';
                tableRow['air_type'] = tableRow['air_type'] || 'public_rest';
                this._addApiRow(tableRow, this.$root.user._airtable_api_keys);
            },
            updateAirtableApiRow(tableRow) {
                tableRow['type'] = 'airtable';
                tableRow['air_type'] = tableRow['air_type'] || 'public_rest';
                this._updateApiRow(tableRow, this.$root.user._airtable_api_keys);
            },
            deleteAirtableApiRow(tableRow) {
                this._deleteApiRow(tableRow, this.$root.user._airtable_api_keys);
            },
        },
    }
</script>