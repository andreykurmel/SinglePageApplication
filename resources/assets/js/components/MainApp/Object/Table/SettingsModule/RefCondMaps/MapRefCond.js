import {MapPosition} from "./MapPosition";

export class MapRefCond {

    /**
     *
     * @param {object} globalMeta
     * @param {object} refCond
     * @param {array} orderedRefConds
     */
    constructor(globalMeta, refCond, orderedRefConds)
    {
        this.globalId = globalMeta.id;
        this.id = refCond.id || null;
        this.opened = true;

        this.refCond = refCond || {};

        this.position = _.find(globalMeta._rcmap_positions, {
            object_type: 'ref_cond',
            object_id: Number(refCond.id)
        });

        this.currentTablePosition = _.find(globalMeta._rcmap_positions, {
            object_type: 'table',
            object_id: Number(this.refCond.table_id)
        });
        this.refTablePosition = _.find(globalMeta._rcmap_positions, {
            object_type: 'table',
            object_id: Number(this.refCond.ref_table_id)
        });

        this.createPositionIfNeeded(globalMeta, orderedRefConds);
    }

    /**
     * Create position
     */
    createPositionIfNeeded(globalMeta, orderedRefConds)
    {
        if (! this.position || ! this.position.id) {
            let x, y;
            // RC to other tables
            if (this.refCond.table_id != this.refCond.ref_table_id) {
                let otherRCs = _.filter(orderedRefConds, (rc) => rc.table_id != rc.ref_table_id);
                let part = 100 / (otherRCs.length + 1);
                let rcIdx = _.findIndex(otherRCs, (rc) => rc.id === this.id);
                x = 45;
                y = part + (rcIdx * part);
            }
            // RC to THIS table
            else {
                let thisRCs = _.filter(orderedRefConds, (rc) => rc.table_id == rc.ref_table_id);
                let halfLength = Math.ceil(thisRCs.length / 2);
                let part = 30 / (halfLength + 1);
                let partX = 10 / (halfLength + 1);
                let rcIdx = _.findIndex(thisRCs, (rc) => rc.id === this.id);
                x = (rcIdx < halfLength
                    ? 90 - (rcIdx * partX)
                    : 90 - ((rcIdx - halfLength) * partX));
                y = (rcIdx < halfLength
                    ? 35 - (rcIdx * part)
                    : 65 + ((rcIdx - halfLength) * part));
            }

            this.position = MapPosition.empty(this.globalId, this.id, 'ref_cond');
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