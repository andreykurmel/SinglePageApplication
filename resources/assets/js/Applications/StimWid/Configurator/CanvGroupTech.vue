<template>
    <div class="tech__wrap"
         :style="wrapStyle"
         @click="settings.selTech(tech)"
         @contextmenu.prevent="$emit('right-click', tech)"
    >
        {{ tech.technology }}
    </div>
</template>

<script>
    import {Tech} from "./Tech";
    import {Settings} from "./Settings";

    export default {
        name: 'CanvGroupTech',
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
                    border: this.settings.sel_tech && this.settings.sel_tech._id === this.tech._id ? '2px solid #F00' : 'none',
                };
            },
        },
        props: {
            tech: Tech,
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
    .tech__wrap {
        cursor: pointer;
    }
</style>