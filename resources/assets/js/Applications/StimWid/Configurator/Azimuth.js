import {Settings} from "./Settings";

export class Azimuth {

    constructor(obj) {
        obj = obj || {};

        this._id = obj._id || null;
        this.deg = Number(obj.deg) || 0;
    }

}