import {MetaTabldaRows} from './MetaTabldaRows';
import {MetaTabldaTable} from './MetaTabldaTable';

/**
 * Master Table Row
 */
export class FoundModel {
    /**
     * constructor
     * @param obj
     */
    constructor(obj) {
        this._id = null;
        this.meta = null;
        this.rows = null;
        this.is_master_table = false;
        this.selected_html = '';
    }

    /**
     * Prepare.
     * @param stimLinkParams
     * @param user
     * @param master
     */
    prepare(stimLinkParams, user, master) {
        let uh = user.view_all ? user.view_all.hash : null;
        this.meta = new MetaTabldaTable({
            table_id: stimLinkParams.table_id,
            user_id: user.id,
            user_hash: uh,
        });
        this.rows = new MetaTabldaRows(stimLinkParams, uh);
        this.is_master_table = !!master;
    }

    /**
     * setSelectedRow
     * @param row
     */
    setSelectedRow(row) {
        this._id = row ? row.id : null;
        this.rows.setDirectId(row ? row.id : null);
        this.rows.setRows([row]);
    }

    /**
     * masterRow
     * @returns {*}
     */
    masterRow() {
        return this.is_master_table ? this.rows.master_row : null;
    }

}