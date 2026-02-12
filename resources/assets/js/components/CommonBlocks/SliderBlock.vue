<template>
    <div class="full-height">
        <div ref="jquery_slider" class="slider-wrapper"></div>
    </div>
</template>

<script>
    export default {
        name: "SliderBlock",
        mixins: [
        ],
        components: {
        },
        data: function () {
            return {
            };
        },
        computed: {
        },
        watch: {
            cur(val) {
                $( this.$refs.jquery_slider ).slider('value', val);
            },
        },
        props:{
            min: Number,
            max: Number,
            cur: Number,
            step: Number,
        },
        methods: {
            slideHandler(event, ui) {
                this.$emit('slide', ui.value);
            },
            stopHandler(event, ui) {
                $(this.$refs.jquery_slider).find('.ui-slider-handle').css(this.$root.themeButtonStyle);
            },
        },
        mounted() {
            $( this.$refs.jquery_slider ).slider({
                min: this.min,
                max: this.max,
                step: this.step,
                value: this.cur,
                slide: this.slideHandler,
                stop: this.stopHandler,
            });
            $(this.$refs.jquery_slider).find('.ui-slider-handle').css(this.$root.themeButtonStyle);
        }
    }
</script>

<style lang="scss" scoped>
    .slider-wrapper {
        /**/
    }
</style>