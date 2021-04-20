<script>
    /* this.$root.tableMeta: Object - should be present */
    export default {
        computed: {
            tr_metaTable() {
                if (this.linkTableMeta !== undefined) {
                    return this.linkTableMeta;
                } else
                if (this.globalMeta !== undefined) {
                    return this.globalMeta;
                } else {
                    return this.$root.tableMeta;
                }
            },
        },
        methods: {
            testRow(tableRow, table_row_group_id) {
                return tableRow._applied_cond_formats
                    && tableRow._applied_cond_formats.indexOf(table_row_group_id) > -1;
            },
            testColumn(tableHeader, table_column_group_id, trMeta) {
                let bool = true;
                let meta = trMeta || this.tr_metaTable;
                let column_group = _.find(meta._column_groups, {id: Number(table_column_group_id)});
                if (column_group) {
                    bool = _.findIndex(column_group._fields, {id: tableHeader.id}) > -1;
                }
                return bool;
            },
        }
    }
</script>