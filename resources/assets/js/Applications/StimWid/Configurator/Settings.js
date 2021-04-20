
import {Pos} from './Pos';
import {Sector} from './Sector';

export class Settings {

    /**
     *
     * @param obj
     */
    constructor(obj) {
        this.applySettings(obj);
        this._sectors = [new Sector()];
        this._pos = [new Pos()];

        //specials
        this.reverse_draw = false;
        this.add_new = false;
    }

    /**
     *
     * @param obj
     * @param no_clear_sel
     */
    applySettings(obj, no_clear_sel) {
        obj = obj || {};

        this._id = obj._id;
        this._app_tb = String(obj._app_tb || '');

        this.background = obj.background || '#ffffff';
        this.top_elev = Number(obj.top_elev) || 0;
        this.bot_elev = Number(obj.bot_elev) || 0;

        this.pd_pos_he = Number(obj.pd_pos_he) || 0;
        this.pd_sector_he = Number(obj.pd_sector_he) || 0;
        this.pd_rest_he = Number(obj.pd_rest_he) || 0;
        this.pd_bot_he = Number(obj.pd_bot_he) || 0;

        this.g_pos_he = Number(obj.g_pos_he) || 0;
        this.g_sector_he = Number(obj.g_sector_he) || 0;
        this.g_rest_he = Number(obj.g_rest_he) || 0;
        this.g_bot_he = Number(obj.g_bot_he) || 0;

        this.elev_by = obj.elev_by === 'g' ? 'g' : 'pd'; // ['g','pd']
        this.show_eqpt_size = Boolean(obj.show_eqpt_size);
        this.show_eqpt_model = Boolean(obj.show_eqpt_model);
        this.show_eqpt_tech = Boolean(obj.show_eqpt_tech);
        this.show_eqpt_id = Boolean(obj.show_eqpt_id);
        this.show_line_model = Boolean(obj.show_line_model);
        this.show_eqpt_tooltip = Boolean(obj.show_eqpt_tooltip);

        this.show_eqpt_size__font = obj.show_eqpt_size__font;
        this.show_eqpt_size__size = Number(obj.show_eqpt_size__size) || 9;
        this.show_eqpt_size__color = obj.show_eqpt_size__color;

        this.show_eqpt_model__font = obj.show_eqpt_model__font;
        this.show_eqpt_model__size = Number(obj.show_eqpt_model__size) || 9;
        this.show_eqpt_model__color = obj.show_eqpt_model__color;

        this.show_eqpt_tech__font = obj.show_eqpt_tech__font;
        this.show_eqpt_tech__size = Number(obj.show_eqpt_tech__size) || 9;
        this.show_eqpt_tech__color = obj.show_eqpt_tech__color;

        this.show_eqpt_id__font = obj.show_eqpt_id__font;
        this.show_eqpt_id__size = Number(obj.show_eqpt_id__size) || 9;
        this.show_eqpt_id__color = obj.show_eqpt_id__color;

        this.show_line_model__font = obj.show_line_model__font;
        this.show_line_model__size = Number(obj.show_line_model__size) || 9;
        this.show_line_model__color = obj.show_line_model__color;

        this.use_independent_controls = Boolean(obj.use_independent_controls);
        this.full_reflection = Boolean(obj.full_reflection);

        this.caption_he = this.caption_he || 0;
        this.shared_sectors = obj.shared_sectors || 'SHARED';
        this._shared_sectors_arr = String(this.shared_sectors).split(',');

        this.air_base_names = obj.air_base_names || 'Air,Base';
        this._air_name = _.trim( _.first( this.air_base_names.split(',') ) );
        this._base_name = _.trim( _.last( this.air_base_names.split(',') ) );

        if (!no_clear_sel) {
            this.clearSel();
        }

        //special applications (read only)
        this.global_draggable = true;
        this.global_top = 24;
        this.port_px = 6;
        this.port_margin = 2;
    }

    /**
     *
     * @returns {boolean}
     */
    is_eqpt_rev() {
        return this.reverse_draw;
    }

    /**
     *
     * @returns {boolean}
     */
    is_full_rev() {
        return this.reverse_draw && this.full_reflection;
    }

    /**
     *
     * @returns {boolean}
     */
    is_rev_no_full() {
        return this.reverse_draw && !this.full_reflection;
    }

