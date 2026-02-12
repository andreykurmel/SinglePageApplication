<template>
    <div v-if="is_vis">
        <div class="popup-wrapper" @click.self="close()"></div>
        <div class="popup" :style="getPopupStyle()">
            <div class="flex flex--col">
                <div class="popup-header">
                    <div class="drag-bkg" draggable="true" @dragstart="dragPopSt()" @drag="dragPopup()" style="right: 25px;width: auto;"></div>
                    <div class="flex">
                        <div class="flex__elem-remain" v-html="getPopUpHeader()"></div>
                        <div class="">
                            <span class="glyphicon glyphicon-remove" style="cursor: pointer" @click="close()"></span>
                        </div>
                    </div>
                </div>
                <div class="popup-content flex__elem-remain">
                    <div class="flex__elem__inner popup-main">
                        <twilio-popup-sms
                            v-if="behaviorType === 'Message'"
                            :table-meta="tableMeta"
                            :table-header="tableHeader"
                            :table-row="tableRow"
                            :cell-item="cellItem"
                            @setWidth="(width) => { getPopupWidth = width }"
                            @close-pop="close()"
                        ></twilio-popup-sms>
                        <twilio-popup-call
                            v-if="behaviorType === 'Phone Call'"
                            :table-meta="tableMeta"
                            :table-header="tableHeader"
                            :table-row="tableRow"
                            :cell-item="cellItem"
                            @setWidth="(width) => { getPopupWidth = width }"
                            @close-pop="close()"
                        ></twilio-popup-call>
                        <twilio-popup-email
                            v-if="behaviorType === 'Email'"
                            :table-meta="tableMeta"
                            :table-header="tableHeader"
                            :table-row="tableRow"
                            :cell-item="cellItem"
                            @setWidth="(width) => { getPopupWidth = width }"
                            @close-pop="close()"
                        ></twilio-popup-email>
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

    import TwilioPopupSms from "./TwilioPopupSms";
    import TwilioPopupCall from "./TwilioPopupCall";
    import TwilioPopupEmail from "./TwilioPopupEmail";

    export default {
        name: "TwilioSendPopup",
        mixins: [
            PopupAnimationMixin,
        ],
        components: {
            TwilioPopupEmail,
            TwilioPopupCall,
            TwilioPopupSms,
        },
        data: function () {
            return {
                is_vis: false,
                behaviorType: '',
                tableHeader: null,
                tableRow: null,
                cellItem: null,

                //PopupAnimationMixin
                getPopupHeight: '80%',
                getPopupWidth: 600,
                idx: 0,
            }
        },
        props: {
            tableMeta: Object,
        },
        methods: {
            getPopUpHeader() {
                if (this.behaviorType === 'Email') {
                    let headers = this.tableMeta._fields;
                    let row = this.tableRow;
                    let res = [];
                    _.each(headers, (hdr) => {
                        if (hdr.popup_header || hdr.popup_header_val) {
                            let row_value = row
                                ? SpecialFuncs.showhtml(hdr, row, row[hdr.field], this.tableMeta)
                                : '';
                            let ar = hdr.popup_header ? [this.$root.uniqName(hdr.name)] : [];
                            if (hdr.popup_header_val) { ar.push(row_value) }
                            res.push( ar.join(': ') );
                        }
                    });
                    res = res.length ? res.join(' | ') : '';
                    return res;// + "* Email field: " + this.tableHeader.name;
                } else {
                    return this.behaviorType;
                }
            },
            hidePop(e) {
                if (this.is_vis && e.keyCode === 27 && !this.$root.e__used) {
                    this.close();
                    this.$root.set_e__used(this);
                }
            },
            close() {
                this.is_vis = false;
            },
            showPop(type, item, header, row) {
                this.is_vis = true;
                this.behaviorType = type;
                this.tableHeader = header;
                this.tableRow = row;
                this.cellItem = item;
                this.$root.tablesZidxIncrease();
                this.zIdx = this.$root.tablesZidx + 700;
                this.getPopupWidth = 600;

                switch (this.behaviorType) {
                    case 'Email': this.getPopupHeight = '600px'; break;
                    case 'Phone Call': this.getPopupHeight = '375px'; break;
                    case 'Message': this.getPopupHeight = '450px'; break;
                }

                this.$nextTick(() => {
                    this.runAnimation();
                });
            }
        },
        mounted() {
            eventBus.$on('global-keydown', this.hideMenu);
            eventBus.$on('show-twilio-send-popup', this.showPop);
        },
        beforeDestroy() {
            eventBus.$off('global-keydown', this.hideMenu);
            eventBus.$off('show-twilio-send-popup', this.showPop);
        }
    }
</script>

<style scoped lang="scss">
@import "resources/assets/js/components/CustomPopup/CustomEditPopUp";

.popup-main {
    padding: 5px !important;
}
</style>