<template>
    <div class="flex flex--center full-height position-relative">
        <iframe v-if="is_full_size && attachment.special_mark === 'vimeo'"
                :src="$root.fileUrl(attachment)"
                allow="fullscreen; picture-in-picture"
                allowfullscreen
                width="100%"
                height="100%"
        ></iframe>
        <iframe v-else-if="is_full_size && attachment.special_mark === 'youtube'"
                :src="$root.fileUrl(attachment)"
                allow="accelerometer; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                allowfullscreen
                width="100%"
                height="100%"
        ></iframe>
        <video v-else-if="is_full_size && attachment.is_video"
               controls
               :src="$root.fileUrl(attachment)"
               :class="imgClass"
        ></video>
        <audio v-else-if="is_full_size && attachment.is_audio"
               controls
               :src="$root.fileUrl(attachment)"
               :class="imgClass"
        ></audio>

        <img v-else-if="attachment.special_mark === 'vimeo'"
             :src="attachment.special_content"
             :class="imgClass"
             @click="$emit('img-clicked')"
             :style="{cursor: !is_full_size ? 'zoom-in' : ''}"
             @mousedown.stop=""
             @mouseup.stop=""/>
        <img v-else-if="attachment.special_mark === 'youtube'"
             :src="ytbLnk(attachment)"
             :class="imgClass"
             @click="$emit('img-clicked')"
             :style="{cursor: !is_full_size ? 'zoom-in' : ''}"
             @mousedown.stop=""
             @mouseup.stop=""/>

        <video v-else-if="attachment.is_video"
               :src="$root.fileUrl(attachment)"
               :class="imgClass"
               @click="$emit('img-clicked')"
               :style="{cursor: !is_full_size ? 'zoom-in' : ''}"
               @mousedown.stop=""
               @mouseup.stop=""
        ></video>

        <img v-else-if="attachment.is_audio"
             src="/assets/img/audio_tag.png"
             :class="imgClass"
             @click="$emit('img-clicked')"
             :style="{cursor: !is_full_size ? 'zoom-in' : ''}"
             @mousedown.stop=""
             @mouseup.stop=""/>

        <template v-else>
            <img :src="$root.fileUrl(attachment, thumb)"
                 :class="imgClass"
                 ref="imgfile"
                 @click="$emit('img-clicked')"
                 :style="{cursor: !is_full_size ? 'zoom-in' : ''}"
                 @mousedown.stop=""
                 @mouseup.stop=""/>

            <div v-if="is_full_size && table_header && (table_header.markerjs_annotations || table_header.markerjs_cropro)" class="menu-btn">
                <i class="fas fa-edit"></i>
                <!-- Visible on:hover menu-btn -->
                <div class="menu-wrapper">
                    <div>
                        <label>Editing options:</label>
                        <div>
                            <button class="btn btn-primary btn-sm blue-gradient"
                                    :style="$root.themeButtonStyle"
                                    :title="imgElement ? 'Loading...' : ''"
                                    :disabled="!table_header.markerjs_annotations || !imgElement"
                                    @click="showAnnotation()"
                            >Annotation</button>
                            <button class="btn btn-primary btn-sm blue-gradient"
                                    :style="$root.themeButtonStyle"
                                    :title="imgElement ? 'Loading...' : ''"
                                    :disabled="!table_header.markerjs_cropro || !imgElement"
                                    @click="showCroPro()"
                            >CroPro</button>
                        </div>
                    </div>
                    <div>
                        <label>Saving options:</label>
                        <div>
                            <input type="radio" v-model="table_header.markerjs_savetype" :value="'replace'" @change="updateSaveStyle()"/>
                            <span>Replace Existing</span>
                        </div>
                        <div>
                            <input type="radio" v-model="table_header.markerjs_savetype" :value="'savecopy'" @change="updateSaveStyle()"/>
                            <span>Save As Renamed</span>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>
</template>

