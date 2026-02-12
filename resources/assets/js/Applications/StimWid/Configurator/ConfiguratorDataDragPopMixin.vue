
<script>
    /** only for Configurator */

    import {Pos} from "./Pos";
    import {Eqpt} from "./Eqpt";
    import {Line} from "./Line";
    import {Tech} from "./Tech";
    import {Status} from "./Status";
    import {Sector} from "./Sector";
    import {Elev} from "./Elev";
    import {Azimuth} from "./Azimuth";
    import {MetaTabldaRows} from "../../../classes/MetaTabldaRows";

    import { eventBus } from '../../../app';

    export default {
        methods: {
            //recalc Line Pos
            recalcPosOfAllLines() {
                //recalc positions and save connected Lines
                let conn_tb = this.popup_tables.linedata_2d;
                if (!this.params.full_reflection) {
                    _.each(this.data_conn, (line) => {
                        if (line._from_eqpt && line._to_eqpt) {
                            if (!this.params.use_independent_controls) {
                                let k_control = this.params.getLineControlKey();
                                let k_obj = this.params.getLineObjKey();
                                _.each(line[k_obj], (obj) => {
                                    let result = this.params.getSectorAndPos(obj.left*this.px_in_ft, null, this.px_in_ft);
                                    let shift = result.width - (obj.left*this.px_in_ft - result.left);
                                    obj.left = (result.left + shift) / this.px_in_ft;
                                });
                                line[k_control] = JSON.stringify( line[k_obj] );
                            }
                            this.setLineBetweenEqpt(line);
                            line.quickSave(conn_tb);
                        }
                    });
                }
                //Redraw
                this.redrawAll();
                this.load2Dfilters();
            },
            setLineBetweenEqpt(line) {
                let tmp_fr = this.get_eqpt_pos(true, line._from_eqpt, line.from_port_pos, line.from_port_idx);
                let tmp_to = this.get_eqpt_pos(false, line._to_eqpt, line.to_port_pos, line.to_port_idx);

                let k_top = this.params.getLineTopKey();
                let k_left = this.params.getLineLeftKey();
                if (!line[k_top]) {
                    line[k_top] = tmp_fr && tmp_to ? (tmp_fr.top_px + tmp_to.top_px) / 2 : 0;
                    line[k_top] = this.settings.top_elev - (line[k_top] / this.px_in_ft);
                }
                line[k_left] = tmp_fr && tmp_to ? (tmp_fr.left_px + tmp_to.left_px)/2 : 0;
                line[k_left] = line[k_left] / this.px_in_ft;
            },

            //drag & drop
            eqptAddStart(eqpt, offset_x, offset_y) {
                this.params.eqptDragStart(eqpt, offset_x, offset_y);
                this.params.add_new = true;
            },
            lineAddStart(line, offset_x, offset_y) {
                this.lineDragStart(line, offset_x, offset_y);
                this.params.add_new = true;
            },
            lineDragStart(line, offset_x, offset_y) {
                this.params.drag_line = line;
                this.params.drag_offset_x = offset_x;
                this.params.drag_offset_y = offset_y;
            },
            pointDragStart(line, point, offset_x, offset_y) {
                this.params.drag_point = point;
                this.lineDragStart(line, offset_x, offset_y);
            },
            dragFinishedDrop(eve, sec, pos, top_lvl) {
                let Y = eve.offsetY || eve.y, X = eve.offsetX || eve.x, i;
                let path = eve.path || eve.composedPath();
                for (i = 0; i < path.length; i++) {
                    if (String(path[i].nodeName).toLowerCase() === 'td') break;
                    X += path[i].offsetLeft;
                    Y += path[i].offsetTop;
                }
                X = this._getoffleft(X, sec, pos);
                Y = this._getofftop(Y, top_lvl);

                if (this.params.drag_line) {
                    this.saveLine(sec, pos, Y, X);
                } else {
                    this.dragMassEqpt(top_lvl, sec, pos, Y, X);
                }
            },
            dragFinishedClick(eve, sec, pos, top_lvl) {
                let cmdOrCtrl = eve.metaKey || eve.ctrlKey;
                if (this.params.drag_eqpt) {
                    let Y = eve.offsetY || eve.y, X = eve.offsetX || eve.x;
                    X = this._getoffleft(X, sec, pos);
                    Y = this._getofftop(Y, top_lvl);
                    // center on adding by 'click'
                    X -= this.params.drag_eqpt.get_wi(this.params)/2 * this.px_in_ft;
                    Y += this.params.drag_eqpt.calc_dy/2;

                    this.saveEqpt(top_lvl, sec, pos, Y, X, 'eqpt');
                } else if(!cmdOrCtrl) {
                    this.params.clearSel();
                    this.sett_select_eqpt = null;
                }
            },
            _getofftop(Y, top_lvl) {
                let off_top = Y - this.params.drag_offset_y;
                off_top = Math.round(off_top / this.px_in_ft * 100) / 100;
                off_top = top_lvl
                    ? top_lvl - off_top
                    : (this.params.bot_elev + this.params.heVal('bot_he')) - off_top;
                return off_top;
            },
            _getoffleft(X, sec, pos) {
                return this.params.is_eqpt_rev()
                    ? this.params.sectorWi(sec, pos, this.px_in_ft) - X
                    : X;
            },

            //save drag&drop position
            dragMassEqpt(top_lvl, sector, pos, off_top, X) {
                let cmdOrCtrl = window.event.metaKey || window.event.ctrlKey;
                let should_copy = !!cmdOrCtrl || this.params.add_new;

                let sidx = sector ? sector._idx : -1;
                let pidx = pos ? pos._idx : -1;

                let ee = this.params.drag_eqpt || new Eqpt();
                let eqpt_left = this.params.calcSectorPosOffset(ee._sec_idx, ee._pos_idx, this.px_in_ft) + ee.posLeft(this.params)*this.px_in_ft;
                let glob_left = this.params.calcSectorPosOffset(sidx, pidx, this.px_in_ft) + X - this.params.drag_offset_x;
                glob_left -= eqpt_left;
                let glob_top = (off_top - ee.elevVal(this.params.elev_by)) * this.px_in_ft;

                let axios_eqpts = [];
                let eqpt_corrs = {};
                let app_tb = this.popup_tables.eqptdata_2d;

                _.each(this.params.mass_eqpt, (eqpt) => {
                    let loc_top = (eqpt.elevVal(this.params.elev_by))*this.px_in_ft + glob_top;
                    let loc_left = this.params.calcSectorPosOffset(eqpt._sec_idx, eqpt._pos_idx, this.px_in_ft) + eqpt.posLeft(this.params)*this.px_in_ft + glob_left;

                    let result = this.params.getSectorAndPos(loc_left, loc_top, this.px_in_ft);

                    let new_eqpt = (should_copy ? _.clone(eqpt) : eqpt);
                    let newleft = (loc_left - result.left) / this.px_in_ft;
                    new_eqpt.posLeft(this.params, newleft);
                    new_eqpt.sector = result.sector ? result.sector.sector : '';
                    new_eqpt.pos = result.pos ? result.pos.name : '';
                    new_eqpt.location = top_lvl ? 'Air' : 'Base';
                    new_eqpt.putTopCoord(this.params.elev_by, loc_top/this.px_in_ft);
                    new_eqpt.setCoordIdx(this.params._sectors, this.params._pos);

                    (should_copy ? this.data_eqpt.push(new_eqpt) : null);

                    let m_params = (should_copy ? this.masterParams(app_tb) : null);
                    axios_eqpts.push(
                        new_eqpt.quickSave(app_tb, m_params).then((resp) => {
                            eqpt_corrs[new_eqpt._id] = resp.data.res;
                            new_eqpt._id = resp.data.res;
                        })
                    );
                });

                Promise.all(axios_eqpts).then(() => {
                    let conn_tb = this.popup_tables.linedata_2d;
                    let axios_lines = [];
                    _.each(this.data_conn, (line) => {
                        let copy_lines = should_copy && eqpt_corrs[line.from_eqpt_id] && eqpt_corrs[line.to_eqpt_id];
                        let update_lines = !should_copy && (eqpt_corrs[line.from_eqpt_id] || eqpt_corrs[line.to_eqpt_id]);
                        if (copy_lines || update_lines) {
                            let new_line = (should_copy ? _.clone(line) : line);
                            if (should_copy) {
                                new_line.from_eqpt_id = eqpt_corrs[line.from_eqpt_id];
                                new_line.to_eqpt_id = eqpt_corrs[line.to_eqpt_id];
                                new_line.clearPos();
                                new_line.findEqpts(this.data_eqpt);
                            }
                            this.setLineBetweenEqpt(new_line);

                            (should_copy ? this.data_conn.push(new_line) : null);

                            let m_params = (should_copy ? this.masterParams(conn_tb) : null);
                            axios_lines.push( new_line.quickSave(conn_tb, m_params) );
                        }
                    });

                    this.redrawAll(); // redraw canvas
                    Promise.all(axios_lines).then(() => {
                        this.params.clearSel();
                        this.reloadTablda(app_tb); // reload rows in another StimApp tabs

                        if (should_copy) {
                            this.load2D(true, should_copy);
                        } else {
                            this.redrawAll(); // reload all data
                            this.$nextTick(() => {
                                this.load2Dfilters();
                            });
                        }
                    });
                });
            },
            saveEqpt(top_lvl, sector, pos, off_top, X, sel_exclude) {
                let app_tb = this.popup_tables.eqptdata_2d;

                let eqpt = this.params.drag_eqpt;
                let newleft = (X - this.params.drag_offset_x) / this.px_in_ft;
                eqpt.posLeft(this.params, newleft);
                eqpt.sector = sector ? sector.sector : '';
                eqpt.pos = pos ? pos.name : '';
                eqpt.location = top_lvl ? 'Air' : 'Base';
                eqpt.putTopCoord(this.params.elev_by, off_top);
                eqpt.setCoordIdx(this.params._sectors, this.params._pos);

                if (this.params.add_new) {
                    eqpt.qty = 1;
                    this.data_eqpt.push(eqpt);
                }

                //recalc positions and save connected Lines
                let conn_tb = this.popup_tables.linedata_2d;
                let axiosPromises = [];
                _.each(this.data_conn, (line) => {
                    let can = line._from_eqpt && line._to_eqpt;
                    if (can && (line.from_eqpt_id == eqpt._id || line.to_eqpt_id == eqpt._id)) {
                        this.setLineBetweenEqpt(line);
                        axiosPromises.push( line.quickSave(conn_tb) );
                    }
                });
                //save Eqpt
                Promise.all(axiosPromises).then(() => {
                    this.callSaveModel('drag_eqpt', app_tb, 'eqpt', null, sel_exclude);
                });
            },
            saveLine(sec, pos, off_top, X, sel_exclude) {
                let s_idx = _.findIndex(this.params._sectors, {_id: sec._id});
                let p_idx = _.findIndex(this.params._pos, {_id: pos._id});
                let app_tb = this.popup_tables.linedata_2d;
                let linleft = (X - this.params.drag_offset_x) / this.px_in_ft;
                linleft += this.params.calcSectorPosOffset(s_idx, p_idx, this.px_in_ft) / this.px_in_ft;
                linleft = Math.round(linleft * 100) / 100;

                if (this.params.drag_point) {
                    let k_control = this.params.getLineControlKey();
                    let k_obj = this.params.getLineObjKey();
                    //work with control points
                    if (this.params.drag_point.is_new) {
                        let point = {
                            top: off_top,
                            left: linleft,
                            ord: Number(this.params.drag_point.order),
                        };
                        this.params.drag_line[k_obj].push( point );
                    } else {
                        let p_point = _.find(this.params.drag_line[k_obj], {ord: Number(this.params.drag_point.order)});
                        p_point.top = off_top;
                        p_point.left = linleft;
                    }
                    this.params.drag_line[k_control] = JSON.stringify( this.params.drag_line[k_obj] );
                } else {
                    //work with line
                    let k_top = this.params.getLineTopKey();
                    let k_left = this.params.getLineLeftKey();
                    this.params.drag_line[k_top] = off_top;
                    this.params.drag_line[k_left] = linleft;
                }

                if (this.params.add_new) {
                    this.data_conn.push(this.params.drag_line);
                }

                this.callSaveModel('drag_line', app_tb, 'line', null, sel_exclude);
            },
            changeEqpt(eqpt, sel_exclude) {
                let app_tb = this.popup_tables.eqptdata_2d;
                this.applyCanvColors();//apply colors
                this.callSaveModel('', app_tb, 'eqpt', eqpt, sel_exclude);
            },
            changeLine(line, sel_exclude) {
                let app_tb = this.popup_tables.linedata_2d;
                this.applyCanvColors();//apply colors
                this.callSaveModel('', app_tb, 'line', line, sel_exclude);
            },
            callSaveModel(drag_key, app_tb, type, direct, sel_exclude) {
                if (this.$root.user.see_view) {
                    return;
                }
                if (this.params.add_new && drag_key) {
                    let item = _.clone(this.params[drag_key]);
                    (type === 'eqpt' ? this.data_eqpt.push(item) : null);
                    (type === 'line' ? this.data_conn.push(item) : null);
                }
                this.redrawAll(); // redraw canvas

                axios.post('?method=save_model', {
                    app_table: app_tb,
                    model: direct || this.params[drag_key],
                    master_params: this.params.add_new ? this.masterParams(app_tb) : null,
                }).then(({data}) => {
                    if (this.params.add_new) {
                        this.load2D(true); // reload all data
                        //this.params.add_new = false; //disabled - multi adding from LIB is not working
                    } else {
                        this.load2Dfilters();
                    }
                    this.reloadTablda(app_tb); // reload rows in another StimApp tabs
                    this.params.clearSel(sel_exclude);
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                });
            },
            masterParams(app_tb) {
                let stim_link_params = this.vuex_links[app_tb];
                let row = {};
                _.each(stim_link_params.link_fields, (fld) => {
                    row[fld.data_field] = this.master_row[fld.link_field_db];
                });
                return row;
            },

            //load data
            load2Dfilters() {
                this.load2D(true, true, true);
            },
            load2D(no_clear_sel, no_redraw, just_filters) {
                if (!just_filters) {
                    this.params.global_draggable = false;
                    this.$nextTick(() => {
                        this.init_wrap_x = this.$refs.wrap_canvas ? this.$refs.wrap_canvas.clientWidth : 0;
                        this.init_wrap_y = this.$refs.wrap_canvas ? this.$refs.wrap_canvas.clientHeight - this.params.get_glob_top() : 0;
                        this.setWrapSize();
                    });
                }

                axios.post('?method=load_2d_data', {
                    type: 'configurator',
                    app_table: this.master_table,
                    master_model: this.master_row,
                    just_filters: !!just_filters,
                }).then(({data}) => {

                    if (just_filters) {
                        this.prepareAndApplyFilters(data.data_filters);
                    } else {
                        this.popup_tables = data.popup_tables;
                        this.params.applySettings(data.g_settings, no_clear_sel);

                        this.setData(data);
                        this.setSettings();

                        this.checkStatClrs(data.colors_eq);
                        this.applyCanvColors();

                        this.prepareAndApplyFilters(data.data_filters);

                        (no_redraw ? null : this.redrawAll()); // redraw canvas

                        this.params.global_draggable = true;
                    }

                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                });
            },
            setData(data) {
                this.eqpt_lib = _.map(data.eqpt_lib, (eq) => { return new Eqpt(eq, this.params._air_name); });
                this.line_lib = _.map(data.line_lib, (eq) => { return new Line(eq); });

                this.data_eqpt = _.map(data.data_eqpt, (eq) => { return new Eqpt(eq, this.params._air_name); });
                this.data_conn = _.map(data.data_conn, (eq) => { return new Line(eq, this.data_eqpt); });

                this.status_list = _.map(data.colors_eq, (eq) => { return new Status(eq); });
                this.tech_list = _.map(data.tech_list, (eq) => { return new Tech(eq); });
                this.elevs_list = _.map(data.elevs_lib, (eq) => { return new Elev(eq); });
                this.azimuth_list = _.map(data.azimuth_lib, (eq) => { return new Azimuth(eq); });

                let secrs = data.sectors.length
                    ? _.map(data.sectors, (eq,i) => { return new Sector(eq,i); })
                    : [new Sector({},0)];

                secrs = _.orderBy(secrs, (sector) => to_float(sector.face_norm));
                this.params._sectors = _.filter(secrs, (sec) => {
                    return !in_array(sec.sector, this.params._shared_sectors_arr)
                });

                this.params._pos = data.pos.length
                    ? _.map(data.pos, (eq,i) => { return new Pos(eq,i); })
                    : [new Pos({},0)];

                _.each(this.data_eqpt, (eqpt) => {
                    eqpt.setCoordIdx(this.params._sectors, this.params._pos);
                });

                this.total_cols = _.sumBy(this.params._sectors, (el) => { return to_float(el._tot_pos_num); });

                this.all_sectors = _.clone(this.params._shared_sectors_arr);
                _.each(this.params._sectors, (s) => { this.all_sectors.push(s.sector) });
            },
            setSettings() {
                if (!this.params.sumHe()) {
                    let only_pos = this.params.filter_eqpts(this.data_eqpt, 'pos');
                    let pos_dy = _.maxBy(only_pos, 'calc_dy');
                    pos_dy = pos_dy ? Math.max(pos_dy.calc_dy, 10) : 10;

                    let only_sec = this.params.filter_eqpts(this.data_eqpt, 'sec');
                    let sec_dy = _.maxBy(only_sec, 'calc_dy');
                    sec_dy = sec_dy ? Math.max(sec_dy.calc_dy, 3) : 3;

                    let only_rest = this.params.filter_eqpts(this.data_eqpt, 'rest');
                    let rest_dy = _.maxBy(only_rest, 'calc_dy');
                    rest_dy = rest_dy ? Math.max(rest_dy.calc_dy, 3) : 3;

                    let only_bot = this.params.filter_eqpts(this.data_eqpt, 'bot');
                    let bot_dy = _.maxBy(only_bot, 'calc_dy');
                    bot_dy = bot_dy ? Math.max(bot_dy.calc_dy, 3) : 3;

                    let cap_he = Math.ceil(14 / this.px_in_ft); //eqpt caption height
                    this.params.caption_he = cap_he;

                    pos_dy = Math.ceil(pos_dy * 1.2 + cap_he);
                    sec_dy = Math.ceil(sec_dy * 1.2 + cap_he);
                    rest_dy = Math.ceil(rest_dy * 1.2 + cap_he);
                    bot_dy = Math.ceil(bot_dy * 1.2 + cap_he);

                    this.params.setHeights(pos_dy, sec_dy, rest_dy, bot_dy);

                    let max = _.maxBy(this.data_eqpt, (eqpt) => {
                        return eqpt.getTopCoord(this.params.elev_by);
                    });
                    max = Math.ceil( max ? (max.getTopCoord(this.params.elev_by) + 1) : 0 );

                    this.params.setElevs(0, max);
                }

                this.params.saveSettings();
            },
            checkStatClrs(model_colors) {
                let all_statuses = _.map(this.data_eqpt, 'status');
                all_statuses = all_statuses.concat( _.map(this.data_conn, 'status') );

                let res = {};
                model_colors.push({name: 'null', color: null});
                _.each(model_colors, (clr) => {
                    res[clr.name] = {
                        key: clr.name,
                        model_val: clr.color,
                        show: in_array(clr.name, all_statuses),
                    };
                });
                this.EqptStatuses.colors = res;
            },
            applyCanvColors() {
                _.each(this.data_eqpt, (el) => {
                    if (this.EqptStatuses.view_enabled) {
                        let f = _.find(this.EqptStatuses.colors, {'key': el.status});
                        el.show_color = f && f.model_val ? f.model_val : el.color;
                    } else {
                        el.show_color = el.color;
                    }
                });
                _.each(this.data_conn, (line) => {
                    if (this.EqptStatuses.view_enabled) {
                        let f = _.find(this.EqptStatuses.colors, {'key': line.status});
                        line.show_color = f && f.model_val ? f.model_val : line.color;
                    } else {
                        line.show_color = line.color;
                    }
                });
            },
            setFilters(d_filters) {
                //save currently unchecked options
                if (this.EqptStatuses.eqpt_filters) {
                    _.each(d_filters, (filter) => {
                        let old_filter = _.find(this.EqptStatuses.eqpt_filters, (old) => {
                            return old.id === filter.id;
                        });
                        if (old_filter) {
                            filter.applied_index = old_filter.applied_index;
                            _.each(filter.values, (vv) => {
                                let found = _.find(old_filter.values, {val: vv.val});
                                vv.checked = Boolean(!found || found.checked);
                            });
                        }
                    });
                }

                //set filters
                this.EqptStatuses.eqpt_filters = d_filters;

                //limit options to values of currently loaded model
                _.each(this.EqptStatuses.eqpt_filters, (filter) => {
                    let eq_key = this.eqpt_all_rows.convertKey(filter.field);
                    filter.values = _.filter(filter.values, (vv) => {
                        return _.find(this.data_eqpt, (eqpt) => {
                            if (this.$root.isMSEL(filter.input_type)) {
                                return String(eqpt[eq_key]).indexOf('"' + vv.val + '"') > -1;
                            } else {
                                return eqpt[eq_key] == vv.val;
                            }
                        });
                    });
                });
            },
            prepareAndApplyFilters(d_filters) {
                this.autoloadMetaEqpt(d_filters);
                this.setFilters(d_filters);
                this.changedFilter();
            },
            autoloadMetaEqpt(d_filters) {
                if (!this.eqpt_tablda_table && this.popup_tables) {
                    let eqpt_tb = this.popup_tables.filters_2d;

                    this.eqpt_tablda_table = this.vuex_fm[eqpt_tb].meta;
                    this.eqpt_tablda_table.loadHeaders();
                    this.eqpt_all_rows = this.vuex_fm[eqpt_tb].rows;

                    let avail_keys = _.map(this.eqpt_all_rows.maps, (tablda) => { return tablda; });
                    this.eqpt_avail_filters = _.intersection(avail_keys, this.vuex_links[eqpt_tb].avail_cols_for_app);
                }
            },
            changedFilter() {
                //show all
                _.each(this.data_eqpt, (eqpt) => { eqpt._hidden = false; });
                _.each(this.data_conn, (line) => { line._hidden = false; });

                //hide filtered
                _.each(this.EqptStatuses.eqpt_filters, (filter) => {
                    let eq_key = this.eqpt_all_rows.convertKey(filter.field);

                    if (eq_key && filter.values && filter.values.length) {
                        _.each(this.data_eqpt, (eqpt) => {
                            let found = null;
                            if (this.$root.isMSEL(filter.input_type)) {
                                found = _.find(filter.values, (vv) => {
                                    return String(eqpt[eq_key]).indexOf('"' + vv.val + '"') > -1;
                                });
                            } else {
                                found = _.find(filter.values, {val: eqpt[eq_key]});
                            }
                            eqpt._hidden = eqpt._hidden || (found && !found.checked);
                        });
                    }

                });
                _.each(this.data_conn, (line) => {
                    line._hidden = !line._from_eqpt || line._from_eqpt._hidden
                        || !line._to_eqpt || line._to_eqpt._hidden;
                });
            },
            reloadFull(ex_app_tb) {
                this.reloadTablda(ex_app_tb || this.popup_app_tb);
                this.load2D();
            },
            reloadTablda(app_tb) {
                let id = this.vuex_fm[app_tb].meta.table_id;
                _.each(this.vuex_fm, (f_model) => {
                    if (f_model && f_model.meta && f_model.meta.table_id === id) {
                        f_model.rows.is_loaded = false;
                    }
                });
            },
            redrawAll() {
                //hide Lines for hidden Eqpts
                let hid_rows = '';
                hid_rows += !this.params.heVal('pos_he') ? 'pos,' : '';
                hid_rows += !this.params.heVal('sector_he') ? 'sec,' : '';
                hid_rows += !this.params.heVal('rest_he') ? 'rest,' : '';
                hid_rows += !this.params.heVal('bot_he') ? 'bot,' : '';

                let hid_eqpt_ids = _.map(this.params.filter_eqpts(this.data_eqpt, hid_rows), '_id');
                _.each(this.data_conn, (line) => {
                    line._eqpt_hidden = in_array(line.from_eqpt_id, hid_eqpt_ids) || in_array(line.to_eqpt_id, hid_eqpt_ids);
                });
                //^^^^^^^

                let scroll_x = this.$refs.wrap_canvas.scrollLeft;
                let scroll_y = this.$refs.wrap_canvas.scrollTop;
                this.canDraw = false;
                this.$nextTick(() => {
                    this.canDraw = true;
                    this.$refs.wrap_canvas.scrollTo(scroll_x, scroll_y);
                });
            },

            //direct popups
            modelidChaged(eqpt, from) {
                _.each(this.data_eqpt, (eq) => {
                    if (eq.locmod_id === from) {
                        eq.locmod_id = eqpt.locmod_id;
                    }
                });
                let app_tb = this.popup_tables.eqptlib_2d;
                eqpt.quickSave(app_tb);
                this.reloadTablda(app_tb);
                this.load2Dfilters();
            },
            settStyle() {
                return {
                    position: 'fixed',
                    top: this.sett_top+'px',
                    left: this.sett_left+'px',
                };
            },
            showSettSelect(eqpt) {
                this.sett_select_eqpt = eqpt;
                this.sett_top = this.$root.lastMouseClick.clientY;
                this.sett_left = this.$root.lastMouseClick.clientX;
            },

            popupLibElem(category, row_id) {
                switch (category) {
                    case 'model': this.popupModel(row_id); break;
                    case 'eqpt_lib': this.popupEqptLib(row_id); break;
                    case 'feedline': this.popupFeedline(row_id); break;
                    case 'line_lib': this.popupLineLib(row_id); break;
                    case 'tech': this.popupTech(row_id); break;
                    case 'status': this.popupStatus(row_id); break;
                    case 'elev': this.popupElev(row_id); break;
                    case 'azimuth': this.popupAzimuth(row_id); break;
                }
                this.link_rows = this.vuex_fm[this.popup_app_tb] ? this.vuex_fm[this.popup_app_tb].rows : null;
            },
            popupEqpt(row_id) {
                this.popup_row_id = row_id;
                this.popup_app_tb = this.popup_tables.eqptdata_2d;
                this.popup_type = 'eqpt';
            },
            popupEqptSett(row_id) {
                this.popup_row_id = row_id;
                this.popup_app_tb = this.popup_tables.eqptsett_2d;
                this.popup_type = 'eqpt_sett';
            },
            popupModel(row_id) {
                this.popup_row_id = row_id;
                this.popup_app_tb = this.vuex_settings.popups_models.equipment;
                this.popup_type = 'model';
            },
            popupEqptLib(row_id) {
                this.popup_row_id = row_id;
                this.popup_app_tb = this.popup_tables.eqptlib_2d;
                this.popup_type = 'eqpt_lib';
            },
            popupLineLib(row_id) {
                this.popup_row_id = row_id;
                this.popup_app_tb = this.popup_tables.linelib_2d;
                this.popup_type = 'line_lib';
            },
            popupPos(row_id) {
                this.popup_row_id = row_id;
                this.popup_app_tb = this.popup_tables.pos_2d;
                this.popup_type = 'pos';
            },
            popupSector(row_id) {
                this.popup_row_id = row_id;
                this.popup_app_tb = this.popup_tables.sectors_2d;
                this.popup_type = 'sector';
            },
            popupTech(row_id) {
                this.popup_row_id = row_id;
                this.popup_app_tb = this.popup_tables.tech_2d;
                this.popup_type = 'tech';
            },
            popupStatus(row_id) {
                this.popup_row_id = row_id;
                this.popup_app_tb = this.popup_tables.status_2d;
                this.popup_type = 'status';
            },
            popupElev(row_id) {
                this.popup_row_id = row_id;
                this.popup_app_tb = this.popup_tables.elevs_2d;
                this.popup_type = 'elev';
            },
            popupAzimuth(row_id) {
                this.popup_row_id = row_id;
                this.popup_app_tb = this.popup_tables.azimuth_2d;
                this.popup_type = 'azimuth';
            },
            popupConn(row_id) {
                this.popup_row_id = row_id;
                this.popup_app_tb = this.popup_tables.linedata_2d;
                this.popup_type = 'line';
            },
            popupFeedline(row_id) {
                this.popup_row_id = row_id;
                this.popup_app_tb = this.vuex_settings.popups_models.feedline;
                this.popup_type = 'feedline';
            },

            //Direct Popups
            preInsert(startHash, tableRow) {
                //
            },
            preUpdate(startHash, tableRow) {
                //
            },
            preDelete(startHash, tableRow) {
                //
            },
            directInsert(data) {
                if (data.rows && data.rows.length) {
                    this.popup_row_id = data.rows[0].id;
                }
                this.reloadFull();
            },
            directUpdate(data) {
                this.reloadFull();
            },
            directDelete(data) {
                this.popup_row_id = null;
                this.reloadFull();
            },
            anotherDirectRow(is_next) {
                let rowsObj = this.vuex_fm[this.popup_app_tb].rows;
                if (!rowsObj.is_loaded) {
                    let meta = this.vuex_fm[this.popup_app_tb].meta.params;
                    rowsObj.setModelAndGroup(this.master_row, this.vuex_links[this.popup_app_tb]);
                    rowsObj.loadRows(meta).then(() => {
                        this.$root.anotherPopup(rowsObj.all_rows, this.popup_row_id, is_next, this.selectAnotherRow);
                    });
                } else {
                    this.$root.anotherPopup(rowsObj.all_rows, this.popup_row_id, is_next, this.selectAnotherRow);
                }
            },
            selectAnotherRow(idx) {
                let rows = this.vuex_fm[this.popup_app_tb].rows.all_rows;
                this.popup_row_id = rows[idx].id;
                if (this.popup_type === 'eqpt') {
                    this.params.selEqpt( _.find(this.data_eqpt, {_id: rows[idx].id}) );
                }
                if (this.popup_type === 'line') {
                    let c_ord = (512 + 0) / 2;
                    this.params.selLine( _.find(this.data_conn, {_id: rows[idx].id}), c_ord );
                }
            },
        },
    }
</script>