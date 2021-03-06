<template>
    <div id="left-menu" :style="leftMenuStyle">
        <div class="menu-header">
            <info-sign-link :app_sett_key="'help_link_menutree'" :hgt="24" class="flo-left"></info-sign-link>
            <button v-if="!$root.sideIsNa('side_left_filter')"
                    class="btn btn-default btn-he"
                    :class="{active : isFilters}"
                    :style="textSysStyle"
                    @click="isFilters = true"
            >FILTER</button>
            <button v-if="!$root.sideIsNa('side_left_menu')"
                    class="btn btn-default btn-he"
                    :class="{active : !isFilters}"
                    :style="textSysStyle"
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
                            <a @click.prevent="changeLeftTab(tab.id)" :style="textSysStyle">{{tab.name}}</a>
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

        <header-resizer :table-header="left_menu_sizes" @resize-finished="saveSizes"></header-resizer>

    </div>
</template>

<script>
    import {eventBus} from '../../../app';

    import {SpecialFuncs} from '../../../classes/SpecialFuncs';

    import CellStyleMixin from "../../_Mixins/CellStyleMixin";

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
        mixins: [
            CellStyleMixin,
        ],
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
                left_menu_sizes:{
                    width: Number(readLocalStorage('local_left_menu_width')) || 250,
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
                    minWidth: this.minWi+'px',
                    ...this.textSysStyle,
                };
            },
            minWi() {
                let fsize = (this.themeSysSize || 14) / 2;
                return _.sumBy(this.tabs, (tab) => {
                    return (tab.name.length * fsize) + 8;
                }) + 20;
            },
        },
        watch: {
            "$root.tableMeta.id": {
                handler(val) {
                    if (this.$root.tableMeta && this.$root.tableMeta._cur_settings) {
                        this.left_menu_sizes.width = Number(this.$root.tableMeta._cur_settings.left_menu_width)
                            || Number(readLocalStorage('local_left_menu_width'))
                            || 250;
                    }
                },
                immediate: true,
            },
        },
        methods:{
            saveSizes() {
                this.$root.changeLeftMenuWi(this.left_menu_sizes.width, this.$root.tableMeta);
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
            reloadMenuTree(force) {
                let menu = SpecialFuncs.tryJson( readLocalStorage('menu_tree') );
                let hash = readLocalStorage('menu_tree_hash');
                if (!force && menu && hash === this.$root.user.memutree_hash) {
                    this.setNewTree(menu);
                } else {
                    axios.get('/ajax/menu-tree').then(({ data }) => {
                        setLocalStorage('menu_tree', JSON.stringify(data));
                        setLocalStorage('menu_tree_hash', this.$root.user.memutree_hash || '');
                        this.setNewTree(data);
                    }).catch(errors => {
                        Swal('', getErrors(errors));
                    }).finally(() => {});
                }
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
                this.tabs.push( {id: 'folder_view', name: 'Folder'} );
                this.currentTabId = 'folder_view';
                this.setNewTree(this.view_tree);
            } else {
                this.tabs = [
                    {id: 'public', name: 'Public'},
                    {id: 'private', name: 'Private'},
                    {id: 'favorite', name: 'Favorite'}
                ];
                if (this.$root.user.id) {
                    this.tabs.push( {id: 'account', name: 'Account'} );
                }
                this.reloadMenuTree(true);
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

<style lang="scss" scoped>
    #left-menu {
        .flo-left {
            float: left;
            margin-left: 10px;
        }

        .btn-he {
            height: 36px;
        }
    }
</style>