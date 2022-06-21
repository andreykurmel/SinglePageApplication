import {SpecialFuncs} from "../SpecialFuncs";

export class RefCondHelper {

    /**
     *
     * @param {object} tableMeta
     */
    static setUses(tableMeta)
    {
        _.each(tableMeta._ref_conditions, (rc) => {
            //Row Groups
            let rGroups = _.filter(tableMeta._row_groups, (rowG) => {
                return rowG.row_ref_condition_id == rc.id;
            });
            rc._uses_rows = _.map(rGroups, (rowG) => {
                return {id: rowG.id, name: rowG.name};
            });

            //Links
            let rLinks = [];
            _.each(tableMeta._fields, (field) => {
                _.each(field._links, (link) => {
                    if (link.link_type === 'Record' && link.table_ref_condition_id == rc.id) {
                        rLinks.push(link);
                    }
                });
            });
            rc._uses_links = _.map(rLinks, (link) => {
                return {id: link.id, table_field_id: link.table_field_id, name: link.icon};
            });

            //DDLs
            let rDdls = _.filter(tableMeta._ddls, (ddl) => {
                return _.find(ddl._references, (ref) => {
                    return ref.table_ref_condition_id == rc.id;
                });
            });
            rc._uses_ddls = _.map(rDdls, (ddl) => {
                return {id: ddl.id, name: ddl.name};
            });
        });
    }

    /**
     *
     * @param tableMeta
     * @param updatedRow
     */
    static updateRGandCFtoRow(tableMeta, updatedRow)
    {
        let applied_row_groups = [];
        _.each(tableMeta._row_groups, (rg) => {
            if (this._checkRowGroup(tableMeta, rg, updatedRow)) {
                applied_row_groups.push(rg.id);
            }
        });
        updatedRow._applied_row_groups = applied_row_groups;

        let applied_cond_formats = [];
        _.each(tableMeta._cond_formats, (cf) => {
            let found = applied_row_groups.indexOf(cf.table_row_group_id) > -1;
            if (!cf.table_row_group_id || found) {
                applied_cond_formats.push(cf.id);
            }
        });
        updatedRow._applied_cond_formats = applied_cond_formats;
    }

    /**
     *
     * @param tableMeta
     * @param rg
     * @param row
     * @returns {*|boolean}
     * @private
     */
    static _checkRowGroup(tableMeta, rg, row)
    {
        let present_regular = _.find(rg._regulars, (regular) => {
            return regular.field_value == row[regular.list_field];
        });

        let ref_cond = _.find(tableMeta._ref_conditions, {id: rg.row_ref_condition_id});
        return present_regular || this._checkRefCond(ref_cond, row);
    }

    /**
     *
     * @param ref_cond
     * @param row
     * @returns {boolean}
     * @private
     */
    static _checkRefCond(ref_cond, row)
    {
        let found = null;
        if (ref_cond.table_id == ref_cond.ref_table_id) {

            if (ref_cond._items && ref_cond._items.length) {
                let grouped_conditions = _.groupBy(ref_cond._items, 'group_clause');
                _.each(grouped_conditions, (conditions) => {

                    let lo_operator = 'AND';
                    _.each(conditions, (cond) => {
                        lo_operator = cond.group_logic === 'OR' ? 'OR' : lo_operator;
                    });

                    //
                    let sub_found = null;
                    _.each(conditions, (cond_item) => {
                        if (this._correctRefItem(cond_item)) {
                            let left_part = row[cond_item._compared_field.field];
                            let right_part = cond_item.item_type == 'P2S' ? row[cond_item._field.field] : cond_item.compared_value;
                            let comparison = false;
                            switch (cond_item.compared_operator) {
                                case '=': comparison = left_part == right_part; break;
                                case '!=': comparison = left_part != right_part; break;
                                case '>': comparison = left_part < right_part; break;
                                case '<': comparison = left_part > right_part; break;
                                case 'like': comparison = String(left_part).indexOf(right_part) > -1; break;
                                case 'not like': comparison = String(left_part).indexOf(right_part) === -1; break;
                            }
                            //console.log('"'+ref_cond.name+'" -> ', left_part, cond_item.compared_operator, right_part, comparison);

                            sub_found = sub_found === null ? comparison : sub_found;
                            sub_found = cond_item.logic_operator === 'OR' ? (sub_found || comparison) : (sub_found && comparison);
                        }
                    });

                    found = found === null ? sub_found : found;
                    found = lo_operator === 'OR' ? (found || sub_found) : (found && sub_found);

                });
            } else {
                found = true;
            }

        } else {
            found = false;
        }
        return found;
    }

    /**
     *
     * @param cond_item
     * @returns {*|boolean}
     * @private
     */
    static _correctRefItem(cond_item)
    {
        return cond_item._compared_field
            && (
                (cond_item.item_type === 'P2S' && cond_item._field)
                ||
                (cond_item.item_type === 'S2V') //$ref_item->compared_value can be null
            );
    }
}