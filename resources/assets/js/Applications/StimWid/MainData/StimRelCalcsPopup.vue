<template>
    <div>
        <div class="popup-wrapper" @click.self="emit_event()"></div>
        <div class="popup" :style="getPopupStyle()">
            <div class="flex flex--col" :style="{height: '200px'}">
                <div class="popup-header">
                    <div class="drag-bkg" draggable="true" @dragstart="dragPopSt()" @drag="dragPopup()"></div>
                    <div class="flex">
                        <div class="flex__elem-remain">Calculate RL Brackets</div>
                        <div class="" style="position: relative">
                            <span class="glyphicon glyphicon-remove pull-right header-btn" @click="emit_event()"></span>
                        </div>
                    </div>
                </div>
                <div class="popup-content flex__elem-remain">
                    <div class="flex__elem__inner popup-main">
                        <div class="flex flex--col">
                            <div class="flex__elem-remain">
                                <label>Found Equipments: {{ load_data ? load_data.eqs.collection.length : '...' }}</label>
                                <div v-if="is_process && load_data" class="bar-wrapper">
                                    <span>Calculating...</span>
                                    <div class="progress-wrapper">
                                        <div class="progress-bar" :style="{width: progress+'%'}"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="popup-buttons">
                                <div class="action_buttons full-height">
                                    <button class="btn btn-success btn-sm" :disabled="is_process" @click="startCalculation()">Run</button>
                                    <button class="btn btn-info btn-sm" @click="emit_event()">Cancel</button>
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
    import {ThreeHelper} from '../../../classes/helpers/ThreeHelper';
    import {StimLinkParams} from '../../../classes/StimLinkParams';
    import {FoundModel} from '../../../classes/FoundModel';

    import PopupAnimationMixin from '../../../components/_Mixins/PopupAnimationMixin';

    export default {
        name: "StimRelCalcsPopup",
        mixins: [
            PopupAnimationMixin,
        ],
        components: {
        },
        data: function () {
            return {
                loaded_master_model: null,
                is_process: false,
                load_data: null,
                progress: 0,
                //PopupAnimationMixin
                getPopupWidth: 500,
                idx: 0,
            };
        },
        computed: {
        },
        props: {
            stimLink: StimLinkParams,
            foundModel: FoundModel,
        },
        methods: {
            loadData() {
                if (!this.is_process) {
                    this.is_process = true;
                    let master_model = this.foundModel.rows.get3D(0);
                    this.loaded_master_model = master_model;

                    ThreeHelper.loadDataServer(this.stimLink.app_table, master_model).then(({data}) => {
                        this.load_data = data;
                        this.is_process = false;
                    }).catch(errors => {
                        Swal('', getErrors(errors));
                    });
                }
            },
            startCalculation() {
                if (!this.is_process && this.load_data && this.loaded_master_model) {
                    this.is_process = true;

                    let helper = new ThreeHelper(this.load_data, this.loaded_master_model);
                    helper.startCalculationRL();

                    this.progress = 0;
                    let timeint = setInterval(() => {
                        this.progress = helper.getProgress();
                        if (this.progress >= 100) {
                            clearInterval(timeint);
                            this.is_process = false;
                            this.$emit('recalc-completed');
                        }
                    }, 500);
                }
            },
            emit_event() {
                this.$emit('popup-close');
            },
        },
        mounted() {
            this.runAnimation();
            this.loadData();
        },
    }
</script>

<style lang="scss" scoped>
    @import "../../../components/CustomPopup/CustomEditPopUp";

    .popup {
        font-size: initial;
        cursor: auto;
        height: auto;

        .popup-content {
            .popup-main {
                padding: 7px;

                label {
                    margin-top: 30px;
                    text-align: center;
                    width: 100%;
                }

                .popup-buttons {
                    text-align: right;
                }

                .progress-wrapper {
                    height: 25px;
                    border-radius: 5px;
                    border: 1px solid #CCC;
                    overflow: hidden;
                }
            }
        }
    }
</style>