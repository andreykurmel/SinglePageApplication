
export class Pos {

    constructor(obj, idx) {
        obj = obj || {};

        this._idx = Number(idx) || 0;
        this._id = obj._id || null;
        this.name = obj.name || '';
    }

}