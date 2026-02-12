export class FieldHelper {

    /**
     *
     * @param tableMeta
     */
    static setDisabledKeys(tableMeta) {
        _.each(tableMeta._fields, (fld) => {
            fld._poptaborder_disabled = false;
        });
        _.each(tableMeta._fields, (f1) => {
            if (f1.pop_tab_order) {
                _.each(tableMeta._fields, (fld) => {
                    if (fld.id != f1.id && fld.pop_tab_name == f1.pop_tab_name && !fld.pop_tab_order) {
                        fld._poptaborder_disabled = true;
                    }
                });
            }
        });
    }
}