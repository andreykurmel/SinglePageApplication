<template>
    <div class="popup-wrapper" @click.self="hide()" :style="{zIndex: zIdx}">
        <div class="popup" :style="getPopupStyle()">
            <div class="flex flex--col">
                <div class="popup-header">
                    <div class="drag-bkg" draggable="true" @dragstart="dragPopSt()" @drag="dragPopup()"></div>
                    <div class="flex">
                        <div class="flex__elem-remain">Feedback for "{{ selected_feedback.purpose }}"</div>
                        <div class="" style="position: relative">
                            <button class="btn btn-default blue-gradient add_btn"
                                    :style="$root.themeButtonStyle"
                                    @click="showAddFeedback"
                            >Add</button>
                            <span class="glyphicon glyphicon-remove pull-right header-btn" @click="hide()"></span>
                        </div>
                    </div>
                </div>
                <div class="flex__elem-remain popup-content">
                    <div class="flex__elem__inner popup-main">
                        <custom-table
                            v-if="all_results.length"
                            :cell_component_name="'custom-cell-stim-app-view'"
                            :global-meta="$root.settingsMeta['stim_app_view_feedback_results']"
                            :table-meta="$root.settingsMeta['stim_app_view_feedback_results']"
                            :all-rows="all_results"
                            :rows-count="all_results.length"
                            :cell-height="$root.cellHeight"
                            :max-cell-rows="$root.maxCellRows"
                            :is-full-width="true"
                            :user="$root.user"
                            :behavior="'stim_views'"
                            :adding-row="{ active: false }"
                            :available-columns="['user_signature','received_date','notes','received_attachments']"
                            :use_theme="true"
                            :no_height_limit="true"
                            :with_edit="false"
                            :widths_div="{index_col: 25, action_col: 40}"
                        ></custom-table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import PopupAnimationMixin from '../../../../components/_Mixins/PopupAnimationMixin';
    import CellStyleMixin from "../../../../components/_Mixins/CellStyleMixin";

    import CustomTable from "../../../../components/CustomTable/CustomTable";

    export default {
        name: "PresentResultsPopup",
        mixins: [
            PopupAnimationMixin,
            CellStyleMixin,
        ],
        components: {
            CustomTable,
        },
        data: function () {
            return {
                //PopupAnimationMixin
                getPopupWidth: 750,
                getPopupHeight: '400px',
                idx: 0,
            }
        },
        props: {
            selected_feedback: {
                type: Object,
                required: true,
            },
            all_results: {
                type: Array,
                required: true,
            },
        },
        methods: {
            showAddFeedback() {
                this.$emit('show-add-feedback');
            },
            //additionals
            hide() {
                this.$root.tablesZidxDecrease();
                this.$emit('popup-close');
            },
        },
        mounted() {
            this.$root.tablesZidxIncrease();
            this.zIdx = this.$root.tablesZidx;
            this.runAnimation();
        },
        beforeDestroy() {
        }
    }
</script>

<style lang="scss" scoped>
    @import "../../../../components/CustomPopup/CustomEditPopUp";

    .add_btn {
        font-size: 1.5rem;
        padding: 0 7px;
        position: absolute;
        right: 30px;
        z-index: 100;
    }
</style>