
export class OptionsHelper {

    /**
     *
     * @param {object} tableMeta
     */
    static rowGroup(tableMeta) {
        return _.map(tableMeta._row_groups, (rowG) => {
            return {val: rowG.id, show: rowG.name};
        });
    }

    /**
     *
     * @param {object} tableMeta
     */
    static linksGrouped(tableMeta) {
        let rLinks = [];
        _.each(tableMeta._fields, (field) => {
            if (field._links && _.find(field._links, {link_type: 'Record'})) {
                rLinks.push({val:null, show:field.name, isTitle:true, disabled:true, style:{background: '#aaa'}});
                _.each(field._links, (link) => {
                    if (link.link_type === 'Record') {
                        rLinks.push({val:link.id, show:link.icon});
                    }
                });
            }
        });
        return rLinks;
    }

    /**
     *
     * @param {object} tableMeta
     */
    static ddlsReferences(tableMeta) {
        let rDdls = [];
        _.each(tableMeta._ddls, (ddl) => {
            if (ddl._references && ddl._references.length) {
                rDdls.push({val:ddl.id, show:ddl.name});
            }
        });
        return rDdls;
    }
}