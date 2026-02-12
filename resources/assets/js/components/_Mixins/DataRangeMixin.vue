<script>
    /**
     *
     *  */
    import {eventBus} from '../../app';

    export default {
        methods: {
            getRGr(tableMeta, noPages) {
                let rrows = [];
                if (noPages) {
                    rrows.push({ val: '0', show: "All Table" });
                } else {
                    rrows.push({val: '-2', show: "Table"});
                    rrows.push({val: '-1', show: "GridView (All pages)"});
                    rrows.push({val: '0', show: "Current Page"});
                }
                rrows.push({ val: null, show: "Row Group:", disabled: true, isTitle: true });
                _.each(tableMeta._row_groups, (rg) => {
                    rrows.push( { val: 'rg:'+rg.id, show: rg.name, html: "&nbsp;&nbsp;&nbsp;"+rg.name } );
                });
                rrows.push({ val: null, show: "Filter Combo:", disabled: true, isTitle: true });
                _.each(tableMeta._saved_filters, (sf) => {
                    rrows.push( { val: 'sf:'+sf.id, show: sf.name, html: "&nbsp;&nbsp;&nbsp;"+sf.name } );
                });
                return rrows;
            },
            showRG(id, tableMeta) {
                id = isNaN(id) ? _.last(String(id).split(':')) : id;
                eventBus.$emit('show-grouping-settings-popup', tableMeta.db_name, 'row', id);
            },
            rgName(rgId, tableMeta, noPages) {
                let groups = this.getRGr(tableMeta, noPages);
                let rgObj = _.find(groups, {val: rgId});
                return rgObj ? rgObj.show : rgId;
            },
        },
    }
</script>