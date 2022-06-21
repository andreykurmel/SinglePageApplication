<script>
    /* this.$root.tableMeta: Object - should be present; this.settingsMeta: Object - should be present */
    export default {
        computed: {
            sl_metaTable() {
                if (this.linkTableMeta !== undefined) {
                    return this.linkTableMeta;
                } else if (this.globalMeta !== undefined) {
                    return this.globalMeta;
                } else {
                    return this.tableMeta;
                }
            },
        },
        methods: {
            availableTable(link) {
                if (link.always_available) {
                    return true;
                }

                let res = false;
                if (link.table_ref_condition_id) {
                    let i = _.findIndex(this.sl_metaTable._ref_conditions, {id: Number(link.table_ref_condition_id)});
                    if (i > -1) {
                        let table_id = this.sl_metaTable._ref_conditions[i].ref_table_id;
                        res = !!(_.find(this.$root.settingsMeta.available_tables, {id: Number(table_id)}));
                    }
                }
                return res;
            },
            showLink(link, val) {
                let avail = $.inArray(val, ['Record']) > -1 ? this.availableTable(link) : true;
                return link.link_type === val && (avail || this.$root.user._is_folder_view);
            },
        }
    }
</script>