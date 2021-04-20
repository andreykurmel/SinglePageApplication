<template>
    <div class="full-frame canvas_wrapper">
        <!--Filters-->
        <div class="float-settings left-bottom bottom--up canv__settings flex flex--col" v-show="EqptStatuses.filters_show">
            <label v-for="(filter, key) in EqptStatuses.colors"
                   v-if="filter.show"
                   class="status-filter"
                   :style="{backgroundColor: filter.model_val}"
            >
                <span class="indeterm_check__wrap">
                    <span class="indeterm_check" @click="filter.checked = !filter.checked;changedFilter()">
                        <i v-if="filter.checked" class="glyphicon glyphicon-ok group__icon"></i>
                    </span>
                </span>
                <span>{{key}}</span>
            </label>
        </div>
        <!--Filters-->
        <!--Settings-->
        <div class="float-settings left-bottom canv__settings flex flex--automargin" :style="{backgroundColor: params.background}">
            <canv-settings :settings="params" @redraw-signal="redrawAll" @changed-heights="reheightEqpts"></canv-settings>
            <div class="edges-setting-button" ref="grid_button">
                <i class="fas fa-border-all" @click="EqptStatuses.show_grids._visible = !EqptStatuses.show_grids._visible"></i>
                <div class="edges__popup" v-show="EqptStatuses.show_grids._visible">
                    <div>
                        <label>Hide grid lines</label>
                    </div>
                    <div>
                        <label>Positions:</label>
                        <span class="indeterm_check__wrap">
                            <span class="indeterm_check" @click="EqptStatuses.show_grids.pos = !EqptStatuses.show_grids.pos">
                                <i v-if="!EqptStatuses.show_grids.pos" class="glyphicon glyphicon-ok group__icon"></i>
                            </span>
                        </span>
                    </div>
                    <div>
                        <label>Sectors:</label>
                        <span class="indeterm_check__wrap">
                            <span class="indeterm_check" @click="EqptStatuses.show_grids.sec = !EqptStatuses.show_grids.sec">
                                <i v-if="!EqptStatuses.show_grids.sec" class="glyphicon glyphicon-ok group__icon"></i>
                            </span>
                        </span>
                    </div>
                    <div>
                        <label>Location:</label>
                        <span class="indeterm_check__wrap">
                            <span class="indeterm_check" @click="EqptStatuses.show_grids.rest = !EqptStatuses.show_grids.rest">
                                <i v-if="!EqptStatuses.show_grids.rest" class="glyphicon glyphicon-ok group__icon"></i>
                            </span>
                        </span>
                    </div>
                    <div>
                        <label>Boundaries:</label>
                        <span class="indeterm_check__wrap">
                            <span class="indeterm_check" @click="EqptStatuses.show_grids.bound = !EqptStatuses.show_grids.bound">
                                <i v-if="!EqptStatuses.show_grids.bound" class="glyphicon glyphicon-ok group__icon"></i>
                            </span>
                        </span>
                    </div>
                </div>
            </div>
            <div class="wid_camera">
                <img src="/assets/img/icons/camera.png" height="28" @click="make_screen()">
            </div>
            <div class="wid_filter">
                <img src="/assets/img/icons/filter.png" @click="EqptStatuses.filters_show = !EqptStatuses.filters_show" height="28" width="28">
            </div>
            <div class="wid_status_color">
                <img src="/assets/img/icons/status_color.png"
                     :style="{filter: EqptStatuses.view_enabled ? null : 'grayscale(1)'}"
                     @click="EqptStatuses.view_enabled = !EqptStatuses.view_enabled; applyCanvColors();"
                     height="28" width="28">
            </div>
            <div class="">
                <button class="btn btn-default full-height"
                        @click="recalcPosOfAllLines();"
                        style="padding: 0 2px;"
                >
                    <span :style="{color: params.is_eqpt_rev() ? '#000' : '#777', fontWeight: params.is_eqpt_rev() ? 'bold' : 'normal'}">F</span>
                    <span>/</span>
                    <span :style="{color: params.is_eqpt_rev() ? '#777' : '#000', fontWeight: params.is_eqpt_rev() ? 'normal' : 'bold'}">B</span>
                </button>
            </div>
            <div class="">
                <button class="btn btn-default full-height" @click="alignEqpts();" style="padding: 0 5px;">
                    <i class="fa fa-align-justify" aria-hidden="true"></i>
                </button>
            </div>
        </div>
        <!--Settings-->

        <!--CONFIGURATOR-->
        <div v-show="canDraw" class="full-frame" ref="wrap_canvas" @click.self="params.clearSel();sett_select_eqpt = null">
            <div v-if="canDraw && avail_canv" class="layout__wrap" ref="canvas_layout">
                <table class="canv__layout" :style="tbLayoutStyle()">
                    <colgroup>
                        <template v-for="sec in param_sectors">
                            <col v-for="pos in poss(sec)" :style="colWidths(sec, pos)"/>
                        </template>
                    </colgroup>
                    <tr>
                        <td v-for="sec in param_sectors"
                            :colspan="sec.pos_num"
                            :style="titleSty()"
                            @contextmenu.prevent="popupSector(sec._id)"
                        >
                            <div class="full-frame sector__titles">{{ sec.sector }}</div>
                        </td>
                    </tr>
                    <tr v-if="params.heVal('pos_he')">
                        <template v-for="(sec,s_i) in param_sectors">
                            <td v-for="(pos,p_i) in poss(sec)"
                                :style="posStyle(sec, pos, s_i, p_i)"
                                @dragover.prevent=""
                                @dragenter="params.over_sec_pos = sec._id+'_'+pos._id"
                                @dragend="params.over_sec_pos = null"
                                @drop="dragFinishedDrop(sec, pos, topLvL(1))"
                            >
                                <span v-if="(p_i+1) * (s_i+1) == total_cols" class="td__title td__title--out _no_png">
                                    <span v-if="!edit_show.top_elev" @click="showHeights('top_elev')">{{ params.top_elev }}</span>
                                    <input v-else=""
                                           class="form-control"
                                           ref="he_inp_top_elev"
                                           style="width: 75px;"
                                           :value="settings.top_elev"
                                           @blur="edit_show.top_elev = false"
                                           @change="(e) => { heightsChaged('top_elev',e) }"/>
                                </span>
                                <span v-if="(p_i+1) * (s_i+1) == total_cols" class="td__title td__title--vert td__title--out _no_png">
                                    <span v-if="!edit_show.he_pos" @click="showHeights('he_pos')">{{ params.heVal('pos_he') }}</span>
                                    <input v-else=""
                                           class="form-control"
                                           ref="he_inp_he_pos"
                                           style="width: 75px;"
                                           :value="settings.heVal('pos_he')"
                                           @blur="edit_show.he_pos = false"
                                           @change="(e) => { heightsChaged('pos_he',e) }"/>
                                </span>
                                <span v-if="pos && EqptStatuses.show_grids.pos"
                                      class="td__title td__title--center"
                                      @contextmenu.prevent="popupPos(pos._id)"
                                >{{ pos.name }}</span>

                                <canv-group
                                        :sector="sec"
                                        :pos="pos"
                                        :top_lvl="topLvL(1)"
                                        :group_he="params.heVal('pos_he')"
                                        :settings="params"
                                        :px_in_ft="px_in_ft"
                                        :data_eqpt="data_eqpt"
                                        @select-port="portSelect"
                                        @right-click="showSettSelect"
                                        @save-model="changeEqpt"
                                        @modelid-changed="modelidChaged"
                                        @empty-clicked="dragFinishedClick(sec, pos, topLvL(1))"
                                ></canv-group>
                            </td>
                        </template>
                    </tr>
                    <tr v-if="params.heVal('sector_he')">
                        <td v-for="(sec, s_i) in param_sectors"
                            :colspan="sec.pos_num"
                            :style="secStyle(sec, s_i)"
                            @dragover.prevent=""
                            @dragenter="params.over_sec_pos = sec._id+'_'"
                            @dragend="params.over_sec_pos = null"
                            @drop="dragFinishedDrop(sec, '', topLvL(2))"
                        >
                            <span v-if="s_i == param_sectors.length-1" class="td__title td__title--vert td__title--out _no_png">
                                <span v-if="!edit_show.he_sec" @click="showHeights('he_sec')">{{ params.heVal('sector_he') }}</span>
                                <input v-else=""
                                       class="form-control"
                                       ref="he_inp_he_sec"
                                       style="width: 75px;"
                                       :value="settings.heVal('sector_he')"
                                       @blur="edit_show.he_sec = false"
                                       @change="(e) => { heightsChaged('sector_he',e) }"/>
                            </span>
                            <span v-if="EqptStatuses.show_grids.sec"
                                  class="td__title"
                                  @contextmenu.prevent="popupSector(sec._id)"
                            >{{ sec.sector }}</span>

                            <canv-group
                                    :sector="sec"
                                    :top_lvl="topLvL(2)"
                                    :group_he="params.heVal('sector_he')"
                                    :settings="params"
                                    :px_in_ft="px_in_ft"
                                    :data_eqpt="data_eqpt"
                                    @select-port="portSelect"
                                    @right-click="showSettSelect"
                                    @save-model="changeEqpt"
                                    @modelid-changed="modelidChaged"
                                    @empty-clicked="dragFinishedClick(sec, '', topLvL(2))"
                            ></canv-group>
                        </td>
                    </tr>
                    <tr v-if="params.heVal('rest_he')">
                        <td :colspan="total_cols"
                            :style="restStyle()"
                            @dragover.prevent=""
                            @dragenter="params.over_sec_pos = '_'"
                            @dragend="params.over_sec_pos = null"
                            @drop="dragFinishedDrop('', '', topLvL(3))"
                        >
                            <span class="td__title td__title--vert td__title--out _no_png">
                                <span v-if="!edit_show.he_rest" @click="showHeights('he_rest')">{{ params.heVal('rest_he') }}</span>
                                <input v-else=""
                                       class="form-control"
                                       ref="he_inp_he_rest"
                                       style="width: 75px;"
                                       :value="settings.heVal('rest_he')"
                                       @blur="edit_show.he_rest = false"
                                       @change="(e) => { heightsChaged('rest_he',e) }"/>
                            </span>
                            <span v-if="EqptStatuses.show_grids.sec" class="td__title">{{ params._shared_sectors_arr.join(',') }}</span>
                            <span v-if="EqptStatuses.show_grids.rest" class="td__title td__title--btm">
                                <span v-if="!air_edit_show" @click="showAirEdit('air_edit_show')">{{ params._air_name }}</span>
                                <input v-else=""
                                       class="form-control"
                                       ref="inp_air_edit_show"
                                       style="width: 75px;"
                                       :value="settings._air_name"
                                       @blur="air_edit_show = false"
                                       @change="(e) => { changeAirNames('_air_name',e) }"/>
                            </span>

                            <canv-group
                                    :shared_sec_pos="params._shared_sectors_arr"
                                    :top_lvl="topLvL(3)"
                                    :group_he="params.heVal('rest_he')"
                                    :settings="params"
                                    :px_in_ft="px_in_ft"
                                    :data_eqpt="data_eqpt"
                                    @select-port="portSelect"
                                    @right-click="showSettSelect"
                                    @save-model="changeEqpt"
                                    @modelid-changed="modelidChaged"
                                    @empty-clicked="dragFinishedClick('', '', topLvL(3))"
                            ></canv-group>
                        </td>
                    </tr>
                    <tr v-if="params.heVal('bot_he')">
                        <td :colspan="total_cols"
                            :style="botStyle()"
                            @dragover.prevent=""
                            @dragenter="params.over_sec_pos = '+'"
                            @dragend="params.over_sec_pos = null"
                            @drop="dragFinishedDrop('', '', (params.drag_line ? topLvL(4) : topLvL(0)))"
                        >
                            <span class="td__title td__title--vert td__title--out _no_png">
                                <span v-if="!edit_show.he_bot" @click="showHeights('he_bot')">{{ params.heVal('bot_he') }}</span>
                                <input v-else=""
                                       class="form-control"
                                       ref="he_inp_he_bot"
                                       style="width: 75px;"
                                       :value="settings.heVal('bot_he')"
                                       @blur="edit_show.he_bot = false"
                                       @change="(e) => { heightsChaged('bot_he',e) }"/>
                            </span>
                            <span v-if="EqptStatuses.show_grids.rest" class="td__title">
                                <span v-if="!base_edit_show" @click="showAirEdit('base_edit_show')">{{ params._base_name }}</span>
                                <input v-else=""
                                       class="form-control"
                                       ref="inp_base_edit_show"
                                       style="width: 75px;"
                                       :value="settings._base_name"
                                       @blur="base_edit_show = false"
                                       @change="(e) => { changeAirNames('_base_name',e) }"/>
                            </span>
                            <span class="td__title td__title--btm">{{ params.is_eqpt_rev() ? 'Front View' : 'Back View' }}</span>

                            <canv-group
                                    :shared_sec_pos="all_sectors"
                                    :top_lvl="topLvL(0)"
                                    :group_he="params.heVal('bot_he')"
                                    :settings="params"
                                    :px_in_ft="px_in_ft"
                                    :data_eqpt="data_eqpt"
                                    @select-port="portSelect"
                                    @right-click="showSettSelect"
                                    @save-model="changeEqpt"
                                    @modelid-changed="modelidChaged"
                                    @empty-clicked="dragFinishedClick('', '', (params.drag_line ? topLvL(4) : topLvL(0)))"
                            ></canv-group>
                        </td>
                    </tr>
                </table>
                <!--<template v-for="line in data_conn" v-if="params.elev_by === 'pd'">-->
                <template v-for="line in data_conn">
                    <canv-group-line
                            v-if="!line._hidden && !line._eqpt_hidden"
                            :line="line"
                            :all_lines="data_conn"
                            :settings="params"
                            :px_in_ft="px_in_ft"
                            @point-drag-start="pointDragStart"
                            @start-drag="lineDragStart"
                            @conn-popup="popupConn"
                            @save-model="changeLine"
                    ></canv-group-line>
                </template>
            </div>
            <div v-if="!avail_canv" class="full-height flex flex--center" style="font-size: 4rem">Need to have Top Level >Bot Level in Settings for proper illustration.</div>
        </div>
        <!--CONFIGURATOR-->

        <!--Menu-->
        <canv-lib-menu
                :settings="params"
                :eqpt_lib="eqpt_lib"
                :line_lib="line_lib"
                :tech_list="tech_list"
                :status_list="status_list"
                :px_in_ft="px_in_ft"
                :ex_height="'calc(100% - 45px)'"
                class="float-settings right-top lib__menu"
                @lib-eqpt-add="eqptAddStart"
                @lib-line-add="lineAddStart"
                @popup-elem="popupLibElem"
                @open-add-popup="openAddPopup"
        ></canv-lib-menu>
        <!--Menu-->
        <!--Zoom-->
        <div class="float-settings right-bottom zoom__btns flex flex--center">
            <button class="btn btn-default blue-gradient" :style="$root.themeButtonStyle" @click="loopOut()">-</button>
            <input class="form-control zoom__inp" :value="scale+'%'" @change="inpScale"/>
            <div class="slide__wrap">
                <slider-block :min="100" :max="300" :cur="scale" @slide="slideScale"></slider-block>
            </div>
            <button class="btn btn-default blue-gradient" :style="$root.themeButtonStyle" @click="loopIn()">+</button>
        </div>
        <!--Zoom-->

        <!--eqpt sett menu-->
        <div v-if="sett_select_eqpt" class="float-settings" :style="settStyle()">
            <button class="btn btn-default blue-gradient"
                    style="padding: 0 3px;"
                    :style="$root.themeButtonStyle"
                    @click="popupModel(sett_select_eqpt._model_id); sett_select_eqpt = null;"
            >Source</button>
            <br>
            <button class="btn btn-default blue-gradient"
                    style="padding: 0 3px;"
                    :style="$root.themeButtonStyle"
                    @click="popupEqptLib(sett_select_eqpt._eqptlib_id); sett_select_eqpt = null;"
            >EqptLib</button>
            <br>
            <button class="btn btn-default blue-gradient"
                    style="padding: 0 3px;"
                    :style="$root.themeButtonStyle"
                    @click="popupEqpt(sett_select_eqpt._id); sett_select_eqpt = null;"
            >Equipment</button>
            <br>
            <button class="btn btn-default blue-gradient"
                    style="padding: 0 3px;"
                    :style="$root.themeButtonStyle"
                    @click="popupEqptSett(sett_select_eqpt._id); sett_select_eqpt = null;"
            >GUI Settings</button>
        </div>
        <!--eqpt sett menu-->


        <!--Change Present Rows-->
        <tablda-direct-popup
                v-if="popup_row_id && vuex_fm[popup_app_tb]"
                :meta-table="vuex_fm[popup_app_tb].meta"
                :stim_link_params="vuex_links[popup_app_tb]"
                :row-id="popup_row_id"
                @close-popup="popup_row_id = null"
                @pre-insert="preInsert"
                @pre-update="preUpdate"
                @pre-delete="preDelete"
                @row-inserted="directInsert"
                @row-updated="directUpdate"
                @row-deleted="directDelete"
                @another-row="anotherDirectRow"
        ></tablda-direct-popup>


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
                :available-columns="stim_link_add.avail_columns_for_app"
                @popup-insert="addPopupInsert"
                @popup-close="closeAddPopUp"
        ></custom-edit-pop-up>
    </div>
