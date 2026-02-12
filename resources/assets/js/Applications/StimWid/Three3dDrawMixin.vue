<script>

    import {Eqpt} from "./Configurator/Eqpt";
    import {Tech} from "./Configurator/Tech";
    import {Status} from "./Configurator/Status";
    import {Secpos} from "./Configurator/Secpos";
    import {Elev} from "./Configurator/Elev";
    import {Azimuth} from "./Configurator/Azimuth";
    import {ThreeHelper} from "../../classes/helpers/ThreeHelper";

    export default {
        data() {
            return {
                present_rh: [],

                eqpt_lib: [],
                tech_list: [],
                status_list: [],
                secpos_list: [],
                elevs_list: [],
                azimuth_list: [],
                popup_tables: {},
                loa_tablda: {},
                ma_tablda: {},
                tabldas: {},
                tablda_hashes: {},
                pos_to_mbrs: [],
                $pos_to_mbr_tb: {},
                $ma_3d: null,

                drawed_geometry: null,
                drawed_equipments: null,
                drawed_rls: null,

                sectionsSizes: [],
                sectionsInfo: [
                    //NEEDED PROPERTIES
                    /*{
                        AISC_Size2: "Pipe2-1/2STD",
                        Ht: "-",
                        B_upr: "",
                        OD: "2.88",
                        d: "-",
                        b: "-",
                        b_f: "-",
                        h: "-",
                        t: "-",
                        t_f: "-",
                        t_w: "-",
                        t_des: "0.189",
                        t_nom: "0.203",
                        k_1: "-",
                        k_des: "-",
                        k_det: "-",
                        y: "-",
                        x: "-",
                    }*/
                ],
                eqpt_obj: {
                    //single object
                    lc: {
                        id: 69,
                        mbr_name: 'MSART',
                        status: 'Proposed',
                        dx: 0,
                        dy: 0,
                        dz: 0,
                        rotx: 0,
                        roty: 0,
                        rotz: 0,
                    },
                    eq: { // like to Equpment/Data
                        id: 92931,
                        d1: "96.00",
                        d2: "16.20",
                        d3: "7.80",
                        d4: "10.00",
                        d5: "0.00",
                        texture_type: "",
                        texture: "",
                        color: "",
                        geometryType: "Single Object",
                        geometryShapeType: "Flat Panel",
                        model: 'CMA-BTLBHH/6518/20/20',
                    },
                    //structure
                    details: {
                        nodes: [
                            {
                                no: null,
                                node_name: null,
                                x: null,
                                y: null,
                                z: null,
                            },//...
                        ],
                        objects: [
                            {
                                name: null,
                                length: null,
                                shape: null,
                                size1: null,
                                size2: null,
                                nodes: {
                                    NodeS: '',
                                    NodeE: '',
                                },
                                other: {
                                    x1: 0,
                                    x2: 0,
                                    y1: 0,
                                    y2: 0,
                                    z1: 0,
                                    z2: 0,
                                    node: {
                                        s: [ 0 , 0 , 0 ],// x,y,z - defaults
                                        e: [ 0 , 1 , 0 ],// x,y,z - defaults
                                    },
                                },
                            },//...
                        ],
                    }
                },
                cached: {
                    node: [],
                    member: [],
                    eqpt: [],
                },
                ma_eqpt_helper_row: null,
                ma_eqpt_old_row: null,
            }
        },
        methods: {
            showMaEqptHelper(lc_eqpt) {
                this.ma_eqpt_helper_row = {
                    id: lc_eqpt.lc_id,
                    ...lc_eqpt._lc_extra,
                    _top_delta: 1,
                    _bottom_delta: 5,
                    _top_delta_unit: 'in',
                    _bottom_delta_unit: 'deg',
                };
                this.ma_eqpt_old_row = _.clone(this.ma_eqpt_helper_row);
            },
            hideMaEqptHelper(lc_eqpt) {
                this.ma_eqpt_helper_row = null;
            },
            setCached(array, cacher) {
                this.cached[cacher] = _.map(array || [], '_row_hash');//
            },

            tdm_draw(geometry, shrink, equipments, dc, sectionsInfo, rls, extra) {
                _.each(geometry.materials, (mat) => {
                    mat.color_gr = mat.color_gr || 'null';
                });

                let objects = [];
                let members = this.fix_row_hash(geometry.members);
                let nodes = this.fix_row_hash(geometry.nodes);
                let sections = this.fix_row_hash(geometry.sections);
                let materials = this.fix_row_hash(geometry.materials);

                //change link in Eqpt: pos2mbr => member
                _.each(equipments, (elem) => {
                    elem.lc.status = elem.lc.status || 'null';
                    elem._row_hash = elem.eq._row_hash + elem.lc._row_hash;
                    elem.eq.color = this.getMaColor(elem.lc.status);
                    let p2m = _.find(this.pos_to_mbrs, (pos2) => {
                        return pos2._id == elem.lc.mbr_id || pos2.member == elem.lc.mbr_id;
                    });
                    elem.lc.mbr_id = p2m ? p2m._id : elem.lc.mbr_id;
                    elem.lc.mbr_name = p2m ? p2m.member : '';

                    if (this.ma_eqpt_helper_row && this.ma_eqpt_helper_row._id === elem.lc._id) {
                        this.$root.assignObject(elem.lc, this.ma_eqpt_helper_row);
                    }
                });
                // console.log('equipments',equipments);

                //set color to RLs
                _.each(rls ? rls.rows : [], (rl) => {
                    rl.lc_color = this.getMaColor(rl.lc_status);
                });
                // console.log('rls',rls);

                if (members) {
                    _.each(members, (data) => {
                        let x = 0;
                        let y = 0;
                        let z = 0;

                        let shape = false;
                        let size1 = false;
                        let size2 = false;
                        let sec_hash = '';
                        let sec_id = '';
                        let mat_id = '';

                        let NodeS = null;
                        let NodeE = null;

                        let Nodes = {
                            s: null,
                            e: null
                        };

                        _.each(nodes, (nod) => {//
                            if (data.NodeS == nod.node_name) {
                                NodeS = nod.no;
                                Nodes.s = [to_float(nod.x), to_float(nod.y), to_float(nod.z)];
                            }

                            if (data.NodeE == nod.node_name) {
                                NodeE = nod.no;
                                Nodes.e = [to_float(nod.x), to_float(nod.y), to_float(nod.z)];
                            }
                        });

                        if (Nodes.s && Nodes.e) {
                            data.Mbr_Lth = Math.pow(Math.pow(Nodes.e[0] - Nodes.s[0], 2) +
                                Math.pow(Nodes.e[1] - Nodes.s[1], 2) +
                                Math.pow(Nodes.e[2] - Nodes.s[2], 2),
                                1 / 2);

                            if (data.Mbr_Lth) {
                                data.Mbr_Lth = data.Mbr_Lth.toFixed(4);
                            }
                        } else {
                            data.Mbr_Lth = data.Mbr_Lth || 5;
                        }

                        _.each(sections, (secs) => {
                            if (data.sec_name === secs.sec_name) {
                                shape = secs.shape;
                                size1 = secs.size1;
                                size2 = secs.size2;
                                sec_hash = secs._row_hash;
                                sec_id = secs._id;
                            }
                        });

                        _.each(materials, (mat) => {
                            if (data.Mat === mat.name) {
                                mat_id = mat._id;
                            }
                        });

                        let attached_eqpts = [];
                        _.each(equipments, (obj) => {
                            if(data['_id'] == obj.lc.mbr_id) {
                                attached_eqpts.push(obj.lc._id);
                            }
                        });

                        let attached_postombrs = [];
                        _.each(this.pos_to_mbrs, (obj) => {
                            if(data['Mbr_Name'] == obj.member) {
                                attached_postombrs.push(obj._id);
                            }
                        });

                        objects.push({
                            _row_hash: data['no']+data['_row_hash']+data.Mbr_Lth+sec_hash,
                            id: data['no'],
                            name: data['Mbr_Name'] || "",
                            Mbr_Name: data['Mbr_Name'] || "",
                            shape: shape || false,
                            length: data['Mbr_Lth'] || 5,
                            color: this.getGeomColor(data['Mat'], materials),
                            size1: size1 || false,
                            size2: size2 || false,
                            other: {
                                x1: x || 0,
                                y1: y || 0,
                                z1: z || 0,
                                x2: 0,
                                y2: data['ROT'] || 0,
                                z2: 0,
                                node: Nodes
                            },
                            nodes: {
                                NodeS: NodeS || null,
                                NodeE: NodeE || null
                            },
                            sec_id: sec_id,
                            mat_id: mat_id,
                            node_start_id: NodeS,
                            node_end_id: NodeE,
                            attached_eqpt_ids: attached_eqpts,
                            attached_postombrs: attached_postombrs,
                        });
                    });//v1////
                }

                webgl.draw(this.cached, nodes, "draw", "nodes");//

                //limit for Equpments is 50
                /*if (equipments.length > 50) {
                    equipments = equipments.slice(0, 50);
                }*/

                webgl.draw(this.cached, objects, "draw", "wid_objects", {}, shrink, equipments, dc, sectionsInfo, rls, extra);
            },
            fix_row_hash(array) {
                if (array) {
                    this.present_rh = [];
                    _.each(array, (nod) => {
                        //remove empty row hashes on 3D Model
                        if (!nod._row_hash) {
                            nod._row_hash = uuidv4();
                        }
                        //remove duplicates in row hashes on 3D Model
                        if (this.present_rh.indexOf(nod._row_hash) > -1) {
                            nod._row_hash = uuidv4();
                        } else {
                            this.present_rh.push(nod._row_hash);
                        }
                    });
                }
                return array;
            },
            getMaColor(status) {
                let $clr = _.find(this.MaClrStatuses.colors, {key: String(status)});
                return this.MaClrStatuses.view_enabled && $clr
                    ? $clr.model_val
                    : (this.viewSettings.defEqptColor || '#cccccc');//
            },
            getGeomColor(mat, materials) {
                let found = _.find(materials, {name: mat});
                let key = found ? found.color_gr : '';

                let $clr = _.find(this.GeomColors.colors, {key: String(key)});

                return this.GeomColors.view_enabled && $clr
                    ? $clr.model_val
                    : null;
            },

            Three3dRedraw(geometry, drawMode, eqs, data_eqs, rls, extra) {
                try {
                    let equipments = eqs ? eqs.collection : [];
                    this.drawed_geometry = geometry;
                    this.drawed_equipments = eqs;
                    this.drawed_rls = rls;

                    if (data_eqs) {
                        this.eqpt_lib = _.map(data_eqs.eqpt_lib, (eq) => { return new Eqpt(eq); });
                        this.status_list = _.map(data_eqs.status_lib, (eq) => { return new Status(eq); });
                        this.secpos_list = _.map(data_eqs.secpos_lib, (eq) => { return new Secpos(eq); });
                        this.elevs_list = _.map(data_eqs.elevs_lib, (eq) => { return new Elev(eq); });
                        this.azimuth_list = _.map(data_eqs.azimuth_lib, (eq) => { return new Azimuth(eq); });
                        this.tech_list = _.map(data_eqs.tech_lib, (eq) => { return new Tech(eq); });
                        this.popup_tables = data_eqs.popup_tables;
                        this.loa_tablda = data_eqs.loa_tablda;
                        this.ma_tablda = data_eqs.ma_tablda;
                        this.pos_to_mbrs = data_eqs.pos_to_mbrs;
                        this.$pos_to_mbr_tb = data_eqs.$pos_to_mbr_tb;
                        this.$ma_3d = data_eqs.ma_3d;
                    }

                    let tabldas = {};
                    tabldas.lcs = eqs ? eqs._lcs_tb : null;
                    tabldas.eqs = eqs ? eqs._eqs_tb : null;
                    tabldas.mat = geometry ? geometry._materials_tb : null;
                    tabldas.node = geometry ? geometry._nodes_tb : null;
                    tabldas.sect = geometry ? geometry._sections_tb : null;
                    tabldas.memb = geometry ? geometry._members_tb : null;
                    tabldas.rls = rls ? rls._rl_tb : null;
                    this.tabldas = tabldas;

                    //cache system
                    this.setCached(geometry ? geometry.nodes : [], 'node');//
                    this.setCached(geometry ? geometry.members : [], 'member');//
                    this.setCached(equipments || [], 'eqpt');//

                    // console.log('Three3dRedraw', geometry, equipments);

                    if (geometry && geometry.sections && geometry.sections.length) {
                        let sec_sizes = _.map(geometry.sections, sec => sec.size2);
                        let difference = this.sectionsSizes
                            .filter(x => !sec_sizes.includes(x))
                            .concat(sec_sizes.filter(x => !this.sectionsSizes.includes(x)));

                        if (difference.length) {
                            this.sectionsSizes = sec_sizes;
                            axios.post('?method=load_aisc', {
                                sizes_array: this.sectionsSizes
                            }).then(({data}) => {
                                this.sectionsInfo = data.aisc || [];
                                this.Three3dRedraw_Inner(geometry, drawMode, equipments, rls, extra);
                            })
                        } else {
                            this.Three3dRedraw_Inner(geometry, drawMode, equipments, rls, extra);
                        }
                    } else {
                        this.Three3dRedraw_Inner(geometry, drawMode, equipments, rls, extra);
                    }
                } catch ($e) {
                    console.log($e);
                }
                this.sync_in_process = false;
            },
            Three3dRedraw_Inner(geometry, drawMode, equipments, rls, extra) {//
                let drawed = false;
                let t1 = + new Date();

                if (geometry && drawMode === 'equipment')
                {
                    let found_model = geometry.found_model || {};
                    if (found_model.geometryType === 'Single Object') {
                        if (RegExp(/flat.*panel/gi).test( found_model.geometryShapeType ) && found_model.d1 && found_model.d2 && found_model.d3) {

                            drawed = true;
                            webgl.draw(this.cached, {
                                dimensions: [found_model.d1, found_model.d2, found_model.d3, found_model.d4],
                                texture: {
                                    type: found_model.texture_type,
                                    color: found_model.color,
                                    name: found_model.texture
                                },
                                product: {
                                    id: found_model._id
                                }
                            }, 'draw', 'flat_panel');
                            /*$scope.models.product.plotResize(found_model.d2, found_model.d3);
                             $scope.models.product.formEpaChart({d1: 0, d2: 0, d3: 0, geometryShapeType: "Cuboid"});*/

                        } else if (RegExp(/cuboid/gi).test( found_model.geometryShapeType ) && found_model.d1 && found_model.d2 && found_model.d3) {

                            drawed = true;
                            webgl.draw(this.cached, {
                                dimensions: [found_model.d1, found_model.d2, found_model.d3],
                                texture: {
                                    type: found_model.texture_type,
                                    color: found_model.color,
                                    name: found_model.texture
                                },
                                product: {
                                    id: found_model._id
                                }
                            }, 'draw', 'cuboid');
                            /*$scope.models.product.plotResize(found_model.d2, found_model.d3);
                             $scope.models.product.formEpaChart({d1: 0, d2: 0, d3: 0, geometryShapeType: "Cuboid"});*/

                        } else if (RegExp(/cylinder/gi).test( found_model.geometryShapeType ) && found_model.d1 && found_model.d2) {

                            drawed = true;
                            webgl.draw(this.cached, {
                                dimensions: [found_model.d1, found_model.d2],
                                texture: {
                                    type: found_model.texture_type,
                                    color: found_model.color,
                                    name: found_model.texture
                                },
                                product: {
                                    id: found_model._id
                                }
                            }, "draw", "cylinder");
                            /*$scope.models.product.plotResize(found_model.d1, found_model.d2);
                             $scope.models.product.formEpaChart({d1: 0, d2: 0, d3: 0, geometryShapeType: "Cuboid"});*/

                        } else if (RegExp(/sphere/gi).test( found_model.geometryShapeType ) && found_model.d1) {

                            drawed = true;
                            webgl.draw(this.cached, {
                                dimensions: [found_model.d1],
                                texture: {
                                    type: found_model.texture_type,
                                    color: found_model.color,
                                    name: found_model.texture
                                },
                                product: {
                                    id: found_model._id
                                }
                            }, "draw", "sphere");
                            /*$scope.models.product.plotResize(found_model.d1, found_model.d1);
                             $scope.models.product.formEpaChart({d1: 0, d2: 0, d3: 0, geometryShapeType: "Cuboid"});*/

                        } else if (RegExp(/conical.*dish.*shroud/gi).test( found_model.geometryShapeType ) && found_model.d1 && found_model.d2 && found_model.d3) {

                            drawed = true;
                            webgl.draw(this.cached, {
                                dimensions: [found_model.d1, found_model.d2, found_model.d3, found_model.d4],
                                texture: {
                                    type: found_model.texture_type,
                                    color: found_model.color,
                                    name: found_model.texture
                                },
                                product: {
                                    id: found_model._id
                                }
                            }, "draw", "conical_dish_w_shroud");
                            /*$scope.models.product.plotResize(found_model.d1, found_model.d1);
                             $scope.models.product.formEpaChart({d1: 0, d2: 0, d3: 0, geometryShapeType: "Cuboid"});*/


                        } else if (RegExp(/cylinder.*dish.*shroud/gi).test( found_model.geometryShapeType ) && found_model.d1 && found_model.d2 && found_model.d3) {

                            drawed = true;
                            webgl.draw(this.cached, {
                                dimensions: [found_model.d1, found_model.d2, found_model.d3, found_model.d4],
                                texture: {
                                    type: found_model.texture_type,
                                    color: found_model.color,
                                    name: found_model.texture
                                },
                                product: {
                                    id: found_model._id
                                }

                            }, "draw", "cylindrical_dish_w_shroud");

                        } else if (RegExp(/dish.*radom/gi).test( found_model.geometryShapeType ) && found_model.d1 && found_model.d2 && found_model.d3) {

                            drawed = true;
                            webgl.draw(this.cached, {
                                dimensions: [found_model.d1, found_model.d2, found_model.d3, found_model.d4],
                                texture: {
                                    type: found_model.texture_type,
                                    color: found_model.color,
                                    name: found_model.texture
                                },
                                product: {
                                    id: found_model._id
                                }
                            }, "draw", "dish_w_radome");

                        } else if (RegExp(/parabolic.*grid.*dish/gi).test( found_model.geometryShapeType ) && found_model.d1 && found_model.d2 && found_model.d3) {

                            drawed = true;
                            webgl.draw(this.cached, {
                                dimensions: [found_model.d1, found_model.d2, found_model.d3, found_model.d4],
                                texture: {
                                    type: found_model.texture_type,
                                    color: found_model.color,
                                    name: found_model.texture
                                },
                                product: {
                                    id: found_model._id
                                }
                            }, "draw", "parabolic_grid_dish");

                        }

                    } else if (found_model.geometryType === 'Structure') {

                        drawed = true;
                        /*geometry.getSectionsInfo(function (response) {
                         if (response.data) {
                         $scope.sectionsInfo = response.data;
                         $scope.sectionsInfo.forEach(function (item) {
                         $scope.unitConversionProcess(item);
                         });
                         }
                         });*/

                        //draw($scope.models.product.associationMembers, $scope.models.product.associationNodes, $scope.models.product.associationSections, this.viewSettings.shrink, '', '', '', $scope.sectionsInfo);

                    } else if (found_model.geometryType === '3D file(s)') {

                        drawed = true;
                        webgl.draw(this.cached, {}, 'delete');//

                        /*if ($scope.models.product.files_list) {
                         $scope.models.product.files_list.forEach(function (item) {
                         if (item.show === 'true') {
                         if (item.file.indexOf('.obj') !== -1) {
                         webgl.draw(this.cached, {
                         file: item.file,
                         id: found_model._id
                         }, "draw", "custom_object");
                         }
                         }
                         });
                         }*/
                    }
                }
                else if (geometry && drawMode === 'geometry')
                {
                    drawed = true;
                    this.tdm_draw(geometry, this.viewSettings.shrink, equipments, this.TR, this.sectionsInfo, rls, extra);
                }
                ThreeHelper.watcher3d_finalized();

                if (!drawed) {
                    webgl.clearAll();//
                }
                console.log('time', + new Date() - t1);
            },
        },
        mounted() {
        }
    }
</script>