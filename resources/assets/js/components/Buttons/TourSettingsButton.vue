<template>
    <div ref="tour_sett_button" class="tour_settings_button" title="Brackets Module Settings" :style="$root.themeMainBgStyle">
        <i class="glyphicon glyphicon-cog" @click="menuOpened = !menuOpened" :style="{color: smartTextColor}"></i>
        <div v-show="menuOpened" class="tour_settings_menu" :style="specStyle">
            <div class="flex flex--center-v no-wrap">
                <div class="heading">Width, Team:&nbsp;&nbsp;&nbsp;</div>
                <div class="slider" ref="slider_p_team_width"></div>
                <div>&nbsp;&nbsp;&nbsp;</div>
                <input class="form-control"
                       v-model="selectedTournament.p_team_width"
                       @change="updateTrnmnt('p_team_width')"
                       :disabled="!withEdit"
                       style="width: 50px;"
                       :style="textSysStyle">
                <label>&nbsp;px</label>
            </div>
            <div class="flex flex--center-v no-wrap">
                <div class="heading">Width, Score:&nbsp;&nbsp;&nbsp;</div>
                <div class="slider" ref="slider_p_goal_width"></div>
                <div>&nbsp;&nbsp;&nbsp;</div>
                <input class="form-control"
                       v-model="selectedTournament.p_goal_width"
                       @change="updateTrnmnt('p_goal_width')"
                       :disabled="!withEdit"
                       style="width: 50px;"
                       :style="textSysStyle">
                <label>&nbsp;px</label>
            </div>
            <div class="flex flex--center-v no-wrap">
                <div class="heading">Space, Match:&nbsp;&nbsp;&nbsp;</div>
                <div class="slider" ref="slider_p_match_margin"></div>
                <div>&nbsp;&nbsp;&nbsp;</div>
                <input class="form-control"
                       v-model="selectedTournament.p_match_margin"
                       @change="updateTrnmnt('p_match_margin')"
                       :disabled="!withEdit"
                       style="width: 50px;"
                       :style="textSysStyle">
                <label>&nbsp;px</label>
            </div>
            <div class="flex flex--center-v no-wrap">
                <div class="heading">Space, Round:&nbsp;&nbsp;&nbsp;</div>
                <div class="slider" ref="slider_p_round_margin"></div>
                <div>&nbsp;&nbsp;&nbsp;</div>
                <input class="form-control"
                       v-model="selectedTournament.p_round_margin"
                       @change="updateTrnmnt('p_round_margin')"
                       :disabled="!withEdit"
                       style="width: 50px;"
                       :style="textSysStyle">
                <label>&nbsp;px</label>
            </div>
        </div>
    </div>
</template>

<script>
import {eventBus} from '../../app';

import CellStyleMixin from "../_Mixins/CellStyleMixin";

export default {
    name: "TourSettingsButton",
    components: {
    },
    mixins: [
        CellStyleMixin,
    ],
    data: function () {
        return {
            menuOpened: false,
            limits: {
                min: {
                    p_team_width: 50,
                    p_goal_width: 5,
                    p_match_margin: 25,
                    p_round_margin: 50,
                },
                max: {
                    p_team_width: 500,
                    p_goal_width: 100,
                    p_match_margin: 250,
                    p_round_margin: 250,
                },
            },
        }
    },
    props: {
        selectedTournament: Object,
        withEdit: Boolean,
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
        updateTrnmnt(key) {
            this.selectedTournament[key] = Math.max(this.selectedTournament[key], this.limits.min[key]);
            this.selectedTournament[key] = Math.min(this.selectedTournament[key], this.limits.max[key]);

            $( this.$refs['slider_' + key] ).slider( 'value', this.selectedTournament[key]);
        },
        hideMenu(e) {
            let container = $(this.$refs.tour_sett_button);
            let color_p = $('.colorpicker');
            if (container.has(e.target).length === 0 && color_p.has(e.target).length === 0 && this.menuOpened) {
                this.menuOpened = false;
                this.$emit('updated-tournament', this.selectedTournament);
            }
        },
        makeSlider(key) {
            $( this.$refs['slider_' + key] ).slider({
                range: false,
                min: to_float(this.limits.min[key]),
                max: to_float(this.limits.max[key]),
                value: to_float(this.selectedTournament[key]),
                slide: ( event, ui ) => {
                    this.selectedTournament[key] = to_float(ui.value);
                },
                stop: ( event, ui ) => {
                    this.updateTrnmnt(key);
                    $(this.$refs['slider_' + key]).find('.ui-slider-handle').css(this.$root.themeButtonStyle);
                }
            });
            $(this.$refs['slider_' + key]).find('.ui-slider-handle').css(this.$root.themeButtonStyle);
        },
    },
    mounted() {
        this.makeSlider('p_team_width');
        this.makeSlider('p_goal_width');
        this.makeSlider('p_match_margin');
        this.makeSlider('p_round_margin');

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
.tour_settings_button {
    cursor: pointer;
    display: inline-block;
    height: 30px;
    font-size: 2em;
    padding: 0;
    background-color: transparent;
    position: relative;
    outline: none;
}
.tour_settings_button > i {
    position: relative;
    top: 2px;
}

.tour_settings_menu {
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
.heading {
    width: 120px;
}
.slider {
    width: 150px;
}
</style>