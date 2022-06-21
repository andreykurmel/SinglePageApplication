<template>
    <div ref="bi_sett_button" class="bi_settings_button" title="BI Module Settings">
        <i class="glyphicon glyphicon-cog" @click="menu_opened = !menu_opened"></i>
        <div v-show="menu_opened" class="bi_settings_menu" :style="textSysStyle">
            <div>
                <label>
                    <span class="indeterm_check"
                          @click="bisett.avail_fix_layout ? saveSettMetasCheckbox('fix_layout') : null"
                          :class="{'disabled': !bisett.avail_fix_layout}"
                          :style="$root.checkBoxStyle"
                    >
                        <i v-if="bisett.fix_layout" class="glyphicon glyphicon-ok group__icon"></i>
                    </span>
                    <span>&nbsp;Lock Layout</span>
                </label>
            </div>
            <div>
                <label>
                    <span class="indeterm_check"
                          @click="bisett.avail_can_add ? saveSettMetasCheckbox('can_add') : null"
                          :class="{'disabled': !bisett.avail_can_add}"
                          :style="$root.checkBoxStyle"
                    >
                        <i v-if="bisett.can_add" class="glyphicon glyphicon-ok group__icon"></i>
                    </span>
                    <span>&nbsp;Add New</span>
                </label>
            </div>
            <div>
                <label>
                    <span class="indeterm_check"
                          @click="bisett.avail_hide_settings ? saveSettMetasCheckbox('hide_settings') : null"
                          :class="{'disabled': !bisett.avail_hide_settings}"
                          :style="$root.checkBoxStyle"
                    >
                        <i v-if="bisett.hide_settings" class="glyphicon glyphicon-ok group__icon"></i>
                    </span>
                    <span>&nbsp;Hide Settings</span>
                </label>
            </div>
            <div>
                <label>
                    <span>Block spacing&nbsp;</span>
                    <input class="form-control"
                           v-model="bisett.cell_spacing"
                           :disabled="!bisett.avail_cell_spacing"
                           @change="saveSettMetas('cell_spacing')"
                           style="width: 50px;"/>
                    <span>px</span>
                </label>
            </div>
            <div>
                <label>
                    <span>Block vertical grid step&nbsp;</span>
                    <input class="form-control"
                           v-model="bisett.cell_height"
                           :disabled="!bisett.avail_cell_height"
                           @change="saveSettMetas('cell_height')"
                           style="width: 50px;"/>
                    <span>px</span>
                </label>
            </div>
        </div>
    </div>
</template>

<script>
    import {ChartFunctions} from '../MainApp/Object/Table/ChartAddon/ChartFunctions';
    
    import {eventBus} from '../../app';

    import CellStyleMixin from "../_Mixins/CellStyleMixin";

    export default {
        name: "BiSettingsButton",
        components: {
        },
        mixins: [
            CellStyleMixin,
        ],
        data: function () {
            return {
                menu_opened: false,
                bisett: {},
                old_cell_height: 0,
            }
        },
        props: {
            table_id: Number,
            tableMeta: Object,
        },
        watch: {
            table_id(val) {
                this.checkSaved();
            }
        },
        methods: {
            saveSettMetasCheckbox(prop) {
                if (prop) {
                    this.bisett[prop] = !this.bisett[prop];
                }
                this.saveSettMetas(prop);
            },
            saveSettMetas(prop) {
                this.bisett.cell_spacing = Math.round( to_float(this.bisett.cell_spacing) / 5 ) * 5;
                this.bisett.cell_height = Math.round( to_float(this.bisett.cell_height) / 5 ) * 5;
                this.bisett.cell_height = Math.max(this.bisett.cell_height, 5);
                /*if (this.old_cell_height !== this.bisett.cell_height) {
                    eventBus.$emit('recalc-bi-height', this.old_cell_height / this.bisett.cell_height, this.bisett.cell_height);
                    this.old_cell_height = this.bisett.cell_height;
                }*/

                ChartFunctions.saveSett(this.tableMeta, this.$root);
                if (prop === 'cell_spacing') {
                    eventBus.$emit('bi-view-recreate');
                } else {
                    eventBus.$emit('bi-view-changed-settings');
                }
            },
            hideMenu(e) {
                let container = $(this.$refs.bi_sett_button);
                let color_p = $('.colorpicker');
                if (container.has(e.target).length === 0 && color_p.has(e.target).length === 0){
                    this.menu_opened = false;
                }
            },
            checkSaved() {
                this.bisett = ChartFunctions.settsFromMeta(this.tableMeta, this);
                this.old_cell_height = this.bisett.cell_height;
            },
        },
        mounted() {
            this.checkSaved();
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
    .bi_settings_button {
        cursor: pointer;
        display: inline-block;
        height: 30px;
        font-size: 2em;
        padding: 0;
        background-color: transparent;
        position: relative;
        outline: none;
    }
    .bi_settings_button > i {
        position: relative;
        top: 2px;
    }

    .bi_settings_menu {
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
    .bi_settings_menu label {
        font-size: 14px;
        display: flex;
        align-items: center;
    }
    .indeterm_check {
        border: 1px solid #AAA;
        border-radius: 3px;
        cursor: pointer;
        background-color: #DDD;
    }
    .bi_settings_menu .indeterm_check i {
        top: -4px !important;
    }
</style>