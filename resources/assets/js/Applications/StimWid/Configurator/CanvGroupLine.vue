<template>
    <div class="line__wrap" v-if="draw_avail && settings.use_eqpt_width()">
        <template v-if="draw_on_canvas">
            <div v-for="vec in conn_vectors">
                <div class="line__connect" :style="vertical_conn(vec)">
                    <div class="line__outer--v" :style="{backgroundColor: settings.background}">
                        <div class="line__inner--v"
                             :style="{backgroundColor: settings.sel_line_conn_id === vec._id ? select_color : line.show_color}"
                             @click="selLineConn(vec)"
                        ></div>
                    </div>
                </div>
                <div v-if="settings.sel_line_conn_id === vec._id"
                     class="line__control"
                     :draggable="settings.global_draggable"
                     :style="point_conn(vec)"
                     @dragstart="pointDragStart(vec, true)"
                ></div>
                <div class="line__connect" :style="horizontal_conn(vec)">
                    <div class="line__outer--h" :style="{backgroundColor: settings.background}">
                        <div class="line__inner--h"
                             :style="{backgroundColor: settings.sel_line_conn_id === vec._id ? select_color : line.show_color}"
                             @click="selLineConn(vec)"
                        ></div>
                    </div>
                </div>

                <template v-if="settings.sel_line_id === line._id && !settings.sel_line_conn_id">
                    <!--Eqpt port-->
                    <div v-if="vec.l._port_conn"
                         class="line__control"
                         :style="control_conn(vec.l, 'port')"
                         @click="selChangePort(vec.l._order)"
                    ></div>
                    <div v-if="vec.r._port_conn"
                         class="line__control"
                         :style="control_conn(vec.r, 'port')"
                         @click="selChangePort(vec.r._order)"
                    ></div>
                    <!--Eqpt port-->
                    <!--Line sample point-->
                    <div v-if="vec.r._special"
                         class="line__control flex flex--center"
                         :draggable="settings.global_draggable"
                         @click="settings.selLine(line, c_order)"
                         :style="control_conn(vec.r, '', 'no_bord')"
                         @dragstart="lineDrStart()"
                    >&times;</div>
                    <!--Line sample point-->
                    <!--Other control points-->
                    <div v-if="!vec.r._port_conn && !vec.r._special"
                         class="line__control"
                         :draggable="settings.global_draggable"
                         @click="selControlP(vec.r)"
                         :style="control_conn(vec.r, '')"
                         @dragstart="pointDragStart(vec, false)"
                    ></div>
                    <!--Other control points-->
                </template>
            </div>
        </template>
        <div v-show="in_lib || draw_on_canvas"
             class="line__title flex flex--col flex--center"
             :draggable="settings.global_draggable"
             :style="titlePos()"
             @click="clickLine()"
             @dragstart="lineDrStart()"
             @drop="selfLDrop()"
             @contextmenu.prevent="rightLineHandler()"
        >
            <label v-if="!in_lib && !line.is_inline()"
                   v-show="settings.show_line_model"
                   class="flex flex--center title__qty"
                   :style="showLineModel"
            >
                <span :style="{backgroundColor: settings.background}">{{ line.qty }}</span>
            </label>
            <label v-if="!in_lib"
                   v-show="settings.show_line_model"
                   class="flex flex--center title__text"
                   :style="showLineModel"
            >
                <span v-if="line.is_inline()" :style="{backgroundColor: settings.background}">({{ line.qty }})&nbsp;</span>
                <span :style="{backgroundColor: settings.background}">{{ line.gui_name || line.title || line.line }}</span>
            </label>

            <div v-if="in_lib" class="title__form" :style="lineStyle()"></div>
            <label v-if="in_lib" class="flex flex--center" :style="showLineModel">{{ line.gui_name || line.title || line.line }}</label>
            <label v-if="in_lib" class="title__thick" :style="showLineModel">{{ line.f_diameter || line.diameter }}</label>
        </div>
    </div>
