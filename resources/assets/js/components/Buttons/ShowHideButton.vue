<template>
    <div ref="show_button" class="show_hide_button" title="Hide/Show Single or Group of Rows/Columns">
        <button class="btn btn-default blue-gradient flex flex--center"
                :style="$root.themeButtonStyle"
                @click="menu_opened = !menu_opened"
        >
            <img src="/assets/img/show_icon1.png"/>
        </button>

        <div v-show="menu_opened" class="show_hide_menu">
            <template v-if="!only_columns">
                <div class="menu_part">
                    <div class="title-elem">
                        <i class="fas fa-plus" @click="showGroupingSettings('row')"></i>
                        <label>Row Groups:</label>
                    </div>
                    <div v-if="tableMeta._row_groups.length">
                        <label>
                            <span class="indeterm_check__wrap">
                                <span class="indeterm_check" @click="toggleAllRows()">
                                    <i v-if="rowsAllChecked() == 2" class="glyphicon glyphicon-ok group__icon"></i>
                                    <i v-if="rowsAllChecked() == 1" class="glyphicon glyphicon-minus group__icon"></i>
                                </span>
                            </span>
                            <span> Check/Uncheck All</span>
                        </label>
                    </div>
                    <div v-for="row_gr in tableMeta._row_groups" v-show="!row_gr._group_hidden">
                        <label :style="{backgroundColor: (row_gr._show_status ? '#CCC;' : ''), opacity: (row_gr._filter_disabled ? '0.5' : '')}"
                               :title="row_gr._filter_disabled ? 'Disabled by Filters' : ''"
                               :class="row_gr._filter_disabled ? 'disabled' : ''"
                        >
                            <span class="indeterm_check__wrap">
                                <span class="indeterm_check"
                                      :class="row_gr._filter_disabled ? 'disabled' : ''"
                                      @click="rowToggled(row_gr)"
                                >
                                    <i v-if="row_gr._show_status && row_gr._show_status == 2" class="glyphicon glyphicon-ok group__icon"></i>
                                    <i v-if="row_gr._show_status && row_gr._show_status == 1" class="glyphicon glyphicon-minus group__icon"></i>
                                </span>
                            </span> {{ row_gr.name }}
                        </label>
                    </div>
                </div>
                <div class="vert_line"></div>
            </template>
            <div class="menu_part">
                <template v-if="!only_columns">
                    <div class="title-elem">
                        <i class="fas fa-plus" @click="showGroupingSettings('col')"></i>
                        <label>Column Groups:</label>
                    </div>
                    <div v-if="meta_column_groups.length">
                        <label>
                            <span class="indeterm_check__wrap">
                                <span class="indeterm_check" @click="toggleAllColumns()">
                                    <i v-if="columnsAllChecked == 2" class="glyphicon glyphicon-ok group__icon"></i>
                                    <i v-if="columnsAllChecked == 1" class="glyphicon glyphicon-minus group__icon"></i>
                                </span>
                            </span>
                            <span> Check/Uncheck All</span>
                        </label>
                    </div>
                    <div v-for="col_gr in meta_column_groups">
                        <label :style="{backgroundColor: (colGroupChecked(col_gr) ? '#CCC;' : '')}">
                            <span class="indeterm_check__wrap">
                                <span class="indeterm_check" @click="colGroupToggle(col_gr)">
                                    <i v-if="colGroupChecked(col_gr) && !colGroupIndeterm(col_gr)" class="glyphicon glyphicon-ok group__icon"></i>
                                    <i v-if="colGroupChecked(col_gr) && colGroupIndeterm(col_gr)" class="glyphicon glyphicon-minus group__icon"></i>
                                </span>
                            </span> {{ col_gr.name }}
                        </label>
                    </div>
                </template>

                <div class="title-elem">
                    <label>Single Columns:</label>
                </div>
                <fields-checker
                        :table-meta="tableMeta"
                        :all-checked="allChecked"
                        :check-func="checkFunc"
                        :only_columns="only_columns"
                        @toggle-all="toggleAll"
                        @toggle-column="visibleChanged"
                ></fields-checker>
            </div>
        </div>
    </div>
