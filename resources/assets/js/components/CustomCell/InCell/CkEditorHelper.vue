<template>
    <div
        ref="editor_button"
        class="editor-helper"
        @click.self="hide()"
        @contextmenu.stop=""
    >
        <div class="popup" :style="getPopupStyle()">
            <div class="flex flex--col">
                <div class="popup-header">
                    <div class="drag-bkg" draggable="true" @dragstart="dragPopSt()" @drag="dragPopup()"></div>
                    <div class="flex">
                        <div class="flex__elem-remain txt--left">Field Editing</div>
                        <div class="" style="padding-bottom: 4px;">
                            <span class="glyphicon glyphicon-remove pull-right header-btn" @click="hide()"></span>
                        </div>
                    </div>
                </div>

                <div class="popup-main popup-content full-height">
                    <tab-ckeditor
                        :table-meta="tableMeta"
                        :target-row="tableRow"
                        :field-name="tableHeader.field"
                        :type="'helper'"
                    ></tab-ckeditor>
                </div>

            </div>
        </div>
    </div>
</template>

<script>
    import {SpecialFuncs} from '../../../classes/SpecialFuncs';

    import {eventBus} from '../../../app';

    import PopupAnimationMixin from './../../_Mixins/PopupAnimationMixin';

    import TabCkeditor from "../../CommonBlocks/TabCkeditor.vue";

    export default {
        name: "CkEditorHelper",
        components: {
            TabCkeditor,
        },
        mixins: [
            PopupAnimationMixin,
        ],
        data: function () {
            return {
                //PopupAnimationMixin
                getPopupHeight: '80%',
                getPopupWidth: 850,
                idx: 0,
            }
        },
        props: {
            tableMeta: Object,
            tableRow: Object,
            tableHeader: Object,
        },
        computed: {
        },
        methods: {
            //Show/Hide Helper Popup
            hideEditorHelper(e) {
                let container = $(this.$refs.editor_button);
                if (container.has(e.target).length === 0) {
                    this.hide();
                }
            },
            hide() {
                this.$emit('close-ck-editor', this.tableRow[this.tableHeader.field]);
            },
        },
        mounted() {
            this.runAnimation();

            eventBus.$on('global-click', this.hideEditorHelper);
        },
        beforeDestroy() {
            eventBus.$off('global-click', this.hideEditorHelper);
        }
    }
</script>

<style lang="scss" scoped>
    @import "./../../CustomPopup/CustomEditPopUp";

    .editor-helper {
        position: fixed;
        width: 100%;
        min-width: 300px;
        top: 0;
        right: 0;
        border: 1px solid #CCC;
        padding: 5px;
        background-color: #FFF;
        color: #444;
        border-radius: 5px;
        z-index: 1500;
    }

    .popup-wrapper {
        z-index: 2500;
        background: rgba(0, 0, 0, 0.45);

        .popup {
            height: auto;

            .popup-main {
            }
        }
    }
</style>