</template>

<script>
    import { eventBus } from './../../../app';

    import {Settings} from "./Settings";
    import {MetaTabldaRows} from '../../../classes/MetaTabldaRows';

    import { mapState } from 'vuex';

    import DirectPopMixin from "./DirectPopMixin.vue";
    import GlobalPosMixin from "./GlobalPosMixin.vue";
    import ConfiguratorDataDragPopMixin from "./ConfiguratorDataDragPopMixin.vue";

    import CanvSettings from "./CanvSettings";
    import CanvLibMenu from "./CanvLibMenu";
    import TabldaDirectPopup from "../MainData/TabldaDirectPopup";
    import CanvGroup from "./CanvGroup";
    import CanvGroupLine from "./CanvGroupLine";
    import CustomEditPopUp from "../../../components/CustomPopup/CustomEditPopUp";
    import SliderBlock from "../../../components/CommonBlocks/SliderBlock";

    export default {
        name: 'ConfiguratorComponent',
        mixins: [
            GlobalPosMixin,
            DirectPopMixin,
            ConfiguratorDataDragPopMixin,
        ],
        components: {
            SliderBlock,
            CustomEditPopUp,
            CanvGroupLine,
            CanvGroup,
            CanvLibMenu,
            CanvSettings,
            TabldaDirectPopup,
        },
        data() {
            return {
                canDraw: true,

                //names
                air_edit_show: false,
                base_edit_show: false,
                //heights
                edit_show: {
                    top_elev: false,
                    he_pos: false,
                    he_sec: false,
                    he_rest: false,
                    he_bot: false,
                },

                sett_select_eqpt: null,
                sett_top: null,
                sett_left: null,

                //layout
                params: new Settings(),
                scale: 100,
                init_wrap_x: 1,
                init_wrap_y: 1,
                wrap_x: 1,
                wrap_y: 1,
                total_cols: 1,
                loc: {
                    top: '',
                    bot: '',
                },

                //data
                eqpt_lib: [],
                line_lib: [],
                data_eqpt: [],
                data_conn: [],
                data_conn_grouped: [],
                all_sectors: [],
                tech_list: [],
                status_list: [],
                popup_tables: {},

                //colors
                EqptStatuses: {
                    show_grids: {
                        _visible: false,
                        pos: true,
                        sec: true,
                        rest: true,
                        bound: true,
                    },
                    filters_show: false,
                    view_enabled: true,
                    colors: {
                        "status": {
                            model_val: '#ffffff',
                            key: 'status',
                            checked: true,
                            show: true,
                        },
                    },
                },
            }
        },
        computed: {
            ...mapState({
                vuex_settings: state => state.stim_settings,
                vuex_links: state => state.stim_links_container,
                vuex_fm: state => state.found_models,
            }),
            px_in_ft() {
                let divide = to_float(this.params.heVal('pos_he')) + to_float(this.params.heVal('sector_he'))
                    + to_float(this.params.heVal('rest_he')) + to_float(this.params.heVal('bot_he'));
                return this.wrap_y / (divide || 1);
            },
            param_sectors() {
                return this.params.is_full_rev()
                    ? this.params._sectors.slice().reverse()
                    : this.params._sectors;
            },
            //For GlobalPosMixin ->
            settings() {
                return this.params;
            },
            line_diameter() {
                return 0;
            },
            //^^^^^^^
            avail_canv() {
                return this.params.top_elev > 0
                    && this.params.top_elev > this.params.bot_elev;
            },
        },
        props: {
            master_row: Object,
            master_table: String,
            is_showed: Boolean,
            selected_html: String,
        },
        watch: {
            is_showed: {
                handler(val) {
                    (val ? this.load2D() : null);
                },
                immediate: true,
            }
        },
        methods: {
            //heights edits
            showHeights(key) {
                this.edit_show[key] = !this.edit_show[key];
                this.$nextTick(() => {
                    let inp = 'he_inp_'+key;
                    let div = _.first(this.$refs[inp]) || this.$refs[inp];
                    if (div && div.focus) {
                        div.focus();
                    }
                });
            },
            heightsChaged(key, e) {
                let affected = '';
                switch (key) {
                    case 'top_elev': affected = 'pos,sec,rest'; break;
                    case 'pos_he': affected = 'sec,rest'; key = this.settings.heVal(key, true); break;
                    case 'sector_he': affected = 'rest'; key = this.settings.heVal(key, true); break;
                    case 'rest_he': affected = ''; key = this.settings.heVal(key, true); break;
                    case 'bot_he': affected = ''; key = this.settings.heVal(key, true); break;
                    case 'bot_elev': affected = 'bot'; break;
                }
                let diff = to_float(this.settings[key]) - to_float(e.target.value);
                this.settings[key] = to_float(e.target.value);
                this.settings.saveSettings();
                this.reheightEqpts(affected, diff);
            },
            //air base names
            showAirEdit(key) {
                this[key] = !this[key];
                this.$nextTick(() => {
                    let inp = 'inp_'+key;
                    if (this.$refs[inp]) {
                        this.$refs[inp].focus();
                    }
                });
            },
            changeAirNames(key, e) {
                this.params.setAirBaseNames(key, e.target.value);
                //this.locationaEqptChange();
                this.params.saveSettings();
            },

            //convert indexes
            poss(sec) {
                let p_idx = sec.pos_num || 1;
                return this.params.is_eqpt_rev()
                    ? this.params._pos.slice(0,p_idx).reverse()
                    : this.params._pos.slice(0,p_idx);
            },
            //connect
            portSelect(eqpt_id, pos, idx) {
                if (this.params.sel_line) {
                    if (this.params.sel_eqpt_id) {
                        let app_tb = this.popup_tables.linedata_2d;
                        this.params.sel_line.from_eqpt_id = this.params.sel_eqpt_id;
                        this.params.sel_line.from_port_pos = this.params.sel_port_pos;
                        this.params.sel_line.from_port_idx = this.params.sel_port_idx;
                        this.params.sel_line.to_eqpt_id = eqpt_id;
                        this.params.sel_line.to_port_pos = pos;
                        this.params.sel_line.to_port_idx = idx;
                        this.params.sel_line.findEqpts(this.data_eqpt);
                        //add Line via ports select
                        if (this.params.sel_line._is_lib) {
                            this.params.add_new = true;
                            this.setLineBetweenEqpt(this.params.sel_line);
                        }

                        this.callSaveModel('sel_line', app_tb, 'line', null, 'line');
                    } else {
                        this.params.selFirstPort(eqpt_id, pos, idx);
                    }
                }
            },

            //draw layout
            colWidths(sec, pos) {
                let ws = String(sec.pos_widths).split(',');
                return {
                    width: Math.round(this.px_in_ft * ws[pos._idx])+'px',
                }
            },
            tbLayoutStyle() {
                return {
                    width: 'fit-content',
                    height: this.wrap_y+'px',
                    borderLeft: this.EqptStatuses.show_grids.bound ? '1px solid #AAE' : 'none',
                    borderTop: this.EqptStatuses.show_grids.bound ? '1px solid #AAE' : 'none',
                    backgroundColor: this.params.background,
                };
            },
            botStyle() {
                return {
                    height: (this.px_in_ft * this.params.heVal('bot_he'))+'px',
                    borderRight: this.EqptStatuses.show_grids.bound ? '1px solid #AAE' : 'none',
                    borderBottom: this.EqptStatuses.show_grids.bound ? '1px solid #AAE' : 'none',
                    backgroundColor: this.params.over_sec_pos === '+' ? '#EEE' : null,
                }
            },
            restStyle() {
                return {
                    height: (this.px_in_ft * this.params.heVal('rest_he'))+'px',
                    borderRight: this.EqptStatuses.show_grids.bound ? '1px solid #AAE' : 'none',
                    borderBottom: this.EqptStatuses.show_grids.rest ? '1px solid #AAE' : 'none',
                    backgroundColor: this.params.over_sec_pos === '_' ? '#EEE' : null,
                }
            },
            secStyle(sec, s_i) {
                let last = s_i === this.param_sectors.length-1;
                last = this.EqptStatuses.show_grids.bound ? last : false;
                return {
                    height: (this.px_in_ft * this.params.heVal('sector_he'))+'px',
                    borderRight: last || this.EqptStatuses.show_grids.sec ? '1px solid #AAE' : 'none',
                    borderBottom: this.EqptStatuses.show_grids.sec ? '1px solid #AAE' : 'none',
                    backgroundColor: this.params.over_sec_pos === sec._id+'_' ? '#EEE' : null,
                }
            },
            posStyle(sec, pos, s_i, p_i) {
                let last = p_i === this.poss(sec).length-1;
                last = last && (this.EqptStatuses.show_grids.sec || s_i === this.param_sectors.length-1);
                last = this.EqptStatuses.show_grids.bound ? last : false;
                return {
                    height: (this.px_in_ft * this.params.heVal('pos_he'))+'px',
                    borderRight: last || this.EqptStatuses.show_grids.pos ? '1px solid #AAE' : 'none',
                    borderBottom: this.EqptStatuses.show_grids.sec ? '1px solid #AAE' : 'none',
                    backgroundColor: this.params.over_sec_pos === sec._id+'_'+pos._id ? '#EEE' : null,
                }
            },
            titleSty() {
                return {
                    borderRight: this.EqptStatuses.show_grids.bound ? '1px solid #AAE' : 'none',
                    borderBottom: this.EqptStatuses.show_grids.bound ? '1px solid #AAE' : 'none',
                    height: this.params.get_glob_top()+'px'
                };
            },

            //align
            reheightEqpts(affected, diff) {
               let app_tb = this.popup_tables.eqptdata_2d;
               let affected_eqpts = this.params.filter_eqpts(this.data_eqpt, affected);
               _.each(affected_eqpts, (eqpt) => {
                   let old = eqpt.elevVal(this.params.elev_by);
                   eqpt.elevVal(this.params.elev_by, old + diff);
                   eqpt.quickSave(app_tb);
               });
               this.redrawAll();
            },
            alignEqpts() {
                let app_tb = this.popup_tables.eqptdata_2d;
                _.each(this.data_eqpt, (eqpt) => {
                    let sector = _.find(this.params._sectors, {sector: eqpt.sector});
                    let pos_idx = _.findIndex(this.params._pos, {name: eqpt.pos});
                    if (sector && pos_idx > -1) {
                        let ws = String(sector.pos_widths).split(',');
                        let s_wi = to_float(ws[pos_idx]);
                        if (s_wi) {
                            let os = Math.ceil( ((eqpt.pos_left + (eqpt.calc_dx / 2)) / s_wi) * 3 );
                            eqpt.pos_left = (s_wi * os/4) - (eqpt.calc_dx / 2);
                            eqpt.quickSave(app_tb);
                        }
                    }
                });
                this.redrawAll();
            },
            topLvL(lvl) {
                switch (lvl) {
                    case 1: return this.params.top_elev;
                    case 2: return this.params.top_elev - this.params.heVal('pos_he');
                    case 3: return this.params.top_elev - this.params.heVal('pos_he') - this.params.heVal('sector_he');
                    case 4: return this.params.top_elev - this.params.heVal('pos_he') - this.params.heVal('sector_he') - this.params.heVal('rest_he');
                    default: return 0;
                }
            },
            /*locationaEqptChange() {
                let app_tb = this.popup_tables.eqptdata_2d;
                _.each(this.data_eqpt, (eqpt) => {
                    eqpt.location = eqpt.is_top() ? this.params._air_name : this.params._base_name;
                    eqpt._air_name = this.params._air_name;
                    eqpt.quickSave(app_tb);
                });
                this.redrawAll();
                this.reloadTablda(app_tb);
            },*/

            //scale
            loopIn() {
                this.scale = Math.min(this.scale+10, 300);
                this.setWrapSize();
            },
            inpScale(e) {
                let val = to_float(e.target.value) || 100;
                val = Math.min(val, 300);
                val = Math.max(val, 100);
                this.scale = val;
                this.setWrapSize();
            },
            slideScale(val) {
                this.scale = val;
                this.setWrapSize();
            },
            loopOut() {
                this.scale = Math.max(this.scale-10, 100);
                this.setWrapSize();
            },
            setWrapSize() {
                this.wrap_x = this.init_wrap_x * (this.scale/100);
                this.wrap_y = this.init_wrap_y * (this.scale/100);
            },

            //settings panel
            make_screen() {
                let str = this.selected_html+'.png';
                export_to_png(this.$refs.canvas_layout, str || 'configurator');
            },

            //keys
            pressedKey(e) {
                if (e.keyCode === 46) { //delete key

                    let k_control = this.params.getLineControlKey();
                    let k_obj = this.params.getLineObjKey();

                    if (this.params.sel_line && this.params.sel_control_point) {
                        let rem_idx = this.params.sel_line
                            ? _.findIndex(this.params.sel_line[k_obj], {ord: this.params.sel_control_point})
                            : -1;
                        if (rem_idx > -1) {
                            //remove control point
                            this.params.sel_line[k_obj].splice(rem_idx, 1);
                            this.params.sel_line[k_control] = JSON.stringify( this.params.sel_line[k_obj] );
                            this.callSaveModel('sel_line', this.popup_tables.linedata_2d, 'line');
                        } else {
                            //remove line
                            //redraw immediately
                            this.data_conn.splice( _.findIndex(this.data_conn, {_id: this.params.sel_line._id}), 1 );
                            this.redrawAll();
                            //save on back-end
                            let popup_app_tb = this.popup_tables.linedata_2d;
                            let metaRows = new MetaTabldaRows(this.vuex_links[popup_app_tb], this.$root.app_stim_uh);
                            metaRows.deleteRow({id: this.params.sel_line._id}, true).then(() => {
                                this.reloadFull(popup_app_tb);
                            });
                        }
                    }
                    if (this.params.mass_eqpt.length) {
                        //remove eqpt
                        let remove_ids = _.map(this.params.mass_eqpt, '_id');
                        this.data_eqpt = _.filter(this.data_eqpt, (eqpt) => {
                            return !in_array(eqpt._id, remove_ids);
                        });
                        //remove connected lines
                        let affect_lines = _.filter(this.data_conn, (line) => {
                            return in_array(line.from_eqpt_id, remove_ids) || in_array(line.to_eqpt_id, remove_ids);
                        });
                        let rem_line_ids = _.map( affect_lines, '_id');
                        this.data_conn = _.filter(this.data_conn, (line) => {
                            return !in_array(line._id, rem_line_ids);
                        });
                        //redraw immediately
                        this.redrawAll();
                        //save on back-end
                        let eqpt_app_tb = this.popup_tables.eqptdata_2d;
                        let eqptRows = new MetaTabldaRows(this.vuex_links[eqpt_app_tb], this.$root.app_stim_uh);
                        let line_app_tb = this.popup_tables.linedata_2d;
                        let lineRows = new MetaTabldaRows(this.vuex_links[line_app_tb], this.$root.app_stim_uh);
                        Promise.all([
                            eqptRows.deleteSelected(remove_ids, true),
                            lineRows.deleteSelected(rem_line_ids, true),
                        ]).then(() => {
                            this.reloadTablda(eqpt_app_tb);
                            this.reloadTablda(line_app_tb);
                            this.load2D();
                        });
                    }

                }
            },

            //menu
            hideMenu(e) {
                let container = $(this.$refs.grid_button);
                if (this.EqptStatuses.show_grids._visible && container.has(e.target).length === 0){
                    this.EqptStatuses.show_grids._visible = false;
                }
            },
        },
        mounted() {
            eventBus.$on('global-keydown', this.pressedKey);
            eventBus.$on('global-click', this.hideMenu);
        },
        beforeDestroy() {
            eventBus.$off('global-keydown', this.pressedKey);
            eventBus.$off('global-click', this.hideMenu);
        }
    }