    /**
     *
     * @returns {number}
     */
    get_glob_top() {
        return this.global_top + (this.is_rev_no_full() ? -2 : 1);
    }

    /**
     *
     * @returns {number}
     */
    get_glob_left() {
        return this.is_rev_no_full() ? 2 : 0;
    }

    /**
     *
     * @param key
     * @param only_key
     * @returns {*}
     */
    heVal(key, only_key) {
        switch (key) {
            case 'pos_he': key = this.elev_by === 'pd' ? 'pd_pos_he' : 'g_pos_he';
                break;
            case 'sector_he': key = this.elev_by === 'pd' ? 'pd_sector_he' : 'g_sector_he';
                break;
            case 'rest_he': key = this.elev_by === 'pd' ? 'pd_rest_he' : 'g_rest_he';
                break;
            case 'bot_he': key = this.elev_by === 'pd' ? 'pd_bot_he' : 'g_bot_he';
                break;
        }
        return only_key ? key : this[key];
    }

    /**
     *
     * @param {Eqpt} eqpt
     * @returns {number}
     */
    eqptMaxWidth(eqpt) {
        let sector = _.find(this._sectors, {sector: eqpt.sector});
        let p_i = _.findIndex(this._pos, {name: eqpt.pos});
        let ws = String(sector ? sector.pos_widths : '').split(',');
        return to_float(ws[p_i])
            || _.sumBy(ws, (el) => { return to_float(el); })
            || this.calcSectorPosOffset(sector ? sector._idx : +Infinity, p_i);
    }

    /**
     * Mirror ports only for
     * @param {Eqpt} eqpt
     * @returns {boolean}
     */
    eqptPortMirr(eqpt) {
        let no_sector = in_array(eqpt.sector, this._shared_sectors_arr) || !eqpt.sector;
        return this.is_rev_no_full() && eqpt.is_top() && !no_sector;
    }

    /**
     *
     * @param key
     * @param val
     */
    setAirBaseNames(key, val) {
        this[key] = _.trim(val);
        this.air_base_names = [this._air_name, this._base_name].join(',');
    }

    /**
     * Selects
     */
    clearSel(exclude) {
        //CLEAR SEL
        if (exclude !== 'line') {
            this.sel_line = null;
            this.sel_line_id = null;
        }

        if (exclude !== 'eqpt') {
            this.mass_eqpt = [];
        }

        if (exclude !== 'tech') {
            this.sel_tech = null;
        }

        if (exclude !== 'status') {
            this.sel_status = null;
        }

        if (exclude !== 'secpos') {
            this.sel_secpos = null;
        }

        this.sel_eqpt_id = null;
        this.sel_port_pos = null;
        this.sel_port_idx = null;

        this.sel_line_conn_id = null;

        this.sel_control_point = null;

        //CLEAR DRAG
        this.add_new = exclude ? this.add_new : false;
        if (exclude !== 'eqpt') {
            this.drag_eqpt = null;
        }
        if (exclude !== 'line') {
            this.drag_line = null;
        }
        this.over_sec_pos = null;
        this.drag_point = null;
        this.drag_offset_x = 0;
        this.drag_offset_y = 0;
    }

    /**
     *
     * @param {Eqpt} eqpt
     * @param offset_x
     * @param offset_y
     */
    eqptDragStart(eqpt, offset_x, offset_y) {
        this.drag_eqpt = eqpt;
        this.drag_offset_x = offset_x;
        this.drag_offset_y = offset_y;
        if (!_.find(this.mass_eqpt, {_id: eqpt._id})) {
            this.mass_eqpt.push(eqpt);
        }
    }

    /**
     *
     * @param {Eqpt} eqpt
     * @param {Event} event
     */
    selEqpt(eqpt, event) {
        this.clearSel(event && event.ctrlKey ? 'eqpt' : '');//save or not prev selected Eqpts
        this.mass_eqpt.push(eqpt);
    }

    /**
     *
     * @param {Line} line
     * @param point
     */
    selLine(line, point) {
        this.clearSel();
        this.sel_line = line;
        this.sel_line_id = line._id;
        this.sel_control_point = point || null;
    }

