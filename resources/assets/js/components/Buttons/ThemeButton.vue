<template>
    <div ref="cell_button" class="cell_height_button" title="Table Themes" @click="menu_opened = !menu_opened">
        <img src="/assets/img/theme.png" height="35">
        <div v-show="menu_opened" class="cell_height_menu">
            <div v-for="th in themes" class="theme-color" :style="{backgroundColor: th}"></div>
        </div>
    </div>
</template>

<script>
    import {eventBus} from './../../app';

    export default {
        name: "ThemeButton",
        data: function () {
            return {
                menu_opened: false,
                themes: ['#FFF', '#000']
            }
        },
        props:{
        },
        methods: {
            hideMenu(e) {
                let container = $(this.$refs.cell_button);
                if (container.has(e.target).length === 0){
                    this.menu_opened = false;
                }
            }
        },
        created() {
            eventBus.$on('global-click', this.hideMenu);
            eventBus.$on('global-keydown', this.hideMenu);
        },
        beforeDestroy() {
            eventBus.$off('global-click', this.hideMenu);
            eventBus.$off('global-keydown', this.hideMenu);
        }
    }
</script>

<style lang="scss" scoped>
    .cell_height_button {
        margin-left: 15px;
        cursor: pointer;
        padding: 0;
        font-size: 22px;
        background-color: #FFF;
        position: relative;
        outline: none;
    }
    .cell_height_menu {
        position: absolute;
        right: 100%;
        top: 100%;
        z-index: 500;
        background-color: #FFF;
        border: 1px solid #CCC;
        padding: 5px;

        .theme-color {
            border: 2px solid #CCC;
            border-radius: 5px;
            width: 40px;
            height: 30px;
        }
    }
</style>