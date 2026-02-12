<template>
    <div class="full-height" style="position:relative;">

        <div class="jstree-wrapper"
             :class="{'full-height': !with_search}"
             @contextmenu.prevent="contextMenuOnEmpty()"
        >
            <div ref="jstree"></div>
        </div>

        <div v-if="with_search" class="search-in-tree flex">
            <input type="text"
                   class="form-control"
                   placeholder="Type to search for tables or links to tables"
                   @input="clearSearchIndex"
                   @keydown="searchKey"
                   v-model="searchVal">
            &nbsp;
            <button class="btn btn-default" @click="searchInTab()"><i class="fa fa-search"></i></button>
        </div>

        <!--Context Menu On Empty Place-->
        <ul v-if="$root.user.is_admin || $root.user.role_id == 3"
            v-show="context_menu.active"
            class="my_context_menu vakata-context jstree-contextmenu jstree-default-contextmenu"
            :style="{left: context_menu.x+'px', top: context_menu.y+'px'}"
            @mouseleave="context_menu.active = false"
        >
            <li @click="addObject(null, null)">
                <a href="#" rel="0"><i></i><span class="vakata-contextmenu-sep">&nbsp;</span>Add Page</a>
            </li>
            <li @click="addObject(null, null, 1)">
                <a href="#" rel="0"><i></i><span class="vakata-contextmenu-sep">&nbsp;</span>Add Folder</a>
            </li>
        </ul>

        <!--Edit Page/Folder form-->
        <div v-if="edit_popup.show" class="modal-wrapper">
            <div class="modal">
                <div class="modal-dialog modal--350">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title">{{ edit_popup.is_folder ? 'Edit Folder' : 'Edit Page' }}</h4>
                        </div>
                        <div class="modal-body">
                            <table class="edit__table">
                                <tr>
                                    <td>
                                        <label>Name:&nbsp;</label>
                                    </td>
                                    <td>
                                        <input class="form-control" type="text" v-model="edit_popup.object.name">
                                    </td>
                                </tr>
                                <tr v-if="!edit_popup.is_folder">
                                    <td>
                                        <label>Node Type:&nbsp;</label>
                                    </td>
                                    <td>
                                        <select v-model="edit_popup.object.node_icon" class="form-control">
                                            <option>Link</option>
                                            <option>YouTube</option>
                                            <option>Page</option>
                                            <option>PowerPoint</option>
                                            <option>PDF</option>
                                            <option>File</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr v-if="!edit_popup.is_folder && edit_popup.object.node_icon === 'Link'">
                                    <td>
                                        <label>Address:&nbsp;</label>
                                    </td>
                                    <td>
                                        <input class="form-control" type="text" v-model="edit_popup.object.link_address">
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <label>Order:&nbsp;</label>
                                    </td>
                                    <td>
                                        <input class="form-control control--50" type="number" v-model="edit_popup.object.order">
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <label>Is Active:&nbsp;</label>
                                    </td>
                                    <td>
                                        <label class="switch_t">
                                            <input type="checkbox" v-model="edit_popup.object.is_active">
                                            <span class="toggler round"></span>
                                        </label>
                                    </td>
                                </tr>
                                <tr v-if="!edit_popup.is_folder">
                                    <td>
                                        <label>Save a Copy:&nbsp;</label>
                                    </td>
                                    <td>
                                        <label class="switch_t">
                                            <input type="checkbox" v-model="edit_popup.object.is_active">
                                            <span class="toggler round"></span>
                                        </label>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-success" @click="editObject()">OK</button>
                            <button type="button" class="btn btn-default" @click="edit_popup.show = false">Cancel</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</template>

