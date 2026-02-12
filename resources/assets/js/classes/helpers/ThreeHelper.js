export class ThreeHelper {

    /**
     *
     * @param load_data
     * @param master_model
     */
    constructor(load_data, master_model) {
        //REQUIRED
        this.load_data = load_data;
        this.master_model = master_model;
        this.progress_done = 0;
        this.progress_total = 1;
        this.counter = 0;
        this.forbidden_local = [];
    }

    /**
     *
     * @param direct_eqpt_3d
     * @returns {number}
     */
    affectedEqpts(direct_eqpt_3d) {
        let affected_cnt = 0;
        _.each(this.load_data.params.members, (memb) => {
            let affected = _.filter(this.load_data.eqs.collection, (eqpt) => {
                return this._canRL(direct_eqpt_3d, eqpt, memb);
            });
            affected_cnt += affected.length;
        });
        return affected_cnt;
    }

    /**
     *
     * @param direct_eqpt_3d
     */
    startCalculationRL(direct_eqpt_3d) {
        axios.post('?method=rl_elem_delete', {
            local_id: direct_eqpt_3d ? direct_eqpt_3d.local_id : null,
            master_model: this.master_model,
        }).then(({data}) => {
            this._prepareEqpts(direct_eqpt_3d);
            this._calculateProcess(direct_eqpt_3d);
        }).catch(errors => {
            Swal('Info', getErrors(errors));
        });
    }

    /**
     *
     * @private
     */
    _prepareEqpts(direct_eqpt_3d) {
        this.counter = 0;
        this.forbidden_local = [];

        _.each(this.load_data.eqs.collection, (eqpt) => {
            if (eqpt.lc.local_id) {
                if (this.forbidden_local.indexOf(parseInt(eqpt.lc.local_id)) > -1) {
                    eqpt.lc.local_id = null;
                } else {
                    this.forbidden_local.push(parseInt(eqpt.lc.local_id));
                }
            }
        });

        _.each(this.load_data.eqs.collection, (eqpt) => {
            if (!eqpt.lc.local_id) {
                eqpt.lc.local_id = this._nextCounter();
                axios.post('?method=store_eqpt_local_id', {
                    eqpt_id: eqpt.lc._id,
                    local_id: eqpt.lc.local_id,
                });
            }
        });

        this.progress_done = 0;
        this.progress_total = 0;
        _.each(this.load_data.params.members, (memb) => {
            _.each(this.load_data.eqs.collection, (eqpt) => {
                this.progress_total += this._canRL(direct_eqpt_3d, eqpt, memb) ? 1 : 0;
            });
        });
    }

    /**
     *
     * @returns {number}
     * @private
     */
    _nextCounter() {
        this.counter++;
        while (this.forbidden_local.indexOf( this.counter ) > -1) {
            this.counter++;
        }
        return this.counter;
    }

    /**
     *
     * @param direct_eqpt_3d
     * @param cur_eqpt
     * @param memb
     * @returns {{mbr_name: string, dx: number, rotx: number, dy: number, dz: number, rotz: number, roty: number, id: number, status: string}|*|null}
     * @private
     */
    _canRL(direct_eqpt_3d, cur_eqpt, memb) {
        let eqpt_lc = direct_eqpt_3d || cur_eqpt.lc;
        let pos2mbr = _.find(this.load_data.libs.pos_to_mbrs, (p2m) => {
            return p2m._id == eqpt_lc.mbr_id || p2m.member == eqpt_lc.mbr_id;
        });

        let avail_eqpt = !direct_eqpt_3d || direct_eqpt_3d._id == cur_eqpt.lc._id;
        let some_attach = ThreeHelper.get_attach(eqpt_lc, 1) || ThreeHelper.get_attach(eqpt_lc, 2) || ThreeHelper.get_attach(eqpt_lc, 3);
        let needed_member = (memb['_id'] == eqpt_lc.mbr_id) || (pos2mbr && pos2mbr.member == memb['Mbr_Name']);
        return avail_eqpt && some_attach && needed_member
            ? eqpt_lc
            : null;
    }

    /**
     *
     * @param direct_eqpt_3d
     * @private
     */
    _calculateProcess(direct_eqpt_3d) {
        if (this.load_data) {

            _.each(this.load_data.params.members, (memb) => {

                let memberS = null;
                let memberE = null;
                _.each(this.load_data.params.nodes, (nod) => {//
                    if (memb.NodeS == nod.node_name) {
                        memberS = [to_float(nod.x), to_float(nod.y), to_float(nod.z)];
                    }
                    if (memb.NodeE == nod.node_name) {
                        memberE = [to_float(nod.x), to_float(nod.y), to_float(nod.z)];
                    }
                });

                _.each(this.load_data.eqs.collection, (eqpt) => {
                    let eqpt_lc = this._canRL(direct_eqpt_3d, eqpt, memb);
                    if (eqpt_lc) {
                        let membTop = new THREE.Vector3(memberS[0], memberS[1], memberS[2]);
                        let membBot = new THREE.Vector3(memberE[0], memberE[1], memberE[2]);
                        let memb_line = new THREE.Line3(membTop, membBot);
                        let memb_mesh = new THREE.Mesh(new THREE.Geometry(), new THREE.MeshLambertMaterial());
                        memb_mesh.position.copy(memb_line.center());
                        memb_mesh.lookAt(membBot);
                        memb_mesh.rotateX(Math.PI / 2);
                        memb_mesh.rotateY(-Math.PI / 2);
                        let eulerPar = new THREE.Euler((memb_mesh.rotation.x), (memb_mesh.rotation.y), (memb_mesh.rotation.z), 'XYZ');
                        let newD = ThreeHelper.eqpt_center(eqpt_lc, memb_mesh);

                        let glob_top = new THREE.Vector3(0, 1, 0);
                        glob_top.applyEuler(eulerPar);

                        let d1 = to_float(eqpt.eq.d1 / 12 / 2);//Half and in. => ft.
                        let eTop = new THREE.Vector3(to_float(newD.dx), to_float(newD.dy) + d1, to_float(newD.dz));
                        eTop.applyEuler(eulerPar);
                        let eBot = new THREE.Vector3(to_float(newD.dx), to_float(newD.dy) - d1, to_float(newD.dz));
                        eBot.applyEuler(eulerPar);

                        let memb_center = memb_line.center();
                        let eqptTop = new THREE.Vector3(eTop.x + memb_center.x, eTop.y + memb_center.y, eTop.z + memb_center.z);
                        let eqptBot = new THREE.Vector3(eBot.x + memb_center.x, eBot.y + memb_center.y, eBot.z + memb_center.z);

                        let attchs = parseInt(eqpt_lc.attach_num_locs) || 3;
                        for (let i = 1; i <= attchs; i++) {
                            this._createRL(i, eqpt_lc, {
                                memb_top: glob_top.y > 0 ? membTop : membBot,
                                memb_bot: glob_top.y > 0 ? membBot : membTop,
                                eqpt_top: glob_top.y > 0 ? eqptTop : eqptBot,
                                eqpt_bot: glob_top.y > 0 ? eqptBot : eqptTop,
                            });
                        }
                    }
                });
            });
            ///////
        }
    }

    /**
     *
     * @param i
     * @param eqpt_lc
     * @param params
     * @private
     */
    _createRL(i, eqpt_lc, params) {
        if (ThreeHelper.get_attach(eqpt_lc, i)) {
            let from = ThreeHelper.get_attach(eqpt_lc, i);
            let node_fr = ['bot', 'mid'].indexOf(from) > -1 ? params.eqpt_bot : params.eqpt_top;
            let node_to = ['bot', 'mid'].indexOf(from) > -1 ? params.eqpt_top : params.eqpt_bot;
            let dist = to_float(eqpt_lc['attach_value_' + i]);
            if (['mid'].indexOf(from) > -1) {
                dist += node_fr.distanceTo(node_to) / 2;//offset to 'Mid'.
            }
            let node_RL_eqpt = ThreeHelper.calc_along(node_fr, node_to, dist);
            let node_RL_mbr = ThreeHelper.calc_perp(params.memb_top, params.memb_bot, node_RL_eqpt);

            let vertE = new THREE.Vector3(node_RL_eqpt.x, node_RL_eqpt.y, node_RL_eqpt.z);
            let vertM = new THREE.Vector3(node_RL_mbr.x, node_RL_mbr.y, node_RL_mbr.z);

            let llc = _.clone(eqpt_lc);
            if (!llc.mbr_name) {
                let pos2mbr = _.find(this.load_data.libs.pos_to_mbrs, (p2m) => {
                    return p2m._id == eqpt_lc.mbr_id || p2m.member == eqpt_lc.mbr_id;
                });
                llc.mbr_name = pos2mbr.member;
            }

            axios.post('?method=rl_elem_store', {
                attach_idx: i,
                eqpt_lc: llc,
                master_model: this.master_model,
                rl_node_eqpt: node_RL_eqpt,
                rl_node_member: node_RL_mbr,
                distance: vertE.distanceTo(vertM),
            }).then(({data}) => {
                this.progress_done++;
            }).catch(errors => {
                Swal('Info', getErrors(errors));
            });
        }
    }

    /**
     *
     * @returns {Number}
     */
    getProgress() {
        return parseInt(this.progress_done / this.progress_total * 100);
    }

    /**
     *
     * @param app_table
     * @param master_model
     * @returns {AxiosPromise<any>}
     */
    static loadDataServer(app_table, master_model) {
        return axios.post('?method=load_3d_rows', {
            type_3d: '3d:ma',
            app_table: app_table,
            master_model: master_model,
            excluded_colors: [],
            front_filters: {},
        });
    }

    /**
     *
     * @param eqpt_lc
     * @param i
     * @returns {string}
     */
    static get_attach(eqpt_lc, i) {
        return String(eqpt_lc['attach_from_' + i] || '').toLowerCase();
    }

    /**
     *
     * @param lc
     * @param parent
     * @returns {{dx: (number), dy: (number), dz: (number), roty: number}}
     */
    static eqpt_center(lc, parent) {
        let globtopy = 0;
        if (parent) {
            let euler = new THREE.Euler(-(parent.rotation.x), -(parent.rotation.y), -(parent.rotation.z), 'ZYX');
            let glob_top = new THREE.Vector3(0, 1, 0);
            glob_top.applyEuler(euler);
            globtopy = glob_top.y;
        }

        var newCoords = {
            dx: to_float(lc.dx),
            dy: (globtopy > 0 ? 1 : -1) * to_float(lc.dy),
            dz: to_float(lc.dz),
            roty: 0,
        };

        // if(lc.rotz) {
        //     var oldX = newCoords.x,
        //         oldY = newCoords.y,
        //         rotZ = lc.rotz * (Math.PI/180);
        //
        //     newCoords.x = oldX * Math.cos(rotZ) - oldY * Math.sin(rotZ);
        //     newCoords.y = oldX * Math.sin(rotZ) + oldY * Math.cos(rotZ);
        // }

        let add_roty = (globtopy > 0 ? 0 : 180) * (Math.PI / 180);
        if (lc.roty || add_roty) {
            var oldX = to_float(newCoords.dx),
                oldZ = to_float(newCoords.dz),
                rotY = -to_float(lc.roty) * (Math.PI / 180);
            rotY += add_roty;

            newCoords.dx = oldX * Math.cos(rotY) - oldZ * Math.sin(rotY);
            newCoords.dz = oldX * Math.sin(rotY) + oldZ * Math.cos(rotY);
            newCoords.roty = add_roty;
        }

        // if(lc.rotx) {
        //     var oldY = newCoords.y,
        //         oldZ = newCoords.z,
        //         rotX = lc.rotx * (Math.PI/180);
        //
        //     newCoords.y = oldY * Math.cos(rotX) - oldZ * Math.sin(rotX);
        //     newCoords.z = oldY * Math.sin(rotX) + oldZ * Math.cos(rotX);
        // }

        return newCoords;
    }

    /**
     *
     * @param lc
     * @param parent
     * @param watched
     * @returns {*}
     */
    static limit_by_member(lc, parent, watched)
    {
        let w3d = this.watcher3d_field();
        let g_member_center = to_float(lc._ma_gctr) + to_float(parent.position.y);
        let half_member_len = to_float(parent.member_len) / 2;

        if (w3d === 'origin_elev') {
            //
        }
        if (w3d === 'elev_g') {
            let glob_idx = this.glob_top_vector(parent, true);
            let diff = to_float(watched.val) - g_member_center;
            if (glob_idx > 0 && Math.abs(diff / glob_idx) > half_member_len) {
                watched.val = g_member_center + (half_member_len - 0.1) * glob_idx * (diff > 0 ? 1 : -1);
            }
        }
        if (['dist_to_node_s','dy'].indexOf(w3d) > -1) {
            let diff = to_float(watched.val);
            if (Math.abs(diff) > half_member_len) {
                watched.val = (diff > 0 ? 1 : -1) * (half_member_len - 0.1);
            }
        }
        return watched;
    }

    /**
     *
     * @param parent
     * @param as_index
     * @returns {THREE.Vector3|number}
     */
    static glob_top_vector(parent, as_index)
    {
        //return to Global Coord System (instead of Member's local) => Local to Global
        let locToGlob = new THREE.Euler( -(parent.rotation.x), -(parent.rotation.y), -(parent.rotation.z), 'ZYX' );

        //get global direction
        let glob_top = new THREE.Vector3( 0, 1, 0 );
        glob_top.applyEuler(locToGlob);

        return !as_index ? glob_top : Math.abs(glob_top.y);
    }

    /**
     *
     * @param lc
     * @param parent
     * @param field
     * @param new_value
     * @returns {boolean}
     */
    static consistent_data(lc, parent, field, new_value) {
        if (new_value === undefined) {
            new_value = lc[field];
        }

        let g_member_center = to_float(lc._ma_gctr) + to_float(parent.position.y);
        let half_member_len = to_float(parent.member_len) / 2;
        let consistent = true;

        if (field === 'elev_g') {
            let glob_idx = this.glob_top_vector(parent, true);
            consistent = glob_idx > 0
                ? Math.abs((g_member_center - to_float(new_value)) / glob_idx) <= half_member_len
                : (g_member_center - to_float(new_value)) === glob_idx;
        }
        if (field === 'dist_to_node_s' || field === 'dy') {
            consistent = Math.abs(to_float(new_value)) <= half_member_len;
        }

        return consistent;
    }

    /**
     * to find a node along the line defined by node_s and node_e and having distance 'dis' from node_s
     *
     * @param node_s
     * @param node_e
     * @param dis
     * @returns {{x: *, y: *, z: *}}
     */
    static calc_along(node_s, node_e, dis) {
        let delta_0 = node_e['x'] - node_s['x'];
        let delta_1 = node_e['y'] - node_s['y'];
        let delta_2 = node_e['z'] - node_s['z'];

        let length = Math.sqrt(Math.pow(delta_0, 2) + Math.pow(delta_1, 2) + Math.pow(delta_2, 2));
        let x = node_s['x'] + dis * delta_0 / length;
        let y = node_s['y'] + dis * delta_1 / length;
        let z = node_s['z'] + dis * delta_2 / length;

        return {x: x, y: y, z: z};
    }

    /**
     * to find the 'node_p' along and within the line section 'line_12' from line_node_1 and line_node_2 so that the distance from 'node' to 'node_p'
     * has the smallest distance from 'node' to line section 'line_12' and the line defined by 'node' and node_p perpendicular to the 'line_12'
     * if the node_p is beyond line_node_1 or line_node_2, then set node_p to be line_node_1 or line_node_2, depending on which isde node_p falls at.
     *
     * @param line_node_1
     * @param line_node_2
     * @param node
     */
    static calc_perp(line_node_1, line_node_2, node) {
        let cos_angel_n12 = this.find_cos_ABC(node, line_node_1, line_node_2);
        let cos_angel_n21 = this.find_cos_ABC(node, line_node_2, line_node_1);

        let node_RL_mbr = null;
        if (cos_angel_n12 > 0 && cos_angel_n21 > 0) {
            // angle from 'node' to line_node_1 to line_node_2
            // and angle from 'node' to line_node_1 to line_node_2
            // both are acute. The perpendicular node on the line section falls in between the line section.
            let delta_0_1n = line_node_1['x'] - node['x'];
            let delta_1_1n = line_node_1['y'] - node['y'];
            let delta_2_1n = line_node_1['z'] - node['z'];
            let length_1n = Math.sqrt(Math.pow(delta_0_1n, 2) + Math.pow(delta_1_1n, 2) + Math.pow(delta_2_1n, 2));

            let len_proj = length_1n * cos_angel_n12;

            node_RL_mbr = this.calc_along(line_node_1, line_node_2, len_proj);
        } else {
            if (cos_angel_n12 > 0) {
                // the perpendicular node falls beyond the line section, line_node_2
                node_RL_mbr = line_node_2;
            } else {
                node_RL_mbr = line_node_1;
                // the perpendicular node falls beyond the line section, line_node_1
            }
        }
        return node_RL_mbr;
    }

    /**
     * Function to find the angle # between the two lines AB (from node_A to node_B) and BC (from node_B to node_C)
     *
     * @param node_A
     * @param node_B
     * @param node_C
     * @returns {number}
     */
    static find_cos_ABC(node_A, node_B, node_C) {
        let x1 = node_A['x'], y1 = node_A['y'], z1 = node_A['z'];
        let x2 = node_B['x'], y2 = node_B['y'], z2 = node_B['z'];
        let x3 = node_C['x'], y3 = node_C['y'], z3 = node_C['z'];

        // Find direction ratio of line AB
        let ABx = x1 - x2;
        let ABy = y1 - y2;
        let ABz = z1 - z2;

        // Find direction ratio of line BC
        let BCx = x3 - x2;
        let BCy = y3 - y2;
        let BCz = z3 - z2;

        // Find the dotProduct of lines AB & BC
        let dotProduct = (ABx * BCx + ABy * BCy + ABz * BCz);

        // Find magnitude of line AB and BC
        let magnitudeAB = (ABx * ABx + ABy * ABy + ABz * ABz);
        let magnitudeBC = (BCx * BCx + BCy * BCy + BCz * BCz);

        // Find the cosine of the angle formed by line AB and BC
        let cos_angle = dotProduct;
        cos_angle /= Math.sqrt(magnitudeAB * magnitudeBC);

        return cos_angle;
        // return the cosine value of the angle.
    }

    //Sync tables data with 3d model
    /**
     *
     * @param stim_app_fields
     * @param changed_rows
     * @returns {null|Object}
     */
    static watched3d_is_needed_action(stim_app_fields, changed_rows) {
        let first_row = _.first(changed_rows);
        let cha_field = first_row._changed_field;
        let watched_for = ['origin_elev','elev_g','dist_to_node_s','dy'];

        let is3dLC = _.find(stim_app_fields, (fld) => {
            return String(fld.link_field_type).indexOf('3d:ma') > -1
                || String(fld.link_field_type).indexOf('3d:lcs') > -1;
        });
        if (is3dLC) {
            let corrFld = _.find(stim_app_fields, (fld) => {
                return cha_field == fld.data_field
                    && watched_for.indexOf(fld.app_field) > -1;
            });
            if (corrFld) {
                window.changed_lc_ids_to_values = _.map(changed_rows, (r) => {
                    return {
                        id: corrFld.app_field == 'origin_elev' ? 'origin_elev' : r.id,
                        val: r[corrFld.data_field],
                        old_val: corrFld.app_field == 'origin_elev' ? first_row._old_val : null,
                    };
                });
                window.changed_lc_field = corrFld.app_field;
                return corrFld;
            }
        }

        return null;
    }

    /**
     *
     * @param lc
     * @returns {*}
     */
    static watcher3d_check(lc) {
        return _.find(window.changed_lc_ids_to_values, (el) => {
            return el.id == lc._id || el.id == 'origin_elev';
        });
    }

    /**
     *
     * @param lc
     */
    static watcher3d_processed(lc) {
        window.changed_lc_ids_to_values = _.filter(window.changed_lc_ids_to_values, (el) => {
            return el.id != lc._id;
        });
    }

    /**
     *
     */
    static watcher3d_finalized() {
        window.changed_lc_field = '';
        window.changed_lc_ids_to_values = [];
    }

    /**
     *
     * @returns {string}
     */
    static watcher3d_field() {
        return window.changed_lc_field || '';
    }
    //^^^^^^^
}