import {Settings} from "./Settings";

export class Elev {

    constructor(obj) {
        obj = obj || {};

        this._id = obj._id || null;
        this.type = obj.type || null; // ['PD','GC','RC']
        this.elev = obj.elev || null;
        this.posidx = '';
    }

    /**
     *
     * @param {Settings} params
     * @param {Elev[]} elevs_list
     * @returns {Elev[]}
     */
    static filterList(params, elevs_list)
    {
        return _.filter(elevs_list, (el) => {
            return this.availableType(params, el);
        });
    }

    /**
     *
     * @param {Settings} params
     * @param {Elev} elev
     * @returns {boolean}
     */
    static availableType(params, elev)
    {
        return params.elev_by === String(elev.type).toLowerCase();
    }

}