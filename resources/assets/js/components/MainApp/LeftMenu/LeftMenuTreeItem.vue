<template>
    <div @contextmenu.prevent="contextMenuOnEmpty()" class="tab-pane">

        <div class="jstree-wrapper" ref="tree_wrap" @scroll="saveScrollPos">
            <div ref="jstree">Register and Login to add and manage your own collection of data tables.</div>
        </div>

        <div class="search-in-tree">
            <input type="text" class="form-control" @change="searchInTab()" v-model="searchVal">
            <button class="btn btn-default" @click="searchInTab()"><i class="fa fa-search"></i></button>
        </div>

        <!--Context Menu On Empty Place-->
        <ul v-show="context_menu.active"
            class="my_context_menu vakata-context jstree-contextmenu jstree-default-contextmenu"
            :style="{left: context_menu.x+'px', top: context_menu.y+'px'}"
            @mouseleave="context_menu.active = false"
        >
            <li v-if="canAddTable" @click="showTablePopup('new', null)">
                <a href="#" rel="0"><i></i><span class="vakata-contextmenu-sep">&nbsp;</span>Add Table</a>
            </li>
            <li @click="addFolder(null, null, 0)">
                <a href="#" rel="0"><i></i><span class="vakata-contextmenu-sep">&nbsp;</span>Add Folder</a>
            </li>
        </ul>

        <!--Transfer form-->
        <div v-show="toOtherModal.show" class="modal-wrapper">
            <div class="modal">
                <div class="modal-dialog modal-sm">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title">{{ toOtherModal.text }}</h4>
                        </div>
                        <div class="modal-body">
                            <label>Select User</label>
                            <select ref="search_user"></select>
                            <copy-table-settings-block
                                    v-if="toOtherModal.action === 'copy'"
                                    @send-settings="getSettingsWith"
                            ></copy-table-settings-block>
                        </div>
                        <div class="modal-footer">
                            <button type="button" 
                                    class="btn btn-success" 
                                    @click="(toOtherModal.action === 'transfer' ? transferObject() : popupCopyObject())"
                            >{{ (toOtherModal.action === 'transfer' ? 'Transfer' : 'Copy') }}</button>
                            <button type="button" class="btn btn-default" @click="toOtherModal.show = false">Cancel</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!--Add/Edit Table form-->
        <left-menu-tree-table-popup
                v-if="tablePopup.type"
                :table-popup="tablePopup"
                @add-table="addTable"
                @edit-table="editTable"
                @close="tablePopup.type = null"
        ></left-menu-tree-table-popup>

        <!--Add Fodler form-->
        <left-menu-tree-folder-popup
                v-if="folderPopup.active"
                :folder-popup="folderPopup"
                @store-folder="storeFolder"
                @close="folderPopup.active = false"
        ></left-menu-tree-folder-popup>

        <!--Force copy of table or folder-->
        <menu-tree-already-copied
                v-if="already_copied_popup_show"
                :type="already_copied_popup_show"
                @proceed="popupCopyObjectProceed"
                @hide="already_copied_popup_show = ''"
        ></menu-tree-already-copied>

    </div>
</template>

