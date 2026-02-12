<template>
    <div class="tab-pane" :title="!$root.user.id ? 'Right-click to see options' : ''">

        <div class="jstree-wrapper relative"
             ref="tree_wrap"
             :style="{height: tab === 'favorite'
                ? '50%'
                : (externalSearch === undefined ? 'calc(100% - 38px)' : '100%')}"
             @scroll="saveScrollPos"
             @contextmenu.prevent="contextMenuOnEmpty()"
        >
            <div ref="jstree">
                <div v-if="tab === 'private'" :style="{ fontSize: ($root.themeTextFontSize) + 'px' }">
                    <div>Register and Login to add and manage your own collection of data tables.</div>
                    <div style="color: #000; margin-top: 10px;">
                        A hierarchical menu structure provides a clear and efficient way to organize and manage your data
                        - tables and links to tables. This structure allows you:
                        <ul style="padding-left: 20px;">
                            <li>quickly find what you need,</li>
                            <li>easily see where items are located within the hierarchy,</li>
                            <li>understand their relationships,</li>
                            <li>effortlessly add or remove entries while maintaining the consistency of the structure,</li>
                            <li>conveniently share all or selected tables, with full or partial data, with team members or visitors.</li>
                        </ul>
                        <img src="/assets/img/Menutree_Intro_basic.gif" :style="{height: ($root.themeTextFontSize * 30) + 'px'}">
                    </div>
                </div>
                <div v-if="tab === 'favorite'" :style="{ fontSize: ($root.themeTextFontSize) + 'px' }">
                    <div>Register and Login to add and manage your own collection of favorite tables.</div>
                    <div style="color: #000; margin-top: 10px;">
                        Mark tables as Favorite for quick access. Tables marked as Favorite will be shown in this panel.
                    </div>
                </div>
            </div>
        </div>

        <div v-if="externalSearch === undefined" class="search-in-tree flex">
            <input type="text"
                   class="form-control"
                   placeholder="Type to search for tables or links to tables"
                   @input="clearSearchIndex"
                   @keydown="searchKey"
                   v-model="searchVal">
            &nbsp;
            <button class="btn btn-default" @click="searchInTab()"><i class="fa fa-search"></i></button>
        </div>

        <div v-if="tab === 'favorite'"
             class="relative"
             :style="{height: tab === 'favorite' ? 'calc(50% - 42px)' : '0'}"
        >
            <ul class="nav nav-tabs flex">
                <li class="active">
                    <a>History</a>
                </li>
            </ul>
            <div style="position: absolute; right: 0; top: 20px;">Days Passed</div>
            <div class="recent-wrap" ref="recent_wrap">
                <div v-if="!$root.user.id" style="color: #000" :style="{ fontSize: ($root.themeTextFontSize) + 'px' }">
                    Tables recently accessed will be shown up here in a chronically order.
                </div>

                <div v-for="(recent, idx) in $root.user._menutree_recents"
                     style="position: relative;"
                >
                    <a :href="recent.url"
                       class="recent-block"
                       @click.prevent="changeOpenedObject(recent.object_type, recent.object_id, recent.name, recent.url)"
                    >
                        <span :ref="'recent_blk_'+recent.id"
                              @mouseenter="recent._hover = true"
                              @mouseleave="recent._hover = false"
                        >{{ recent.name }}</span>
                    </a>
                    <span class="recent-time">{{ daysDiff(recent.last_access) }}</span>

                    <hover-block v-if="recent._hover"
                                 :html_str="recent.url"
                                 :p_left="pLeft(recent)"
                                 :p_top="pTop(recent)"
                                 :extra_style="{maxWidth: '1000px'}"
                    ></hover-block>
                </div>
            </div>
        </div>

        <!--Context Menu On Empty Place-->
        <ul v-show="context_menu.active"
            class="my_context_menu vakata-context jstree-contextmenu jstree-default-contextmenu"
            :style="{left: context_menu.x+'px', top: context_menu.y+'px'}"
            @mouseleave="context_menu.active = false"
        >
            <li v-if="context_menu.canaddtable" @click="emptAddTable()">
                <a href="#" rel="0">
                    <i class="fa fa-circle green"></i>
                    <span class="vakata-contextmenu-sep">&nbsp;</span>Add Table
                </a>
            </li>
            <li @click="addFolder(null, null, 0)">
                <a href="#" rel="0">
                    <i class="fa fa-circle green"></i>
                    <span class="vakata-contextmenu-sep">&nbsp;</span>Add Folder
                </a>
            </li>
            <li @click="addPage(null, null)">
                <a href="#" rel="0">
                    <i class="fa fa-circle green"></i>
                    <span class="vakata-contextmenu-sep">&nbsp;</span>Add Dashboard
                </a>
            </li>
        </ul>

        <!--Transfer form-->
        <div v-show="toOtherModal.show" class="modal-wrapper">
            <div class="modal">
                <div class="modal-dialog modal-sm" style="width: 350px; margin-bottom: 100px;">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title">{{ toOtherModal.text }}</h4>
                        </div>
                        <div class="modal-body">
                            <table class="full-width">
                                <tr>
                                    <td style="font-weight: bold;">Search & Select a User</td>
                                </tr>
                                <tr>
                                    <td>
                                        <select ref="search_user" class="form-group"></select>
                                    </td>
                                </tr>
                                <tr v-if="$root.tableMeta && $root.tableMeta.id">
                                    <td style="font-weight: bold;">Or select a usergroup or a user</td>
                                </tr>
                                <tr v-if="$root.tableMeta && $root.tableMeta.id">
                                    <td style="position: relative; height: 30px;">
                                        <tablda-user-select
                                                :edit_value="toOtherModal.user_select"
                                                :show_selected="true"
                                                :table_field="{table_id: $root.tableMeta.id}"
                                                :can_empty="true"
                                                style="min-width: auto;"
                                                @selected-item="(val) => { toOtherModal.user_select = val; }"
                                        ></tablda-user-select>
                                    </td>
                                </tr>
                            </table>
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

        <!--Info Message-->
        <info-popup v-if="info_message"
                    :title_html="'Info'"
                    :content_html="info_message"
                    :extra_style="{top:'calc(50% - 75px)'}"
                    @hide="info_message = ''"
        ></info-popup>

        <!--Add/Edit Table form-->
        <left-menu-tree-table-popup
                v-if="tablePopup.type"
                :table-popup="tablePopup"
                @add-table="addTable"
                @edit-table="editTable"
                @close="tablePopup.type = null"
        ></left-menu-tree-table-popup>

        <!--Add Folder form-->
        <left-menu-tree-folder-popup
                v-if="folderPopup.active"
                :folder-popup="folderPopup"
                @store-folder="storeFolder"
                @close="folderPopup.active = false"
        ></left-menu-tree-folder-popup>

        <!--Add Page form-->
        <left-menu-tree-page-popup
                v-if="pagePopup.active"
                :page-popup="pagePopup"
                @store-page="storeNewPage"
                @update-page="storeUpdatedPage"
                @close="pagePopup.active = false"
        ></left-menu-tree-page-popup>

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
    import {eventBus} from '../../../app';

    import {JsTree} from '../../../classes/JsTree';
    import {SpecialFuncs} from '../../../classes/SpecialFuncs';

    import CopyTableSettingsBlock from "../../CommonBlocks/CopyTableSettingsBlock";
    import LeftMenuTreeTablePopup from "./LeftMenuTreeTablePopup";
    import LeftMenuTreeFolderPopup from "./LeftMenuTreeFolderPopup";
    import MenuTreeAlreadyCopied from "../../CustomPopup/MenuTreeAlreadyCopied";
    import InfoPopup from "../../CustomPopup/InfoPopup";
    import TabldaUserSelect from "../../CustomCell/Selects/TabldaUserSelect";
    import LeftMenuTreePagePopup from "./LeftMenuTreePagePopup";

    export default {
        name: 'LeftMenuTreeItem',
        components: {
            LeftMenuTreePagePopup,
            InfoPopup,
            TabldaUserSelect,
            MenuTreeAlreadyCopied,
            LeftMenuTreeFolderPopup,
            LeftMenuTreeTablePopup,
            CopyTableSettingsBlock,
        },
        data() {
            return {
                search_index: 0,
                prevent_recusrion: false,
                info_message: '',
                already_copied_popup_show: '',
                searchVal: '',

                context_menu: {
                    canaddtable: false,
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
                    user_select: null,
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

                pagePopup: {
                    page_name: null,
                    type: null,
                    active: false,
                    page_id: null,
                    folder_id: null,
                    $node: null,
                },
            }
        },
        props: {
            tab: String,
            tab_tree: Array,
            object_type: String,
            object_id: Number,
            selectedLink: Object,
            settingsMeta: Object,
            externalSearch: String,
        },
        watch: {
            externalSearch(val) {
                this.searchVal = val;
                this.searchInTab();
            },
        },
        computed: {
            canAddTable() {
                return this.tab === 'private' && this.$root.checkAvailable(this.$root.user, 'q_tables', this.$root.user.tables_owned_count);
            },
        },
        methods: {
            pLeft(recent) {
                let el = _.first(this.$refs['recent_blk_'+recent.id]);
                let box = el ? el.getBoundingClientRect() : {};
                return box.right + 10;
            },
            pTop(recent) {
                let el = _.first(this.$refs['recent_blk_'+recent.id]);
                let box = el ? el.getBoundingClientRect() : {};
                return box.top;
            },
            saveScrollPos() { //save scroll position
                localStorage.setItem('scroll_menutree_'+this.tab, this.$refs.tree_wrap ? $(this.$refs.tree_wrap).scrollTop() : 0);
            },
            changeOpenedObject(type, object_id, name, path, reload) {
                let nodomain = JsTree.get_no_domain(path);

                //change browser url
                try {
                    if (this.tab !== 'folder_view') {
                        window.history.pushState(name, name, nodomain);
                    } else {
                        window.location.hash = nodomain;
                    }
                    this.$emit('update-object-id', type, object_id);
                    this.saveRecent(type, object_id);
                } catch (e) {
                    window.location.href = nodomain;
                }

                if (reload) {
                    this.$root.user.memutree_hash = null;//reload menutree in 10 sec (MainAppWrapper)
                }

                let $json = $(this.$refs.jstree).jstree().get_json(null, {flat: true}) || [];
                _.each($json, (node, index) => {
                    if (node && node.a_attr && node.a_attr.href) {
                        $('#' + node.a_attr.id).removeClass('currently-selected-obj');
                        //add class to selected
                        if (node.a_attr.href === path) {
                            $('#' + node.a_attr.id).addClass('currently-selected-obj');
                        }
                    }
                });
            },

            // creating JsTree -----------------------------------------------------------------------------------------
            createTreeMenu() {
                $(this.$refs.jstree).bind('loaded.jstree', (e, data) => {
                        let top = Number(localStorage.getItem('scroll_menutree_'+this.tab) || 0);
                        $(this.$refs.tree_wrap).scrollTop(top);
                        this.highligtSelectedNode(true);
                    })
                    .jstree( this.getContextMenu() )
                    .on('select_node.jstree', (e, data) => {
                        this.jstree_select_node(e, data.node);
                    })
                    .on('open_node.jstree', (e, data) => {
                        this.jstree_toggle_node(data.node, 1);
                    })
                    .on('close_node.jstree', (e, data) => {
                        this.jstree_toggle_node(data.node, 0);
                    })
                    .on('move_node.jstree', (e, data) => {
                        this.jstree_move_node(data);
                    })
                    .on('copy_node.jstree', (e, data) => {
                        this.jstree_copy_node(data);
                    })
                    .on('search.jstree', function (e, data) {
                        let array = $(this).find('.jstree-search');
                        if (array && array.length) {
                            if (!array[this.search_index]) {
                                this.search_index = 0;
                            }
                            if (!!window.chrome) {
                                array[this.search_index].scrollIntoView();
                            }
                            this.search_index++;
                        }
                    });
            },
            jstree_select_node(e, $node) {
                //highlight current table/folder
                if (this.prevent_recusrion) {
                    this.prevent_recusrion = false;
                    return false;
                }

                //only for left click
                let evt =  window.event || e;
                let button = evt.which || evt.button;
                if( button !== 1 && ( typeof button !== 'undefined')) return false;
                //
                let type = $node.li_attr['data-type'];
                let object = $node.li_attr['data-object'];

                this.highligtSelectedNode(true);

                if (this.selectedLink) {
                    if (type !== 'folder') {
                        this.info_message = 'You should select a folder';
                    } else
                    if (this.tab === 'public' && !this.$root.user.is_admin && object.name !== 'UNCATEGORIZED') {
                        this.info_message = 'You can create link on "public" tab only in "UNCATEGORIZED" folder';
                    } else {
                        //create link for table or folder
                        let type = this.get_selected_link_type(this.selectedLink);
                        this.createLink(this.selectedLink.id, object, $node, type);
                        this.$emit('update-selected-link', null);
                    }
                } else {
                    if (type === 'folder') {
                        $(this.$refs.jstree).jstree().toggle_node($node);
                    } else
                    if (type === 'page') {
                        let path = $node.a_attr['href'];
                        this.changeOpenedObject('page', object.id, object.name, path);
                    }
                    else {
                        let path = $node.a_attr['href'];
                        this.changeOpenedObject('table', object.id, object.name, path);
                    }
                }
            },
            get_selected_link_type(selectedLink) {
                if (selectedLink.tree_type) {
                    return selectedLink.tree_type;
                }
                //Old style
                let link = selectedLink.link || {};
                let type = link.entity_type || link.type;
                if (! type) {
                    if (selectedLink._tables) {
                        type = 'folder';
                    } else if (selectedLink.db_name) {
                        type = 'table';
                    } else {
                        type = 'pages';
                    }
                }
                return type;
            },
            jstree_toggle_node($node, is_open) {
                if (this.tab !== 'folder_view') {
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
                    if (icon.hasClass('fa-cubes') || icon.hasClass('fa-cube')) {
                        icon
                            .removeClass(is_open ? 'fa-cube' : 'fa-cubes')
                            .addClass(is_open ? 'fa-cubes' : 'fa-cube');
                    }
                }
            },
            jstree_move_node(data) {
                this.moveObject( this.node_move_object(data) );
            },
            jstree_copy_node(node) {
                let nmo = this.node_move_object(node);
                if (this.tab === 'favorite' && nmo.sel_type === 'table') {
                    this.favoriteTable(nmo.object, nmo.target_folder.li_attr['data-id']);
                    return;
                }

                let new_name = (nmo.sel_type === 'table' ? 'Copy_' : '') + nmo.object.name;
                if (nmo.sel_type === 'table') {
                    $(this.$refs.jstree).jstree().rename_node(nmo.$node, new_name);
                    nmo.$node.a_attr['href'] = String(nmo.$node.a_attr['href']).replaceAll(nmo.object.name, new_name);
                }
                this.copyObject(
                    nmo.sel_type,
                    nmo.object.id,
                    this.$root.user.id,
                    null,
                    null,
                    new_name,
                    nmo.sel_type === 'folder' ? 'with_links' : null,
                    nmo.target_folder.li_attr['data-id']
                ).then(({ data }) => {
                    if (data && data.error) {
                        Swal('Info', data.msg || 'Server Error');
                        return;
                    }
                    if (data && data.table_object) {
                        nmo.$node.li_attr = data.table_object.li_attr;
                        nmo.$node.state = data.table_object.state;
                        nmo.object = data.table_object.li_attr['data-object'];
                        $(this.$refs.jstree).jstree().rename_node(nmo.$node, nmo.object.name);
                    }
                    if (data && data.new_folder_id) {
                        nmo.object.id = data.new_folder_id;
                    }
                    this.sync_tables();
                    this.$root.user.memutree_hash = null;//reload menutree in 10 sec (MainAppWrapper)
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                });

                $.LoadingOverlay('show');
                setTimeout(() => {
                    $.LoadingOverlay('hide');
                }, 500);
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
                let context_menu = {
                    'core': {
                        'data': this.tab_tree,
                        'keyboard': {
                            'left': false,
                            'right': false,
                            'up': false,
                            'down': false,
                        },
                    }
                };

                //no functions for Folder Views
                if (this.tab === 'folder_view') {
                    return context_menu;
                }

                let plugins = ['contextmenu', 'search'];
                context_menu['contextmenu'] = {
                    'items': ($node) => {
                        return {};
                    }
                };

                context_menu.contextmenu.items = ($node) => {
                    return this.getContextMenuItems($node, this.$root.user.is_admin);
                };
                if (this.$root.user.id) {
                    plugins.push('dnd');
                    context_menu.core.check_callback = (operation, node, node_parent, more) => {
                        return this.treeCheckCallback(operation, node, node_parent);
                    };
                }

                context_menu.plugins = plugins;
                return context_menu;
            },
            getContextMenuItems($node, $is_admin) {
                let $is_editor = this.$root.user.role_id == 3;

                let type = $node.li_attr['data-type'];
                let object = $node.li_attr['data-object'];//can be instance 'Folder' or 'Table' !!!
                let parent_id = $node.li_attr['data-parent_id'];//can be instance 'Folder' or 'Table' !!!
                let menu = {};

                if (
                    (object._in_apps)//no menu for APPs
                    ||
                    (object.is_system && !object.in_shared && this.tab !== 'favorite')//no menu for System objects
                    ||
                    (!this.$root.user.id)//no menu for visitor.
                ) {
                    if (!this.$root.user.id && type === 'folder' && object.inside_folder_link) {
                        menu.public_copy = this.publicCopyMenu(false);
                    }
                    return menu;
                }

                let $is_owner = object.user_id == this.$root.user.id || object.created_by == this.$root.user.id;
                let $lnkOwner = object.link && object.link.user_id == this.$root.user.id;
                let $isFolderLink = object.is_folder_link || (object.link && object.link.is_folder_link);
                let $folderLnkOwner = object.is_folder_link == this.$root.user.id || (object.link && object.link.is_folder_link == this.$root.user.id);

                if (this.tab === 'public') {
                    let $isNativeFolder = ! object.inside_folder_link;
                    //can have 'folder' and 'link' only.
                    if (type === 'folder') {
                        if (!$isNativeFolder) {
                            let canPublicCopy = !$isNativeFolder && object.inside_folder_link != this.$root.user.id;//not-owner
                            menu.public_copy = this.publicCopyMenu(canPublicCopy);
                        }
                        if ($is_admin && $isNativeFolder) {//Admin can add to his folder
                            menu.add = {
                                'icon': 'fa fa-circle green',
                                'separator_before': false,
                                'separator_after': false,
                                'label': 'Add Folder',
                                'action': (obj) => {
                                    this.addFolder(object.id, $node, object.in_shared ? 1 : 0);
                                }
                            };
                            menu.edit = {
                                'icon': 'fa fa-circle green',
                                'separator_before': false,
                                'separator_after': false,
                                'label': 'Settings&More',
                                'action': (obj) => {
                                    let path = $node.a_attr['href'];
                                    this.changeOpenedObject('folder', object.id, object.name, path);
                                    this.editedFolderNode = $node;
                                }
                            };
                        }
                        if (($is_admin && $isNativeFolder) || ($is_admin && $isFolderLink) || $folderLnkOwner) {//Admin can remove his folder or folder link
                            menu.remove = {
                                'icon': 'fa fa-circle green',
                                'separator_before': false,
                                'separator_after': false,
                                'label': 'Remove',
                                'action': (obj) => {
                                    this.deleteFolder(object.id, $node);
                                }
                            };
                        }
                    }

                    if (type === 'page') {
                        menu.open = {
                            'icon': 'fa fa-circle green',
                            'separator_before': false,
                            'separator_after': false,
                            'label': 'Open in New Tab',
                            'action': (obj) => {
                                window.open($node.a_attr['href'], '_blank');
                            }
                        };
                        if ($is_admin || $lnkOwner) {
                            menu.remove = {
                                'icon': 'fa fa-circle green',
                                'separator_before': false,
                                'separator_after': false,
                                'label': 'Remove',
                                'action': (obj) => {
                                    this.deleteLink(object, $node, 'link');
                                }
                            };
                        }
                    }

                    if (type === 'link') {
                        menu.public_copy = {
                            'icon': 'fa fa-circle ' + (object._permis_can_public_copy && !$lnkOwner ? 'green' : 'gray'),
                            'separator_before': false,
                            'separator_after': false,
                            'label': 'Copy to MySpace',
                            'actjstree.get_nodeion': (obj) => {
                                if ($lnkOwner) {
                                    Swal('Info', 'This table is owned by yourself.');
                                    return;
                                }
                                if (!object._permis_can_public_copy) {
                                    Swal('Info', '"Allow Copy for Public Sharing" permission is needed.');
                                    return;
                                }
                                $.LoadingOverlay('show');
                                this.copyObject('table', object.id, this.$root.user.id, [], 'overwrite', null, 'visitor').then(({ data }) => {
                                    this.info_message = 'The table has been copied to folder "MySpace/Transferred"';
                                    this.$root.user.memutree_hash = null;//reload menutree in 10 sec (MainAppWrapper)
                                }).finally(() => {
                                    $.LoadingOverlay('hide');
                                });
                            }
                        };
                        menu.open = {
                            'icon': 'fa fa-circle green',
                            'separator_before': false,
                            'separator_after': false,
                            'label': 'Open in New Tab',
                            'action': (obj) => {
                                window.open($node.a_attr['href'], '_blank');
                            }
                        };
                        menu.favorite = {
                            'icon': 'fa fa-circle green',
                            'separator_before': false,
                            'separator_after': false,
                            'label': 'Favorite',
                            'action': (obj) => {
                                this.favoriteTable(object);
                            }
                        };
                        if ($lnkOwner) {
                            menu.rename = {
                                'icon': 'fa fa-circle green',
                                'separator_before': false,
                                'separator_after': false,
                                'label': 'Rename',
                                'action': (obj) => {
                                    this.renameShared(object, $node);
                                }
                            };
                        }
                        if ($is_admin || $lnkOwner) {
                            menu.remove = {
                                'icon': 'fa fa-circle green',
                                'separator_before': false,
                                'separator_after': false,
                                'label': 'Remove',
                                'action': (obj) => {
                                    this.deleteLink(object, $node);
                                }
                            };
                        }
                    }

                    return menu;
                }

                if (this.tab === 'private') {
                    if (object.in_shared || object.is_system) {
                        return menu;
                    }

                    if (type === 'folder') {
                        menu.add = {
                            'icon': 'fa fa-circle green',
                            'separator_before': false,
                            'separator_after': false,
                            'label': 'Add Folder',
                            'action': (obj) => {
                                this.addFolder(object.id, $node, object.in_shared ? 1 : 0);
                            }
                        };
                        menu.add_table = {
                            'icon': 'fa fa-circle ' + (this.canAddTable ? 'green' : 'gray'),
                            'separator_before': false,
                            'separator_after': false,
                            'label': 'Add Table',
                            'action': (obj) => {
                                if (!this.canAddTable) {
                                    Swal('Info', '"Can add table" permission is needed.');
                                    return;
                                }
                                this.showTablePopup('new', $node);
                            }
                        };
                        if ($is_admin || $is_editor) {
                            menu.add_page = {
                                'icon': 'fa fa-circle green',
                                'separator_before': false,
                                'separator_after': false,
                                'label': 'Add Dashboard',
                                'action': (obj) => {
                                    this.addPage(object.id, $node);
                                }
                            };
                        }
                        menu.add_link = {
                            'icon': 'fa fa-circle green',
                            'separator_before': false,
                            'separator_after': false,
                            'label': 'Create Link',
                            'action': (obj) => {
                                Swal({
                                    title: 'Info',
                                    html: 'Create a Link to the Folder: Select a Tab in the menutree and then a folder in the Tab (folder "UNCATEGORIZED" only in Tab "Public").',
                                    showCancelButton: true,
                                }).then((result) => {
                                    if (result.value) {
                                        this.$emit('update-selected-link', object);
                                    }
                                });
                            }
                        };
                        menu.favorite = {
                            'icon': 'fa fa-circle green',
                            'separator_before': false,
                            'separator_after': false,
                            'label': 'Favorite',
                            'action': (obj) => {
                                this.favoriteFolder(object);
                            }
                        };
                        menu.transfer = {
                            'icon': 'fa fa-circle green',
                            'separator_before': false,
                            'separator_after': false,
                            'label': 'Transfer',
                            'action': (obj) => {
                                this.showToOtherModal('transfer', 'folder', 'Transfer folder', object.id, $node);
                            }
                        };
                        menu.copy = {
                            'icon': 'fa fa-circle green',
                            'separator_before': false,
                            'separator_after': false,
                            'label': 'Copy to Others',
                            'action': (obj) => {
                                this.showCopyFolderToOthers(object);
                            }
                        };
                        menu.edit = {
                            'icon': 'fa fa-circle green',
                            'separator_before': false,
                            'separator_after': false,
                            'label': 'Settings&More',
                            'action': (obj) => {
                                let path = $node.a_attr['href'];
                                this.changeOpenedObject('folder', object.id, object.name, path);
                                this.editedFolderNode = $node;
                            }
                        };
                        menu.remove = {
                            'icon': 'fa fa-circle green',
                            'separator_before': false,
                            'separator_after': false,
                            'label': 'Remove',
                            'action': (obj) => {
                                this.deleteFolder(object.id, $node);
                            }
                        };
                    }

                    if (type === 'table') {
                        menu = {
                            'open': {
                                'icon': 'fa fa-circle green',
                                'separator_before': false,
                                'separator_after': false,
                                'label': 'Open in New Tab',
                                'action': (obj) => {
                                    window.open($node.a_attr['href'], '_blank');
                                }
                            },
                            'link': {
                                'icon': 'fa fa-circle green',
                                'separator_before': false,
                                'separator_after': false,
                                'label': 'Create Link',
                                'action': (obj) => {
                                    Swal({
                                        title: 'Info',
                                        html: 'For creating a link to the table: select a tab under MENU and then a folder in the tab. In tab "Public", only folder "UNCATEGORIZED" can be selected).',
                                        showCancelButton: true,
                                    }).then((result) => {
                                        if (result.value) {
                                            this.$emit('update-selected-link', object);
                                        }
                                    });
                                }
                            },
                            'favorite': {
                                'icon': 'fa fa-circle green',
                                'separator_before': false,
                                'separator_after': false,
                                'label': 'Favorite',
                                'action': (obj) => {
                                    this.favoriteTable(object);
                                }
                            },
                            'table_rename': {
                                'icon': 'fa fa-circle green',
                                'separator_before': false,
                                'separator_after': false,
                                'label': 'Rename',
                                'action': (obj) => {
                                    this.showTablePopup('edit', $node);
                                }
                            },
                            'transfer': {
                                'icon': 'fa fa-circle green',
                                'separator_before': false,
                                'separator_after': false,
                                'label': 'Transfer',
                                'action': (obj) => {
                                    this.showToOtherModal('transfer', 'table', 'Transfer table', object.id, $node);
                                }
                            },
                            'copy': {
                                'icon': 'fa fa-circle green',
                                'separator_before': false,
                                'separator_after': false,
                                'label': 'Copy to Others',
                                'action': (obj) => {
                                    this.showToOtherModal('copy', 'table', 'Copy Table', object.id, $node);
                                }
                            },
                            'remove': {
                                'icon': 'fa fa-circle green',
                                'separator_before': false,
                                'separator_after': false,
                                'label': 'Remove',
                                'action': (obj) => {
                                    this.deleteTable(object, $node);
                                }
                            },
                        }
                    }

                    if (type === 'page') {
                        menu.open = {
                            'icon': 'fa fa-circle green',
                            'separator_before': false,
                            'separator_after': false,
                            'label': 'Open in New Tab',
                            'action': (obj) => {
                                window.open($node.a_attr['href'], '_blank');
                            }
                        };
                        menu.add_link = {
                            'icon': 'fa fa-circle green',
                            'separator_before': false,
                            'separator_after': false,
                            'label': 'Create Link',
                            'action': (obj) => {
                                Swal({
                                    title: 'Info',
                                    html: 'For creating a link to the page: select a tab under MENU and then a folder in the tab. In tab "Public", only folder "UNCATEGORIZED" can be selected).',
                                    showCancelButton: true,
                                }).then((result) => {
                                    if (result.value) {
                                        this.$emit('update-selected-link', object);
                                    }
                                });
                            }
                        };
                        menu.favorite = {
                            'icon': 'fa fa-circle green',
                            'separator_before': false,
                            'separator_after': false,
                            'label': 'Favorite',
                            'action': (obj) => {
                                this.favoritePage(object);
                            }
                        };
                        menu.page_rename = {
                            'icon': 'fa fa-circle green',
                            'separator_before': false,
                            'separator_after': false,
                            'label': 'Rename',
                            'action': (obj) => {
                                this.editPage(object, $node);
                            }
                        };
                        let canPageRemove = $is_admin || $is_owner;
                        menu.page_remove = {
                            'icon': 'fa fa-circle ' + (canPageRemove ? 'green' : 'gray'),
                            'separator_before': false,
                            'separator_after': false,
                            'label': 'Remove',
                            'action': (obj) => {
                                if (!canPageRemove) {
                                    Swal('Info', 'You should be an owner.');
                                    return;
                                }
                                this.deletePage(object, $node);
                            }
                        };
                    }

                    if (type === 'link') {
                        menu.open = {
                            'icon': 'fa fa-circle green',
                            'separator_before': false,
                            'separator_after': false,
                            'label': 'Open in New Tab',
                            'action': (obj) => {
                                window.open($node.a_attr['href'], '_blank');
                            }
                        };
                        // if ($lnkOwner) {
                        //     menu.rename = {
                        //         'icon': 'fa fa-circle green',
                        //         'separator_before': false,
                        //         'separator_after': false,
                        //         'label': 'Rename',
                        //         'action': (obj) => {
                        //             this.renameShared(object, $node);
                        //         }
                        //     };
                        // }
                        menu.favorite = {
                            'icon': 'fa fa-circle green',
                            'separator_before': false,
                            'separator_after': false,
                            'label': 'Favorite',
                            'action': (obj) => {
                                this.favoriteTable(object);
                            }
                        };
                        if (!object._in_shared && !object._in_app) {
                            menu.remove = {
                                'icon': 'fa fa-circle ' + ($lnkOwner ? 'green' : 'gray'),
                                'separator_before': false,
                                'separator_after': false,
                                'label': 'Remove',
                                'action': (obj) => {
                                    if (!$lnkOwner) {
                                        Swal('Info', 'You should be an owner.');
                                        return;
                                    }
                                    this.deleteLink(object, $node);
                                }
                            };
                        }
                    }

                    return menu;
                }

                if (this.tab === 'favorite') {
                    if (type === 'folder') {
                        menu.add = {
                            'icon': 'fa fa-circle green',
                            'separator_before': false,
                            'separator_after': false,
                            'label': 'Add Folder',
                            'action': (obj) => {
                                this.addFolder(object.id, $node, object.in_shared ? 1 : 0);
                            }
                        };
                        menu.remove = {
                            'icon': 'fa fa-circle green',
                            'separator_before': false,
                            'separator_after': false,
                            'label': 'Remove',
                            'action': (obj) => {
                                this.deleteFolder(object.id, $node);
                            }
                        };
                    }

                    if (type === 'link' || type === 'table' || type === 'page') {
                        menu.open = {
                            'icon': 'fa fa-circle green',
                            'separator_before': false,
                            'separator_after': false,
                            'label': 'Open in New Tab',
                            'action': (obj) => {
                                window.open($node.a_attr['href'], '_blank');
                            }
                        };
                        menu.remove = {
                            'icon': 'fa fa-circle green',
                            'separator_before': false,
                            'separator_after': false,
                            'label': 'Remove',
                            'action': (obj) => {
                                this.deleteLink(object, $node, 'favorite');
                            }
                        };
                    }
                }

                if (this.tab === 'account') {
                    if (type === 'link') {
                        menu.open = {
                            'icon': 'fa fa-circle green',
                            'separator_before': false,
                            'separator_after': false,
                            'label': 'Open in New Tab',
                            'action': (obj) => {
                                window.open($node.a_attr['href'], '_blank');
                            }
                        };
                    }
                }

                return menu;
            },
            publicCopyMenu(canPublicCopy) {
                return {
                    'icon': 'fa fa-circle ' + (canPublicCopy ? 'green' : 'gray'),
                    'separator_before': false,
                    'separator_after': false,
                    'label': 'Copy to MySpace',
                    'action': (obj) => {
                        if (!this.$root.user.id) {
                            Swal('Info', 'Only logged-in users can copy folders to their private MySpace.');
                            return;
                        }
                        if (!canPublicCopy) {
                            Swal('Info', 'This folder is owned by yourself.');
                            return;
                        }
                        $.LoadingOverlay('show');
                        this.copyObject('folder', object.id, this.$root.user.id, [], 'overwrite', null, 'with_links').then(({data}) => {
                            this.info_message = 'The Folder has been copied to folder "MySpace/Transferred"';
                            this.$root.user.memutree_hash = null;//reload menutree in 10 sec (MainAppWrapper)
                        }).finally(() => {
                            $.LoadingOverlay('hide');
                        });
                    }
                };
            },
            treeCheckCallback(operation, node, node_parent) {
                if (operation === 'move_node' || operation === 'copy_node') {
                    let is_admin = this.$root.user.is_admin;
                    let node_li = node.li_attr || {};
                    let node_object = node_li['data-object'] || {};
                    let parent_li = node_parent.li_attr || {};
                    let parent_object = parent_li['data-object'] || {};

                    let tables_count = 0;
                    _.each(node_parent.children, (ch) => {
                        let ch_node = $(this.$refs.jstree).jstree().get_node(ch);
                        if (ch_node && ch_node.li_attr && ch_node.li_attr['data-type'] !== 'folder') {
                            tables_count++;
                        }
                    });

                    //cannot move/copy 'Pages' object
                    if (node_li['data-type'] === 'page') {
                        return false;
                    }

                    //cannot move 'folder link'
                    if ((!is_admin && node_object.is_folder_link) || (node_object.link && node_object.link.is_folder_link)) {
                        return false;
                    }

                    //cannot change system objects
                    if (node_object.is_system || parent_object.is_system) {
                        return false;
                    }

                    //cannot move/copy from/in SHARED/APP folders
                    if (node_object.in_shared || parent_object.in_shared || node_object._in_shared || parent_object._in_shared) {
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
            favoriteTable(object, parent_id) {
                axios.put('/ajax/table/favorite', {
                    table_id: object.id,
                    parent_id: parent_id,
                    favorite: true
                }).then(({ data }) => {
                    this.$root.user.memutree_hash = null;//reload menutree in 10 sec (MainAppWrapper)
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                });
            },
            favoriteFolder(object) {
                axios.put('/ajax/folder/favorite', {
                    folder_id: object.id,
                    favorite: true
                }).then(({ data }) => {
                    this.$root.user.memutree_hash = null;//reload menutree in 10 sec (MainAppWrapper)
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                });
            },
            favoritePage(object) {
                axios.put('/ajax/pages/favorite', {
                    page_id: object.id,
                    favorite: true
                }).then(({ data }) => {
                    this.$root.user.memutree_hash = null;//reload menutree in 10 sec (MainAppWrapper)
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
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
                if (this.toOtherModal.user_select || user_id) {
                    axios.post('/ajax/'+this.toOtherModal.object_type+'/transfer', {
                        id: this.toOtherModal.object_id,
                        new_user_or_group: this.toOtherModal.user_select || user_id,
                        table_with: [],
                    }).then(({ data }) => {
                        //if name of the object is present in the URL path
                        if ( window.location.href.indexOf(node_name) > -1 ) {
                            this.changeOpenedObject('table', 0, '', '/data/');
                        }
                        this.sync_tables();
                    }).catch(errors => {
                        Swal('Info', getErrors(errors));
                    });

                    $.LoadingOverlay('show');
                    setTimeout(() => {
                        $(this.$refs.jstree).jstree('delete_node', this.toOtherModal.node);
                        this.toOtherModal.show = false;
                        $.LoadingOverlay('hide');
                    }, 500);
                }
            },
            copyObject(object_type, object_id, user_id, $with, proceed_type, new_name, visitor, direct_folder_id) {
                //Folder Copy
                if (object_type === 'folder') {
                    return new Promise((resolve) => {
                        axios.get('/ajax/folder', {
                            params: {
                                folder_id: object_id,
                                structure: this.tab,
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
                                with_links: visitor,
                                direct_folder_id: direct_folder_id,
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
                        new_user_or_group: String(user_id),
                        table_with: $with,
                        overwrite: proceed_type === 'overwrite',
                        new_name: new_name,
                        visitor: visitor,
                        direct_folder_id: direct_folder_id,
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

                    //update URL
                    if ( window.location.href.indexOf(nmo.object.name) > -1 ) {
                        let path = data.path || nmo.$node.a_attr['href'];
                        this.changeOpenedObject(nmo.sel_type, nmo.object.id, nmo.object.name, path);
                    }

                    this.sync_tables();
                    this.$root.user.memutree_hash = null;//reload menutree in 10 sec (MainAppWrapper)
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
            popupCopyObject() {
                let user_id = $(this.$refs.search_user).val();
                if (this.toOtherModal.user_select || user_id) {
                    this.copyObject(
                        this.toOtherModal.object_type,
                        this.toOtherModal.object_id,
                        this.toOtherModal.user_select || user_id,
                        this.toOtherModal._with,
                        this.toOtherModal.proceed_type,
                        this.toOtherModal.new_name
                    ).then(({ data }) => {
                        if (data.error && data.msg.length) {
                            if (data.already_copied) {
                                this.already_copied_popup_show = this.toOtherModal.object_type;
                            } else {
                                this.info_message = data.msg;
                            }
                        } else {
                            if (this.toOtherModal.proceed_type === 'overwrite') {
                                this.info_message = 'Table copied and overwrote existing.';
                            }
                            if (this.toOtherModal.proceed_type === 'rename' && this.toOtherModal.new_name) {
                                this.info_message = 'Table copied and renamed as '+this.toOtherModal.new_name+'.';
                            }
                            if (this.$root.user.id == user_id) {
                                this.$root.user.memutree_hash = null;//reload menutree in 10 sec (MainAppWrapper)
                            }
                        }
                    }).catch(errors => {
                        Swal('Info', getErrors(errors));
                    });

                    $.LoadingOverlay('show');
                    setTimeout(() => {
                        this.toOtherModal.show = false;
                        $.LoadingOverlay('hide');
                    }, 500);
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
                this.changeOpenedObject('table', data.table_id, data.table_object.name, data.path);
                this.sync_tables();
                this.tablePopup.type = null;
            },
            editTable($node, old_name, new_name) {
                this.updateObjectLinks($node, old_name, new_name, 1);
                $(this.$refs.jstree).jstree('rename_node', $node, new_name);
                this.sync_menu_tree($node);
                this.sync_tables();
                this.tablePopup.type = null;
            },
            deleteTable(object, $node) {
                Swal({
                    title: 'Info',
                    text: 'Delete Table. All data would be completely removed! Confirm to proceed.',
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
                            if (object.id === this.object_id) {
                                this.changeOpenedObject('table', 0, '', '/data/');
                            }
                            $(this.$refs.jstree).jstree('delete_node', $node);
                            this.sync_tables();
                        }).catch(errors => {
                            Swal('Info', getErrors(errors));
                        }).finally(() => {
                            $.LoadingOverlay('hide');
                        });
                    }
                });
            },
            renameShared(object, $node) {
                Swal({
                    title: 'Info',
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
                            Swal('Info', getErrors(errors));
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
            storeFolder(name, description, params_object) {
                let $node = params_object.$node;
                if (name) {
                    name = this.$root.safeName(name);
                    $.LoadingOverlay('show');
                    axios.post('/ajax/folder', {
                        parent_id: params_object.folder_id,
                        name: name,
                        description: description,
                        structure: this.tab,
                        in_shared: params_object.in_shared
                    }).then(({ data }) => {
                        $(this.$refs.jstree).jstree().create_node($node, data, 'last', false, false);
                        $(this.$refs.jstree).jstree().open_node($node);
                    }).catch(errors => {
                        Swal('Info', getErrors(errors));
                    }).finally(() => {
                        $.LoadingOverlay('hide');
                    });
                } else {
                    this.info_message = 'You need to write something!';
                }
                this.folderPopup.active = false;
            },
            deleteFolder(folder_id, $node) {
                let node_name = $node.li_attr['data-object'].name;
                Swal({
                    title: 'Info',
                    text: 'Delete Folder. All folder and table/data nodes under this folder would be completely removed! Confirm to proceed.',
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
                                this.changeOpenedObject('table', 0, '', '/data/');
                            }
                            $(this.$refs.jstree).jstree('delete_node', $node);
                            this.sync_tables();
                        }).catch(errors => {
                            Swal('Info', getErrors(errors));
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
                    if (data.memutree_hash) {
                        this.$root.user.memutree_hash = data.memutree_hash;
                    }
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {});
            },

            //Menu functions for 'folders' -----------------------------------------------------------------------------
            addPage(folder_id, $node) {
                this.pagePopup.page_id = null;
                this.pagePopup.type = 'new';
                this.pagePopup.folder_id = folder_id;
                this.pagePopup.$node = $node;
                this.pagePopup.active = true;
            },
            editPage(page, $node) {
                this.pagePopup.page_name = page.name;
                this.pagePopup.type = 'edit';
                this.pagePopup.page_id = page.id;
                this.pagePopup.$node = $node;
                this.pagePopup.active = true;
            },
            storeNewPage(name, params_object) {
                let $node = params_object.$node;
                if (name) {
                    name = this.$root.safeName(name);
                    $.LoadingOverlay('show');
                    axios.post('/ajax/pages', {
                        folder_id: params_object.folder_id,
                        folder_path: $node ? $node.a_attr['href'] : '',
                        name: name,
                        structure: this.tab,
                    }).then(({ data }) => {
                        if ($node) {
                            $(this.$refs.jstree).jstree().create_node($node, data, 'first', false, false);
                        }
                    }).catch(errors => {
                        Swal('Info', getErrors(errors));
                    }).finally(() => {
                        $.LoadingOverlay('hide');
                    });
                } else {
                    this.info_message = 'You need to write something!';
                }
                this.pagePopup.active = false;
            },
            storeUpdatedPage(name, params_object) {
                let $node = params_object.$node;
                if (name) {
                    name = this.$root.safeName(name);
                    $.LoadingOverlay('show');
                    axios.put('/ajax/pages', {
                        page_id: params_object.page_id,
                        fields: {
                            name: name,
                        },
                    }).then(({ data }) => {
                        $(this.$refs.jstree).jstree().rename_node($node, name);
                    }).catch(errors => {
                        Swal('Info', getErrors(errors));
                    }).finally(() => {
                        $.LoadingOverlay('hide');
                    });
                } else {
                    this.info_message = 'You need to write something!';
                }
                this.pagePopup.active = false;
            },
            deletePage(object, $node) {
                Swal({
                    title: 'Info',
                    text: 'Delete Page. All data would be completely removed! Confirm to proceed.',
                    confirmButtonClass: 'btn-danger',
                    confirmButtonText: 'Yes',
                    showCancelButton: true,
                    animation: 'slide-from-top'
                }).then(response => {
                    if (response.value) {
                        $.LoadingOverlay('show');
                        axios.delete('/ajax/pages', {
                            params: {
                                page_id: object.id
                            }
                        }).then(({ data }) => {
                            $(this.$refs.jstree).jstree('delete_node', $node);
                        }).catch(errors => {
                            Swal('Info', getErrors(errors));
                        }).finally(() => {
                            $.LoadingOverlay('hide');
                        });
                    }
                });
            },

            //Menu functions for 'links' -------------------------------------------------------------------------------
            createLink(object_id, object, $node, type) {
                $.LoadingOverlay('show');
                axios.post('/ajax/'+type+'/link', {
                    object_id: object_id,
                    folder_id: object.id,
                    type: 'link',
                    structure: this.tab,
                    path: $node.a_attr['href'],
                }).then(({ data }) => {
                    if (type === 'folder') {
                        this.$root.user.memutree_hash = null;
                        eventBus.$emit('event-reload-menu-tree');
                    } else {
                        $(this.$refs.jstree).jstree().create_node($node, data, 'last', false, false);
                        $(this.$refs.jstree).jstree().open_node($node);
                    }
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
            deleteLink(object, $node, isPageSuffix) {
                Swal({
                    title: 'Info',
                    text: 'Delete Link',
                    confirmButtonClass: 'btn-danger',
                    confirmButtonText: 'Yes',
                    showCancelButton: true,
                    animation: 'slide-from-top'
                }).then(response => {
                    if (response.value) {
                        $.LoadingOverlay('show');

                        let $axios = null;

                        if (!isPageSuffix) {
                            $axios = axios.delete('/ajax/table/link', {
                                params: {
                                    table_id: object.id,
                                    link_id: object.link.id,
                                }
                            });
                        }
                        if (isPageSuffix === 'link') {
                            $axios = axios.delete('/ajax/pages/link', {
                                params: {
                                    page_id: object.id,
                                    link_id: object.link.id,
                                }
                            });
                        }
                        if (isPageSuffix === 'favorite') {
                            $axios = axios.put('/ajax/pages/favorite', {
                                page_id: object.id,
                                favorite: false
                            })
                        }

                        $axios.then(({ data }) => {
                            $(this.$refs.jstree).jstree('delete_node', $node);
                        }).catch(errors => {
                            Swal('Info', getErrors(errors));
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

                _.each($node.children, (id) => {
                    let child_node = $(this.$refs.jstree).jstree('get_node', id);
                    this.updateObjectLinks(child_node, old_name, new_name, lvl+1);
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
                    ($.inArray(this.tab, ['public']) > -1 && this.$root.user.is_admin)
                ) {
                    if (window.event.target.nodeName != 'I' && window.event.target.nodeName != 'A') {
                        let y_offset = this.$refs && this.$refs.tree_wrap
                            ? this.$refs.tree_wrap.getBoundingClientRect().top + 5
                            : 160;
                        this.context_menu.canaddtable = $.inArray(this.tab, ['private']) > -1;
                        this.context_menu.active = true;
                        this.context_menu.x = window.event.clientX - 10;
                        this.context_menu.y = window.event.clientY - y_offset;
                    }
                }
            },
            clearSearchIndex(e) {
                this.search_index = 0;
            },
            searchKey(e) {
                if (e.keyCode == 13) {
                    this.searchInTab();
                }
            },
            searchInTab() {
                $(this.$refs.jstree).jstree().search( this.searchVal );
            },
            sync_tables() {
                axios.get('/ajax/table-data/available-tables', {
                }).then(({ data }) => {
                    this.settingsMeta.available_tables = data;
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
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
            highligtSelectedNode() {
                let jstre = $(this.$refs.jstree).jstree();
                if (!jstre || !jstre.get_json) {
                    return;
                }
                if (jstre.deselect_all) {
                    jstre.deselect_all(true);
                }

                let node_id = '';
                let hrefs = [window.location.href];
                jstre.get_json(null, { 'flat': true }).forEach(function (node) {
                    if ($.inArray(node.a_attr['href'], hrefs) > -1) {
                        node_id = node.id;
                    }
                });

                this.prevent_recusrion = !!node_id;
                if (node_id) {
                    jstre.select_node('#' + node_id, true);
                }
            },
            saveRecent(type, object_id) {
                axios.post('/ajax/menutree/recent', {
                    type: type || this.object_type,
                    id: object_id || this.object_id,
                }).then(({data}) => {
                    this.$root.user._menutree_recents = data;

                    _.each(this.$root.user._menutree_recents, (recent) => {
                        this.$set(recent, '_hover', false);
                    });
                });
            },
            daysDiff(date) {
                let diff = moment(date).diff(moment().startOf('day'), 'days');
                return diff < 0 ? diff : '';//remove zeros
            },
            emptAddTable() {
                if (this.canAddTable) {
                    this.showTablePopup('new', null);
                } else {
                    Swal('Info', '"Can add table" permission is needed.');
                }
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
                minimumInputLength: {val:3},
                width: '100%',
                height: '100%'
            });
            $(this.$refs.search_user).next().css('height', '30px');

            if (this.tab === 'folder_view') {
                let $nn = SpecialFuncs.findInTree(this.tab_tree, this.object_id, 'table');
                window.location.hash = $nn ? $nn['a_attr']['href'] : '';
            }
            if (this.tab === 'favorite') {
                this.saveRecent();
            }

            eventBus.$on('re-highlight-menu-tree', this.highligtSelectedNode);
        },
        beforeDestroy() {
            eventBus.$off('re-highlight-menu-tree', this.highligtSelectedNode);
        }
    }
</script>

<style>
    a.jstree-anchor.user-available-link {
        color: green;
    }
    a.jstree-anchor.currently-selected-obj {
        color: blue;
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
        position: relative;

        input {
            width: 100%;
        }

        button {
            border: none;
            width: 25px;
            background-color: transparent;
            padding: 0;
            font-size: 1.7em;
        }
    }

    .recent-wrap {
        height: calc(100% - 35px);
        padding: 3px;
        overflow: auto;

        .recent-block {
            display: block;
        }
        .recent-time {
            position: absolute;
            right: 0;
            top: 0;
        }
    }
</style>