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
    </div>
</template>

<script>
    export default {
        name: 'FolderPermissionsTree',
        data() {
            return {
                old_tables: [],
                changed: false,
                no_handle: false,
            }
        },
        props: {
            user_group_id: Number,
            is_active: Boolean|Number,
            is_app: Boolean|Number,
            tree: Object,
            checked_tables: Array,
            assigned_permissions: Array,
            button_style: Object,
        },
        methods:{

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
                        this.$emit('open-permis-assign', $node.li_attr['data-id']);
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
                        let db_el = _.find(this.checked_tables, {table_id: Number(el.li_attr['data-id'])});
                        let permis = _.find(this.assigned_permissions, {table_id: Number(el.li_attr['data-id'])});

                        el.text = el.init_name
                            + (permis ? ' (permission: '+permis.name+')' : '')
                            + (db_el && !db_el.is_active ? ' (innactive)' : '');
                        el.state.disabled = db_el && !db_el.is_active;
                        el.a_attr['class'] = (db_el && db_el.is_app ? 'node_green' : '');

                        el.state.selected = db_el;

                        if (el.state.selected) {
                            this.old_tables.push( el.li_attr['data-id'] );
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
                    this.$emit('changed-shared-tables', this.user_group_id, data);
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            }
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
</style>