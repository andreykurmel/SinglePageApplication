<template>
    <div>
        <div class="popup-wrapper" @click.self="emit_event()"></div>
        <div class="popup" :style="getPopupStyle()">
            <div class="flex flex--col" :style="{height: '260px'}">
                <div class="popup-header">
                    <div class="drag-bkg" draggable="true" @dragstart="dragPopSt()" @drag="dragPopup()"></div>
                    <div class="flex">
                        <div class="flex__elem-remain">Copy {{ sel_tab + (sel_sub_tab ? '/'+sel_sub_tab : '')}} from</div>
                        <div class="" style="position: relative">
                            <span class="glyphicon glyphicon-remove pull-right header-btn" @click="emit_event()"></span>
                        </div>
                    </div>
                </div>
                <div class="popup-content flex__elem-remain">
                    <div class="flex__elem__inner popup-main">
                        <div class="flex flex--col">
                            <div class="flex__elem-remain">
                                <label>Search and select source:</label>
                                <wid-search-model v-if="localModel"
                                                  :found_model="localModel"
                                                  :stim_link_params="stimLink"
                                                  :is_visible="true"
                                                  :as_input_style="wid_style"
                                                  style="height: 36px;"
                                                  @set-found-model=""></wid-search-model>
                            </div>
                            <div class="popup-buttons">
                                <div class="action_buttons full-height">
                                    <button class="btn btn-success btn-sm" :disabled="is_process || (localModel && !localModel._id)" @click="copyRows()">Go</button>
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
    import {FoundModel} from '../../../classes/FoundModel';
    import {StimLinkParams} from '../../../classes/StimLinkParams';
    
    import {eventBus} from '../../../app';

    import PopupAnimationMixin from '../../../components/_Mixins/PopupAnimationMixin';

    import WidSearchModel from "../TopPanel/WidSearchModel";

    export default {
        name: "StimCopyFromModelPopup",
        mixins: [
            PopupAnimationMixin,
        ],
        components: {
            WidSearchModel,
        },
        data: function () {
            return {
                is_process: false,
                localModel: null,
                //PopupAnimationMixin
                getPopupWidth: 450,
                idx: 0,
            };
        },
        computed: {
            wid_style() {
                return {
                    form_control: { width: '100%' },
                    selected_span: {
                        paddingLeft: '0',
                        overflow: 'hidden',
                    },
                    search_popup_wrapper: {
                        top: 'initial',
                        left: (this.leftPos)+'px',
                        position: 'fixed',
                        transform: 'translate(12px, 36px)',
                    },
                };
            },
        },
        props:{
            cur_stim_link: StimLinkParams,
            stimLink: StimLinkParams,
            foundModel: FoundModel,
            sel_tab: String,
            sel_sub_tab: String,
        },
        methods: {
            copyRows() {
                if (!this.is_process && this.localModel._id) {
                    this.is_process = true;
                    $.LoadingOverlay('show');
                    axios.post('?method=copy_child', {
                        target_id: this.foundModel._id,
                        master_id: this.localModel._id,
                        master_table: this.stimLink.app_table,
                        child_table: this.cur_stim_link.app_table,
                    }).then(({data}) => {
                        (data.error ? Swal('', data.error) : this.$emit('copy-model-completed'));
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
            },
        },
        mounted() {
            this.localModel = _.cloneDeep(this.foundModel);
            this.localModel.setSelectedRow(null);
            this.runAnimation();
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
                }

                .popup-buttons {
                    text-align: right;
                }
            }
        }
    }
</style>