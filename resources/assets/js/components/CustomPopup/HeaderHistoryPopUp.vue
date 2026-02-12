<template>
    <div class="popup-wrapper" @click.self.stop="hide()">
        <div class="popup" :style="getPopupStyle()">
            <div class="flex flex--col">
                <div class="popup-header">
                    <div class="drag-bkg" draggable="true" @dragstart="dragPopSt()" @drag="dragPopup()"></div>
                    <div class="flex">
                        <div class="flex__elem-remain">History of: {{ historyHeader.name }}</div>
                        <div class="" style="position: relative">
                            <span class="glyphicon glyphicon-remove pull-right header-btn" @click.stop="hide()"></span>
                        </div>
                    </div>
                </div>
                <div class="popup-content flex__elem-remain" v-if="drawHist">
                    <div class="popup-main full-height">
                        <history-elem
                            :user="$root.user"
                            :table-meta="tableMeta"
                            :history-header="historyHeader"
                            :table-row="tableRow"
                            :can-add="!!link.can_row_add"
                            :can-del="!!link.can_row_delete"
                        ></history-elem>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import {Endpoints} from "../../classes/Endpoints";
    import {SpecialFuncs} from "../../classes/SpecialFuncs";

    import {eventBus} from '../../app';

    import PopupAnimationMixin from './../_Mixins/PopupAnimationMixin';

    import HistoryElem from "../CommonBlocks/HistoryElem";

    export default {
        name: "HeaderHistoryPopUp",
        mixins: [
            PopupAnimationMixin,
        ],
        components: {
            HistoryElem
        },
        data: function () {
            return {
                drawHist: true,
                //PopupAnimationMixin
                getPopupWidth: 600,
            };
        },
        props: {
            idx: String|Number,
            tableMeta: Object,
            historyHeader: Object,
            tableRow: Object,
            link: Object,
            popupKey: String|Number,
            isVisible: Boolean,
        },
        watch: {
            isVisible: {
                handler(val) {
                    if (val) {
                        this.openPopup();
                    }
                },
                immediate: true,
            },
        },
        methods: {
            hide() {
                this.$emit('popup-close', this.popupKey);
            },
            openPopup() {
                this.runAnimation();
                this.drawHist = false;
                this.$nextTick(() => {
                    this.drawHist = true;
                });
            },
        },
        mounted() {
            eventBus.$on('global-keydown', this.hideMenu);
        },
        beforeDestroy() {
            eventBus.$off('global-keydown', this.hideMenu);
        }
    }
</script>

<style lang="scss" scoped>
    @import "./CustomEditPopUp";
</style>