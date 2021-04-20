<template>
    <div ref="charts_per_row_button" class="blue-gradient charts_per_row_button" title="Set rows per page">
        <span class="blue-gradient val">{{ charts_per_row }}</span>
        <span class="blue-gradient sel" @click="menu_opened = !menu_opened"><i class="fa fa-sort"></i></span>
        <div v-show="menu_opened" class="blue-gradient charts_per_row_menu">
            <span v-for="key in select_list"
                  :class="[charts_per_row === Number(key) ? 'gray-gradient' : 'blue-bk']"
                  @click="$emit('val-changed', Number(key)); menu_opened = false;"
            >
                {{ key }}
            </span>
        </div>
    </div>
</template>

<script>
    import {eventBus} from './../../app';

    export default {
        name: "ChartsPerRowButton",
        data: function () {
            return {
                menu_opened: false,
                select_list: [1, 2, 3]
            }
        },
        props:{
            charts_per_row: Number,
        },
        methods: {
            hideMenu(e) {
                let container = $(this.$refs.charts_per_row_button);
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

<style scoped>
    .charts_per_row_button {
        position: relative;
        width: 70px;
        height: 30px;
        outline: none;
        font-weight: normal;
        border-radius: 4px;
    }
    .charts_per_row_button > span {
        position: absolute;
        width: 45px;
        height: 28px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 4px 0 0 4px;
    }
    .charts_per_row_button > .sel {
        left: 45px;
        width: 25px;
        cursor: pointer;
        border-radius: 0 4px 4px 0;
    }
    .charts_per_row_menu {
        position: absolute;
        top: 100%;
        width: 70px;
        z-index: 1200;
        border-radius: 0 0 4px 4px;
    }
    .charts_per_row_menu > span {
        font-size: 1em;
        font-weight: bold;
        width: 100%;
        padding: 2px 5px;
        display: inline-block;
    }
    .blue-bk {
        background: #045ab2;
    }
    .blue-bk:hover {
        background: #004995;
    }
</style>