<template>
    <div>
        <div class="popup-wrapper" @click.self="closeP()"></div>
        <div class="popup" :style="getPopupStyle()">
            <div class="flex flex--col">
                <div class="popup-header">
                    <div class="drag-bkg" draggable="true" @dragstart="dragPopSt()" @drag="dragPopup()"></div>
                    <div class="flex">
                        <div class="flex__elem-remain">Copy Permission</div>
                        <div class="" style="position: relative">
                            <span class="glyphicon glyphicon-remove pull-right header-btn" @click="closeP()"></span>
                        </div>
                    </div>
                </div>
                <div class="popup-content">
                    <div class="popup-main">
                        <div class="flex flex--col">
                            <div class="">
                                <div class="form-group">
                                    <label>Copy:</label>
                                    <select v-model="from_permis_id" class="form-control">
                                        <option></option>
                                        <option v-for="permis in tableMeta._table_permissions" :value="permis.id">{{ permis.name }}</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>To:</label>
                                    <select v-model="to_permis_id" class="form-control">
                                        <option></option>
                                        <option v-for="permis in tableMeta._table_permissions"
                                                v-if="permis.id != from_permis_id && permis.is_system == 0"
                                                :value="permis.id"
                                        >{{ permis.name }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="popup-buttons">
                                <button class="btn btn-success btn-sm"
                                        :disabled="!from_permis_id || !to_permis_id"
                                        @click="copyPermis()"
                                >Copy</button>
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
    import {eventBus} from './../../app';

    import PopupAnimationMixin from './../_Mixins/PopupAnimationMixin';

    import SelectWithFolderStructure from "../CustomCell/InCell/SelectWithFolderStructure";

    export default {
        components: {
            SelectWithFolderStructure
        },
        name: "CopyPermissionFromTablePopup",
        mixins: [
            PopupAnimationMixin,
        ],
        data: function () {
            return {
                from_permis_id: null,
                to_permis_id: null,
                //PopupAnimationMixin
                getPopupWidth: 400,
                getPopupHeight: '275px',
                idx: 0,
            };
        },
        computed: {
        },
        props:{
            tableMeta: Object,
        },
        methods: {
            selTB(val) {
                this.from_permis_id = val;
            },
            copyPermis() {
                if (this.from_permis_id || this.to_permis_id) {
                    $.LoadingOverlay('show');
                    axios.post('/ajax/table-permission/copy', {
                        from_permis_id: this.from_permis_id,
                        to_permis_id: this.to_permis_id,
                    }).then(({data}) => {
                        this.closeP(data);
                    }).catch(errors => {
                        Swal('', getErrors(errors));
                    }).finally(() => $.LoadingOverlay('hide'));
                } else {
                    Swal('No Permissions Selected!');
                }
            },
            closeP(permissions) {
                this.$emit('popup-close', permissions);
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

    .ml5 {
        margin-left: 5px;
    }
</style>