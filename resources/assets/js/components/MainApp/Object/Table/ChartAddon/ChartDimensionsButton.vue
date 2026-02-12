<template>
    <div ref="chart_button" class="chart_dimensions_button" title="Chart Dimensions" :style="textSysStyle">
        <i class="glyphicon glyphicon-cog" ref="chart_dim_menu" @click="menuOpn()"></i>
        <div v-show="menu_opened" class="chart_dimensions_menu" :style="specialStyle()">
            <div>
                <label>
                    <span>Name: </span>
                    <input
                        type="text"
                        class="form-control control-inline"
                        placeholder="Name"
                        v-model="all_settings.name"
                        @change="$emit('dimensions-changed')"
                    />
                </label>
            </div>
            <div>
                <label>
                    <span>Width (cells): </span>
                    <input
                        type="text"
                        class="form-control control-inline"
                        placeholder="Width"
                        v-model="all_settings.dimensions.gs_wi"
                        @change="$emit('dimensions-changed')"
                    />
                </label>
            </div>
            <div>
                <label>
                    <span>Height (cells): </span>
                    <input
                        type="text"
                        class="form-control control-inline"
                        placeholder="Height"
                        v-model="all_settings.dimensions.gs_he"
                        @change="$emit('dimensions-changed')"
                    />
                </label>
            </div>
            <div class="form-group flex flex--center">
                <label style="margin: 0;">Background color: </label>
                <div class="color_picker_wrapper control-inline">
                    <tablda-colopicker
                            :init_color="all_settings.dimensions.back_color"
                            :avail_null="true"
                            :menu_shift="true"
                            @set-color="setColor"
                    ></tablda-colopicker>
                </div>
            </div>
            <div>
                <label>
                    <span>Type: </span>
                    <select class="form-control control-inline"
                            v-model="all_settings.elem_type"
                            @change="$emit('dimensions-changed', 1)"
                            style="padding: 6px 0px"
                    >
                        <option value="pivot_table">Table</option>
                        <option value="bi_chart">Chart</option>
                        <option value="text_data">Text</option>
                    </select>
                </label>
            </div>
            <div>
                <label>
                    <span>Setup: </span>
                    <button class="btn btn-default blue-gradient setup_btn"
                            @click="$emit('open-settings')"
                            :style="$root.themeButtonStyle"
                    >
                        <i class="fas fa-tools"></i>
                    </button>
                </label>
            </div>
            <div>
                <label>
                    <span>Download: </span>
                    <slot name="dwn_button"></slot>
                </label>
            </div>
            <div v-if="can_edit">
                <label>
                    <span>Clone: </span>
                    <span class="fa fa-clone remov" @click="$emit('clone-vertical')"></span>
                </label>
            </div>
            <div v-if="can_edit">
                <label>
                    <span>Delete: </span>
                    <span class="glyphicon glyphicon-remove remov" @click="$emit('delete-chart')"></span>
                </label>
            </div>
            <div>
                <label>
                    <span>Auto Updating: </span>
                    <span class="indeterm_check__wrap" style="margin-right: 25px;">
                        <span class="indeterm_check"
                              @click="aUpdChange"
                        >
                            <i v-if="!all_settings.no_auto_update" class="glyphicon glyphicon-ok group__icon"></i>
                        </span>
                    </span>
                </label>
            </div>
            <div v-if="all_settings.wait_for_update">
                <label>
                    <button class="btn btn-default blue-gradient full-width"
                            @click="$emit('refresh-chart')"
                            :style="$root.themeButtonStyle"
                    >Update</button>
                </label>
            </div>
            <template v-if="all_settings.elem_type === 'pivot_table'">
                <div>
                    <label>
                        <span>Alignment (V): </span>
                        <select class="form-control control-inline"
                                v-model="all_settings.vert_align"
                                @change="$emit('dimensions-changed', 1)"
                                style="padding: 6px 0px"
                        >
                            <option value="start">Top</option>
                            <option value="center">Middle</option>
                            <option value="end">Bottom</option>
                        </select>
                    </label>
                </div>
                <div>
                    <label>
                        <span>Alignment (H): </span>
                        <select class="form-control control-inline"
                                v-model="all_settings.hor_align"
                                @change="$emit('dimensions-changed', 1)"
                                style="padding: 6px 0px"
                        >
                            <option value="start">Left</option>
                            <option value="center">Middle</option>
                            <option value="end">Right</option>
                        </select>
                    </label>
                </div>
            </template>
        </div>
    </div>
