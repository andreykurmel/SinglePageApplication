<template>
    <div class="container-wrapper">
        <div class="flexer">
            <div v-if="!errors_present.length">
                <button class="btn btn-default m-right"
                        @click="uploadForm = true"
                >Upload New</button>
                <button class="btn btn-default"
                        :disabled="!file_present"
                        :title="file_present ? '' : 'File is not uploaded'"
                        @click="sendParse()"
                >Parsing Existing</button>
            </div>
            <div v-else="">
                <label>Errors:</label>
                <br>
                <label v-html="errors_present.join('<br>')"></label>
            </div>
        </div>

        <!--Upload form-->
        <div v-show="uploadForm" class="modal-wrapper" @click.self="uploadForm = false">
            <div class="modal" @click.self="uploadForm = false">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <span class="pull-right close-modal" @click="uploadForm = false">&times;</span>
                            <h4 class="modal-title">Upload Risa3d File</h4>
                        </div>
                        <div class="modal-body">
                            <div class="container-fluid">
                                <file-uploader-block
                                        v-if="table_id && file_col && row_id"
                                        class="form-group upload-group"
                                        :header-index="0"
                                        :table_id="table_id"
                                        :field_id="file_col"
                                        :row_id="row_id"
                                        :clear_before="true"
                                        @uploaded-file="uploadedFile"
                                ></file-uploader-block>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import FileUploaderBlock from './../components/CommonBlocks/FileUploaderBlock.vue';

    export default {
        name: 'Risa3dForm',
        mixins: [
        ],
        components: {
            FileUploaderBlock,
        },
        data() {
            return {
                errors_present: [],
                uploadForm: false,
                file_present: !!this.init_file_present,
            }
        },
        props: {
            usergroup: String,
            mg_name: String,
            file_col: Number,
            row_id: Number,
            table_id: Number,
            init_file_present: Number,
        },
        methods: {
            uploadedFile() {
                this.file_present = true;
                this.uploadForm = false;
                this.sendParse();
            },
            sendParse() {
                $.LoadingOverlay('show');
                axios.post('/apps/risa3d/parser', {
                    usergroup: this.usergroup,
                    mg_name: this.mg_name,
                    table_id: this.table_id,
                    row_id: this.row_id,
                    column_id: this.file_col,
                }).then(({ data }) => {
                    if (data.length) {
                        Swal({ html: data.join('<br>') });
                    } else {
                        Swal('Successfully parsed!').then(() => {
                            let data = {
                                event_name: 'close-application',
                                app_code: 'risa3d_parser',
                            };
                            window.parent.postMessage(data, '*');
                        });
                    }
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => $.LoadingOverlay('hide'));
            },
        },
        mounted() {
            if (!this.usergroup) {
                this.errors_present.push('Param "Usergroup" is not present!');
            }
            if (!this.mg_name) {
                this.errors_present.push('Param "MG Name" is not present!');
            }
            if (!this.row_id) {
                this.errors_present.push('Param "Row Id" is not present or incorrect!');
            }
            if (!this.file_col) {
                this.errors_present.push('Param "File Column" is not present or incorrect!');
            }
            if (!this.table_id) {
                this.errors_present.push('Calculated "Table Id" is incorrect!');
            }
        }
    }
</script>

<style lang="scss" scoped="">
    .container-wrapper {
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;

        .flexer {
            display: flex;
            background-color: #005fa4;
            color: #FFF;
            padding: 25px;
            border-radius: 20px;

            .m-right {
                margin-right: 25px;
            }
        }
    }

    .modal-wrapper {
        position: fixed;
        top: 0;
        left: 0;
        bottom: 0;
        right: 0;
        z-index: 1500;
        background: rgba(0, 0, 0, 0.45);

        .modal {
            display: block;
            top: 50%;
            transform: translateY(-50%);
            margin: 0 auto;

            .close-modal {
                font-size: 2.5em;
                line-height: 0.8em;
                cursor: pointer;
            }

        }
    }
</style>