<template>
    <div ref="cell_button" class="cell_height_button" title="Table cell height">
        <button class="btn btn-primary btn-sm blue-gradient"
                @click="menu_opened = !menu_opened"
                :style="$root.themeButtonStyle"
        >
            <i class="fas fa-table"></i>
        </button>
        <div v-show="menu_opened" class="cell_height_menu">
            <table class="table">
                <colgroup>
                    <col width="48"/>
                    <col width="120"/>
                </colgroup>
                <thead>
                <tr>
                    <th>Max. Text Rows</th>
                    <th>Text Row Height</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>
                        <div class="max_rows">
                            <span class="indeterm_check__wrap">
                                <span class="indeterm_check" @click="maxAutoChanged()">
                                    <i v-if="max_auto" class="glyphicon glyphicon-ok group__icon"></i>
                                </span>
                            </span>
                            <span>Auto</span>
                        </div>
                        <div class="max_rows">
                            <input v-model="max_cell_rows" :disabled="!!max_auto" class="form-control" @change="rowsChanged()"/>
                        </div>
                    </td>
                    <td>
                        <div class="imgs_cell">
                            <img
                                :src="cellHeight === 1 ? '/assets/img/row_height_active.png' : '/assets/img/row_height_fade.png'"
                                height="24"
                                @click="heightChanged(1);">

                            <img
                                :src="cellHeight === 2 ? '/assets/img/row_height_active.png' : '/assets/img/row_height_fade.png'"
                                height="32"
                                @click="heightChanged(2);">

                            <img
                                :src="cellHeight === 3 ? '/assets/img/row_height_active.png' : '/assets/img/row_height_fade.png'"
                                height="48"
                                @click="heightChanged(3);">

                            <img
                                :src="cellHeight === 5 ? '/assets/img/row_height_active.png' : '/assets/img/row_height_fade.png'"
                                height="64"
                                @click="heightChanged(5);">
                        </div>
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
        name: "CellHeightButton",
        mixins: [
        ],
        data: function () {
            return {
                menu_opened: false,
                max_auto: !Number(this.maxCellRows),
                max_cell_rows: this.maxCellRows,
                sizes: [1,2,3,5],
            }
        },
        props:{
            table_meta: Object,
            cellHeight: Number,
            maxCellRows: Number,
        },
        watch: {
            maxCellRows(val) {
                this.max_cell_rows = val;
                this.max_auto = !Number(val);
            },
        },
        methods: {
            maxAutoChanged() {
                this.max_auto = !this.max_auto;
                this.$emit('change-max-cell-rows', this.max_auto ? null : Number(this.max_cell_rows), this.table_meta);
            },
            rowsChanged() {
                this.max_cell_rows = Number(this.max_cell_rows);
                this.max_cell_rows = Math.max(1, this.max_cell_rows);
                this.max_cell_rows = Math.min(20, this.max_cell_rows);//max_he_for_auto_rows: 20

                this.$emit('change-max-cell-rows', this.max_cell_rows, this.table_meta);
                this.menu_opened = false;
            },
            heightChanged(val) {
                this.$emit('change-cell-height', val, this.table_meta);
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

            if (this.sizes.indexOf(this.cellHeight) === -1) {
                this.heightChanged(2);
            }
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
        width: 32px;
        height: 32px;
        padding: 0;
        font-size: 22px;
        background-color: transparent;
        position: relative;
        outline: none;
    }
    .cell_height_button > button {
        border-radius: 50%;
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
            width: 164px;
            font-size: 13px;
        }

        .imgs_cell {
            display: flex;
            align-items: flex-end;
            img {
                padding: 0 7px;
                cursor: pointer;
            }
        }
        .max_rows {
            display: inline-block;
            font-size: 14px;
            width: 48px;
        }
    }
</style>