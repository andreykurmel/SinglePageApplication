
export class Line {

    constructor(obj, eqpts) {
        obj = obj || {};

        this._id = obj._id || null;
        this._linelib_id = obj._linelib_id || null;
        this._feedline_id = obj._feedline_id || null;

        this._is_lib = !eqpts;
        this._hidden = false;
        this._eqpt_hidden = false;

        this.line = obj.line || obj.title || '';
        this.title = obj.title || '';
        this.gui_name = obj.gui_name || '';
        this.qty = Number(obj.qty) || 1;
        this.diameter = Number(obj.diameter) || 1;
        this.f_diameter = Number(obj.f_diameter) || 0.5;
        this.status = obj.status || 'null';
        this.color = '#000';
        this.show_color = '#000';

        this.caption_style = obj.caption_style || 'Inline';
        this.caption_sect = obj.caption_sect || 'Mid';
        this.caption_orient = obj.caption_orient || 'Horizontal';

        this.from_eqpt_id = Number(obj.from_eqpt_id) || null;
        this.from_port_pos = obj.from_port_pos;
        this.from_port_idx = Number(obj.from_port_idx) || 0;
        this.to_eqpt_id = Number(obj.to_eqpt_id) || null;
        this.to_port_pos = obj.to_port_pos;
        this.to_port_idx = Number(obj.to_port_idx) || 0;

        //control points
        this.control_points = obj.control_points;
        this.control_points_back = obj.control_points_back;
        try {
            this._control_points_obj = obj.control_points ? JSON.parse(obj.control_points) : [];
            this._control_points_back_obj = obj.control_points_back ? JSON.parse(obj.control_points_back) : [];
        } catch (e) {
            this._control_points_obj = []; // {left: number, top: number, ord: number}
            this._control_points_back_obj = []; // {left: number, top: number, ord: number}
        }
        //position
        this.pos_top = Number(obj.pos_top) || 0;
        this.pos_left = Number(obj.pos_left) || 0;
        this.pos_top_back = Number(obj.pos_top_back) || 0;
        this.pos_left_back = Number(obj.pos_left_back) || 0;
        //connection
        this._from_eqpt = null;
        this._to_eqpt = null;

        if (eqpts) {
            this.findEqpts(eqpts);
        }
    }

    /**
     *
     * @param app_tb
     * @param master_params
     * @returns {AxiosPromise}
     */
    quickSave(app_tb, master_params) {
        return axios.post('?method=save_model', {
            app_table: app_tb,
            model: this,
            master_params: master_params || null,
        });
    }

    /**
     *
     * @returns {boolean}
     */
    is_inline() {
        return this.caption_style.toLowerCase().indexOf( 'inline' ) > -1;
    }

    /**
     *
     * @returns {boolean}
     */
    is_horizontal() {
        return this.caption_orient.toLowerCase().indexOf( 'vert' ) === -1;
    }

    /**
     *
     * @returns {number} [-1, 0, 1]
     */
    section_place() {
        return this.caption_sect.toLowerCase().indexOf( 'top' ) > -1
            ? -1
            : (this.caption_sect.toLowerCase().indexOf( 'bot' ) > -1 ? 1 : 0);
    }

    /**
     *
     */
    clearPos() {
        this.pos_top = 0;
        this.pos_left = 0;
        this.pos_top_back = 0;
        this.pos_left_back = 0;
    }

    /**
     *
     * @param eqpts
     */
    findEqpts(eqpts) {
        this._from_eqpt = _.find(eqpts, {'_id': Number(this.from_eqpt_id)});
        if (this._from_eqpt) {
            this._from_eqpt.usePort(this.from_port_pos, this.from_port_idx);
        }

        this._to_eqpt = _.find(eqpts, {'_id': Number(this.to_eqpt_id)});
        if (this._to_eqpt) {
            this._to_eqpt.usePort(this.to_port_pos, this.to_port_idx);
        }
    }

}