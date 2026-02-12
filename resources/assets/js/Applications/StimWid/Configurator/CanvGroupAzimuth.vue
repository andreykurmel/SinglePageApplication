<template>
    <div class="elev__wrap"
         :style="wrapStyle"
         @click="settings.selAzimuth(azimuth)"
         @contextmenu.prevent="$emit('right-click', azimuth)"
    >
        {{ azimuth.deg }}
    </div>
</template>

<script>
    import {Elev} from "./Elev";
    import {Settings} from "./Settings";
    import {Azimuth} from "./Azimuth";

    export default {
        name: 'CanvGroupAzimuth',
        mixins: [
        ],
        components: {
        },
        data() {
            return {
            }
        },
        computed: {
            wrapStyle() {
                return {
                    border: this.settings.sel_azimuth && this.settings.sel_azimuth._id === this.azimuth._id ? '2px solid #F00' : 'none',
                };
            },
        },
        props: {
            azimuth: Azimuth,
            settings: Settings,
        },
        watch: {
        },
        methods: {
        },
        mounted() {
        },
        beforeDestroy() {
        }
    }
</script>

<style lang="scss" scoped>
    .elev__wrap {
        cursor: pointer;
    }
</style>