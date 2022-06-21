<template>
    <div class="popup-wrapper" @click.self="hide()">
        <div class="popup" :style="extraStl">
            <div class="flex flex--col">
                <div class="popup-header">
                    <div class="flex">
                        <div class="flex__elem-remain" style="text-align: left">{{ title_html }}</div>
                        <div class="" style="padding-bottom: 4px;">
                            <span class="glyphicon glyphicon-remove pull-right header-btn" @click="hide()"></span>
                        </div>
                    </div>
                </div>

                <div class="popup-content popup-main">
                    <div>{{ content_html }}</div>
                    <div>
                        <button v-if="cancel_btn" class="btn btn-info btn-sm pull-right" @click="$emit('cancel-click')">{{ cancel_btn }}</button>
                        <button v-if="add_btn" class="btn btn-success btn-sm pull-right" @click="$emit('add-click')">{{ add_btn }}</button>
                    </div>
                </div>

            </div>
        </div>
    </div>
</template>

<script>
    import PopupAnimationMixin from './../_Mixins/PopupAnimationMixin';

    export default {
        name: "InfoPopup",
        components: {
        },
        mixins: [
            PopupAnimationMixin,
        ],
        data: function () {
            return {
                //PopupAnimationMixin
                getPopupWidth: this.pop_width || 500,
                idx: 0,
            }
        },
        props:{
            pop_width: Number,
            title_html: String,
            content_html: String,
            extra_style: Object,
            add_btn: String,
            cancel_btn: String,
        },
        computed: {
            extraStl() {
                let style = _.clone(this.getPopupStyle());
                if (this.extra_style) {
                    return {
                        ...style,
                        ...this.extra_style,
                    };
                } else {
                    return style;
                }
            },
        },
        methods: {
            hide() {
                this.$emit('hide');
            },
        },
        mounted() {
            this.runAnimation();
        }
    }
</script>

<style lang="scss" scoped>
    @import "CustomEditPopUp";

    .popup-wrapper {
        z-index: 2500;

        .popup {
            height: auto;

            .popup-main {
                font-size: 2rem;
                line-height: 2rem;
                font-weight: bold;

                button {
                    margin: 15px 0 0 5px;
                }
            }
        }
    }
</style>