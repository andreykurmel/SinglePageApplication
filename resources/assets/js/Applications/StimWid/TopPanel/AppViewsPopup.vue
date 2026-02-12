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
                                    <div class="col-xs-6 full-height" style="padding-right: 0;">
                                        <div class="top-text">
                                            <span>List</span>
                                        </div>
                                        <div class="permissions-panel no-padding" style="height: calc(30% - 35px)">
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
                                                :no_height_limit="true"
                                                :is_visible="show_this"
                                                :widths_div="{index_col: 30, action_col: 60}"
                                                @added-row="insertAppView"
                                                @updated-row="updateAppView"
                                                @delete-row="deleteAppView"
                                                @row-index-clicked="rowIndexClickedView"
                                            ></custom-table>
                                        </div>
                                        <div class="top-text">
                                            <span>Additional Details of View: <span>{{ (selected_view ? selected_view.name : '') }}</span></span>
                                        </div>
                                        <div class="permissions-panel full-frame" style="height: calc(70% - 35px)">
                                            <vertical-table
                                                v-if="selected_view"
                                                :td="'custom-cell-stim-app-view'"
                                                :global-meta="$root.settingsMeta['stim_app_views']"
                                                :table-meta="$root.settingsMeta['stim_app_views']"
                                                :settings-meta="$root.settingsMeta"
                                                :table-row="selected_view"
                                                :user="$root.user"
                                                :cell-height="$root.cellHeight"
                                                :max-cell-rows="$root.maxCellRows"
                                                :behavior="'stim_views'"
                                                :available-columns="vertColAva"
                                                :no_height_limit="true"
                                                :widths="{name: '35%', col: '65%', history: 0, no_margins: true}"
                                                @updated-cell="updateAppView"
                                            ></vertical-table>
                                            <template v-if="selected_view && selected_view.side_left !== 'na'">
                                                <label class="tabs-header">Tabs</label>
                                                <view-avail-tabs
                                                    :app_view="selected_view"
                                                    :tabs_tree="selected_view.__tabs_tree || {}"
                                                    style="margin-left: 28%"
                                                    @update-view="updateAppView"
                                                ></view-avail-tabs>
                                            </template>
                                        </div>
                                    </div>

                                    <!--RIGHT SIDE-->
                                    <div class="col-xs-6 full-height">
                                        <div class="top-text">
                                            <span>Feedback Request (View: {{ (selected_view ? selected_view.name : '') }})</span>
                                        </div>
                                        <div class="permissions-panel no-padding" style="height: calc(50% - 35px)">
                                            <custom-table
                                                v-if="selected_view"
                                                :cell_component_name="'custom-cell-stim-app-view'"
                                                :global-meta="$root.settingsMeta['stim_app_view_feedbacks']"
                                                :table-meta="$root.settingsMeta['stim_app_view_feedbacks']"
                                                :all-rows="selected_view._feedbacks"
                                                :rows-count="selected_view._feedbacks.length"
                                                :cell-height="$root.cellHeight"
                                                :max-cell-rows="$root.maxCellRows"
                                                :is-full-width="true"
                                                :user="$root.user"
                                                :behavior="'stim_views'"
                                                :adding-row="addingRow"
                                                :available-columns="feedbackCols"
                                                :use_theme="true"
                                                :no_height_limit="true"
                                                :is_visible="show_this"
                                                :widths_div="{index_col: 30, action_col: 50}"
                                                @added-row="insertAppViewFeedback"
                                                @updated-row="updateAppViewFeedback"
                                                @delete-row="deleteAppViewFeedback"
                                                @row-index-clicked="rowIndexClickedFeedback"
                                            ></custom-table>
                                        </div>
                                        <div class="top-text">
                                            <span>Request#{{ selectedFeedback+1 }} : {{ (selected_feedback ? selected_feedback.purpose : '') }}</span>
                                        </div>
                                        <div class="permissions-panel no-padding" style="height: calc(50% - 35px)">
                                            <custom-table
                                                v-if="selected_feedback"
                                                :cell_component_name="'custom-cell-stim-app-view'"
                                                :global-meta="$root.settingsMeta['stim_app_view_feedback_results']"
                                                :table-meta="$root.settingsMeta['stim_app_view_feedback_results']"
                                                :all-rows="selected_feedback._results"
                                                :rows-count="selected_feedback._results.length"
                                                :cell-height="$root.cellHeight"
                                                :max-cell-rows="$root.maxCellRows"
                                                :is-full-width="true"
                                                :user="$root.user"
                                                :behavior="'stim_views'"
                                                :adding-row="{ active: false }"
                                                :available-columns="resultCols"
                                                :use_theme="true"
                                                :no_height_limit="true"
                                                :is_visible="show_this"
                                                :widths_div="{index_col: 30, action_col: 50}"
                                                @updated-row="updateAppViewFeedbackResult"
                                                @delete-row="deleteAppViewFeedbackResult"
                                            ></custom-table>
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
    import {eventBus} from '../../../app';

    import {FoundModel} from '../../../classes/FoundModel';

    import PopupAnimationMixin from './../../../components/_Mixins/PopupAnimationMixin';

    import CustomTable from "./../../../components/CustomTable/CustomTable";
    import ViewAvailTabs from "./ViewAvailTabs";

    export default {
        name: "AppViewsPopup",
        mixins: [
            PopupAnimationMixin,
        ],
        components: {
            ViewAvailTabs,
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
                selectedFeedback: -1,
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
                feedbackCols: [
                    'purpose', '_edit_email', '_send_email', 'send_date', 'request_pass'
                ],
                resultCols: [
                    'user_signature', 'received_date', 'notes', 'received_attachments'
                ],
            }
        },
        computed: {
            avail_views() {
                return _.filter(this.$root.user._app_users_views || [], (el) => {
                    return el.v_tab == this.cur_tab
                        && el.v_select == this.cur_sel
                        && this.found_row._id == el.master_row_id;
                });
            },
            selected_view() {
                return this.selectedView > -1 && this.avail_views[this.selectedView] ? this.avail_views[this.selectedView] : null;
            },
            selected_feedback() {
                return this.selectedFeedback > -1 && this.selected_view._feedbacks[this.selectedFeedback]
                    ? this.selected_view._feedbacks[this.selectedFeedback]
                    : null;
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
                this.selectedFeedback = -1;
                this.selectedView = -1;
                this.$nextTick(() => {
                    this.selectedView = index;
                });
            },
            rowIndexClickedFeedback(index) {
                this.selectedFeedback = -1;
                this.$nextTick(() => {
                    this.selectedFeedback = index;
                });
            },

            //additionals
            hide() {
                this.selectedFeedback = -1;
                this.selectedView = -1;
                this.show_this = false;
                this.$root.tablesZidxDecrease();
                this.$emit('popup-close');
            },
            showAppViewsPopupHandler() {
                this.show_this = true;
                this.$root.tablesZidxIncrease();
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

                this.$root.sm_msg_type = 1;
                axios.post('/ajax/wid_view', {
                    fields: fields,
                }).then(({ data }) => {
                    this.$root.user._app_users_views.push(data);
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                    this.viewRow = null;
                });
            },
            updateAppView(vData) {
                let fields = _.cloneDeep(vData);//copy object
                this.$root.sm_msg_type = 1;
                axios.put('/ajax/wid_view', {
                    view_id: vData.id,
                    fields: fields,
                }).then(({ data }) => {
                    //
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
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
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },

            //work with Feedbacks
            insertAppViewFeedback(vData) {
                let fields = _.cloneDeep(vData);//copy object
                this.$root.sm_msg_type = 1;
                axios.post('/ajax/wid_view/feedback', {
                    view_id: this.selected_view.id,
                    fields: fields,
                }).then(({ data }) => {
                    this.selected_view._feedbacks = data;
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                    this.viewRow = null;
                });
            },
            updateAppViewFeedback(vData) {
                if (vData._changed_field == '_send_email') {
                    this.sendFeedbackEmail(vData);
                    return;
                }

                let fields = _.cloneDeep(vData);//copy object
                this.$root.sm_msg_type = 1;
                axios.put('/ajax/wid_view/feedback', {
                    view_id: this.selected_view.id,
                    fields: fields,
                }).then(({ data }) => {
                    this.selected_view._feedbacks = data;
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
            sendFeedbackEmail(feedback) {
                if (!feedback.email_to) {
                    Swal('Info','Recipients are empty! Fill them by clicking "EDIT".');
                    return;
                }

                this.$root.sm_msg_type = 1;
                axios.post('/ajax/wid_view/feedback/email', {
                    feedback_id: feedback.id,
                }).then(({ data }) => {
                    this.selected_view._feedbacks = data;
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
            deleteAppViewFeedback(vData) {
                this.$root.sm_msg_type = 1;
                axios.delete('/ajax/wid_view/feedback', {
                    params: {
                        view_id: this.selected_view.id,
                        feedback_id: vData.id,
                    }
                }).then(({ data }) => {
                    this.selected_view._feedbacks = data;
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },

            //work with Feedback Results
            updateAppViewFeedbackResult(vData) {
                let fields = _.cloneDeep(vData);//copy object
                this.$root.sm_msg_type = 1;
                axios.put('/ajax/wid_view/feedback/result', {
                    feedback_id: this.selected_feedback.id,
                    fields: fields,
                }).then(({ data }) => {
                    this.selected_feedback._results = data;
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
            deleteAppViewFeedbackResult(vData) {
                this.$root.sm_msg_type = 1;
                axios.delete('/ajax/wid_view/feedback/result', {
                    params: {
                        feedback_id: this.selected_feedback.id,
                        result_id: vData.id,
                    }
                }).then(({ data }) => {
                    this.selected_feedback._results = data;
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
        },
        mounted() {
            setInterval(() => {
                if (this.selected_view) {
                    axios.post('/ajax/wid_view/state', {
                        view_id: this.selected_view.id,
                        state: this.selected_view.state,
                    }).then(({ data }) => {
                        if (data.new_view) {
                            this.selected_view._feedbacks = data.new_view._feedbacks;
                        }
                    }).catch(errors => {
                        Swal('Info', getErrors(errors));
                    });
                }
            }, 5000);

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

    .tabs-header {
        margin-top: 15px;
        font-size: 2rem;
        color: #222;
    }
</style>