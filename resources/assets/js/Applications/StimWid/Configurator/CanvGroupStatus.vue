<template>
    <div class="status__wrap"
         :style="wrapStyle"
         @click="settings.selStatus(status)"
         @contextmenu.prevent="$emit('right-click', status)"
    >
        {{ status.name }}
    </div>
</template>

<script>
    import {Status} from "./Status";
    import {Settings} from "./Settings";

    export default {
        name: 'CanvGroupStatus',
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
                    backgroundColor: this.status.color,
                    border: this.settings.sel_status && this.settings.sel_status._id === this.status._id ? '2px solid #F00' : 'none',
                };
            },
        },
        props: {
            status: Status,
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
    .status__wrap {
        cursor: pointer;
    }
</style>