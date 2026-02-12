<template>
    <div class="saver-position">
        <tab-bi-view
            v-if="rcMeta() && rcMeta().id"
            :table_id="rcMeta().id"
            :table-meta="rcMeta()"
            :rows_count="justToShowCharts"
            :request_params="rowParams()"
            :is-visible="true"
            :trigger-bi-saving="tblRow['id']"
            class="full-width"
            @set-chart-in-saving-process="handleSaving"
        ></tab-bi-view>
    </div>
</template>

<script>
    import {eventBus} from "../../../../../app";

    import {SpecialFuncs} from "../../../../../classes/SpecialFuncs";

    import TabBiView from "../TabBiView.vue";
    import BiChartElement from "../ChartAddon/BiChartElement.vue";

    export default {
        name: "ReportBiSaver",
        mixins: [
        ],
        components: {
            BiChartElement,
            TabBiView
        },
        data: function () {
            return {
                numberInSaving: 0,
                justToShowCharts: 1,
                link: {
                    'table_ref_condition_id': this.ref_cond_id,
                    'always_available': 0,
                },
                viewAuth: {
                    view_hash: this.$root.user.view_hash,
                    is_folder_view: this.$root.user._is_folder_view
                },
            }
        },
        props: {
            ref_cond_id: Number,
            ref_table_id: Number,
            tblRow: Object,
        },
        methods: {
            closeSaverOnNoAction() {
                window.setTimeout(() => {
                    this.handleSaving(0);//Close saver if no charts were found for saving
                }, 10000);
            },
            rcMeta() {
                return this.$root.reportBiSaverMetas[this.ref_table_id];
            },
            getHeaders() {
                if (this.rcMeta()) {
                    this.closeSaverOnNoAction();
                    return;
                }

                if (!this.ref_cond_id) {
                    this.$root.reportBiSaverMetas[this.ref_table_id] = this.$root.tableMeta;
                    this.closeSaverOnNoAction();
                } else {
                    this.$root.reportBiSaverMetas[this.ref_table_id] = { in_loading: 1 };
                    axios.post('/ajax/table-data/get-headers', {
                        ref_cond_id: this.ref_cond_id,
                        user_id: !this.$root.user.see_view ? this.$root.user.id : null,
                        special_params: this.viewAuth,
                    }).then(({data}) => {
                        this.$set(this.$root.reportBiSaverMetas, this.ref_table_id, data);
                        this.closeSaverOnNoAction();
                        eventBus.$emit('bi-savers-redraw');
                    }).catch(errors => {
                        Swal('Info', getErrors(errors));
                    });
                }
            },
            rowParams() {
                let params = {};
                if (this.ref_cond_id) {
                    params = SpecialFuncs.tableMetaRequest(null, this.ref_cond_id);
                    params.link = this.link;
                    params.table_row = this.tblRow;
                } else {
                    params = SpecialFuncs.tableMetaRequest(this.ref_table_id);
                }
                params.special_params = this.viewAuth;
                return params;
            },
            handleSaving(val) {
                this.numberInSaving += Number(val);
                if (!this.numberInSaving) {
                    this.$emit('charts-are-saved');
                }
            },
            handleRedraw() {
                this.$forceUpdate();
            },
        },
        mounted() {
            //set timeout needed to execute getHeaders one by one instead of parallel.
            window.setTimeout(() => {
                this.getHeaders();
            }, 1);

            eventBus.$on('bi-savers-redraw', this.handleRedraw);
        },
        beforeDestroy() {
            eventBus.$off('bi-savers-redraw', this.handleRedraw);
        }
    }
</script>

<style lang="scss" scoped>
    .saver-position {
        position: fixed;
        top: 100%;
        left: 0;
        right: 0;
        z-index: -100;
    }
</style>