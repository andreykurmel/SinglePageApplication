<template>
    <div class="flex flex--col menu_wrapper" :style="wrapStyle">

        <span class="menu__collapse glyphicon"
              :class="collapse ? 'glyphicon-triangle-left' : 'glyphicon-triangle-right'"
              :style="{left: collapse ? '-15px' : '0'}"
              @click="collapseToggle()"
        ></span>


        <div v-show="!collapse"
             v-if="eqpt_lib"
             class="menu__elem"
             :class="(opened_key === 'eqpt' ? 'flex__elem-remain flex flex--col' : '')"
        >
            <div class="elem__head">
                <button class="btn btn-default blue-gradient"
                        :style="$root.themeButtonStyle"
                        @click="opened_key = (opened_key === 'eqpt' ? '' : 'eqpt')"
                >Eqpt LIB</button>
            </div>
            <div v-show="opened_key === 'eqpt'" class="elem__body flex flex--col">
                <i class="fa fa-plus" @click="addPopupHandler('eqpt')"></i>
                <div class="body__overflow flex flex--col flex--center-v" @click.self="cclear()">
                    <div class="body__item" v-for="eqpt in eqpt_lib">
                        <canv-group-eqpt
                                :eqpt="eqpt"
                                :px_in_ft="px_in_ft"
                                :in_lib="true"
                                :settings="settings"
                                @right-click="(eqpt) => { showSettSelect('eqpt', eqpt) }"
                                @start-drag="libDragStartE"
                        ></canv-group-eqpt>
                    </div>

                    <!--eqpt sett menu-->
                    <div v-if="sett_type === 'eqpt' && sett_object" class="float-settings" :style="settStyle">
                        <button class="btn btn-default blue-gradient"
                                style="padding: 0 3px;"
                                :style="$root.themeButtonStyle"
                                @click="popupElem('model')"
                        >Source</button>
                        <br>
                        <button class="btn btn-default blue-gradient"
                                style="padding: 0 3px;"
                                :style="$root.themeButtonStyle"
                                @click="popupElem('eqpt_lib')"
                        >Eqpt LIB</button>
                    </div>
                    <!--eqpt sett menu-->
                </div>
            </div>
        </div>

        <div v-show="!collapse"
             v-if="line_lib"
             class="menu__elem"
             :class="(opened_key === 'line' ? 'flex__elem-remain flex flex--col' : '')"
        >
            <div class="elem__head">
                <button class="btn btn-default blue-gradient"
                        :style="$root.themeButtonStyle"
                        @click="opened_key = (opened_key === 'line' ? '' : 'line')"
                >Line LIB</button>
            </div>
            <div v-show="opened_key === 'line'" class="elem__body flex flex--col">
                <i class="fa fa-plus" @click="addPopupHandler('line')"></i>
                <div class="body__overflow flex flex--col flex--center-v" @click.self="cclear()">
                    <div class="body__item item--sm" v-for="line in line_lib">
                        <canv-group-line
                                :line="line"
                                :px_in_ft="px_in_ft"
                                :in_lib="true"
                                :settings="settings"
                                @right-click="(line) => { showSettSelect('line', line) }"
                                @start-drag="libDragStartL"
                        ></canv-group-line>
                    </div>

                    <!--line sett menu-->
                    <div v-if="sett_type === 'line' && sett_object" class="float-settings" :style="settStyle">
                        <button class="btn btn-default blue-gradient"
                                style="padding: 0 3px;"
                                :style="$root.themeButtonStyle"
                                @click="popupElem('feedline')"
                        >Source</button>
                        <br>
                        <button class="btn btn-default blue-gradient"
                                style="padding: 0 3px;"
                                :style="$root.themeButtonStyle"
                                @click="popupElem('line_lib')"
                        >Line LIB</button>
                    </div>
                    <!--line sett menu-->
                </div>
            </div>
        </div>

        <div v-show="!collapse"
             v-if="tech_list"
             class="menu__elem"
             :class="(opened_key === 'tech' ? 'flex__elem-remain flex flex--col' : '')"
        >
            <div class="elem__head">
                <button class="btn btn-default blue-gradient"
                        :style="$root.themeButtonStyle"
                        @click="opened_key = (opened_key === 'tech' ? '' : 'tech')"
                >Tech LIB</button>
            </div>
            <div v-show="opened_key === 'tech'" class="elem__body flex flex--col">
                <i class="fa fa-plus" @click="addPopupHandler('tech')"></i>
                <div class="body__overflow flex flex--col flex--center-v" @click.self="cclear()">
                    <div class="body__item" v-for="tech in tech_list">
                        <canv-group-tech
                                :tech="tech"
                                :settings="settings"
                                @right-click="sett_object = tech; popupElem('tech');"
                        ></canv-group-tech>
                    </div>
                </div>
            </div>
        </div>

        <div v-show="!collapse"
             v-if="status_list"
             class="menu__elem"
             :class="(opened_key === 'status' ? 'flex__elem-remain flex flex--col' : '')"
        >
            <div class="elem__head">
                <button class="btn btn-default blue-gradient"
                        :style="$root.themeButtonStyle"
                        @click="opened_key = (opened_key === 'status' ? '' : 'status')"
                >Status LIB</button>
            </div>
            <div v-show="opened_key === 'status'" class="elem__body flex flex--col">
                <i class="fa fa-plus" @click="addPopupHandler('status')"></i>
                <div class="body__overflow flex flex--col flex--center-v" @click.self="cclear()">
                    <div class="body__item" v-for="status in status_list">
                        <canv-group-status
                                :status="status"
                                :settings="settings"
                                @right-click="sett_object = status; popupElem('status');"
                        ></canv-group-status>
                    </div>
                </div>
            </div>
        </div>

        <div v-show="!collapse"
             v-if="elevs_list"
             class="menu__elem"
             :class="(opened_key === 'elev' ? 'flex__elem-remain flex flex--col' : '')"
        >
            <div class="elem__head">
                <button class="btn btn-default blue-gradient"
                        :style="$root.themeButtonStyle"
                        @click="opened_key = (opened_key === 'elev' ? '' : 'elev')"
                >Elevs LIB</button>
            </div>
            <div v-show="opened_key === 'elev'" class="elem__body flex flex--col">
                <i class="fa fa-plus" @click="addPopupHandler('elev')"></i>
                <div class="body__overflow flex flex--col flex--center-v" @click.self="cclear()">
                    <div class="body__item" v-for="elev in elevs_list" v-if="echeck(elev)">
                        <canv-group-elev
                                :elev="elev"
                                :settings="settings"
                                @right-click="sett_object = elev; popupElem('elev');"
                        ></canv-group-elev>
                    </div>
                </div>
            </div>
        </div>

        <div v-show="!collapse"
             v-if="azimuth_list"
             class="menu__elem"
             :class="(opened_key === 'azimuth' ? 'flex__elem-remain flex flex--col' : '')"
        >
            <div class="elem__head">
                <button class="btn btn-default blue-gradient"
                        :style="$root.themeButtonStyle"
                        @click="opened_key = (opened_key === 'azimuth' ? '' : 'azimuth')"
                >Azimuths</button>
            </div>
            <div v-show="opened_key === 'azimuth'" class="elem__body flex flex--col">
                <i class="fa fa-plus" @click="addPopupHandler('azimuth')"></i>
                <div class="body__overflow flex flex--col flex--center-v" @click.self="cclear()">
                    <div class="body__item" v-for="az in azimuth_list">
                        <canv-group-azimuth
                                :azimuth="az"
                                :settings="settings"
                                @right-click="sett_object = az; popupElem('azimuth');"
                        ></canv-group-azimuth>
                    </div>
                </div>
            </div>
        </div>

        <div v-show="!collapse"
             v-if="secpos_list"
             class="menu__elem"
             :class="(opened_key === 'secpos' ? 'flex__elem-remain flex flex--col' : '')"
        >
            <div class="elem__head">
                <button class="btn btn-default blue-gradient"
                        :style="$root.themeButtonStyle"
                        @click="opened_key = (opened_key === 'secpos' ? '' : 'secpos')"
                >SEC/POS</button>
            </div>
            <div v-show="opened_key === 'secpos'" class="elem__body flex flex--col">
                <div class="menu_wrapper body__overflow flex flex--col flex--center-v" @click.self="cclear()" style="padding: 5px;">

                    <div v-for="secpos in secpos_list"
                         v-if="secpos.sec_name"
                         class="menu__elem full-width"
                         :class="(secpos_key === secpos.sec_name ? 'flex__elem-remain flex flex--col' : '')"
                    >
                        <div class="elem__head">
                            <button class="btn btn-default blue-gradient"
                                    :style="$root.themeLightBtnStyle"
                                    @click="secpos_key = (secpos_key === secpos.sec_name ? '' : secpos.sec_name)"
                            >{{ secpos.sec_name }}</button>
                        </div>
                        <div v-show="secpos_key === secpos.sec_name" class="elem__body flex flex--col" style="height: calc(100% - 35px)">
                            <div class="body__overflow flex flex--col flex--center-v" @click.self="cclear()">
                                <div class="body__item secpos_prim" v-for="idx in secpos.primary_qty">
                                    <canv-group-secpos
                                            :secpos="secpos"
                                            :settings="settings"
                                            :is_primary="true"
                                            :idx="idx"
                                    ></canv-group-secpos>
                                </div>
                                <div class="body__item secpos_other" v-for="idx in secpos.others_qty">
                                    <canv-group-secpos
                                            :secpos="secpos"
                                            :settings="settings"
                                            :idx="secpos.primary_qty+idx"
                                    ></canv-group-secpos>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </div>
