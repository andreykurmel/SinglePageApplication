<template>
    <div class="popup-wrapper" @click.self="hide()" :style="{zIndex: zIdx}">
        <div class="popup" :style="getPopupStyle()">
            <div class="flex flex--col">
                <div class="popup-header">
                    <div class="drag-bkg" draggable="true" @dragstart="dragPopSt()" @drag="dragPopup()"></div>
                    <div class="flex">
                        <div class="flex__elem-remain">
                            <slot name="title"></slot>
                        </div>
                        <div class="" style="position: relative">
                            <span class="glyphicon glyphicon-remove pull-right header-btn" @click="hide()"></span>
                        </div>
                    </div>
                </div>
                <div class="flex__elem-remain popup-content">
                    <div class="flex__elem__inner popup-main" :class="[nopadding ? 'no-padding' : '']">

                        <div class="popup-overflow">
                            <slot name="body"></slot>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import PopupAnimationMixin from './../_Mixins/PopupAnimationMixin';

    import {eventBus} from '../../app';

    export default {
        name: "SlotPopup",
        mixins: [
            PopupAnimationMixin,
        ],
        components: {
        },
        data: function () {
            return {
                //PopupAnimationMixin
                getPopupWidth: this.popup_width || 750,
                getPopupHeight: this.popup_height ? this.popup_height+'px' : null,
                idx: 0,
            }
        },
        props: {
            popup_width: Number,
            popup_height: Number,
            nopadding: Boolean,
        },
        methods: {
            hide() {
                this.$emit('popup-close');
            },
        },
        mounted() {
            this.runAnimation();
        },
        beforeDestroy() {
        }
    }
</script>

<style lang="scss" scoped>
    @import "CustomEditPopUp";
</style>