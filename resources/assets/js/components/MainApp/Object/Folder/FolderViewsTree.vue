<template>
    <div class="tab-pane">
        <div ref="jstree"></div>

        <!--edit FolderViewTable form-->
        <div v-if="table_views !== null" class="modal-wrapper">
            <div class="modal">
                <div class="modal-dialog modal-sm">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title">
                                Select a
                                <a @click.prevent="openMrvPopup">MRV</a>
                                for table: {{ assignedTable ? assignedTable.name : '' }}
                            </h4>
                        </div>
                        <div class="modal-body">
                            <select-block
                                :options="tbViews()"
                                :sel_value="assigned_view_id"
                                :link_path="tbViewLink(assigned_view_id)"
                                @option-select="(opt) => { assigned_view_id = opt.val }"
                            ></select-block>
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
    import SelectBlock from "../../../CommonBlocks/SelectBlock.vue";

    export default {
        name: 'FolderViewsTree',
        components: {SelectBlock},
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
        methods: {
            tbViewLink(viewId, tableId) {
                let finder = viewId ? {id: Number(viewId)} : {is_system: 1};
                let view = _.find(this.table_views, finder);
                if (!view && tableId) {
                    let table = _.find(this.$root.settingsMeta.available_tables, {id: Number(tableId)}) || {};
                    view = _.find(table._views || [], finder);
                }
                if (view) {
                    return this.$root.clear_url + '/mrv/' + view.hash;
                }
                return '#';
            },
            tbViews() {
                return _.map(this.table_views || [], (view) => {
                    return {
                        val: view.id,
                        show: view.name,
                    };
                });
            },
            openMrvPopup() {
                this.$emit('open-view-assign', this.assignedTable ? this.assignedTable.id : null);
                this.table_views = null;
            },
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
                    Swal('Info', getErrors(errors));
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
                        Swal('Info', getErrors(errors));
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

                        if (evt.target.className.indexOf('jstree-aview') > -1) {
                            window.open(evt.target.href, '_blank').focus();
                        } else
                        if (evt.target.className.indexOf('jstree-icon') > -1) {
                            this.$emit('open-view-assign', $node.li_attr['data-id']);
                        } else {
                            axios.get('/ajax/folder/view/checked-table', {
                                params: {
                                    folder_view_id: this.folder_view_id,
                                    table_id: $node.li_attr['data-id']
                                }
                            }).then(({data}) => {
                                this.table_views = data.table_views;
                                this.assigned_view_id = data.folder_view_table.assigned_view_id;
                                if (!this.assigned_view_id) {
                                    this.assigned_view_id = (_.find(this.table_views, {is_system: 1}) || {}).id;
                                }
                                this.table_id = data.folder_view_table.table_id;
                                this.assigned_node = $node;
                            }).catch(errors => {
                                Swal('Info', getErrors(errors));
                            }).finally(() => {
                            });
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
                        let tableId = el.li_attr['data-id'];
                        el.state.selected = _.findIndex(this.checked_tables, {id: tableId}) > -1;
                        //has assigned view
                        let assigned_view = el.state.selected
                            ? _.find(this.assigned_views, {table_id: el.li_attr['data-id']}) || {name: 'Visiting'}
                            : null;
                        el.text = '<a class="jstree-aview" href="'
                            + (assigned_view ? this.tbViewLink(assigned_view.id, tableId) : '#')
                            +'">' + el.init_name + '</a>'
                            + (assigned_view ? ' <span class="jstree-tpart">(View: '+assigned_view.name+')</span>' : '');
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

<style>
    .jstree-aview {
        color: rgb(99, 107, 111);
    }
    .jstree-aview:hover,
    .jstree-tpart:hover,
    .jstree-icon:hover {
        text-decoration: underline;
    }
</style>
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