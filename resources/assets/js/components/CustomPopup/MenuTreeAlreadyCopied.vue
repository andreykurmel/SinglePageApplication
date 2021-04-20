<template>
    <div class="popup-wrapper" @click.self="hide()">
        <div class="popup" :style="getPopupStyle()">
            <div class="flex flex--col">
                <div class="popup-header">
                    <div class="drag-bkg" draggable="true" @dragstart="dragPopSt()" @drag="dragPopup()"></div>
                    <div class="flex">
                        <div class="flex__elem-remain">Info</div>
                        <div class="" style="position: relative">
                            <span class="glyphicon glyphicon-remove pull-right header-btn" @click="hide()"></span>
                        </div>
                    </div>
                </div>

                <div class="popup-content">
                    <div class="popup-main">
                        <div class="form-group">
                            <label>The {{ type }} is already copied to the other user</label>
                        </div>
                        <div class="">
                            <label>Choose an option to proceed</label>
                        </div>
                        <div class="">
                            <input type="radio" value="overwrite" v-model="proceed_status"/>
                            <label>Overwrite existing.</label>
                        </div>
                        <div class="">
                            <input type="radio" value="rename" v-model="proceed_status"/>
                            <label>Copy and rename.</label>
                        </div>
                        <div class="" v-show="proceed_status === 'rename'">
                            <label>Enter a new name:</label>
                            <input type="text" class="form-control" v-model="new_name" style="display: inline-block;width: auto;">
                        </div>
                        <div class="right-txt">
                            <button class="btn btn-sm btn-primary blue-gradient"
                                    @click="proceed()"
                                    :style="$root.themeButtonStyle"
                            >Proceed</button>
                            <button class="btn btn-sm btn-primary blue-gradient"
                                    @click="hide()"
                                    :style="$root.themeButtonStyle"
                            >Cancel</button>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</template>

<script>
    import PopupAnimationMixin from './../_Mixins/PopupAnimationMixin';

    import VerticalTable from "../CustomTable/VerticalTable";

    export default {
        name: "MenuTreeAlreadyCopied",
        components: {
            VerticalTable
        },
        mixins: [
            PopupAnimationMixin,
        ],
        data: function () {
            return {
                proceed_status: null,
                new_name: '',
                //PopupAnimationMixin
                idx: 0,
                getPopupWidth: 400,
            }
        },
        props:{
            type: String,
        },
        computed: {
        },
        methods: {
            hide() {
                this.$emit('hide');
            },
            proceed() {
                this.$emit('proceed', this.proceed_status, this.new_name);
            },
        },
        mounted() {
            this.noAnimation(2);
        }
    }
</script>

<style lang="scss" scoped>
    @import "CustomEditPopUp";

    .popup-wrapper {
        z-index: 2500;

        .popup {
            height: auto;

            .popup-content {

                .popup-main {
                    padding: 10px;
                    font-size: 16px;

                    label {
                        margin: 0px;
                    }
                }

                .right-txt {
                    text-align: right;
                    margin-top: 15px;
                }

                .opt-label {
                    margin: 0;
                    width: 160px;
                }

            }
        }
    }
</style>