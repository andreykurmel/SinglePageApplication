<script>

    import {eventBus} from '../../app';

    import {RefCondHelper} from "../../classes/helpers/RefCondHelper";
    import {RefCondEndpoints} from "../../classes/RefCondEndpoints";

    /**
     *  should be present:
     *
     *  this.$root.tableMeta: Object 0668835535
     *  this.is_incoming: Boolean
     *  this.filter_id: Number
     *  */
    export default {
        name: "RefConditionsMixin",
        data: function () {
            return {
                draw_table: true,
                activeTab: 'list',
                rc_for_copy: null,
                selectedRefGroup: 0,
                selectedRefItem: 0,
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
            tbm_rc_key() {
                return this.is_incoming
                    ? '__incoming_refconds'
                    : '_ref_conditions';
            },
            tbm_refconds() {
                return this.filter_id
                    ? _.filter(this.rcm_tableMeta[this.tbm_rc_key], {id: Number(this.filter_id)})
                    : this.rcm_tableMeta[this.tbm_rc_key];
            },
            sel_refcond() {
                return this.tbm_refconds[this.selectedRefGroup];
            },
            addingRowRC() {
                return {
                    active: this.selectedRefGroup > -1,
                    position: 'bottom'
                }
            },
            refRow() {
                let res = {};
                if (this.selectedRefCondItems[this.selectedRefItem]) {
                    res = this.selectedRefCondItems[this.selectedRefItem];
                }
                return res;
            },
            ref_tb_from_refcond() {
                if (this.sel_refcond) {
                    return this.is_incoming
                        ? this.sel_refcond._table
                        : this.sel_refcond._ref_table;
                } else {
                    return {_fields: []};
                }
            },
            selectedRefCondItems() {
                let sel_items = [];
                if (this.sel_refcond) {
                    sel_items = _.orderBy(this.sel_refcond._items, ['group_clause']);
                }
                return sel_items;
            },
        },
        methods: {
            changeTab(key) {
                this.activeTab = key;
                this.$emit('set-sub-tab', key);
            },
            refcondOpts(filter) {
                let refconds = this.tbm_refconds;
                if (this.sel_refcond && filter) {
                    refconds = _.filter(refconds, (refc) => {
                        return refc.id != this.sel_refcond.id && refc.ref_table_id == this.sel_refcond.ref_table_id;
                    });
                }
                return _.map(refconds, (refc) => {
                    return { val:refc.id, show:refc.name };
                });
            },
            refcondChange(opt) {
                let idx = _.findIndex(this.tbm_refconds, {id: Number(opt.val)});
                this.rowIndexClickedRefGroup(idx);
            },
            baseRefChange(opt) {
                this.sel_refcond.base_refcond_id = opt.val;
                this.updateRefGroup(this.sel_refcond);
            },
            copyLCs() {
                if (this.rc_for_copy && this.sel_refcond) {
                    this.$root.sm_msg_type = 2;
                    axios.post('/ajax/ref-condition/copy', {
                        from_rc_id: this.rc_for_copy,
                        to_rc_id: this.sel_refcond.id,
                    }).then(({ data }) => {
                        this.tbm_refconds[this.selectedRefGroup] = data;
                        //redraw
                        this.chngRefGr(this.selectedRefGroup);
                    }).catch(errors => {
                        Swal('Info', getErrors(errors));
                    }).finally(() => {
                        this.$root.sm_msg_type = 0;
                    });
                }
            },

            //row clicked functions
            updRefFormulas(old, changed) {
                _.each(this.rcm_tableMeta._fields, (fld) => {
                    if (fld.f_formula) {
                        fld.f_formula = String(fld.f_formula).replace('"'+old+'"', '"'+changed+'"');
                    }
                });
            },
            chngRefGr(idx) {
                if (this.tbm_refconds[this.selectedRefGroup] && this.tbm_refconds[idx]) {
                    this.tbm_refconds[idx]._out_uses = this.tbm_refconds[this.selectedRefGroup]._out_uses;
                }
                this.selectedRefGroup = null;
                this.$nextTick(() => {
                    this.selectedRefGroup = idx;
                });
            },
            rowIndexClickedRefGroup(index, row, noChange) {
                this.chngRefGr(index);
                this.selectedRefItem = -1;
                this.activeTab = noChange ? this.activeTab : 'lcs';
            },
            rowIndexClickedRefItem(index) {
                this.selectedRefItem = index;
            },
            closeDetails() {
                this.sel_refcond._out_uses = 0;
                this.rowIndexClickedRefGroup(this.selectedRefGroup, null, true);
            },


            //Table Ref Condition Functions
            addRefGroup(tableRow) {
                RefCondEndpoints.addRefGroup(this.rcm_tableMeta, tableRow)
                    .then(() => {
                        this.chngRefGr(this.tbm_refconds.length-1);
                    });
            },
            updateRefGroup(tableRow) {
                if (tableRow._changed_field === '_out_uses') {
                    let idx = _.findIndex(this.tbm_refconds, {id: Number(tableRow.id)});
                    if (idx !== this.selectedRefGroup) {
                        tableRow._out_uses = 1;
                    }
                    this.rowIndexClickedRefGroup(idx > -1 ? idx : this.selectedRefGroup, null, true);
                    return;
                }

                if (tableRow._changed_field === 'rc_static' && _.find(tableRow._items || [], {item_type: 'P2S'})) {
                    Swal('Info', 'Note: by turning on "STATIC" for the RC, all existing "P2S" type LCs defined for the RC will be removed.');
                }

                RefCondEndpoints.updateRefGroup(this.rcm_tableMeta, tableRow)
                    .then(({ data }) => {
                        let idx = _.findIndex(this.tbm_refconds, {id: Number(tableRow.id)});
                        if (idx > -1 && tableRow._changed_field === 'ref_table_id') {
                            eventBus.$emit('reload-meta-table');
                        }
                        eventBus.$emit('reload-page');
                        this.chngRefGr(this.selectedRefGroup);
                        if (tableRow._changed_field === 'name') {
                            this.updRefFormulas(tableRow._old_val, tableRow.name);
                        }
                    });
            },
            deleteRefGroup(tableRow) {
                RefCondEndpoints.deleteRefGroup(this.rcm_tableMeta, tableRow).then(() => {
                    let idx = _.findIndex(this.tbm_refconds, {id: Number(tableRow.id)});
                    if (idx > -1) {
                        let selectedId = this.tbm_refconds[this.selectedRefGroup];
                        selectedId = selectedId ? selectedId.id : null;

                        let newIdx = _.findIndex(this.tbm_refconds, {id: Number(selectedId)});
                        this.chngRefGr(newIdx);
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
                });
            },
            rowsReordered() {
                this.$root.sm_msg_type = 2;
                axios.get('/ajax/settings/load/ref-conds', {
                    params: { table_id: this.tableMeta.id }
                }).then(({ data }) => {
                    this.rcm_tableMeta._ref_conditions = data;
                    this.redrawTb();
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
            redrawTb() {
                this.draw_table = false;
                this.$nextTick(() => {
                    this.draw_table = true;
                });
            },

            //Table Ref Condition Item Functions
            addRefGroupItem(tableRow) {
                RefCondEndpoints.addRefGroupItem(this.sel_refcond, tableRow)
                    .then(() => {
                        this.chngRefGr(this.selectedRefGroup);
                    });
            },
            updateRefGroupItem(tableRow) {
                RefCondEndpoints.updateRefGroupItem(this.sel_refcond, tableRow)
                    .then(() => {
                        if (_.find(this.rcm_tableMeta._cond_formats, {status: 1})) {
                            eventBus.$emit('reload-page');
                        }
                        this.chngRefGr(this.selectedRefGroup);
                    });
            },
            deleteRefGroupItem(tableRow) {
                RefCondEndpoints.deleteRefGroupItem(this.sel_refcond, tableRow)
                    .then(() => {
                        this.selectedRefItem = -1;
                        this.chngRefGr(this.selectedRefGroup);
                    });
            },
        },
    }
</script>