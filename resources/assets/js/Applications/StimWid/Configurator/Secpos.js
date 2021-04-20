
export class Secpos {

    constructor(obj) {
        obj = obj || {};

        this._id = obj._id || null;
        this.sec_name = obj.sec_name || '';
        this.primary_qty = Number(obj.primary_qty) || 0;
        this.others_qty = Number(obj.others_qty) || 0;
        this.posidx = '';
    }

}