</template>

<script>
    import {eventBus} from './../../app';

    import IsShowFieldMixin from "../_Mixins/IsShowFieldMixin.vue";

    import FieldsChecker from "../CommonBlocks/FieldsChecker";

    export default {
        components: {
            FieldsChecker
        },
        mixins: [
            IsShowFieldMixin
        ],
        name: "ShowHideButton",
        data: function () {
            return {
                menu_opened: false
            }
        },
        props:{
            tableMeta: Object,
            user: Object,
            only_columns: Array,
        },
        computed: {
            columnsAllChecked() {
                let gr_checked = _.findIndex(this.meta_column_groups, (col_gr) => {
                        return this.colGroupChecked(col_gr);
                    }) > -1;
                let gr_indeterm = _.findIndex(this.meta_column_groups, (col_gr) => {
                        return this.colGroupIndeterm(col_gr);
                    }) > -1;
                let gr_uncheck = _.findIndex(this.meta_column_groups, (col_gr) => {
                        return !this.colGroupChecked(col_gr);
                    }) > -1;
                return !gr_indeterm && !gr_uncheck ? 2 : (gr_indeterm || gr_checked ? 1 : 0);
            },
            allChecked() {
                let fld_hidden = _.findIndex(this.tableMeta._fields, (el) => {
                        return this.canView(el) && !el.is_showed;
                    }) > -1;
                let fld_showed = _.findIndex(this.tableMeta._fields, (el) => {
                        return this.canView(el) && el.is_showed;
                    }) > -1;
                return !fld_hidden ? 2 : (fld_showed ? 1 : 0);
            },
            meta_column_groups() {
                return _.filter(this.tableMeta._column_groups, (col_group) => {
                    let visibleCols = false;
                    _.each(col_group._fields, (gr_fld) => {
                        let i = _.findIndex(this.tableMeta._fields, (el) => {
                            return el.field == gr_fld.field && this.canView(el);
                        });
                        visibleCols = visibleCols || i > -1;
                    });
                    return visibleCols;
                });
            },
        },
        methods: {
            canView(col) {
                return (!this.only_columns || in_array(col.field, this.only_columns))
                    && this.isShowField(col, true);
            },
            //row groups
            rowToggled(row_gr) {
                if (!row_gr._filter_disabled) {
                    row_gr._show_status = !row_gr._show_status ? 2 : 0;
                    row_gr._toggled = 1;
                    eventBus.$emit('reload-page');
                }
            },
            toggleAllRows() {
                this.$root.all_rg_toggled = this.rowsAllChecked() ? 0 : 2;
                eventBus.$emit('reload-page');
            },
            rowsAllChecked() {
                return !this.tableMeta._view_rows_count
                    ? 0
                    : (this.tableMeta._view_rows_count < this.tableMeta._global_rows_count ? 1 : 2);
            },

            //column groups
            colGroupIndeterm(col_group) {
                let showed = _.findIndex(col_group._fields, (gr_fld) => {
                        return _.findIndex(this.tableMeta._fields, (el) => {
                                return this.canView(el) && el.is_showed && el.field === gr_fld.field;
                            }) > -1;
                    }) > -1;

                let hidden = _.findIndex(col_group._fields, (gr_fld) => {
                        return _.findIndex(this.tableMeta._fields, (el) => {
                                return this.canView(el) && !el.is_showed && el.field === gr_fld.field;
                            }) > -1;
                    }) > -1;

                return showed && hidden;
            },
            colGroupChecked(col_group) {
                let gr_checked = false;
                _.each(col_group._fields, (gr_fld) => {
                    let fld_showed = _.findIndex(this.tableMeta._fields, (el) => {
                            return this.canView(el) && el.is_showed && el.field === gr_fld.field;
                        }) > -1;
                    gr_checked = gr_checked || fld_showed;
                });
                return gr_checked;
            },
            colGroupToggle(col_group) {
                let set_status = !this.colGroupChecked(col_group);
                let ids = [];
                _.each(this.tableMeta._fields, (el) => {
                    if (this.canView(el) && _.find(col_group._fields, {field: el.field})) {
                        this.setStatus(el, set_status);
                        ids.push(el.id);
                    }
                });
                this.$emit('show-changed', ids, set_status);
            },
            toggleAllColumns() {
                let set_status = !this.columnsAllChecked;
                let ids = [];
                _.each(this.meta_column_groups, (col_gr) => {
                    _.each(this.tableMeta._fields, (el) => {
                        if (this.canView(el) && _.find(col_gr._fields, {field: el.field})) {
                            this.setStatus(el, set_status);
                            ids.push(el.id);
                        }
                    });
                });
                this.$emit('show-changed', _.uniq(ids), set_status);
            },

            //single columns
            visibleChanged(fld) {
                fld.is_showed = !fld.is_showed;
                this.setStatus(fld, fld.is_showed);
                this.$emit('show-changed', [fld.id], fld.is_showed);
            },
            toggleAll() {
                let set_status = this.allChecked ? 0 : 1;
                let ids = [];
                _.each(this.tableMeta._fields, (el) => {
                    if (this.canView(el)) {
                        this.setStatus(el, set_status);
                        ids.push(el.id);
                    }
                });
                this.$emit('show-changed', ids, set_status);
            },
            setStatus(el, status) {
                el.is_showed = status;
                let filter_el = _.find(this.$root.tableMeta._fields, {id: el.id});
                if (filter_el) {
                    filter_el.is_showed = status ? 1 : 0;
                }
            },

            //systems
            checkFunc(header) {
                return header.is_showed;
            },
            inArray(item, array) {
                return $.inArray(item, array) > -1;
            },
            hideMenu(e) {
                let container = $(this.$refs.show_button);
                if (container.has(e.target).length === 0){
                    this.menu_opened = false;
                }
            },
            pageReloadedHandler(type) {
                if (this.menu_opened && type === 'page') {
                    this.$forceUpdate();
                }
            },

            showGroupingSettings(type) {
                eventBus.$emit('show-grouping-settings-popup', this.tableMeta.db_name, type);
            },
        },
        created() {
            eventBus.$on('global-click', this.hideMenu);
            eventBus.$on('global-keydown', this.hideMenu);
            eventBus.$on('page-reloaded', this.pageReloadedHandler);
        },
        beforeDestroy() {
            eventBus.$off('global-click', this.hideMenu);
            eventBus.$off('global-keydown', this.hideMenu);
            eventBus.$off('page-reloaded', this.pageReloadedHandler);
        }
    }
</script>

<style scoped lang="scss">
    .show_hide_button {
        cursor: pointer;
        width: 35px;
        height: 30px;
        padding: 0;
        font-size: 22px;
        background-color: transparent;
        position: relative;
        outline: none;
    }
    .show_hide_button > .blue-gradient {
        padding: 0;
        position: relative;
        width: 100%;
    }
    .show_hide_button > .blue-gradient > img {
        height: 30px;
    }

    .show_hide_menu {
        position: absolute;
        right: 100%;
        top: 100%;
        z-index: 500;
        max-height: 500px;
        background-color: #EEE;
        border: 1px solid #777;
        border-radius: 5px;
        display: flex;

        .fa-plus {
            font-size: 14px;
            margin-right: 5px;
            cursor: pointer;
        }
        .menu_part {
            margin: 5px;
            width: 175px;
            overflow: auto;
        }
        .vert_line {
            width: 1px;
            border-right: 1px solid #777;
        }
        .menu_part > div {
            border-bottom: 1px dashed #CCC;
            line-height: 20px;
        }
        .menu_part > div > label {
            white-space: nowrap;
            font-size: 0.7em;
            margin: 0;
        }
    }
    .title-elem {
        text-align: center;
        background-color: #DDD;
    }
</style>