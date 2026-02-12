<template>
    <div ref="ckeditor_sett_button" class="ckeditor_settings_button" title="Editor Module Settings" :style="$root.themeMainBgStyle">
        <i class="glyphicon glyphicon-cog" @click="menuOpened = !menuOpened" :style="{color: smartTextColor}"></i>
        <div v-show="menuOpened" class="ckeditor_settings_menu" :style="specStyle">
            <div class="flex flex--center-v no-wrap">
                <div>Width:&nbsp;</div>
                <select style="width: 150px;"
                        class="form-control"
                        @change="emitUpd()"
                        v-model="targetRow.email_link_width_type"
                        :disabled="is_disabled"
                        :style="textSysStyle">
                    <option value="full">Full Body</option>
                    <option value="content">Fit Content</option>
                    <option value="column_size">Fixed (ea. col.)</option>
                    <option value="total_size">Fixed (Total)</option>
                </select>

                <template v-if="targetRow.email_link_width_type === 'column_size' || targetRow.email_link_width_type === 'total_size'">
                    <input style="width: 80px;"
                           class="form-control input-sm"
                           type="number"
                           @change="emitUpd()"
                           v-model="targetRow.email_link_width_size"
                           :disabled="is_disabled"
                           :style="textSysStyle">
                    <div>px</div>
                </template>
            </div>

            <div class="flex flex--center-v no-wrap">
                <div>Align:&nbsp;</div>
                <select style="width: 80px;"
                        class="form-control"
                        @change="emitUpd()"
                        v-model="targetRow.email_link_align"
                        :disabled="is_disabled"
                        :style="textSysStyle">
                    <option value="left">Left</option>
                    <option value="center">Center</option>
                    <option value="right">Right</option>
                </select>
            </div>
        </div>
    </div>
</template>

<script>
import {eventBus} from '../../app';

import CellStyleMixin from "../_Mixins/CellStyleMixin";

export default {
    name: "CkeditorSettingsButton",
    components: {
    },
    mixins: [
        CellStyleMixin,
    ],
    data: function () {
        return {
            menuOpened: false,
        }
    },
    props: {
        targetRow: Object,
        is_disabled: Boolean,
    },
    computed: {
        specStyle() {
            return {
                ...this.textSysStyleSmart,
                ...this.$root.themeMainBgStyle,
            };
        },
    },
    methods: {
        emitUpd() {
            this.$emit('updated-ckeditor', this.selectedTournament);
        },
        hideMenu(e) {
            let container = $(this.$refs.ckeditor_sett_button);
            let color_p = $('.colorpicker');
            if (container.has(e.target).length === 0 && color_p.has(e.target).length === 0) {
                this.menuOpened = false;
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
.ckeditor_settings_button {
    cursor: pointer;
    display: inline-block;
    height: 30px;
    font-size: 2em;
    padding: 0;
    background-color: transparent;
    position: relative;
    outline: none;
}
.ckeditor_settings_button > i {
    position: relative;
    top: 2px;
}

.ckeditor_settings_menu {
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
.input-sm {
    height: 36px;
}
</style>