<script>
    import {Endpoints} from "../../classes/Endpoints";
    import {SpecialFuncs} from "../../classes/SpecialFuncs";

    import * as markerjs2 from "markerjs2";
    import * as cropro from "cropro";

    export default {
        name: "SingleAttachmentBlock",
        mixins: [
        ],
        components: {
        },
        data: function () {
            return {
            };
        },
        computed: {
            imgClass() {
                let fit = this.image_fit || 'full';
                switch (fit) {
                    case 'width': return 'img_preview__width';
                    case 'height': return 'img_preview__height';
                    default: return 'img_preview';
                }
            },
            imgElement() {
                return this.$root.fileOnServer(this.attachment) ? this.$refs.imgfile : null;
            },
        },
        props:{
            table_meta: Object,//needed for 'is_full_size'
            table_header: Object,//needed for 'is_full_size'
            table_row: Object,//needed for 'is_full_size'
            attachment: Object,
            is_full_size: Boolean,
            image_fit: String,
            thumb: String,
        },
        methods: {
            ytbLnk(attachment) {
                return 'http://img.youtube.com/vi/'+String(attachment.filename).replace('.', '')+'/maxresdefault.jpg'
                    || 'https://i.ytimg.com/vi/'+String(attachment.filename).replace('.', '')+'/0.jpg';
            },
            showAnnotation() {
                if (this.imgElement) {
                    const markerArea = new markerjs2.MarkerArea(this.imgElement);
                    markerArea.addEventListener('render', event => {
                        this.imgElement.src = event.dataUrl;
                        this.storeDataUrl(event.dataUrl);
                    });
                    markerArea.settings.displayMode = 'popup';
                    markerArea.show();
                }
            },
            showCroPro() {
                if (this.imgElement) {
                    const cropArea = new cropro.CropArea(this.imgElement);
                    cropArea.addRenderEventListener(dataUrl => {
                        this.imgElement.src = dataUrl;
                        this.storeDataUrl(dataUrl);
                    });
                    cropArea.displayMode = 'popup';
                    cropArea.show();
                }
            },
            storeDataUrl(dataUrl) {
                if (this.table_meta && this.table_header && this.table_row) {
                    let newname = String(this.attachment.filename);
                    let lastdot = _.lastIndexOf(newname, '.');
                    let replace_file_id = isNaN(this.attachment.id) ? 0 : this.attachment.id;

                    if (this.table_header.markerjs_savetype === 'savecopy') {
                        let idx = _.lastIndexOf(newname, '.');
                        newname = newname.slice(0, idx) + '_' + moment().format('YYYY-MM-DD') + newname.slice(idx);
                        replace_file_id = 0;
                    }

                    let imgfile = dataURLtoFile(dataUrl, newname);
                    Endpoints.fileUpload({
                        replace_file_id: replace_file_id,
                        table_id: this.table_meta.id,
                        table_field_id: this.table_header.id,
                        row_id: this.table_row.id,
                        file: imgfile,
                        file_new_name: '',
                        clear_before: false,
                        special_params: SpecialFuncs.specialParams(),
                    }).then(({data}) => {
                        this.$root.attachFileToRow(this.table_row, this.table_header, data, replace_file_id);
                    }).catch(errors => {
                        Swal('Info', getErrors(errors));
                    });
                }
            },
            updateSaveStyle() {
                this.table_header._changed_field = 'markerjs_savetype';
                this.$root.updateSettingsColumn(this.table_meta, this.table_header);
            },
        },
        mounted() {
        }
    }
</script>

<style lang="scss" scoped>
    .img_preview {
        max-width: 100%;
        max-height: 100%;
    }
    .img_preview__width {
        max-width: 100%;
    }
    .img_preview__height {
        max-height: 100%;
    }

    .menu-btn {
        cursor: pointer;
        color: #FFF;
        position: absolute;
        right: 5px;
        top: 5px;
        z-index: 100;

        .fas {
            color: rgb(255, 255, 255);
            margin-left: 3px;
            padding: 3px;
            background: #777;
            border-radius: 3px;
        }
    }

    .menu-wrapper {
        top: 0;
        right: 21px;
        position: absolute;
        background: #aaa;
        padding: 5px;
        border: 1px solid #777;
        border-radius: 5px;
        white-space: nowrap;
        display: none;

        label {
            margin: 0;
        }
    }

    .menu-btn:hover .menu-wrapper {
        display: block;
    }
</style>

<style>
    .__markerjs2_, .__cropro_ {
        z-index: 5000 !important;
    }
</style>