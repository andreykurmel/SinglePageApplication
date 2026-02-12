<template>
    <div class="full-frame">
        <div class="webgl-wrapper" :style="{zIndex: popup_row_id ? 1405 : 'initial'}">
            <!--NOTE: ALL IS COPIED FROM 'WID' PROJECT AND CHANGED FOR "VUE"-->
            <div class="flex flex--center" style="position: absolute; z-index: 200;">
                <div class="logo">
                    <img src="/assets/img/icons/3DView.png" @click="set_Default_Angle_WID()" height="28" width="28">
                </div>

                <div class="edges-setting-button"
                     @mouseenter="edges_menu=true"
                     @mouseleave="edges_menu=false"
                     @click="vuex_tab_object.type_3d !== '3d:ma' ? vSettEL() : null"
                >
                    <span>EL</span>
                    <div v-if="vuex_tab_object.type_3d === '3d:ma' && edges_menu" class="edges--menu">
                        <div class="flex flex--center-v">
                            <label class="switch_t">
                                <input type="checkbox" v-model="viewSettings.edges_eqpts" @change="vSettSave()">
                                <span class="toggler round"></span>
                            </label>
                            <label>&nbsp;Equpments</label>
                        </div>
                        <div class="flex flex--center-v">
                            <label class="switch_t">
                                <input type="checkbox" v-model="viewSettings.edges_members" @change="vSettSave()">
                                <span class="toggler round"></span>
                            </label>
                            <label>&nbsp;Members</label>
                        </div>
                        <div class="flex flex--center-v">
                            <div style="height: 24px;width: 48px;position: relative;">
                                <tablda-colopicker
                                        :init_color="viewSettings.edges_color"
                                        :avail_null="true"
                                        @set-color="setEdgeclr"
                                ></tablda-colopicker>
                            </div>
                            <label>&nbsp;Color</label>
                        </div>
                    </div>
                </div>

                <div class="wid_camera">
                    <img src="/assets/img/icons/camera.png" height="28" @click="make_Screenshot()">
                </div>

                <div class="wid_filter" v-show="vuex_tab_object.type_3d === '3d:structure'">
                    <img src="/assets/img/icons/filter.png" @click="GeomColors.filters_show = !GeomColors.filters_show" height="28" width="28">
                </div>
                <div class="wid_status_color" v-show="vuex_tab_object.type_3d === '3d:structure'">
                    <img src="/assets/img/icons/status_color.png"
                         :style="{filter: GeomColors.view_enabled ? null : 'grayscale(1)'}"
                         @click="GeomColors.view_enabled = !GeomColors.view_enabled; drawWholeGeom();"
                         height="28" width="28">
                </div>

                <div class="wid_filter" v-show="vuex_tab_object.type_3d === '3d:ma'">
                    <img src="/assets/img/icons/filter.png"
                         :style="{filter: MaClrStatuses.view_enabled ? null : 'grayscale(1)'}"
                         @click="MaClrStatuses.filters_show = !MaClrStatuses.filters_show"
                         height="28" width="28">
                </div>
                <div class="wid_status_color" v-show="vuex_tab_object.type_3d === '3d:ma'">
                    <img src="/assets/img/icons/status_color.png" @click="MaClrStatuses.view_enabled = !MaClrStatuses.view_enabled; drawWholeGeom();" height="28" width="28">
                </div>

                <div class="cam_view flex flex--center" v-show="vuex_tab_object.type_3d !== '3d:equipment'" @mouseenter="cam_menu=true" @mouseleave="cam_menu=false">
                    <img src="/assets/img/icons/ortho_camera_1.png" v-if="cur_cam_orto" @click="cameraChange(false)" class="cam_img"/>
                    <img src="/assets/img/icons/persp_camera_1.png" v-else="" @click="cameraChange(true)" class="cam_img"/>
                    <!--<i class="fas fa-cubes" @click="cameraChange(false)" :style="{opacity: cur_cam_orto ? 1 : 0.5}"></i>-->
                    <!--<i class="fab fa-delicious" @click="cameraChange(true)"></i>-->
                    <div v-if="cur_cam_orto && cam_menu" class="cam_view--menu">
                        <button class="btn btn-sm" @click="cameraChange('front')">Front</button>
                        <button class="btn btn-sm" @click="cameraChange('back')">Back</button>
                        <button class="btn btn-sm" @click="cameraChange('left')">Left</button>
                        <button class="btn btn-sm" @click="cameraChange('right')">Right</button>
                        <button class="btn btn-sm" @click="cameraChange('top')">Top</button>
                        <button class="btn btn-sm" @click="cameraChange('bot')">Bottom</button>
                    </div>
                </div>
            </div>

            <div class="settings-btn" ref="wid_butt" @click="base.toggleSettingsMenu = !base.toggleSettingsMenu">
                <span class="glyphicon glyphicon-cog" aria-hidden="true"></span>
            </div>


            <!--Lib Menu-->
            <canv-lib-menu
                    v-show="vuex_tab_object.type_3d === '3d:ma'"
                    :settings="confSett"
                    :eqpt_lib="eqpt_lib"
                    :tech_list="tech_list"
                    :status_list="status_list"
                    :secpos_list="secpos_list"
                    :elevs_list="elevs_list"
                    :azimuth_list="azimuth_list"
                    :px_in_ft="pxInFt"
                    :ex_height="'calc(100% - 140px)'"
                    :def_collapse="true"
                    class="float-elem model3d__lib"
                    @collapse-toggle="libToggle"
                    @popup-elem="popupLibElem"
                    @open-add-popup="openAddPopup"
            ></canv-lib-menu>

            <!--Add New Libs-->
            <custom-edit-pop-up
                    v-if="stim_link_add && empty_object_add && found_model_add && found_model_add.meta.is_loaded"
                    :global-meta="found_model_add.meta.params"
                    :table-meta="found_model_add.meta.params"
                    :table-row="empty_object_add"
                    :settings-meta="$root.settingsMeta"
                    :role="'add'"
                    :input_component_name="'custom-cell-table-data'"
                    :behavior="'list_view'"
                    :user="$root.user"
                    :cell-height="$root.cellHeight"
                    :max-cell-rows="$root.maxCellRows"
                    :available-columns="stim_link_add.avail_cols_for_app"
                    @popup-insert="addPopupInsert"
                    @popup-close="closeAddPopUp"
            ></custom-edit-pop-up>

            <!--Eqpt select menu-->
            <div v-if="eqptPop" ref="eqpt_menu" class="float-elem" :style="settStyle">
                <button class="btn btn-default blue-gradient"
                        style="padding: 0 3px;"
                        :style="$root.themeButtonStyle"
                        @click="rEqptSelected('eqs')"
                >Source</button>
                <br>
                <button class="btn btn-default blue-gradient"
                        style="padding: 0 3px;"
                        :style="$root.themeButtonStyle"
                        @click="rEqptSelected('lcs')"
                >Equipment</button>
            </div>
            <!--eqpt sett menu-->

            <!--Eqpt select menu-->
            <div v-if="ma_eqpt_helper_row" class="float-elem" style="top:10px; right:50px;">
                <table class="ma_helper_table">
                    <tr class="tablda-like-bg" style="height: 30px">
                        <th>Delta</th>
                        <th colspan="2">dx</th>
                        <th colspan="2">dy</th>
                        <th colspan="2">dz</th>
                        <th colspan="2">Dist to Mid</th>
                    </tr>
                    <tr class="tablda-like-bg">
                        <th class="no-padding">
                            <select class="form-control"
                                    v-model="ma_eqpt_helper_row._top_delta_unit"
                                    @change="eqptHelperUnitRecalc('_top_delta_unit')"
                            >
                                <option value="in">in.</option>
                                <option value="ft">ft.</option>
                            </select>
                        </th>
                        <th colspan="2">ft</th>
                        <th colspan="2">ft</th>
                        <th colspan="2">ft</th>
                        <th colspan="2">ft</th>
                    </tr>
                    <tr>
                        <td class="no-padding">
                            <input class="form-control" v-model="ma_eqpt_helper_row._top_delta"/>
                        </td>
                        <td class="no-padding" colspan="2">
                            <input class="form-control" v-model="ma_eqpt_helper_row.dx" @change="updMaEqptRow('dx')"/>
                        </td>
                        <td class="no-padding" colspan="2">
                            <input class="form-control" v-model="ma_eqpt_helper_row.dy" @change="updMaEqptRow('dy')"/>
                        </td>
                        <td class="no-padding" colspan="2">
                            <input class="form-control" v-model="ma_eqpt_helper_row.dz" @change="updMaEqptRow('dz')"/>
                        </td>
                        <td class="no-padding" colspan="2">
                            <input class="form-control" v-model="ma_eqpt_helper_row.dist_to_node_s" @change="updMaEqptRow('dist_to_node_s')"/>
                        </td>
                    </tr>
                    <tr>
                        <th class="tablda-like-bg"></th>
                        <td class="tb-btn" @click="updStep('dx', '_top_delta', 'inc')">+</td>
                        <td class="tb-btn" @click="updStep('dx', '_top_delta', 'dec')">-</td>
                        <td class="tb-btn" @click="updStep('dy', '_top_delta', 'inc')">+</td>
                        <td class="tb-btn" @click="updStep('dy', '_top_delta', 'dec')">-</td>
                        <td class="tb-btn" @click="updStep('dz', '_top_delta', 'inc')">+</td>
                        <td class="tb-btn" @click="updStep('dz', '_top_delta', 'dec')">-</td>
                        <td class="tb-btn" @click="updStep('dist_to_node_s', '_top_delta', 'inc')">+</td>
                        <td class="tb-btn" @click="updStep('dist_to_node_s', '_top_delta', 'dec')">-</td>
                    </tr>
                    <tr class="tablda-like-bg" style="height: 30px">
                        <th>Delta</th>
                        <th colspan="2">rotx</th>
                        <th colspan="2">roty</th>
                        <th colspan="2">rotz</th>
                        <th colspan="2">AZM</th>
                    </tr>
                    <tr class="tablda-like-bg">
                        <th class="no-padding">
                            <select class="form-control"
                                    v-model="ma_eqpt_helper_row._bottom_delta_unit"
                                    @change="eqptHelperUnitRecalc('_bottom_delta_unit')"
                            >
                                <option value="deg">deg.</option>
                                <option value="rad">rad.</option>
                            </select>
                        </th>
                        <th colspan="2">deg.</th>
                        <th colspan="2">deg.</th>
                        <th colspan="2">deg.</th>
                        <th colspan="2">deg.</th>
                    </tr>
                    <tr>
                        <td class="no-padding">
                            <input class="form-control" v-model="ma_eqpt_helper_row._bottom_delta"/>
                        </td>
                        <td class="no-padding" colspan="2">
                            <input class="form-control" v-model="ma_eqpt_helper_row.rotx" @change="updMaEqptRow('rotx')"/>
                        </td>
                        <td class="no-padding" colspan="2">
                            <input class="form-control" v-model="ma_eqpt_helper_row.roty" @change="updMaEqptRow('roty')"/>
                        </td>
                        <td class="no-padding" colspan="2">
                            <input class="form-control" v-model="ma_eqpt_helper_row.rotz" @change="updMaEqptRow('rotz')"/>
                        </td>
                        <td class="no-padding" colspan="2">
                            <input class="form-control" v-model="ma_eqpt_helper_row.azm" @change="updMaEqptRow('azm')"/>
                        </td>
                    </tr>
                    <tr>
                        <th class="tablda-like-bg"></th>
                        <td class="tb-btn" @click="updStep('rotx', '_bottom_delta', 'inc')">+</td>
                        <td class="tb-btn" @click="updStep('rotx', '_bottom_delta', 'dec')">-</td>
                        <td class="tb-btn" @click="updStep('roty', '_bottom_delta', 'inc')">+</td>
                        <td class="tb-btn" @click="updStep('roty', '_bottom_delta', 'dec')">-</td>
                        <td class="tb-btn" @click="updStep('rotz', '_bottom_delta', 'inc')">+</td>
                        <td class="tb-btn" @click="updStep('rotz', '_bottom_delta', 'dec')">-</td>
                        <td class="tb-btn" @click="updStep('azm', '_bottom_delta', 'inc')">+</td>
                        <td class="tb-btn" @click="updStep('azm', '_bottom_delta', 'dec')">-</td>
                    </tr>
                </table>
            </div>
            <!--eqpt sett menu-->


            <div class="settings-menu" ref="wid_sett" v-show="base.toggleSettingsMenu">

                <div class="checkbox" style="display: flex;">
                    <span style="float: left; width: 76px;">Grids:</span>&nbsp;
                    <label style="width: 50px">
                        <span class="indeterm_check__wrap">
                            <span class="indeterm_check" @click="viewSettings.planeXY = !viewSettings.planeXY;can_save_sett = true">
                                <i v-if="viewSettings.planeXY" class="glyphicon glyphicon-ok group__icon"></i>
                            </span>
                        </span>
                        <span>XY&nbsp;</span>
                    </label>
                    <label style="width: 50px">
                        <span class="indeterm_check__wrap">
                            <span class="indeterm_check" @click="viewSettings.planeYZ = !viewSettings.planeYZ;can_save_sett = true">
                                <i v-if="viewSettings.planeYZ" class="glyphicon glyphicon-ok group__icon"></i>
                            </span>
                        </span>
                        <span>YZ&nbsp;</span>
                    </label>
                    <label style="width: 50px">
                        <span class="indeterm_check__wrap">
                            <span class="indeterm_check" @click="viewSettings.planeZX = !viewSettings.planeZX;can_save_sett = true">
                                <i v-if="viewSettings.planeZX" class="glyphicon glyphicon-ok group__icon"></i>
                            </span>
                        </span>
                        <span>ZX&nbsp;</span>
                    </label>
                    <label>
                        <span style="float: left;">Size:</span>
                        <select style="margin-top: -2px; float: left; width: 75px; margin-left: 5px;" class="form-control" v-model="viewSettings.gridSize" @change="can_save_sett = true">
                            <option value="1in"> 1 inch </option>
                            <option value="3in"> 3 inch </option>
                            <option value="6in"> 6 inch </option>
                            <option value="1ft"> 1 ft </option>
                        </select>
                    </label>
                </div>


                <div class="checkbox">
                    <span style="float: left; width: 80px;">Nodes:</span>
                    <label style="width: 65px;">
                        <span class="indeterm_check__wrap">
                            <span class="indeterm_check" @click="viewSettings.nodes = !viewSettings.nodes;can_save_sett = true">
                                <i v-if="viewSettings.nodes" class="glyphicon glyphicon-ok group__icon"></i>
                            </span>
                        </span>
                        <span>View&nbsp;</span>
                    </label>
                    <label style="width: 82px;">
                        <span class="indeterm_check__wrap">
                            <span class="indeterm_check" @click="viewSettings.nodesName = !viewSettings.nodesName;can_save_sett = true">
                                <i v-if="viewSettings.nodesName" class="glyphicon glyphicon-ok group__icon"></i>
                            </span>
                        </span>
                        <span>Name&nbsp;</span>
                    </label>

                    <label style="padding: 0;">
                        <input style="width: 40px; margin-right: 5px; float: left;" class="form-control" type="number" v-model="viewSettings.fontSize" @change="can_save_sett = true">
                        <span>px</span> <span>Font size</span>
                    </label>
                </div>

                <div class="checkbox">
                    <span style="float: left; width: 80px;">Wireframe:</span>
                    <label style="width: 65px;">
                        <span class="indeterm_check__wrap">
                            <span class="indeterm_check" @click="viewSettings.wireframe = !viewSettings.wireframe;can_save_sett = true">
                                <i v-if="viewSettings.wireframe" class="glyphicon glyphicon-ok group__icon"></i>
                            </span>
                        </span>
                        <span>View&nbsp;</span>
                    </label>
                    <label style="width: 90px;">
                        <span class="indeterm_check__wrap">
                            <span class="indeterm_check" @click="viewSettings.wireframeName = !viewSettings.wireframeName;can_save_sett = true">
                                <i v-if="viewSettings.wireframeName" class="glyphicon glyphicon-ok group__icon"></i>
                            </span>
                        </span>
                        <span>Name&nbsp;</span>
                    </label>
                    <label style="padding-left: 1px;">
                        <span>Shrink</span>
                    </label>
                    <label style="padding-left: 24px;">
                        <span>Def. Color</span>
                    </label>
                </div>

                <div class="checkbox flex flex--center-v">
                    <span style="float: left; width: 80px;">Members:</span>
                    <label style="width: 65px;">
                        <span class="indeterm_check__wrap">
                            <span class="indeterm_check" @click="viewSettings.members = !viewSettings.members;can_save_sett = true">
                                <i v-if="viewSettings.members" class="glyphicon glyphicon-ok group__icon"></i>
                            </span>
                        </span>
                        <span>View&nbsp;</span>
                    </label>
                    <label style="width: 90px;">
                        <span class="indeterm_check__wrap">
                            <span class="indeterm_check" @click="viewSettings.membersName = !viewSettings.membersName;can_save_sett = true">
                                <i v-if="viewSettings.membersName" class="glyphicon glyphicon-ok group__icon"></i>
                            </span>
                        </span>
                        <span>Name&nbsp;</span>
                    </label>
                    <label style="padding: 0;">
                        <input style="width: 40px; margin-right: 5px; float: left;" class="form-control" type="number" @change="can_save_sett = true" min="0" max="50" v-model="viewSettings.gridShrink">
                        <span>%</span>
                    </label>
                    <label class="colorPicker" style="margin-left: 10px;">
                        <tablda-colopicker
                                :init_color="viewSettings.defMemberColor"
                                :avail_null="viewSettings.defMemberColor != '#aaaaaa'"
                                @set-color="(clr) => { setclr('defMemberColor', clr) }"
                        ></tablda-colopicker>
                    </label>
                </div>

                <div class="checkbox flex flex--center-v">
                    <span style="float: left; width: 80px;">Objects:</span>
                    <label style="width: 65px;">
                        <span class="indeterm_check__wrap">
                            <span class="indeterm_check" @click="viewSettings.objects = !viewSettings.objects;can_save_sett = true">
                                <i v-if="viewSettings.objects" class="glyphicon glyphicon-ok group__icon"></i>
                            </span>
                        </span>
                        <span>View&nbsp;</span>
                    </label>
                    <label style="width: 90px;">
                        <span class="indeterm_check__wrap">
                            <span class="indeterm_check" @click="viewSettings.showLabelsEqpt = !viewSettings.showLabelsEqpt;can_save_sett = true">
                                <i v-if="viewSettings.showLabelsEqpt" class="glyphicon glyphicon-ok group__icon"></i>
                            </span>
                        </span>
                        <span>Name&nbsp;</span>
                    </label>
                    <label class="colorPicker" style="margin-left: 67px;">
                        <tablda-colopicker
                                :init_color="viewSettings.defEqptColor"
                                :avail_null="viewSettings.defEqptColor != '#aaaaaa'"
                                @set-color="(clr) => { setclr('defEqptColor', clr) }"
                        ></tablda-colopicker>
                    </label>
                </div>

                <div class="checkbox flex flex--center-v">
                    <span style="float: left; width: 80px;">Bracket RL:</span>
                    <label style="width: 65px;">
                        <span class="indeterm_check__wrap">
                            <span class="indeterm_check" @click="viewSettings.rl_view = !viewSettings.rl_view;can_save_sett = true">
                                <i v-if="viewSettings.rl_view" class="glyphicon glyphicon-ok group__icon"></i>
                            </span>
                        </span>
                        <span>View&nbsp;</span>
                    </label>
                    <label style="">
                        <span>Size:</span>
                        <input style="width: 75px;display: inline-block;" class="form-control" type="number" @change="can_save_sett = true;drawWholeGeom();" min="0" max="10" v-model="viewSettings.rl_size">
                    </label>
                    <label class="colorPicker" style="margin-left: 27px;">
                        <span>Color:</span>
                        <tablda-colopicker
                                :init_color="viewSettings.defRLColor"
                                :avail_null="viewSettings.defRLColor != '#aaaaaa'"
                                @set-color="(clr) => { setclr('defRLColor', clr) }"
                        ></tablda-colopicker>
                    </label>
                </div>
                <!--ABOVE SAVE SETTINGS-->
                <div class="row envRow">
                    <div class="environment">

                        <div class="content">
                            <div class="skyboxContainer">
                                <span>Skybox:</span>
                                <select style="float: left; width: 84px; margin-left: 0px;" class="form-control" v-model="viewSettings.skybox" @change="can_save_sett = true">
                                    <option value=""></option>
                                    <option value="skyboxColorPicker">Color</option>
                                    <option v-for="option in skyBoxOptions" :value="option.value">{{option.name}}</option>
                                </select>

                                <span class="colorPicker" v-show="viewSettings.skybox === 'skyboxColorPicker'">
                                    <tablda-colopicker
                                            :init_color="viewSettings.skyboxColor"
                                            :avail_null="true"
                                            @set-color="(clr) => { viewSettings.skyboxColor = clr;can_save_sett = true }"
                                    ></tablda-colopicker>
                                </span>
                            </div>

                            <div class="terrainContainer">
                                <span>Terrain:</span>
                                <select style="float: left; width: 84px; margin-left: 0px;" class="form-control" v-model="viewSettings.terrain" @change="can_save_sett = true">
                                    <option value=""></option>
                                    <option value="terrainColorPicker">Color</option>
                                    <option v-for="option in terrainOptions" :value="option.src">{{option.name}}</option>
                                </select>

                                <span class="colorPicker" v-show="viewSettings.terrain === 'terrainColorPicker'">
                                    <tablda-colopicker
                                            :init_color="viewSettings.terrainColor"
                                            :avail_null="true"
                                            @set-color="(clr) => { viewSettings.terrainColor = clr;can_save_sett = true }"
                                    ></tablda-colopicker>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="frameRange">
                        <span style="margin-bottom: 5px;">CS Frame Size:</span>
                        <input type="range" step="0.5" min="1" max="5" value="2.5"
                               v-model="viewSettings.frameScale" @change="can_save_sett = true">
                    </div>
                    <div v-if="$root.user.id && !$root.user.see_view">
                        <button class="btn btn-success btn-sm"
                                style="font-weight: bold;position: relative;top: 5px;"
                                :disabled="!can_save_sett"
                                @click="vSettSave()"
                        >Save Settings</button>
                    </div>
                </div>

                <div style="font-weight: 400; height: 25px; margin-top: 5px;">
                    <span style="width: 65px;display: inline-block;">
                        <a v-if="viewSettings.active_camera != 1" @click="settActCamera(1)">View #1</a>
                        <span v-else="" style="font-weight: bold;">View #1</span>
                        <span>:&nbsp;</span>
                    </span>

                    <span>X:</span>
                    <input class="inp_stl form-control" type="text" @change="change_Camera_Position()" v-model="viewSettings.camera_pos_x">
                    <span>ft &nbsp;&nbsp;</span>

                    <span>Y:</span>
                    <input class="inp_stl form-control" type="text" @change="change_Camera_Position()" v-model="viewSettings.camera_pos_y">
                    <span>ft &nbsp;&nbsp;</span>

                    <span>Z:</span>
                    <input class="inp_stl form-control" type="text" @change="change_Camera_Position()" v-model="viewSettings.camera_pos_z">
                    <span>ft</span>

                    <button v-if="viewSettings.active_camera == 1 && can_save_cam && !$root.user.see_view"
                            class="btn btn-success btn-sm"
                            style="margin-left: 15px;padding: 3px 5px;"
                            @click="vSettSave()"
                    >
                        <i class="fas fa-save"></i>
                    </button>
                </div>

                <div style="font-weight: 400; height: 25px; margin-top: 5px;">
                    <span style="width: 65px;display: inline-block;">
                        <a v-if="viewSettings.active_camera != 2" @click="settActCamera(2)">View #2</a>
                        <span v-else="" style="font-weight: bold;">View #2</span>
                        <span>:&nbsp;</span>
                    </span>

                    <span>X:</span>
                    <input class="inp_stl form-control" type="text" @change="change_Camera_Position()" v-model="viewSettings.camera_add1_x">
                    <span>ft &nbsp;&nbsp;</span>

                    <span>Y:</span>
                    <input class="inp_stl form-control" type="text" @change="change_Camera_Position()" v-model="viewSettings.camera_add1_y">
                    <span>ft &nbsp;&nbsp;</span>

                    <span>Z:</span>
                    <input class="inp_stl form-control" type="text" @change="change_Camera_Position()" v-model="viewSettings.camera_add1_z">
                    <span>ft</span>

                    <button v-if="viewSettings.active_camera == 2 && can_save_cam && !$root.user.see_view"
                            class="btn btn-success btn-sm"
                            style="margin-left: 15px;padding: 3px 9px;"
                            @click="vSettSave()"
                    >
                        <i class="fas fa-save"></i>
                    </button>
                </div>

                <div style="font-weight: 400; height: 25px; margin-top: 5px;">
                    <span style="width: 65px;display: inline-block;">
                        <a v-if="viewSettings.active_camera != 3" @click="settActCamera(3)">View #3</a>
                        <span v-else="" style="font-weight: bold;">View #3</span>
                        <span>:&nbsp;</span>
                    </span>

                    <span>X:</span>
                    <input class="inp_stl form-control" type="text" @change="change_Camera_Position()" v-model="viewSettings.camera_add2_x">
                    <span>ft &nbsp;&nbsp;</span>

                    <span>Y:</span>
                    <input class="inp_stl form-control" type="text" @change="change_Camera_Position()" v-model="viewSettings.camera_add2_y">
                    <span>ft &nbsp;&nbsp;</span>

                    <span>Z:</span>
                    <input class="inp_stl form-control" type="text" @change="change_Camera_Position()" v-model="viewSettings.camera_add2_z">
                    <span>ft</span>

                    <button v-if="viewSettings.active_camera == 3 && can_save_cam && !$root.user.see_view"
                            class="btn btn-success btn-sm"
                            style="margin-left: 15px;padding: 3px 9px;"
                            @click="vSettSave()"
                    >
                        <i class="fas fa-save"></i>
                    </button>
                </div>

            </div>
            <div id="2d" style="display: none;"></div>

            <div class="viewByStatusPanel flex flex--col" v-show="GeomColors.filters_show">
                <label v-for="filter in GeomColors.colors"
                       v-if="filter.show"
                       class="status-filter"
                       :style="{backgroundColor: filter.model_val}"
                >
                    <span class="indeterm_check__wrap">
                        <span class="indeterm_check" @click="filter.checked = !filter.checked;drawWholeGeom()">
                            <i v-if="filter.checked" class="glyphicon glyphicon-ok group__icon"></i>
                        </span>
                    </span>
                    <span>{{ filter.key == 'null' ? '' : filter.key }}</span>
                </label>
            </div>

            <div class="viewByStatusPanel flex flex--col" v-show="MaClrStatuses.filters_show">
                <label v-for="filter in MaClrStatuses.colors"
                       v-if="filter.show"
                       class="status-filter"
                       :style="{backgroundColor: filter.model_val}"
                >
                    <span class="indeterm_check__wrap">
                        <span class="indeterm_check" @click="filter.checked = !filter.checked;drawWholeGeom()">
                            <i v-if="filter.checked" class="glyphicon glyphicon-ok group__icon"></i>
                        </span>
                    </span>
                    <span>{{ filter.key == 'null' ? '' : filter.key }}</span>
                </label>
                <div v-if="MaClrStatuses.rls.present" class="rls_checker">
                    <label class="status-filter">
                        <span class="indeterm_check__wrap">
                            <span class="indeterm_check" @click="MaClrStatuses.rls.hidden = !MaClrStatuses.rls.hidden;drawWholeGeom()">
                                <i v-if="MaClrStatuses.rls.hidden" class="glyphicon glyphicon-ok group__icon"></i>
                            </span>
                        </span>
                        <span>Hide RLs</span>
                    </label>
                </div>
            </div>
            
            <div id="webgl"></div>
            <div id="DOMaxesHelper"></div>
        </div>

        <tablda-direct-popup
                v-if="popup_row_id && vuex_fm[popup_app_tb]"
                :meta-table="vuex_fm[popup_app_tb].meta"
                :stim_link_params="vuex_links[popup_app_tb]"
                :row-id="popup_row_id"
                :shift-object="{top_px: 110,left: 0.6}"
                @close-popup="popup_row_id = null"
                @pre-insert="preInsert"
                @pre-update="preUpdate"
                @pre-delete="preDelete"
                @row-inserted="directInsert"
                @row-updated="directUpdate"
                @row-deleted="directDelete"
                @another-row="anotherDirectRow"
        ></tablda-direct-popup>
    </div>
