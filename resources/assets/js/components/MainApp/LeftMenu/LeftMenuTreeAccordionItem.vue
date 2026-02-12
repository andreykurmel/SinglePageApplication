<template>
    <div v-if="accordions" :class="[isSub ? 'full-height' : 'tab-pane']">
        <div :class="[isSub ? 'full-height pl10' : 'public-wrapper']" class="flex flex--col">
            <template v-for="folder in tab_tree">
                <div class="accordion_btn tab_btn flex flex--center-v"
                     @click="showAccTab(folder.init_name)"
                     :class="{tab_btn_active: accordions[folder.init_name]}"
                >
                    <span style="width: 15px">{{ accordions[folder.init_name] ? '-' : '+' }}</span>
                    <a v-if="canPanel"
                       :href="folder && folder['a_attr'] ? folder['a_attr']['href'] : '#'"
                       @click.prevent="changeOpenedObject(folder)"
                    >{{ folder.text }}</a>
                    <span v-else>{{ folder.text }}</span>
                </div>
                <left-menu-tree-accordion-item
                    v-if="$root.allIsAccordion(folder.children)"
                    :tab="tab"
                    :tab_tree="folder.children"
                    :object_id="object_id"
                    :object_type="object_type"
                    :selected-link="selectedLink"
                    :settings-meta="settingsMeta"
                    :style="{
                        maxHeight: accordions[folder.init_name] ? '100%' : '0',
                        overflow: 'hidden',
                        transition: 'all 0.5s linear',
                    }"
                    :external-search="externalSearch || appliedSearch"
                    @update-object-id="updateObjectId"
                    @update-selected-link="updateSelectedLink"
                    @reload-menu-tree="reloadMenuTree"
                ></left-menu-tree-accordion-item>
                <left-menu-tree-item
                    v-else
                    :tab="tab"
                    :tab_tree="folder.children"
                    :object_id="object_id"
                    :object_type="object_type"
                    :selected-link="selectedLink"
                    :settings-meta="settingsMeta"
                    :style="{
                        maxHeight: accordions[folder.init_name] ? '100%' : '0',
                        overflow: 'hidden',
                        transition: 'all 0.5s linear',
                    }"
                    :external-search="externalSearch || appliedSearch"
                    @update-object-id="updateObjectId"
                    @update-selected-link="updateSelectedLink"
                    @reload-menu-tree="reloadMenuTree"
                ></left-menu-tree-item>
            </template>
        </div>

        <div v-if="!isSub" class="search-in-tree flex">
            <input type="text"
                   class="form-control"
                   placeholder="Type to search for tables or links to tables"
                   @input="clearSearchIndex"
                   @keydown="searchKey"
                   v-model="searchVal">
            &nbsp;
            <button class="btn btn-default" @click="searchInTab()"><i class="fa fa-search"></i></button>
        </div>
    </div>
</template>

<script>
    import {JsTree} from "../../../classes/JsTree";

    import LeftMenuTreeItem from './LeftMenuTreeItem';
    import RequestFormViewElement from "../../RequestData/RequestFormViewElement.vue";

    export default {
        name: 'LeftMenuTreeAccordionItem',
        components: {
            RequestFormViewElement,
            LeftMenuTreeItem
        },
        mixins: [
        ],
        data() {
            return {
                accordions: null,
                searchVal: '',
                appliedSearch: '',
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
        computed: {
            isSub() {
                return this.externalSearch !== undefined;
            },
            canPanel() {
                return this.tab === 'public'
                    && this.$root.user.is_admin;
            },
        },
        methods: {
            clearSearchIndex(e) {
                this.search_index = 0;
            },
            searchKey(e) {
                if (e.keyCode == 13) {
                    this.searchInTab();
                }
            },
            searchInTab() {
                this.appliedSearch = this.searchVal;
            },
            showAccTab(name) {
                this.accordions[name] = Number( !this.accordions[name] );
            },
            updateObjectId(type, object_id) {
                this.$emit('update-object-id', type, object_id);
            },
            updateSelectedLink(selectedLink) {
                this.$emit('update-selected-link', selectedLink);
            },
            reloadMenuTree(force) {
                this.$emit('reload-menu-tree', force);
            },
            changeOpenedObject(folder) {
                if (!folder || !folder['a_attr']) {
                    return;
                }

                let nodomain = JsTree.get_no_domain(folder['a_attr']['href']);

                //change browser url
                try {
                    if (this.tab !== 'folder_view') {
                        let name = folder['init_name'];
                        window.history.pushState(name, name, nodomain);
                    } else {
                        window.location.hash = nodomain;
                    }
                    this.$emit('update-object-id', 'folder', folder['li_attr']['data-id']);
                } catch (e) {
                    window.location.href = nodomain;
                }
            },
        },
        mounted() {
            let obj = {};
            _.each(this.tab_tree, (folder) => {
                obj[folder.init_name] = 0;
            });
            this.accordions = obj;
        },
        beforeDestroy() {
        }
    }
</script>

<style lang="scss" scoped>
.tab-pane {
    position: relative;
    height: calc(100% - 35px);
}

.public-wrapper {
    height: calc(100% - 38px);
    overflow: auto;
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

.accordion_btn {
    background: #BBB;
    color: #000;
    padding: 5px 10px;
    font-weight: bold;
    cursor: pointer;
}
.tab_btn {
    margin: 5px 0 0 5px;
}
.tab_btn_active {
    background-color: #DDD !important;
}
</style>