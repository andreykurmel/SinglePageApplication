
export class DDLHelper {

    /**
     * @param row
     */
    static initUses(row) {
        row._unit_uses = [];
        row._input_uses = [];
        return row;
    }

    /**
     * @param {object} tableMeta
     * @param {array} externalDDLs
     */
    static setUses(tableMeta, externalDDLs)
    {
        _.each(externalDDLs || tableMeta._ddls, (ddl) => {
            let unitFields = _.filter(tableMeta._fields, (fld) => {
                return fld.unit_ddl_id == ddl.id;
            });
            ddl._unit_uses = _.map(unitFields, (fld) => {
                return String(fld.id);
            });

            let inputFields = _.filter(tableMeta._fields, (fld) => {
                return fld.ddl_id == ddl.id;
            });
            ddl._input_uses = _.map(inputFields, (fld) => {
                return String(fld.id);
            });
        });
    }
}