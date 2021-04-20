<template>
    <div class="popup-wrapper" v-show="show_this" @click.self="hide()" :style="{zIndex: zIdx}">
        <div class="popup" :style="getPopupStyle()">
            <div class="flex flex--col">
                <div class="popup-header">
                    <div class="drag-bkg" draggable="true" @dragstart="dragPopSt()" @drag="dragPopup()"></div>
                    <div class="flex">
                        <div class="flex__elem-remain">Views</div>
                        <div class="" style="position: relative">
                            <span class="glyphicon glyphicon-remove pull-right header-btn" @click="hide()"></span>
                        </div>
                    </div>
                </div>
                <div class="flex__elem-remain popup-content" v-if="$root.user._app_users_views">
                    <div class="flex__elem__inner popup-main" :style="$root.themeMainBgStyle">
                        <div class="full-frame">
                            <div class="container-fluid full-height">
                                <div class="row full-height permissions-tab">
                                    <!--LEFT SIDE-->
                                    <div class="col-xs-7 full-height" style="padding-right: 0;">
                                        <div class="top-text">
                                            <span>List</span>
                                        </div>
                                        <div class="permissions-panel no-padding">
                                            <custom-table
                                                    :cell_component_name="'custom-cell-stim-app-view'"
                                                    :global-meta="$root.settingsMeta['stim_app_views']"
                                                    :table-meta="$root.settingsMeta['stim_app_views']"
                                                    :all-rows="avail_views"
                                                    :rows-count="avail_views.length"
                                                    :cell-height="$root.cellHeight"
                                                    :max-cell-rows="$root.maxCellRows"
                                                    :is-full-width="true"
                                                    :user="$root.user"
                                                    :behavior="'stim_views'"
                                                    :adding-row="addingRow"
                                                    :available-columns="horColAva"
                                                    :selected-row="selectedView"
                                                    :use_theme="true"
                                                    :fixed_ddl_pos="true"
                                                    :no_height_limit="true"
                                                    :is_visible="show_this"
                                                    @added-row="insertAppView"
                                                    @updated-row="updateAppView"
                                                    @delete-row="deleteAppView"
                                                    @row-index-clicked="rowIndexClickedView"
                                            ></custom-table>
                                        </div>
                                    </div>
                                    <!--RIGHT SIDE-->
                                    <div class="col-xs-5 full-height">
                                        <div class="top-text">
                                            <span>Additional Details of View: <span>{{ (selectedView > -1 && avail_views[selectedView] ? avail_views[selectedView].name : '') }}</span></span>
                                        </div>
                                        <div class="permissions-panel full-frame">
                                            <vertical-table
                                                    v-if="selectedView > -1 && avail_views[selectedView]"
                                                    :td="'custom-cell-stim-app-view'"
                                                    :global-meta="$root.settingsMeta['stim_app_views']"
                                                    :table-meta="$root.settingsMeta['stim_app_views']"
                                                    :settings-meta="$root.settingsMeta"
                                                    :table-row="avail_views[selectedView]"
                                                    :user="$root.user"
                                                    :fixed_ddl_pos="true"
                                                    :cell-height="$root.cellHeight"
                                                    :max-cell-rows="$root.maxCellRows"
                                                    :behavior="'stim_views'"
                                                    :available-columns="vertColAva"
                                                    :no_height_limit="true"
                                                    :widths="{name: '35%', col: '65%', history: 0, no_margins: true}"
                                                    @updated-cell="updateAppView"
                                            ></vertical-table>
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
    import {eventBus} from './../../../app';

    import {FoundModel} from '../../../classes/FoundModel';

    import PopupAnimationMixin from './../../../components/_Mixins/PopupAnimationMixin';

    import CustomTable from "./../../../components/CustomTable/CustomTable";
    import VerticalTable from "../../../components/CustomTable/VerticalTable";

    export default {
        name: "AppViewsPopup",
        mixins: [
            PopupAnimationMixin,
        ],
        components: {
            VerticalTable,
            CustomTable,
        },
        data: function () {
            return {
                show_this: false,
                //PopupAnimationMixin
                getPopupWidth: 1300,
                idx: 0,
                //
                selectedView: -1,
                addingRow: {
                    active: !!this.$root.user.id,
                    position: 'bottom'
                },
                horColAva: [
                    'name',
                    'source_string',
                ],
                vertColAva: [
                    'is_active',
                    'is_locked',
                    'lock_pass',
                    'side_top',
                    'side_left',
                    'side_right',
                ],
            }
        },
        computed: {
            avail_views() {
                return _.filter(this.$root.user._app_users_views || [], (el) => {
                    return el.v_tab == this.cur_tab && el.v_select == this.cur_sel;
                });
            },
        },
        props:{
            cur_tab: String,
            cur_sel: String,
            found_row: FoundModel,
        },
        methods: {
            //additionals
            hideMenu(e) {
                if (this.show_this && e.keyCode === 27 && this.$root.tablesZidx == this.zIdx && !this.$root.e__used) {
                    this.hide();
                    this.$root.set_e__used(this);
                }
            },
            rowIndexClickedView(index) {
                this.selectedView = -1;
                this.$nextTick(() => {
                    this.selectedView = index;
                });
            },

            //additionals
            hide() {
                this.show_this = false;
                this.$root.tablesZidx -= 10;
                this.$emit('popup-close');
            },
            showAppViewsPopupHandler() {
                this.show_this = true;
                this.$root.tablesZidx += 10;
                this.zIdx = this.$root.tablesZidx;
                this.runAnimation();
            },

            //work with views
            insertAppView(vData) {
                let fields = _.cloneDeep(vData);//copy object
                fields.lock_pass = vData.lock_pass ? 1 : 0;
                fields.v_tab = this.cur_tab;
                fields.v_select = this.cur_sel;
                fields.master_row_id = this.found_row._id;
                fields.source_string = location.search.replace(/\?/gi, '');
                fields.side_top = fields.side_left = fields.side_right = 'show';
                this.$root.deleteSystemFields(fields);

                this.$root.sm_msg_type = 1;
                axios.post('/ajax/wid_view', {
                    fields: fields,
                }).then(({ data }) => {
                    this.$root.user._app_users_views.push(data);
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                    this.viewRow = null;
                });
            },
            updateAppView(vData) {
                let fields = _.cloneDeep(vData);//copy object
                this.$root.deleteSystemFields(fields);

                this.$root.sm_msg_type = 1;
                axios.put('/ajax/wid_view', {
                    view_id: vData.id,
                    fields: fields,
                }).then(({ data }) => {
                    //
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
            deleteAppView(vData) {
                this.$root.sm_msg_type = 1;
                let idx = _.findIndex(this.$root.user._app_users_views, {id: vData.id});
                axios.delete('/ajax/wid_view', {
                    params: {
                        view_id: vData.id,
                    }
                }).then(({ data }) => {
                    this.$root.user._app_users_views.splice(idx, 1);
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
        },
        created() {
            eventBus.$on('global-keydown', this.hideMenu);
            eventBus.$on('stim-app-show-view-popup', this.showAppViewsPopupHandler);
        },
        beforeDestroy() {
            eventBus.$off('global-keydown', this.hideMenu);
            eventBus.$off('stim-app-show-view-popup', this.showAppViewsPopupHandler);
        }
    }
</script>

<style lang="scss" scoped>
    @import "./../../../components/CustomPopup/CustomEditPopUp";
    @import "./../../../components/MainApp/Object/Table/SettingsModule/TabSettingsPermissions";

    .popup-main {
        padding: 0;
    }
</style>