<script>
    export default {
        data() {
            return {
                inherited_tree: [],
            }
        },
        methods: {
            objChecked(obj1) {
                let stat = obj1.checked ? 0 : 1;
                obj1.checked = stat;
                _.each(obj1.children, (el2) => {
                    el2.checked = stat;
                    _.each(el2.children, (el3) => {
                        el3.checked = stat;
                    });
                });
            },
            loadInheritTree() {
                axios.get('/ajax/ref-condition/get-inherited-tree', {
                    params: {
                        table_id: this.master_table.id,
                    }
                }).then(({ data }) => {
                    this.inherited_tree = data;
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                });
            },
            noInheritanceIds() {
                let no_inheritance_ids = [];
                _.each(this.inherited_tree, (el1) => {
                    if (!el1.checked) {
                        no_inheritance_ids.push(el1.id);
                    }
                    _.each(el1.children, (el2) => {
                        if (!el2.checked) {
                            no_inheritance_ids.push(el2.id);
                        }
                        _.each(el2.children, (el3) => {
                            if (!el3.checked) {
                                no_inheritance_ids.push(el3.id);
                            }
                        });
                    });
                });
                return no_inheritance_ids;
            },
        },
    }
</script>