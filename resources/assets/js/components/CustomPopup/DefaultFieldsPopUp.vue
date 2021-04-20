<template>
    <div class="popup-wrapper" @click.self="$emit('popup-close')">
        <div class="popup" :style="getPopupStyle()">
            <div class="flex flex--col">
                <div class="popup-header">
                    <div class="drag-bkg" draggable="true" @dragstart="dragPopSt()" @drag="dragPopup()"></div>
                    <div class="flex">
                        <div class="flex__elem-remain">Default Values</div>
                        <div class="" style="position: relative">
                            <span class="glyphicon glyphicon-remove pull-right header-btn" @click="$emit('popup-close', false)"></span>
                        </div>
                    </div>
                </div>
                <div class="flex__elem-remain popup-content">
                    <div class="flex__elem__inner popup-main">
                        <div class="popup-overflow">
                            <default-fields-table
                                    v-if="tableMeta"
                                    :table-permission-id="tablePermissionId"
                                    :user-group-id="UserGroupId"
                                    :table-meta="tableMeta"
                                    :default-fields="defaultFields"
                                    :user="user"
                                    :with_edit="tableMeta._is_owner"
                                    :cell-height="$root.cellHeight"
                                    :max-cell-rows="$root.maxCellRows"
                            ></default-fields-table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import DefaultFieldsTable from './DefaultFieldsTable';

    import PopupAnimationMixin from './../_Mixins/PopupAnimationMixin';

    export default {
        name: "DefaultFieldsPopUp",
        mixins: [
            PopupAnimationMixin,
        ],
        components: {
            DefaultFieldsTable
        },
        data: function () {
            return {
                //PopupAnimationMixin
                getPopupWidth: 768,
                idx: 0,
            };
        },
        props:{
            tablePermissionId: Number,
            UserGroupId: Number,
            tableMeta: Object,
            defaultFields: Array,
            user: Object,
            cellHeight: Number,
            maxCellRows: Number,
        },
        methods: {
        },
        mounted() {
            this.runAnimation();
        }
    }
</script>

<style lang="scss" scoped>
    @import "CustomEditPopUp";

    .popup {
        width: 768px;
    }
</style>