<script>
    import {eventBus} from "../../app";

    export default {
        name: 'PagesTree',
        data() {
            return {
                context_menu: {
                    active: false,
                    x: 0,
                    y: 0
                },
                edit_popup: {
                    show: false,
                    object: null,
                    node: null,
                    is_folder: false,
                },
                searchVal: '',
                search_index: 0,
            }
        },
        props: {
            type: String,
            page_id: Number,
            tree: Array,
            with_search: Boolean,
        },
        methods: {
            //additional functions
            contextMenuOnEmpty() {
                if (this.$root.user.is_admin || this.$root.user.role_id == 3) {
                    if (window.event.target.nodeName != 'I' && window.event.target.nodeName != 'A') {
                        this.context_menu.active = true;
                        this.context_menu.x = window.event.clientX - 10;
                        this.context_menu.y = window.event.clientY - 10;
                    }
                }
            },
            updateObjectLinks($node, old_url, new_url, lvl) {
                let new_path = window.location.href.replace(/%20/gi, ' ');
                let old_path = new_path;
                new_path = new_path.replace('/'+old_url, '/'+new_url);
                if (lvl === 1 && new_path !== old_path) {
                    window.history.pushState(new_url, new_url, new_path);
                }
                $node.a_attr['href'] = $node.a_attr['href'].replace('/'+old_url, '/'+new_url);

                _.each($node.children, (id) => {
                    let child_node = $(this.$refs.jstree).jstree('get_node', id);
                    this.updateObjectLinks(child_node, old_url, new_url, lvl+1);
                });

                if (lvl === 1) {
                    $(this.$refs.jstree).jstree('redraw', true);
                }
            },

            // creating JsTree -----------------------------------------------------------------------------------------
            createTreeMenu() {
                let self = this;
                $(this.$refs.jstree).jstree( this.getContextMenu() )
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
                        if (data.is_multi) {
                            self.jstree_move_node(data);//move between trees
                        } else {
                            Swal("Copy is not available");
                        }
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
                //only for left click
                let evt =  window.event || e;
                let button = evt.which || evt.button;
                if( button !== 1 && ( typeof button !== 'undefined')) return false;
                //
                let pageObject = $node.li_attr['data-object'];

                if (!pageObject.is_folder) {
                    if (pageObject.link_address) {
                        location.href = pageObject.link_address;
                    } else {
                        this.$emit('selected-page', pageObject, $node.a_attr['href']);
                    }
                }
                return false;
            },
            jstree_toggle_node($node, is_open) {
                let icon = $('#' + $node.id).find('i.jstree-icon.jstree-themeicon').first();
                if (icon.hasClass('fa-folder') || icon.hasClass('fa-folder-open')) {
                    icon
                        .removeClass(is_open ? 'fa-folder' : 'fa-folder-open')
                        .addClass(is_open ? 'fa-folder-open' : 'fa-folder');
                }
            },
            jstree_move_node(param) {
                let $node = param.node;
                let target_folder = param.instance.get_node(param.parent);
                let isRoot = target_folder && !target_folder.parent;
                let targetObject = !isRoot
                    ? target_folder.li_attr['data-object'] //folder
                    : { id: null, type: this.type }; //root of the tree
                let pageObject = $node.li_attr['data-object'];

                $.LoadingOverlay('show');
                axios.put('/ajax/static-page/move', {
                    page_id: pageObject.id,
                    folder_id: targetObject.id,
                    position: param.position,
                    type: this.type,
                }).then(({ data }) => {
                    if (pageObject.is_folder) {
                        $(this.$refs.jstree).jstree().open_node($node, false, false);
                    }
                    this.updateObjectLinks($node, pageObject.type, targetObject.type, 1);
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },

            //creating context menu ------------------------------------------------------------------------------------
            getContextMenu() {
                let self = this;
                let plugins = ['search'];
                let context_menu = {
                    'contextmenu': {
                        'items': function ($node) {
                            return {};
                        }
                    },
                    'core' : {
                        'data' : this.tree,
                        'check_callback': true,
                    }
                };
                if (this.$root.user.is_admin || this.$root.user.role_id == 3) {
                    plugins.push('dnd');
                    context_menu.core.check_callback = function (operation, node, node_parent, node_position, more) {
                        return self.treeCheckCallback(this, operation, node, node_parent);
                    };

                    plugins.push('contextmenu');
                    context_menu.contextmenu.items = function ($node) {
                        return self.getContextMenuItems($node);
                    };
                }

                context_menu.plugins = plugins;
                return context_menu;
            },
            getContextMenuItems($node) {
                let self = this;
                let pageObject = $node.li_attr['data-object'];
                let menu = {};

                if (pageObject.is_folder) {
                    menu.add_folder = {
                        'separator_before': false,
                        'separator_after': false,
                        'label': 'Add Folder',
                        'action': function (obj) {
                            self.addObject(pageObject, $node, 1);
                        }
                    };
                    menu.edit_folder = {
                        'separator_before': false,
                        'separator_after': false,
                        'label': 'Edit Folder',
                        'action': function (obj) {
                            self.showEditObjectPopup(pageObject, $node, 1);
                        }
                    };
                    menu.remove_folder = {
                        'separator_before': false,
                        'separator_after': false,
                        'label': 'Delete Folder',
                        'action': function (obj) {
                            self.deleteObject(pageObject, $node, 1);
                        }
                    };
                    menu.add = {
                        'separator_before': true,
                        'separator_after': false,
                        'label': 'Add Page',
                        'action': function (obj) {
                            self.addObject(pageObject, $node);
                        }
                    };
                } else {
                    menu.edit = {
                        'separator_before': false,
                        'separator_after': false,
                        'label': 'Edit Page',
                        'action': function (obj) {
                            self.showEditObjectPopup(pageObject, $node);
                        }
                    };
                    menu.remove = {
                        'separator_before': false,
                        'separator_after': false,
                        'label': 'Delete Page',
                        'action': function (obj) {
                            self.deleteObject(pageObject, $node);
                        }
                    };
                }

                return menu;
            },
            treeCheckCallback(jstree, operation, node, node_parent) {
                if (operation === "move_node") {
                    if ([1,3].indexOf(this.$root.user.role_id) < 0) {//Admin and Editor can move
                        return false;
                    }
                    let parentObject = node_parent && node_parent.li_attr ? node_parent.li_attr['data-object'] : null;
                    if (parentObject && !parentObject.is_folder) {
                        return false;
                    }
                }
                return true; //allow all other operations
            },

            //Menu functions for 'Pages' ------------------------------------------------------------------------------
            addObject(pageObject, $node, $is_folder = 0) {
                Swal({
                    title: 'Info',
                    input: 'text',
                    showCancelButton: true,
                    animation: 'slide-from-top',
                    inputPlaceholder: 'Name of the '+($is_folder ? 'Folder' : 'Page'),
                    inputValidator: (value) => {
                        return !value && 'You need to write something!'
                    }
                }).then(response => {
                    if (response.value) {
                        $.LoadingOverlay('show');
                        axios.post('/ajax/static-page', {
                            fields: {
                                type: this.type,
                                name: response.value,
                                parent_id: pageObject ? pageObject.id : null,
                                is_folder: $is_folder
                            },
                            page_url: $node ? $node.a_attr['href'] : '/'+this.type+'/',
                        }).then(({ data }) => {
                            $(this.$refs.jstree).jstree().create_node($node, data, 'last', false, false);
                            $(this.$refs.jstree).jstree().open_node($node);
                        }).catch(errors => {
                            Swal('Info', getErrors(errors));
                        }).finally(() => {
                            $.LoadingOverlay('hide');
                        });
                    }
                });
            },
            showEditObjectPopup(pageObject, $node, $is_folder = 0) {
                this.edit_popup.show = true;
                this.edit_popup.object = pageObject;
                this.edit_popup.node = $node;
                this.edit_popup.is_folder = $is_folder;
            },
            getIcon($page) {
                if ($page.is_folder) {
                    return 'fa fa-folder-open';
                }
                let $link;
                switch ($page.node_icon) {
                    case 'YouTube': $link = 'fab fa-youtube';
                        break;
                    case 'Page': $link = 'far fa-file-alt';
                        break;
                    case 'PowerPoint': $link = 'fas fa-file-powerpoint';
                        break;
                    case 'PDF': $link = 'fas fa-file-pdf';
                        break;
                    case 'File': $link = 'far fa-copy';
                        break;
                    default: $link = 'fa fa-link';
                }
                return $link;
            },
            editObject() {
                let $page = this.edit_popup.object;
                let $node = this.edit_popup.node;
                $.LoadingOverlay('show');
                axios.put('/ajax/static-page', {
                    page_id: $page.id,
                    fields: $page
                }).then(({ data }) => {
                    $node.a_attr['class'] = ($page.is_active ? '' : 'tree_not_active');
                    $node.icon = this.getIcon($page);
                    this.updateObjectLinks($node, $page.url, data, 1);
                    $page.url = data;
                    $(this.$refs.jstree).jstree('rename_node', $node, $page.name);
                    this.$emit('selected-page', $page, $node.a_attr['href']);
                    this.edit_popup.show = false;
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
            deleteObject(pageObject, $node, $is_folder = 0) {
                Swal({
                    title: 'Info',
                    text: 'Delete '+($is_folder ? 'Folder' : 'Page')+'. Are you sure?',
                    showCancelButton: true,
                }).then((result) => {
                    if (result.value) {
                        $.LoadingOverlay('show');
                        axios.delete('/ajax/static-page', {
                            params: {
                                page_id: pageObject.id
                            }
                        }).then(({ data }) => {
                            if (pageObject.id === this.page_id) {
                                window.location.href = '/getstarted';
                            }
                            $(this.$refs.jstree).jstree('delete_node', $node);
                        }).catch(errors => {
                            Swal('Info', getErrors(errors));
                        }).finally(() => {
                            $.LoadingOverlay('hide');
                        });
                    }
                });
            },
            //SEARCH
            clearSearchIndex() {
                this.search_index = 0;
            },
            searchKey(e) {
                if (e.keyCode == 13) {
                    this.searchInTab();
                }
            },
            searchInTab(external) {
                $(this.$refs.jstree).jstree().search( external || this.searchVal );
            },
            externalSearch(search, withClear) {
                if (withClear) {
                    this.clearSearchIndex();
                }
                this.searchInTab(search);
            },
        },
        mounted() {
            if (this.tree) {
                this.createTreeMenu();
            }
            eventBus.$on('static-page-global-search', this.externalSearch);
        },
        beforeDestroy() {
            eventBus.$off('static-page-global-search', this.externalSearch);
        }
    }
</script>

<style>
    .tree_not_active {
        background-color: #CCC;
    }
</style>

<style lang="scss" scoped>
    .jstree-wrapper {
        height: calc(100% - 38px);
        overflow: auto;
    }
    .my_context_menu {
        position: fixed;
        z-index: 2500;
        display: block;
        left: 10px;
        top: 10px;
    }

    .modal-wrapper {
        position: fixed;
        top: 0;
        left: 0;
        bottom: 0;
        right: 0;
        z-index: 1500;
        background: rgba(0, 0, 0, 0.45);
        overflow: auto;

        .modal--350 {
            width: 350px;
        }

        .modal {
            display: block;
            position: absolute;
            bottom: auto;

            .modal-header {
                text-align: center;
                background-color: #444;
                padding: 5px;

                .modal-title {
                    font-size: 2em;
                    font-weight: bold;
                    color: #FFF;
                }
            }

            .modal-body {
                .edit__table {
                    width: 100%;

                    td {
                        padding-bottom: 15px;
                    }

                    .control--50 {
                        width: 50%;
                    }

                    label {
                        font-weight: normal;
                    }
                }
            }
        }
    }

    .search-in-tree {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;

        input {
            width: 100%;
        }

        button {
            border: none;
            width: 25px;
            background-color: transparent;
            padding: 0;
            font-size: 1.6em;
        }
    }
</style>