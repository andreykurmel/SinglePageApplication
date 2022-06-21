<template>
    <div style="overflow: hidden;">
        <div class="row row-padding" v-if="!just_default">
            <div class="col-xs-4">
                <select class="form-control" :disabled="progressBarWidth > -1" v-model="uploadStyle" @change="initSpecials()">
                    <option value="file">Browse</option>
                    <option value="link">Link</option>
                    <option value="drag">Drag & Drop</option>
                    <option value="photo" v-if="table_id != 'temp'">Camera (photo)</option>
                    <option value="paste">Paste</option>
                </select>
            </div>
            <div class="col-xs-4">
                <input v-if="table_id != 'temp'"
                       class="form-control"
                       :disabled="progressBarWidth > -1"
                       placeholder="Enter new name if to change"
                       v-model="uploadNewName"/>
            </div>
            <div class="col-xs-4" v-show="inArr(uploadStyle,['file','link'])">
                <button class="btn btn-primary pull-right"
                        :style="{backgroundColor: progressBarWidth > -1 ? '#FFF' : '', width: progressBarWidth > -1 ? '76px' : ''}"
                        :disabled="progressBarWidth > -1"
                        @click="insertFile()"
                >
                    <img v-if="progressBarWidth > -1" height="22" src="/assets/img/Loading_icon.gif">
                    <span v-else>Upload</span>
                </button>
                <button class="btn btn-danger pull-right" v-show="cancelSource" @click="cancelUploading()" :style="{marginRight: '10px'}">
                    <span>Cancel</span>
                </button>
            </div>
            <div class="col-xs-6" v-show="uploadStyle === 'drag'">
                <button class="btn btn-danger pull-right" v-show="activeDropzone" @click="dropzone.removeAllFiles(true)">
                    <span>Cancel</span>
                </button>
            </div>
            <div class="col-xs-4" v-show="uploadStyle === 'photo'">
                <button class="btn btn-success pull-right" @click="savePic()">
                    <span>Save</span>
                </button>
            </div>
        </div>
        <div>
            <input
                v-show="uploadStyle === 'file'"
                type="file"
                :disabled="progressBarWidth > -1"
                :accept="acceptExt"
                ref="upload_file"
                class="form-control"
                placeholder="Select a file">

            <input
                v-show="uploadStyle === 'link'"
                type="text"
                :disabled="progressBarWidth > -1"
                ref="upload_link"
                class="form-control"
                placeholder="Type a link">

            <div class="drag-drop-wrapper" v-show="uploadStyle === 'drag'">
                <div ref="dropzone_elem" class="full-frame flex flex--center">
                    <img v-if="progressBarWidth > -1" height="22" src="/assets/img/Loading_icon.gif">
                    <span v-else=""
                          class="full-frame flex flex--center"
                          :style="{backgroundColor: dragFile ? '#37B' : ''}"
                          @dragenter="dragFile = true"
                          @dragleave="dragFile = false"
                          @drop="dragFile = false"
                    >Drag & Drop File Here</span>
                </div>
            </div>

            <div class="cam-rec-wrapper" v-show="uploadStyle === 'photo'">
                <video ref="video_elem" class="vid-elem" autoplay></video>
                <canvas ref="canvas_elem" class="canv-elem" :width="canv.wi" :height="canv.he"></canvas>
                <h1 v-show="cam_not_found" class="head-elem">Camera is not found!</h1>
            </div>

            <input
                v-show="uploadStyle === 'paste'"
                type="text"
                :disabled="progressBarWidth > -1"
                class="form-control"
                placeholder="Ctrl + V to Paste File"
                @paste="attachPasted">
        </div>
        <div class="progress-wrapper" v-show="progressBarWidth > -1">
            <div class="progress-bar" :style="{width: progressBarWidth+'%'}"></div>
        </div>
    </div>
</template>

