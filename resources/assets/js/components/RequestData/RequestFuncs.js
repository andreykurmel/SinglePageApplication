
export class RequestFuncs {
    /**
     *
     * @param table_meta
     * @param table_permission
     * @param field_name
     * @returns {null}
     */
    static recordUrlHeader(table_meta, table_permission, field_name) {
        field_name = field_name || 'dcr_record_url_field_id';
        if (table_permission[field_name]) {
            return _.find(table_meta._fields, {id: Number(table_permission[field_name])});
        } else {
            return null;
        }
    }

    /**
     *
     * @param status
     * @returns {*}
     */
    static prefix(status) {
        switch (status) {
            case 'Saved': return 'dcr_save_';
            case 'Updated': return 'dcr_upd_';
            case 'Sumbitted':
            default: return 'dcr_';
        }
    }
}