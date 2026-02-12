<template>
    <div class="pages-wrapper full-width" :style="$root.themeMainBgStyle">
        <nav class="navbar navbar-default" role="navigation">
            <div class="flex" :style="$root.themeRibbonStyle">
                <ul class="nav flex flex--center flex--automargin flex--wrap pull-left">
                    <div v-if="!$root.sideIsNa('side_left_menu') || !$root.sideIsNa('side_left_filter')">
                        <a @click.prevent="showTree()">
                            <span class="glyphicon" :class="[ $root.isLeftMenu ? 'glyphicon-triangle-left': 'glyphicon-triangle-right']"></span>
                        </a>
                    </div>
                </ul>
                <div class="flex__elem-remain"></div>
                <div class="nav flex flex--center flex--automargin pull-right">
                    <div v-if="pageMeta && pageMeta._is_owner">
                        <button class="btn btn-primary btn-sm blue-gradient"
                                :style="$root.themeButtonStyle"
                                @click="settings_pop = true"
                        >
                            <i class="fas fa-cog" style="font-size: 20px;"></i>
                        </button>
                    </div>

                    <div v-if="!$root.sideIsNa('side_right')">
                        <a @click.prevent="showNote()"><span class="glyphicon" :class="[ $root.isRightMenu ? 'glyphicon-triangle-right': 'glyphicon-triangle-left']"></span></a>
                    </div>
                </div>
            </div>
        </nav>

        <div v-if="pageMeta && candraw" class="full-frame pages-remain" :style="edgeStyle()">
            <div class="grid-stack">
                <template v-for="content in pageMeta._contents">
                    <bi-grid-wrap :gs_hash="content.row_hash"
                                  :gs_wi="content.grid_position.wi"
                                  :gs_he="content.grid_position.he"
                                  :gs_x="content.grid_position.x"
                                  :gs_y="content.grid_position.y"
                    >
                        <iframe v-if="content._mrv"
                                :src="getIframeUrl(content)"
                                width="100%"
                                height="100%"
                                class="iframe-block"
                                :style="{
                                    borderWidth: pageMeta.border_width + 'px',
                                    borderRadius: pageMeta.border_radius + 'px',
                                    cursor: pageMeta._is_owner ? 'move' : 'auto'
                                }"
                        ></iframe>
                    </bi-grid-wrap>
                </template>
            </div>
        </div>

        <page-settings-popup
            v-if="pageMeta && settings_pop"
            :page-meta="pageMeta"
            @hide-pop="settings_pop = false"
            @contents-changed="createGridstack"
            @redraw-gridstack="redrawGridstack"
            @show-view-settings-popup="openViewSettings"
        ></page-settings-popup>

        <!--Popup for assign views-->
        <table-views-popup
            v-if="viewTbMeta"
            :table-meta="viewTbMeta"
            :is-limited="'MRV'"
            :init_show="true"
            @popup-close="viewTbMeta = null"
        ></table-views-popup>

        <!--Popup for assign permissions-->
        <permissions-settings-popup
            v-if="viewTbMeta"
            :table-meta="viewTbMeta"
            :user="$root.user"
        ></permissions-settings-popup>

        <!--Popup for adding column links-->
        <grouping-settings-popup
            v-if="viewTbMeta"
            :table-meta="viewTbMeta"
            :user="$root.user"
        ></grouping-settings-popup>

        <!--Popup for showing ref conditions -->
        <ref-conditions-popup
            v-if="viewTbMeta"
            :table-meta="viewTbMeta"
            :user="$root.user"
            :table_id="viewTbMeta ? viewTbMeta.id : null"
        ></ref-conditions-popup>
    </div>
</template>

