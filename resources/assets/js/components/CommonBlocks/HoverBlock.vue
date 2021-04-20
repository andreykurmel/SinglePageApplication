<template>
    <div class="popup_wrap" ref="popup_ref" :style="hovPos" @mouseleave="$emit('tooltip-blur')">
        <div v-if="extra_title" style="text-align: left;font-size: 1.2em;">
            <span v-html="extra_title"></span>
            <span class="glyphicon glyphicon-remove pull-right" @click="$emit('another-click')"></span>
        </div>
        <div v-if="extra_title" style="border-bottom: 1px solid #000; margin: 3px -6px;"></div>
        <div v-html="html_str"></div>
    </div>
</template>

<script>
    import {eventBus} from './../../app';

    export default {
        name: "HoverBlock",
        mixins: [
        ],
        components: {
        },
        data: function () {
            return {
                prevent: true,
            };
        },
        computed: {
            hovWidth() {
                return Math.min(this.html_str.length*7.5 + 12, 300);
            },
            hovPos() {
                let style = {};
                let c_off = to_float(this.c_offset);

                let x = (this.p_left || this.$root.lastMouseClick.clientX) - 5;
                style.left = (x + this.hovWidth) < window.innerWidth
                    ? x + c_off
                    : x - this.hovWidth - c_off;

                let y = (this.p_top || this.$root.lastMouseClick.clientY) - 5;
                style.top = (y + this.htmlHe) < window.innerHeight
                    ? y + c_off
                    : y - this.htmlHe - c_off;

                style.top += 'px';
                style.left += 'px';
                return style;
            },
            htmlHe() {
                return this.html_str.length * 8 / 300 * 24;
            },
        },
        props:{
            extra_title: String,
            bg_color: String,
            html_str: String,
            p_top: Number,
            p_left: Number,
            c_offset: Number,
        },
        methods: {
        },
        created() {
            eventBus.$on('global-click', () => {
                this.$emit('another-click');
            });
        },
    }
</script>

<style lang="scss" scoped>
    @keyframes popup_fadein {
        0% {
            opacity: 0;
        }
        100% {
            opacity: 1;
        }
    }

    .popup_wrap {
        animation-name: popup_fadein;
        animation-duration: 1s;
        position: fixed;
        z-index: 5000;
        padding: 2px 6px;
        font-weight: bold;
        color: #FFF;
        background-color: #444;
        border: 1px solid #000;
        border-radius: 5px;
        max-width: 25%;
        transition: opacity 1s;
        white-space: normal;
    }
</style>