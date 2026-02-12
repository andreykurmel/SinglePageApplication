
export class Sector {

    constructor(obj, idx) {
        obj = obj || {};

        this._id = obj._id || null;
        this._idx = Number(idx) || 0;
        this.sector = obj.sector || '';
        this.pos_num = Number(obj.pos_num) || 0;
        this.pos_widths = obj.pos_widths || '';
        this.spt_pos_num = Number(obj.spt_pos_num) || 0;
        this.spt_pos_widths = obj.spt_pos_widths || '';
        this.face_norm = String(obj.face_norm || '');

        this._tot_pos_num = (this.pos_num + this.spt_pos_num) || 1;
        this._widths_array = [];
        this.widthsArray();
    }

    /**
     *
     */
    widthsArray() {
        this._widths_array = [];
        _.each(String(this.pos_widths).split(','), (w) => {
            this._widths_array.push({
                wi: to_float(w),
                spt: false,
            });
        });
        _.each(String(this.spt_pos_widths).split(','), (w) => {
            this._widths_array.push({
                wi: to_float(w),
                spt: true,
            });
        });
    }

}