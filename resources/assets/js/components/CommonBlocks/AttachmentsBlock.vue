<template>
    <div :class="[tab_style ? '' : 'flex flex--col']" v-if="tableRow && tableMeta">
        <div class="popup-menu">
            <button v-if="canWorkWithAttach" class="btn btn-default" :class="{active: activeAttachTab === 'uploads'}" @click="activeAttachTab = 'uploads'">Upload New</button>
            <button class="btn btn-default" :class="{active: activeAttachTab === 'pictures'}" @click="activeAttachTab = 'pictures'">Pictures ({{ imgCount }})</button>
            <button class="btn btn-default" :class="{active: activeAttachTab === 'files'}" @click="activeAttachTab = 'files'">Files ({{ fileCount }})</button>
        </div>
        <!--Pictures Tab-->
        <div class="flex__elem-remain popup-tab" v-show="activeAttachTab === 'pictures'" :style="tab_style">
            <div v-for="(tableHeader, index) in tableMeta._fields"
                 v-if="headerAvailToShow(tableHeader, '_images_for_')">
                <label>{{ getHeader(tableHeader, '_images_for_') }}</label>
                <attachments-block-buttons
                    v-if="canChangeFile(tableHeader)"
                    :all_type="'images'"
                    :table-row="tableRow"
                    :table-header="tableHeader"
                    :special_params="special_params"
                    style="float: right"
                    @changed-file="$emit('updated-cell')"
                ></attachments-block-buttons>
                <table class="table">
                    <colgroup>
                        <col :style="{width: '40%'}">
                        <col :style="{width: '10%'}">
                        <col :style="{width: '40%'}">
                        <col :style="{width: '10%'}">
                    </colgroup>
                    <thead>
                    <tr>
                        <th>Picture</th>
                        <th>Rename</th>
                        <th>Notes</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="(image, idx) in tableRow['_images_for_'+tableHeader.field]">
                        <td>
                            <a target="_blank" :href="$root.fileUrl(image)">
                                <iframe v-if="image.special_mark === 'youtube'"
                                        :src="$root.fileUrl(image)"
                                        allow="accelerometer; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                        allowfullscreen
                                        width="100%"
                                        height="100%"
                                ></iframe>
                                <video v-else-if="image.is_video"
                                       controls
                                       class="_img_preview"
                                       :src="$root.fileUrl(image)"
                                       @click.prevent="imageClick([image], idx, tableHeader)"
                                ></video>
                                <audio v-else-if="image.is_audio"
                                       controls
                                       class="_img_preview"
                                       :src="$root.fileUrl(image)"
                                       @click.prevent="imageClick([image], idx, tableHeader)"
                                ></audio>
                                <img v-else
                                     class="_img_preview"
                                     :title="image.filename"
                                     :src="$root.fileUrl(image, 'md')"
                                     @click.prevent="imageClick([image], idx, tableHeader)"
                                >
                            </a>
                        </td>
                        <td>
                            <button class="btn btn-success btn-sm"
                                    :disabled="!canChangeFile(tableHeader)"
                                    @click="selRename(image, idx)"
                            >GO</button>
                        </td>
                        <td>
                            <textarea class="form-control"
                                      rows="3"
                                      :disabled="!canChangeFile(tableHeader)"
                                      @change="updateFile(image)"
                                      v-model="image.notes"
                            ></textarea>
                        </td>
                        <td>
                            <attachments-block-buttons
                                v-if="canChangeFile(tableHeader)"
                                :table-row="tableRow"
                                :table-header="tableHeader"
                                :special_params="special_params"
                                :file="image"
                                :idx="idx"
                                @changed-file="$emit('updated-cell')"
                            ></attachments-block-buttons>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <!--Files Tab-->
        <div class="flex__elem-remain popup-tab" v-show="activeAttachTab === 'files'" :style="tab_style">
            <div v-for="(tableHeader, index) in tableMeta._fields"
                 v-if="headerAvailToShow(tableHeader, '_files_for_')">
                <label>{{ getHeader(tableHeader, '_files_for_') }}</label>
                <attachments-block-buttons
                    v-if="canChangeFile(tableHeader)"
                    :all_type="'files'"
                    :table-row="tableRow"
                    :table-header="tableHeader"
                    :special_params="special_params"
                    style="float: right"
                    @changed-file="$emit('updated-cell')"
                ></attachments-block-buttons>
                <table class="table">
                    <colgroup>
                        <col :style="{width: '40%'}">
                        <col :style="{width: '10%'}">
                        <col :style="{width: '40%'}">
                        <col :style="{width: '10%'}">
                    </colgroup>
                    <thead>
                    <tr>
                        <th>File</th>
                        <th>Rename</th>
                        <th>Notes</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="(file, idx) in tableRow['_files_for_'+tableHeader.field]">
                        <td>
                            <a target="_blank" :href="$root.fileUrl(file)">
                                <audio v-if="file.is_audio"
                                       controls
                                       class="_img_preview"
                                       :src="$root.fileUrl(file)"
                                ></audio>
                                <span v-else>{{ file.filename }}</span>
                            </a>
                        </td>
                        <td>
                            <button class="btn btn-success btn-sm"
                                    :disabled="!canChangeFile(tableHeader)"
                                    @click="selRename(file, idx)"
                            >GO</button>
                        </td>
                        <td>
                            <textarea class="form-control"
                                      rows="3"
                                      :disabled="!canChangeFile(tableHeader)"
                                      @change="updateFile(file)"
                                      v-model="file.notes"
                            ></textarea>
                        </td>
                        <td>
                            <attachments-block-buttons
                                v-if="canChangeFile(tableHeader)"
                                :table-row="tableRow"
                                :table-header="tableHeader"
                                :special_params="special_params"
                                :file="file"
                                :idx="idx"
                                @changed-file="$emit('updated-cell')"
                            ></attachments-block-buttons>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <!--Uploads Tab-->
        <div class="flex__elem-remain popup-tab" v-show="activeAttachTab === 'uploads'" :style="tab_style">
            <template v-if="role === 'update' || role === 'add'">
                <div
                    class="container-fluid no-padding"
                    v-for="(tableHeader, index) in tableMeta._fields"
                    v-if="canChangeFile(tableHeader)"
                >
                    <label>Field: {{ getHeader(tableHeader) }}</label>
                    <div class="form-group upload-group">
                        <file-uploader-block
                                v-if="!recreate_dropzones"
                                :format="tableHeader.f_format"
                                :header-index="index"
                                :table_id="tableMeta.id"
                                :field_id="tableHeader.id"
                                :row_id="role === 'add' ? tableRow._temp_id : tableRow.id"
                                :special_params="local_spec_params"
                                :default_method="tableHeader.f_default"
                                @uploaded-file="insertedFile"
                        ></file-uploader-block>
                    </div>
                </div>
            </template>
            <div v-else="">Available after inserting row</div>
        </div>


        <!-- Full-size img for attachments if mouse over -->
        <full-size-img-block
                v-if="overImages && overImages.length"
                :table_meta="tableMeta"
                :table_header="overImgHeader"
                :table_row="tableRow"
                :file_arr="overImages"
                :file_idx="overImageIdx"
                :single_full_size_image="true"
                @close-full-img="overImages = null"
        ></full-size-img-block>


        <!-- Full-size img for attachments if mouse over -->
        <file-renamer-block
                v-if="fileRenamer"
                :init_name="fileRenamer.filename"
                @hide="fileRenamer = null"
                @f_rename="fRename"
        ></file-renamer-block>
    </div>
