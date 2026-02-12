<template>
    <div class="elev__wrap" v-if="draw_avail">
        <div :style="eleStyle">
            <div class="elevs">{{ elev.elev }}</div>
        </div>
    </div>
</template>

<script>
    import {Elev} from "./Elev";
    import {Settings} from "./Settings";

    import GlobalPosMixin from "./GlobalPosMixin.vue";

    export default {
        name: 'CanvGroupLibElev',
        mixins: [
            GlobalPosMixin,
        ],
        components: {
        },
        data() {
            return {
                draw_avail: true,
            }
        },
        computed: {
            eleStyle() {
                return {
                    top: this.get_top_pos_px(this.elev.elev)+'px',
                    left: 0,
                    right: 0,
                    borderTop: '1px dashed #ccc',
                    position: 'absolute',
                }
            },
        },
        props: {
            elev: Elev,
            settings: Settings,
            px_in_ft: Number,
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
        .elevs {
            position: absolute;
            left: 100%;
            top: -10px;
            font-size: 12px;
            color: #777;
        }
    }
</style>