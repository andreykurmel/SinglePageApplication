<template>
    <div ref="selected_rows" class="table_selected_rows" v-show="hasSelectedRows" title="Actions with selected Rows">
        <button class="btn btn-primary btn-sm blue-gradient flex flex--center"
                @click="toggleMenu(!this.menu_opened)"
                :style="$root.themeButtonStyle"
        >
            <i class="glyphicon glyphicon-cog"></i>
        </button>
        <div v-show="menu_opened" class="selected_rows_menu">

            <div class="selected_rows_action" :class="[canAdd ? 'disabled' : '']" @click="canAdd ? copySelectedRows() : ''">
                <label><i class="glyphicon glyphicon-copy"></i> Copy</label>
            </div>
            <!--<div v-if="canDelete" class="selected_rows_action" @click="DeleteSelectedRows">-->
                <!--<label><i class="glyphicon glyphicon-remove" style="color: #039;"></i> Delete</label>-->
            <!--</div>-->
            <div v-if="tableMeta.user_id === $root.user.id" class="selected_rows_action">
                <label @click="groupingClick()"><i class="glyphicon glyphicon-tasks"></i> Grouping</label>
                <div v-show="group_menu_opened" class="selected_rows_menu">
                    <div v-for="(r_gr, idx) in tableMeta._row_groups"
                         class="selected_rows_action"
                         @click="addToRowGroup(idx)"
                    >
                        <label>{{ r_gr.name }}</label>
                    </div>
                </div>
            </div>

        </div>

        <copy-and-replace-pop-up
                v-if="showCopyPopup"
                :table-meta="tableMeta"
                :request_params="request_params"
                :all-rows="allRows"
                :avail-fields="avaFields"
                @popup-close="showCopyPopup = false"
                @after-copy="afterCopied"
        ></copy-and-replace-pop-up>
    </div>
</template>

<script>
    import {eventBus} from './../../app';

    import CanEditMixin from "../_Mixins/CanViewEditMixin.vue";
    import CanViewEditMixin from "../_Mixins/CanViewEditMixin.vue";

    import CopyAndReplacePopUp from "../CustomPopup/CopyAndReplacePopUp";

    export default {
        components: {
            CopyAndReplacePopUp
        },
        name: "SelectedRowsButton",
        mixins: [
            CanEditMixin,
            CanViewEditMixin,
        ],
        data: function () {
            return {
                menu_opened: false,
                group_menu_opened: false,
                showCopyPopup: false,
                //for IsShowFieldMixin
                behavior: 'list_view',
            }
        },
        props:{
            tableMeta: Object,
            request_params: Object,
            allRows: Array,
        },
        computed: {
            hasSelectedRows() {
                return _.findIndex(this.$root.listTableRows, {_checked_row: true}) > -1;
            },
            avaFields() {
                let showed = _.filter(this.tableMeta._fields, (fld) => {
                    return this.canViewHdr(fld);
                });
                return _.map(showed, 'field');
            },
        },
        methods: {
            groupingClick() {
                if (this.tableMeta._row_groups.length) {
                    this.group_menu_opened = !this.group_menu_opened;
                } else {
                    Swal('No row group has been defined.');
                }
            },
            toggleMenu(val) {
                this.menu_opened = val;
                this.group_menu_opened = false;
            },
            copySelectedRows() {
                this.showCopyPopup = true;
                this.toggleMenu(false);
            },
            DeleteSelectedRows() {
                /**/
            },
            addToRowGroup(gr_idx) {
                let rows_ids = [];
                let all_rows_checked = true;
                _.each(this.$root.listTableRows, (row) => {
                    if (row._checked_row) {
                        rows_ids.push(row.id);
                    } else {
                        all_rows_checked = false;
                    }
                });

                $.LoadingOverlay('show');
                axios.put('/ajax/row-group', {
                    table_row_group_id: this.tableMeta._row_groups[gr_idx].id,
                    fields: {
                        listing_field: 'id'
                    }
                }).then(({ data }) => {
                    this.tableMeta._row_groups[gr_idx].listing_field = 'id';

                    $.LoadingOverlay('show');

                    let request_params = _.cloneDeep(this.$root.request_params);
                    request_params.page = 1;
                    request_params.rows_per_page = 0;

                    axios.post('/ajax/row-group/regular-mass', {
                        table_row_group_id: this.tableMeta._row_groups[gr_idx].id,
                        rows_ids: (all_rows_checked ? null : rows_ids),
                        request_params: (all_rows_checked ? request_params : null)
                    }).then(({ data }) => {
                        this.toggleMenu(false);
                        this.tableMeta._row_groups[gr_idx]._regulars = data;
                        eventBus.$emit('reload-page');
                        eventBus.$emit('clear-selected-settings-groups');
                    }).catch(errors => {
                        Swal('', getErrors(errors));
                    }).finally(() => {
                        $.LoadingOverlay('hide');
                    });

                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
            afterCopied(data, all_rows_checked) {
                if (!all_rows_checked) {
                    _.each(data.rows, (row) => {
                        row._is_new = 1;
                        this.allRows.splice(0, 0, row);
                    });
                    this.$emit('update-meta-params', {
                        rows_count: Number(this.tableMeta._view_rows_count) + Number(data.rows_count)
                    });
                } else {
                    eventBus.$emit('reload-page');
                    this.showCopyPopup = false;
                }
            },
            updateMetaParams(data) {
                this.$emit('update-meta-params', data);
            },
            hideMenu(e) {
                let container = $(this.$refs.selected_rows);
                if (container.has(e.target).length === 0 && this.menu_opened) {
                    this.toggleMenu(false);
                }
            }
        },
        created() {
            eventBus.$on('global-click', this.hideMenu);
        },
        beforeDestroy() {
            eventBus.$off('global-click', this.hideMenu);
        }
    }
</script>

<style lang="scss" scoped>
    .table_selected_rows {
        cursor: pointer;
        height: 30px;
        width: 35px;
        padding: 0;
        font-size: 22px;
        background-color: transparent;
        position: relative;
        outline: none;

        button {
            width: 100%;
            height: 100%;
            font-size: 0.9em;
            padding: 0;
            display: flex;
            justify-content: center;

            i {
                padding: 0 !important;
            }
        }
    }

    .selected_rows_menu {
        position: absolute;
        right: 100%;
        top: 100%;
        width: max-content;
        z-index: 1500;
        background: linear-gradient(to bottom, #efeff4, #d6dadf);
        padding: 5px;
        border: 1px solid #CCC;
        border-radius: 5px;
    }

    .selected_rows_action {
        display: flex;
    }
    .selected_rows_action:not(:last-child) {
        border-bottom: 1px solid #CCC;
    }
    .selected_rows_action label {
        cursor: pointer;
        font-size: 16px;
        width: 100%;
        margin-bottom: 0;

        &:hover {
            color: #222;
        }
    }
</style>