<script>
    import BiGridWrap from "../Table/ChartAddon/BiGridWrap";
    import PageSettingsPopup from "./PageSettingsPopup";
    import PermissionsSettingsPopup from "../../../CustomPopup/PermissionsSettingsPopup.vue";
    import GroupingSettingsPopup from "../../../CustomPopup/GroupingSettingsPopup.vue";
    import TableViewsPopup from "../../../CustomPopup/TableViewsPopup.vue";
    import RefConditionsPopup from "../../../CustomPopup/RefConditionsPopup.vue";

    export default {
        name: "Pages",
        components: {
            RefConditionsPopup,
            TableViewsPopup,
            GroupingSettingsPopup,
            PermissionsSettingsPopup,
            PageSettingsPopup,
            BiGridWrap
        },
        data: function () {
            return {
                viewTbMeta: null,
                pageMeta: null,
                settings_pop: false,
                grid: null,
                candraw: true,
            }
        },
        props: {
            page_id: Number|null,
        },
        watch: {
            page_id: function(val) {
                this.activeTab = 'basics';
                if (val) {
                    this.getPageMeta();
                }
            }
        },
        methods: {
            edgeStyle() {
                let p = this.pageMeta
                    ? Math.max(this.pageMeta.edge_spacing - this.pageMeta.cell_spacing/2, this.pageMeta.cell_spacing/2)
                    : 0;
                return {
                    padding: p + 'px',
                };
            },
            getPageMeta() {
                $.LoadingOverlay('show');
                axios.get('/ajax/pages', {
                    params: {
                        page_id: this.page_id,
                    }
                }).then(({ data }) => {
                    this.pageMeta = data.pages;
                    if (this.$root.user.id) {
                        $('head title').html(this.$root.app_name+': '+this.pageMeta.name);
                    }
                    this.createGridstack();

                    console.log('PageSettings', this.pageMeta, 'size about: ', JSON.stringify(this.pageMeta).length);
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => $.LoadingOverlay('hide'));
            },
            createGridstack() {
                this.grid ? this.grid.destroy() : null;
                this.candraw = false;
                this.$nextTick(() => {
                    this.candraw = true;
                    this.$nextTick(() => {
                        this.grid = window.GridStack.init({
                            disableDrag: !this.pageMeta._is_owner,
                            disableResize: !this.pageMeta._is_owner,
                            cellHeight: 50,
                            margin: this.pageMeta ? this.pageMeta.cell_spacing/2 : 5,
                        });
                        this.grid.on('change', this.changedPosition);
                    });
                });
            },
            redrawGridstack() {
                this.grid.margin(this.pageMeta ? this.pageMeta.cell_spacing/2 : 5);
            },
            //drag
            changedPosition() {
                let items = $('.grid-stack .grid-stack-item');
                _.each(items, (it) => {
                    let attributes = it.attributes || ($(it)[0] || []).attributes;
                    let hsh = attributes['hash'] ? attributes['hash'].value : 'tmp';
                    _.each(this.pageMeta._contents, (cnt) => {
                        if (cnt.row_hash === hsh) {
                            cnt.grid_position.x = attributes['gs-x'] ? attributes['gs-x'].value : 0;
                            cnt.grid_position.y = attributes['gs-y'] ? attributes['gs-y'].value : 0;
                            cnt.grid_position.wi = attributes['gs-w'] ? attributes['gs-w'].value : 0;
                            cnt.grid_position.he = attributes['gs-h'] ? attributes['gs-h'].value : 0;
                        }
                    });
                });

                if (this.pageMeta._is_owner) {
                    axios.post('/ajax/pages/content-positions', {
                        page_id: this.pageMeta.id,
                        contents: this.pageMeta._contents,
                    });
                }
            },
            getIframeUrl(pageContent) {
                let params = {
                    captcha_style: 'hidden',
                };
                if (pageContent.type === 'table_view' && pageContent.view_part) {
                    params.only_viewpart = pageContent.view_part;
                }
                return this.$root.clear_url
                    + '/mrv/'
                    + pageContent._mrv.hash
                    + '?'
                    + (new URLSearchParams(params)).toString();
            },
            showNote() {
                this.$root.toggleRightMenu();
            },
            showTree() {
                this.$root.toggleLeftMenu();
            },
            openViewSettings(table_id, table_view_id) {
                $.LoadingOverlay('show');
                axios.post('/ajax/table-data/get-headers', {
                    table_id: table_id,
                    user_id: this.$root.user.id,
                }).then(({ data }) => {
                    this.viewTbMeta = data;
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
        },
        mounted() {
            if (this.page_id) {
                this.getPageMeta();
            }
        }
    }
</script>

<style scoped lang="scss">
    .navbar {
        min-height: 40px !important;
        border-radius: 0 !important;
    }
    .blue-gradient {
        height: 30px;
        padding: 0 7px;
        line-height: 1px;
    }
    .pages-remain {
        height: calc(100% - 40px);
        overflow: auto;
    }
    .pages-wrapper {
        position: relative;
        overflow: hidden;
    }
    .iframe-block {
        border: 7px solid #e0e0e0;
        border-radius: 10px;
    }
</style>