<template>
    <div>
        <div class="popup-wrapper" @click.self="closeP()"></div>
        <div class="popup" :style="getPopupStyle()">
            <div class="flex flex--col">
                <div class="popup-header">
                    <div class="drag-bkg" draggable="true" @dragstart="dragPopSt()" @drag="dragPopup()"></div>
                    <div class="flex">
                        <div class="flex__elem-remain">Auto DDL Creation</div>
                        <div class="" style="position: relative">
                            <span class="glyphicon glyphicon-remove pull-right header-btn" @click="closeP()"></span>
                        </div>
                    </div>
                </div>
                <div class="popup-content">
                    <div class="popup-main" style="font-size: 14px; padding: 20px 10px;">
                        <div class="flex flex--col">
                            <div class="">
                                <div class="form-group flex flex--center-v">
                                    <label style="width: 40%;">Data Range:</label>
                                    <div style="height: 30px; position: relative;width: 60%;z-index: 30;">
                                        <tablda-select-simple
                                            :options="getRGr(tableMeta)"
                                            :table-row="settings"
                                            :hdr_field="'data_range'"
                                            :allowed_search="true"
                                            :init_no_open="true"
                                            @selected-item="(key) => settings['data_range'] = key"
                                        ></tablda-select-simple>
                                    </div>
                                </div>
                                <div class="form-group flex flex--center-v">
                                    <label style="width: 40%;">DDL Names: (distinctive values)</label>
                                    <div style="height: 30px; position: relative;width: 60%;z-index: 20;">
                                        <tablda-select-simple
                                            :options="getFields()"
                                            :table-row="settings"
                                            :hdr_field="'names_fld_id'"
                                            :allowed_search="true"
                                            :init_no_open="true"
                                            @selected-item="(key) => settings['names_fld_id'] = key"
                                        ></tablda-select-simple>
                                    </div>
                                </div>
                                <div class="form-group flex flex--center-v">
                                    <label style="width: 100%;">
                                        Ignore already defined DDLs:
                                        <input type="checkbox" v-model="settings['is_ignored']" style="margin-left: 50px;"></input>
                                    </label>
                                </div>
                                <div class="form-group flex flex--center-v">
                                    <label style="width: 40%;">DDL Options:</label>
                                    <div style="height: 30px; position: relative;width: 60%;z-index: 10;">
                                        <tablda-select-simple
                                            :options="getFields()"
                                            :table-row="settings"
                                            :hdr_field="'options_fld_id'"
                                            :allowed_search="true"
                                            :init_no_open="true"
                                            @selected-item="(key) => settings['options_fld_id'] = key"
                                        ></tablda-select-simple>
                                    </div>
                                </div>
                            </div>
                            <div class="popup-buttons">
                                <button class="btn btn-success btn-sm"
                                        :disabled="!settings['names_fld_id'] || !settings['options_fld_id']"
                                        @click="autoDDL()"
                                >Create</button>
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
    import DataRangeMixin from './../_Mixins/DataRangeMixin';

    import TabldaSelectSimple from "../CustomCell/Selects/TabldaSelectSimple";
    import {SpecialFuncs} from "../../classes/SpecialFuncs";

    export default {
        components: {
            TabldaSelectSimple,
        },
        name: "AutoDdlCreationPopup",
        mixins: [
            PopupAnimationMixin,
            DataRangeMixin,
        ],
        data: function () {
            return {
                settings: {
                    data_range: '0',
                    names_fld_id: null,
                    is_ignored: false,
                    options_fld_id: null,
                },
                //PopupAnimationMixin
                getPopupWidth: 460,
                getPopupHeight: 'auto',
                idx: 0,
            };
        },
        computed: {
        },
        props: {
            tableMeta: Object,
            requestParams: Object,
        },
        methods: {
            getFields() {
                return _.map(this.tableMeta._fields, (fld) => {
                    return {
                        show: fld.name,
                        val: fld.id,
                    };
                });
            },
            autoDDL() {
                if (this.settings['names_fld_id'] && this.settings['options_fld_id']) {
                    $.LoadingOverlay('show');
                    axios.post('/ajax/ddl/auto-creation', {
                        is_ignored: this.settings.is_ignored,
                        names_fld_id: this.settings.names_fld_id,
                        options_fld_id: this.settings.options_fld_id,
                        request_params: SpecialFuncs.dataRangeRequestParams(this.settings.data_range, this.tableMeta.id, this.requestParams)
                    }).then(({data}) => {
                        this.tableMeta._ddls = data;
                        // eventBus.$emit('reload-meta-table');
                        this.closeP(true);
                    }).catch(errors => {
                        Swal('Info', getErrors(errors));
                    }).finally(() => $.LoadingOverlay('hide'));
                } else {
                    Swal('Info','Names or Options are empty!');
                }
            },
            closeP(changed) {
                this.$emit('popup-close', changed);
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