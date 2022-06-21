<template>
    <!--Add/Edit Table form-->
    <div class="modal-wrapper">
        <div class="modal full-height">
            <div class="modal-dialog modal--390">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">New Folder</h4>
                    </div>
                    <div class="modal-body">
                        <div>
                            <label class="l-inl-label" :style="{width: (themeTextFontSize*7.5)+'px', color: themeTextFontColor}">Name:</label>
                            <input class="form-control form-group l-inl-control"
                                   type="text"
                                   v-model="f_name"
                                   @change="fixName()"
                                   :style="textStyle">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-success" @click="addFolder()">OK</button>
                        <button type="button" class="btn btn-default" @click="$emit('close');">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import {SpecialFuncs} from "../../../classes/SpecialFuncs";

    import CellStyleMixin from "./../../_Mixins/CellStyleMixin.vue";

    export default {
        name: 'LeftMenuTreeFolderPopup',
        mixins: [
            CellStyleMixin,
        ],
        data() {
            return {
                f_name: '',
            }
        },
        props: {
            folderPopup: Object,
        },
        methods: {
            addFolder() {
                this.$emit('store-folder', this.f_name, this.folderPopup);
            },
            fixName() {
                this.f_name = SpecialFuncs.safeTableName(this.f_name);
            },
        },
        mounted() {
        },
        beforeDestroy() {
        }
    }
</script>

<style lang="scss" scoped>
    .modal--390 {
        width: 400px;
        margin: auto;
        top: 50%;
        transform: translateY(-50%);
    }
</style>