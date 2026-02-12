<template>
    <div class="full-height">
        <div class="flex flex--center-v form-group">
            <label>Field saving the URL of documents to be uploaded:</label>
            <select-block
                :options="tbFlds()"
                :sel_value="url_field_id"
                class="sel-width"
                @option-select="(opt) => { url_field_id = opt.val }"
            ></select-block>
        </div>
        <div class="flex flex--center-v form-group">
            <label>"Document" field where the documents to be uploaded to:</label>
            <select-block
                :options="attachFlds()"
                :sel_value="attach_field_id"
                class="sel-width"
                @option-select="(opt) => { attach_field_id = opt.val }"
            ></select-block>
        </div>
        <div class="flex flex--center-v form-group">
            <label>Apply to records in RowGroup:</label>
            <select-block
                :options="rowGroups()"
                :sel_value="row_group_id"
                class="sel-width"
                @option-select="(opt) => { row_group_id = opt.val }"
            ></select-block>
        </div>

        <div v-if="image_progress_id" class="percentage_bar">
            <progress-bar
                :pr_val="progress_complete"
                :ignore_format="true"
                :table_header="{}"
            ></progress-bar>
        </div>
        <div style="position: absolute; right: 5px; bottom: 5px;">
            <button class="btn btn-success pull-right"
                    :disabled="image_progress_id"
                    :style="$root.themeButtonStyle"
                    @click="goUpload()"
            >Upload</button>
        </div>
    </div>
</template>

<script>
    import SelectBlock from "./SelectBlock";
    import ProgressBar from "../CustomCell/InCell/ProgressBar";

    export default {
        name: "BatchUploadingBlock",
        mixins: [
        ],
        components: {
            ProgressBar,
            SelectBlock,
        },
        data: function () {
            return {
                url_field_id: null,
                attach_field_id: null,
                row_group_id: null,

                image_progress_id: null,
                progress_complete: 0,
            }
        },
        props:{
            tableMeta: Object,
        },
        computed: {
        },
        methods: {
            mapFields(avail) {
                return _.map(avail, (fld) => {
                    return { val: fld.id, show: fld.name };
                });
            },
            //
            tbFlds() {
                let avail = _.filter(this.tableMeta._fields, (fld) => {
                    return this.$root.systemFields.indexOf(fld.field) === -1;
                });
                return this.mapFields(avail);
            },
            attachFlds() {
                let avail = _.filter(this.tableMeta._fields, (fld) => {
                    return fld.f_type === 'Attachment';
                });
                return this.mapFields(avail);
            },
            //
            rowGroups() {
                return this.mapFields(this.tableMeta._row_groups);
            },
            goUpload() {
                $.LoadingOverlay('show');
                axios.post('/ajax/table-data/batch-uploading', {
                    url_field_id: this.url_field_id,
                    attach_field_id: this.attach_field_id,
                    row_group_id: this.row_group_id,
                }).then(({data}) => {
                    if (data.job_id) {
                        this.image_progress_id = data.job_id;
                    }
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => $.LoadingOverlay('hide'));
            },
        },
        mounted() {
            setInterval(() => {
                if (this.image_progress_id) {
                    axios.get('/ajax/import/status', {
                        params: {
                            import_jobs: [this.image_progress_id]
                        }
                    }).then(({ data }) => {
                        let frst = _.first(data);
                        this.progress_complete = parseFloat(frst ? frst.complete : 0);
                        if (frst.status === 'done') {
                            this.image_progress_id = false;
                        }
                    });
                }
            }, 1000);
        },
        beforeDestroy() {
        }
    }
</script>

<style lang="scss" scoped>
    label {
        margin: 0;
        display: block;
        width: 60%;
    }
    .sel-width {
        width: 60%;
    }
    .percentage_bar {
        width: 50%;
        position: absolute;
        left: 5px;
        bottom: 10px;
        height: 28px;
    }
</style>