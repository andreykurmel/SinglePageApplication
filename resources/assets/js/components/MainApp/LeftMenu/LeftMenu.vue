<template>
    <div id="left-menu" :style="leftMenuStyle">
        <div class="menu-header">
            <info-sign-link :app_sett_key="'help_link_menutree'" :hgt="24" class="flo-left"></info-sign-link>
            <button v-if="!$root.sideIsNa('side_left_filter')"
                    class="btn btn-default"
                    :class="{active : isFilters}"
                    @click="isFilters = true"
            >FILTER</button>
            <button v-if="!$root.sideIsNa('side_left_menu')"
                    class="btn btn-default"
                    :class="{active : !isFilters}"
                    @click="isFilters = false"
            >MENU</button>
        </div>
        <div class="menu-body" ref="menu_body">
            <filters-block
                    v-if="$root.filters && isFilters"
                    :table-meta="$root.tableMeta"
                    :input_filters="$root.filters"
                    @changed-filter="changedFilter"
            ></filters-block>
            <template v-if="should_build_tree_tabs">
                <div v-if="!embed || $root.user._is_folder_view" v-show="!isFilters" class="full-height position-relative">
                    <ul class="nav nav-tabs">
                        <li v-for="tab in tabs" :class="{active : currentTabId === tab.id}">
                            <a @click.prevent="changeLeftTab(tab.id)">{{tab.name}}</a>
                        </li>
                    </ul>
                    <template v-for="tab in tabs">
                        <left-menu-tree-item
                                v-show="currentTabId === tab.id"
                                :tab="tab.id"
                                :tab_tree="($root.user.id || $root.user._is_folder_view || tab.id === 'public' ? tree[tab.id] : [])"
                                :table_id="table_id"
                                :selected-link="selectedLink"
                                :settings-meta="settingsMeta"
                                :tab-obj="tab"
                                @update-object-id="updateObjectId"
                                @update-selected-link="updateSelectedLink"
                                @reload-menu-tree="reloadMenuTree"
                        ></left-menu-tree-item>
                    </template>
                </div>
            </template>
        </div>

        <header-resizer :table-header="left_menu_sizes" @header-resizing-ends="saveSizes"></header-resizer>

    </div>
</template>

<script>
    import {eventBus} from './../../../app';

    import LeftMenuTreeItem from './LeftMenuTreeItem';
    import SliderFilterElem from "./SliderFilterElem";
    import InfoSignLink from "../../CustomTable/Specials/InfoSignLink";
    import HeaderResizer from "../../CustomTable/Header/HeaderResizer";
    import FiltersBlock from "../../CommonBlocks/FiltersBlock";

    export default {
        name: 'LeftMenu',
        components: {
            FiltersBlock,
            HeaderResizer,
            InfoSignLink,
            SliderFilterElem,
            LeftMenuTreeItem
        },
        data() {
            return {
                selectedLink: null,
                currentTabId: Cookies.get('left_tab') || (this.$root.user.id ? 'private' : 'public'),
                isFilters: this.$root.sideIsNa('side_left_menu'),
                currentField: null,
                filterHeight: '0px',
                tabs: [],
                tree: null,
                should_build_tree_tabs: false,
                left_menu_sizes: {
                    width: localStorage.getItem('left_menu_width') || 250,
                    max_width: 400,
                    min_width: 260,
                },
            }
        },
        props: {
            table_id: Number,
            app_domain: String,
            settingsMeta: Object,
            embed: Number,
            view_tree: Object,
        },
        computed: {
            leftMenuStyle() {
                return {
                    flexShrink: 0,
                    flexGrow: 0,
                    flexBasis: this.left_menu_sizes.width+'px',
                    width: this.left_menu_sizes.width+'px',
                };
            },
        },
        methods:{
            saveSizes() {
                localStorage.setItem('left_menu_width', this.left_menu_sizes.width);
            },
            changeLeftTab(tab) {
                this.currentTabId = tab;
                Cookies.set('left_tab', tab, {
                    domain: this.app_domain,
                    expires: 365
                });
            },
            changedFilter() {
                eventBus.$emit('changed-filter');
            },
            updateObjectId(type, object_id) {
                this.$emit('update-object-id', type, object_id);
            },
            updateSelectedLink(selectedLink) {
                this.selectedLink = selectedLink;
            },
            reloadMenuTree() {
                axios.get('/ajax/menu-tree').then(({ data }) => {
                    this.setNewTree(data);
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {});
            },
            setNewTree(data) {
                this.should_build_tree_tabs = false;
                this.tree = data;
                this.$nextTick(() => {
                    this.should_build_tree_tabs = true;
                    console.log('Folder Tree:', this.tree);

                    this.$root.private_menu_tree = this.tree['private'];
                    this.$root.account_menu_tree = this.tree['account'];
                });
            }
        },
        created() {
            if (this.$root.user._is_folder_view) {
                this.tabs.push( {id: 'folder_view', name: 'Folder', init_scrl: 0} );
                this.currentTabId = 'folder_view';
                this.setNewTree(this.view_tree);
            } else {
                this.tabs = [
                    {id: 'public', name: 'Public', init_scrl: 0},
                    {id: 'private', name: 'Private', init_scrl: 0},
                    {id: 'favorite', name: 'Favorite', init_scrl: 0}
                ];
                if (this.$root.user.id) {
                    this.tabs.push( {id: 'account', name: 'Account', init_scrl: 0} );
                }
                this.reloadMenuTree();
            }
        },
        mounted() {
            eventBus.$on('event-reload-menu-tree', this.reloadMenuTree);
        },
        beforeDestroy() {
            eventBus.$off('event-reload-menu-tree', this.reloadMenuTree);
        }
    }
</script>

<style lang="scss" scoped="">
    #left-menu {
        .flo-left {
            float: left;
            margin-left: 10px;
        }

        .nav-tabs {
            li {
                width: 58px;
            }
        }
    }
</style>