</script>

<style lang="scss" scoped="">
    .canvas_wrapper {
        border: 1px solid #777;

        /* Layout of Settings */
        .float-settings {
            position: absolute;
            z-index: 900;
        }
        .left-top {
            left: 5px;
            top: 5px;
        }
        .left-bottom {
            left: 5px;
            bottom: 5px;
        }
        .right-bottom {
            right: 5px;
            bottom: 5px;
        }
        .right-top {
            top: 5px;
            right: 5px;
        }
        .bottom--up {
            bottom: 45px;
        }

        /* Settins */
        .canv__settings {
            height: auto;
            padding: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;

            label {
                margin: 0;
            }
        }
        .zoom__btns {
            button {
                height: 30px;
                width: 35px;
                padding: 0;
                font-size: 22px;
                line-height: 30px;
            }

            .zoom__inp {
                width: 50px;
                padding: 0 3px;
                height: 24px;
                margin-left: 10px;
            }

            .slide__wrap {
                width: 150px;
                margin: 0 15px;
            }
        }
        .lib__menu {
            max-width: 250px;
        }

        /* Data and Layout */
        .layout__wrap {
            width: fit-content;
            position: relative;

            .canv__layout {
                border-collapse: separate;
                table-layout: fixed;

                td {
                    position: relative;

                    .td__title {
                        color: #CCC;
                        position: absolute;
                        right: 5px;
                        top: 0;
                        cursor: pointer;
                        z-index: 500;
                    }
                    .td__title--left {
                        left: 5px;
                        right: auto;
                    }
                    .td__title--btm {
                        top: auto;
                        bottom: 0;
                    }
                    .td__title--center {
                        right: auto;
                        left: 50%;
                        transform: translateX(-50%);
                    }
                    .td__title--vert {
                        top: 50%;
                        transform: translateY(-50%);
                    }
                    .td__title--out {
                        right: auto;
                        left: calc(100% + 5px);
                    }
                }
                .sector__titles {
                    text-align: center;
                    font-weight: bold;
                    overflow: hidden;
                }
            }
        }


        /* Others */
        .edges-setting-button {
            box-shadow: 0 3px 10px 0 rgba(0, 0, 0, 0.2);
            background: #00b0ff;
            border-radius: 2px;
            font-size: 22px;
            font-weight: bold;
            cursor: pointer;
            color: #ffffff;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;

            .edges__popup {
                position: absolute;
                left: 0;
                bottom: 100%;
                background-color: #FFF;
                color: #000;
                font-size: 13px;
                white-space: nowrap;
                padding: 0 3px;
                border: 1px solid #CCC;
            }
        }

        .fa-align-justify {
            transform: rotate(90deg);
        }
    }
</style>