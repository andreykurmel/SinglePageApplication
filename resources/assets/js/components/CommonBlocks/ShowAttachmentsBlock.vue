<template>
    <div class="full-height">
        <div v-if="image_carousel" class="full-height">
            <carousel-block :images="attachImages" @img-clicked="imgClick"></carousel-block>
        </div>
        <div v-else
             class="full-height"
        >
            <!--IMAGES-->
            <!--As Names-->
            <div v-if="imagesAsFiles" class="no-wrap" :style="extraStyles.images">
                <template v-for="(image, idx) in attachImages">
                    <span>&nbsp;</span>
                    <a target="_blank" class="has-deleter" @click="dwnFile(image)" :href="dwnPath(image)">
                        <span>{{ image.filename }}</span>
                        <span v-if="canEdit"
                              class="img--deleter"
                              @click.stop.prevent="deleteFile('img', idx, tableHeader)"
                        >&times;</span>
                    </a>
                </template>
            </div>
            <!--As images-->
            <div v-else
                 ref="drop_ref"
                 class="full-height no-wrap images_wrap"
                 :class="images_wrap_class"
                 @dragenter="drEnter"
                 @dragover.prevent=""
                 @dragleave="drLeave"
                 @drop.prevent.stop="attachDrop"
                 :style="extraStyles.images"
            >
                <template v-for="(image, idx) in attachImages">
                    <a target="_blank"
                       class="img_a has-deleter"
                       :class="{'flex-i': justFirst, 'flex--center-v': justFirst}"
                       @click="dwnFile(image)"
                       :href="dwnPath(image)"
                    >
                        <img :class="imgClass"
                             :src="$root.fileUrl(image)"
                             @click="imgClick(attachImages, idx)"
                             @mousedown.stop=""
                             @mouseup.stop="">
                        <span v-if="canEdit"
                              class="img--deleter"
                              @click.stop.prevent="deleteFile('img', idx, tableHeader)"
                        >&times;</span>
                    </a>
                </template>
            </div>

            <!--FILES-->
            <div v-if="attachFiles && (!attachImages || forceFiles)" class="no-wrap" :style="extraStyles.files">
                <template v-for="(file, idx) in attachFiles">
                    <a target="_blank" class="has-deleter" @click="dwnFile(file)" :href="dwnPath(file)">
                        <span>{{ file.filename }}</span>
                        <span v-if="canEdit"
                              class="img--deleter"
                              @click.stop.prevent="deleteFile('file', idx, tableHeader)"
                        >&times;</span>
                    </a>
                    <br/>
                </template>
            </div>

        </div>



        <!-- Full-size img for attachments -->
        <full-size-img-block
            v-if="overImages && overImages.length"
            :file_arr="overImages"
            :file_idx="overImageIdx"
            @close-full-img="overImages = null"
        ></full-size-img-block>
    </div>
</template>

