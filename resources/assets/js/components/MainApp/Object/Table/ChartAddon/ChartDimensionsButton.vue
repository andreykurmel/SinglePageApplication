<template>
    <div ref="chart_button" class="chart_dimensions_button" title="Chart Dimensions">
        <i class="glyphicon glyphicon-cog" @click="menuOpn()"></i>
        <div v-show="menu_opened" class="chart_dimensions_menu" ref="chart_dim_menu" :style="ItemsListStyle()">
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
                            :saved_colors="$root.color_palette"
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
        </div>
    </div>
</template>

<script>
    import MixinSmartPosition from './../../../../CustomCell/Selects/MixinSmartPosition';

    import TabldaColopicker from '../../../../CustomCell/InCell/TabldaColopicker';

    import {eventBus} from '../../../../../app';

    export default {
        name: "ChartDimensionsButton",
        components: {
            TabldaColopicker,
        },
        mixins: [
            MixinSmartPosition,
        ],
        data: function () {
            return {
                menu_opened: false,
                smart_wrapper: 'chart_dim_menu',
                smart_limit: 5,
            }
        },
        props:{
            all_settings: Object,
            can_edit: Boolean,
        },
        methods: {
            setColor(clr, save) {
                if (save) {
                    this.$root.color_palette.unshift(clr);
                    localStorage.setItem('color_palette', this.$root.color_palette.join(','));
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
        right: 100%;
        top: 100%;
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