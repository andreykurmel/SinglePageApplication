
export class Sector {

    constructor(obj, idx) {
        obj = obj || {};

        this._id = obj._id || null;
        this._idx = Number(idx) || 0;
        this.sector = obj.sector || '';
        this.pos_num = Number(obj.pos_num) || 0;
        this.pos_widths = obj.pos_widths || '';
    }

}