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
                            <span> All Groups</span>
                        </label>
                    </div>
                    <div v-for="row_gr in tableMeta._row_groups" v-show="!row_gr._group_hidden">
                        <label :style="{backgroundColor: (row_gr._show_status ? '#CCC;' : ''), opacity: (row_gr._filter_disabled ? '0.5' : '')}"
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
                            </span>
                            <a :title="row_gr._filter_disabled ? 'Disabled by Filter(s).' : 'Open records/rows of the group.'"
                               @click.stop="showLinkedRows(row_gr)"
                            >{{ row_gr.name }}</a>
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
                                <span class="indeterm_check" @click="toggleAllColumns(!columnsAllChecked)">
                                    <i v-if="columnsAllChecked == 2" class="glyphicon glyphicon-ok group__icon"></i>
                                    <i v-if="columnsAllChecked == 1" class="glyphicon glyphicon-minus group__icon"></i>
                                </span>
                            </span>
                            <span> All Groups</span>
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
                this.emitShowChanged(ids, set_status);
            },
            toggleAllColumns(set_status) {
                let ids = [];
                _.each(this.meta_column_groups, (col_gr) => {
                    _.each(this.tableMeta._fields, (el) => {
                        if (this.canView(el) && _.find(col_gr._fields, {field: el.field})) {
                            this.setStatus(el, set_status);
                            ids.push(el.id);
                        }
                    });
                });
                this.emitShowChanged(_.uniq(ids), set_status);
            },

            //single columns
            visibleChanged(fld) {
                fld.is_showed = !fld.is_showed;
                this.setStatus(fld, fld.is_showed);
                this.emitShowChanged([fld.id], fld.is_showed);
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
                this.emitShowChanged(ids, set_status);
            },
            emitShowChanged(ids, status) {
                this.$emit('show-changed', ids, status);
                eventBus.$emit('show-hide-button-has-been-changed', this.tableMeta.db_name);
            },
            setStatus(el, status) {
                el.is_showed = status;
                let filter_el = _.find(this.tableMeta._fields, {id: el.id});
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
            showLinkedRows(rowGroup) {
                let lif = _.find(this.tableMeta._fields, {field: rowGroup.listing_field});
                let lnk = {
                    id: null,
                    link_type: 'Record',
                    link_display: 'Popup',
                    popup_display: 'Table',
                    always_available: true,
                    name: rowGroup.name,
                    listing_field_id: lif ? lif.id : null,
                    table_ref_condition_id: rowGroup.row_ref_condition_id || undefined,
                    dir_table_id: !rowGroup.row_ref_condition_id ? rowGroup.table_id : null,
                    extra_ids: rowGroup._regulars ? _.map(rowGroup._regulars, 'field_value') : null,
                };
                this.$emit('show-linked-rows', lnk, {field:''}, {}, 'list_view');
            },
            showHideOnlyColGroup(table_id, col_group_id) {
                let col_group = _.find(this.tableMeta._column_groups, {id: Number(col_group_id)});
                if (table_id === this.tableMeta.id && col_group) {
                    this.toggleAllColumns(false);
                    this.colGroupToggle(col_group);
                }
            },
        },
        created() {
            eventBus.$on('global-click', this.hideMenu);
            eventBus.$on('global-keydown', this.hideMenu);
            eventBus.$on('page-reloaded', this.pageReloadedHandler);
            eventBus.$on('show-hide-only-col-group', this.showHideOnlyColGroup);
            eventBus.$on('hide-table-column', this.visibleChanged);
        },
        beforeDestroy() {
            eventBus.$off('global-click', this.hideMenu);
            eventBus.$off('global-keydown', this.hideMenu);
            eventBus.$off('page-reloaded', this.pageReloadedHandler);
            eventBus.$off('show-hide-only-col-group', this.showHideOnlyColGroup);
            eventBus.$off('hide-table-column', this.visibleChanged);
        }
    }
</script>

<style scoped lang="scss">
    @import "ShowHide";
</style>