<script>
    /**
     *  should be present:
     *
     *  */
    import IsShowFieldMixin from './IsShowFieldMixin.vue';
    import TestRowColMixin from './TestRowColMixin.vue';

    export default {
        mixins: [
            IsShowFieldMixin,
            TestRowColMixin
        ],
        data: function () {
            return {
            }
        },
        methods: {
            /**
             * return [header, header, header, ...]
             * @returns {Array}
             */
            sortAndFilterFields(tableMeta, fields, tableRow, ignore_format) {
                let flds = [];
                if (tableMeta.vert_tb_floating) {
                    //first 'is_floating'
                    _.each(fields, (fld) => {
                        (fld.is_floating ? flds.push(fld) : null);
                    });
                    //then rest of the Fields
                    _.each(fields, (fld) => {
                        (!fld.is_floating ? flds.push(fld) : null);
                    });
                } else {
                    flds = fields;
                }
                //filter 'only visible'
                flds = _.filter(flds, (header) => {
                    return this.canMainView(tableMeta, header, tableRow, ignore_format);
                });
                return flds;
            },
            //can view
            canMainView(tableMeta, header, tableRow, ignore_format) {
                return this.isShowField(header) && (ignore_format || this.hiddenByFormat(tableMeta, header, tableRow));
            },
            //hidden by CondFormat 'show in header'
            hiddenByFormat(tableMeta, tableHeader, tableRow) {
                let visible = !!(tableHeader.is_default_show_in_popup);
                _.each(tableMeta._cond_formats, (format) => {
                    //check that Format is applied to this Cell
                    if (
                        format.status == 1 //check that Format is Active
                        &&
                        tableRow && this.testRow(tableRow, format.id) //check saved result that current row is active for format
                        &&
                        (!format.table_column_group_id || this.testColumn(tableHeader, format.table_column_group_id, tableMeta)) //check column
                    ) {
                        visible = !!(format.show_form_data);
                    }
                });
                return visible;
            },
        },
    }
</script>