
export class MapHelper {

    /**
     *
     * @param tableMeta {Object}
     * @param tableRow {Object}
     * @param link {Object}
     * @param type {String}
     * @returns {string}
     */
    static gmapLink(tableMeta, tableRow, link, type) {
        let lnk = '';
        switch (type) {
            case 'map': lnk = 'https://www.google.com/maps/search/'; break;
            case 'embed': lnk = 'https://maps.google.com/maps?q='; break;
            case 'earth': lnk = 'https://earth.google.com/web/search/'; break;
        }

        let lat_header = _.find(tableMeta._fields, {id: Number(link.link_field_lat)});
        let long_header = _.find(tableMeta._fields, {id: Number(link.link_field_lng)});
        let address_header = _.find(tableMeta._fields, {id: Number(link.link_field_address)});
        let lnk_header = _.find(tableMeta._fields, {id: Number(link.table_field_id)});

        if (lat_header && long_header) {
            lnk += tableRow[lat_header.field]+','+tableRow[long_header.field];
        }
        else
        if (address_header) {
            lnk += tableRow[address_header.field];
        }
        else
        if (lnk_header) {
            lnk += tableRow[lnk_header.field];
        }

        switch (type) {
            case 'embed': lnk += '&output=embed'; break;
        }

        return lnk;
    }
}