<script>
    import {SpecialFuncs} from '../../classes/SpecialFuncs';
    import {Endpoints} from "../../classes/Endpoints";
    import {FileHelper} from "../../classes/helpers/FileHelper";

    import FullSizeImgBlock from "./FullSizeImgBlock";
    import CarouselBlock from "./CarouselBlock";

    export default {
        components: {
            CarouselBlock,
            FullSizeImgBlock,
        },
        data: function () {
            return {
                overImages: null,
                overImageIdx: null,
                attach_overed: false,
            }
        },
        props: {
            imageFit: String,
            showType: String,
            tableMeta: {
                type: Object,
                required: true,
            },
            tableHeader: {
                type: Object,
                required: true,
            },
            tableRow: {
                type: Object,
                required: true,
            },
            canEdit: Boolean,
            justFirst: Boolean,
            forceFiles: Boolean,
            imagesAsFiles: Boolean,
            extraStyles: {
                type: Object,
                default() {
                    return {};
                },
            },
        },
        computed: {
            images_wrap_class() {
                return {
                    'absolute-frame': !this.image_carousel,
                    'overed_frame': this.attach_overed,
                    'wrap_for_one': this.justFirst,
                };
            },
            image_carousel() {
                let st = this.showType || this.tableHeader.image_display_view;
                return String(st).toLowerCase() === 'slide';
            },
            attachImages() {
                let images = this.tableRow['_images_for_'+this.tableHeader.field];
                if (images && images.length) {
                    return images;
                }
                return null;
            },
            attachFiles() {
                let files = this.tableRow['_files_for_'+this.tableHeader.field];
                if (files && files.length) {
                    return files;
                }
                return null;
            },
            imgClass() {
                let fit = this.imageFit || this.tableHeader.image_display_fit;
                switch (fit) {
                    case 'width': return '_img_preview__width';
                    case 'height': return '_img_preview__height';
                    default: return '_img_preview';
                }
            },
        },
        methods: {

            //attachments
            dwnFile(file) {
                if (window.event.ctrlKey) {
                    window.event.preventDefault();
                    window.location = this.dwnPath(file)+'&dwn=true';
                }
            },
            dwnPath(file) {
                return this.$root.fileUrl(file);
            },
            imgClick(images, idx) {
                if (!window.event.ctrlKey) {
                    this.overImages = images;
                    this.overImageIdx = idx;
                    window.event.stopPropagation();
                    window.event.preventDefault();
                }
            },
            deleteFile(type, idx, tableHeader) {
                if (!this.canEdit) {
                    return;
                }
                Swal({
                    title: 'File deleted cannot be recovered!',
                    text: 'Are you sure?',
                    showCancelButton: true,
                }).then((result) => {
                    if (result.value) {
                        let key = (type === 'file' ? '_files_for_' : '_images_for_') + tableHeader.field;
                        let file = this.tableRow[key][idx];
                        this.$root.sm_msg_type = 1;
                        axios.delete('/ajax/files', {
                            params: {
                                id: file.id,
                                table_id: file.table_id,
                                table_field_id: file.table_field_id,
                                row_id: this.tableRow.id,
                                special_params: SpecialFuncs.specialParams(),
                            }
                        }).then(({ data }) => {
                            this.tableRow[key].splice(idx, 1);
                            this.sendUpdateSignal('');
                        }).catch(errors => {
                            Swal('', getErrors(errors));
                        }).finally(() => {
                            this.$root.sm_msg_type = 0;
                        });
                    }
                });
            },

            //drag&drop to the cell directly
            drEnter(e) {
                this.attach_overed = this.canEdit;
            },
            drLeave(e) {
                if ($(this.$refs.drop_ref).has(e.fromElement).length === 0) {
                    this.attach_overed = false;
                }
            },
            attachDrop(ev) {
                if (this.canEdit && this.tableHeader.f_type === 'Attachment') {
                    let file = ev.dataTransfer.items && ev.dataTransfer.items[0] && ev.dataTransfer.items[0].kind === 'file'
                        ? ev.dataTransfer.items[0].getAsFile()
                        : null;

                    if (FileHelper.checkFile(file, this.tableHeader.f_format)) {
                        Endpoints.fileUpload({
                            table_id: this.tableMeta.id,
                            table_field_id: this.tableHeader.id,
                            row_id: this.tableRow.id || this.tableRow._temp_id,
                            file: file,
                            special_params: JSON.stringify(SpecialFuncs.specialParams()),
                        }).then(({ data }) => {
                            this.$root.attachFileToRow(this.tableRow, this.tableHeader, data);
                            if (data.filepath && data.filename) {
                                this.sendUpdateSignal(data.filepath + data.filename);
                            }
                        });
                    }
                }
                this.attach_overed = false;
            },
            sendUpdateSignal(file) {
                this.$emit('update-signal', file);
            },

        },
    }
</script>

<style lang="scss" scoped>
    .has-deleter {
        display: inline-block;
        position: relative;
    }
    .has-deleter > .img--deleter {
        display: none;
        color: #F00;
        font-size: 1.6em;
        font-weight: bold;
        line-height: 0.8em;
        position: absolute;
        top: calc(50% - 8px);
        left: 100%;
    }
    .has-deleter:hover > .img--deleter {
        display: inline-block;
    }

    .images_wrap {
        overflow-x: auto;
        overflow-y: hidden;
    }

    .img_a {
        height: 100%;

        &:not(:last-child) {
            margin-right: 10px;
        }

        ._img_preview {
            max-width: 100%;
            max-height: 100%;
        }
        ._img_preview__width {
            max-width: 100%;
        }
        ._img_preview__height {
            max-height: 100%;
        }
    }
    
    .img_a_auto {
        height: auto;
    }

    .overed_frame {
        border: 4px dashed #F77;
    }

    .wrap_for_one {
        display: -webkit-box;
    }
</style>