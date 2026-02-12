import {MapPosition} from "./MapPosition";

export class MapTable {

    /**
     *
     * @param {object} globalMeta
     * @param {object} tablemeta
     * @param {array} orderedRefConds
     */
    constructor(globalMeta, tablemeta, orderedRefConds)
    {
        this.globalId = globalMeta.id;
        this.id = tablemeta.id || null;
        this.opened = true;

        this.meta = tablemeta || {};

        this.position = _.find(globalMeta._rcmap_positions, {
            object_type: 'table',
            object_id: Number(tablemeta.id)
        });

        this.createPositionIfNeeded(globalMeta, orderedRefConds);
    }

    /**
     * Create position
     */
    createPositionIfNeeded(globalMeta, orderedRefConds)
    {
        if (! this.position || ! this.position.id) {
            let tableIds = _.uniq( _.map(orderedRefConds, 'ref_table_id') );
            tableIds = _.filter(tableIds, (id) => id !== this.globalId);

            let x, y;
            // THIS table
            if (this.globalId === this.id) {
                x = 80;
                y = 45;
            }
            // Other tables
            else {
                let part = 100 / (tableIds.length + 1);
                let tbIdx = _.findIndex(tableIds, (id) => id === this.id);
                x = 5;
                y = (part * 0.75) + (tbIdx * part);
            }

            this.position = MapPosition.empty(this.globalId, this.id, 'table');
            this.position.pos_x = x;
            this.position.pos_y = y;

            globalMeta._rcmap_positions.push(this.position);
            this.positionToBackend();
        }
    }

    /**
     * Update or create a backend position
     */
    positionToBackend()
    {
        MapPosition.storePosition(this.position);
    }
}