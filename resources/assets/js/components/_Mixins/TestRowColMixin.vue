<script>
    /* this.$root.tableMeta: Object - must be present */
    /* this.behavior: Boolean - can be present */
    /* this.selectedRow: Object - can be present */
    /* this.conditionArray: Array - can be present */
    /* this.excluded_row_values: Array - can be present */
    export default {
        computed: {
            tr_metaTable() {
                if (this.linkTableMeta !== undefined) {
                    return this.linkTableMeta;
                } else
                if (this.globalMeta !== undefined) {
                    return this.globalMeta;
                } else {
                    return this.$root.tableMeta;
                }
            },
        },
        methods: {
            testRow(tableRow, table_row_group_id) {
                return tableRow._applied_cond_formats
                    && tableRow._applied_cond_formats.indexOf(table_row_group_id) > -1;
            },
            testColumn(tableHeader, table_column_group_id, trMeta) {
                let bool = true;
                let meta = trMeta || this.tr_metaTable;
                let column_group = _.find(meta._column_groups, {id: Number(table_column_group_id)})
                    || _.find(meta._gen_col_groups, {id: Number(table_column_group_id)});

                if (column_group) {
                    bool = _.findIndex(column_group._fields, {id: Number(tableHeader.id)}) > -1;
                }
                return bool;
            },
            //row showing methods
            getPresentRowStyle(tableRow, index, tableMeta) {
                let res = {};
                //stim preselect
                let stim = _.find(this.$root.tablda_highlights, (el) => {
                    return tableMeta.id == el.table_id && tableRow.id == el.row_id;
                });
                if (stim) {
                    res.border = '2px solid #44F';
                }
                //missed call in Twilio
                if (this.behavior === 'tw_history_call' && tableRow.missed) {
                    res.backgroundColor = '#FAA';
                }
                else //data set select
                if (this.selectedRow === index || this.behavior === 'data_sets_columns') {
                    let present = !this.conditionArray || _.findIndex(this.conditionArray, {id: Number(tableRow.id)}) > -1;
                    res.backgroundColor = present ? '#FFD' : '';
                }
                else //ListView is new
                if (tableRow._is_new && this.inArray(this.behavior, ['list_view', 'request_view'])) {
                    res.backgroundColor = '#DFD';
                }
                else //CondFormats
                if (tableMeta._cond_formats && tableMeta._cond_formats.length) {

                    _.each(tableMeta._cond_formats, (format) => {
                        if (
                            format.status == 1 //check that Format is Active
                            &&
                            this.testRow(tableRow, format.id) //check saved result that current row is active for format
                            &&
                            !format.table_column_group_id
                        ) {
                            res.backgroundColor = format.bkgd_color || null;
                            res.color = format.color || null;
                            if (format.font_size) {
                                res.fontSize = format.font_size + 'px';
                                res.lineHeight = (Number(format.font_size) + 2) + 'px';
                            }
                            let fonts = this.$root.parseMsel(format.font);
                            _.each(fonts, (f) => {
                                (f === 'Italic' ? res.fontStyle = 'italic' : res.fontStyle = res.fontStyle || null);
                                (f === 'Bold' ? res.fontWeight = 'bold' : res.fontWeight = res.fontWeight || null);
                                (f === 'Strikethrough' ? res.textDecoration = 'line-through' : res.textDecoration = res.textDecoration || null);
                                (f === 'Overline' ? res.textDecoration = 'overline' : res.textDecoration = res.textDecoration || null);
                                (f === 'Underline' ? res.textDecoration = 'underline' : res.textDecoration = res.textDecoration || null);
                            });
                            res.freezed_by_format = (res.freezed_by_format || format.activity === 'Freezed');
                            res.hidden_by_format = !format.show_table_data;
                        }
                    });
                    res.backgroundColor = (res.hidden_by_format ? '#CCC' : res.backgroundColor);

                }
                return res;
            },
            rowExcludedByValue(row) {
                let res = false;

                if (this.excluded_row_values && this.excluded_row_values.length) {//All should match
                    res = true;
                    _.each(this.excluded_row_values, (values_object) => {
                        let fld = values_object.field;
                        res = res && _.indexOf(values_object.excluded, row[fld]) > -1;
                    });
                }

                //exclude global hidden Columns from showing them in 'settings display'
                if (this.behavior === 'settings_display' && row._permis_hidden) {
                    res = true;
                }

                return res;
            },
        }
    }
</script>