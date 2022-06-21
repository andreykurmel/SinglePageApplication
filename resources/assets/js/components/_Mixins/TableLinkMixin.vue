<script>
    export default {
        methods: {
            m_table_link(tableRow, link) {
                let lnk = '#';
                if (link._src_conditions && link._src_table_link) {
                    let queryParams = '';
                    _.each(link._src_conditions, (el) => {
                        queryParams += '&' + el.compared + '='
                            + (el.value || tableRow[el.field]);
                    });

                    if (this.$root.user && this.$root.user._is_folder_view) {
                        lnk = window.location.pathname + '?table_id=' + this.$root.tableMeta.id + queryParams;
                    } else {
                        lnk = link._src_table_link + '?' + queryParams.substr(1);
                    }
                }
                return lnk;
            },
        }
    }
</script>