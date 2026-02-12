<template>
    <div class="popup" :style="getPopupStyle" ref="popup_wrapper">
        <div class="flex flex--col">
            <div class="popup-header">Enter Password</div>
            <div class="popup-content flex__elem-remain">
                <div class="flex__elem__inner popup-main">
                    <div class="flex flex--col">
                        <div class="flex__elem-remain">
                            <div class="flex__elem__inner">
                                <like-pass-input :field="pass" @inputed="updatePass"></like-pass-input>
                            </div>
                        </div>
                        <div class="popup-buttons">
                            <button class="btn btn-default btn-sm pull-right" @click="hide()">Close</button>
                            <button class="btn btn-success btn-sm pull-right" @click="checkPass()">Confirm</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import LikePassInput from "../../../../components/Buttons/LikePassInput";

    export default {
        name: "FeedbackPassBlock",
        components: {
            LikePassInput,
        },
        data: function () {
            return {
                pass: '',
            };
        },
        props: {
            selected_feedback: {
                type: Object,
                required: true,
            },
        },
        computed: {
            getPopupStyle() {
                return {
                    top: '50%',
                    left: '50%',
                    width: '300px',
                    height: '170px',
                    transform: 'translate(-50%, -50%)',
                    zIndex: 'auto'
                };
            },
        },
        methods: {
            hide() {
                this.$emit('popup-close');
            },
            updatePass(val) {
                this.pass = val;
            },
            checkPass() {
                axios.post('/ajax/wid_view/feedback/check-pass', {
                    feedback_id: this.selected_feedback.id,
                    pass: this.pass
                }).then(({ data }) => {
                    if (data.status) {
                        this.$emit('password-correct');
                        this.hide();
                    } else {
                        Swal('Info','Password incorrect!');
                    }
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
        },
    }
</script>

<style lang="scss" scoped>
    @import "../../../../components/CustomPopup/CustomEditPopUp";
</style>