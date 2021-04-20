<template>
    <table :style="tbStyle">
        <tr>
            <td :is="td_component"
                :global-meta="tableMeta"
                :table-meta="tableMeta"
                :settings-meta="$root.settingsMeta"
                :table-row="tableRow"
                :table-header="tableHeader"
                :cell-value="tdValue"
                :user="$root.user"
                :cell-height="$root.cellHeight"
                :max-cell-rows="$root.maxCellRows"
                :row-index="-1"
                :table_id="tableMeta.id"
                :behavior="'list_view'"
                :is_td_single="true"
                :is-add-row="true"
                :no_width="no_width"
                :with_edit="with_edit"
                :force_edit="force_edit"
                :extra-style="tbStyle"
                :class="tableHeader.f_type !== 'Boolean' ? 'edit-cell' : ''"
                @updated-cell="updatedCell"
            ></td>
        </tr>
    </table>
</template>

<script>
    import CustomCellTableData from '../CustomCell/CustomCellTableData.vue';
    import CustomCellSystemTableData from '../CustomCell/CustomCellSystemTableData.vue';
    import CustomCellCorrespTableData from '../CustomCell/CustomCellCorrespTableData.vue';

    export default {
        name: "SingleTdField",
        mixins: [
        ],
        components: {
            CustomCellTableData,
            CustomCellSystemTableData,
            CustomCellCorrespTableData,
        },
        data: function () {
            return {
                info_row: {},
            }
        },
        props:{
            tableMeta: Object,
            tableHeader: Object,
            tdValue: String|Number,
            fixedWidth: Number,
            no_width: Boolean,
            with_edit: Boolean,
            force_edit: Boolean,
            extRow: Object,
        },
        watch: {
            tdValue: {
                handler(val) {
                    if (val) {
                        if (this.extRow) {
                            this.info_row = _.cloneDeep(this.extRow);
                        } else {
                            axios.post('/ajax/table-data/info-row', {
                                table_id: this.tableMeta.id,
                                table_row: this.tableRow,
                            }).then(({data}) => {
                                this.info_row = data.row || {};
                            });
                        }
                    } else {
                        this.info_row = {};
                    }
                },
                immediate: true,
            }
        },
        computed: {
            td_component() {
                return this.$root.tdCellComponent(this.tableMeta.is_system);
            },
            tableRow() {
                let obj = {};
                obj[this.tableHeader.field] = this.tdValue;
                return _.merge(this.info_row, obj);
            },
            tbStyle() {
                return this.fixedWidth
                    ? {
                        tableLayout: 'fixed',
                        width: this.fixedWidth+'px',
                    }
                    : null;
            },
        },
        methods: {
            updatedCell(tableRow) {
                this.user_field = null;
                this.$emit('updated-td-val', tableRow[this.tableHeader.field]);
            },
        },
        created() {
        },
        beforeDestroy() {
        }
    }
</script>

<style scoped>
</style>