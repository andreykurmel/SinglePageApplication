<script>
    /**
     * Should be present:
     *  - getTableData() Function
     */
    export default {
        data: function () {
            return {
                sortApplied: true,
                sort: this.queryPreset
                    ? this.queryPreset.sort || []
                    : [],
            }
        },
        methods: {
            sortByFieldWrows(sort, tableHeader, $dir, $sub) {
                let max_idx = ($sub ? sort.length-1 : 0);
                let direction = $dir || (sort.length > 0 && sort[max_idx].direction === 'asc' ? 'desc' : 'asc');
                let sort_obj = {
                    field: tableHeader.field,
                    direction: direction
                };

                if ($sub) {
                    ($sub === 'add' ? sort.push(sort_obj) : sort[max_idx] = sort_obj);
                } else {
                    sort = [sort_obj];
                }
                return sort;
            },
            subSortByFieldWrows(sort, tableHeader, $dir) {
                if (sort.length > 0 && sort[sort.length-1].field === tableHeader.field) {
                    return this.sortByFieldWrows(sort, tableHeader, $dir, 'replace');
                } else {
                    return this.sortByFieldWrows(sort, tableHeader, $dir, 'add');
                }
            },

            sortByField(tableHeader, $dir, $sub) {
                this.sort = this.sortByFieldWrows(this.sort, tableHeader, $dir, $sub);
                this.getTableData('sort');
            },
            subSortByField(tableHeader, $dir) {
                if (this.sort.length > 0 && this.sort[this.sort.length-1].field === tableHeader.field) {
                    this.sortByField(tableHeader, $dir, 'replace');
                } else {
                    this.sortByField(tableHeader, $dir, 'add');
                }
            },
        }
    }
</script>