</template>

<script>
    import {Eqpt} from "./Eqpt";
    import {Line} from "./Line";
    import {Settings} from "./Settings";

    import GlobalPosMixin from "./GlobalPosMixin.vue";

    export default {
        name: 'CanvGroupLine',
        mixins: [
            GlobalPosMixin,
        ],
        components: {
        },
        data() {
            return {
                draw_avail: false,
                from_eq: null,
                to_eq: null,
                conn_vectors: [],
                select_color: '#F00',
            }
        },
        computed: {
            draw_on_canvas() {
                return (!this.line._from_eqpt || !this.line._from_eqpt._hidden)
                    && (!this.line._to_eqpt || !this.line._to_eqpt._hidden);
            },
            showLineModel() {
                return {
                    fontFamily: this.settings.show_line_model__font,
                    fontSize: this.settings.show_line_model__size+'px',
                    color: this.settings.sel_line && this.settings.sel_line._id === this.line._id
                        ? this.select_color
                        : this.settings.show_line_model__color,
                    top: this.line.is_inline() ? 'auto' : null,
                    bottom: this.line.is_inline() ? 'auto' : null,
                };
            },
            line_diameter() {
                return this.line.f_diameter * 4;
            },
        },
        props: {
            line: Line,
            all_lines: Array,
            settings: Settings,
            px_in_ft: Number,
            in_lib: Boolean,
        },
        watch: {
            px_in_ft(val) {
                this.preparePoints();
            },
        },
        methods: {
            //styles
            titlePos() {
                if (this.in_lib) {
                    return {};
                } else {
                    let k_top = this.settings.getLineTopKey();
                    let k_left = this.settings.getLineLeftKey();
                    // mid
                    let top = (this.settings.top_elev - this.line[k_top]) * this.px_in_ft;
                    top += this.settings.get_glob_top();
                    let left = this.line[k_left] * this.px_in_ft;
                    // top
                    if (this.line.section_place() < 0 && this.conn_vectors.length) {
                        let start = _.first(this.conn_vectors).l;
                        let end = _.last(this.conn_vectors).r;
                        let vectr = start.top_px > end.top_px ? end : start;
                        top = (vectr.top_px + top) / 2;
                        left = vectr.left_px;
                    }
                    // bot
                    if (this.line.section_place() > 0 && this.conn_vectors.length) {
                        let start = _.first(this.conn_vectors).l;
                        let end = _.last(this.conn_vectors).r;
                        let vectr = start.top_px > end.top_px ? start : end;
                        top = (vectr.top_px + top) / 2;
                        left = vectr.left_px;
                    }
                    // style
                    return this.settings.full_mirr({
                        position: 'absolute',
                        left: left+'px',
                        top: top+'px',
                        height: (this.line_diameter * 1.5)+'px',
                        backgroundColor: 'transparent',
                        transform: this.line.is_horizontal() ? null : 'rotate(-90deg)',
                    });
                }
            },
            lineStyle() {
                return {
                    border: (this.line_diameter/2)+'px solid',
                    width: this.in_lib ? '1px' : '50px',
                    height: this.in_lib ? '50px' : '1px',
                };
            },

            //click
            clickLine() {
                if (!this.in_lib && this.settings.sel_status) {
                    this.line.status = this.settings.sel_status.name;
                    this.settings.clearSel('status');
                    this.$emit('save-model', this.line, 'status');
                } else {
                    this.settings.selLine(this.line, this.c_order);
                }
            },

            //conn points
            point_conn(vector) {
                let ch_ord = this.ch_ord(vector);
                let llp = ch_ord ? vector.r.left_px : vector.l.left_px;
                let ttp = ch_ord ? vector.l.top_px : vector.r.top_px;
                return this.settings.full_mirr({
                    left: Math.round(llp - 2) + 'px',
                    top: Math.round(ttp - 2) +'px',
                    height: Math.round(this.line_diameter + 4)+'px',
                    width: Math.round(this.line_diameter + 4)+'px',
                    border: '1px solid '+this.select_color,
                    backgroundColor: '#FFF',
                    transform: 'rotate(45deg)',
                });
            },
            control_conn(vector_el, port, no_border) {
                let same = this.settings.sel_line_id === this.line._id
                    && this.settings.sel_control_point === vector_el._order;
                return this.settings.full_mirr({
                    left: Math.round(vector_el.left_px - 2) + 'px',
                    top: Math.round(vector_el.top_px - 2) +'px',
                    height: Math.round(this.line_diameter + 4)+'px',
                    width: Math.round(this.line_diameter + 4)+'px',
                    color: same ? this.select_color : this.line.show_color,
                    border: no_border ? 'none' : (same ? '1px solid '+this.select_color : '1px solid '+this.line.show_color),
                    backgroundColor: port && same ? this.select_color : '#FFF',
                    borderRadius: port ? '50%' : '',
                    transform: !port && !no_border ? 'rotate(45deg)' : '',
                });
            },
            //conn lines
            vertical_conn(vector) {
                let ch_ord = this.ch_ord(vector);
                let llp = ch_ord ? vector.r.left_px : vector.l.left_px;
                let top = Math.min(vector.l.top_px, vector.r.top_px);
                return this.settings.full_mirr({
                    left: Math.round(llp) + 'px',
                    top: Math.round(top)+'px',
                    height: Math.round(Math.abs(vector.l.top_px - vector.r.top_px) + (this.line_diameter))+'px',
                    width: Math.round(this.line_diameter)+'px',
                    backgroundColor: this.settings.sel_line_conn_id === vector._id ? this.select_color : this.line.show_color,
                });
            },
            horizontal_conn(vector) {
                let ch_ord = this.ch_ord(vector);
                let ttp = ch_ord ? vector.l.top_px : vector.r.top_px;
                let top = Math.min(vector.l.left_px, vector.r.left_px);
                return this.settings.full_mirr({
                    left: Math.round(top) + 'px',
                    top: Math.round(ttp) + 'px',
                    width: Math.round(Math.abs(vector.l.left_px - vector.r.left_px) + (this.line_diameter)) + 'px',
                    height: Math.round(this.line_diameter)+'px',
                    backgroundColor: this.settings.sel_line_conn_id === vector._id ? this.select_color : this.line.show_color,
                });
            },
            ch_ord(vector) {
                return (vector.r._port_conn ? vector.r._change_line_order : vector.l._change_line_order);
            },

            //conn data
            preparePoints() {
                this.draw_avail = false;
                let arr = [], res = [];
                if (this.line._from_eqpt && this.line._to_eqpt) {
                    //eqpts
                    let tmp_fr = this.get_eqpt_pos(true, this.line._from_eqpt, this.line.from_port_pos, this.line.from_port_idx);
                    let tmp_to = this.get_eqpt_pos(false, this.line._to_eqpt, this.line.to_port_pos, this.line.to_port_idx);
                    let from_first = tmp_fr.left_px < tmp_to.left_px;
                    this.from_eq = this.get_eqpt_pos(from_first, this.line._from_eqpt, this.line.from_port_pos, this.line.from_port_idx);
                    this.to_eq = this.get_eqpt_pos(!from_first, this.line._to_eqpt, this.line.to_port_pos, this.line.to_port_idx);
                    arr.push(this.from_eq);
                    arr.push(this.to_eq);

                    //line caption
                    let k_top = this.settings.getLineTopKey();
                    let k_left = this.settings.getLineLeftKey();
                    arr.push( this.get_point_pos(this.line[k_top], this.line[k_left], this.c_order, true) );

                    //point
                    let control_obj = this.settings.getLineObjKey();
                    _.each(this.line[control_obj], (obj) => {
                        let oo = this.settings.is_rev_no_full() && !this.settings.use_independent_controls
                            ? this.max_ord - obj.ord
                            : obj.ord;
                        arr.push( this.get_point_pos(obj.top, obj.left, oo, false) );
                    });

                    //order
                    arr = _.orderBy(arr, '_order');

                    //set as pairs
                    _.each(arr, (el, i) => {
                        if (arr[i+1]) {
                            res.push({
                                l: arr[i],
                                r: arr[i + 1],
                                _id: uuidv4(),
                            });
                        }
                    });
                }
                this.conn_vectors = res;
                this.draw_avail = true;
            },

            //drag
            lineDrStart() {
                this.$emit('start-drag', this.line, window.event.offsetX, window.event.offsetY);
            },
            pointDragStart(vector, to_add) {
                let point = {
                    is_new: Boolean(to_add),
                    order: to_add ? (vector.r._order + vector.l._order)/2 : vector.r._order,
                };
                this.$emit('point-drag-start', this.line, point, window.event.offsetX, window.event.offsetY);
            },
            //clicks
            rightLineHandler() {
                if (this.in_lib) {
                    this.$emit('right-click', this.line);
                } else {
                    this.$emit('conn-popup', this.line._id);
                    this.settings.selLine(this.line, this.c_order);
                }
            },
            selfLDrop() {
                if (!this.settings.drag_line || this.settings.drag_line._id !== this.line._id) {
                    window.event.stopPropagation();
                    Swal('Info','Not allowed!');
                    this.$emit('start-drag', null, null, null);
                }
            },
            selLineConn(vec) {
                this.settings.selConn( this.line, this.settings.sel_line_conn_id !== vec._id ? vec._id : null );
            },
            selControlP(vec_point) {
                this.settings.selLine( this.line, this.settings.sel_control_point !== vec_point._order ? vec_point._order : null );
            },
            selChangePort(eq_order) {
                let eq_point = eq_order === this.from_eq._order ? this.to_eq : this.from_eq;
                if (eq_point && eq_point._port_conn) {
                    this.settings.selLine(this.line, eq_order);
                    this.settings.selFirstPort(eq_point._e_id, eq_point._e_pos, eq_point._e_idx);
                }
            },
        },
        mounted() {
            this.preparePoints();
        },
        beforeDestroy() {
        }
    }
