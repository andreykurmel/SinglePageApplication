<template>
    <div class="element"
         @mouseover="mouseIn()"
         @mouseout="mouseOut()"
    >
        <div class="inner flex flex--col flex--center"
             :draggable="settings.global_draggable"
             :style="styleEqpt(eqpt)"
             @click.self="clickEqpt"
             @contextmenu.prevent="rightEqptHandler()"
             @dragstart="eqptDrStart"
             @drop="selfDrop()"
        >
            <div v-if="settings.use_eqpt_width()" class="ports ports--top" :style="{top: -settings.port_px+'px'}">
                <div v-for="(used,idx) in eqpt._used_top_ports"
                     class="port__elem"
                     :style="portBorder(used, 'Top', idx)"
                     @click="selPort('Top', idx)"
                ></div>
            </div>
            <div v-if="settings.use_eqpt_width()" class="ports ports--left" :style="{left: -settings.port_px/2+'px'}">
                <div v-for="(used,idx) in (port_mirror ? eqpt._used_right_ports : eqpt._used_left_ports)"
                     class="port__elem"
                     :style="portBorder(used, 'Left', idx)"
                     @click="selPort('Left', idx)"
                ></div>
            </div>

            <div class="model__id margin-auto" v-show="in_lib || settings.show_eqpt_id" :style="showEqptId">
                <span v-if="!edit_model_id" @click="modelidShow()">{{ eqpt.locmod_id }}</span>
                <input v-else=""
                       class="form-control"
                       ref="model_id_editor"
                       style="width: 75px;"
                       v-model="eqpt.locmod_id"
                       @blur="edit_model_id = false"
                       @change="changedModelid()"/>
            </div>
            <div class="margin-auto" v-show="!in_lib && settings.show_eqpt_azimuths && eqpt.azm">
                <sector-azimuth :azimuth="eqpt.azm" :color="settings.show_eqpt_azimuth__color"></sector-azimuth>
                <div class="sector_face__label" :style="showEqptAzms">{{ Number(eqpt.azm).toFixed(0) }}</div>
            </div>

            <div class="eqpt__tech" v-show="in_lib || settings.show_eqpt_tech" :style="showEqptTech">
                <span>{{ eqpt.tech_arr.join('/') }}</span>
            </div>

            <div class="dimensions" v-show="in_lib || settings.show_eqpt_size" :style="showEqptSize">
                <span>{{ eqpt.showDim('x') }}</span>
                <br>
                <span>{{ eqpt.showDim('y') }}</span>
                <br>
                <span>{{ eqpt.showDim('z') }}</span>
            </div>

            <div v-if="settings.use_eqpt_width()" class="ports ports--right" :style="{right: -settings.port_px/2+'px'}">
                <div v-for="(used,idx) in (port_mirror ? eqpt._used_left_ports : eqpt._used_right_ports)"
                     class="port__elem"
                     :style="portBorder(used, 'Right', idx)"
                     @click="selPort('Right', idx)"
                ></div>
            </div>
            <div v-if="settings.use_eqpt_width()" class="ports ports--bottom">
                <div v-for="(used,idx) in eqpt._used_bottom_ports"
                     class="port__elem"
                     :style="portBorder(used, 'Bottom', idx)"
                     @click="selPort('Bottom', idx)"
                ></div>
            </div>

            <div class="caption" :style="captionPos()">
                <label class="flex flex--center" :style="showEqptModel">{{ showTitle() }}</label>
            </div>
        </div>
        <div v-show="!in_lib && show_tooltip && settings.show_eqpt_tooltip" class="e_tooltip" :style="styleTooltip(eqpt)">
            <table>
                <tr>
                    <th>Sector:</th>
                    <td>{{ eqpt.sector }}</td>
                </tr>
                <tr>
                    <th>Pos:</th>
                    <td>{{ eqpt.pos }}</td>
                </tr>
                <tr>
                    <th>Model:</th>
                    <td>{{ eqpt.equipment }}</td>
                </tr>
                <tr>
                    <th>RAD Ctr:</th>
                    <td>{{ eqpt.elev_rad }}</td>
                </tr>
                <tr>
                    <th>G Ctr:</th>
                    <td>{{ eqpt.elev_g }}</td>
                </tr>
                <tr>
                    <th>PD Ctr:</th>
                    <td>{{ eqpt.elev_pd }}</td>
                </tr>
                <tr>
                    <th>Dims:</th>
                    <td>
                        <span>{{ eqpt.showDim('x') }}x{{ eqpt.showDim('y') }}x{{ eqpt.showDim('z') }}</span>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</template>

