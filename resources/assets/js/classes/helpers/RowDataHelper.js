import {SpecialFuncs} from "../SpecialFuncs";

export class RowDataHelper {

    /**
     * 
     * @param {Object} tableMeta
     * @param {Array} tableRows
     */
    static fillCanEdits(tableMeta, tableRows) {
        if (!tableMeta || !tableRows) {
            return;
        }
        _.each(tableRows, (tableRow) => {
            tableRow.__can_edit = this.canEditRow(tableMeta, tableRow);
        });
        _.each(tableMeta._fields, (header) => {
            header.__can_edit = this.canEditColumn(tableMeta, header);
        });
    }

    /**
     *
     * @param {Object} tableMeta
     * @param {Object} tableRow
     * @returns {boolean}
     */
    static canEditRow(tableMeta, tableRow) {
        //all needed data present
        if (!tableRow._applied_row_groups) {
            tableRow._applied_row_groups = [];
        }
        
        // manager can edit additionally
        if (SpecialFuncs.managerOfRow(tableMeta, tableRow)) { return true; }

        //can edit only owner OR RowGroups are not set OR not-saved row (is new)
        let rg_present = tableMeta._current_right
            && tableMeta._current_right.view_row_groups
            && tableMeta._current_right.view_row_groups.length;
        if (this._tableOwner(tableMeta) || !rg_present || !to_float(tableRow.id)) {
            return true;
        }
        // OR user with available rights for edit Row
        let edit_groups = (tableMeta._current_right ? tableMeta._current_right.edit_row_groups || [] : []);
        let has_edit_groups = tableRow._applied_row_groups.filter((gr_id) => {
            return edit_groups.includes(gr_id);
        });
        return !!has_edit_groups.length;
    }

    /**
     *
     * @param {Object} tableMeta
     * @param {Object} tableHeader
     * @returns {boolean}
     */
    static canEditColumn(tableMeta, tableHeader) {
        return ( //can edit only owner
                this._tableOwner(tableMeta)
                || // OR user with available rights for edit Column
                (tableMeta._current_right && tableMeta._current_right.edit_fields.indexOf(tableHeader.field) > -1)
            )
            &&
            vueRootApp.systemFields.indexOf(tableHeader.field) == -1;
    }

    /**
     *
     * @param format
     * @returns {number}
     */
    static _tableOwner(tableMeta) {
        return tableMeta._is_owner
            && ! vueRootApp.user.see_view
            && ! vueRootApp.is_dcr_page;
    }
}