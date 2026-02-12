<template>
    <div id="left-menu" :style="leftMenuStyle">
        <div class="menu-header">
            <div v-if="nearTheLimit" class="red" style="font-size: 14px">Please note that our menu has limits of 500 folders and 2000 tables</div>

            <template v-for="tab in tabs">
                <info-sign-link v-if="settingsMeta.is_loaded"
                                v-show="!isFilters && currentTabId === tab.id"
                                :app_sett_key="'help_link_menutree_'+tab.id"
                                :hgt="24"
                                :txt="'for Menutree/'+tab.id"
                                class="flo-left"
                ></info-sign-link>
            </template>
            <info-sign-link v-if="settingsMeta.is_loaded"
                            v-show="isFilters"
                            :app_sett_key="'help_link_menutree_menu'"
                            :hgt="24"
                            :txt="'for Filters'"
                            class="flo-left"
            ></info-sign-link>

            <button v-if="!$root.sideIsNa('side_left_filter') && $root.tableMeta && !fltrVar()"
                    class="btn btn-default btn-he"
                    :class="{active : isFilters}"
                    :style="textSysStyle"
                    @click="isFilters = true"
            >FILTERS</button>
            <button v-if="!$root.sideIsNa('side_left_menu')"
                    class="btn btn-default btn-he"
                    :class="{active : !isFilters}"
                    :style="textSysStyle"
                    @click="isFilters = false"
            >MENU</button>
        </div>
        <div class="menu-body" ref="menu_body">
            <div v-if="$root.selectedAddon.code && $root.selectedAddon.sub_id && isFilters" class="red">
            Filtering for Data Range activated:
                {{ ($root.selectedAddon.name || $root.selectedAddon.code) + ' - ' + ($root.selectedAddon.sub_name || $root.selectedAddon.sub_id) }}
            </div>
            <div class="relative" :style="{height: 'calc(100% - ' + ($root.selectedAddon.code && $root.selectedAddon.sub_id ? 32 : 0) + 'px)'}">
                <filters-block-with-combos
                        v-if="$root.filters && isFilters && !fltrVar()"
                        :table-meta="$root.tableMeta"
                        :input_filters="getInputFilters()"
                        @changed-filter="changedFilter"
                        @applied-saved-filter="applyCombo"
                ></filters-block-with-combos>
                <template v-if="should_build_tree_tabs && (!embed || $root.user._is_folder_view)">
                    <div v-show="!isFilters" class="full-height position-relative">
                        <ul class="nav nav-tabs flex">
                            <li v-for="tab in tabs" :class="{active : currentTabId === tab.id}">
                                <a @click.prevent="changeLeftTab(tab.id)" :style="textSysStyle">{{tab.name}}</a>
                            </li>
                        </ul>
                        <template v-for="tab in tabs">
                            <left-menu-tree-accordion-item
                                v-if="settingsMeta.is_loaded && tab.id === 'public' && $root.allIsAccordion(tree[tab.id])"
                                v-show="currentTabId === tab.id"
                                :tab="tab.id"
                                :tab_tree="tree[tab.id]"
                                :object_id="object_id"
                                :object_type="object_type"
                                :selected-link="selectedLink"
                                :settings-meta="settingsMeta"
                                @update-object-id="updateObjectId"
                                @update-selected-link="updateSelectedLink"
                                @reload-menu-tree="reloadMenuTree"
                            ></left-menu-tree-accordion-item>
                            <left-menu-tree-item
                                v-else-if="settingsMeta.is_loaded"
                                v-show="currentTabId === tab.id"
                                :tab="tab.id"
                                :tab_tree="($root.user.id || $root.user._is_folder_view || tab.id === 'public' ? tree[tab.id] : [])"
                                :object_id="object_id"
                                :object_type="object_type"
                                :selected-link="selectedLink"
                                :settings-meta="settingsMeta"
                                @update-object-id="updateObjectId"
                                @update-selected-link="updateSelectedLink"
                                @reload-menu-tree="reloadMenuTree"
                            ></left-menu-tree-item>
                        </template>
                    </div>
                </template>
            </div>
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
    import FiltersBlockWithCombos from "../../CommonBlocks/FiltersBlockWithCombos";

    export default {
        name: 'LeftMenu',
        components: {
            FiltersBlockWithCombos,
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
                nearTheLimit: false,
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
            object_id: Number,
            object_type: String,
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
                let fsize = (this.themeSysSize || 14) / 1.5;
                return _.sumBy(this.tabs, (tab) => {
                    return (tab.name.length * fsize) + 8;
                }) + 20;
            },
        },
        watch: {
            "$root.tableMeta.id": { //watch for root to be sure that tableMeta is loaded
                handler(val) {
                    if (this.object_type === 'table' && this.$root.tableMeta && this.$root.tableMeta._cur_settings) {
                        this.left_menu_sizes.width = Number(this.$root.tableMeta._cur_settings.left_menu_width)
                            || Number(readLocalStorage('local_left_menu_width'))
                            || 250;
                        this.syncLogoPos();
                    }
                },
                immediate: true,
            },
        },
        methods:{
            getInputFilters() {
                return this.$root.selectedAddon.name && this.$root.selectedAddon.sub_name
                    ? (this.$root.addonFilters[this.$root.selectedAddon.code][this.$root.selectedAddon.sub_id] || this.$root.filters)
                    : this.$root.filters;
            },
            saveSizes() {
                this.$root.changeLeftMenuWi(this.left_menu_sizes.width, this.$root.tableMeta);
                this.syncLogoPos();
            },
            syncLogoPos() {
                let folderLogo = document.getElementById('top-logo');
                if (folderLogo) {
                    folderLogo.style.left = Math.max(this.left_menu_sizes.width, this.minWi) + 'px';
                }
                let calculating = document.getElementById('top-calculating');
                if (calculating) {
                    calculating.style.left = Math.max(this.left_menu_sizes.width, this.minWi) + 'px';
                }
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
            applyCombo(combo) {
                this.$root.filters = combo;
                this.changedFilter();
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
                    axios.get('/ajax/menu-tree', {
                        params: { table_id: this.object_id }
                    }).then(({ data }) => {
                        let menuString = JSON.stringify(data);
                        if (this.$root.user.folders_owned_count > 250 || this.$root.user.tables_owned_count > 1000) {
                            this.nearTheLimit = true;
                        }
                        setLocalStorage('menu_tree', menuString);
                        setLocalStorage('menu_tree_hash', this.$root.user.memutree_hash || '');
                        this.setNewTree(data);
                    }).catch(errors => {
                        Swal('Info', getErrors(errors));
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
            },
            fltrVar(neededPos) {
                return SpecialFuncs.filterOnTop(this.$root.tableMeta, this.$root.user, neededPos);
            },
        },
        created() {
            if (this.$root.user._is_folder_view) {
                let tabName = this.$root.user.view_all ? this.$root.user.view_all.name : 'Folder';
                this.tabs.push( {id: 'folder_view', name: tabName} );
                this.currentTabId = 'folder_view';
                this.setNewTree(this.view_tree);
            } else {
                this.tabs = [
                    {id: 'public', name: 'Public'},
                    {id: 'private', name: 'MySpace'},
                    {id: 'favorite', name: 'Favorite'}
                ];
                if (this.$root.user.id) {
                    this.tabs.push( {id: 'account', name: 'Account'} );
                }
                this.reloadMenuTree(true);
            }
        },
        mounted() {
            this.syncLogoPos();
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
            margin-left: 5px;
        }

        .btn-he {
            height: 36px;
        }
    }
</style>