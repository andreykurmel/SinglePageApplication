<template>
    <div>
        <div class="popup-wrapper" @click.self="$emit('popup-close', false)"></div>
        <div class="popup" :style="getPopupStyle()">
            <div class="flex flex--col">
                <div class="popup-header">
                    <div class="drag-bkg" draggable="true" @dragstart="dragPopSt()" @drag="dragPopup()"></div>
                    <div class="flex">
                        <div class="flex__elem-remain">
                            Data Modifications (DM)
                        </div>
                        <div class="" style="position: relative">
                            <span class="glyphicon glyphicon-remove pull-right header-btn" @click="$emit('popup-close', false)"></span>
                        </div>
                    </div>
                </div>
                <div class="popup-content flex__elem-remain">
                    <div class="flex__elem__inner popup-main">
                        <div class="flex flex--col">
                            <div class="popup-menu">
                                <button class="btn btn-default"
                                        :class="{active: activeTab === 'find_replace'}"
                                        @click="activeTab = 'find_replace'"
                                >
                                    <span>Find & Replace</span>
                                </button>
                                <button class="btn btn-default"
                                        :class="{active: activeTab === 'remove_duplicate'}"
                                        @click="activeTab = 'remove_duplicate'"
                                >
                                    <span>Remove Duplicate</span>
                                </button>
                                <button class="btn btn-default"
                                        :class="{active: activeTab === 'batch_upload'}"
                                        @click="activeTab = 'batch_upload'"
                                >
                                    <span>Batch Upload</span>
                                </button>
                            </div>
                            <div class="flex__elem-remain popup-tab">

                                <div class="flex__elem__inner" v-if="activeTab === 'find_replace'">
                                    <replace-block
                                        :table_id="tableMeta.id"
                                        :table-meta="tableMeta"
                                    ></replace-block>
                                </div>

                                <div class="flex__elem__inner" v-if="activeTab === 'remove_duplicate'">
                                    <remove-duplicates-block
                                        :table-meta="tableMeta"
                                    ></remove-duplicates-block>
                                </div>

                                <div class="flex__elem__inner" v-if="activeTab === 'batch_upload'">
                                    <batch-uploading-block
                                        :table-meta="tableMeta"
                                    ></batch-uploading-block>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</template>

<script>
    import {SpecialFuncs} from '../../classes/SpecialFuncs';

    import PopupAnimationMixin from '../_Mixins/PopupAnimationMixin';

    import ReplaceBlock from "../CommonBlocks/ReplaceBlock";
    import RemoveDuplicatesBlock from "../CommonBlocks/RemoveDuplicatesBlock";
    import BatchUploadingBlock from "../CommonBlocks/BatchUploadingBlock";

    export default {
        name: "DataModifPopup",
        mixins: [
            PopupAnimationMixin,
        ],
        components: {
            BatchUploadingBlock,
            RemoveDuplicatesBlock,
            ReplaceBlock
        },
        data: function () {
            return {
                activeTab: 'find_replace',
                //PopupAnimationMixin
                getPopupWidth: 600,
                idx: 0,
            };
        },
        watch: {
        },
        props:{
            tableMeta: Object,
        },
        computed: {
        },
        methods: {
        },
        mounted() {
            this.runAnimation();
        },
        beforeDestroy() {
        }
    }
</script>

<style lang="scss" scoped>
    @import "./CustomEditPopUp";

    .popup-wrapper {
        z-index: 1300;
    }
    .popup {
        z-index: 1350;
    }
</style>