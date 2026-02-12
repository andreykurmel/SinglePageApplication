<template>
    <div ref="bi_sett_button" class="bi_settings_button" title="BI Module Settings">
        <i class="glyphicon glyphicon-cog" @click="menu_opened = !menu_opened" :style="{color: smartTextColor}"></i>
        <div v-show="menu_opened" class="bi_settings_menu" :style="textSysStyle">
            <div>
                <label>
                    <span class="indeterm_check"
                          @click="can('bi_fix_layout') ? saveSettMetasCheckbox('bi_fix_layout') : null"
                          :class="{'disabled': !can('bi_fix_layout')}"
                          :style="$root.checkBoxStyle"
                    >
                        <i v-if="biSettings.bi_fix_layout" class="glyphicon glyphicon-ok group__icon"></i>
                    </span>
                    <span>&nbsp;Lock Layout</span>
                </label>
            </div>
            <div>
                <label>
                    <span class="indeterm_check"
                          @click="can('bi_can_add') ? saveSettMetasCheckbox('bi_can_add') : null"
                          :class="{'disabled': !can('bi_can_add')}"
                          :style="$root.checkBoxStyle"
                    >
                        <i v-if="biSettings.bi_can_add" class="glyphicon glyphicon-ok group__icon"></i>
                    </span>
                    <span>&nbsp;Add New</span>
                </label>
            </div>
            <div>
                <label>
                    <span class="indeterm_check"
                          @click="can('bi_can_settings') ? saveSettMetasCheckbox('bi_can_settings') : null"
                          :class="{'disabled': !can('bi_can_settings')}"
                          :style="$root.checkBoxStyle"
                    >
                        <i v-if="!biSettings.bi_can_settings" class="glyphicon glyphicon-ok group__icon"></i>
                    </span>
                    <span>&nbsp;Hide Settings</span>
                </label>
            </div>
            <div>
                <label>
                    <span>Block spacing&nbsp;</span>
                    <input class="form-control"
                           v-model="biSettings.bi_cell_spacing"
                           :disabled="!can('bi_cell_spacing')"
                           @change="saveSettMetas('bi_cell_spacing')"
                           style="width: 50px;"/>
                    <span>px</span>
                </label>
            </div>
            <div>
                <label>
                    <span>Block vertical grid step&nbsp;</span>
                    <input class="form-control"
                           v-model="biSettings.bi_cell_height"
                           :disabled="!can('bi_cell_height')"
                           @change="saveSettMetas('bi_cell_height')"
                           style="width: 50px;"/>
                    <span>px</span>
                </label>
            </div>
            <div>
                <label>
                    <span>Corner radius&nbsp;</span>
                    <input class="form-control"
                           v-model="biSettings.bi_corner_radius"
                           :disabled="!can('bi_corner_radius')"
                           @change="saveSettMetas('bi_corner_radius')"
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
            old_cell_height: 0,
        }
    },
    props: {
        tableMeta: Object,
        biSettings: Object,
    },
    methods: {
        can(key) {
            return this.biSettings[key] !== undefined && !this.$root.sm_msg_type;
        },
        saveSettMetasCheckbox(prop) {
            if (prop) {
                this.biSettings[prop] = !this.biSettings[prop];
            }
            this.saveSettMetas(prop);
        },
        saveSettMetas(prop) {
            this.biSettings.bi_cell_spacing = Math.round( to_float(this.biSettings.bi_cell_spacing) / 5 ) * 5;
            this.biSettings.bi_cell_height = Math.round( to_float(this.biSettings.bi_cell_height) / 5 ) * 5;

            this.biSettings.bi_cell_height = Math.max(this.biSettings.bi_cell_height, 5);
            this.biSettings.bi_cell_spacing = Math.max(this.biSettings.bi_cell_spacing, 0);
            this.biSettings.bi_corner_radius = Math.max(this.biSettings.bi_corner_radius, 0);

            if (this.old_cell_height !== this.biSettings.bi_cell_height) {
                eventBus.$emit('bi-chart-recalc-height', this.biSettings.id, this.old_cell_height / this.biSettings.bi_cell_height, this.biSettings.bi_cell_height);
                this.old_cell_height = this.biSettings.bi_cell_height;
            }

            this.$emit('updated-row', this.biSettings);
            eventBus.$emit('bi-tab-changed-settings', prop);
        },
        hideMenu(e) {
            let container = $(this.$refs.bi_sett_button);
            let color_p = $('.colorpicker');
            if (container.has(e.target).length === 0 && color_p.has(e.target).length === 0){
                this.menu_opened = false;
            }
        },
        checkSaved() {
            this.old_cell_height = this.biSettings.bi_cell_height;
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