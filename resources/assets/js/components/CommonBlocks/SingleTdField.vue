<template>
    <table :style="tbStyle" v-if="tableMeta && tableHeader && tableRow">
        <tr>
            <td :is="td_component"
                :global-meta="tableMeta"
                :table-meta="tableMeta"
                :settings-meta="$root.settingsMeta"
                :table-row="tableRow"
                :table-header="tableHeader"
                :cell-value="tdValue"
                :user="$root.user"
                :cell-height="1"
                :max-cell-rows="0"
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
                @show-src-record="showSrcRecord"
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
                },
                immediate: true,
            }
        },
        computed: {
            td_component() {
                return this.$root.tdCellComponent(this.tableMeta.is_system);
            },
            tableRow() {
                this.info_row[this.tableHeader.field] = this.tdValue;
                return this.info_row;
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
            updatedCell(tableRow, header, ddl_option) {
                this.user_field = null;
                this.$emit('updated-td-val', tableRow[this.tableHeader.field], header, ddl_option);
            },
            showSrcRecord(lnk, header, tableRow) {
                this.$emit('show-src-record', lnk, header, tableRow);
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