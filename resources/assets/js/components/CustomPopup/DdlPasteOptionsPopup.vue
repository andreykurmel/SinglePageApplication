<template>
    <div>
        <div class="popup-wrapper" @click.self="closeP()"></div>
        <div class="popup" :style="getPopupStyle()">
            <div class="flex flex--col">
                <div class="popup-header">
                    <div class="drag-bkg" draggable="true" @dragstart="dragPopSt()" @drag="dragPopup()"></div>
                    <div class="flex">
                        <!-- Paste to import -->
                        <div class="flex__elem-remain">{{ top_title || 'Import Options' }}</div>
                        <div class="" style="position: relative">
                            <span class="glyphicon glyphicon-remove pull-right header-btn" @click="closeP()"></span>
                        </div>
                    </div>
                </div>
                <div class="popup-content">
                    <div class="popup-main">
                        <div class="flex flex--col">
                            <label>{{ main_text || 'Paste options below' }} (separated by comma or semi-colon or each placed in a row):</label>
                            <textarea v-model="parseImportOptions" class="form-control" rows="7"></textarea>
                            <div class="popup-buttons">
                                <button class="btn btn-success btn-sm" @click="parseOpt()">Parse &amp; Import</button>
                                <button class="btn btn-info btn-sm ml5" @click="closeP()">Cancel</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import PopupAnimationMixin from './../_Mixins/PopupAnimationMixin';

    export default {
        name: "DdlPasteOptionsPopup",
        mixins: [
            PopupAnimationMixin,
        ],
        data: function () {
            return {
                parseImportOptions: '',
                //PopupAnimationMixin
                getPopupWidth: 400,
                getPopupHeight: 'auto',
                idx: 0,
            };
        },
        props: {
            top_title: String,
            main_text: String,
        },
        methods: {
            closeP() {
                this.$emit('popup-close');
            },
            parseOpt() {
                this.$emit('parse-options', this.parseImportOptions);
            },
        },
        mounted() {
            this.runAnimation();
        },
    }
</script>

<style lang="scss" scoped>
    @import "CustomEditPopUp";

    .popup {
        font-size: initial;
        cursor: auto;

        .popup-content {
            label {
                margin: 0;
            }

            .popup-buttons {
                text-align: right;
            }
        }
    }
</style>