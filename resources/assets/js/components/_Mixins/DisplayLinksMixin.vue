<script>
    /**
     *  should be present:
     *
     *  this.settingsMeta: Object
     *  this.$root.tableMeta: Object
     *  this.table_id: Number
     *  this.selectedCol: Number
     *  */
    export default {
        data: function () {
            return {
                selectedLink: -1,
                availableLinks: [
                    'link_type',
                    'icon',
                    'popup_display',
                    'show_sum',
                ],
            }
        },
        computed: {
            dli_metaTable() {
                if (this.linkTableMeta !== undefined) {
                    return this.linkTableMeta;
                } else if (this.globalMeta !== undefined) {
                    return this.globalMeta;
                } else {
                    return this.$root.tableMeta;
                }
            },
            availableLinkColumns() {
                let arr = [];
                let curlink = this.selectedCol > -1 && this.selectedLink > -1 && this.dli_metaTable._fields[this.selectedCol] ? this.dli_metaTable._fields[this.selectedCol]._links[this.selectedLink] : null;
                if (curlink) {
                    switch (curlink.link_type) {
                        case 'Record':
                        case 'RorT':
                            arr = ['add_record_limit','tooltip','table_ref_condition_id','listing_field_id',
                                'always_available','link_preview_fields','link_preview_show_flds'];
                            break;
                        case 'Table':
                            arr = ['add_record_limit','tooltip','table_ref_condition_id','always_available',
                                'link_preview_fields','link_preview_show_flds'];
                            break;
                        case 'Web':
                            arr = ['add_record_limit','tooltip','address_field_id','web_prefix'];
                            break;
                        case 'App':
                            if (curlink.table_app_id == this.$root.settingsMeta.payment_app_id) {
                                arr = ['tooltip','table_app_id','payment_method_fld_id','payment_paypal_keys_id','payment_stripe_keys_id',
                                    'payment_amount_fld_id','payment_history_payee_fld_id','payment_history_amount_fld_id','payment_history_date_fld_id'];
                            } else {
                                arr = ['add_record_limit','tooltip','table_app_id','always_available'];
                            }
                            break;
                        case 'GMap':
                        case 'GEarth':
                            arr = ['add_record_limit','tooltip','link_field_lat','link_field_lng','link_field_address'];
                            break;
                    }
                }
                return arr;
            },
            linkRow() {
                let res = {};
                if (this.selectedCol > -1 && this.selectedLink > -1) {
                    res = this.dli_metaTable._fields[this.selectedCol]._links[this.selectedLink];
                }
                return res;
            },
            addingRow() {
                return {
                    active: this.selectedCol > -1,
                    position: 'bottom'
                }
            },
        },
        watch: {
            table_id: function(val) {
                this.selectedCol = -1;
            }
        },
        methods: {
            //sys methods
            rowIndexClickedLink(index) {
                this.selectedLink = -1;
                this.$nextTick(() => {
                    this.selectedLink = index;
                });
            },

            //Settings Column Links Functions
            addLink(tableRow) {
                $.LoadingOverlay('show');

                if ($.inArray(tableRow.link_type, ['GMap','GEarth']) > -1) {
                    tableRow.icon = tableRow.link_type === 'GMap' ? 'map' : 'earth';
                }

                let fields = _.cloneDeep(tableRow);//copy object
                this.$root.deleteSystemFields(fields);

                axios.post('/ajax/settings/data/link', {
                    table_field_id: this.dli_metaTable._fields[this.selectedCol].id,
                    fields: fields,
                }).then(({ data }) => {
                    this.dli_metaTable._fields[this.selectedCol].active_links = 1;
                    let present_rows = _.cloneDeep(this.dli_metaTable._fields[this.selectedCol]._links);
                    present_rows.push( data );
                    this.dli_metaTable._fields[this.selectedCol]._links = present_rows;
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
            updateLink(tableRow) {
                $.LoadingOverlay('show');

                let link_id = tableRow.id;
                let fields = _.cloneDeep(tableRow);//copy object
                this.$root.deleteSystemFields(fields);

                axios.put('/ajax/settings/data/link', {
                    table_link_id: link_id,
                    fields: fields
                }).then(({ data }) => {
                    _.each(data, (val, key) => {
                        tableRow[key] = val;
                    });
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
            deleteLink(tableRow) {
                $.LoadingOverlay('show');
                axios.delete('/ajax/settings/data/link', {
                    params: {
                        table_link_id: tableRow.id
                    }
                }).then(({ data }) => {
                    let idx = _.findIndex(this.dli_metaTable._fields[this.selectedCol]._links, {id: Number(tableRow.id)});
                    if (idx > -1) {
                        let present_rows = _.cloneDeep(this.dli_metaTable._fields[this.selectedCol]._links);
                        present_rows.splice(idx, 1);
                        this.dli_metaTable._fields[this.selectedCol]._links = present_rows;
                    }
                    this.selectedLink = -1;
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
        },
    }
</script>