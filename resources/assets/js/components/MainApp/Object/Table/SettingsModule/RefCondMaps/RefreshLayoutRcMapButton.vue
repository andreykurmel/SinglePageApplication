<template>
    <div ref="show_button" class="show_hide_button" title="Hide/Show Single or Group of Tables/RCs">
        <button class="btn btn-default blue-gradient flex flex--center"
                :style="$root.themeButtonStyle"
                @click="openMenu"
        >
            <img src="/assets/img/replace1.png"/>
        </button>

        <div v-show="menu_opened" class="show_hide_menu">
            <div class="menu_part" style="width: 145px; font-size: 12px;">
                <label style="font-size: 14px;">Refresh the layout of:</label>
                <button class="btn btn-default blue-gradient flex flex--center full-width mb5"
                        :style="$root.themeButtonStyle"
                        title="Left column"
                        @click="refresh('left')"
                >Other tables</button>
                <button class="btn btn-default blue-gradient flex flex--center full-width mb5"
                        :style="$root.themeButtonStyle"
                        title="Center column"
                        @click="refresh('center')"
                >RCs to other tables</button>
                <button class="btn btn-default blue-gradient flex flex--center full-width mb5"
                        :style="$root.themeButtonStyle"
                        title="Right column"
                        @click="refresh('right')"
                >RCs to THIS table</button>
            </div>
        </div>
    </div>
</template>

<script>
    import {eventBus} from "../../../../../../app";

    export default {
        components: {
        },
        mixins: [
        ],
        name: "RefreshLayoutRcMapButton",
        data() {
            return {
                menu_opened: false,
            }
        },
        props: {
        },
        computed: {
        },
        methods: {
            refresh(column) {
                this.$emit('refresh-layout', column);
            },
            openMenu() {
                this.menu_opened = ! this.menu_opened;
            },
            hideMenu(e) {
                let container = $(this.$refs.show_button);
                if (container.has(e.target).length === 0){
                    this.menu_opened = false;
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

<style scoped lang="scss">
    @import "../../../../../Buttons/ShowHide";
</style>