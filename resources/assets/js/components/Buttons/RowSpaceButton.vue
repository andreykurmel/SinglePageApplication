<template>
    <div ref="cell_button" class="cell_height_button" title="Row Space">
        <button class="btn btn-primary btn-sm blue-gradient"
                @click="menu_opened = disabled_btn ? false : !menu_opened"
                :disabled="disabled_btn"
                :style="$root.themeButtonStyle"
        >
            <img src="/assets/img/elevator-dn1.png" width="15" height="15"/>
        </button>
        <div v-show="menu_opened" class="cell_height_menu">
            <table class="table">
                <colgroup>
                    <col width="120"/>
                </colgroup>
                <thead>
                <tr>
                    <th>Row Spacing:</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>
                        <div class="flex">
                            <input class="form-control"
                                   v-model="selected_size"
                                   :disabled="disabled_btn"
                                   @change="heightChanged()"
                                   style="text-align: center;"/>
                            <button v-if="!disabled_btn && selected_size"
                                    class="btn btn-danger btn-sm flex flex--center"
                                    style="padding: 0 5px;"
                                    @click.stop.prevent="selected_size = 0.5; heightChanged();"
                            >
                                <span style="font-size: 26px">×</span>
                            </button>
                        </div>
                        <div style="text-align: center; font-weight: bold; font-size: 13px;">x Row Height</div>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script>
    import {eventBus} from './../../app';

    export default {
        name: "RowSpaceButton",
        mixins: [
        ],
        data: function () {
            return {
                def: 50,
                menu_opened: false,
                selected_size: this.selVal(this.init_size),
            }
        },
        props:{
            init_size: Number,
            disabled_btn: Boolean,
        },
        methods: {
            selVal(val) {
                return Number((val || this.def) / 100).toFixed(2);
            },
            heightChanged() {
                let val = this.selected_size;
                val = parseInt(val * 100) || this.def;
                val = val > 0 ? val : this.def;

                this.selected_size = this.selVal(val);
                this.$emit('changed-space', val);
                this.menu_opened = false;
            },
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
    @import "./../CustomTable/Table.scss";

    .cell_height_button {
        cursor: pointer;
        width: 24px;
        height: 32px;
        padding: 0;
        font-size: 22px;
        background-color: transparent;
        position: relative;
        outline: none;
    }
    .cell_height_button > button {
        width: 100%;
        height: 100%;
        font-size: 0.7em;
        padding: 0;
        display: flex;
        justify-content: center;
        align-items: center;

        .fas {
            padding: 0;
            margin: 0;
        }
    }
    .cell_height_menu {
        position: absolute;
        right: 100%;
        top: 100%;
        z-index: 500;
        background-color: #FFF;
        border: 1px solid #CCC;

        .table {
            width: 120px;
            font-size: 13px;
        }

        .max_rows {
            display: inline-block;
            font-size: 14px;
            width: 48px;
        }
    }
</style>