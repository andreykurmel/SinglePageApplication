<script>
    /**
     * Should be present:
     *  - metaTable: MetaTabldaTable
     *  - allRows: MetaTabldaRows
     */
    export default {
        name: 'VerticalRowMixin',
        computed: {
            verticalRow() {
                let vRow = {};
                if (this.metaTable.is_loaded) {
                    vRow = this.allRows.master_row || this.metaTable.empty_row;
                }
                return vRow;
            },
        },
    }
</script>