</script>

<style lang="scss" scoped>
    .line__wrap {

        .line__connect {
            position: absolute;
            border: none;
            z-index: 500;

            .line__outer--v {
                width: calc(100% + 6px);
                height: calc(100% - 20px);
                background-color: #FFF;
                margin-top: 10px;
                margin-left: -3px;

                .line__inner--v {
                    width: calc(100% - 6px);
                    height: 100%;
                    margin-left: 3px;
                }
            }
            .line__outer--h {
                width: calc(100% - 20px);
                height: calc(100% + 6px);
                background-color: #FFF;
                position: relative;
                top: -3px;
                margin-left: 10px;

                .line__inner--h {
                    width: 100%;
                    height: calc(100% - 6px);
                    position: relative;
                    top: 3px;
                }
            }
        }
        .line__control {
            font-size: 24px;
            cursor: move;
            position: absolute;
            z-index: 600;
        }

        .line__title {
            position: relative;
            height: auto;
            background-color: #FFF;
            z-index: 550;
            font-size: 9px;
            color: #777;
            white-space: nowrap;
            max-width: 100%;
            padding: 1px;
            border: none;

            .title__qty {
                cursor: pointer;
                position: absolute;
                bottom: 100%;
                margin: 0;
            }
            .title__form {
                position: relative;
            }
            .title__text {
                cursor: pointer;
                position: absolute;
                top: 100%;
                margin: 0;
            }
            .title__thick {
                position: absolute;
                left: 100%;
                top: 50%;
                transform: translateY(-90%);
            }
        }
    }
</style>