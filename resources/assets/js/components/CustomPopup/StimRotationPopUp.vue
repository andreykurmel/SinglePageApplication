<template>
    <div>
        <div class="popup-wrapper" @click.self="emit_event()"></div>
        <div class="popup" :style="getPopupStyle()">
            <div class="flex flex--col" :style="{height: '260px'}">
                <div class="popup-header">
                    <div class="drag-bkg" draggable="true" @dragstart="dragPopSt()" @drag="dragPopup()"></div>
                    <div class="flex">
                        <div class="flex__elem-remain">RTS</div>
                        <div class="" style="position: relative">
                            <span class="glyphicon glyphicon-remove pull-right header-btn" @click="emit_event()"></span>
                        </div>
                    </div>
                </div>
                <div class="popup-content flex__elem-remain">
                    <div class="flex__elem__inner popup-main">

                        <div class="flex flex--col">
                            <div class="popup-menu">
                                <button class="btn btn-default" :class="{active: rts_type === 'rotate'}" @click="rts_type = 'rotate'">
                                    Rotate
                                </button>
                                <button class="btn btn-default" :class="{active: rts_type === 'translate'}" @click="rts_type = 'translate'">
                                    Translate
                                </button>
                                <button class="btn btn-default" :class="{active: rts_type === 'scale'}" @click="rts_type = 'scale'">
                                    Scale
                                </button>
                            </div>

                            <div class="flex__elem-remain popup-tab flex flex--col flex--center-h" v-show="rts_type === 'rotate'">
                                <div class="form-group">
                                    <label>
                                        <span>Rotate w.r.t.</span>
                                        <select class="form-control f-inp" v-model="p_settings.rotate.axis">
                                            <option value="x">X</option>
                                            <option value="y">Y</option>
                                            <option value="z">Z</option>
                                        </select>
                                        <span>dir about</span>
                                        <select class="form-control f-inp f-inp--2" v-model="p_settings.rotate.about_id">
                                            <option :value="0">Origin</option>
                                            <option v-for="row in metaRows.all_rows" :value="row.id">{{ row[stimLink.name_field] }}</option>
                                        </select>
                                    </label>
                                </div>
                                <div style="margin-left: 68px">
                                    <label>
                                        <span>for</span>
                                        <input class="form-control f-inp" v-model="p_settings.rotate.deg"/>
                                        <span>degrees</span>
                                    </label>
                                </div>
                            </div>

                            <div class="flex__elem-remain popup-tab flex flex--col flex--center-h" v-show="rts_type === 'translate'">
                                <label>
                                    <span>Translate for, X:</span>
                                    <input class="form-control f-inp" v-model="p_settings.translate.dx"/>
                                    <span>Y:</span>
                                    <input class="form-control f-inp" v-model="p_settings.translate.dy"/>
                                    <span>Z:</span>
                                    <input class="form-control f-inp" v-model="p_settings.translate.dz"/>
                                </label>
                            </div>

                            <div class="flex__elem-remain popup-tab flex flex--col flex--center-h" v-show="rts_type === 'scale'">
                                <label>
                                    <span>Scale factor, X:</span>
                                    <input class="form-control f-inp" v-model="p_settings.scale.dx"/>
                                    <span>Y:</span>
                                    <input class="form-control f-inp" v-model="p_settings.scale.dy"/>
                                    <span>Z:</span>
                                    <input class="form-control f-inp" v-model="p_settings.scale.dz"/>
                                </label>
                            </div>

                            <div class="popup-buttons">
                                <div class="action_buttons full-height">
                                    <button class="btn btn-success btn-sm" :disabled="is_process" @click="rtsRows()">Go</button>
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
    import {StimLinkParams} from './../../classes/StimLinkParams';
    import {MetaTabldaRows} from './../../classes/MetaTabldaRows';
    
    import {eventBus} from './../../app';

    import PopupAnimationMixin from './../_Mixins/PopupAnimationMixin';

    import SingleTdField from "../CommonBlocks/SingleTdField";
    import HeaderResizer from "../CustomTable/Header/HeaderResizer";

    export default {
        name: "StimRotationPopUp",
        mixins: [
            PopupAnimationMixin,
        ],
        components: {
            HeaderResizer,
            SingleTdField,
        },
        data: function () {
            return {
                is_process: false,
                rts_type: 'rotate',
                p_settings: {
                    rotate: { axis: 'y', about_id: 0, deg: 0, },
                    translate: { dx: null, dy: null, dz: null, },
                    scale: { dx: 1, dy: 1, dz: 1, },
                },
                //PopupAnimationMixin
                getPopupWidth: 500,
                idx: 0,
            };
        },
        computed: {
        },
        props:{
            stimLink: StimLinkParams,
            metaRows: MetaTabldaRows,
        },
        methods: {
            rtsRows() {
                if (!this.is_process) {
                    this.is_process = true;
                    $.LoadingOverlay('show');

                    let rows_ids = [];
                    _.each(this.metaRows.all_rows, (row) => {
                        if (row._checked_row) {
                            rows_ids.push(row.id);
                        }
                    });

                    let request_params = this.metaRows.rowsRequest();
                    request_params.rows_per_page = 0;
                    if (rows_ids.length < this.metaRows.rows_per_page) {
                        request_params.row_id = rows_ids;
                    }
                    
                    axios.post('?method=do_rts', {
                        app_table: this.stimLink.app_table,
                        rts_type: this.rts_type,
                        rts_params: this.p_settings[this.rts_type],
                        request_params: request_params,
                    }).then(({data}) => {
                        (data.error ? Swal('', data.error) : this.$emit('rts-completed'));
                    }).catch(errors => {
                        Swal('', getErrors(errors));
                    }).finally(() => {
                        this.is_process = false;
                        $.LoadingOverlay('hide');
                    });
                }
            },
            emit_event() {
                this.$emit('popup-close');
            }
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
        height: auto;

        .popup-content {
            .popup-main {
                padding: 7px;

                label {
                    margin: 0;
                }

                .table {
                    td {
                        padding: 0;
                    }
                }

                .f-inp {
                    width: 60px;
                    display: inline-block;
                }
                .f-inp--2 {
                    width: 120px;
                }

                .popup-buttons {
                    text-align: right;
                }
            }
        }
    }
</style>