    /**
     *
     * @param {Line} line
     * @param conn_id
     */
    selConn(line, conn_id) {
        this.clearSel();
        this.sel_line = line;
        this.sel_line_id = line._id;
        this.sel_line_conn_id = conn_id;
    }

    /**
     *
     * @param {Tech} tech
     */
    selTech(tech) {
        this.clearSel();
        this.sel_tech = tech;
    }

    /**
     *
     * @param {Status} status
     */
    selStatus(status) {
        this.clearSel();
        this.sel_status = status;
    }

    /**
     *
     * @param {Secpos} secpos
     * @param {String} posidx
     */
    selSecPos(secpos, posidx) {
        this.clearSel();
        this.sel_secpos = secpos;
        this.sel_secpos.posidx = posidx;
    }

    /**
     *
     * @param eqpt_id
     * @param pos
     * @param idx
     */
    selFirstPort(eqpt_id, pos, idx) {
        this.sel_eqpt_id = eqpt_id;
        this.sel_port_pos = pos;
        this.sel_port_idx = idx;
    }

    /**
     *
     */
    saveSettings() {
        if (this._app_tb) {
            axios.post('?method=save_model', {
                app_table: this._app_tb,
                model: this,
            }).catch(errors => {
                Swal('', getErrors(errors));
            });
        }
    }

    /**
     *
     * @returns {*}
     */
    sumHe() {
        return this.heVal('pos_he') + this.heVal('sector_he') + this.heVal('rest_he') + this.heVal('bot_he');
    }

    /**
     *
     * @param s_idx
     * @param p_idx
     * @param px_in_ft
     * @returns {number}
     */
    calcSectorPosOffset(s_idx, p_idx, px_in_ft) {
        let sectrs = this._sectors;
        /*if (s_idx > -1 && s_idx < Infinity && this.is_rev_no_full()) {
            s_idx = Math.abs( s_idx - (this._sectors.length-1) );
            sectrs = this._sectors.slice().reverse();
        }*/

        let left_off = 0;
        _.each(sectrs, (sec,i) => {
            if (i < s_idx) { //sum all up to current sector
                let ws = String(sec.pos_widths).split(',');
                _.each(ws, (el) => {
                    left_off += Math.round(to_float(el) * (px_in_ft || 1));
                });
            }
            if (i == s_idx) { //sum all up to current position
                let ws = String(sec.pos_widths).split(',');

                if (p_idx > -1 && p_idx < Infinity && this.is_rev_no_full()) {
                    p_idx = Math.abs( p_idx - (ws.length-1) );
                    ws = ws.slice().reverse();
                }
                _.each(ws, (el, pi) => {
                    left_off += pi < p_idx
                        ? Math.round(to_float(el) * (px_in_ft || 1))
                        : 0;
                });
            }
        });
        if (px_in_ft) {
            left_off += this.get_glob_left();
        }
        return left_off;
    }

    /**
     *
     * @param coord_x
     * @param coord_y
     * @param px_in_ft
     */
    getSectorAndPos(coord_x, coord_y, px_in_ft) {
        if (px_in_ft) { // convert px to ft
            coord_x /= px_in_ft;
            coord_y /= px_in_ft;
        }
        let sector = null, pos = null, width = null;
        if (!coord_y || coord_y > (this.top_elev - this.heVal('pos_he') - this.heVal('sector_he'))) {
            //sectors
            let cur_wi = 0;
            _.each(this._sectors, (sec,si) => {
                let ws = String(sec.pos_widths).split(',');
                _.each(ws, (pw,pi) => {
                    cur_wi += to_float(pw);
                    if (!sector && !pos && coord_x < cur_wi) {
                        sector = this._sectors[si];
                        pos = this._pos[pi];
                    }
                })
            });
            if (!coord_y || coord_y < (this.top_elev - this.heVal('pos_he'))) {
                pos = null; //sector, no pos
            }
            width = this.sectorWi(sector, pos, px_in_ft);
        } else {
            //rest
            width = this.sectorWi(null, null, px_in_ft);
        }

        let si = sector ? sector._idx : -1;
        let pi = pos ? pos._idx : -1;
        let left_off = this.calcSectorPosOffset(si, pi) * px_in_ft;

        return {
            sector: sector,
            pos: pos,
            width: width,
            left: left_off,
        }
    }