</template>

<script>
    import {SpecialFuncs} from '../../classes/SpecialFuncs';
    import {FileHelper} from "../../classes/helpers/FileHelper";

    import CanEditMixin from '../_Mixins/CanViewEditMixin';

    import FileUploaderBlock from './FileUploaderBlock';
    import FullSizeImgBlock from './FullSizeImgBlock';
    import FilesAttacher from "./FilesAttacher";
    import FileRenamerBlock from "./FileRenamerBlock";
    import AttachmentsBlockButtons from "./AttachmentsBlockButtons";

    export default {
        name: "AttachmentsBlock",
        mixins: [
            CanEditMixin,
        ],
        components: {
            AttachmentsBlockButtons,
            FileRenamerBlock,
            FilesAttacher,
            FileUploaderBlock,
            FullSizeImgBlock
        },
        data: function () {
            return {
                activeAttachTab: 'uploads',
                overImgHeader: null,
                overImages: null,
                overImageIdx: null,
                fileRenamer: null,
                fileRenamerType: null,
                recreate_dropzones: false,
            };
        },
        computed: {
            canWorkWithAttach() {
                let res = 0;
                _.each(this.tableMeta._fields, (hdr) => {
                    res += (this.canChangeFile(hdr) ? 1 : 0);
                });
                return res;
            },
            imgCount() {
                let res = 0;
                for(let key in this.tableRow) {
                    if (key && key.indexOf('_images_for_') > -1 && this.tableRow[key]) {
                        res += this.tableRow[key].length;
                    }
                }
                return res;
            },
            fileCount() {
                let res = 0;
                for(let key in this.tableRow) {
                    if (key && key.indexOf('_files_for_') > -1 && this.tableRow[key]) {
                        res += this.tableRow[key].length;
                    }
                }
                return res;
            },
            total_id() {
                return this.tableMeta.id + '_' + (this.tableRow ? this.tableRow.id : '');
            },
            local_spec_params() {
                return this.special_params || SpecialFuncs.specialParams();
            },
        },
        props:{
            tableMeta: Object,
            tableRow: Object|null,
            role: String,
            behavior: String,
            tab_style: Object,
            reqest_edit: Boolean,
            forbiddenColumns: Array,
            availableColumns: Array,
            special_params: Object,
            with_edit: {
                type: Boolean,
                default: true
            },
        },
        watch: {
            total_id(val) {
                this.recreate_dropzones = true;
                this.$nextTick(() => {
                    this.recreate_dropzones = false;
                });
            }
        },
        methods: {
            headerAvailToShow(tableHeader, file_or_image) {
                return tableHeader.f_type === 'Attachment'
                    && this.canViewHdr(tableHeader)
                    && this.tableRow[file_or_image + tableHeader.field]
                    && this.tableRow[file_or_image + tableHeader.field].length
            },

            canChangeFile(tableHeader) {
                return this.with_edit
                    && tableHeader.f_type === 'Attachment'
                    && (
                        (this.role === 'update' && this.canEditCell(tableHeader, this.tableRow))
                        ||
                        (this.role === 'add' && this.canAdd && this.canViewHdr(tableHeader))
                    )
                    &&
                    (this.behavior !== 'request_view' || this.reqest_edit); //if embed request -> can edit only newly added rows
            },
            getHeader(tableHeader, file_or_image) {
                let size = '';
                if (
                    file_or_image
                    && this.tableRow[file_or_image + tableHeader.field]
                    && this.tableRow[file_or_image + tableHeader.field].length
                ) {
                    let total = 0;
                    _.each(this.tableRow[file_or_image + tableHeader.field], (file) => {
                        total += file.filesize || 0;
                    });
                    total = total / (1024*1024);
                    size = ' ('+Number(total).toFixed(1)+' MB)';
                }
                let arr = tableHeader.name.split(',');
                return arr[ arr.length-1 ] + size;
            },
            //working with files
            insertedFile(idx, file) {
                let tableHeader = this.tableMeta._fields[idx];
                this.$root.attachFileToRow(this.tableRow, tableHeader, file);
                this.$emit('updated-cell');
            },
            updateFile(file) {
                axios.put(FileHelper.fileUrl(file), {
                    id: file.id,
                    table_id: file.table_id,
                    table_field_id: file.table_field_id,
                    filename: file.filename,
                    notes: file.notes,
                    special_params: this.local_spec_params,
                }).then(({ data }) => {
                    if (data.file) {
                        file.filename = data.file.filename;
                        this.$emit('updated-cell');
                    }
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                });
            },
            setFiles(field, files) {
                this.tableRow[field] = files;
            },
            selRename(file, idx) {
                this.fileRenamer = file;
            },
            fRename(new_name) {
                this.fileRenamer.filename = new_name;
                this.updateFile(this.fileRenamer);
                this.fileRenamer = null;
            },
            imageClick(images, idx, tableHeader) {
                this.overImages = images;
                this.overImageIdx = 0;
                this.overImgHeader = tableHeader;
            },
        },
        mounted() {
        }
    }
</script>

<style lang="scss" scoped>
    @import "../CustomPopup/CustomEditPopUp";

    .btn-danger, .btn-success {
        line-height: 20px;
        padding: 6px;
        margin: 2px;
    }
</style>