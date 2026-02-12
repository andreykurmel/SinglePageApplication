
export class Status {

    constructor(obj) {
        obj = obj || {};

        this._id = obj._id || null;
        this.name = obj.name || '';
        this.color = obj.color || '';
    }

}