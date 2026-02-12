<template>
    <div class="elev__wrap"
         :style="wrapStyle"
         @click="settings.selElev(elev)"
         @contextmenu.prevent="$emit('right-click', elev)"
    >
        {{ elev.elev }}
    </div>
</template>

<script>
    import {Elev} from "./Elev";
    import {Settings} from "./Settings";

    export default {
        name: 'CanvGroupElev',
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
                    backgroundColor: this.elev.color,
                    border: this.settings.sel_elev && this.settings.sel_elev._id === this.elev._id ? '2px solid #F00' : 'none',
                };
            },
        },
        props: {
            elev: Elev,
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