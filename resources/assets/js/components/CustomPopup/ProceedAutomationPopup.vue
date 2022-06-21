<template>
    <div class="popup-wrapper" @click.self="terminateAnr()">
        <div class="popup" :style="getPopupStyle()">
            <div class="flex flex--col">
                <div class="popup-header">
                    <div class="drag-bkg" draggable="true" @dragstart="dragPopSt()" @drag="dragPopup()"></div>
                    <div class="flex">
                        <div class="flex__elem-remain">
                            ANA - Adding New Records (ANR) Confirmation
                        </div>
                        <div class="" style="position: relative">
                            <span class="glyphicon glyphicon-remove pull-right header-btn" @click="terminateAnr()"></span>
                        </div>
                    </div>
                </div>

                <div class="popup-content full-height">
                    <div class="flex flex--col">
                        <!--<div>-->
                            <!--<label>Turn On/Off ANR items in the list to bypass the execution:</label>-->
                        <!--</div>-->
                        <div class="flex__elem-remain">
                            <div class="flex__elem__inner popup-overflow">
                                <alert-automation-anr
                                        :is_temp="true"
                                        :can_edit="canEditAlert"
                                        :table-meta="tableMeta"
                                        :alert_sett="tableAlert"
                                        style="padding: 5px;"
                                ></alert-automation-anr>
                            </div>
                        </div>
                        <div class="">
                            <div class="right-txt">
                                <button v-if="user_id" class="btn btn-success" style="float: left" :disabled="base_updating" @click="updateChanges(base_updating)">
                                    {{ base_updating ? 'Updating...' : 'Update Base' }}
                                </button>
                                <button class="btn btn-success" @click="postAnr()">Proceed</button>
                                <button class="btn btn-warning" @click="terminateAnr()">Terminate</button>
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

    import CustomTable from "../CustomTable/CustomTable";
    import AlertAutomationAnr from "../MainApp/Object/Table/AlertAddon/AlertAutomationAnr";

    export default {
        name: "ProceedAutomationPopup",
        components: {
            AlertAutomationAnr,
            CustomTable,
        },
        mixins: [
            PopupAnimationMixin,
        ],
        data: function () {
            return {
                base_updating: false,
                addingRow: {
                    active: true,
                    position: 'bottom'
                },
                //PopupAnimationMixin
                idx: 0,
                getPopupWidth: '800',
            }
        },
        props:{
            user_id: Number,
            tableMeta: Object,
            tableAlert: Object,
        },
        computed: {
            canEditAlert() {
                return Boolean(this.tableAlert && (this.$root.user.id === this.tableAlert.user_id || this.tableAlert._can_edit));
            },
        },
        methods: {
            hide() {
                this.$emit('hide-popup');
            },
            updateChanges() {
                if (this.base_updating) {
                    return;
                }

                this.base_updating = true;
                axios.post('/ajax/table/alert/anr_tmp_to_main', {
                    alert_id: this.tableAlert.id,
                }).then(({ data }) => {
                    let alert = _.find(this.tableMeta._alerts, {id: Number(this.tableAlert.id)});
                    if (alert && data) {
                        alert._anr_tables = data._anr_tables;
                    }
                    this.base_updating = false;
                }).catch(errors => {
                    Swal('', getErrors(errors));
                });
            },
            postAnr() {
                axios.post('/ajax/table/alert/anr_proceed', {
                    alert_id: this.tableAlert.id,
                }).then(({ data }) => {
                    this.hide();
                }).catch(errors => {
                    Swal('', getErrors(errors));
                });
            },
            terminateAnr() {
                this.hide();
            },
        },
        mounted() {
            $.LoadingOverlay('hide');
            this.runAnimation();
        }
    }
</script>

<style lang="scss" scoped>
    @import "CustomEditPopUp";

    .popup-wrapper {
        z-index: 2500;

        .popup {
            .popup-content {
                padding: 10px;

                .right-txt {
                    padding-top: 10px;
                    text-align: right;
                }
            }
        }
    }
</style>