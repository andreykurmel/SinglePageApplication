export class LinkHelper {

    /**
     *
     * @param {Object} tableMeta
     */
    static updateInlinedState(tableMeta) {
        _.each(tableMeta._fields, (fld) => {
            fld.__inlined_link = !! _.find(fld._links, (link) => {
                return Number(link.inline_in_vert_table) && Number(link.inline_is_opened);
            });
            fld.__inlined_field_width = !! _.find(fld._links, (link) => {
                return (link.inline_width === 'field') && Number(link.inline_in_vert_table);
            });
        });
    }

    /**
     *
     * @param tableMeta
     * @returns {*[]}
     */
    static allLinks(tableMeta) {
        let links = [];
        _.each(tableMeta._fields, (fld) => {
            _.each(fld._links, (lnk) => {
                links.push(lnk);
            });
        });
        return _.orderBy(links, 'row_order');
    }
}