<template>
    <div ref="rowperpage_button"
         class="blue-gradient row_per_page_button"
         title="# of records to be displayed per page"
         :style="$root.themeButtonStyle"
    >
        <span class="blue-gradient val" :style="buttonNoBorder">{{ select_list[rowPerPage] }}</span>
        <span class="blue-gradient sel" :style="buttonNoBorder" @click="menu_opened = !menu_opened"><i class="fa fa-sort"></i></span>
        <div v-show="menu_opened" class="blue-gradient row_per_page_menu" :style="$root.themeButtonStyle">
            <span v-for="(val, key) in select_list"
                  :class="[rowPerPage === Number(key) ? 'gray-gradient' : 'blue-bk']"
                  :style="$root.themeMainBgStyle"
                  @click="$emit('val-changed', Number(key)); menu_opened = false;"
            >
                {{ val }}
            </span>
        </div>
    </div>
</template>

<script>
    import {eventBus} from './../../app';

    export default {
        name: "RowPerPageButton",
        mixins: [
        ],
        data: function () {
            return {
                menu_opened: false,
                select_list: {
                    '10': 10,
                    '20': 20,
                    '50': 50,
                    '100': 100,
                    '200': 200,
                    //'0': 'All'
                }
            }
        },
        props:{
            rowPerPage: Number,
        },
        computed: {
            buttonNoBorder() {
                let style = _.cloneDeep(this.$root.themeButtonStyle);
                style.border = 'none';
                return style;
            }
        },
        methods: {
            hideMenu(e) {
                let container = $(this.$refs.rowperpage_button);
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
    .row_per_page_button {
        position: relative;
        width: 45px;
        height: 28px;
        outline: none;
        font-weight: normal;
        border-radius: 4px;
    }
    .row_per_page_button > span {
        position: absolute;
        width: 30px;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 4px 0 0 4px;
    }
    .row_per_page_button > .sel {
        right: 0;
        width: 15px;
        cursor: pointer;
        border-radius: 0 4px 4px 0;
    }
    .row_per_page_menu {
        position: absolute;
        bottom: 100%;
        width: 45px;
        z-index: 500;
        border-radius: 0 0 4px 4px;
    }
    .row_per_page_menu > span {
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