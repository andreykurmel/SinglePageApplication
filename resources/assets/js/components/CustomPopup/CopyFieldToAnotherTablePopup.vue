<template>
    <div>
        <div class="popup-wrapper" @click.self="$emit('popup-close')"></div>
        <div class="popup" :style="getPopupStyle()">
            <div class="flex flex--col">
                <div class="popup-header">
                    <div class="drag-bkg" draggable="true" @dragstart="dragPopSt()" @drag="dragPopup()"></div>
                    <div class="flex">
                        <div class="flex__elem-remain">
                            <span>Copy "{{ copyHeader.name }}" to selected table.</span>
                        </div>
                        <div class="" style="position: relative">
                            <span class="glyphicon glyphicon-remove pull-right header-btn" @click="$emit('popup-close')"></span>
                        </div>
                    </div>
                </div>
                <div class="flex__elem-remain popup-content">
                    <div class="flex__elem__inner popup-main">

                        <div class="flex flex--col">
                            <div class="bold">
                                <label>Select a table:</label>
                            </div>
                            <div class="copy-another-wrap">
                                <select-with-folder-structure
                                    :cur_val="selectedTableId"
                                    :available_tables="$root.settingsMeta.available_tables"
                                    :user="$root.user"
                                    @sel-changed="(val) => { selectedTableId = val; }"
                                    class="form-control"
                                ></select-with-folder-structure>
                            </div>
                            <div style="margin-top: 10px">
                                <button class="btn btn-success pull-right" @click="copyHeaderTo()">Send</button>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import PopupAnimationMixin from '../_Mixins/PopupAnimationMixin';

    import SelectWithFolderStructure from "../CustomCell/InCell/SelectWithFolderStructure.vue";

    export default {
        name: "CopyFieldToAnotherTablePopup",
        mixins: [
            PopupAnimationMixin,
        ],
        components: {
            SelectWithFolderStructure,
        },
        data: function () {
            return {
                selectedTableId: null,
                //PopupAnimationMixin
                getPopupHeight: '170px',
                getPopupWidth: 500,
                idx: 0,
            }
        },
        props: {
            copyHeader: Object,
        },
        methods: {
            copyHeaderTo() {
                if (! this.copyHeader.id || ! this.copyHeader.table_id) {
                    Swal('Info', '"Copy Header" is empty!');
                    return;
                }
                if (! this.selectedTableId) {
                    Swal('Info', '"Selected Table" is empty!');
                    return;
                }

                $.LoadingOverlay('show');
                axios.post('/ajax/settings/copy-to', {
                    from_field_id: this.copyHeader.id,
                    to_table_id: this.selectedTableId,
                }).then(({ data }) => {
                    Swal('Info', data.msg || 'The header settings were copied!');
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
        },
        mounted() {
            this.$root.tablesZidxIncrease();
            this.zIdx = this.$root.tablesZidx;
            this.runAnimation({anim_transform:'none'});
        }
    }
</script>

<style lang="scss" scoped>
    @import "CustomEditPopUp";
</style>

<style lang="scss">
    .copy-another-wrap {
        .select2-container {
            max-width: 100% !important;
        }
    }
</style>