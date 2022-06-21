<template>
    <div class="tab-pane">
        <div ref="jstree"></div>

        <!--edit FolderViewTable form-->
        <div v-if="table_views !== null" class="modal-wrapper">
            <div class="modal">
                <div class="modal-dialog modal-sm">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title">Select a view for {{ assignedTable ? assignedTable.name : '' }}</h4>
                        </div>
                        <div class="modal-body">
                            <select v-model="assigned_view_id" class="form-control">
                                <option></option>
                                <option v-for="view in table_views" :value="view.id">{{ view.name }}</option>
                            </select>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-success" @click="saveFolderViewTable()">Save</button>
                            <button type="button" class="btn btn-default" @click="table_views = null">Cancel</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: 'FolderViewsTree',
        data() {
            return {
                table_views: null,
                assigned_view_id: null,
                table_id: null,
                assigned_node: null,
                no_handle: false,
            }
        },
        props: {
            folder_view_id: Number,
            tree: Object,
            checked_tables: Array,
            assigned_views: Array,
        },
        computed: {
            assignedTable() {
                return _.find(this.$root.settingsMeta.available_tables, {id: Number(this.table_id)});
            },
        },
        methods:{
            saveFolderViewTable() {
                axios.put('/ajax/folder/view/checked-table', {
                    folder_view_id: this.folder_view_id,
                    table_id: this.table_id,
                    assigned_view_id: this.assigned_view_id
                }).then(({ data }) => {
                    //set new View near the table in tree
                    let $view = _.find(this.table_views, {id: Number(this.assigned_view_id)});
                    let new_name = this.assigned_node.li_attr['data-object'].name + ($view ? ' (View: '+$view.name+')' : '');
                    $(this.$refs.jstree).jstree('rename_node', this.assigned_node, new_name);

                    //set new View in assigned
                    if ($view && this.assigned_views) {
                        let assigned_view = _.find(this.assigned_views, {table_id: Number(this.table_id)});
                        if (assigned_view) {
                            assigned_view.name = $view.name;
                        } else {
                            this.assigned_views.push($view);
                        }
                    }

                    this.table_views = null;
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                });
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
                    let new_views = [];
                    _.each($(this.$refs.jstree).jstree("get_selected", true), (elem) => {
                        if (elem.li_attr['data-type'] === 'table') {
                            new_views.push({
                                id: elem.li_attr['data-id'],
                            });
                        }
                    });

                    axios.post('/ajax/folder/view/tables', {
                        folder_view_id: this.folder_view_id,
                        checked_tables: new_views
                    }).then(({ data }) => {
                        this.$emit('updated-views', data);
                    }).catch(errors => {
                        Swal('', getErrors(errors));
                    }).finally(() => {
                    });
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
                        axios.get('/ajax/folder/view/checked-table', {
                            params: {
                                folder_view_id: this.folder_view_id,
                                table_id: $node.li_attr['data-id']
                            }
                        }).then(({ data }) => {
                            this.table_views = data.table_views;
                            this.assigned_view_id = data.folder_view_table.assigned_view_id;
                            this.table_id = data.folder_view_table.table_id;
                            this.assigned_node = $node;
                        }).catch(errors => {
                            Swal('', getErrors(errors));
                        }).finally(() => {
                        });
                    }
                }
            },

            //creating context menu ------------------------------------------------------------------------------------
            getContextMenu() {
                let self = this;
                let plugins = ['checkbox'];
                let context_menu = {
                    'core' : {
                        'data' : this.tree,
                        'check_callback': true
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

                    if (el.li_attr['data-type'] === 'table') {
                        //in checked tables
                        el.state.selected = _.findIndex(this.checked_tables, {id: el.li_attr['data-id']}) > -1;
                        //has assigned view
                        let assigned_view = _.find(this.assigned_views, {table_id: el.li_attr['data-id']});
                        el.text = el.init_name + (assigned_view ? ' (View: '+assigned_view.name+')' : '');
                    }
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

<style lang="scss" scoped>
    .tab-pane {
        position: relative;
        height: calc(100% - 35px);
    }

    .jstree-wrapper {
        height: calc(100% - 38px);
        overflow: auto;
    }

    .modal {
        display: block;
    }
</style>