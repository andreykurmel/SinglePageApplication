<template>
    <div class="tab-pane">
        <div class="full-frame">
            <div ref="jstree"></div>
        </div>
        <button v-if="changed"
                class="btn btn-success right-bottom"
                @click="savePermissions"
                :style="button_style"
        >Save</button>

        <!--edit FolderAssignedPermission form-->
        <div v-if="table_permissions && selected_checked_table" class="modal-wrapper">
            <div class="modal">
                <div class="modal-dialog modal-sm">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title">
                                Select a
                                <a @click.prevent="openPermisPopup">permission</a>
                                for table: {{ assigned_table ? assigned_table.name : '' }}
                            </h4>
                        </div>
                        <div class="modal-body">
                            <select v-model="selected_checked_table.table_permission_id" class="form-control">
                                <option v-for="permission in table_permissions" :value="permission.id">{{ permission.name }}</option>
                            </select>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-success" @click="assignOnePermission()">Save</button>
                            <button type="button" class="btn btn-default" @click="closeAssignPop()">Cancel</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: 'FolderPermissionsTree',
        data() {
            return {
                selected_checked_table: null,
                assigned_table: null,
                table_permissions: null,
                old_tables: [],
                changed: false,
                no_handle: false,
            }
        },
        props: {
            is_system: Boolean|Number,
            user_group_id: Number,
            is_active: Boolean|Number,
            is_app: Boolean|Number,
            tree: Object,
            checked_tables: Array,
            assigned_permissions: Array,
            button_style: Object,
        },
        methods: {
            openPermisPopup() {
                this.$emit('open-permis-assign', this.selected_checked_table ? this.selected_checked_table.table_id : null);
                this.closeAssignPop();
            },

            // creating JsTree -----------------------------------------------------------------------------------------
            createTreeMenu() {
                let self = this;
                $(this.$refs.jstree).jstree( this.getContextMenu() )
                    .on('select_node.jstree', function (e, data) {
                        self.jstree_select_node(e, data);
                    })
                    .on('deselect_node.jstree', function (e, data) {
                        self.jstree_select_node(e, data);
                    });
            },
            jstree_select_node(e, data) {
                if (this.no_handle) {
                    return;
                }
                //only for left click
                let evt =  window.event || e;
                let button = evt.which || evt.button;
                if( button !== 1 && ( typeof button !== 'undefined')) return false;
                //
                let $node = data.node;

                //click on checkbox or Folder
                if (evt.target.className.indexOf('jstree-checkbox') > -1 || $node.li_attr['data-type'] !== 'table') {
                    this.changed = true;
                }
                //click on title of Table
                else {
                    //prevent node check/uncheck
                    this.no_handle = true;
                    if ($node.state.selected) {
                        $(this.$refs.jstree).jstree('deselect_node', $node);
                    } else {
                        $(this.$refs.jstree).jstree('select_node', $node);
                    }
                    this.no_handle = false;

                    if ($node.state.selected && $node.li_attr['data-type'] === 'table') {

                        if (evt.target.className.indexOf('jstree-apermis') > -1) {
                            window.open(evt.target.href, '_blank').focus();
                        } else
                        if (evt.target.className.indexOf('jstree-icon') > -1) {
                            this.$emit('open-permis-assign', $node.li_attr['data-id']);
                        } else {
                            if (this.is_system) {
                                Swal('Info', 'You cannot change "Visiting" permission for "Visitors" group.');
                                return;
                            }
                            this.selected_checked_table = _.find(this.checked_tables, {table_id: Number($node.li_attr['data-id'])});
                            this.assigned_table = _.find(this.$root.settingsMeta.available_tables, {id: Number($node.li_attr['data-id'])});
                            this.table_permissions = this.assigned_table ? this.assigned_table._table_permissions : [];
                            if (! this.selected_checked_table) {
                                Swal('Info', 'Permission object was not found for the table. Please select checkbox and save changes or reload the page.');
                            } else if (! this.selected_checked_table.table_permission_id) {
                                this.selected_checked_table.table_permission_id = (_.find(this.table_permissions, {is_system: 1}) || {}).id;
                            }
                        }

                    }
                }
            },

            //creating context menu ------------------------------------------------------------------------------------
            getContextMenu() {
                let self = this;
                let plugins = ['checkbox'];
                let context_menu = {
                    'core' : {
                        'data' : this.tree
                    }
                };

                context_menu.plugins = plugins;
                return context_menu;
            },

            prepareTree(elems) {
                _.each(elems, (el) => {
                    if (el.children) {
                        this.prepareTree(el.children);
                    }

                    if (el.li_attr['data-type'] === 'table')
                    {
                        let tableId = el.li_attr['data-id'];
                        let db_el = _.find(this.checked_tables, {table_id: Number(tableId)});
                        let permis = db_el
                            ? _.find(this.assigned_permissions, {table_id: Number(tableId)}) || {name: 'Visiting'}
                            : null;

                        el.text = '<a class="jstree-apermis" href="'+el.a_attr['href']+'">' + el.init_name + '</a>'
                            + (permis ? ' <span class="jstree-tpart">(Permission: '+permis.name+')</span>' : '')
                            + (db_el && !db_el.is_active ? ' (innactive)' : '');
                        el.state.disabled = db_el && !db_el.is_active;
                        el.a_attr['class'] = (db_el && db_el.is_app ? 'node_green' : '');

                        el.state.selected = db_el;

                        if (el.state.selected) {
                            this.old_tables.push(tableId);
                        }
                    }
                });
            },

            savePermissions() {
                let new_permissions = [];
                _.each($(this.$refs.jstree).jstree("get_selected", true), (elem) => {
                    if (elem.li_attr['data-type'] === 'table') {
                        new_permissions.push(elem.li_attr['data-id']);
                    }
                });

                $.LoadingOverlay('show');
                axios.post('/ajax/folder/permission/tables', {
                    user_group_id: this.user_group_id,
                    is_active: this.is_active ? 1 : 0,
                    is_app: this.is_app ? 1 : 0,
                    checked_tables: new_permissions,
                    old_tables: this.old_tables
                }).then(({ data }) => {
                    this.changed = false;
                    this.$emit('changed-shared-tables');
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
            assignOnePermission() {
                $.LoadingOverlay('show');
                axios.post('/ajax/folder/permission/set-one', {
                    user_group_id: this.selected_checked_table.user_group_id,
                    tb_shared_id: this.selected_checked_table.id,
                    permission_id: this.selected_checked_table.table_permission_id,
                }).then(({ data }) => {
                    this.closeAssignPop();
                    this.$emit('assigned-new-permission');
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
            closeAssignPop() {
                this.table_permissions = null;
                this.selected_checked_table = null;
            },
        },
        mounted() {
            if (this.tree) {
                this.prepareTree([this.tree]);
                this.createTreeMenu();
            }
        }
    }
</script>

<style>
    .node_green {
        color: #080 !important;
    }
    .jstree-apermis {
        color: rgb(99, 107, 111);
    }
    .jstree-apermis:hover,
    .jstree-tpart:hover,
    .jstree-icon:hover {
        text-decoration: underline;
    }
</style>
<style lang="scss" scoped>
    .tab-pane {
        position: relative;
        height: 100%;

        .right-bottom {
            position: absolute;
            z-index: auto;
            right: 5px;
            top: 5px;
        }
    }

    .jstree-wrapper {
        height: calc(100% - 38px);
        overflow: auto;
    }

    .modal {
        display: block;
    }
</style>