    /**
     *
     * @param sec
     * @param pos
     * @param px_in_ft
     * @returns {number}
     */
    sectorWi(sec, pos, px_in_ft) {
        let ws = sec ? String(sec.pos_widths).split(',') : [];
        let p_idx = pos ? _.findIndex(this._pos, {_id: pos._id}) : -1;
        return sec
            ? ( p_idx > -1 ? ws[p_idx] * px_in_ft : _.sumBy(ws, (i) => to_float(i)) * px_in_ft )
            : this.calcSectorPosOffset(Infinity, Infinity, px_in_ft);
    }

    /**
     *
     * @param min
     * @param max
     */
    setElevs(min, max) {
        this.top_elev = this.top_elev || Number(max) || 0;
        this.bot_elev = this.bot_elev || Number(min) || 0;
    }

    /**
     *
     * @param pos
     * @param sec
     * @param rest
     * @param bot
     */
    setHeights(pos, sec, rest, bot) {
        if (this.elev_by === 'pd') {
            this.pd_pos_he = this.pd_pos_he || Number(pos) || 0;
            this.pd_sector_he = this.pd_sector_he || Number(sec) || 0;
            this.pd_rest_he = this.pd_rest_he || Number(rest) || 0;
            this.pd_bot_he = this.pd_bot_he || Number(bot) || 0;
        } else {
            this.g_pos_he = this.g_pos_he || Number(pos) || 0;
            this.g_sector_he = this.g_sector_he || Number(sec) || 0;
            this.g_rest_he = this.g_rest_he || Number(rest) || 0;
            this.g_bot_he = this.g_bot_he || Number(bot) || 0;
        }
    }

    /**
     *
     * @param style
     * @returns {*}
     */
    full_mirr(style) {
        if (this.is_full_rev()) {
            style.right = style.left;
            style.left = null;
        }
        return style;
    }

    /**
     *
     * @param {Eqpt} eqpt
     * @returns {Number}
     */
    convLeftEqpt(eqpt) {
        let left = 0;
        if (this.is_rev_no_full()) {
            let wi = this.eqptMaxWidth(eqpt);
            left += to_float( wi - eqpt.pos_left*2 - eqpt.calc_dx );
        }
        return left;
    }

    // LINE ATTRIBUTES

    /**
     *
     * @returns {string}
     */
    getLineTopKey() {
        return this.is_eqpt_rev() && this.use_independent_controls ? 'pos_top_back' : 'pos_top';
    }

    /**
     *
     * @returns {string}
     */
    getLineLeftKey() {
        return this.is_eqpt_rev() && this.use_independent_controls ? 'pos_left_back' : 'pos_left';
    }

    /**
     *
     * @returns {string}
     */
    getLineControlKey() {
        return this.is_eqpt_rev() && this.use_independent_controls ? 'control_points_back' : 'control_points';
    }

    /**
     *
     * @returns {string}
     */
    getLineObjKey() {
        return this.is_eqpt_rev() && this.use_independent_controls ? '_control_points_back_obj' : '_control_points_obj';
    }


    //SPEC GETTERS
    /**
     *
     * @param eqpts_arr
     * @param variant
     * @returns {Array}
     */
    filter_eqpts(eqpts_arr, variant) {
        variant = String(variant);
        let ff = [];
        if (variant.indexOf('pos') > -1) {
            ff = ff.concat( _.filter(eqpts_arr, (el) => {
                return el.is_top()
                    && !in_array(el.sector, this._shared_sectors_arr)
                    && el.sector
                    && el.pos;
            }) );
        }
        if (variant.indexOf('sec') > -1) {
            ff = ff.concat( _.filter(eqpts_arr, (el) => {
                return el.is_top()
                    && !in_array(el.sector, this._shared_sectors_arr)
                    && el.sector
                    && !el.pos;
            }) );
        }
        if (variant.indexOf('rest') > -1) {
            ff = ff.concat( _.filter(eqpts_arr, (el) => {
                return el.is_top()
                    && (in_array(el.sector, this._shared_sectors_arr) || !el.sector)
                    && !el.pos;
            }) );
        }
        if (variant.indexOf('bot') > -1) {
            ff = ff.concat( _.filter(eqpts_arr, (el) => {
                return !el.is_top();
            }) );
        }
        return ff;
    }

}