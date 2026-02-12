<template>
    <div class="full-height position-relative attach-wrap">
        <div v-if="image_carousel" class="full-height">
            <carousel-block :images="attachImagesAndLinks" :can_click="true" @img-clicked="imgClick"></carousel-block>
        </div>
        <div v-else
             class="full-height"
        >
            <!--IMAGES-->
            <!--As Names-->
            <div v-if="imagesAsFiles" class="no-wrap" :style="styleImages">
                <template v-for="(image, idx) in attachImagesAndLinks">
                    <span>&nbsp;</span>
                    <a target="_blank" class="has-deleter" @click="dwnFile(image)" :href="dwnPath(image)">
                        <span>{{ image.filename }}</span>
                        <span v-if="canEdit && !image.is_remote"
                              class="img--deleter"
                              @click.stop.prevent="deleteFile(image, idx, tableHeader)"
                        >&times;</span>
                    </a>
                </template>
            </div>
            <!--As images in GridView-->
            <div v-if="isGridView && firstImage" class="full-height position-relative">
                <a target="_blank" class="full-height has-deleter flex-i flex--center-v" :style="styleImages">
                    <single-attachment-block
                        :attachment="firstImage"
                        :is_full_size="false"
                        :image_fit="imageFit || tableMeta.board_display_fit"
                        :thumb="extThumb || 'md'"
                    ></single-attachment-block>
                </a>
                <div class="more-images flex flex--center" @click="imgClick(attachImagesAndLinks, 0)">+{{ attachImagesAndLinks.length-1 }}</div>
            </div>
            <!--As images in Vertical table-->
            <div v-else
                 ref="drop_ref"
                 class="full-height no-wrap images_wrap"
                 :class="images_wrap_class"
                 @dragenter="drEnter"
                 @dragover.prevent=""
                 @dragleave="drLeave"
                 @drop.prevent.stop="attachDrop"
                 :style="styleImages"
            >
                <template v-for="(image, idx) in attachImagesAndLinks">
                    <a target="_blank"
                       class="img_a has-deleter"
                       :class="{'flex-i': justFirst, 'flex--center-v': justFirst}"
                       @click="dwnFile(image)"
                       :href="dwnPath(image)"
                    >
                        <single-attachment-block
                            :attachment="image"
                            :is_full_size="false"
                            :image_fit="imageFit || tableMeta.board_display_fit"
                            :thumb="extThumb || 'md'"
                            @img-clicked="() => { imgClick(attachImagesAndLinks, idx) }"
                        ></single-attachment-block>

                        <span v-if="canEdit && !image.is_remote"
                              class="img--deleter"
                              :style="{top: !image.special_mark && image.is_video ? '0' : ''}"
                              @click.stop.prevent="deleteFile(image, idx, tableHeader)"
                              @mousedown.stop=""
                              @mouseup.stop=""
                        >&times;</span>
                    </a>
                </template>
            </div>

            <!--FILES-->
            <div v-if="attachFiles && (!attachImagesAndLinks || forceFiles)" class="no-wrap" :style="extraStyles.files">
                <template v-for="(file, idx) in attachFiles">
                    <a target="_blank" class="has-deleter" @click="dwnFile(file)" :href="dwnPath(file)">
                        <span>
                            <img v-if="isPdf(file)" src="/assets/img/icons/pdf_icon.png" width="17" height="17">
                            {{ file.filename }}
                        </span>
                        <span v-if="canEdit && !file.is_remote"
                              class="img--deleter"
                              @click.stop.prevent="deleteFile(file, idx, tableHeader)"
                              @mousedown.stop=""
                              @mouseup.stop=""
                        >&times;</span>
                    </a>
                    <br/>
                </template>
            </div>

        </div>


        <div v-if="hasRemote"
             class="remote-resync"
             @click.stop.prevent="remoteResync()"
             @mousedown.stop=""
             @mouseup.stop=""
        >
            <i class="fas fa-sync"></i>
        </div>


        <!-- Full-size img for attachments -->
        <full-size-img-block
            v-if="overImages && overImages.length"
            :table_meta="tableMeta"
            :table_header="tableHeader"
            :table_row="tableRow"
            :file_arr="overImages"
            :file_idx="overImageIdx"
            :single_full_size_image="overImages.length == 1 && overImageIdx == 0"
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
    import SingleAttachmentBlock from "./SingleAttachmentBlock";

    export default {
        components: {
            SingleAttachmentBlock,
            CarouselBlock,
            FullSizeImgBlock,
        },
        data: function () {
            return {
                overImages: null,
                overImageIdx: null,
                attach_overed: false,
                resync_process: false,
            }
        },
        props: {
            isGridView: Boolean,
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
            extThumb: String,
            imagesPrefix: {
                type: String,
                default: '_images_for_',
            },
            filesPrefix: {
                type: String,
                default: '_files_for_',
            },
            clearBefore: {
                type: Number,
                default: 0,
            },
            emitOverImages: Boolean,
        },
        computed: {
            images_wrap_class() {
                return {
                    'absolute-frame': !this.image_carousel,
                    'overed_frame': this.attach_overed,
                    'flex': this.justFirst,
                    'flex--center': this.justFirst,
                };
            },
            image_carousel() {
                let st = this.showType || this.tableMeta.board_display_view;
                return String(st).toLowerCase() === 'slide';
            },
            attachImagesAndLinks() {
                let images = this.tableRow[this.imagesPrefix+this.tableHeader.field] || [];
                return images.length ? images : null;
            },
            attachFiles() {
                let files = this.tableRow[this.filesPrefix+this.tableHeader.field] || [];
                return files.length ? files : null;
            },
            firstImage() {
                return this.attachImagesAndLinks && this.attachImagesAndLinks.length > 1
                    ? _.first(_.orderBy(this.attachImagesAndLinks, ['is_video'], ['asc']))
                    : '';
            },
            hasRemote() {
                return !this.resync_process && !!this.tableHeader.fetch_source_id;
            },
            styleImages() {
                return {
                    textAlign: this.tableHeader.col_align || 'center',
                    justifyContent: this.tableHeader.col_align || 'center',
                    ...this.extraStyles.images,
                };
            },
        },
        methods: {
            isPdf(file) {
                return String(file.remote_link).match(/.pdf$/gi);
            },
            //attachments
            dwnFile(file) {
                let cmdOrCtrl = window.event.metaKey || window.event.ctrlKey;
                if (cmdOrCtrl) {
                    window.event.preventDefault();
                    window.location = this.dwnPath(file)+'&dwn=true';
                }
            },
            dwnPath(file) {
                return this.$root.fileUrl(file);
            },
            imgClick(images, idx) {
                let cmdOrCtrl = window.event.metaKey || window.event.ctrlKey;
                if (!cmdOrCtrl) {
                    window.event.stopPropagation();
                    window.event.preventDefault();
                    if (this.emitOverImages) {
                        this.$emit('over-images', images, idx);
                    } else {
                        this.overImages = images;
                        this.overImageIdx = idx;
                    }
                }
            },
            deleteFile(file, idx, tableHeader) {
                if (!this.canEdit) {
                    return;
                }
                Swal({
                    title: 'Info',
                    text: 'File deleted cannot be recovered! Are you sure?',
                    showCancelButton: true,
                }).then((result) => {
                    if (result.value) {
                        if (this.tableRow.id) {
                            this.$root.sm_msg_type = 1;
                            axios.delete(FileHelper.fileUrl(file), {
                                params: {
                                    id: file.id,
                                    table_id: file.table_id,
                                    table_field_id: file.table_field_id,
                                    row_id: this.tableRow.id,
                                    special_params: SpecialFuncs.specialParams(),
                                }
                            }).then(({data}) => {
                                this.tableRow[FileHelper.fileKey(file, tableHeader)].splice(idx, 1);
                                this.sendUpdateSignal('');
                            }).catch(errors => {
                                Swal('Info', getErrors(errors));
                            }).finally(() => {
                                this.$root.sm_msg_type = 0;
                            });
                        } else {
                            this.tableRow[FileHelper.fileKey(file, tableHeader)].splice(idx, 1);
                            this.sendUpdateSignal('');
                        }
                    }
                });
            },
            remoteResync() {
                this.resync_process = true;
                axios.post('/ajax/remote-files/resync-row', {
                    table_id: this.tableMeta.id,
                    table_field_id: this.tableHeader.id,
                    row_id: this.tableRow.id
                }).then(({ data }) => {
                    this.resync_process = false;
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
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
                            row_id: Number(this.tableRow.id) || this.tableRow._temp_id,
                            file: file,
                            special_params: JSON.stringify(SpecialFuncs.specialParams()),
                            clear_before: this.clearBefore,
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
    }

    .img_a_auto {
        height: auto;
    }

    .overed_frame {
        border: 4px dashed #F77;
    }

    .more-images {
        position: absolute;
        left: 0;
        right: 0;
        top: 0;
        bottom: 0;
        z-index: 95;
        background: rgba(255, 255, 255, 0.5);
        font-size: 2em;
        cursor: pointer;
    }

    .remote-resync {
        position: absolute;
        top: 3px;
        right: 3px;
        color: #222;
        font-size: 14px;
        opacity: 0.7;
        z-index: 200;
        cursor: pointer;
        display: none;

        &:hover {
            opacity: 1;
        }
    }

    .attach-wrap:hover .remote-resync {
        display: block;
    }
</style>