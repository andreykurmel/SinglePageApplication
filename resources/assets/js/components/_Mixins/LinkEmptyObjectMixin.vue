<script>
    /**
     *  should be present:
     *
     *  this.linkTableMeta: Object
     *  this.tableRow: Object
     *  */
    import {SpecialFuncs} from './../../classes/SpecialFuncs';

    export default {
        data: function () {
            return {
                objectForAdd: {},
            }
        },
        computed: {
            leo_metaTable() {
                if (this.linkTableMeta !== undefined) {
                    return this.linkTableMeta;
                } else
                if (this.tableMeta !== undefined) {
                    return this.tableMeta;
                } else {
                    return this.$root.tableMeta;
                }
            },
        },
        methods: {
            createObjectForAdd() {
                this.objectForAdd = SpecialFuncs.emptyRow(this.leo_metaTable);
            },
            getLinkParams(condition_items, linkSourceRow) {
                linkSourceRow = linkSourceRow || {};
                let link_params = {};
                _.each(condition_items, (cond) => {
                    if (cond.item_type === 'P2S' && cond._field && cond._compared_field) {
                        link_params[cond._compared_field.field] = linkSourceRow[cond._field.field];
                    }
                    if (cond.item_type === 'S2V' && cond.compared_value && cond._compared_field) {
                        switch (cond.compared_operator) {
                            case 'Include':
                            case '=': link_params[cond._compared_field.field] = cond.compared_value;
                                break;
                            case '>': link_params[cond._compared_field.field] = cond.compared_value+1;
                                break;
                            case '<': link_params[cond._compared_field.field] = cond.compared_value-1;
                                break;
                        }
                    }
                });
                return link_params;
            },
        },
    }
</script>