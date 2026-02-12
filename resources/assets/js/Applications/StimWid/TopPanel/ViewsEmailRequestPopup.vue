<template>
    <div class="popup-wrapper" v-if="selected_feedback" v-show="show_this" @click.self="hide()" :style="{zIndex: zIdx}">
        <div class="popup" :style="getPopupStyle()">
            <div class="flex flex--col">
                <div class="popup-header">
                    <div class="drag-bkg" draggable="true" @dragstart="dragPopSt()" @drag="dragPopup()"></div>
                    <div class="flex">
                        <div class="flex__elem-remain">Message - Feedback Request</div>
                        <div class="" style="position: relative">
                            <span class="glyphicon glyphicon-remove pull-right header-btn" @click="hide()"></span>
                        </div>
                    </div>
                </div>
                <div class="popup-content">
                    <div class="popup-main">
                        <div class="full-frame">
                            <div class="form-group flex flex--center-v">
                                <label>Recipients (email addresses. Use comma, semi-colon or space to separate multiple addresses):</label>
                            </div>
                            <div class="form-group flex flex--center-v">
                                <label style="min-width: 60px">&nbsp;&nbsp;&nbsp;To:&nbsp;</label>
                                <input class="form-control" @change="updateFeedback()" v-model="selected_feedback.email_to" :style="textStyle"/>
                            </div>
                            <div class="form-group flex flex--center-v">
                                <label style="min-width: 60px">&nbsp;&nbsp;&nbsp;Cc:&nbsp;</label>
                                <input class="form-control" @change="updateFeedback()" v-model="selected_feedback.email_cc" :style="textStyle"/>
                            </div>
                            <div class="form-group flex flex--center-v">
                                <label style="min-width: 60px">&nbsp;&nbsp;&nbsp;Bcc:&nbsp;</label>
                                <input class="form-control" @change="updateFeedback()" v-model="selected_feedback.email_bcc" :style="textStyle"/>
                            </div>

                            <div class="form-group flex flex--center-v">
                                <label style="min-width: 60px">Subject:&nbsp;</label>
                                <input class="form-control" @change="updateFeedback()" v-model="selected_feedback.email_subject" :style="textStyle"/>
                            </div>

                            <div class="form-group">
                                <textarea class="form-control" rows="10" @change="updateFeedback()" v-model="selected_feedback.email_body" :style="textStyle"></textarea>
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

    import PopupAnimationMixin from './../../../components/_Mixins/PopupAnimationMixin';
    import CellStyleMixin from "../../../components/_Mixins/CellStyleMixin";

    export default {
        name: "ViewsEmailRequestPopup",
        mixins: [
            PopupAnimationMixin,
            CellStyleMixin,
        ],
        components: {
        },
        data: function () {
            return {
                selected_feedback: null,
                show_this: false,
                //PopupAnimationMixin
                getPopupWidth: 700,
                getPopupHeight: 'auto',
                idx: 0,
            }
        },
        props:{
        },
        methods: {
            //additionals
            hide() {
                this.selected_feedback = null;
                this.show_this = false;
                this.$root.tablesZidxDecrease();
                this.$emit('popup-close');
            },
            showViewEmailRequestHandler(selected_feedback) {
                this.selected_feedback = selected_feedback;
                this.show_this = true;
                this.$root.tablesZidxIncrease();
                this.zIdx = this.$root.tablesZidx;
                this.runAnimation();
            },

            //work with Feedbacks
            updateFeedback() {
                let fields = _.cloneDeep(this.selected_feedback);//copy object
                this.$root.sm_msg_type = 1;
                axios.put('/ajax/wid_view/feedback', {
                    view_id: this.selected_feedback.stim_view_id,
                    fields: fields,
                }).then(({ data }) => {
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
        },
        created() {
            eventBus.$on('stim-app-show-email-edit-popup', this.showViewEmailRequestHandler);
        },
        beforeDestroy() {
            eventBus.$off('stim-app-show-email-edit-popup', this.showViewEmailRequestHandler);
        }
    }
</script>

<style lang="scss" scoped>
    @import "./../../../components/CustomPopup/CustomEditPopUp";
</style>