
<template>
    <div>
        <div class="eml_block">

            <div class="eml_header" :style="{backgroundColor: emailSettings.email_background_header}">
                <div class="flex flex--center-v">
                    <label>From:</label>
                    <span>{{ element.from }}</span>
                    <label>&nbsp;&nbsp;&nbsp;Reply To:</label>
                    <span>{{ element.reply.join(', ') }}</span>
                </div>
                <div>
                    <label>To:</label>
                    <span>{{ element.to.join(', ') }}</span>
                </div>
                <div v-if="element.cc.length">
                    <label>CC:</label>
                    <span>{{ element.cc.join(', ') }}</span>
                </div>
                <div v-if="element.bcc.length">
                    <label>BCC:</label>
                    <span>{{ element.bcc.join(', ') }}</span>
                </div>
                <div>
                    <label>Subject:</label>
                    <span>{{ element.subject }}</span>
                </div>

                <div v-if="emlAttachField && element.row_tablda">
                    <div class="eml_attach_field flex flex--center-v">
                        <label>Attachments:</label>
                        <show-attachments-block
                            :images-as-files="true"
                            :force-files="true"
                            :table-header="emlAttachField"
                            :table-meta="tableMeta"
                            :table-row="element.row_tablda"
                        ></show-attachments-block>
                    </div>
                </div>

                <div v-if="shortView && lastHistory && lastHistory.preview_body === element.body" class="sent_at">
                    <label>Sent at: {{ $root.convertToLocal(lastHistory.send_date, $root.user.timezone) }}</label>
                </div>
            </div>

            <div :style="{backgroundColor: emailSettings.email_background_body}">
                <div v-html="element.body"></div>
            </div>

        </div>

        <template v-if="!shortView">
            <div v-for="hist in element.history" class="eml_block">

                <div class="eml_header" :style="{backgroundColor: emailSettings.email_background_header}">
                    <div class="flex flex--center-v">
                        <label>From:</label>
                        <span>{{ hist.preview_from }}</span>
                        <label>&nbsp;&nbsp;&nbsp;Reply To:</label>
                        <span>{{ hist.preview_reply.join(', ') }}</span>
                    </div>
                    <div>
                        <label>To:</label>
                        <span>{{ hist.preview_to.join(', ') }}</span>
                    </div>
                    <div v-if="hist.preview_cc.length">
                        <label>CC:</label>
                        <span>{{ hist.preview_cc.join(', ') }}</span>
                    </div>
                    <div v-if="hist.preview_bcc.length">
                        <label>BCC:</label>
                        <span>{{ hist.preview_bcc.join(', ') }}</span>
                    </div>
                    <div>
                        <label>Subject:</label>
                        <span>{{ hist.preview_subject }}</span>
                    </div>

                    <div v-if="emlAttachField && hist.preview_row_tablda">
                        <div class="eml_attach_field flex felx--center-v">
                            <label>Attachments:</label>
                            <show-attachments-block
                                :images-as-files="true"
                                :force-files="true"
                                :table-header="emlAttachField"
                                :table-meta="tableMeta"
                                :table-row="hist.preview_row_tablda"
                            ></show-attachments-block>
                        </div>
                    </div>

                    <div class="sent_at">
                        <label>Sent at: {{ $root.convertToLocal(hist.send_date, $root.user.timezone) }}</label>
                    </div>
                </div>

                <div :style="{backgroundColor: emailSettings.email_background_body}">
                    <div v-html="hist.preview_body"></div>
                </div>

            </div>
        </template>
    </div>
</template>

<script>
    import ShowAttachmentsBlock from "../../../../CommonBlocks/ShowAttachmentsBlock";

    export default {
        name: "EmailPreview",
        mixins: [
        ],
        components: {
            ShowAttachmentsBlock
        },
        data: function () {
            return {
            }
        },
        props:{
            element: Object,
            tableMeta: Object,
            emailSettings: Object,
            shortView: Boolean,
        },
        computed: {
            emlAttachField() {
                return _.find(this.tableMeta._fields, {id: Number(this.emailSettings.field_id_attachments)});
            },
            lastHistory() {
                return _.first(this.element.history || []);
            },
        },
        methods: {
        },
        mounted() {
        },
        beforeDestroy() {
        }
    }
</script>

<style lang="scss" scoped>
    label {
        margin: 0;
    }
    .eml_block {
        background-color: #F4f4f4;
        margin-bottom: 30px;

        .eml_header {
            position: relative;
            background-color: #DDD;
        }
        .eml_attach_field {
            position: relative;
        }
        .sent_at {
            position: absolute;
            bottom: 5px;
            right: 5px;
        }
    }
</style>