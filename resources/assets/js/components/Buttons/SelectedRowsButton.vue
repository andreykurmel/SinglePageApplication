<template>
    <div ref="selected_rows" class="table_selected_rows flex flex--center-v flex--space" v-show="hasSelectedRows">

        <button title="Operations on selected rows"
                v-if="tableMeta.user_id === $root.user.id"
                class="btn btn-primary btn-sm blue-gradient flex flex--center"
                @click="menu_opened = true"
                :style="$root.themeButtonStyle"
        >
            <i class="glyphicon glyphicon-cog"></i>
        </button>
        <div v-show="menu_opened"
             class="selected_rows_menu"
             title="Operations on selected rows"
        >
            <div class="selected_rows_action">
                <label>
                    <i class="glyphicon glyphicon-tasks"></i>
                    <a @click.stop="showGroupsPopup('row')">Grouping</a>
                </label>
            </div>

            <div title="Add to this Row Group"
                 v-for="(r_gr, idx) in tableMeta._row_groups"
                 class="selected_rows_action"
                 @click="addToRowGroup(idx)"
            >
                <label>{{ r_gr.name }}</label>
            </div>
            <div v-if="!tableMeta._row_groups.length">No row group<br>has been defined.</div>
        </div>

        <button title="Copy operations on selected rows"
                class="btn btn-primary btn-sm blue-gradient flex flex--center"
                :class="[!canAdd && !canDelete ? 'disabled' : '']"
                @click="canAdd || canDelete ? copy_opened=true : ''"
                :style="$root.themeButtonStyle"
        >
            <i class="fa fa-clone"></i>
        </button>
        <div v-show="copy_opened"
             class="selected_rows_menu"
             title="Copy operations on selected rows"
             style="right: 45%;"
        >
            <div v-show="canAdd" class="selected_rows_action">
                <label @click.stop="copySelectedRows()">Copy w/ Replacements</label>
            </div>
            <div v-show="canAdd" class="selected_rows_action">
                <label @click.stop="copyChildrenRows()">Copy w/ Inheritance</label>
            </div>
            <div v-show="canDelete" class="selected_rows_action">
                <label @click.stop="copyDeleteRows()">Delete w/ Inheritance</label>
            </div>
        </div>

        <copy-and-replace-pop-up
                v-if="showCopyPopup"
                :table-meta="tableMeta"
                :request_params="request_params"
                :all-rows="allRows"
                :avail-fields="avaFields"
                @popup-close="showCopyPopup = false"
                @after-copy="afterCopiedData"
        ></copy-and-replace-pop-up>

        <copyw-children-popup
                v-if="showChildrenPopup"
                :master_table="tableMeta"
                :request_params="request_params"
                :all_rows="allRows"
                @popup-close="showChildrenPopup = false"
                @after-copy="afterCopied"
        ></copyw-children-popup>

        <deletew-children-popup
                v-if="showDeletePopup"
                :master_table="tableMeta"
                :request_params="request_params"
                :all_rows="allRows"
                @popup-close="showDeletePopup = false"
                @after-delete="afterDeleted"
        ></deletew-children-popup>
    </div>
</template>

<script>
    import {eventBus} from '../../app';

    import CanEditMixin from "../_Mixins/CanViewEditMixin.vue";
    import CanViewEditMixin from "../_Mixins/CanViewEditMixin.vue";

    import CopyAndReplacePopUp from "../CustomPopup/CopyAndReplacePopUp";
    import CopywChildrenPopup from "../CustomPopup/Inheritance/CopywChildrenPopup";
    import DeletewChildrenPopup from "../CustomPopup/Inheritance/DeletewChildrenPopup";

    export default {
        components: {
            DeletewChildrenPopup,
            CopywChildrenPopup,
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
                copy_opened: false,
                showCopyPopup: false,
                showDeletePopup: false,
                showChildrenPopup: false,
                //for IsShowFieldMixin
                behavior: 'list_view',
            }
        },
        props:{
            tableMeta: Object,
            request_params: Object,
            allRows: Array|Object,
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
            hideAll() {
                this.showCopyPopup = false;
                this.showDeletePopup = false;
                this.showChildrenPopup = false;
            },
            toggleMenu() {
                this.menu_opened = false;
                this.copy_opened = false;
            },
            copySelectedRows() {
                this.showCopyPopup = true;
                this.toggleMenu();
            },
            copyChildrenRows() {
                this.showChildrenPopup = true;
                this.toggleMenu();
            },
            copyDeleteRows() {
                this.showDeletePopup = true;
                this.toggleMenu();
            },
            addToRowGroup(gr_idx) {
                let check_obj = this.$root.checkedRowObject(this.$root.listTableRows);

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
                        rows_ids: (check_obj.all_checked ? null : check_obj.rows_ids),
                        request_params: (check_obj.all_checked ? request_params : null)
                    }).then(({ data }) => {
                        this.toggleMenu();
                        this.tableMeta._row_groups[gr_idx]._regulars = data;
                        eventBus.$emit('reload-page');
                        eventBus.$emit('clear-selected-settings-groups');
                    }).catch(errors => {
                        Swal('Info', getErrors(errors));
                    }).finally(() => {
                        $.LoadingOverlay('hide');
                    });

                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
            afterCopiedData(data, all_rows_checked) {
                this.afterCopied(!all_rows_checked ? data.rows : []);
            },
            afterCopied(data_rows) {
                if (data_rows.length) {
                    _.each(data_rows, (row) => {
                        row._is_new = 1;
                        this.allRows.splice(0, 0, row);
                    });
                    this.$emit('update-meta-params', {
                        rows_count: Number(this.tableMeta._view_rows_count) + Number(data_rows.length)
                    });
                } else {
                    eventBus.$emit('reload-page');
                }
                this.hideAll();
            },
            afterDeleted(all_checked) {
                if (all_checked) {
                    Swal('Info','Deleting process has started. Table will be reloaded after finishing.');
                } else {
                    eventBus.$emit('reload-page');
                }
                this.hideAll();
            },
            updateMetaParams(data) {
                this.$emit('update-meta-params', data);
            },
            hideMenu(e) {
                let container = $(this.$refs.selected_rows);
                if (container.has(e.target).length === 0 && this.menu_opened) {
                    this.toggleMenu();
                }
                if (container.has(e.target).length === 0 && this.copy_opened) {
                    this.toggleMenu();
                }
            },
            showGroupsPopup(type, id) {
                this.menu_opened = false;
                eventBus.$emit('show-grouping-settings-popup', this.tableMeta.db_name, type, id);
            },
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
        width: 75px;
        background-color: transparent;
        position: relative;

        button {
            cursor: pointer;
            height: 30px;
            width: 35px;
            font-size: 22px;
            padding: 0;
            outline: none;

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
        color: #222;
    }
</style>