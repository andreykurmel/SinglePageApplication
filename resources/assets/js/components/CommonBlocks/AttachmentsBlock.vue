<template>
    <div :class="[tab_style ? '' : 'flex flex--col']" v-if="tableRow && tableMeta">
        <div class="popup-menu">
            <button class="btn btn-default" :class="{active: activeAttachTab === 'pictures'}" @click="activeAttachTab = 'pictures'">Pictures ({{ imgCount }})</button>
            <button class="btn btn-default" :class="{active: activeAttachTab === 'files'}" @click="activeAttachTab = 'files'">Files ({{ fileCount }})</button>
            <button v-if="canWorkWithAttach" class="btn btn-default" :class="{active: activeAttachTab === 'uploads'}" @click="activeAttachTab = 'uploads'">Upload New</button>
        </div>
        <!--Pictures Tab-->
        <div class="flex__elem-remain popup-tab" v-show="activeAttachTab === 'pictures'" :style="tab_style">
            <div v-for="(tableHeader, index) in tableMeta._fields" v-if="tableHeader.f_type === 'Attachment' && canViewHdr(tableHeader)">
                <label>{{ getHeader(tableHeader.name) }}</label>
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
                        <th>Actions</th>
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
                            <button class="btn btn-success btn-sm" @click="selRename(image, idx)">GO</button>
                        </td>
                        <td>
                            <input class="form-control"
                                   :disabled="!canChangeFile(tableHeader)"
                                   type="text"
                                   @change="updateFile(tableRow['_images_for_'+tableHeader.field], idx)"
                                   v-model="image.notes"
                            />
                        </td>
                        <td>
                            <button class="btn btn-danger"
                                    v-if="canChangeFile(tableHeader)"
                                    @click="deleteFile(tableRow['_images_for_'+tableHeader.field], idx, tableHeader)"
                            >×</button>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <!--Files Tab-->
        <div class="flex__elem-remain popup-tab" v-show="activeAttachTab === 'files'" :style="tab_style">
            <div v-for="(tableHeader, index) in tableMeta._fields" v-if="tableHeader.f_type === 'Attachment' && canViewHdr(tableHeader)">
                <label>{{ getHeader(tableHeader.name) }}</label>
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
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="(file, idx) in tableRow['_files_for_'+tableHeader.field]">
                        <td>
                            <a target="_blank" :href="$root.fileUrl(file)">{{ file.filename }}</a>
                        </td>
                        <td>
                            <button class="btn btn-success btn-sm" @click="selRename(file, idx)">GO</button>
                        </td>
                        <td>
                            <input class="form-control"
                                   :disabled="!canChangeFile(tableHeader)"
                                   type="text"
                                   @change="updateFile(tableRow['_files_for_'+tableHeader.field], idx)"
                                   v-model="file.notes"
                            />
                        </td>
                        <td>
                            <button class="btn btn-danger"
                                    v-if="canChangeFile(tableHeader)"
                                    @click="deleteFile(tableRow['_files_for_'+tableHeader.field], idx, tableHeader)"
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
                    <label>{{ getHeader(tableHeader.name) }}</label>
                    <div class="form-group upload-group">
                        <file-uploader-block
                                v-if="!recreate_dropzones"
                                :header-index="index"
                                :table_id="tableMeta.id"
                                :field_id="tableHeader.id"
                                :row_id="role === 'add' ? tableRow._temp_id : tableRow.id"
                                :special_params="special_params"
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
            }
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
            special_params: String|Object,
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
            canChangeFile(tableHeader) {
                return tableHeader.f_type === 'Attachment'
                    && (
                        (this.role === 'update' && this.canEditHdr(tableHeader))
                        ||
                        (this.role === 'add' && this.canAdd && this.canViewHdr(tableHeader))
                    )
                    &&
                    (this.behavior !== 'request_view' || this.reqest_edit); //if embed request -> can edit only newly added rows
            },
            getHeader(name) {
                let arr = name.split(',');
                return arr[ arr.length-1 ];
            },
            //working with files
            insertedFile(idx, file) {
                let tableHeader = this.tableMeta._fields[idx];
                let row_images = this.tableRow['_images_for_'+tableHeader.field];
                let row_files = this.tableRow['_files_for_'+tableHeader.field];

                //add uploaded file to the row
                if (file.is_img) {
                    (row_images ? row_images.push(file) : this.$set(this.tableRow, '_images_for_'+tableHeader.field, [file]));
                } else {
                    (row_files ? row_files.push(file) : this.$set(this.tableRow, '_files_for_'+tableHeader.field, [file]));
                }

                if (tableHeader) {
                    this.tableRow[tableHeader.field] = file.filepath + file.filename;
                }
            },
            updateFile(files, idx) {
                let file = files[idx];
                axios.put('/ajax/files', {
                    id: file.id,
                    table_id: file.table_id,
                    filename: file.filename,
                    notes: file.notes
                }).then(({ data }) => {
                    if (data.file) {
                        file.filename = data.file.filename;
                    }
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                });
            },
            deleteFile(files, idx, tableHeader) {
                let file = files[idx];
                $.LoadingOverlay('show');
                axios.delete('/ajax/files', {
                    params: {
                        id: file.id,
                        table_id: file.table_id,
                        table_field_id: file.table_field_id,
                        row_id: this.tableRow.id,
                    }
                }).then(({ data }) => {
                    files.splice(idx, 1);
                    this.tableRow[tableHeader.field] = '';
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
                this.updateFile([this.fileRenamer], 0);
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