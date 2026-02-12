<template>
    <div class="popup-wrapper" v-if="pageMeta" @click.self="hide()" :style="{zIndex: zIdx}">
        <div class="popup" :style="getPopupStyle()">
            <div class="flex flex--col">
                <div class="popup-header">
                    <div class="drag-bkg" draggable="true" @dragstart="dragPopSt()" @drag="dragPopup()"></div>
                    <span>Dashboard Settings - {{ pageMeta.name }}</span>
                    <span class="glyphicon glyphicon-remove pull-right header-btn" @click="hide()"></span>
                </div>
                <div class="flex__elem-remain popup-content">
                    <div class="flex__elem__inner popup-main">
                        <div class="full-frame">
                            <div class="full-height permissions-tab">

                                <div class="permissions-panel full-height">
                                    <div class="permissions-menu-header m-5">
                                        <button class="btn btn-default h36" :style="textSysStyle" :class="{active : activeTab === 'list'}" @click="activeTab = 'list'">
                                            List
                                        </button>
                                        <button class="btn btn-default h36" :style="textSysStyle" :class="{active : activeTab === 'details'}" @click="activeTab = 'details'">
                                            Details
                                        </button>

                                        <div v-if="activeTab" class="flex flex--center-v flex--end" style="position: absolute; top: 0; right: 0; height: 32px;" :style="textSysStyleSmart">
                                            <a :href="dashboardLink" target="_blank">
                                                <button class="btn btn-primary btn-sm blue-gradient" :style="$root.themeButtonStyle">Share</button>
                                            </a>
                                        </div>
                                    </div>

                                    <div class="permissions-menu-body" style="border: 1px solid #CCC;">
                                        <div v-show="activeTab === 'list'" class="permissions-panel full-height no-padding">
                                            <custom-table
                                                :cell_component_name="'custom-cell-pages'"
                                                :global-meta="pageMeta"
                                                :table-meta="$root.settingsMeta['page_contents']"
                                                :all-rows="tbViews"
                                                :rows-count="tbViews.length"
                                                :cell-height="1"
                                                :max-cell-rows="0"
                                                :is-full-width="true"
                                                :user="$root.user"
                                                :behavior="'cell_page'"
                                                :adding-row="addingRow"
                                                :forbidden-columns="$root.systemFields"
                                                :use_theme="true"
                                                :style="{height: '30%'}"
                                                @added-row="addView"
                                                @updated-row="updateView"
                                                @delete-row="deleteView"
                                                @show-add-ddl-option="showViewSettingsPopup"
                                            ></custom-table>
                                        </div>
                                        <div v-show="activeTab === 'details'" class="permissions-panel full-height" style="padding: 10px;">
                                            <div class="flex" style="margin-bottom: 10px;">
                                                <div class="flex flex--center-v no-wrap">
                                                    <label>Cell Spacing:&nbsp;</label>
                                                    <input type="number" :style="textSysStyle" v-model="pageMeta.cell_spacing" @change="updatePage('cell_spacing')" class="form-control"/>
                                                    <label>&nbsp;px</label>
                                                </div>
                                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                <div class="flex flex--center-v no-wrap">
                                                    <label>Edge Spacing:&nbsp;</label>
                                                    <input type="number" :style="textSysStyle" v-model="pageMeta.edge_spacing" @change="updatePage('edge_spacing')" class="form-control"/>
                                                    <label>&nbsp;px</label>
                                                </div>
                                            </div>
                                            <div class="flex" style="margin-bottom: 10px;">
                                                <div class="flex flex--center-v no-wrap">
                                                    <label>Boundary Line Thickness:&nbsp;</label>
                                                    <input type="number" :style="textSysStyle" v-model="pageMeta.border_width" @change="updatePage('border_width')" class="form-control"/>
                                                    <label>&nbsp;px</label>
                                                </div>
                                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                <div class="flex flex--center-v no-wrap">
                                                    <label>Corner Radius:&nbsp;</label>
                                                    <input type="number" :style="textSysStyle" v-model="pageMeta.border_radius" @change="updatePage('border_radius')" class="form-control"/>
                                                    <label>&nbsp;px</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import {eventBus} from "../../../../app";

    import PopupAnimationMixin from "../../../_Mixins/PopupAnimationMixin";
    import CellStyleMixin from "../../../_Mixins/CellStyleMixin";

    import CustomTable from "../../../CustomTable/CustomTable";

    export default {
        name: "PageSettingsPopup",
        mixins: [
            PopupAnimationMixin,
            CellStyleMixin,
        ],
        components: {
            CustomTable
        },
        data: function () {
            return {
                activeTab: 'list',
                addingRow: {
                    active: true,
                    position: 'bottom'
                },
                //PopupAnimationMixin
                getPopupWidth: 700,
                idx: 0,
                //
            }
        },
        computed: {
            tbViews() {
                return _.filter(this.pageMeta._contents, {type: 'table_view'});
            },
            dashboardLink() {
                return this.$root.clear_url + '/dashboard/' + this.pageMeta.share_hash;
            },
        },
        props: {
            pageMeta: Object,
        },
        methods: {
            contentsChanged() {
                this.$emit('contents-changed');
            },
            //Connections Functions
            addView(tableRow) {
                this.$root.sm_msg_type = 1;

                tableRow.type = 'table_view';
                let fields = _.cloneDeep(tableRow);//copy object
                this.$root.deleteSystemFields(fields);

                axios.post('/ajax/pages/contents', {
                    page_id: this.pageMeta.id,
                    fields: fields,
                }).then(({ data }) => {
                    this.$root.assignObject(data, this.pageMeta);
                    this.contentsChanged();
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
            updateView(tableRow) {
                this.$root.sm_msg_type = 1;

                let row_id = tableRow.id;
                let fields = _.cloneDeep(tableRow);//copy object
                this.$root.deleteSystemFields(fields);

                axios.put('/ajax/pages/contents', {
                    page_id: this.pageMeta.id,
                    page_content_id: row_id,
                    fields: fields
                }).then(({ data }) => {
                    this.$root.assignObject(data, this.pageMeta);
                    this.contentsChanged();
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
            deleteView(tableRow) {
                this.$root.sm_msg_type = 1;
                let row_id = tableRow.id;
                axios.delete('/ajax/pages/contents', {
                    params: {
                        page_id: this.pageMeta.id,
                        page_content_id: row_id,
                    }
                }).then(({ data }) => {
                    this.$root.assignObject(data, this.pageMeta);
                    this.contentsChanged();
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
            //Page
            updatePage(col) {
                let flds = {};
                flds[col] = this.pageMeta[col];

                $.LoadingOverlay('show');
                axios.put('/ajax/pages', {
                    page_id: this.pageMeta.id,
                    fields: flds
                }).then(({ data }) => {
                    this.$emit('redraw-gridstack');
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
            showViewSettingsPopup(table_id, table_view_id) {
                this.$emit('show-view-settings-popup', table_id, table_view_id);
            },
            //
            hide() {
                this.$root.tablesZidxDecrease();
                this.$emit('hide-pop');
            },
            showPopup() {
                this.$root.tablesZidxIncrease();
                this.zIdx = this.$root.tablesZidx;
                this.runAnimation();
            },
        },
        mounted() {
            this.showPopup();
            eventBus.$on('global-keydown', this.hideMenu);
        },
        beforeDestroy() {
            eventBus.$off('global-keydown', this.hideMenu);
        }
    }
</script>

<style lang="scss" scoped>
    @import "./../../../CustomPopup/CustomEditPopUp";
    @import "./../../Object/Table/SettingsModule/TabSettingsPermissions";

    .popup-wrapper {
        .popup {
            position: relative;

            .popup-main {
                padding: 15px 15px 15px 20px;
            }
        }
        label {
            margin: 0;
        }
    }
</style>