</template>

<script>
    import MixinSmartPosition from '../../../../_Mixins/MixinSmartPosition';
    import CellStyleMixin from "../../../../_Mixins/CellStyleMixin";

    import TabldaColopicker from '../../../../CustomCell/InCell/TabldaColopicker';

    import {eventBus} from '../../../../../app';

    export default {
        name: "ChartDimensionsButton",
        components: {
            TabldaColopicker,
        },
        mixins: [
            MixinSmartPosition,
            CellStyleMixin,
        ],
        data: function () {
            return {
                fixed_pos: true,
                menu_opened: false,
                smart_wrapper: 'chart_dim_menu',
                smart_limit: 380,
            }
        },
        props:{
            all_settings: Object,
            can_edit: Boolean|Number,
            chart_hash: String,
        },
        methods: {
            specialStyle() {
                let style = this.ItemsListStyle();
                style.left = 'initial';
                style.right = (window.innerWidth - this.fix_left)+'px';
                style.width = '220px';
                return style;
            },
            setColor(clr, save) {
                if (save) {
                    this.$root.saveColorToPalette(clr);
                }
                this.all_settings.dimensions.back_color = clr;
                this.$emit('dimensions-changed');
            },
            menuOpn() {
                this.menu_opened = !this.menu_opened;
                this.showItemsList();
            },
            hideMenu(e) {
                let container = $(this.$refs.chart_button);
                let color_p = $('.colorpicker');
                if (container.has(e.target).length === 0 && color_p.has(e.target).length === 0){
                    this.menu_opened = false;
                }
            },
            aUpdChange() {
                this.all_settings.no_auto_update = !this.all_settings.no_auto_update;
                this.$emit('dimensions-changed');
                eventBus.$emit('bi-chart-redraw-highcharts', this.chart_hash);
            },
        },
        mounted() {
            eventBus.$on('global-click', this.hideMenu);
            eventBus.$on('global-keydown', this.hideMenu);
        },
        beforeDestroy() {
            eventBus.$off('global-click', this.hideMenu);
            eventBus.$off('global-keydown', this.hideMenu);
        }
    }
</script>

<style scoped>
    .chart_dimensions_button {
        cursor: pointer;
        display: inline-block;
        height: 26px;
        font-size: 1.2em;
        margin: 0 5px 0 5px;
        padding: 0;
        background-color: transparent;
        position: relative;
        outline: none;
    }
    .chart_dimensions_button > i {
        position: relative;
        top: 2px;
    }

    .chart_dimensions_menu {
        position: absolute;
        z-index: 500;
        padding: 5px;
        border: 1px solid #777;
        border-radius: 5px;
        background-color: #FFF;
        white-space: nowrap;
    }
    .chart_dimensions_menu label {
        font-size: 14px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .control-inline {
        display: inline-block;
        width: 65px;
        height: 36px;
        border: 1px solid #CCC;
        border-radius: 5px;
    }

    .color_picker_wrapper {
        position: relative;
        width: 65px;
        height: 35px;
    }

    .setup_btn {
        width: 35px;
        height: 30px;
        margin-right: 15px;
        padding: 0px;
    }

    .remov {
        width: 65px;
        text-align: center;
        font-size: 1.5em;
        color: #F00;
        line-height: 1.5em;
        cursor: pointer;
    }

    .form-group {
        margin-bottom: 5px;
    }
</style>