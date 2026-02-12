<template>
    <a ref="cell_button" @click="aClick()">
        <input v-if="menu_opened" ref="inp" class="form-control" v-model="page" @keydown="keyDown()"/>
        <span v-else>...</span>
    </a>
</template>

<script>
    import {eventBus} from '../../../app';

    export default {
        name: "PaginationButton",
        data: function () {
            return {
                page: null,
                menu_opened: false
            }
        },
        methods: {
            hideMenu(e) {
                let container = $(this.$refs.cell_button);
                if (container.has(e.target).length === 0){
                    this.menu_opened = false;
                }
            },
            keyDown() {
                if (window.event.keyCode === 13) {
                    this.$emit('change-page', Number(this.page));
                }
            },
            aClick() {
                this.menu_opened = true;
                this.$nextTick(function () {
                    if(this.$refs.inp) {
                        this.$refs.inp.focus();
                    }
                })
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
    a {
        position: relative;
        width: 35px;

        input {
            position: absolute;
            top: 0;
            left: 0;
            height: 100%;
            width: 100%;
        }
        .form-control {
            padding: 3px 0 0 0;
            text-align: center;
        }
    }
</style>