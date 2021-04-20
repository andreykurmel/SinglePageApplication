<script>
    /**
     * Should be present:
     *  - showField() Function
     *  - updateValue() Function
     *  - tableRow - Object
     *  - tableHeader - Object
     *  - is_visible - Boolean
     */
    export default {
        data: function () {
            return {
                cont_height: 0,
                cont_width: 0,
                cont_html: null,
                uuid: uuidv4(),
            }
        },
        watch: {
            'tableHeader.width': function (val) {
                this.$nextTick(() => {
                    this.changedContSize();
                });
            },
            is_visible: {
                handler(val) {
                    this.$nextTick(() => {
                        this.changedContSize();
                    });
                },
                immediate: true,
            },
        },
        methods: {
            //Expand icon
            changedContSize() {
                if (this.$refs.sett_content_elem) {
                    this.cont_height = Math.floor($(this.$refs.sett_content_elem).height());
                    this.cont_width = Math.floor($(this.$refs.sett_content_elem).width());
                    this.cont_html = String(this.showField());
                }
            },
            tableDataStringUpdateHandler(uniq_id, val) {
                if (uniq_id === this.getuniqid()) {
                    this.tableRow[this.tableHeader.field] = val;
                    this.updateValue();
                }
            },
            getuniqid() {
                return this.uuid;
            },
        }
    }
</script>