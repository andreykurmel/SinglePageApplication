<template>
    <div :class="[tab_style ? '' : 'flex flex--col']" v-if="tableRow && tableMeta">
        <div class="popup-menu">
            <button class="btn btn-default" :class="{active: activeAttachTab === 'pictures'}" @click="activeAttachTab = 'pictures'">Pictures ({{ imgCount }})</button>
            <button class="btn btn-default" :class="{active: activeAttachTab === 'files'}" @click="activeAttachTab = 'files'">Files ({{ fileCount }})</button>
            <button v-if="canWorkWithAttach" class="btn btn-default" :class="{active: activeAttachTab === 'uploads'}" @click="activeAttachTab = 'uploads'">Upload New</button>
        </div>
        <!--Pictures Tab-->
        <div class="flex__elem-remain popup-tab" v-show="activeAttachTab === 'pictures'" :style="tab_style">
            <div v-for="(tableHeader, index) in tableMeta._fields"
                 v-if="headerAvailToShow(tableHeader, '_images_for_')">
                <label>{{ getHeader(tableHeader, '_images_for_') }}</label>
                <table class="table">
                    <colgroup>
                        <col width="40%">
                        <col width="10%">
                        <col width="40%">
                        <col width="10%">
                    </colgroup>
                    <thead>
                    <tr>
                        <th>Picture</th>
                        <th>Rename</th>
                        <th>Notes</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="(image, idx) in tableRow['_images_for_'+tableHeader.field]">
                        <td>
                            <a target="_blank" :href="$root.fileUrl(image)">
                                <img class="_img_preview"
                                     :src="$root.fileUrl(image)"
                                     @click.prevent="imageClick(tableRow['_images_for_'+tableHeader.field], idx)"
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
                            <input class="form-control"
                                   :disabled="!canChangeFile(tableHeader)"
                                   type="text"
                                   @change="updateFile(image)"
                                   v-model="image.notes"
                            />
                        </td>
                        <td>
                            <button class="btn btn-danger"
                                    v-if="canChangeFile(tableHeader)"
                                    @click="deleteFile('img', idx, tableHeader)"
                            >×</button>
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
                <table class="table">
                    <colgroup>
                        <col width="40%">
                        <col width="10%">
                        <col width="40%">
                        <col width="10%">
                    </colgroup>
                    <thead>
                    <tr>
                        <th>File</th>
                        <th>Rename</th>
                        <th>Notes</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="(file, idx) in tableRow['_files_for_'+tableHeader.field]">
                        <td>
                            <a target="_blank" :href="$root.fileUrl(file)">{{ file.filename }}</a>
                        </td>
                        <td>
                            <button class="btn btn-success btn-sm"
                                    :disabled="!canChangeFile(tableHeader)"
                                    @click="selRename(file, idx)"
                            >GO</button>
                        </td>
                        <td>
                            <input class="form-control"
                                   :disabled="!canChangeFile(tableHeader)"
                                   type="text"
                                   @change="updateFile(file)"
                                   v-model="file.notes"
                            />
                        </td>
                        <td>
                            <button class="btn btn-danger"
                                    v-if="canChangeFile(tableHeader)"
                                    @click="deleteFile('file', idx, tableHeader)"
                            >×</button>
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
                    <label>{{ getHeader(tableHeader) }}</label>
                    <div class="form-group upload-group">
                        <file-uploader-block
                                v-if="!recreate_dropzones"
                                :format="tableHeader.f_format"
                                :header-index="index"
                                :table_id="tableMeta.id"
                                :field_id="tableHeader.id"
                                :row_id="role === 'add' ? tableRow._temp_id : tableRow.id"
                                :special_params="local_spec_params"
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
                :file_arr="overImages"
                :file_idx="overImageIdx"
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

    import CanEditMixin from '../_Mixins/CanViewEditMixin';

    import FileUploaderBlock from './FileUploaderBlock';
    import FullSizeImgBlock from './FullSizeImgBlock';
    import FilesAttacher from "./FilesAttacher";
    import FileRenamerBlock from "./FileRenamerBlock";

    export default {
        name: "AttachmentsBlock",
        mixins: [
            CanEditMixin,
        ],
        components: {
            FileRenamerBlock,
            FilesAttacher,
            FileUploaderBlock,
            FullSizeImgBlock
        },
        data: function () {
            return {
                activeAttachTab: 'uploads',
                overImages: null,
                overImageIdx: null,
                fileRenamer: null,
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
                return this.tableMeta.id + '_' + this.tableRow.id;
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
                return tableHeader.f_type === 'Attachment'
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
            },
            updateFile(file) {
                axios.put('/ajax/files', {
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
                    Swal('', getErrors(errors));
                }).finally(() => {
                });
            },
            deleteFile(type, idx, tableHeader) {
                let key = (type === 'file' ? '_files_for_' : '_images_for_') + tableHeader.field;
                let file = this.tableRow[key][idx];
                $.LoadingOverlay('show');
                axios.delete('/ajax/files', {
                    params: {
                        id: file.id,
                        table_id: file.table_id,
                        table_field_id: file.table_field_id,
                        row_id: this.tableRow.id,
                        special_params: this.local_spec_params,
                    }
                }).then(({ data }) => {
                    this.tableRow[key].splice(idx, 1);
                    this.tableRow[tableHeader.field] = '';
                    this.$emit('updated-cell');
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
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
            imageClick(images, idx) {
                this.overImages = images;
                this.overImageIdx = idx;
            },
        },
        mounted() {
        }
    }
</script>

<style lang="scss" scoped>
    @import "../CustomPopup/CustomEditPopUp";
</style>