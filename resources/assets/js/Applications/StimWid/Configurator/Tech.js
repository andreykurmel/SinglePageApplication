
export class Tech {

    constructor(obj) {
        obj = obj || {};

        this._id = obj._id || null;
        this.technology = obj.technology || '';
    }

}