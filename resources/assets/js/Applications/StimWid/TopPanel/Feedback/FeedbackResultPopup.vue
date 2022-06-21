<template>
    <div class="popup-wrapper" v-if="selected_feedback" @click.self="hide()" :style="{zIndex: zIdx}">
        <div class="popup" :style="getPopupStyle()">
            <div class="flex flex--col">
                <div class="popup-header">
                    <div class="drag-bkg" draggable="true" @dragstart="dragPopSt()" @drag="dragPopup()"></div>
                    <div class="flex">
                        <div class="flex__elem-remain">Please fill out the form and submit</div>
                        <div class="" style="position: relative">
                            <span class="glyphicon glyphicon-remove pull-right header-btn" @click="hide()"></span>
                        </div>
                    </div>
                </div>
                <div class="popup-content">
                    <div class="popup-main">
                        <div class="flex flex--col">

                            <div class="form-group flex">
                                <label style="min-width: 80px">Notes<span class="required-wildcart">*</span>:&nbsp;</label>
                                <textarea class="form-control" rows="5" v-model="insert_object.notes"></textarea>
                            </div>

                            <div class="form-group">
                                <label>Attachments:</label>
                                <show-attachments-block
                                    :table-header="tableHeader"
                                    :table-meta="tableMeta"
                                    :table-row="tableRow"
                                    :extra-styles="styles"
                                    :can-edit="true"
                                    :force-files="true"
                                ></show-attachments-block>
                            </div>

                            <div class="form-group">
                                <label>Upload:</label>
                                <file-uploader-block
                                    v-if="tableMeta && tbHeader"
                                    :format="tbHeader.f_format"
                                    :header-index="0"
                                    :table_id="tableMeta.id"
                                    :field_id="tbHeader.id"
                                    :row_id="insert_object._tmp_id"
                                    @uploaded-file="insAttach"
                                ></file-uploader-block>
                            </div>

                            <div class="form-group flex flex--center-v">
                                <label style="min-width: 80px">
                                    <span>Signature<span class="required-wildcart">*</span>:&nbsp;</span>
                                </label>
                                <input class="form-control" v-model="insert_object.user_signature"/>
                            </div>

                            <div class="popup-buttons" style="text-align: right">
                                <button class="btn btn-success" @click="insertFeedbackResult()">Submit</button>
                                <button class="btn btn-info ml5" @click="hide()">Cancel</button>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import {eventBus} from '../../../../app';

    import PopupAnimationMixin from '../../../../components/_Mixins/PopupAnimationMixin';

    import SelectBlock from "../../../../components/CommonBlocks/SelectBlock";
    import FileUploaderBlock from "../../../../components/CommonBlocks/FileUploaderBlock";
    import ShowAttachmentsBlock from "../../../../components/CommonBlocks/ShowAttachmentsBlock";

    export default {
        name: "FeedbackResultPopup",
        mixins: [
            PopupAnimationMixin,
        ],
        components: {
            ShowAttachmentsBlock,
            FileUploaderBlock,
            SelectBlock,
        },
        data: function () {
            return {
                styles: {
                    images: {
                        height: '60px',
                        position: 'relative',
                        overflow: 'auto',
                        border: '1px solid #ccc',
                        borderRadius: '5px',
                        padding: '3px',
                    },
                    files: {
                        maxHeight: '75px',
                        overflow: 'auto',
                        border: '1px solid #ccc',
                        borderRadius: '5px',
                        padding: '3px',
                    },
                },
                insert_object: {
                    '_tmp_id': uuidv4(),
                    'user_signature': '',
                    'notes': '',
                    'received_attachments': '',
                },
                //Attachments mixin
                tableRow: {
                    id: null,
                    _images_for_received: [],
                    _files_for_received: [],
                },
                //PopupAnimationMixin
                getPopupWidth: 500,
                getPopupHeight: 'auto',
                idx: 0,
            }
        },
        props: {
            selected_feedback: {
                type: Object,
                required: true,
            },
        },
        computed: {
            tableMeta() {
                return this.$root.settingsMeta['stim_app_view_feedback_results'];
            },
            tbHeader() {
                return _.find(this.tableMeta._fields, {f_type: 'Attachment'});
            },
            //Attachments mixin
            tableHeader() {
                return {
                    id: this.tbHeader ? this.tbHeader.id : null,
                    field: 'received'
                };
            },
        },
        methods: {
            //additionals
            hide() {
                this.$root.tablesZidx -= 10;
                this.$emit('popup-close');
            },

            //work with Feedbacks
            insertFeedbackResult() {
                if (!this.insert_object.notes || !this.insert_object.user_signature) {
                    Swal('Required fields are empty!');
                    return;
                }

                this.$root.sm_msg_type = 1;
                axios.post('/ajax/wid_view/feedback/result', {
                    feedback_id: this.selected_feedback.id,
                    fields: this.insert_object,
                }).then(({ data }) => {
                    this.$emit('result-submitted', data);
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
            insAttach(idx, file) {
                this.insert_object.received_attachments = file.filepath + file.filename;
                if (file.is_img) {
                    this.tableRow._images_for_received.push(file);
                } else {
                    this.tableRow._files_for_received.push(file);
                }
            },
            sendUpdateSignal() {
                //AttachmentsMixin
            },
        },
        mounted() {
            this.tableRow.id = this.insert_object._tmp_id;

            this.$root.tablesZidx += 10;
            this.zIdx = this.$root.tablesZidx;
            this.runAnimation();
        },
        beforeDestroy() {
        }
    }
</script>

<style lang="scss" scoped>
    @import "../../../../components/CustomPopup/CustomEditPopUp";
    @import "../../../../components/CustomCell/CustomCell";

    label {
        margin: 0;
    }
</style>