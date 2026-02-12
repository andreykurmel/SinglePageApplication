
export class AddingRow {
    constructor(obj) {
        obj = obj || {};
        this.active = !!obj.active;
        this.position = obj.position || 'top';
    }
}