</template>

<script>
    //
    import {ThreeHelper} from '../../classes/helpers/ThreeHelper';
    import {MetaTabldaRows} from '../../classes/MetaTabldaRows';
    import {Settings} from './Configurator/Settings';
    import {WidSettings} from './WidSettings';
    import {Secpos} from './Configurator/Secpos';

    import { eventBus } from "./../../app";

    import { mapState, mapGetters, mapMutations, mapActions } from 'vuex';

    import Three3dDrawMixin from './Three3dDrawMixin.vue';
    import DirectPopMixin from './Configurator/DirectPopMixin.vue';

    import TabldaDirectPopup from "./MainData/TabldaDirectPopup";
    import TabldaColopicker from "../../components/CustomCell/InCell/TabldaColopicker";
    import CanvLibMenu from "./Configurator/CanvLibMenu";
    import CustomEditPopUp from "../../components/CustomPopup/CustomEditPopUp";
    
    export default {
        name: 'Three3dWid',
        mixins: [
            Three3dDrawMixin,
            DirectPopMixin,
        ],
        components: {
            CustomEditPopUp,
            CanvLibMenu,
            TabldaColopicker,
            TabldaDirectPopup,
        },
        data() {
            return {
                deboucedFullReload: _.debounce(() => {
                    this.fullReload();
                }, 1000),
                direct_row_3d: null,
                local_load_data: null,
                sett_top: 0,
                sett_left: 0,
                eqptPop: null,
                confSett: new Settings(),
                pxInFt: 20,

                GeomColors: {
                    filters_show: false,
                    view_enabled: true,
                    colors: [
                        {
                            model_val: '#ffffff',
                            key: 'color_code',
                            check: true,
                            show: true,
                        },
                    ],
                },

                MaClrStatuses: {
                    filters_show: false,
                    view_enabled: true,
                    colors: [
                        {
                            model_val: '#ffffff',
                            key: 'status',
                            check: true,
                            show: true,
                        },
                    ],
                    rls: {
                        present: false,
                        hidden: true,
                    },
                },

                can_save_sett: false,
                can_save_cam: false,

                terrainOptions: [
                    {
                        name: 'grass',
                        src: 'grass/grass.jpg'
                    },
                    {
                        name: 'gravel',
                        src: 'gravel/gravel.jpg'
                    }
                ],

                skyBoxOptions: [
                    {
                        name: 'sunny',
                        value: 'skybox01'
                    },
                    {
                        name: 'dawn',
                        value: 'skybox02'
                    }
                ],

                base: {
                    toggleSettingsMenu: false,
                    showAxesHelperForMesh: false,
                },

                TR: {
                    dx: 0,
                    dy: 0,
                    dz: 0,
                    rotx: 0,
                    roty: 0,
                    rotz: 0
                },

                edges_menu: false,
                cam_menu: false,
                cur_cam_orto: false,
                sync_in_process: false,
                progress_rl: 0,
                ticker: null,
            }
        },
        computed: {
            ...mapState({
                vuex_cur_select: state => state.cur_select,
                vuex_redraw_soft_counter: (state) => state.redraw_soft_counter,
                vuex_redraw_counter: (state) => state.redraw_counter,
                vuex_settings: state => state.stim_settings,
                vuex_links: state => state.stim_links_container,
                vuex_fm: state => state.found_models,
                vuex_main: state => state.width_main,
                vuex_3d: state => state.width_3d,
                viewSettings: state => state.settings_3d,
            }),
            ...mapGetters({
                vuex_tab_object: 'cur_tab_object',
            }),
            settStyle() {
                return {
                    position: 'fixed',
                    top: this.sett_top+'px',
                    left: this.sett_left+'px',
                };
            },
        },
        watch: {
            viewSettings: {
                handler(newValue) {
                    if (!this.viewSettings.nodes) {
                        this.viewSettings.nodesName = false;
                    }

                    if (!this.viewSettings.wireframe) {
                        this.viewSettings.wireframeName = false;
                    }

                    if (this.webglReady()) {
                        webgl.changeViewSettingsWID(this.viewSettings);
                        webgl.changeGridSettings(this.viewSettings);
                    }
                },
                deep: true,
            },
            vuex_redraw_counter(newValue) {
                this.redrawModel('vuex_redraw_counter');
            },
            vuex_redraw_soft_counter(newValue) {
                this.redrawModel('vuex_redraw_soft_counter');
            },
            cur_tab_sel(newValue) {
                this.closeSettingsReload();
            },
        },
        props: {
            cur_tab_sel: String,
        },
        methods: {
            ...mapMutations([
                'SET_WIDTH_3D',
            ]),
            setEdgeclr(clr) {
                setTimeout(() => {
                    this.viewSettings.edges_color = clr || null;
                    this.can_save_sett = true;
                    this.vSettSave();
                }, 100);
            },
            setclr(fld, clr) {
                setTimeout(() => {
                    this.viewSettings[fld] = clr || '#aaaaaa';
                    this.can_save_sett = true;
                }, 100);
            },
            settActCamera(cam) {
                this.viewSettings.active_camera = cam;
                this.change_Camera_Position();
            },
            vSettEL() {
                this.viewSettings.edges_members = !this.viewSettings.edges_members;
                this.viewSettings.edges_eqpts = !this.viewSettings.edges_eqpts;
            },
            vSettSave() {
                if (!this.$root.user.see_view) {
                    this.viewSettings.saveSettings();
                    this.drawWholeGeom();
                }
                this.can_save_sett = false;
                this.can_save_cam = false;
            },
            closeSettingsReload() {
                this.base.toggleSettingsMenu = false;
                this.GeomColors.filters_show = false;
                this.MaClrStatuses.filters_show = false;
                this.redrawModel('closeSettingsReload');
            },
            set_Default_Angle_WID() {
                if (this.webglReady()) {
                    this.viewSettings.defaultAngle = true;
                    webgl.changeViewSettingsWID(this.viewSettings);
                    this.viewSettings.setCameraP(-5, 8, 6);
                    this.viewSettings.defaultAngle = false;
                }
            },
            change_Camera_Position() {
                let cam_pos = this.viewSettings.cameraPosGet();
                if (webgl && cam_pos) {
                    webgl.changeCameraPosition(cam_pos.x, cam_pos.y, cam_pos.z);
                }
            },
            make_Screenshot() {
                if (!this.webglReady()) {
                    return;
                }
                let url = webgl.getCurentScreenshotURL();

                let cmdOrCtrl = window.event.metaKey || window.event.ctrlKey;
                if (!cmdOrCtrl) {
                    let a = document.createElement('a');
                    a.href = url;
                    a.download = this.screenshotname();
                    a.click();
                }
                else {
                    let w = window.open('', '');
                    w.document.title = $('head title').text();
                    let img = new Image();
                    img.src = url;
                    w.document.body.appendChild(img);
                }
            },
            screenshotname() {
                let strings = [];

                let foundModel = this.vuex_fm[this.vuex_tab_object.master_table];
                let stim_link_params = this.vuex_links[this.vuex_tab_object.master_table];
                if (stim_link_params && foundModel && foundModel.meta && foundModel.meta.is_loaded) {
                    let t_meta = foundModel.meta.params;
                    let model_row = foundModel.masterRow();
                    _.each(stim_link_params.top_columns_show, (db_name) => {
                        let fld = _.find(t_meta._fields, {field: db_name});
                        if (fld && fld.f_type === 'User') {
                            let usr_string = this.$root.getUserFullStr(model_row, fld, t_meta._owner_settings);
                            strings.push(usr_string);
                        } else {
                            strings.push(model_row[db_name]);
                        }
                    });
                }

                return strings.join('_') + '_'
                    + String(this.vuex_cur_select).toUpperCase() + '_3D_'
                    + moment().format('YYYYMMDD_HHmmss') + '.png';
            },
            checkRLSClrs(rls) {
                this.MaClrStatuses.rls.present = rls && rls.rows && rls.rows.length;
            },
            checkMaStatusClrs(model_colors, all_statuses, excluded) {
                this.MaClrStatuses.colors = this.checkColors(model_colors, all_statuses, excluded);
            },
            checkGeomClrs(model_colors, all_statuses, excluded) {
                this.GeomColors.colors = this.checkColors(model_colors, all_statuses, excluded);
            },
            checkColors(model_colors, all_statuses, excluded) {
                all_statuses = _.map(all_statuses, (st) => { return st || 'null'; });
                let res = [
                    {
                        key: 'null',
                        model_val: this.viewSettings.defEqptColor || '#cccccc',
                        checked: !in_array('null', excluded),
                        show: in_array('null', all_statuses),
                    }
                ];
                _.each(model_colors, (clr) => {
                    res.push({
                        key: clr.name,
                        model_val: clr.color,
                        checked: !in_array(clr.name, excluded),
                        show: in_array(clr.name, all_statuses),
                    });
                });
                return res;
            },

            cameraChange(orto_type) {
                if (this.webglReady()) {
                    this.cur_cam_orto = orto_type;
                    webgl.cameraChange(orto_type);
                }
            },
            redrawModel(from) { //v.3
                console.log('redrawModel',from);
                if (!this.webglReady()) {
                    return;
                }
                this.sync_in_process = true;
                let app_tb = this.vuex_tab_object.master_table;
                let cur_fm = this.vuex_fm[app_tb];
                let master_model = cur_fm && cur_fm.rows ? cur_fm.rows.get3D(0) : null;
                master_model = master_model && master_model._id
                    ? master_model
                    : cur_fm.rows.getTemp3DRow();

                switch (this.vuex_tab_object.type_3d) {
                    case '3d:equipment':
                        let params = {
                            found_model: master_model,
                        };
                        this.Three3dRedraw(params, 'equipment');
                        break;

                    case '3d:structure':
                        if (master_model._id) {
                            let excluded = _.map(_.filter(this.GeomColors.colors, {checked: false}), (clr) => {
                                return clr.key;
                            });
                            axios.post('?method=load_3d_rows', {
                                type_3d: '3d:structure',
                                app_table: app_tb,
                                master_model: master_model,
                                excluded_colors: excluded,
                                front_filters: this.getFrontFilters(),
                            }).then(({data}) => {
                                this.checkGeomClrs(data.colors, _.map(data.params.materials, 'color_gr'), excluded);
                                this.Three3dRedraw(data.params, 'geometry', null, null, null,{excluded_colors: excluded});
                            }).catch(errors => {
                                Swal('Info', getErrors(errors));
                            });
                        } else {
                            this.Three3dRedraw(null);
                        }
                        break;

                    case '3d:ma':
                        if (master_model._id) {
                            let excluded = _.map(_.filter(this.MaClrStatuses.colors, {checked: false}), (clr) => {
                                return clr.key;
                            });
                            axios.post('?method=load_3d_rows', {
                                type_3d: '3d:ma',
                                app_table: app_tb,
                                master_model: master_model,
                                excluded_colors: excluded,
                                front_filters: this.getFrontFilters(),
                            }).then(({data}) => {
                                this.local_load_data = data;
                                this.checkRLSClrs(data.rls);
                                this.checkMaStatusClrs(data.colors.ma, data.eqs.all_statuses, excluded);
                                this.checkGeomClrs(data.colors.geom, _.map(data.params.materials, 'color_gr'), []);
                                this.Three3dRedraw(data.params, 'geometry', data.eqs, data.libs, data.rls, {
                                    rl_sett: this.MaClrStatuses.rls
                                });
                            }).catch(errors => {
                                Swal('Info', getErrors(errors));
                            });
                        } else {
                            this.Three3dRedraw(null);
                        }
                        break;

                    default:
                        this.Three3dRedraw(null);
                        break;
                }

                this.loadWidSettings(from);
            },
            checkDrawedArr(draw_arr, start_hash, table_row, type) {
                _.each(draw_arr, (row, i) => {
                    if (row && row._row_hash === start_hash) {
                        switch (type) {
                            case 'update':
                                draw_arr.splice(i, 1, table_row);
                                draw_arr[i]._row_hash = uuidv4();
                                break;
                            case 'del':
                                draw_arr.splice(i, 1);
                                break;
                        }
                    }
                });
            },
            drawWholeGeom() {
                this.redrawModel('drawWholeGeom');
            },
            getFrontFilters() {
                let avail_app_tables = _.map(this.vuex_tab_object.tables, 'table');
                let result = {};
                _.each(this.vuex_fm, (el, app_tb) => {
                    if (in_array(app_tb, avail_app_tables) && el.rows && el.rows.filters_active && el.rows.filters.length) {
                        let fltrs = _.filter(el.rows.filters, (fl) => { return !!fl.applied_index; });
                        if (fltrs && fltrs.length) {
                            result[app_tb] = fltrs;
                        }
                    }
                });
                return result;
            },

            //Direct graph edits
            masterParams(app_tb, master_row) {
                app_tb = String(app_tb).toLowerCase();
                let stim_link_params = this.vuex_links[app_tb];
                let row = {};
                _.each(stim_link_params.link_fields, (fld) => {
                    row[fld.data_field] = master_row[fld.link_field_db];
                });
                return row;
            },
            tablda_push(tablda_elem, row_id) {
                if (tablda_elem) {
                    this.$root.tablda_highlights.push({
                        table_id: tablda_elem.id,
                        row_id: row_id,
                    });
                }
            },
            intersect_tablda(intersel_arr) {
                this.$root.tablda_highlights = [];
                //just standard click
                _.each(intersel_arr, (itersect) => {
                    if (!itersect) {
                        return;
                    }

                    let sel_ty = this.getMeshType(itersect);
                    if (sel_ty === 'node' && this.tabldas.node) {
                        this.tablda_push(this.tabldas.node, itersect.item);
                    }
                    if (sel_ty === 'member' && this.tabldas.memb) {
                        this.tablda_push(this.tabldas.memb, itersect.item_no);
                        this.tablda_push(this.tabldas.mat, itersect.parent.mat_id);
                        this.tablda_push(this.tabldas.sect, itersect.parent.sec_id);
                        this.tablda_push(this.tabldas.node, itersect.parent.node_start_id);
                        this.tablda_push(this.tabldas.node, itersect.parent.node_end_id);
                        _.each(itersect.parent.attached_eqpt_ids, (eq_id) => {
                            this.tablda_push(this.tabldas.lcs, eq_id);
                        });
                        _.each(itersect.parent.attached_postombrs, (ptm_id) => {
                            this.tablda_push(this.$pos_to_mbr_tb, ptm_id);
                        });
                    }
                    if (sel_ty === 'equipment' && this.tabldas.lcs) {
                        this.tablda_push(this.tabldas.lcs, itersect.parent.lc_id);
                    }
                    if (sel_ty === 'rl_bracket' && this.tabldas.rls) {
                        this.tablda_push(this.tabldas.rls, itersect.rl_id);
                    }
                });
                //scroll all tablda-components - to last clicked ////
                if (this.$root.tablda_highlights.length) {
                    _.each(this.$root.tablda_highlights, (hgl) => {
                        eventBus.$emit('scroll-tabldas-to-row', hgl.table_id, hgl.row_id);
                    });
                }
            },
            getMeshType(mesh) {
                return mesh.type === 'node'
                    ? 'node'
                    : (mesh.single_type || mesh.parent.type);
            },
            selectedFunction(mesh, intersel_arr) {
                this.hideMaEqptHelper();
                if (mesh) {
                    let sel_type = this.getMeshType(mesh);
                    //has selected item in Lib
                    //add Eqpt to Member
                    if (sel_type === 'member' && mesh.item_no && mesh.parent && this.confSett.drag_eqpt) {
                        let app = this.lcAppTb();
                        let model = {
                            equipment: this.confSett.drag_eqpt.equipment,
                            mbr_name: mesh.parent.Mbr_Name,
                            dist_to_node_s: '0.01',
                        };
                        let master = this.masterParams(app, this.ma_tablda);
                        this.save_model_func(app, model, master, true);
                    }
                    //add PosToMbr for the Member
                    if (sel_type === 'member' && mesh.item_no && this.$pos_to_mbr_tb.app_table && this.$ma_3d && this.confSett.sel_secpos) {
                        let model = {
                            geometry: this.$ma_3d.structure,
                            sector: this.confSett.sel_secpos.sec_name,
                            pos: this.confSett.sel_secpos.posidx,
                            member: mesh.parent.Mbr_Name,
                        };
                        let master = this.masterParams(this.$pos_to_mbr_tb.app_table, this.loa_tablda);
                        this.save_model_func(this.$pos_to_mbr_tb.app_table, model, master, false, 'remove_prev');
                    }
                    //change Eqpt settings
                    if (sel_type === 'equipment' && mesh.parent.lc_id) {
                        if (this.confSett.sel_status || this.confSett.sel_tech || this.confSett.sel_elev) {
                            let app = this.lcAppTb();
                            let model = {_id: mesh.parent.lc_id};
                            (this.confSett.sel_status ? model.status = this.confSett.sel_status.name : null);
                            (this.confSett.sel_tech ? model.tech = this.confSett.sel_tech.technology : null);
                            if (this.confSett.sel_elev) {
                                switch (this.confSett.elev_by) {
                                    case "pd":
                                        model.elev_pd = this.confSett.sel_elev.elev;
                                        break;
                                    case "gc":
                                        model.elev_g = this.confSett.sel_elev.elev;
                                        break;
                                    case "pc":
                                        model.elev_rad = this.confSett.sel_elev.elev;
                                        break;
                                }
                            }
                            this.save_model_func(app, model, null, true);
                        } else {
                            this.showMaEqptHelper(mesh.parent);
                        }
                    }
                } else {
                    this.confSett.clearSel();
                }

                if (intersel_arr) {
                    this.intersect_tablda(intersel_arr);
                }
            },
            save_model_func(app, model, master, no_clear, remove_prev) {//
                axios.post('?method=save_model', {
                    app_table: app,
                    model: model,
                    master_params: master,
                    remove_prev: remove_prev,
                }).then(({data}) => {
                    (!no_clear ? this.confSett.clearSel() : null);
                    this.redrawModel('save_model_func');
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                });
            },
            rClickFunction(mesh, intersel_arr) {
                if (mesh) {
                    this.popup_row_id = null;
                    this.popup_type = this.getMeshType(mesh);
                    //Node popup
                    if (this.popup_type === 'node' && mesh.item) {
                        this.setPopupParams(mesh.item, this.vuex_settings.popups_models.nodes);
                    }
                    //Member popup
                    if (this.popup_type === 'member' && mesh.item_no) {
                        this.setPopupParams(mesh.item_no, this.vuex_settings.popups_models.members);
                    }
                    //Eqpt menu popup
                    if (this.popup_type === 'equipment' && mesh.parent.lc_id && mesh.parent.eqpt_id) {
                        this.eqptPop = {
                            lc_id: mesh.parent.lc_id,
                            eqpt_id: mesh.parent.eqpt_id,
                        };
                        this.sett_top = this.$root.lastMouseClick.clientY;
                        this.sett_left = this.$root.lastMouseClick.clientX;
                    }
                    //RL Bracket popup1
                    if (this.popup_type === 'rl_bracket' && mesh.rl_id) {
                        this.setPopupParams(mesh.rl_id, this.vuex_settings.popups_models.rls);
                    }
                }

                if (intersel_arr) {
                    this.intersect_tablda(intersel_arr);
                }
            },
            rEqptSelected(type) {
                switch (type) {
                    case 'lcs': this.setPopupParams(this.eqptPop.lc_id, this.vuex_settings.popups_models.lcs);
                        break;
                    case 'eqs': this.setPopupParams(this.eqptPop.eqpt_id, this.vuex_settings.popups_models.equipment);
                        break;
                }
                this.eqptPop = null;
            },
            setPopupParams(row_id, app_tb) {
                this.popup_row_id = row_id;
                this.popup_app_tb = app_tb;
                this.link_rows = this.vuex_fm[app_tb] ? this.vuex_fm[app_tb].rows : null;
            },
            updStep(key, step, inc) {
                let un = this.ma_eqpt_helper_row[step+'_unit'];
                let div = un === 'in' ? 12 : (un === 'rad' ? Math.PI/180 : 1);
                let stepFloat = to_float(this.ma_eqpt_helper_row[step]) / div;

                if (this.ma_eqpt_helper_row && stepFloat) {
                    this.ma_eqpt_helper_row[key] = inc === 'inc'
                        ? to_float(this.ma_eqpt_helper_row[key]) + stepFloat
                        : to_float(this.ma_eqpt_helper_row[key]) - stepFloat;
                    this.updMaEqptRow(key);
                }
            },
            updMaEqptRow(key) {
                let app_tb = this.lcAppTb();
                let metaLcRows = this.vuex_fm[app_tb] ? this.vuex_fm[app_tb].rows : null;
                let stimLinkParam = this.vuex_links[app_tb];
                let lcMaps = metaLcRows ? metaLcRows.maps : null;

                if (metaLcRows && lcMaps && this.ma_eqpt_helper_row) {
                    let updatedRow = metaLcRows.convertTo3D(this.ma_eqpt_helper_row);
                    updatedRow._old_val = this.ma_eqpt_old_row[key];
                    updatedRow._changed_field = lcMaps[key];

                    metaLcRows.massUpdatedRows(null, [updatedRow]).then(() => {
                        this.ma_eqpt_old_row = _.clone(this.ma_eqpt_helper_row);
                        let is3dLC = ThreeHelper.watched3d_is_needed_action(stimLinkParam.app_fields, [updatedRow]);
                        if (is3dLC) {
                            this.redrawModel('vuex_redraw_soft_counter');
                        } else {
                            this.direct_row_3d = _.clone(this.ma_eqpt_helper_row);
                            this.checkRecalcRL(false) || this.fullReload();
                        }
                    });
                }
            },
            eqptHelperUnitRecalc(key) {
                switch (key) {
                    case '_top_delta_unit': this.ma_eqpt_helper_row._top_delta = this.ma_eqpt_helper_row._top_delta_unit === 'ft'//changed to ft?
                        ? to_float(this.ma_eqpt_helper_row._top_delta) * 12
                        : to_float(this.ma_eqpt_helper_row._top_delta) / 12;
                    break;
                    case '_bottom_delta_unit': this.ma_eqpt_helper_row._bottom_delta = this.ma_eqpt_helper_row._bottom_delta_unit === 'rad'//changed to rad?
                        ? to_float(this.ma_eqpt_helper_row._bottom_delta) * (Math.PI/180)
                        : to_float(this.ma_eqpt_helper_row._bottom_delta) / (Math.PI/180);
                    break;
                }
                this.ma_eqpt_helper_row._top_delta = Number(this.ma_eqpt_helper_row._top_delta).toFixed(1);
                this.ma_eqpt_helper_row._bottom_delta = Number(this.ma_eqpt_helper_row._bottom_delta).toFixed(1);
            },
            lcAppTb() {
                return this.vuex_settings.popups_models.lcs;
            },

            //direct popups
            preInsert(startHash, tableRow) {
                this.direct_row_3d = this.vuex_fm[this.popup_app_tb].rows.convertOne(tableRow);
            },
            preUpdate(startHash, tableRow) {
                this.direct_row_3d = this.vuex_fm[this.popup_app_tb].rows.convertOne(tableRow);
            },
            preDelete(startHash, tableRow) {
                this.direct_row_3d = this.vuex_fm[this.popup_app_tb].rows.convertOne(tableRow);
            },
            directInsert(data) {
                if (data.rows && data.rows.length) {
                    this.popup_row_id = data.rows[0].id;
                    this.direct_row_3d._id = data.rows[0].id;
                }
                this.checkRecalcRL(false) || this.fullReload();
            },
            directUpdate(data) {
                this.checkRecalcRL(false) || this.fullReload();
            },
            directDelete(data) {
                this.popup_row_id = null;
                this.checkRecalcRL(true) || this.fullReload();
            },
            fullReload() {
                if (this.link_rows && this.link_rows.is_loaded) {
                    let meta = this.vuex_fm[this.popup_app_tb] ? this.vuex_fm[this.popup_app_tb].meta.params : null;
                    this.link_rows.loadRows(meta);
                }
                this.redrawModel('fullReload');
            },
            checkRecalcRL(to_delete) {
                let cur_stim_link = this.popup_app_tb ? this.vuex_links[this.popup_app_tb] : '';
                let avail = this.direct_row_3d
                    && cur_stim_link
                    && cur_stim_link.app_table_options.indexOf('recalc_rl:true') > -1;
                
                if (avail) {
                    let app_tb = this.vuex_tab_object.master_table;
                    let cur_fm = this.vuex_fm[app_tb];
                    let master_model = cur_fm && cur_fm.rows ? cur_fm.rows.get3D(0) : null;

                    if (this.local_load_data) {
                        this.rlstartRecalc(master_model, to_delete);
                    } else {
                        ThreeHelper.loadDataServer(this.master_stim_link.app_table, master_model).then(({data}) => {
                            this.local_load_data = data;
                            this.rlstartRecalc(master_model, to_delete);
                        }).catch(errors => {
                            Swal('Info', getErrors(response.errors));
                        });
                    }
                    ////////
                }
                
                return avail;
            },
            rlstartRecalc(master_model, to_delete) {
                //NOTE: Auto-recalc of RL is disabled
                this.direct_row_3d = null;
                return;
                // if (to_delete) {
                //     this.direct_row_3d._id = null;
                // }
                // let helper = new ThreeHelper(this.local_load_data, master_model);
                // helper.startCalculationRL(this.direct_row_3d);
                // this.direct_row_3d = null;
                //
                // this.progress_rl = 0;
                // let timeint = setInterval(() => {
                //     this.progress_rl = helper.getProgress();
                //     if (this.progress_rl >= 100) {
                //         clearInterval(timeint);
                //         this.deboucedFullReload();
                //     }
                // }, 500);
            },
            afterThreeJsDistanceCalc(lc) {
                let app_tb = this.vuex_tab_object.master_table;
                let cur_fm = this.vuex_fm[app_tb];
                let master_model = cur_fm && cur_fm.rows ? cur_fm.rows.get3D(0) : null;
                this.direct_row_3d = lc;
                this.rlstartRecalc(master_model);
            },

            //another popup row
            getDrawedRows() {
                let rows = [];
                if (this.popup_type === 'node') {
                    rows = this.drawed_geometry.nodes;
                }
                if (this.popup_type === 'member') {
                    rows = this.drawed_geometry.members;
                }
                if (this.popup_type === 'equipment') {
                    let collect = this.drawed_equipments ? this.drawed_equipments.collection : [];
                    rows = _.map(collect, (elem) => {
                        return elem.lc;
                    });
                }
                if (this.popup_type === 'rl_bracket') {
                    rows = this.drawed_rls.rows;
                }
                return rows;
            },
            anotherDirectRow(is_next) {
                let rows = this.getDrawedRows();
                this.$root.anotherPopup(rows, this.popup_row_id, is_next, this.selectAnotherRow);
            },
            selectAnotherRow(idx) {
                if (this.webglReady()) {
                    let rows = this.getDrawedRows();
                    this.popup_row_id = rows[idx]._id;
                    webgl.outerSelect(this.popup_type, this.popup_row_id);
                }
            },

            hideSett(e) {
                let sett = $(this.$refs.wid_sett);
                let butt = $(this.$refs.wid_butt);
                if (this.base.toggleSettingsMenu && sett.has(e.target).length === 0 && butt.has(e.target).length === 0) {
                    this.base.toggleSettingsMenu = false;
                }
                let menu = $(this.$refs.eqpt_menu);//only for left click
                if (this.eqptPop && e.button === 0 && menu.has(e.target).length === 0) {
                    this.eqptPop = null;
                }
            },

            //Lib functions1
            libToggle(hidden) {
                //this.SET_WIDTH_3D({main: !hidden, d3: true});
            },
            set_id_obj(id_object, key, map) {
                if (this.tabldas[key]) {
                    id_object[this.tabldas[key].id] = _.map(map, (el) => {
                        return (key == 'lcs' ? el.lc._id : (key == 'eqs' ? el.eq._id : el._id));
                    });
                    return true;
                } else {
                    return false;
                }
            },
            widPressKey(e) {
                if (e.keyCode === 46 && this.$root.tablda_highlights.length) { //delete key

                    let promises = [];
                    let del_array = _.groupBy(this.$root.tablda_highlights, 'table_id');
                    _.each(del_array, (arr, tb_id) => {
                        let row_ids = _.map(arr, 'row_id');
                        let remover = new MetaTabldaRows({table_id: tb_id});
                        promises.push( remover.deleteSelected(row_ids, true) );
                    });
                    Promise.all(promises).then(() => {
                        this.redrawModel('widPressKey');
                    });

                }
            },
            loadWidSettings(from) {
                if (['drawWholeGeom','fullReload'].indexOf(from) > -1) {
                    return;
                }
                let app_tb = this.vuex_tab_object.master_table;
                let cur_fm = this.vuex_fm[app_tb];
                let master_model = cur_fm && cur_fm.rows ? cur_fm.rows.get3D(0) : null;
                let curtab = this.vuex_tab_object.init_top+'_'+this.vuex_tab_object.init_select;

                axios.post('?method=load_3d_rows&t=wid_settings', {
                    type_3d: 'wid_settings',
                    usergroup: master_model ? master_model.usergroup || this.$root.user.id : this.$root.user.id,
                    model: master_model ? master_model.model : ' ',
                    curtab: curtab,
                }).then(({data}) => {
                    let skip_pos = ['vuex_redraw_counter','closeSettingsReload'].indexOf(from) === -1;
                    //EL is default disabled
                    data = data || {};
                    //data.edges_eqpts = 0;
                    //data.edges_members = 0;
                    this.viewSettings.applyProps(data, this.$root.user.id, skip_pos);
                    this.change_Camera_Position();
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                });
            },

            //sync camera position
            syncSettPosition(position) {
                this.can_save_cam = this.viewSettings.setCameraP(position.x, position.y, position.z);
            },

            //data changes watcher//
            intervalTickHandler() {
                if (!this.drawed_geometry || this.sync_in_process || this.$root.sm_msg_type) {
                    return;
                }

                let can = false;
                let id_object = {};

                can = this.set_id_obj(id_object, 'lcs', this.drawed_equipments ? this.drawed_equipments.collection : []) || can;
                can = this.set_id_obj(id_object, 'eqs', this.drawed_equipments ? this.drawed_equipments.collection : []) || can;
                can = this.set_id_obj(id_object, 'mat', this.drawed_geometry.materials) || can;
                can = this.set_id_obj(id_object, 'node', this.drawed_geometry.nodes) || can;
                can = this.set_id_obj(id_object, 'sect', this.drawed_geometry.sections) || can;
                can = this.set_id_obj(id_object, 'memb', this.drawed_geometry.members) || can;
                can = this.set_id_obj(id_object, 'rls', this.drawed_rls ? this.drawed_rls.rows : []) || can;

                if (!can) {
                    return;
                }

                axios.post('/ajax/table/version_hash', {
                    id_object: id_object,
                }).then(({ data }) => {
                    if (!Object.values(this.tablda_hashes).length) {
                        this.tablda_hashes = data.version_hashes;
                        return;
                    }

                    let changed = _.difference(Object.values(data.version_hashes), Object.values(this.tablda_hashes));
                    if (changed && changed.length) {
                        this.tablda_hashes = data.version_hashes;
                        this.redrawModel('intervalTickHandler');
                    }

                    if (data.job_msg) {
                        Swal('Info', data.job_msg);
                    }
                });
            },
            webglReady() {
                return webgl && webgl._initialized;
            },
        },
        created() {
            //init root colors
            this.MaClrStatuses.colors = [];
            this.GeomColors.colors = [];
        },
        mounted() {//
            if (!window.threeHelper) {
                window.threeHelper = ThreeHelper;
            }
            let ini = setInterval(() => {
                if (webgl && webgl.run) {
                    clearTimeout(ini);
                    webgl.run("#webgl", "wid");
                    webgl.changeViewSettingsWID(this.viewSettings);
                    webgl.selected(this.selectedFunction);
                    webgl.rightClickSelected(this.rClickFunction);
                    webgl.cameraUpdate(this.syncSettPosition);
                    webgl.onCalcDist(this.afterThreeJsDistanceCalc);
                    this.redrawModel('vuex_redraw_counter');
                }
            }, 100);

            //sync datas with collaborators
            this.ticker = setInterval(() => {
                if (!localStorage.getItem('no_ping')) {
                    this.intervalTickHandler();
                }
            }, this.$root.version_hash_delay);

            eventBus.$on('global-click', this.hideSett);
            eventBus.$on('global-keydown', this.widPressKey);
        },
        beforeDestroy() {
            clearInterval(this.ticker);

            eventBus.$off('global-click', this.hideSett);
            eventBus.$off('global-keydown', this.widPressKey);
        },
    }
