<template>
    <div class="full-height">
        <div class="flex flex--center-v form-group">
            <label>Field:</label>
            <select-block
                :options="tFields()"
                :sel_value="select_field_id"
                class="sel-width"
                @option-select="(opt) => { select_field_id = opt.val }"
            ></select-block>
        </div>
        <div class="flex flex--center-v form-group">
            <label>Comparison:</label>
            <select-block
                :options="[
                    { val:'ddl:show_to_val', show:'Show To Value' },
                    { val:'ddl:val_to_show', show:'Value To Show' },
                    // { val:'show_show', show:'Show To Show' },
                    // { val:'val_val', show:'Value To Value' },
                ]"
                :sel_value="auto_comparison"
                class="sel-width"
                @option-select="(opt) => { auto_comparison = opt.val }"
            ></select-block>
        </div>
        <div class="flex flex--center-v form-group">
            <label>Apply to RowGroup:</label>
            <select-block
                :options="rGroups()"
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
                    :style="$root.themeButtonStyle"
                    @click="goUpload()"
            >Go</button>
        </div>
    </div>
</template>

<script>
    import {SpecialFuncs} from "../../classes/SpecialFuncs";

    import SelectBlock from "./SelectBlock";
    import ProgressBar from "../CustomCell/InCell/ProgressBar";

    export default {
        name: "AutoSelectBlock",
        mixins: [
        ],
        components: {
            ProgressBar,
            SelectBlock,
        },
        data: function () {
            return {
                select_field_id: null,
                auto_comparison: 'val_val',
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
            tFields() {
                return _.filter(this.tableMeta._fields, (fld) => {
                    return this.$root.systemFields.indexOf(fld.field) === -1
                        && SpecialFuncs.issel(fld.input_type)
                        && fld.ddl_id;
                }).map((fld) => {
                    return { val: fld.id, show: fld.name };
                });
            },
            rGroups() {
                let arr = _.map(this.tableMeta._row_groups, (gr) => {
                    return { val: gr.id, show: gr.name };
                });
                arr.unshift({val:null, show:''});
                return arr;
            },
            goUpload() {
                $.LoadingOverlay('show');
                axios.post('/ajax/table-data/batch-autoselect', {
                    select_field_id: this.select_field_id,
                    auto_comparison: this.auto_comparison,
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
</style>