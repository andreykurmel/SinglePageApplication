
<script>
    /**
     * Should be present:
     * this.settings : Settings;
     * this.px_in_ft: Number;
     * this.line_diameter: Number;
     */
    import {Eqpt} from "./Eqpt";
    import {Line} from "./Line";
    import {Settings} from "./Settings";

    export default {
        data() {
            return {
                max_ord: 512,
                min_ord: 0,
                c_order: (512 + 0) / 2,
            }
        },
        methods: {
            //connect point pos
            get_point_pos(top_pos, left_pos, order, is_special) {
                let cur_top = (this.settings.top_elev - top_pos) * this.px_in_ft;
                cur_top += (this.line_diameter/2);
                cur_top += this.settings.get_glob_top();
                return {
                    top_px: cur_top,
                    left_px: left_pos * this.px_in_ft,
                    _order: order,
                    _special: Boolean(is_special),
                    _change_line_order: Boolean(is_special),
                };
            },
            //connect port for eqpt pos
            get_eqpt_pos(is_first, calc_eqpt, port_pos, port_idx) {
                let port_delta = this.port_delta(calc_eqpt, port_pos, port_idx);
                let is_port_pos_side = this.port_pos_side(calc_eqpt, port_pos);
                //top
                let ee = this.settings.elev_by;
                let elem_top = calc_eqpt.is_top()
                    ? calc_eqpt.getTopCoord(ee) // in TOP part
                    : this.settings.top_elev - this.settings.sumHe() - this.settings.bot_elev + calc_eqpt.getTopCoord(ee); // in Bottom Part

                let top_port = (this.settings.top_elev - elem_top) * this.px_in_ft
                    + (is_port_pos_side ? port_delta : -this.settings.port_px/2) //port_px - center of port
                    + this.settings.get_glob_top();
                let bot_port = top_port + (calc_eqpt.calc_dy * this.px_in_ft)
                    + (is_port_pos_side ? port_delta : this.settings.port_px); //port_px - center of port
                //top

                //left
                let left_off = this.settings.calcSectorPosOffset(calc_eqpt._sec_idx, calc_eqpt._pos_idx, this.px_in_ft);
                left_off += this.settings.convLeftEqpt(calc_eqpt) * this.px_in_ft;
                let bord_left = left_off + calc_eqpt.pos_left * this.px_in_ft
                    + (!is_port_pos_side ? port_delta : -this.settings.port_px/2); //port_px - center of port
                let bord_right = bord_left + (calc_eqpt.calc_dx * this.px_in_ft)
                    + (!is_port_pos_side ? port_delta : this.settings.port_px); //port_px - center of port
                //left

                return {
                    top_px: calc_eqpt.portKey(port_pos, this.settings) !== 'port_bot' ? top_port : bot_port,
                    left_px: calc_eqpt.portKey(port_pos, this.settings) !== 'port_right' ? bord_left : bord_right,
                    _order: is_first ? this.min_ord : this.max_ord,
                    _port_conn: true,
                    _change_line_order: is_first
                        ? in_array(calc_eqpt.portKey(port_pos, this.settings), ['port_left','port_right'])
                        : in_array(calc_eqpt.portKey(port_pos, this.settings), ['port_top','port_bot']),
                    _e_id: calc_eqpt._id,
                    _e_pos: port_pos,
                    _e_idx: port_idx,
                };
            },
            //port offset
            port_pos_side(calc_eqpt, port_pos) {
                return in_array(calc_eqpt.portKey(port_pos, this.settings), ['port_left','port_right']);
            },
            port_delta(calc_eqpt, port_pos, port_idx) {
                let rev = this.settings.eqptPortMirr(calc_eqpt) ? -1 : 1;
                let px = this.settings.port_px + this.settings.port_margin*2;
                let port_key = calc_eqpt.portKey(port_pos, this.settings);
                let port_delta = this.port_pos_side(calc_eqpt, port_pos)
                    ? (calc_eqpt.calc_dy / 2) * this.px_in_ft // center eqpt
                    : (calc_eqpt.calc_dx / 2) * this.px_in_ft; // center eqpt
                port_delta -= rev * (calc_eqpt[port_key] * px/2); // left side of ports (half of ports widths)
                port_delta += rev * (port_idx * px); // left side of selected port zone
                port_delta += rev * (px/2 + this.settings.port_margin/2); // center of selected port
                return Math.round(port_delta - this.line_diameter/2);
            },
        },
    }
</script>