</template>

<script>
    import {Elev} from './Elev';
    import {Eqpt} from './Eqpt';
    import {Settings} from './Settings';

    import CanvGroupEqpt from "./CanvGroupEqpt";
    import CanvGroupLine from "./CanvGroupLine";
    import CanvGroupTech from "./CanvGroupTech";
    import CanvGroupStatus from "./CanvGroupStatus";
    import CanvGroupSecpos from "./CanvGroupSecpos";
    import CanvGroupElev from "./CanvGroupElev";
    import CanvGroupAzimuth from "./CanvGroupAzimuth";

    export default {
        name: 'LibMenu',
        mixins: [
        ],
        components: {
            CanvGroupAzimuth,
            CanvGroupElev,
            CanvGroupSecpos,
            CanvGroupStatus,
            CanvGroupTech,
            CanvGroupLine,
            CanvGroupEqpt,
        },
        data() {
            return {
                opened_key: '',
                secpos_key: '',
                collapse: this.def_collapse,

                sett_type: null,
                sett_object: null,
                sett_top: null,
                sett_left: null,
            }
        },
        computed: {
            wrapStyle() {
                return {
                    height: this.opened_key === '' ? 'auto' : (this.ex_height || '100%'),
                    width: this.collapse ? '0' : '100%',
                };
            },
            settStyle() {
                return {
                    position: 'fixed',
                    top: this.sett_top+'px',
                    left: this.sett_left+'px',
                };
            },
        },
        props: {
            settings: Settings,
            eqpt_lib: Array,
            line_lib: Array,
            tech_list: Array,
            status_list: Array,
            secpos_list: Array,
            elevs_list: Array,
            azimuth_list: Array,
            px_in_ft: Number,
            ex_height: String,
            def_collapse: Boolean,
        },
        watch: {
        },
        methods: {
            libDragStartE(eqpt, offset_x, offset_y) {
                this.$emit('lib-eqpt-add', eqpt, offset_x, offset_y);
            },
            libDragStartL(line, offset_x, offset_y) {
                this.$emit('lib-line-add', line, offset_x, offset_y);
            },
            collapseToggle() {
                this.collapse = !this.collapse;
                this.opened_key = '';
                this.$emit('collapse-toggle', this.collapse);
            },
            showSettSelect(type, object) {
                this.sett_type = type;
                this.sett_object = object;
                this.sett_top = this.$root.lastMouseClick.clientY;
                this.sett_left = this.$root.lastMouseClick.clientX;
            },
            popupElem(category) {
                let row_id;
                switch (category) {
                    case 'model': row_id = this.sett_object._model_id; break;
                    case 'eqpt_lib': row_id = this.sett_object._eqptlib_id; break;
                    case 'feedline': row_id = this.sett_object._feedline_id; break;
                    case 'line_lib': row_id = this.sett_object._linelib_id; break;
                    case 'azimuth':
                    case 'tech':
                    case 'elev':
                    case 'status': row_id = this.sett_object._id; break;
                }
                this.$emit('popup-elem', category, row_id);
                this.sett_type = null;
                this.sett_object = null;
            },
            feedlinePopup(row_id) {
                this.$emit('feedline-popup', row_id)
            },
            //Add Libs
            addPopupHandler(type) {
                this.$emit('open-add-popup', type);
            },
            //clear
            cclear() {
                this.settings.clearSel();
                this.sett_type = null;
                this.sett_object = null;
            },
            echeck(elev) {
                return Elev.availableType(this.settings, elev);
            },
        },
        mounted() {
        },
        beforeDestroy() {
        }
    }
</script>

<style lang="scss" scoped>
    .menu_wrapper {
        width: 100%;
        height: 100%;
        background-color: #fff;
        border: 1px solid #777;
        border-radius: 5px;
        position: relative;

        .menu__collapse {
            position: absolute;
            top: 5px;
            cursor: pointer;
            font-size: 20px;
        }

        .menu__elem {
            overflow: hidden;

            .elem__head {
                button {
                    width: 100%;
                }
            }
            .elem__body {
                position: relative;
                height: calc(100% - 35px);//prevent 'not all elem showing'.

                .body__overflow {
                    overflow: auto;
                    max-width: 100%;
                }
                .body__item {
                    margin: 15px 0;
                    max-width: 100%;
                }
                .item--sm {
                    margin: 5px 0;
                }
                .fa-plus {
                    position: absolute;
                    cursor: pointer;
                    left: 5px;
                    top: 5px;
                    font-size: 1.5em;

                    &:hover {
                        color: #F00;
                    }
                }
            }
        }

        .float-settings {
            position: absolute;
            z-index: 900;
        }
    }
</style>