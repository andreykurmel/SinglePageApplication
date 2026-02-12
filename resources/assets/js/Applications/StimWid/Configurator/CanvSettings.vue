<template>
    <div ref="settings_button" class="settings_wrapper">
        <button class="btn btn-default blue-gradient flex flex--center settings_btn"
                :style="$root.themeButtonStyle"
                @click="menu_opened = !menu_opened"
        >
            <i class="fas fa-cog"></i>
        </button>

        <div v-show="menu_opened" class="settings_menu">
            <table class="table">
                <tr>
                    <td colspan="2">
                        <table class="table-inner">
                            <tr>
                                <th></th>
                                <th>Show</th>
                                <th>Font</th>
                                <th>Size</th>
                                <th>Color</th>
                            </tr>

                            <tr>
                                <td><b>Eqpt/AZMs</b></td>
                                <td class="td--center">
                                    <span class="indeterm_check__wrap">
                                        <span class="indeterm_check" @click="settings.show_eqpt_azimuths = !settings.show_eqpt_azimuths;saveOnBknd()">
                                            <i v-if="settings.show_eqpt_azimuths" class="glyphicon glyphicon-ok group__icon"></i>
                                        </span>
                                    </span>
                                </td>
                                <td>
                                    <select class="form-control"
                                            :disabled="!settings.show_eqpt_azimuths"
                                            v-model="settings.show_eqpt_azimuth__font"
                                            @change="saveOnBknd()"
                                    >
                                        <option>Cursive</option>
                                        <option>Monospace</option>
                                        <option>Serif</option>
                                        <option>Sans-Serif</option>
                                    </select>
                                </td>
                                <td class="td--center">
                                    <input type="number"
                                           class="form-control"
                                           :disabled="!settings.show_eqpt_azimuths"
                                           v-model="settings.show_eqpt_azimuth__size"
                                           @change="saveOnBknd()"/>
                                </td>
                                <td>
                                    <tablda-colopicker
                                            :init_color="settings.show_eqpt_azimuth__color"
                                            :can_edit="settings.show_eqpt_azimuths"
                                            :avail_null="true"
                                            @set-color="(clr) => {settings.show_eqpt_azimuth__color = clr; saveOnBknd()}"
                                    ></tablda-colopicker>
                                </td>
                            </tr>

                            <tr>
                                <td><b>Eqpt/ID</b></td>
                                <td class="td--center">
                                    <span class="indeterm_check__wrap">
                                        <span class="indeterm_check" @click="settings.show_eqpt_id = !settings.show_eqpt_id;saveOnBknd()">
                                            <i v-if="settings.show_eqpt_id" class="glyphicon glyphicon-ok group__icon"></i>
                                        </span>
                                    </span>
                                </td>
                                <td>
                                    <select class="form-control"
                                            :disabled="!settings.show_eqpt_id"
                                            v-model="settings.show_eqpt_id__font"
                                            @change="saveOnBknd()"
                                    >
                                        <option>Cursive</option>
                                        <option>Monospace</option>
                                        <option>Serif</option>
                                        <option>Sans-Serif</option>
                                    </select>
                                </td>
                                <td class="td--center">
                                    <input type="number"
                                           class="form-control"
                                           :disabled="!settings.show_eqpt_id"
                                           v-model="settings.show_eqpt_id__size"
                                           @change="saveOnBknd()"/>
                                </td>
                                <td>
                                    <tablda-colopicker
                                            :init_color="settings.show_eqpt_id__color"
                                            :can_edit="settings.show_eqpt_id"
                                            :avail_null="true"
                                            @set-color="(clr) => {settings.show_eqpt_id__color = clr; saveOnBknd()}"
                                    ></tablda-colopicker>
                                </td>
                            </tr>

                            <tr>
                                <td><b>Eqpt/DIMs</b></td>
                                <td class="td--center">
                                    <span class="indeterm_check__wrap">
                                        <span class="indeterm_check" @click="settings.show_eqpt_size = !settings.show_eqpt_size;saveOnBknd()">
                                            <i v-if="settings.show_eqpt_size" class="glyphicon glyphicon-ok group__icon"></i>
                                        </span>
                                    </span>
                                </td>
                                <td>
                                    <select class="form-control"
                                            :disabled="!settings.show_eqpt_size"
                                            v-model="settings.show_eqpt_size__font"
                                            @change="saveOnBknd()"
                                    >
                                        <option>Cursive</option>
                                        <option>Monospace</option>
                                        <option>Serif</option>
                                        <option>Sans-Serif</option>
                                    </select>
                                </td>
                                <td class="td--center">
                                    <input type="number"
                                           class="form-control"
                                           :disabled="!settings.show_eqpt_size"
                                           v-model="settings.show_eqpt_size__size"
                                           @change="saveOnBknd()"/>
                                </td>
                                <td>
                                    <tablda-colopicker
                                            :init_color="settings.show_eqpt_size__color"
                                            :can_edit="settings.show_eqpt_size"
                                            :avail_null="true"
                                            @set-color="(clr) => {settings.show_eqpt_size__color = clr; saveOnBknd()}"
                                    ></tablda-colopicker>
                                </td>
                            </tr>
                            <tr>
                                <td><b>Eqpt/Model</b></td>
                                <td class="td--center">
                                    <span class="indeterm_check__wrap">
                                        <span class="indeterm_check" @click="settings.show_eqpt_model = !settings.show_eqpt_model;saveOnBknd()">
                                            <i v-if="settings.show_eqpt_model" class="glyphicon glyphicon-ok group__icon"></i>
                                        </span>
                                    </span>
                                </td>
                                <td>
                                    <select class="form-control"
                                            :disabled="!settings.show_eqpt_model"
                                            v-model="settings.show_eqpt_model__font"
                                            @change="saveOnBknd()"
                                    >
                                        <option>Cursive</option>
                                        <option>Monospace</option>
                                        <option>Serif</option>
                                        <option>Sans-Serif</option>
                                    </select>
                                </td>
                                <td class="td--center">
                                    <input type="number"
                                           class="form-control"
                                           :disabled="!settings.show_eqpt_model"
                                           v-model="settings.show_eqpt_model__size"
                                           @change="saveOnBknd()"/>
                                </td>
                                <td>
                                    <tablda-colopicker
                                            :init_color="settings.show_eqpt_model__color"
                                            :can_edit="settings.show_eqpt_model"
                                            :avail_null="true"
                                            @set-color="(clr) => {settings.show_eqpt_model__color = clr; saveOnBknd()}"
                                    ></tablda-colopicker>
                                </td>
                            </tr>

                            <tr>
                                <td><b>Eqpt/Tech</b></td>
                                <td class="td--center">
                                    <span class="indeterm_check__wrap">
                                        <span class="indeterm_check" @click="settings.show_eqpt_tech = !settings.show_eqpt_tech;saveOnBknd()">
                                            <i v-if="settings.show_eqpt_tech" class="glyphicon glyphicon-ok group__icon"></i>
                                        </span>
                                    </span>
                                </td>
                                <td>
                                    <select class="form-control"
                                            :disabled="!settings.show_eqpt_tech"
                                            v-model="settings.show_eqpt_tech__font"
                                            @change="saveOnBknd()"
                                    >
                                        <option>Cursive</option>
                                        <option>Monospace</option>
                                        <option>Serif</option>
                                        <option>Sans-Serif</option>
                                    </select>
                                </td>
                                <td class="td--center">
                                    <input type="number"
                                           class="form-control"
                                           :disabled="!settings.show_eqpt_tech"
                                           v-model="settings.show_eqpt_tech__size"
                                           @change="saveOnBknd()"/>
                                </td>
                                <td>
                                    <tablda-colopicker
                                            :init_color="settings.show_eqpt_tech__color"
                                            :can_edit="settings.show_eqpt_tech"
                                            :avail_null="true"
                                            @set-color="(clr) => {settings.show_eqpt_tech__color = clr; saveOnBknd()}"
                                    ></tablda-colopicker>
                                </td>
                            </tr>

                            <tr>
                                <td><b>Line/Model</b></td>
                                <td class="td--center">
                                    <span class="indeterm_check__wrap">
                                        <span class="indeterm_check" @click="settings.show_line_model = !settings.show_line_model;saveOnBknd()">
                                            <i v-if="settings.show_line_model" class="glyphicon glyphicon-ok group__icon"></i>
                                        </span>
                                    </span>
                                </td>
                                <td>
                                    <select class="form-control"
                                            :disabled="!settings.show_line_model"
                                            v-model="settings.show_line_model__font"
                                            @change="saveOnBknd()"
                                    >
                                        <option>Cursive</option>
                                        <option>Monospace</option>
                                        <option>Serif</option>
                                        <option>Sans-Serif</option>
                                    </select>
                                </td>
                                <td class="td--center">
                                    <input type="number"
                                           class="form-control"
                                           :disabled="!settings.show_line_model"
                                           v-model="settings.show_line_model__size"
                                           @change="saveOnBknd()"/>
                                </td>
                                <td>
                                    <tablda-colopicker
                                            :init_color="settings.show_line_model__color"
                                            :can_edit="settings.show_line_model"
                                            :avail_null="true"
                                            @set-color="(clr) => {settings.show_line_model__color = clr; saveOnBknd()}"
                                    ></tablda-colopicker>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td><label>Background:</label></td>
                    <td>
                        <div class="form-control color_wrap">
                            <tablda-colopicker
                                    :init_color="settings.background"
                                    :fixed_pos="true"
                                    :avail_null="true"
                                    @set-color="colorChanger"
                            ></tablda-colopicker>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td><label>Top Elev:</label></td>
                    <td>
                        <div class="flex flex--center">
                            <input class="form-control"
                                   type="number"
                                   :value="settings.top_elev"
                                   @change="(e) => { setHeights('top_elev',e) }"/>
                            <span>(ft)</span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td><label>Bot Elev:</label></td>
                    <td>
                        <div class="flex flex--center">
                            <input class="form-control"
                                   type="number"
                                   :value="settings.bot_elev"
                                   @change="(e) => { setHeights('bot_elev',e) }"/>
                            <span>(ft)</span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td><label>Air Name:</label></td>
                    <td>
                        <div class="flex flex--center">
                            <input class="form-control"
                                   :value="settings._air_name"
                                   @change="(e) => { air_base_nameChanger('_air_name',e) }"/>
                            <span>&nbsp;&nbsp;&nbsp;&nbsp;</span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td><label>Base Name:</label></td>
                    <td>
                        <div class="flex flex--center">
                            <input class="form-control"
                                   :value="settings._base_name"
                                   @change="(e) => { air_base_nameChanger('_base_name',e) }"/>
                            <span>&nbsp;&nbsp;&nbsp;&nbsp;</span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td><label>Heights</label></td>
                    <td></td>
                </tr>
                <tr>
                    <td><label class="m--l">Top/Pos:</label></td>
                    <td>
                        <div class="flex flex--center">
                            <input class="form-control"
                                   type="number"
                                   :value="settings.heVal('pos_he')"
                                   @change="(e) => { setHeights('pos_he',e) }"/>
                            <span>(ft)</span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td><label class="m--l">Top/Sector:</label></td>
                    <td>
                        <div class="flex flex--center">
                            <input class="form-control"
                                   type="number"
                                   :value="settings.heVal('sector_he')"
                                   @change="(e) => { setHeights('sector_he',e) }"/>
                            <span>(ft)</span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td><label class="m--l">Top/Shared:</label></td>
                    <td>
                        <div class="flex flex--center">
                            <input class="form-control"
                                   type="number"
                                   :value="settings.heVal('rest_he')"
                                   @change="(e) => { setHeights('rest_he',e) }"/>
                            <span>(ft)</span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td><label class="m--l">Bottom:</label></td>
                    <td>
                        <div class="flex flex--center">
                            <input class="form-control"
                                   type="number"
                                   :value="settings.heVal('bot_he')"
                                   @change="(val1, val2) => { setHeights('bot_he',val1, val2) }"/>
                            <span>(ft)</span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td><label>Tooltip:</label></td>
                    <td>
                        <div class="flex flex--center">
                            <span class="indeterm_check__wrap">
                                <span class="indeterm_check" @click="settings.show_eqpt_tooltip = !settings.show_eqpt_tooltip;saveOnBknd()">
                                    <i v-if="settings.show_eqpt_tooltip" class="glyphicon glyphicon-ok group__icon"></i>
                                </span>
                            </span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td><label>Independent<br>CTRL Nodes:</label></td>
                    <td>
                        <div class="flex flex--center">
                            <span class="indeterm_check__wrap">
                                <span class="indeterm_check" @click="changeIndepend()">
                                    <i v-if="settings.use_independent_controls" class="glyphicon glyphicon-ok group__icon"></i>
                                </span>
                            </span>
                            <i class="fa fa-info-circle" ref="indep_ref" @click="showHover('indep_ref')"></i>
                            <hover-block v-if="indep_ref"
                                         :html_str="'Independent CTRL Nodes for Lines for F/B Views'"
                                         :p_left="help_left"
                                         :p_top="help_top"
                                         :c_offset="12"
                                         @another-click="indep_ref = false"
                            ></hover-block>
                        </div>
                    </td>
                </tr>

                <tr>
                    <td><label>Full F/B<br>Reflection</label></td>
                    <td>
                        <div class="flex flex--center">
                            <span class="indeterm_check__wrap">
                                <span class="indeterm_check" @click="changeReflect()">
                                    <i v-if="settings.full_reflection" class="glyphicon glyphicon-ok group__icon"></i>
                                </span>
                            </span>
                            <i class="fa fa-info-circle" ref="refl_ref" @click="showHover('refl_ref')"></i>
                            <hover-block v-if="refl_ref"
                                         :html_str="'Reflect all items for<br>switching F/B views.'"
                                         :p_left="help_left"
                                         :p_top="help_top"
                                         :c_offset="12"
                                         @another-click="refl_ref = false"
                            ></hover-block>
                        </div>
                    </td>
                </tr>

            </table>
        </div>
    </div>
