
import {SpecialFuncs} from './../../../classes/SpecialFuncs';

export class Eqpt {

    constructor(obj, air_name) {
        obj = obj || {};

        this._id = obj._id || null;
        this._model_id = obj._model_id || null;
        this._eqptlib_id = obj._eqptlib_id || null;

        this.equipment = obj.equipment;
        this.location = obj.location || '';
        this.sector = obj.sector || '';
        this.pos = obj.pos || '';
        this.status = obj.status || 'null';
        this.elev_rad = Number(obj.elev_rad) || 0;
        this.elev_pd = Number(obj.elev_pd) || 0;
        this.elev_g = Number(obj.elev_g) || 0;
        this.pos_left = Number(obj.pos_left) || 0;
        this.qty = Number(obj.qty) || 1;
        this.model_id = String(obj.model_id || '');

        this.technology = String(obj.technology || '');
        this.tech_arr = SpecialFuncs.parseMsel(this.technology);

        this.dx = Number(obj.dx) || 1;//in inch.
        this.dy = Number(obj.dy) || 1;//in inch.
        this.dz = Number(obj.dz) || 1;//in inch.
        this.scale_x = Number(obj.scale_x) || 1;
        this.scale_y = Number(obj.scale_y) || 1;
        this.scale_z = Number(obj.scale_z) || 1;

        this.ft_dx = this.dx / 12;//in ft.
        this.ft_dy = this.dy / 12;//in ft.
        this.ft_dz = this.dz / 12;//in ft.
        this.calc_dx = this.ft_dx * this.scale_x;//scaled
        this.calc_dy = this.ft_dy * this.scale_y;//scaled
        this.calc_dz = this.ft_dz * this.scale_z;//scaled

        this.port_top = Number(obj.ports_qty_top) || Number(obj.port_top) || 0;
        this.port_bot = Number(obj.ports_qty_bot) || Number(obj.port_bot) || 0;
        this.port_left = Number(obj.ports_qty_left) || Number(obj.port_left) || 0;
        this.port_right = Number(obj.ports_qty_right) || Number(obj.port_right) || 0;
        this.color = obj.color;
        this.show_color = obj.color;
        this.label_side = obj.label_side;
        this.label_dir = obj.label_dir;

        //additionals
        this._sec_idx = null;
        this._pos_idx = null;
        this._hidden = false;
        this._air_name = air_name || 'Air';

        this._used_top_ports = new Array( this.port_top ).fill(0);
        this._used_left_ports = new Array( this.port_left ).fill(0);
        this._used_right_ports = new Array( this.port_right ).fill(0);
        this._used_bottom_ports = new Array( this.port_bot ).fill(0);
    }

    /**
     *
     * @param tech
     */
    setTech(tech) {
        let idx = this.tech_arr.indexOf(tech);
        if (idx > -1) {
            this.tech_arr.splice(idx, 1);
        } else {
            this.tech_arr.push(tech);
        }
        this.technology = JSON.stringify(this.tech_arr);
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
    is_top() {
        return String(this.location).toLowerCase().indexOf( 'air' ) > -1;
    }

    /**
     *
     * @param pos
     * @param {Settings} settings
     * @returns {*}
     */
    portKey(pos, settings) {
        pos = String(pos).toLowerCase();
        switch (pos) {
            case 'top': return 'port_top';
            case 'left': return settings.eqptPortMirr(this) ? 'port_right' : 'port_left';
            case 'right': return settings.eqptPortMirr(this) ? 'port_left' : 'port_right';
            default: return 'port_bot';
        }
    }

    /**
     *
     * @returns {boolean}
     */
    label_is_vert() {
        return String(this.label_dir).toLowerCase().indexOf('vert') > -1;
    }

    /**
     *
     * @param sectors
     * @param pos
     */
    setCoordIdx(sectors, pos) {
        this._sec_idx = _.findIndex(sectors, (sec) => { return sec.sector === this.sector; });
        this._pos_idx = _.findIndex(pos, (p) => { return p.name === this.pos; });
    }

    /**
     *
     * @param elev_by
     * @param new_val
     * @returns {*}
     */
    elevVal(elev_by, new_val) {
        if (new_val === undefined) {
            return elev_by === 'g' ? this.elev_g : this.elev_pd;
        } else {
            elev_by === 'g'
                ? this.elev_g = new_val
                : this.elev_pd = new_val;
        }
    }

    /**
     *
     * @param elev_by
     * @returns {number}
     */
    getTopCoord(elev_by) {
        return Math.max( this.elevVal(elev_by) + this.calc_dy/2, this.calc_dy );
    }

    /**
     *
     * @param elev_by
     * @param off_top
     */
    putTopCoord(elev_by, off_top) {
        this.elevVal( elev_by, to_float(off_top - this.calc_dy/2) );
    }

    /**
     *
     * @param pos
     * @param idx
     */
    usePort(pos, idx) {
        pos = String(pos).toLowerCase();
        switch (pos) {
            case 'top': this._used_top_ports[idx] = 1; break;
            case 'left': this._used_left_ports[idx] = 1; break;
            case 'right': this._used_right_ports[idx] = 1; break;
            default: this._used_bottom_ports[idx] = 1; break;
        }
    }

    /**
     *
     * @param dim - avail: [x,y,z]
     * @returns {string}
     */
    showDim(dim) {
        let size = 'd'+dim;
        let scale = 'scale_'+dim;
        return String(this[size]) + (this[scale] !== 1 ? '(x'+this[scale]+')' : '');
    }
}