<template>

    <!--FOR SINGLE REMOTE FILE-->
    <div v-if="file && file.is_remote">
        <button class="btn btn-danger"
                :disabled="!canEditCloud(tableHeader)"
                :title="'Delete File From Cloud'"
                :style="{fontSize: '40px'}"
                @click="deleteFile(file, idx, tableHeader, 'cloud')"
        >
            <span>×</span>
        </button>
        <button class="btn btn-success"
                v-if="tableHeader.fetch_source_id"
                :disabled="true"
                :title="'Already uploaded to cloud'"
                :style="{fontSize: '18px'}"
        >
            <i class="fas fa-cloud-upload-alt"></i>
        </button>
        <button class="btn btn-danger"
                v-if="tableHeader.fetch_source_id"
                :title="'Remove from fetch'"
                :style="{fontSize: '22px'}"
                @click="deleteFile(file, idx, tableHeader)"
        >
            <i class="fas fa-sign-out-alt"></i>
        </button>
    </div>

    <!--FOR SINGLE SERVER FILE-->
    <div v-else-if="file && !file.is_remote">
        <button class="btn btn-danger"
                :title="'Delete File'"
                :style="{fontSize: '40px'}"
                @click="deleteFile(file, idx, tableHeader)"
        >
            <span>×</span>
        </button>
        <button class="btn btn-success"
                v-if="tableHeader.fetch_source_id"
                :disabled="!tableHeader.fetch_uploading || !canEditCloud(tableHeader)"
                :title="!tableHeader.fetch_uploading ? 'Fetch/Uploading option should be activated' : 'Upload to cloud'"
                :style="{fontSize: '18px'}"
                @click="moveFileToCloud(file, idx, tableHeader)"
        >
            <i class="fas fa-cloud-upload-alt"></i>
        </button>
        <button class="btn btn-danger"
                v-if="tableHeader.fetch_source_id"
                :disabled="true"
                :title="'Just for cloud files'"
                :style="{fontSize: '22px'}"
        >
            <i class="fas fa-sign-out-alt"></i>
        </button>
    </div>

    <!--FOR ALL FILES-->
    <div v-else>
        <button class="btn btn-danger"
                :title="'Delete All Files'"
                :style="{fontSize: '40px'}"
                @click="deleteAllFiles(tableHeader, 'cloud')"
        >
            <span>×</span>
        </button>
        <button class="btn btn-success"
                v-if="tableHeader.fetch_source_id"
                :disabled="!tableHeader.fetch_uploading"
                :title="!tableHeader.fetch_uploading ? 'Fetch/Uploading option should be activated' : 'Upload to cloud'"
                :style="{fontSize: '18px'}"
                @click="moveAllFilesToCloud(tableHeader)"
        >
            <i class="fas fa-cloud-upload-alt"></i>
        </button>
        <button class="btn btn-danger"
                v-if="tableHeader.fetch_source_id"
                :title="'Remove from fetch'"
                :style="{fontSize: '22px'}"
                @click="deleteAllFiles(tableHeader)"
        >
            <i class="fas fa-sign-out-alt"></i>
        </button>
    </div>

</template>

<script>
    import {FileHelper} from "../../classes/helpers/FileHelper";
    import {SpecialFuncs} from "../../classes/SpecialFuncs";

    export default {
        name: "AttachmentsBlockButtons",
        mixins: [
        ],
        components: {
        },
        data: function () {
            return {
            };
        },
        computed: {
            local_spec_params() {
                return this.special_params || SpecialFuncs.specialParams();
            },
        },
        props:{
            all_type: String,
            tableHeader: Object,
            tableRow: Object,
            special_params: Object,
            file: Object|null,
            idx: Number|null,
        },
        methods: {
            canEditCloud(tableHeader) {
                let can = false;
                _.each(this.tableRow['_images_for_'+tableHeader.field], (file) => {
                    can = file.can_upload || can;
                });
                _.each(this.tableRow['_files_for_'+tableHeader.field], (file) => {
                    can = file.can_upload || can;
                });
                return can;
            },
            //working with files
            deleteAllFiles(tableHeader, cloud) {
                $.LoadingOverlay('show');
                let key = this.all_type === 'images' ? '_images_for_' : '_files_for_';
                let promises = [];
                _.each(this.tableRow[key+tableHeader.field], (file) => {
                    let req = this.deleteRequest(file, cloud);
                    req.catch(errors => {
                        Swal('Info', getErrors(errors));
                    });
                    promises.push(req);
                });
                Promise.all(promises).then(() => {
                    this.tableRow[key+tableHeader.field] = [];
                    this.tableRow[tableHeader.field] = '';
                    this.$emit('changed-file');
                    $.LoadingOverlay('hide');
                });
            },
            deleteFile(file, idx, tableHeader, cloud) {
                $.LoadingOverlay('show');
                this.deleteRequest(file, cloud).then(({ data }) => {
                    this.tableRow[FileHelper.fileKey(file, tableHeader)].splice(idx, 1);
                    this.tableRow[tableHeader.field] = '';
                    this.$emit('changed-file');
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
            deleteRequest(file, cloud) {
                return axios.delete(FileHelper.fileUrl(file), {
                    params: {
                        id: file.id,
                        table_id: file.table_id,
                        table_field_id: file.table_field_id,
                        row_id: this.tableRow.id,
                        special_params: this.local_spec_params,
                        with_cloud: cloud,
                    }
                });
            },
            //moving to clouds
            moveAllFilesToCloud(tableHeader) {
                $.LoadingOverlay('show');
                let key = this.all_type === 'images' ? '_images_for_' : '_files_for_';
                let promises = [];
                _.each(this.tableRow[key+tableHeader.field], (file, idx) => {
                    if (!file.is_remote) {
                        let req = this.moveRequest(file);
                        req.then(({data}) => {
                            if (data.remote_file) {
                                this.tableRow[FileHelper.fileKey(file, tableHeader)].splice(idx, 1, data.remote_file);
                            }
                        }).catch(errors => {
                            Swal('Info', getErrors(errors));
                        });
                        promises.push(req);
                    }
                });
                if (promises.length) {
                    Promise.all(promises).then(() => {
                        this.$emit('changed-file');
                        $.LoadingOverlay('hide');
                    });
                } else {
                    $.LoadingOverlay('hide');
                }
            },
            moveFileToCloud(file, idx, tableHeader) {
                if (!file.is_remote) {
                    $.LoadingOverlay('show');
                    this.moveRequest(file).then(({data}) => {
                        if (data.remote_file) {
                            this.tableRow[FileHelper.fileKey(file, tableHeader)].splice(idx, 1, data.remote_file);
                            this.$emit('changed-file');
                        }
                    }).catch(errors => {
                        Swal('Info', getErrors(errors));
                    }).finally(() => {
                        $.LoadingOverlay('hide');
                    });
                }
            },
            moveRequest(file) {
                return axios.post(FileHelper.moveCloudUrl(), {
                    id: file.id,
                    table_id: file.table_id,
                    table_field_id: file.table_field_id,
                    special_params: this.local_spec_params,
                });
            },
        },
        mounted() {
        }
    }
</script>

<style lang="scss" scoped>
    .btn-danger, .btn-success {
        line-height: 20px;
        padding: 6px;
        margin: 2px;
    }
</style>