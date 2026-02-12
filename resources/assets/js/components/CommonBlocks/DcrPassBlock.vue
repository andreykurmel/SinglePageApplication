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
                            <button class="btn btn-default btn-sm pull-right" @click="cancelPass()">Cancel</button>
                            <button class="btn btn-success btn-sm pull-right" @click="checkPass()">Submit</button>
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
        name: "DcrPassBlock",
        components: {
            LikePassInput
        },
        data: function () {
            return {
                pass: '',
            };
        },
        props: {
            dcr_id: Number,
            row_id: Number,
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
            cancelPass() {
                this.$emit('cancel-pass');
            },
            checkPass() {
                axios.post('/ajax/table-request/row-pass', {
                    table_dcr_id: this.dcr_id,
                    dcr_row_id: this.row_id,
                    pass: this.pass
                }).then(({ data }) => {
                    if (data.status) {
                        this.$emit('correct-pass');
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
    @import "../CustomPopup/CustomEditPopUp";
</style>