<script>
    import {Eqpt} from "./Eqpt";
    import {Settings} from "./Settings";
    import SectorAzimuth from "./SectorAzimuth";

    export default {
        name: 'CanvGroupEqpt',
        mixins: [
        ],
        components: {
            SectorAzimuth
        },
        data() {
            return {
                from_model_id: 0,
                edit_model_id: false,
                show_tooltip: false,
                mouse_x: null,
                mouse_y: null,
            }
        },
        computed: {
            showEqptModel() {
                return {
                    fontFamily: this.settings.show_eqpt_model__font,
                    fontSize: this.settings.show_eqpt_model__size+'px',
                    color: this.eqpt_selected ? '#F00' : this.settings.show_eqpt_model__color,
                };
            },
            showEqptTech() {
                let px = this.settings.port_px;
                return {
                    fontFamily: this.settings.show_eqpt_tech__font,
                    fontSize: this.settings.show_eqpt_tech__size+'px',
                    color: this.eqpt_selected ? '#F00' : this.settings.show_eqpt_tech__color,
                    bottom: this.eqpt.port_top ? 'calc(100% + '+px+'px)' : '100%',
                };
            },
            showEqptId() {
                return {
                    fontFamily: this.settings.show_eqpt_id__font,
                    fontSize: this.settings.show_eqpt_id__size+'px',
                    color: this.eqpt_selected ? '#F00' : this.settings.show_eqpt_id__color,
                    border: this.edit_model_id ? 'none' : '1px solid #444',
                };
            },
            showEqptAzms() {
                return {
                    fontFamily: this.settings.show_eqpt_azimuth__font,
                    fontSize: this.settings.show_eqpt_azimuth__size+'px',
                    color: this.eqpt_selected ? '#F00' : this.settings.show_eqpt_azimuth__color,
                };
            },
            showEqptSize() {
                let px = this.settings.port_px;
                return {
                    fontFamily: this.settings.show_eqpt_size__font,
                    fontSize: this.settings.show_eqpt_size__size+'px',
                    color: this.eqpt_selected ? '#F00' : this.settings.show_eqpt_size__color,
                    left: this.eqpt.port_right ? 'calc(100% + '+px+'px)' : '100%',
                };
            },
            eqpt_selected() {
                return !!_.find(this.settings.mass_eqpt, {_id: this.eqpt._id});
            },
            port_mirror() {
                return this.settings.is_full_rev() || this.settings.eqptPortMirr(this.eqpt);
            },
        },
        props: {
            eqpt: Eqpt,
            settings: Settings,
            px_in_ft: Number,
            in_lib: Boolean,
            eqpt_params: Object,
            no_port_mirr: Boolean,
        },
        watch: {
        },
        methods: {
            //model id edit
            modelidShow() {
                this.edit_model_id = true;
                this.from_model_id = this.eqpt.locmod_id;
                this.$nextTick(() => {
                    if (this.$refs.model_id_editor) {
                        this.$refs.model_id_editor.focus();
                    }
                });
            },
            changedModelid() {
                let eqptlib = _.clone(this.eqpt);
                eqptlib._id = eqptlib._eqptlib_id;
                this.$emit('modelid-changed', eqptlib, this.from_model_id);
                this.edit_model_id = false;
            },
            //style
            styleEqpt(eqpt) {
                let top_off = 0, posleft = 0, posit = 'relative';

                if (this.eqpt_params) {
                    posit = 'absolute';

                    // Full Eqpt always in the section
                    let max_wi = this.settings.eqptMaxWidth(eqpt);
                    let e_max_left = 0;
                    let e_max_right = max_wi - eqpt.get_wi(this.settings);
                    if (e_max_right < e_max_left) {
                        let mid = (e_max_left + e_max_right) / 2;
                        e_max_left = mid;
                        e_max_right = mid;
                    }
                    posleft = Math.max(eqpt.posLeft(this.settings), e_max_left);
                    posleft = Math.min(eqpt.posLeft(this.settings), e_max_right);

                    let e_max_top = 0;// 0 -> as Eqpt is placed relatively to SECTION
                    let e_max_bottom = this.eqpt_params.group_he - eqpt.calc_dy;
                    if (e_max_bottom < e_max_top) {
                        let mid = (e_max_top + e_max_bottom) / 2;
                        e_max_top = mid;
                        e_max_bottom = mid;
                    }

                    let ee = this.settings.elev_by;
                    if (eqpt.is_top()) {
                        top_off = this.eqpt_params.top_lvl - eqpt.getTopCoord(ee);
                    } else {
                        top_off = this.eqpt_params.group_he + this.settings.bot_elev - eqpt.getTopCoord(ee);
                    }
                    top_off = Math.min(top_off, e_max_bottom); // Full Eqpt always in the section
                    top_off = Math.max(top_off, e_max_top);
                }

                posleft += this.settings.convLeftEqpt(eqpt);
                return this.settings.full_mirr({
                    position: posit,
                    top: Math.round(top_off * this.px_in_ft) + 'px',
                    left: Math.round(posleft * this.px_in_ft) + 'px',
                    width: Math.round(eqpt.get_wi(this.settings) * this.px_in_ft) + 'px',
                    height: Math.round(eqpt.calc_dy * this.px_in_ft) + 'px',
                    backgroundColor: eqpt.show_color,
                    border: this.eqpt_selected ? '2px solid #F00' : '1px solid #444'
                });
            },
            styleTooltip(eqpt) {
                /*let style = this.styleEqpt(eqpt);
                return this.settings.convStyleObj({
                    top: (to_float(style.top) + to_float(style.height)) + 'px',
                    left: (to_float(style.left) + to_float(style.width)) + 'px',
                });*/
                return {
                    top: this.mouse_y+'px',
                    left: this.mouse_x+'px',
                };
            },

            //click
            clickEqpt(eve) {
                if (!this.in_lib && this.settings.sel_status) {
                    this.eqpt.status = this.settings.sel_status.name;
                    this.settings.clearSel('status');
                    this.$emit('save-model', this.eqpt, 'status');
                } else
                if (!this.in_lib && this.settings.sel_elev) {
                    switch (this.settings.elev_by) {
                        case "pd": this.eqpt.elev_pd = Number(this.settings.sel_elev.elev); break;
                        case "gc": this.eqpt.elev_g = Number(this.settings.sel_elev.elev); break;
                        case "pc": this.eqpt.elev_rad = Number(this.settings.sel_elev.elev); break;
                    }
                    this.settings.clearSel('elev');
                    this.$emit('save-model', this.eqpt, 'elev');
                } else
                if (!this.in_lib && this.settings.sel_azimuth) {
                    this.eqpt.azm = Number(this.settings.sel_azimuth.deg);
                    this.eqpt.at_rot_y = Number(this.settings.sel_azimuth.deg);
                    this.settings.clearSel('azimuth');
                    this.$emit('save-model', this.eqpt, 'azimuth');
                } else
                if (!this.in_lib && this.settings.sel_tech) {
                    this.eqpt.setTech( this.settings.sel_tech.technology );
                    this.settings.clearSel('tech');
                    this.$emit('save-model', this.eqpt, 'tech');
                } else {
                    if (this.in_lib) {
                        this.settings.clearSel();
                        this.settings.eqptDragStart(this.eqpt, 0, 0);
                        this.settings.add_new = true;
                    } else {
                        let cmdOrCtrl = eve.metaKey || eve.ctrlKey;
                        this.settings.selEqpt(this.eqpt, !!cmdOrCtrl);
                    }
                }
            },

            //positions
            captionPos() {
                let is_vert = this.eqpt.label_is_vert();
                let side = String(this.eqpt.label_side).toLowerCase();
                let px = this.settings.port_px;
                switch (side) {
                    case 'top': return {
                        bottom: this.eqpt.port_top ? 'calc(100% + '+px+'px)' : '100%',
                        transform: is_vert ? 'rotate(-90deg) translateX(50%)' : null,
                    };
                    case 'left': return {
                        right: this.eqpt.port_left ? 'calc(100% + '+px*2+'px)' : 'calc(100% + '+px+'px)',
                        transform: is_vert ? 'translateX(50%) rotate(-90deg)' : null,
                    };
                    case 'right': return {
                        left: this.eqpt.port_right ? 'calc(100% + '+px*2+'px)' : 'calc(100% + '+px+'px)',
                        transform: is_vert ? 'translateX(-50%) rotate(90deg)' : null,
                    };
                    case 'bottom':
                    default: return {
                        top: this.eqpt.port_bot ? 'calc(100% + '+px+'px)' : '100%',
                        transform: is_vert ? 'rotate(-90deg) translateX(-50%)' : null,
                    };
                }
            },
            eqptDrStart(e) {
                this.mouseOut();
                if (this.in_lib) {
                    this.settings.clearSel();
                    this.settings.add_new = true;
                }
                console.log('offset - x:'+e.offsetX+' y:'+e.offsetY);
                this.settings.eqptDragStart(this.eqpt, e.offsetX, e.offsetY);

                var target = e.target || e.srcElement,
                    rect = target.getBoundingClientRect(),
                    offsetX = e.clientX - rect.left,
                    offsetY = e.clientY - rect.top;
                console.log('from absolute - x:'+offsetX+' y:'+offsetY);
                console.log('target - '+target.nodeName+' calss:'+target.className);//
            },
            //draw
            showTitle() {
                return this.in_lib || this.settings.show_eqpt_model
                    ? this.eqpt.equipment
                    : '';
            },
            portBorder(used, pos, idx) {
                //reverse ports
                let rev = this.reverse_ports(pos, idx);
                pos = rev.pos;
                idx = rev.idx;
                //style
                let obj = {
                    backgroundColor: '#333',//used ? '#777' : '#333',
                    margin: '0 '+this.settings.port_margin+'px',
                    width: this.settings.port_px+'px',
                    height: this.settings.port_px+'px',
                };
                if (
                    this.settings
                    && this.settings.sel_eqpt_id === this.eqpt._id
                    && this.settings.sel_port_pos === pos
                    && this.settings.sel_port_idx === idx
                ) {
                    obj['backgroundColor'] = '#F00';
                }
                return obj;
            },
            //right click
            rightEqptHandler() {
                this.$emit('right-click', this.eqpt);
                if (!this.in_lib) {
                    this.settings.selEqpt(this.eqpt);
                }
            },
            selfDrop() {
                if (
                    this.settings.drag_eqpt
                    && this.settings.drag_eqpt._id !== this.eqpt._id
                    && this.settings.drag_eqpt.status === this.eqpt.status
                ) {
                    window.event.stopPropagation();
                    Swal('Info','Not allowed!');
                    this.$emit('start-drag', null, null, null);
                }
            },
            selPort(pos, idx) {
                //reverse ports
                let rev = this.reverse_ports(pos, idx);
                pos = rev.pos;
                idx = rev.idx;
                //send signal
                this.$emit('select-port', this.eqpt._id, pos, idx);
            },
            reverse_ports(pos, idx) {
                if (this.port_mirror) {
                    switch (pos) {
                        case 'Left': pos = 'Right'; break;
                        case 'Right': pos = 'Left'; break;
                    }
                    switch (pos) {
                        case 'Top': idx = (this.eqpt._used_top_ports.length-1) - idx; break;
                        case 'Bottom': idx = (this.eqpt._used_bottom_ports.length-1) - idx; break;
                    }
                }
                return {
                    pos: pos,
                    idx: idx,
                };
            },

            //tooltip
            mouseIn() {
                this.show_tooltip = true;
                this.mouse_x = window.event.pageX;
                this.mouse_y = window.event.pageY;
            },
            mouseOut() {
                this.show_tooltip = false;
                this.mouse_x = null;
                this.mouse_y = null;
            },
        },
        mounted() {//
        },
        beforeDestroy() {
        }
    }
