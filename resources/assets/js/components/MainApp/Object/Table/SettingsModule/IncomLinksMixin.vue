<script>
    /**
     * Needed:
     * this.tableMeta - Object
     * this.filter_id - Number
     */
    export default {
        data: function () {
            return {
                availableRcIncom: ['incoming_allow', 'user_id', 'table_name', 'ref_cond_name', 'use_category', 'use_name', 'rc_inheriting'],
                availableLinkIncom: ['incoming_allow', 'user_id', 'table_name', 'ref_cond_name', 'use_category', 'use_name'],
            };
        },
        computed: {
        },
        methods: {
            incomLinks() {
                return Number(this.filter_id) > 0
                    ? _.filter(this.tableMeta.__incoming_links, {id: Number(this.filter_id)})
                    : this.tableMeta.__incoming_links;
            },
            clearIncom() {
                this.tableMeta.__incoming_links = null;
            },
            loadIncomings() {
                if (!this.tableMeta.__incoming_links) {
                    axios.get('/ajax/ref-condition/get-incoming', {
                        params: {
                            table_id: this.tableMeta.id,
                        }
                    }).then(({data}) => {
                        this.tableMeta.__incoming_links = data;
                        this.$forceUpdate();
                    }).catch(errors => {
                        Swal('Info', getErrors(errors));
                    });
                }
            },
            updateIncomLink(tableRow) {
                this.$root.sm_msg_type = 1;
                axios.post('/ajax/ref-condition/incom', {
                    table_ref_condition_id: tableRow.id,
                    incoming_allow: tableRow.incoming_allow,
                }).then(({ data }) => {
                    this.tableMeta.__incoming_links = data.__incoming_links;
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
        },
    }
</script>