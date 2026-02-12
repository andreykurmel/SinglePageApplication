
export class OptionsHelper {

    /**
     *
     * @param {object} tableMeta
     * @param {boolean} canEmpty
     * @param {string} special
     */
    static rowGroup(tableMeta, canEmpty, special) {
        let rgs = [];
        switch (special) {
            case 'empty_rc': rgs = _.filter(tableMeta._row_groups, (rg) => { return !rg.row_ref_condition_id; });
                break;
            default: rgs = tableMeta._row_groups;
                break;
        }
        let result = _.map(rgs, (rowG) => {
            return {val: rowG.id, show: rowG.name};
        });
        if (canEmpty) {
            result.unshift({val: null, show: ''});
        }
        return result;
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
                        rLinks.push({val:link.id, show:link.name});
                    }
                });
            }
        });
        return rLinks;
    }

    /**
     *
     * @param {object} tableMeta
     * @param {string} linkType
     * @returns {*[]}
     */
    static allLinks(tableMeta, linkType = '') {
        let links = [];
        _.each(tableMeta._fields, (fld) => {
            _.each(fld._links, (lnk) => {
                if (!linkType || lnk.link_type === linkType) {
                    links.push({ val:lnk.id, show:lnk.name, })
                }
            });
        });
        return links;
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