<script>
    import {eventBus} from './../../../app';

    import {JsTree} from './../../../classes/JsTree';
    import {SpecialFuncs} from './../../../classes/SpecialFuncs';

    import CopyTableSettingsBlock from "../../CommonBlocks/CopyTableSettingsBlock";
    import LeftMenuTreeTablePopup from "./LeftMenuTreeTablePopup";
    import LeftMenuTreeFolderPopup from "./LeftMenuTreeFolderPopup";
    import MenuTreeAlreadyCopied from "../../CustomPopup/MenuTreeAlreadyCopied";

    export default {
        name: 'LeftMenuTreeItem',
        components: {
            MenuTreeAlreadyCopied,
            LeftMenuTreeFolderPopup,
            LeftMenuTreeTablePopup,
            CopyTableSettingsBlock
        },
        data() {
            return {
                already_copied_popup_show: '',
                searchVal: '',

                context_menu: {
                    active: false,
                    x: 0,
                    y: 0
                },

                toOtherModal: {
                    show: false,
                    action: 'transfer',
                    text: '',
                    object_type: null,
                    object_id: null,
                    node: null,
                    proceed_type: null,
                    new_name: null,
                    _with: {}
                },
                editedFolderNode: null,

                tablePopup: {
                    type: null,
                    $node: null
                },

                folderPopup: {
                    active: false,
                    folder_id: null,
                    $node: null,
                    in_shared: null,
                },
            }
        },
        props: {
            tab: String,
            tab_tree: Array,
            table_id: Number,
            selectedLink: Object,
            settingsMeta: Object,
            tabObj: Object,
        },
        computed: {
            canAddTable() {
                return this.tab === 'private' && this.$root.checkAvailable(this.$root.user, 'q_tables', this.$root.user.tables_count);
            },
        },
        methods: {
            saveScrollPos() { //save scroll position
                this.tabObj.init_scrl = this.$refs.tree_wrap ? $(this.$refs.tree_wrap).scrollTop() : 0;
            },
            changeOpenedObject(type, object_id, name, path, reload) {
                if (reload) {
                    window.location.reload();
                    //window.location.href = path;
                    return;
                }

                try {
                    if (this.tab !== 'folder_view') {
                        window.history.pushState(name, name, path);
                    } else {
                        window.location.hash = path;
                    }
                    this.$emit('update-object-id', type, object_id);
                } catch (e) {
                    window.location.href = path;
                }
            },

            // creating JsTree -----------------------------------------------------------------------------------------
            createTreeMenu() {
                let self = this;
                $(this.$refs.jstree).bind('loaded.jstree', (e, data) => {
                        $(this.$refs.tree_wrap).scrollTop(this.tabObj.init_scrl);
                    })
                    .jstree( this.getContextMenu() )
                    .on('select_node.jstree', function (e, data) {
                        self.jstree_select_node(e, data.node);
                    })
                    .on('open_node.jstree', function(e, data) {
                        self.jstree_toggle_node(data.node, 1);
                    })
                    .on('close_node.jstree', function(e, data) {
                        self.jstree_toggle_node(data.node, 0);
                    })
                    .on('move_node.jstree', function (e, data) {
                        self.jstree_move_node(data);
                    })
                    .on('copy_node.jstree', function (e, data) {
                        self.jstree_copy_node(data);
                    });
            },
            jstree_select_node(e, $node) {
                //only for left click
                let evt =  window.event || e;
                let button = evt.which || evt.button;
                if( button !== 1 && ( typeof button !== 'undefined')) return false;
                //
                let type = $node.li_attr['data-type'];
                let object = $node.li_attr['data-object'];

                if (this.selectedLink) {
                    if (type !== 'folder') {
                        Swal('', 'You should select a folder');
                    } else
                    if (this.tab === 'public' && !this.$root.user.is_admin && object.name !== 'UNCATEGORIZED') {
                        Swal('', 'You can create link on "public" tab only in "UNCATEGORIZED" folder');
                    } else {
                        //create link for table
                        this.createLink(this.selectedLink.id, object, $node);
                        this.$emit('update-selected-link', null);
                    }
                } else {
                    if (type === 'folder') {
                        $(this.$refs.jstree).jstree().toggle_node($node);
                    } else {
                        let path = $node.a_attr['href'];
                        this.changeOpenedObject('table', object.id, object.name, path);
                    }
                }
            },
            jstree_toggle_node($node, is_open) {
                if (this.tab !== 'public' && this.tab !== 'folder_view') {
                    let folder_id = $node.li_attr['data-object'].id;
                    if (this.$root.user.id === $node.li_attr['data-object'].user_id) {
                        this.toggleFolder(folder_id, is_open);
                    }
                    let icon = $('#' + $node.id).find('i.jstree-icon.jstree-themeicon').first();
                    if (icon.hasClass('fa-folder') || icon.hasClass('fa-folder-open')) {
                        icon
                            .removeClass(is_open ? 'fa-folder' : 'fa-folder-open')
                            .addClass(is_open ? 'fa-folder-open' : 'fa-folder');
                    }
                }
            },
            jstree_move_node(data) {
                this.moveObject( this.node_move_object(data) );
            },
            jstree_copy_node(data) {
                let nmo = this.node_move_object(data);
                $.LoadingOverlay('show');
                this.copyObject(
                    nmo.sel_type, nmo.object.id, this.$root.user.id
                ).then(({ data }) => {
                    if (data && data.table_object) {
                        nmo.object = data.table_object.li_attr['data-object'];
                        this.moveObject( nmo );
                    }
                    if (data && data.new_folder_id) {
                        nmo.object.id = data.new_folder_id;
                        this.moveObject( nmo );
                    }
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
            node_move_object(data) {
                let nmo = {};
                nmo.$node = data.node;
                nmo.target_folder = data.instance.get_node(data.parent);
                nmo.target_type = nmo.target_folder.li_attr ? nmo.target_folder.li_attr['data-type'] : null;
                nmo.target_object = nmo.target_folder.li_attr ? nmo.target_folder.li_attr['data-object'] : null;
                nmo.sel_type = nmo.$node.li_attr['data-type'] === 'folder' ? 'folder' : 'table';
                nmo.object = nmo.$node.li_attr['data-object'];//can be instance 'Folder' or 'Table' !!!
                nmo.position = data.position;

                if (nmo.sel_type === 'folder' && nmo.target_type === 'folder') {
                    let tables_count = 0;
                    _.each(nmo.target_folder.children, (ch) => {
                        let ch_node = data.instance.get_node(ch);
                        if (ch_node && ch_node.li_attr && ch_node.li_attr['data-type'] !== 'folder') {
                            tables_count++;
                        }
                    });
                    nmo.position -= tables_count;
                }
                return nmo;
            },

            //creating context menu ------------------------------------------------------------------------------------
            getContextMenu() {
                //no functions for Folder Views
                if (this.tab === 'folder_view') {
                    return {
                        'core' : {
                            'data' : this.tab_tree
                        }
                    };
                }

                let self = this;
                let plugins = ['contextmenu', 'search'];
                let context_menu = {
                    'contextmenu': {
                        'items': function ($node) {
                            return {};
                        }
                    },
                    'core' : {
                        'data' : this.tab_tree
                    }
                };

                context_menu.contextmenu.items = function ($node) {
                    return self.getContextMenuItems($node, self.$root.user.is_admin);
                };
                if (this.$root.user.id) {
                    plugins.push('dnd');
                    context_menu.core.check_callback = function (operation, node, node_parent, more) {
                        return self.treeCheckCallback(this, operation, node, node_parent);
                    };
                }

                context_menu.plugins = plugins;
                return context_menu;
            },
            getContextMenuItems($node, $is_admin) {
                let self = this;
                let type = $node.li_attr['data-type'];
                let object = $node.li_attr['data-object'];//can be instance 'Folder' or 'Table' !!!
                let parent_id = $node.li_attr['data-parent_id'];//can be instance 'Folder' or 'Table' !!!
                let menu = {};

                //no menu for APPs
                if (object._in_apps) {
                    return menu;
                }

                if (object.is_system && !object.in_shared && this.tab !== 'favorite') {
                    return menu;
                }

                if ($is_admin || object.user_id === this.$root.user.id || object.in_shared) {
                    //menu items for 'folders'
                    if (type === 'folder') {
                        menu.add = {
                            'separator_before': false,
                            'separator_after': false,
                            'label': 'Add Folder',
                            'action': function (obj) {
                                self.addFolder(object.id, $node, object.in_shared ? 1 : 0);
                            }
                        };
                        if (this.tab === 'private' && this.canAddTable && !object.in_shared) {
                            menu.add_table = {
                                'separator_before': false,
                                'separator_after': false,
                                'label': 'Add Table',
                                'action': function (obj) {
                                    self.showTablePopup('new', $node);
                                }
                            };
                        }
                        if (!object.is_system && object.user_id === this.$root.user.id) {
                            menu.remove = {
                                'separator_before': false,
                                'separator_after': false,
                                'label': 'Remove',
                                'action': function (obj) {
                                    self.deleteFolder(object.id, $node);
                                }
                            };
                            if (this.tab !== 'favorite' && !object.in_shared) {
                                menu.favorite = {
                                    'separator_before': false,
                                    'separator_after': false,
                                    'label': 'Favorite',
                                    'action': function (obj) {
                                        self.favoriteFolder(object);
                                    }
                                };
                            }
                            if (this.tab === 'private' && !object.in_shared) {
                                menu.edit = {
                                    'separator_before': false,
                                    'separator_after': false,
                                    'label': 'Settings',
                                    'action': function (obj) {
                                        let path = $node.a_attr['href'];
                                        self.changeOpenedObject('folder', object.id, object.name, path);
                                        self.editedFolderNode = $node;
                                    }
                                };
                                menu.transfer = {
                                    'separator_before': false,
                                    'separator_after': false,
                                    'label': 'Transfer',
                                    'action': function (obj) {
                                        self.showToOtherModal('transfer', 'folder', 'Transfer folder', object.id, $node);
                                    }
                                };
                                menu.transfer = {
                                    'separator_before': false,
                                    'separator_after': false,
                                    'label': 'Copy to Others',
                                    'action': function (obj) {
                                        self.showCopyFolderToOthers(object);
                                    }
                                };
                            }
                        }
                    }

                    //menu items for 'tables'
                    if (type === 'table') {
                        menu = {
                            /*'edit': {
                                'separator_before': false,
                                'separator_after': false,
                                'label': 'Settings',
                                'action': function (obj) {
                                    self.showTablePopup('edit', $node);
                                }
                            },*/
                            'link': {
                                'separator_before': false,
                                'separator_after': false,
                                'label': 'Create Link',
                                'action': function (obj) {
                                    self.$emit('update-selected-link', object);
                                    Swal('Create a Link to the Table', 'Select a Tab in the menutree and then a folder in the Tab (folder "UNCATEGORIZED" only in Tab "Public").');
                                }
                            },
                            'transfer': {
                                'separator_before': false,
                                'separator_after': false,
                                'label': 'Transfer',
                                'action': function (obj) {
                                    self.showToOtherModal('transfer', 'table', 'Transfer table', object.id, $node);
                                }
                            },
                            'copy': {
                                'separator_before': false,
                                'separator_after': false,
                                'label': 'Copy to Others',
                                'action': function (obj) {
                                    self.showToOtherModal('copy', 'table', 'Copy Table', object.id, $node);
                                }
                            },
                            'remove': {
                                'separator_before': false,
                                'separator_after': false,
                                'label': 'Remove',
                                'action': function (obj) {
                                    self.deleteTable(object, $node);
                                }
                            },
                            'open': {
                                'separator_before': false,
                                'separator_after': false,
                                'label': 'Open in New Tab',
                                'action': function (obj) {
                                    window.open($node.a_attr['href'], '_blank');
                                }
                            },
                            'favorite': {
                                'separator_before': false,
                                'separator_after': false,
                                'label': 'Favorite',
                                'action': function (obj) {
                                    self.favoriteTable(object);
                                }
                            }
                        }
                    }
                }

                //menu items for 'links'
                if (type === 'link') {
                    if (this.tab !== 'favorite' && !object.in_shared) {
                        menu.favorite = {
                            'separator_before': false,
                            'separator_after': false,
                            'label': 'Favorite',
                            'action': function (obj) {
                                self.favoriteTable(object);
                            }
                        };
                    }
                    if (object.in_shared) {
                        menu.rename = {
                            'separator_before': false,
                            'separator_after': false,
                            'label': 'Rename',
                            'action': function (obj) {
                                self.renameShared(object, $node);
                            }
                        };
                    }
                    /*if (object._shared_edit_tb) {
                        menu.edit = {
                            'separator_before': false,
                            'separator_after': false,
                            'label': 'Settings',
                            'action': function (obj) {
                                self.showTablePopup('edit', $node);
                            }
                        };
                    }*/
                    menu.open = {
                        'separator_before': false,
                        'separator_after': false,
                        'label': 'Open in New Tab',
                        'action': function (obj) {
                            window.open($node.a_attr['href'], '_blank');
                        }
                    };
                    if (($is_admin || object.link.user_id === this.$root.user.id) && !object.in_shared) {
                        menu.remove = {
                            'separator_before': false,
                            'separator_after': false,
                            'label': 'Remove',
                            'action': function (obj) {
                                self.deleteLink(object, $node);
                            }
                        };
                    }
                }
                return menu;
            },
            treeCheckCallback(jstree, operation, node, node_parent) {
                if (operation === 'move_node' || operation === 'copy_node') {
                    let node_li = node.li_attr || {};
                    let node_object = node_li['data-object'] || {};
                    let parent_li = node_parent.li_attr || {};
                    let parent_object = parent_li['data-object'] || {};

                    let tables_count = 0;
                    _.each(node_parent.children, (ch) => {
                        let ch_node = jstree.get_node(ch);
                        if (ch_node && ch_node.li_attr && ch_node.li_attr['data-type'] !== 'folder') {
                            tables_count++;
                        }
                    });

                    //cannot change system objects
                    if (node_object.is_system || parent_object.is_system) {
                        return false;
                    }

                    //target can be only 'folder' or root
                    if (node_parent.id !== '#' && parent_li['data-type'] !== 'folder') {
                        return false;
                    }

                    //can change only admin or owner
                    if (!this.$root.user.is_admin && node_object.user_id !== this.$root.user.id) {
                        return false;
                    }
                    if (!this.$root.user.is_admin && parent_object.user_id !== this.$root.user.id) {
                        return false;
                    }
                    //
                }
                return true; //allow all other operations
            },

            //Favorite functions ----------------------------------
            favoriteTable(object) {
                axios.put('/ajax/table/favorite', {
                    table_id: object.id,
                    favorite: true
                }).then(({ data }) => {
                    this.$emit('reload-menu-tree');
                }).catch(errors => {
                    Swal('', getErrors(errors));
                });
            },
            favoriteFolder(object) {
                axios.put('/ajax/folder/favorite', {
                    folder_id: object.id,
                    favorite: true
                }).then(({ data }) => {
                    this.$emit('reload-menu-tree');
                }).catch(errors => {
                    Swal('', getErrors(errors));
                });
            },

            //Menu functions for 'transferring' ------------------------------------------------------------------------
            showToOtherModal(action, type, text, object_id, $node) {
                $(this.$refs.search_user).val(null).trigger('change');
                this.toOtherModal.show = true;
                this.toOtherModal.action = action;
                this.toOtherModal.object_type = type;
                this.toOtherModal.text = text;
                this.toOtherModal.object_id = object_id;
                this.toOtherModal.node = $node;
                this.toOtherModal.proceed_type = null;
                this.toOtherModal.new_name = null;
            },
            transferObject() {
                let user_id = $(this.$refs.search_user).val();
                let node_name = this.toOtherModal.node.li_attr['data-object'].name;
                if (user_id) {
                    $.LoadingOverlay('show');
                    axios.post('/ajax/'+this.toOtherModal.object_type+'/transfer', {
                        id: this.toOtherModal.object_id,
                        new_user_id: user_id,
                    }).then(({ data }) => {
                        //if name of the object is present in the URL path
                        if ( window.location.href.indexOf(node_name) > -1 ) {
                            window.location.reload();
                        }
                        $(this.$refs.jstree).jstree('delete_node', this.toOtherModal.node);
                        this.sync_tables();
                        this.toOtherModal.show = false;
                    }).catch(errors => {
                        Swal('', getErrors(errors));
                    }).finally(() => {
                        $.LoadingOverlay('hide');
                    });
                }
            },
            copyObject(object_type, object_id, user_id, $with, proceed_type, new_name) {
                //Folder Copy
                if (object_type === 'folder') {
                    return new Promise((resolve) => {
                        axios.get('/ajax/folder', {
                            params: {
                                folder_id: object_id,
                            }
                        }).then(({ data }) => {
                            let subtree = data.folder._sub_tree;
                            JsTree.select_all(subtree);
                            axios.post('/ajax/folder/copy', {
                                id: object_id,
                                new_user_id: user_id,
                                folder_json: subtree,
                                overwrite: proceed_type === 'overwrite',
                                new_name: new_name,
                            }).then(({ data }) => {
                                resolve({data: data});
                            });
                        });
                    });
                }
                //Table Copy
                if (object_type === 'table') {
                    $with = $with || {
                        data: true,
                        data_attach: true,
                        basics: true,
                        grouping_rows: true,
                        grouping_columns: true,
                        grouping_rcs: true,
                        links: true,
                        ddls: true,
                        cond_formats: true,
                    };
                    return axios.post('/ajax/table/copy', {
                        id: object_id,
                        new_user_id: user_id,
                        table_with: $with,
                        overwrite: proceed_type === 'overwrite',
                        new_name: new_name,
                    });
                }
            },
            moveObject(nmo) {
                $.LoadingOverlay('show');
                axios.put('/ajax/'+nmo.sel_type+'/move', {
                    id: nmo.object.id,
                    link_id: nmo.sel_type === 'folder' ? null : nmo.object.link.id,
                    folder_id: nmo.target_object ? nmo.target_object.id : null,
                    position: nmo.position,
                }).then(({ data }) => {
                    if (nmo.sel_type === 'folder') {
                        $(this.$refs.jstree).jstree().open_node(nmo.$node, false, false);
                    } else {
                        if (data.path) {
                            nmo.$node.a_attr['href'] = data.path;
                        }
                    }
                    this.sync_tables();
                    this.$emit('reload-menu-tree');
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
            popupCopyObject() {
                let user_id = $(this.$refs.search_user).val();
                if (user_id) {
                    $.LoadingOverlay('show');
                    this.copyObject(
                        this.toOtherModal.object_type,
                        this.toOtherModal.object_id,
                        user_id,
                        this.toOtherModal._with,
                        this.toOtherModal.proceed_type,
                        this.toOtherModal.new_name
                    ).then(({ data }) => {
                        this.toOtherModal.show = false;
                        if (data.error && data.msg.length) {
                            if (data.already_copied) {
                                this.already_copied_popup_show = this.toOtherModal.object_type;
                            } else {
                                Swal('', data.msg);
                            }
                        } else {
                            if (this.toOtherModal.proceed_type === 'overwrite') {
                                Swal('', 'Table copied and overwrote existing.');
                            }
                            if (this.toOtherModal.proceed_type === 'rename' && this.toOtherModal.new_name) {
                                Swal('', 'Table copied and renamed as '+this.toOtherModal.new_name+'.');
                            }
                            if (this.$root.user.id == user_id) {
                                eventBus.$emit('event-reload-menu-tree');
                            }
                        }
                    }).catch(errors => {
                        Swal('', getErrors(errors));
                    }).finally(() => {
                        $.LoadingOverlay('hide');
                    });
                }
            },
            popupCopyObjectProceed(type, name) {
                this.toOtherModal.proceed_type = type;
                this.toOtherModal.new_name = name;
                this.already_copied_popup_show = '';
                this.popupCopyObject();
            },

            //Menu functions for 'tables' ------------------------------------------------------------------------------
            showTablePopup(type, $node) {
                this.tablePopup.type = type;
                this.tablePopup.$node = $node;
            },
            addTable($node, data) {
                $(this.$refs.jstree).jstree().create_node($node ? $node : '#', data.table_object, 'last', false, false);
                if ($node) {
                    $(this.$refs.jstree).jstree().open_node($node);
                }
                this.changeOpenedObject('table', data.table_id, data.table_object.name, data.path, true);
                this.sync_tables();
                this.tablePopup.type = null;
            },
            editTable($node, object, old_name, new_name) {
                this.updateObjectLinks($node, old_name, new_name, 1);
                $(this.$refs.jstree).jstree('rename_node', $node, new_name);
                this.sync_menu_tree($node);
                this.sync_tables();
                this.tablePopup.type = null;
            },
            deleteTable(object, $node) {
                Swal({
                    title: 'Delete Table',
                    text: 'All data would be completely removed! Confirm to proceed.',
                    confirmButtonClass: 'btn-danger',
                    confirmButtonText: 'Yes',
                    showCancelButton: true,
                    animation: 'slide-from-top'
                }).then(response => {
                    if (response.value) {
                        $.LoadingOverlay('show');
                        axios.delete('/ajax/import/delete-table', {
                            params: {
                                table_id: object.id
                            }
                        }).then(({ data }) => {
                            if (object.id === this.table_id) {
                                window.location.reload();
                            }
                            $(this.$refs.jstree).jstree('delete_node', $node);
                            this.sync_tables();
                        }).catch(errors => {
                            Swal('', getErrors(errors));
                        }).finally(() => {
                            $.LoadingOverlay('hide');
                        });
                    }
                });
            },
            renameShared(object, $node) {
                Swal({
                    title: '',
                    input: 'text',
                    showCancelButton: true,
                    animation: 'slide-from-top',
                    inputPlaceholder: 'Rename Shared Table',
                    inputValidator: (value) => {
                        return !value && 'You need to write something!'
                    }
                }).then(response => {
                    if (response.value) {
                        $.LoadingOverlay('show');
                        axios.post('/ajax/table/shared/rename', {
                            table_id: object.id,
                            name: response.value
                        }).then(({ data }) => {
                            this.updateObjectLinks($node, object.name, data.name, 1);
                            object.name = data.name;
                            $node.li_attr['data-object'] = object;
                            $(this.$refs.jstree).jstree('rename_node', $node, data.name);
                            this.sync_tables();
                        }).catch(errors => {
                            Swal('', getErrors(errors));
                        }).finally(() => {
                            $.LoadingOverlay('hide');
                        });
                    }
                });
            },

            //Menu functions for 'folders' -----------------------------------------------------------------------------
            addFolder(folder_id, $node, in_shared) {
                this.folderPopup.folder_id = folder_id;
                this.folderPopup.$node = $node;
                this.folderPopup.in_shared = in_shared;
                this.folderPopup.active = true;
            },
            storeFolder(name, params_object) {
                let $node = params_object.$node;
                if (name) {
                    name = name.replace(/[^\w\d\.-_ ]/gi, '');
                    $.LoadingOverlay('show');
                    axios.post('/ajax/folder', {
                        parent_id: params_object.folder_id,
                        name: name,
                        structure: this.tab,
                        in_shared: params_object.in_shared
                    }).then(({ data }) => {
                        $(this.$refs.jstree).jstree().create_node($node, data, 'last', false, false);
                        $(this.$refs.jstree).jstree().open_node($node);
                    }).catch(errors => {
                        Swal('', getErrors(errors));
                    }).finally(() => {
                        $.LoadingOverlay('hide');
                    });
                } else {
                    Swal('You need to write something!');
                }
                this.folderPopup.active = false;
            },
            deleteFolder(folder_id, $node) {
                let node_name = $node.li_attr['data-object'].name;
                Swal({
                    title: 'Delete Folder',
                    text: 'All folder and table/data nodes under this folder would be completely removed! Confirm to proceed.',
                    confirmButtonClass: 'btn-danger',
                    confirmButtonText: 'Yes',
                    showCancelButton: true,
                    animation: 'slide-from-top'
                }).then(response => {
                    if (response.value) {
                        $.LoadingOverlay('show');
                        axios.delete('/ajax/folder', {
                            params: {
                                folder_id: folder_id
                            }
                        }).then(({ data }) => {
                            //if name of the object is present in the URL path
                            if ( new RegExp(node_name, 'gi').test( window.location.href ) ) {
                                window.location.reload();
                            }
                            $(this.$refs.jstree).jstree('delete_node', $node);
                            this.sync_tables();
                        }).catch(errors => {
                            Swal('', getErrors(errors));
                        }).finally(() => {
                            $.LoadingOverlay('hide');
                        });
                    }
                });
            },
            toggleFolder(folder_id, status) {
                axios.put('/ajax/folder', {
                    folder_id: folder_id,
                    fields: {
                        is_opened: status
                    },
                }).then(({ data }) => {
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {});
            },

            //Menu functions for 'links' -------------------------------------------------------------------------------
            createLink(table_id, object, $node) {
                $.LoadingOverlay('show');
                axios.post('/ajax/table/link', {
                    table_id: table_id,
                    folder_id: object.id,
                    type: 'link',
                    structure: this.tab,
                    path: $node.a_attr['href'],
                }).then(({ data }) => {
                    $(this.$refs.jstree).jstree().create_node($node, data, 'last', false, false);
                    $(this.$refs.jstree).jstree().open_node($node);
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
            deleteLink(object, $node) {
                Swal({
                    title: 'Delete Link',
                    text: '',
                    confirmButtonClass: 'btn-danger',
                    confirmButtonText: 'Yes',
                    showCancelButton: true,
                    animation: 'slide-from-top'
                }).then(response => {
                    if (response.value) {
                        $.LoadingOverlay('show');
                        axios.delete('/ajax/table/link', {
                            params: {
                                table_id: object.id,
                                link_id: object.link.id,
                            }
                        }).then(({ data }) => {
                            $(this.$refs.jstree).jstree('delete_node', $node);
                        }).catch(errors => {
                            Swal('', getErrors(errors));
                        }).finally(() => {
                            $.LoadingOverlay('hide');
                        });
                    }
                });
            },

            //helper functions -----------------------------------------------------------------------------------------
            updateObjectLinks($node, old_name, new_name, lvl) {
                let path = window.location.href.replace(/%20/gi, ' ');
                path = path.replace('/'+old_name, '/'+new_name);
                if (lvl === 1 && path !== window.location.href) {
                    window.history.pushState(old_name, old_name, path);
                }
                $node.a_attr['href'] = $node.a_attr['href'].replace('/'+old_name, '/'+new_name);

                let self = this;
                _.each($node.children, function (id) {
                    let child_node = $(self.$refs.jstree).jstree('get_node', id);
                    self.updateObjectLinks(child_node, old_name, new_name, lvl+1);
                });

                if (lvl === 1) {
                    $(this.$refs.jstree).jstree('redraw', true);
                }
            },
            contextMenuOnEmpty() {
                if (this.tab === 'folder_view') {
                    return;
                }

                if (
                    ($.inArray(this.tab, ['private', 'favorite']) > -1 && this.$root.user.id)
                    ||
                    (this.tab === 'public' && this.$root.user.is_admin)
                ) {
                    if (window.event.target.nodeName != 'I' && window.event.target.nodeName != 'A') {
                        this.context_menu.active = true;
                        this.context_menu.x = window.event.clientX - 10;
                        this.context_menu.y = window.event.clientY - 160;
                    }
                }
            },
            searchInTab() {
                $(this.$refs.jstree).jstree().search( this.searchVal );
            },
            sync_tables() {
                $.LoadingOverlay('show');
                axios.get('/ajax/table-data/available-tables', {
                }).then(({ data }) => {
                    this.settingsMeta.available_tables = data;
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
            sync_menu_tree($node) {
                let obj = $node.li_attr['data-object'];
                let type = $node.li_attr['data-type'];
                this.recursiveUpdate(this.$root.private_menu_tree, $node);
            },
            recursiveUpdate(elements, $node) {
                _.each(elements, (elem) => {
                    if (
                        (elem.li_attr['data-object'].id === $node.li_attr['data-object'].id)
                        &&
                        (elem.li_attr['data-type'] === $node.li_attr['data-type'])
                    ) {
                        elem.li_attr['data-object'].name = $node.li_attr['data-object'].name;
                    }
                    if (elem.children) {
                        this.recursiveUpdate(elem.children, $node);
                    }
                });
            },
            getSettingsWith(sett) {
                this.toOtherModal._with = sett;
            },
            showCopyFolderToOthers(folder) {
                eventBus.$emit('show-copy-folder-to-others', folder.id);
            },
        },
        mounted() {
            if (this.tab_tree && (this.$root.user.id || this.$root.user._is_folder_view || this.tab === 'public')) {
                this.createTreeMenu();
            }

            $(this.$refs.search_user).select2({
                ajax: {
                    url: '/ajax/user/search',
                    dataType: 'json',
                    delay: 250
                },
                minimumInputLength: 1,
                width: '100%',
                height: '100%'
            });
            $(this.$refs.search_user).next().css('height', '30px');

            if (this.tab === 'folder_view') {
                let $nn = SpecialFuncs.findInTree(this.tab_tree, this.table_id, 'table');
                window.location.hash = $nn ? $nn['a_attr']['href'] : '';
            }
        },
        beforeDestroy() {
        }
    }
</script>

<style>
    a.jstree-anchor.user-available-link {
        color: green;
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

    .my_context_menu {
        display: block;
        left: 10px;
        top: 10px;
    }

    .search-in-tree {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;

        input {
            display: inline-block;
            width: calc(100% - 32px);
        }

        button {
            border:none;
            width: 25px;
            background-color: transparent;
            padding: 0;
            font-size: 1.7em;
        }
    }
</style>