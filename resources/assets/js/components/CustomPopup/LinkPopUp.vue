<template>
    <div class="popup" v-show="show_popup" :style="linkPopStyle" ref="popup_wrapper" :class="[activePop ? 'active-popup' : 'passive-popup']">
        <link-displayer
            :source-meta="sourceMeta"
            :idx="idx"
            :link="link"
            :shift-object="shiftObject"
            :meta-header="metaHeader"
            :meta-row="metaRow"
            :popup-key="popupKey"
            :forbidden-columns="forbiddenColumns"
            :available-columns="availableColumns"
            :view_authorizer="view_authorizer"
            :external-rows="externalRows"
            @link-show="showPopup"
            @link-close="closePopup"
            @popup-style="setStyle"
            @show-src-record="showSrcRecord"
        ></link-displayer>
    </div>
</template>

<script>
    import {eventBus} from '../../app';

    import LinkDisplayer from "../CommonBlocks/Link/LinkDisplayer";

    export default {
        name: "LinkPopUp",
        mixins: [
        ],
        components: {
            LinkDisplayer,
        },
        data: function () {
            return {
                linkPopStyle: {},
                show_popup: false,
                activePop: true,
                viewType: '',
            };
        },
        props: {
            sourceMeta: Object,
            idx: String|Number,//PopupAnimationMixin
            shiftObject: Object,//PopupAnimationMixin
            link: Object,
            metaHeader: Object,
            metaRow: Object,
            popupKey: String|Number,
            forbiddenColumns: {
                type: Array,
                default: function () {
                    return [];
                }
            },
            availableColumns: Array,
            externalRows: Array,
            view_authorizer: Object,
        },
        computed: {
        },
        methods: {
            setStyle(style) {
                if (window.innerWidth < 768) {
                    style.left = '0';
                    style.top = '10%';
                }
                this.linkPopStyle = style;
            },
            setActivePopup(e) {
                let container = $(this.$refs.popup_wrapper);
                this.activePop = container.has(e.target).length !== 0;
            },
            closePopup(popupKey, should_update, $id) {
                this.$emit('link-popup-close', popupKey, should_update, $id);
            },
            showPopup() {
                this.show_popup = true;
            },
            showSrcRecord(lnk, header, tableRow) {
                this.$emit('show-src-record', lnk, header, tableRow, 'link');
            },
        },
        mounted() {
            eventBus.$on('global-click', this.setActivePopup);
        },
        beforeDestroy() {
            eventBus.$off('global-click', this.setActivePopup);
        }
    }
</script>

<style lang="scss" scoped>
    @import "CustomEditPopUp";

    .active-popup {
        z-index: 1700;
    }
    .passive-popup {
        z-index: 1600;
    }
</style>
