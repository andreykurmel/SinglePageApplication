
export class StimLinkParams {
    /**
     * constructor
     * @param obj
     */
    constructor(obj) {
        obj = obj || {};

        //required
        this.app_table = obj.app_table;
        this.table_id = obj.table_id;
        this.rows_per_page = obj.rows_per_page || 50;
        //^^^^^
        this.link_fields = obj.link_fields || [];
        this.in_url_elements = obj.in_url_elements || [];
        this.name_field = obj.name_field || '';

        this.filters_active = !!obj.filters_active;
        this.download_active = !!obj.download_active;
        this.copy_from_active = !!obj.copy_from_active;
        this.halfmoon_active = !!obj.halfmoon_active;
        this.condformat_active = !!obj.condformat_active;
        this.cellheight_btn_active = !!obj.cellheight_btn_active;
        this.string_replace_active = !!obj.string_replace_active;
        this.copy_childrene_active = !!obj.copy_childrene_active;
        this.has_viewpop_active = !!obj.has_viewpop_active;
        this.on_edit_changers = obj.on_edit_changers || {};
        this.app_table_options = obj.app_table_options || '';
        this.avail_cols_for_app = obj.avail_cols_for_app || [];
        this.top_columns_show = obj.top_columns_show || [];
        this.app_maps = obj.app_maps || [];
        this.app_fields = obj.app_fields || [];
        this.logo_replace_field = obj.logo_replace_field || '';
    }

    /**
     * masterInherits
     * @param tableRow
     * @returns {{}}
     */
    masterInherits(tableRow) {
        let result = {};
        _.each(this.link_fields, (fld) => {
            result[fld.data_field] = tableRow[fld.link_field_db];
        });
        return result;
    }

    /**
     *
     * @param mainval
     * @returns {{}}
     */
    virtualMasterRow(mainval) {
        let result = { _virtual_mr: true };
        _.each(this.app_maps, (tablda,stim) => {
            result[tablda] = stim === '_id' ? -1 : mainval;
        });
        return result;
    }

    /**
     * fillRowFromMaster
     * @param {object} masterRow
     * @param {object} tableRow
     * @param force_apply
     * @returns {object}
     */
    fillRowFromMaster(masterRow, tableRow, force_apply) {
        if (masterRow._virtual_mr) {
            return tableRow;
        }
        _.each(this.link_fields, (fld) => {
            if (!tableRow[fld.data_field] || force_apply) {
                //set Value
                tableRow[fld.data_field] = masterRow[fld.link_field_db];
                //set spec field for 'User' type
                tableRow['_u_'+fld.data_field] = masterRow['_u_'+fld.link_field_db];
                //set spec field for 'DDL show/val' type
                tableRow['_rc_'+fld.data_field] = masterRow['_rc_'+fld.link_field_db];
            }
        });
        return tableRow;
    }

    /**
     * linkMap
     * @param key
     * @returns {Array}
     */
    linkMap(key) {
        return _.map(this.link_fields, key || 'data_field');
    }
}