<script>

    import {eventBus} from '../../app';

    /**
     *  should be present:
     *
     *  this.$root.tableMeta: Object 0668835535
     *  */
    export default {
        name: "RefConditionsMixin",
        data: function () {
            return {
                rc_for_copy: null,
                selectedRefGroup: -1,
                selectedRefItem: -1,
                selectedRefColumns: _.concat([
                    'table_field_id',
                    'compared_operator',
                    'compared_field_id',
                    'compared_value',
                ], this.$root.systemFields),
            }
        },
        computed: {
            rcm_tableMeta() {
                if (this.tableMeta !== undefined) {
                    return this.tableMeta;
                } else {
                    return this.$root.tableMeta;
                }
            },
            addingRowRC() {
                return {
                    active: this.selectedRefGroup > -1,
                    position: 'bottom'
                }
            },
            forbiddenRefItemColumns() {
                let arr = ['item_type','logic_operator','table_field_id',
                    'compared_operator','compared_field_id','compared_value',
                    'group_clause','group_logic'];
                if (this.selectedRefGroup > -1 && this.selectedRefItem > -1 && this.rcm_tableMeta._ref_conditions[this.selectedRefGroup]) {
                    switch (this.rcm_tableMeta._ref_conditions[this.selectedRefGroup]._items[this.selectedRefItem].item_type) {
                        case 'P2S': arr = ['item_type','logic_operator','compared_value','group_clause','group_logic'];
                            break;
                        case 'S2V': arr = ['item_type','logic_operator','table_field_id','group_clause','group_logic'];
                            break;
                    }
                }
                return _.concat(arr, this.$root.systemFields);
            },
            refRow() {
                let res = {};
                if (this.selectedRefGroup > -1 && this.selectedRefItem > -1) {
                    res = this.selectedRefCondItems[this.selectedRefItem];
                }
                return res;
            },
            ref_tb_from_refcond() {
                if (this.selectedRefGroup > -1 && this.rcm_tableMeta._ref_conditions[this.selectedRefGroup]) {
                    return this.rcm_tableMeta._ref_conditions[this.selectedRefGroup]._ref_table;
                } else {
                    return {_fields: []};
                }
            },
            selectedRefCondItems() {
                let sel_items = [];
                if (this.selectedRefGroup > -1 && this.rcm_tableMeta._ref_conditions[this.selectedRefGroup]) {
                    sel_items = _.orderBy(this.rcm_tableMeta._ref_conditions[this.selectedRefGroup]._items, ['group_clause']);
                }
                return sel_items;
            },
        },
        methods: {
            copyLCs() {
                if (this.rc_for_copy && this.rcm_tableMeta._ref_conditions[this.selectedRefGroup]) {
                    this.$root.sm_msg_type = 2;
                    axios.post('/ajax/ref-condition/copy', {
                        from_rc_id: this.rc_for_copy,
                        to_rc_id: this.rcm_tableMeta._ref_conditions[this.selectedRefGroup].id,
                    }).then(({ data }) => {
                        this.rcm_tableMeta._ref_conditions[this.selectedRefGroup] = data;
                        //redraw
                        let tmp_sel = this.selectedRefGroup;
                        this.selectedRefGroup = null;
                        this.$nextTick(() => {
                            this.selectedRefGroup = tmp_sel;
                        });
                    }).catch(errors => {
                        Swal('', getErrors(errors));
                    }).finally(() => {
                        this.$root.sm_msg_type = 0;
                    });
                }
            },

            //row clicked functions
            rowIndexClickedRefGroup(index) {
                this.selectedRefGroup = index;
                this.selectedRefItem = -1;
            },
            rowIndexClickedRefItem(index) {
                this.selectedRefItem = index;
            },


            //Table Ref Condition Functions
            addRefGroup(tableRow) {
                $.LoadingOverlay('show');

                let fields = _.cloneDeep(tableRow);//copy object
                this.$root.deleteSystemFields(fields);

                axios.post('/ajax/ref-condition', {
                    table_id: this.rcm_tableMeta.id,
                    fields: fields,
                }).then(({ data }) => {
                    let present_rows = _.cloneDeep(this.rcm_tableMeta._ref_conditions);
                    present_rows.push( data );
                    this.rcm_tableMeta._ref_conditions = present_rows;
                    this.selectedRefGroup = this.rcm_tableMeta._ref_conditions.length-1;
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
            updateRefGroup(tableRow) {
                $.LoadingOverlay('show');

                let group_id = tableRow.id;
                let fields = _.cloneDeep(tableRow);//copy object
                this.$root.deleteSystemFields(fields);

                axios.put('/ajax/ref-condition', {
                    table_ref_condition_id: group_id,
                    fields: fields
                }).then(({ data }) => {
                    let idx = _.findIndex(this.rcm_tableMeta._ref_conditions, {id: Number(group_id)});
                    if (idx > -1) {
                        if (tableRow._changed_field === 'ref_table_id') {
                            eventBus.$emit('reload-meta-table');
                            /*this.rcm_tableMeta._row_groups = _.filter(this.rcm_tableMeta._row_groups, (rg) => {
                                return rg.row_ref_condition_id != data.id;
                            });
                            _.each(this.rcm_tableMeta._fields, (fld) => {
                                fld._links = _.filter(fld._links, (rg) => {
                                    return rg.table_ref_condition_id != data.id;
                                });
                            });
                            _.each(this.rcm_tableMeta._ddls, (ddl) => {
                                ddl._references = _.filter(ddl._references, (rg) => {
                                    return rg.table_ref_condition_id != data.id;
                                });
                            });*/
                        }
                        this.rcm_tableMeta._ref_conditions[idx] = data;
                    }
                    eventBus.$emit('reload-page');
                    let tmp = this.selectedRefGroup;
                    this.selectedRefGroup = -1;
                    this.$nextTick(() => {
                        this.selectedRefGroup = tmp;
                    });
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
            deleteRefGroup(tableRow) {
                $.LoadingOverlay('show');
                axios.delete('/ajax/ref-condition', {
                    params: {
                        table_ref_condition_id: tableRow.id
                    }
                }).then(({ data }) => {
                    let idx = _.findIndex(this.rcm_tableMeta._ref_conditions, {id: Number(tableRow.id)});
                    if (idx > -1) {
                        let present_rows = _.cloneDeep(this.rcm_tableMeta._ref_conditions);
                        present_rows.splice(idx, 1);
                        this.rcm_tableMeta._ref_conditions = present_rows;
                        this.selectedRefGroup = -1;
                        this.selectedRefItem = -1;

                        _.each(this.rcm_tableMeta._fields, (fld) => {
                            _.each(fld._links, (lnk) => {
                                if (lnk.table_ref_condition_id === Number(tableRow.id)) {
                                    lnk.table_ref_condition_id = null;
                                    lnk.listing_field_id = null;
                                }
                            });
                        });

                        let present_ddls = _.cloneDeep(this.rcm_tableMeta._ddls);
                        _.each(present_ddls, (ddl) => {
                            _.remove(ddl._references, {table_ref_condition_id: Number(tableRow.id)});
                        });
                        this.rcm_tableMeta._ddls = present_ddls;

                        let present_groups = _.cloneDeep(this.rcm_tableMeta._row_groups);
                        _.remove(present_groups, (gr) => { return gr.row_ref_condition_id == tableRow.id });
                        this.rcm_tableMeta._row_groups = present_groups;

                        eventBus.$emit('empty-sel', '_row_groups');
                    }
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },

            //Table Ref Condition Item Functions
            addRefGroupItem(tableRow) {
                $.LoadingOverlay('show');

                let fields = _.cloneDeep(tableRow);//copy object
                this.$root.deleteSystemFields(fields);

                axios.post('/ajax/ref-condition/item', {
                    table_ref_condition_id: this.rcm_tableMeta._ref_conditions[this.selectedRefGroup].id,
                    fields: fields,
                }).then(({ data }) => {
                    this.rcm_tableMeta._ref_conditions[this.selectedRefGroup] = data;
                    this.rcm_reload();
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
            updateRefGroupItem(tableRow) {
                $.LoadingOverlay('show');

                let group_id = tableRow.id;
                let fields = _.cloneDeep(tableRow);//copy object
                this.$root.deleteSystemFields(fields);

                axios.put('/ajax/ref-condition/item', {
                    table_ref_condition_item_id: group_id,
                    fields: fields
                }).then(({ data }) => {
                    this.rcm_tableMeta._ref_conditions[this.selectedRefGroup] = data;
                    if (_.find(this.rcm_tableMeta._cond_formats, {status: 1})) {
                        eventBus.$emit('reload-page');
                    }
                    this.rcm_reload();
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
            deleteRefGroupItem(tableRow) {
                $.LoadingOverlay('show');
                axios.delete('/ajax/ref-condition/item', {
                    params: {
                        table_ref_condition_item_id: tableRow.id
                    }
                }).then(({ data }) => {
                    this.rcm_tableMeta._ref_conditions[this.selectedRefGroup] = data;
                    this.selectedRefItem = -1;
                    this.rcm_reload();
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },

            //redraw
            rcm_reload() {
                let tmp = this.selectedRefGroup;
                this.selectedRefGroup = null;
                this.$nextTick(() => {
                    this.selectedRefGroup = tmp;
                });
            },
        },
    }
</script>