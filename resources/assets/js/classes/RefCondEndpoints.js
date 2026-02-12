import {RefCondHelper} from "./helpers/RefCondHelper";

export class RefCondEndpoints {

    /**
     *
     * @param {object} tableMeta
     * @param {object} tableRow
     * @returns {Promise<object>}
     */
    static addRefGroup(tableMeta, tableRow) {
        return new Promise((resolve) => {
            window.vueRootApp.sm_msg_type = 1;
            axios.post('/ajax/ref-condition', {
                table_id: tableMeta.id,
                fields: tableRow,
            }).then(({ data }) => {
                if (data._rcmap_positions) {
                    tableMeta._rcmap_positions = data._rcmap_positions;
                }
                let refCond = RefCondHelper.initUses(data.ref_cond);
                tableMeta._ref_conditions.push(refCond);
                resolve(refCond);
            }).catch(errors => {
                Swal('Info', getErrors(errors));
            }).finally(() => {
                window.vueRootApp.sm_msg_type = 0;
            });
        });
    }

    /**
     *
     * @param {object} tableMeta
     * @param {object} tableRow
     * @returns {Promise<object>}
     */
    static updateRefGroup(tableMeta, tableRow) {
        return new Promise((resolve) => {
            window.vueRootApp.sm_msg_type = 1;
            axios.put('/ajax/ref-condition', {
                table_ref_condition_id: tableRow.id,
                fields: tableRow,
            }).then(({ data }) => {
                if (data._rcmap_positions) {
                    tableMeta._rcmap_positions = data._rcmap_positions;
                }
                let idx = _.findIndex(tableMeta._ref_conditions, {id: Number(tableRow.id)});
                if (idx > -1) {
                    tableMeta._ref_conditions[idx] = {
                        ...tableMeta._ref_conditions[idx],
                        ...data.ref_cond
                    };
                }
                resolve(data.ref_cond);
            }).catch(errors => {
                Swal('Info', getErrors(errors));
            }).finally(() => {
                window.vueRootApp.sm_msg_type = 0;
            });
        });
    }

    /**
     *
     * @param {object} tableMeta
     * @param {object} tableRow
     * @returns {Promise<object>}
     */
    static deleteRefGroup(tableMeta, tableRow) {
        return new Promise((resolve) => {
            window.vueRootApp.sm_msg_type = 1;
            axios.delete('/ajax/ref-condition', {
                params: {
                    table_ref_condition_id: tableRow.id
                }
            }).then(({ data }) => {
                let idx = _.findIndex(tableMeta._ref_conditions, {id: Number(tableRow.id)});
                if (idx > -1) {
                    tableMeta._ref_conditions.splice(idx, 1);
                }
                resolve(data);
            }).catch(errors => {
                Swal('Info', getErrors(errors));
            }).finally(() => {
                window.vueRootApp.sm_msg_type = 0;
            });
        });
    }

    /**
     *
     * @param {object} refCond
     * @param {object} tableRow
     * @returns {Promise<object>}
     */
    static addRefGroupItem(refCond, tableRow) {
        return new Promise((resolve) => {
            window.vueRootApp.sm_msg_type = 1;
            axios.post('/ajax/ref-condition/item', {
                table_ref_condition_id: refCond.id,
                fields: tableRow,
            }).then(({ data }) => {
                refCond._items = data._items;
                refCond._ref_table = data._ref_table;
                resolve(data);
            }).catch(errors => {
                Swal('Info', getErrors(errors));
            }).finally(() => {
                window.vueRootApp.sm_msg_type = 0;
            });
        });
    }

    /**
     *
     * @param {object} refCond
     * @param {object} tableRow
     * @returns {Promise<object>}
     */
    static updateRefGroupItem(refCond, tableRow) {
        return new Promise((resolve) => {
            window.vueRootApp.sm_msg_type = 1;
            axios.put('/ajax/ref-condition/item', {
                table_ref_condition_item_id: tableRow.id,
                fields: tableRow,
            }).then(({ data }) => {
                refCond._items = data._items;
                refCond._ref_table = data._ref_table;
                resolve(data);
            }).catch(errors => {
                Swal('Info', getErrors(errors));
            }).finally(() => {
                window.vueRootApp.sm_msg_type = 0;
            });
        });
    }

    /**
     *
     * @param {object} refCond
     * @param {object} tableRow
     * @returns {Promise<object>}
     */
    static deleteRefGroupItem(refCond, tableRow) {
        return new Promise((resolve) => {
            window.vueRootApp.sm_msg_type = 1;
            axios.delete('/ajax/ref-condition/item', {
                params: {
                    table_ref_condition_item_id: tableRow.id
                }
            }).then(({ data }) => {
                refCond._items = data._items;
                refCond._ref_table = data._ref_table;
                resolve(data);
            }).catch(errors => {
                Swal('Info', getErrors(errors));
            }).finally(() => {
                window.vueRootApp.sm_msg_type = 0;
            });
        });
    }
}