</script>

<style lang="scss" scoped>
    .element {
        cursor: pointer;

        .inner {
            z-index: 300;
            border-radius: 3px;

            .dimensions {
                position: absolute;
                overflow: hidden;
                line-height: 12px;
                font-size: 12px;
                text-align: left;
                padding-left: 2px;
            }

            .eqpt__tech {
                position: absolute;
                bottom: 100%;
                overflow: hidden;
                line-height: 12px;
                font-size: 12px;
                white-space: nowrap;
            }

            .model__id {
                cursor: pointer;
                overflow: hidden;
                line-height: 12px;
                font-size: 12px;
                white-space: nowrap;
                border-radius: 50%;
                padding: 0 3px;
                max-width: 100%;

                .form-control {
                    padding: 0 3px;
                    height: 26px;
                }
            }

            .ports {
                position: absolute;
                display: flex;

                .port__elem {
                    border: none;
                    cursor: cell;
                }
            }
            .ports--top {
                top: -6px;
            }
            .ports--left {
                left: -3px;
                transform: rotate(90deg) translateX(-50%);
                transform-origin: left center;
            }
            .ports--right {
                right: -3px;
                transform: rotate(90deg) translateX(50%);
                transform-origin: right center;
            }
            .ports--bottom {
                top: 100%;
            }

            .caption {
                position: absolute;
                font-size: 9px;
                color: #777;
                white-space: nowrap;

                label {
                    margin: 0;
                }
            }
        }
        .e_tooltip {
            position: fixed;
            background-color: #FFF;
            padding: 0 3px;
            border: 1px solid #CCC;
            border-radius: 5px;
            z-index: 750;

            table {
                white-space: nowrap;
            }
        }
        .sector_face__label {
            font-size: 10px;
            text-align: center;
            line-height: 1px;
        }
    }
</style>