</template>

<script>
    import {eventBus} from './../../../app';

    import {Settings} from './Settings';

    import TabldaColopicker from "../../../components/CustomCell/InCell/TabldaColopicker";
    import HoverBlock from "../../../components/CommonBlocks/HoverBlock";

    export default {
        name: 'CanvSettings',
        mixins: [
        ],
        components: {
            HoverBlock,
            TabldaColopicker,
        },
        data() {
            return {
                menu_opened: false,
                indep_ref: false,
                refl_ref: false,
                help_left: 0,
                help_top: 0,
            }
        },
        computed: {
        },
        props: {
            settings: Settings,
        },
        watch: {
        },
        methods: {
            showHover(type) {
                let bounds = this.$refs[type] ? this.$refs[type].getBoundingClientRect() : {};
                let px = (bounds.left + bounds.right) / 2;
                let py = (bounds.top + bounds.bottom) / 2;
                this[type] = true;
                this.help_left = px || window.event.clientX;
                this.help_top = py || window.event.clientY;
                this.help_offset = Math.abs(bounds.top - bounds.bottom) || 0;
            },
            changeIndepend() {
                this.settings.use_independent_controls = !this.settings.use_independent_controls;
                this.redrSignal();
            },
            changeReflect() {
                this.settings.full_reflection = !this.settings.full_reflection;
                this.redrSignal();
            },
            redrSignal() {
                this.$emit('redraw-signal');
                this.saveOnBknd();
            },
            air_base_nameChanger(key, e) {
                this.settings.setAirBaseNames(key, e.target.value);
                //this.$emit('eqpt-location');
                this.saveOnBknd();
            },
            colorChanger(clr) {
                this.settings.background = clr;
                this.saveOnBknd();
            },
            setHeights(key, e) {
                let diff = to_float(this.settings[key]) - to_float(e.target.value);
                let affected = '';
                switch (key) {
                    case 'top_elev': affected = 'pos,sec,rest'; break;
                    case 'pos_he': affected = 'sec,rest'; key = this.settings.heVal(key, true); break;
                    case 'sector_he': affected = 'rest'; key = this.settings.heVal(key, true); break;
                    case 'rest_he': affected = ''; key = this.settings.heVal(key, true); break;
                    case 'bot_he': affected = ''; key = this.settings.heVal(key, true); break;
                    case 'bot_elev': affected = 'bot'; diff *= -1; break;
                }
                this.settings[key] = to_float(e.target.value);
                this.saveOnBknd();
                this.$emit('changed-heights', affected, diff);
            },
            saveOnBknd() {
                this.settings.saveSettings();
            },
            hideMenu(e) {
                let container = $(this.$refs.settings_button);
                if (this.menu_opened && container.has(e.target).length === 0) {
                    this.menu_opened = false;
                }
            },
        },
        mounted() {
            eventBus.$on('global-click', this.hideMenu);
        },
        beforeDestroy() {
            eventBus.$off('global-click', this.hideMenu);
        }
    }
</script>

<style lang="scss" scoped>
    .settings_wrapper {
        .settings_btn {
            width: 35px;
            height: 30px;
            padding: 0px;
        }
        .settings_menu {
            position: absolute;
            left: 0;
            bottom: 100%;
            padding: 5px;
            background: #fff;
            border: 1px solid #777;
            border-radius: 5px;

            .table {
                margin: 0;
                white-space: nowrap;

                th, td {
                    padding: 0 2px;

                    input {
                        width: 100px;
                    }
                    label {
                        margin: 0px;
                    }
                    .color_wrap {
                        position: relative;
                    }
                    .m--l {
                        margin-left: 15px;
                    }
                }
            }

            .table-inner {
                border-collapse: collapse;

                th {
                    text-align: center;
                    background-color: #ddd;
                }
                th, td {
                    position: relative;
                    border: 1px solid #777;
                    border-left: none;

                    input {
                        width: 35px;
                    }
                    select {
                        width: 110px;
                    }
                }
                .td--center {
                    text-align: center;
                }
            }

            .form-control {
                padding: 0 3px;
                height: 24px;
            }
        }

        .fa-info-circle {
            margin-left: 5px;
        }
    }
</style>