<script>
    import {Endpoints} from "../../classes/Endpoints";
    import {FileHelper} from "../../classes/helpers/FileHelper";

    import Dropzone from 'dropzone';

    export default {
        name: "FileUploaderBlock",
        data: function () {
            return {
                all_streams: null,
                uploadNewName: '',
                uploadStyle: 'drag',
                progressBarWidth: -1,
                dragFile: false,
                cancelSource: null,
                dropzone: null,
                activeDropzone: false,
                cam_not_found: false,
                canv: { wi: 1440, he:1080 } //frame-rate as 4/3
            };
        },
        props:{
            format: String,
            headerIndex: Number,
            table_id: String|Number,
            field_id: String|Number,
            row_id: String|Number,
            clear_before: Boolean,
            just_default: Boolean,
            special_params: Object,
        },
        computed: {
            acceptExt() {
                let arr = this.format ? String(this.format).split('-') : [];
                arr = String(_.first(arr) || '').split(',');
                return _.filter(arr).join(',.');
            },
        },
        methods: {
            inArr(el, arr) {
                return $.inArray(el, arr) > -1;
            },
            //working with files
            attachPasted(e) {
                let images = this.$root.getImagesFromClipboard(e);
                if (images.length) {
                    this.progressBarWidth = 0;
                    _.each(images, (file) => {

                        if (!FileHelper.checkFile(file, this.format)) {
                            return false;
                        }

                        Endpoints.fileUpload({
                            table_id: this.table_id,
                            table_field_id: this.field_id,
                            row_id: this.row_id,
                            file: file,
                            file_new_name: this.uploadNewName,
                            clear_before: this.clear_before,
                            special_params: this.special_params ? JSON.stringify(this.special_params) : null,
                        }).then(({ data }) => {
                            this.$emit('uploaded-file', this.headerIndex, data);
                        }).catch(errors => {
                            Swal('', getErrors(errors));
                        }).finally(() => {
                            this.progressBarWidth += (1/images.length)*100 + 1;
                            if (this.progressBarWidth >= 100) {
                                this.progressBarWidth = -1;
                            }
                        });
                    });
                } else {
                    Swal('', 'Images not found in the Clipboard.');
                }
            },
            insertFile(ext_file) {
                let data = new FormData();
                let file = ext_file || ($.inArray(this.uploadStyle, ['file','drag']) > -1 ? this.$refs.upload_file.files[0] : '');
                let file_link = (this.uploadStyle === 'link' ? this.$refs.upload_link.value : '');
                data.append('table_id', this.table_id);
                data.append('table_field_id', this.field_id);
                data.append('row_id', this.row_id);
                data.append('file', file);
                data.append('file_link', file_link);
                data.append('file_new_name', this.uploadNewName);
                data.append('clear_before', this.clear_before ? 1 : 0);
                if (this.special_params) {
                    data.append('special_params', JSON.stringify(this.special_params));
                }

                if (!FileHelper.checkFile(file, this.format)) {
                    return false;
                }

                if (file || file_link) {
                    this.progressBarWidth = 0;
                    this.cancelSource = axios.CancelToken.source();
                    axios.post('/ajax/files', data, {
                        headers: {
                            'Content-Type': 'multipart/form-data'
                        },
                        cancelToken: this.cancelSource.token,
                        onUploadProgress: ( progressEvent ) => {
                            this.progressBarWidth = parseInt( Math.round( ( progressEvent.loaded * 100 ) / progressEvent.total ) );
                        }
                    }).then(({ data }) => {
                        this.$emit('uploaded-file', this.headerIndex, data);
                        $(this.$refs.upload_file).val(null);
                        $(this.$refs.upload_link).val(null);
                    }).catch(errors => {
                        if (axios.isCancel(errors)) {
                            Swal('Canceled', '', 'info');
                        } else {
                            Swal('', getErrors(errors));
                        }
                    }).finally(() => {
                        this.progressBarWidth = -1;
                        this.cancelSource = null;
                    });
                } else {
                    Swal('No file', '', 'info');
                }
            },
            initDropzone() {
                this.dropzone = new Dropzone(this.$refs.dropzone_elem, {
                    url: '/ajax/files',
                    paramName: 'file',
                    previewTemplate: '<div></div>',
                    clickable: false,
                    autoQueue: false,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    addedfile: ( file ) => {
                        if (FileHelper.checkFile(file, this.format)) {
                            file.accepted = true;
                            this.activeDropzone = true;
                            this.dropzone.enqueueFile(file);
                        }
                    },
                    uploadprogress: ( file, progress, bytesSent ) => {
                        this.progressBarWidth = parseInt( progress );
                    },
                    params: {
                        table_id: this.table_id,
                        table_field_id: this.field_id,
                        row_id: this.row_id,
                        file_new_name: this.uploadNewName,
                        clear_before: this.clear_before ? 1 : 0,
                        special_params: this.special_params ? JSON.stringify(this.special_params) : null,
                    },
                    success: (file, data) => {
                        //add uploaded file to the row
                        this.$emit('uploaded-file', this.headerIndex, data);
                        Swal('Uploaded', '', 'success');
                    },
                    error: (file, errors) => {
                        Swal('', errors.message);
                    },
                    complete: ( file ) => {
                        this.activeDropzone = false;
                        this.progressBarWidth = -1;
                    },
                });
            },
            initCameraRec() {
                if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia && this.$refs.video_elem) {
                    navigator.mediaDevices.getUserMedia({ video: true }).then((stream) => {
                        this.all_streams = stream;
                        this.$refs.video_elem.srcObject = stream;
                        this.$refs.video_elem.play();
                    });
                } else {
                    this.cam_not_found = true;
                }
            },
            stopCamRec() {
                if (this.$refs.video_elem && this.$refs.video_elem.srcObject) {
                    const stream = this.$refs.video_elem.srcObject;
                    const tracks = stream.getTracks();
                    tracks.forEach(function(track) {
                        track.stop();
                    });
                    this.$refs.video_elem.pause();
                    this.$refs.video_elem.srcObject = null;
                }
            },
            savePic() {
                let canvas = this.$refs.canvas_elem;
                let context = canvas.getContext('2d');
                let video = this.$refs.video_elem;
                context.drawImage(video, 0, 0, canvas.width, canvas.height);

                let imgfile = dataURLtoFile( canvas.toDataURL('image/png'), uuidv4()+'.png' );
                this.insertFile( imgfile );
                context.clearRect(0, 0, canvas.width, canvas.height);
            },
            cancelUploading() {
                if (this.cancelSource) {
                    this.cancelSource.cancel();
                }
            },
            initSpecials() {
                if (this.uploadStyle === 'drag' && !this.dropzone) {
                    this.initDropzone();
                }
                if (this.uploadStyle === 'photo') {
                    this.initCameraRec();
                } else {
                    this.stopCamRec();
                }
            },
        },
        mounted() {
            this.initSpecials();
        }
    }
</script>

<style lang="scss" scoped>
    .upload-group {
        padding: 10px;
        border: 1px solid #CCC;
        .row-padding {
            padding-bottom: 5px;
        }
    }

    .drag-drop-wrapper {
        position: relative;
        height: 75px;
        border:2px dashed #ccc;
    }

    .cam-rec-wrapper {
        position: relative;
        height: 250px;

        .vid-elem {
            position: absolute;
            left: 0px;
            top: 0px;
            width: 100%;
            height: 100%;
            border: 1px solid #CCC;
        }
        .canv-elem {
            width: 1440px;
            height: 1080px;
            position: absolute;
        }
        .head-elem {
            margin: 0;
            text-align: center;
            padding-top: 100px;
        }
    }

    .progress-wrapper {
        margin-top: 10px;
        height: 10px;
        border-radius: 5px;
        border: 1px solid #CCC;
    }
</style>