<template>
    <div class="popup" :style="getPopupStyle" ref="popup_wrapper">
        <div class="flex flex--col">
            <div class="popup-header">Enter Password</div>
            <div class="popup-content flex__elem-remain">
                <div class="flex__elem__inner popup-main">
                    <div class="flex flex--col">
                        <div class="flex__elem-remain">
                            <div class="flex__elem__inner">
                                <like-pass-input :field="pass" @inputed="changePass"></like-pass-input>
                            </div>
                        </div>
                        <div class="popup-buttons">
                            <a class="btn btn-default btn-sm pull-right" href="/">Close</a>
                            <button class="btn btn-success btn-sm pull-right" @click="checkPass()">Confirm</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import LikePassInput from './../Buttons/LikePassInput';

    export default {
        name: "RequestPassPopUp",
        components: {
            LikePassInput
        },
        data: function () {
            return {
                pass: '',
            };
        },
        props:{
            table_request_id: Number,
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
            changePass(val) {
                this.pass = val;
            },
            checkPass() {
                axios.get('/ajax/table-data-request/check-pass', {
                    params: {
                        table_request_id: this.table_request_id,
                        pass: this.pass
                    }
                }).then(({ data }) => {
                    if (data.status) {
                        this.$emit('pass-popup-close', data.status ? this.pass : false);
                    } else {
                        Swal('Password incorrect!');
                    }
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
        },
    }
</script>

<style lang="scss" scoped>
    @import "../CustomPopup/CustomEditPopUp";
</style>