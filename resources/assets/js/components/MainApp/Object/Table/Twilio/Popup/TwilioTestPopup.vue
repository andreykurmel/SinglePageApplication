<template>
    <div v-if="is_vis">
        <div class="popup-wrapper" @click.self="close()"></div>
        <div class="popup" :style="getPopupStyle()">
            <div class="flex flex--col">
                <div class="popup-header">
                    <div class="drag-bkg" draggable="true" @dragstart="dragPopSt()" @drag="dragPopup()" style="right: 25px;width: auto;"></div>
                    <div class="flex">
                        <div class="flex__elem-remain">SMS/Voice Test</div>
                        <div class="">
                            <span class="glyphicon glyphicon-remove" style="cursor: pointer" @click="close()"></span>
                        </div>
                    </div>
                </div>
                <div class="popup-content flex__elem-remain">
                    <div class="flex__elem__inner popup-main">
                        <twilio-single-elements
                            :row_id="row_id"
                            :ext_tab="ext_tab"
                            :is_vis="is_vis"
                        ></twilio-single-elements>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import {SpecialFuncs} from "../../../../../../classes/SpecialFuncs";

    import {eventBus} from '../../../../../../app';

    import PopupAnimationMixin from '../../../../../_Mixins/PopupAnimationMixin';

    import TwilioSingleElements from "../TwilioSingleElements";

    export default {
        name: "TwilioTestPopup",
        mixins: [
            PopupAnimationMixin,
        ],
        components: {
            TwilioSingleElements,
        },
        data: function () {
            return {
                ext_tab: '',
                is_vis: false,
                row_id: null,

                //PopupAnimationMixin
                getPopupHeight: '80%',
                getPopupWidth: 700,
                idx: 0,
            }
        },
        props: {
        },
        methods: {
            hidePop(e) {
                if (this.is_vis && e.keyCode === 27 && !this.$root.e__used) {
                    this.close();
                    this.$root.set_e__used(this);
                }
            },
            close() {
                this.is_vis = false;
            },
            showPop(row_id, ext_tab) {
                this.is_vis = true;
                this.row_id = row_id;
                this.ext_tab = ext_tab || '';
                this.$root.tablesZidxIncrease();
                this.zIdx = this.$root.tablesZidx + 700;

                this.$nextTick(() => {
                    this.runAnimation();
                });
            }
        },
        mounted() {
            window.twilio_test_pop_ready = 1;
            eventBus.$on('global-keydown', this.hideMenu);
            eventBus.$on('show-twilio-test-popup', this.showPop);
        },
        beforeDestroy() {
            eventBus.$off('global-keydown', this.hideMenu);
            eventBus.$off('show-twilio-test-popup', this.showPop);
        }
    }
</script>

<style scoped lang="scss">
@import "resources/assets/js/components/CustomPopup/CustomEditPopUp";

.popup-main {
    padding: 5px !important;
}
</style>