</script>

<style lang="scss" scoped>
    .webgl-wrapper {
        position: relative;
        width: 100%;
        height: 100%;
        overflow: hidden;
        padding: 10px;
        color: #333;

        #webgl {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 100;
        }
        #DOMaxesHelper {
            z-index: 200;
            position: absolute;
            width: 75px;
            height: 75px;
            bottom: 5px;
            right: 5px;
            border-radius: 4px;
            box-shadow: 0 3px 10px 0 rgba(0, 0, 0, 0.2);
        }



        .logo {
            padding: 10px;
        }

        .edges-setting-button {
            box-shadow: 0 3px 10px 0 rgba(0, 0, 0, 0.2);
            background: #00b0ff;
            border-radius: 2px;
            font-size: 16px;
            font-weight: bold;
            height: 30px;
            position: relative;
            cursor: pointer;
            padding: 3px 4px;
            text-align: center;
            color: #ffffff;

            .edges--menu {
                padding: 3px;
                position: absolute;
                left: 0;
                top: 26px;
                font-size: 14px;
                color: black;
                background-color: #FFF;
                border: 1px solid #CCC;
                border-radius: 5px;

                label {
                    margin: 0;
                }
            }
        }

        .wid_camera {
            padding: 10px;
        }

        .wid_filter {
            //padding: 10px;
        }

        .wid_status_color {
            padding-left: 10px;
        }

        .cam_view {
            left: 210px;
            font-size: 30px;
            width: 48px;
            height: 48px;

            .fa-cubes, .fa-delicious {
                cursor: pointer;
            }
            .cam_view--menu {
                padding: 3px;
                position: absolute;
                left: calc(100% - 6px);
                top: 18px;
                font-size: 12px;

                button {
                    padding: 0 3px;
                    width: 100%;
                }
            }
        }

        .viewByStatusPanel {
            position: absolute;
            bottom: 5px;
            z-index: 200;
            height: auto;
            padding: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background-color: #fff;

            label {
                margin: 0;
                padding: 0 3px;
                border-radius: 3px;
            }
        }



        .settings-btn {
            font-size: 25px;
            position: absolute;
            top: 20px;
            right: 20px;
            cursor: pointer;
            z-index: 200;

            .glyphicon {
                position: relative;
                top: 1px;
                display: inline-block;
                font-style: normal;
                font-weight: 400;
                line-height: 1;
                -webkit-font-smoothing: antialiased;
                -moz-osx-font-smoothing: grayscale;
            }
        }

        .settings-menu {
            position: absolute;
            top: 20px;
            right: 50px;
            background: rgba(195, 194, 194, .85);
            padding: 14px;
            border-radius: 10px;
            font-weight: 600;
            z-index: 240;

            label {
                white-space: nowrap;
            }

            .form-control {
                font-size: 13px;
                padding: 0;
                height: 25px;
            }

            input.form-control {
                padding: 2px;
            }

            .envRow {
                display: inline-flex;
                margin-left: 3px;

                .environment {
                    width: max-content;
                    height: max-content;
                    margin-top: -15px;
                    margin-bottom: 10px;

                    .content {
                        display: inline-flex;

                        .skyboxContainer, .terrainContainer {
                            display: flex;
                            flex-direction: column;
                        }
                    }
                }

                .frameRange {
                    padding: 5px;
                    width: 120px;
                    display: flex;
                    flex-direction: column;
                    margin-top: -18px;
                }
            }

            .colorPicker {
                position: relative;
                height: 24px;
                width: 84px;
                display: inline-block;
                margin: 0;
            }
        }

        .float-elem {
            position: absolute;
            z-index: 300;
        }
        .model3d__lib {
            top: 60px;
            right: 20px;
            max-width: 250px;
        }

        .cam_img {
            cursor: pointer;
            width: 32px;
        }

        .inp_stl {
            width: 55px;
            display: inline-block;
        }
        .rls_checker {
            border-top: 1px solid #ccc;
            margin-top: 5px;
            padding-top: 5px;
        }

        .ma_helper_table {
            width: 325px;
            background: white;
            border: 2px solid #ccc;
            border-collapse: collapse;

            th, td {
                font-size: 12px;
                line-height: 14px;
                padding: 0 3px;
                text-align: center;
                border: 1px solid #aaa;
                vertical-align: center;

                input, select {
                    padding: 0 3px;
                    font-size: 12px;
                    height: 24px;
                    text-align: center;
                }
            }

            .tb-btn {
                cursor: pointer;
                background: #CCC;
                height: 22px;
                font-size: 16px;
                font-weight: bold;
            }
        }
    }
</style>