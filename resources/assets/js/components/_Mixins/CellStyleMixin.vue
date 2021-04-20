<script>
    /**
     *  should be present:
     *
     *  this.tableHeader: Object
     *  this.tableRow: Object
     *  this.cellHeight: Number
     *
     *  also are needed:
     *
     *  this.isVertTable: Boolean
     *  this.isSelected: Boolean
     *  this.maxCellRows: Number
     *  */
    import TestRowColMixin from './TestRowColMixin.vue';

    export default {
        mixins: [
            TestRowColMixin,
        ],
        data: function () {
            return {
                max_he_for_auto_rows: 20,
                cell_top_padding: 4,
                freezed_by_format: false,
                hidden_by_format: false,
            }
        },
        computed: {
            //mains
            lineHeight() {
                return this.$root.themeTextFontSize + 2;
            },
            curRowHeight() {
                if (this.isVertTable && this.tableHeader) {
                    return Number(this.tableHeader.verttb_cell_height || this.cellHeight);
                } else {
                    return Number(this.cellHeight);
                }
            },
            maxCellRowCnt() {
                let max_cell_rows = Number(this.maxCellRows);
                if (!max_cell_rows) {
                    max_cell_rows = this.max_he_for_auto_rows;
                }
                return max_cell_rows;
            },
            tdCellHGT() { //Current Height of cell
                let currHe = this.curRowHeight;
                if (this.isVertTable && this.tableHeader) {
                    currHe = this.tableHeader.verttb_he_auto ? this.curRowHeight : this.tableHeader.verttb_row_height;
                }
                return (this.lineHeight * currHe) + this.cell_top_padding;
            },
            maxCellHGT() { //Max Height of cell
                let maxCell = this.maxCellRowCnt;
                if (this.isVertTable && this.tableHeader) {
                    maxCell = this.tableHeader.verttb_he_auto ? this.max_he_for_auto_rows : this.tableHeader.verttb_row_height;
                }
                return (this.lineHeight * maxCell)/* + this.cell_top_padding*/;
            },

            //cell style
            getCellStyle() {
                return this.getCellStyleMethod(this.tableRow, this.tableHeader);
            },

            //inner cell styles
            getTdWrappStyle() {
                if (!this.tableHeader) {
                    return {};
                }

                let bord = (this.isSelected ? '2px solid #8A8' : '2px solid rgba(0,0,0,0)');
                if (this.selectedCell && this.selectedCell.copy_mode) {
                    bord = (this.selectedCell.is_sel_copy(this.tableMeta, this.tableHeader, this.rowIndex) ? '2px dashed #8A8' : bord);
                }
                let styl = { border: bord };
                if (this.tableHeader.f_type === 'Attachment' && this.attach_overed) {
                    styl.border = '4px dashed #F77';
                }
                return styl;
            },

            getWrapperStyle() {
                if (this.reactive_provider && this.reactive_provider.no_height_limit) {
                    return {
                        maxHeight: 'auto',
                        overflow: 'hidden',//this.isVertTable ? 'auto' : 'hidden',
                    };
                } else {
                    return {
                        maxHeight: this.maxCellHGT ? this.maxCellHGT+'px' : null,
                        overflow: 'hidden',//this.isVertTable ? 'auto' : 'hidden',
                    };
                }
            },
            getInnerStyle() {
                if (!this.tableHeader) {
                    return {};
                }

                return {
                    maxHeight: this.maxCellHGT ? this.maxCellHGT+'px' : null,
                    textAlign: this.no_align ? 'left' : this.tableHeader.col_align,
                };
            },

            getEditStyle() {
                let style = _.cloneDeep(this.textStyle);
                style.padding = this.cell_top_padding/4+'px';
                return style;
            },
            borderPaddings() {
                return {
                    border: '2px solid rgba(0,0,0,0)',
                };
            },
            textStyle() {
                return {
                    lineHeight: this.lineHeight+'px',
                    fontSize: this.$root.themeTextFontSize+'px',
                    color: this.$root.themeTextFontColor,
                };
            },
        },
        methods: {
            tdCellWidth(tableHeader) {
                if (this.no_width) {
                    return null;
                }
                let cellWidth = this.$root.getFloat(tableHeader.width);
                //limit min width
                if (tableHeader.min_width && Number(tableHeader.min_width) > cellWidth) {
                    cellWidth = Number(tableHeader.min_width);
                }
                //limit max width
                if (tableHeader.max_width && Number(tableHeader.max_width) < cellWidth) {
                    cellWidth = Number(tableHeader.max_width);
                }

                return cellWidth;
            },
            getCellStyleMethod(tableRow, tableHeader) {
                tableHeader = tableHeader || this.tableHeader;
                tableRow = tableRow || this.tableRow;
                if (!tableHeader) {
                    return {};
                }

                let width = this.extraStyle ? this.extraStyle.width : this.tdCellWidth(tableHeader)+'px';
                if (this.no_width) {
                    width = null;
                }
                let res = {
                    width: width,
                    height: this.tdCellHGT+'px',
                    backgroundColor: this.isVertTable ? '#FFF' : 'inherit',
                    textAlign: (tableHeader.f_type === 'Boolean' ? 'center' : ''),
                    freezed_by_format: false,
                    hidden_by_format: this.isVertTable ? !(tableHeader.is_default_show_in_popup) : false,
                    color: null,
                    fontStyle: null,
                    fontWeight: null,
                    textDecoration: null,
                };

                //just for adding watcher by Vue and update style after RowUpdate.
                if (tableRow.row_hash) {
                    res.color = null;
                }
                let isdeftable = this.reactive_provider && this.reactive_provider.is_def_fields;

                if (isdeftable) {
                    res.hidden_by_format = false;
                }
                else
                if (this.tableMeta) {
                    _.each(this.tableMeta._cond_formats, (format) => {

                        //check that Format is applied to this Cell
                        if (
                            format.status == 1 //check that Format is Active
                            &&
                            this.testRow(tableRow, format.id) //check saved result that current row is active for format
                            &&
                            (!format.table_column_group_id || this.testColumn(tableHeader, format.table_column_group_id)) //check column
                        ) {
                            res.backgroundColor = format.bkgd_color || res.backgroundColor;
                            res.color = format.color || res.color;
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
                            res.hidden_by_format = this.isVertTable ? !format.show_form_data : !format.show_table_data;
                        }
                    });
                }

                res.backgroundColor = (this.isSelected ? '#CFC' : (res.hidden_by_format ? '#CCC' : res.backgroundColor));
                res.color = res.color || this.$root.themeTextFontColor;

                if (tableHeader.f_type === 'Color' && tableRow[tableHeader.field]) {
                    res.padding = '0';
                }

                if (!res.fontSize || !res.lineHeight) {
                    res.lineHeight = this.lineHeight+'px';
                    res.fontSize = Number(this.$root.themeTextFontSize)+'px';
                }

                this.freezed_by_format = res.freezed_by_format;
                this.hidden_by_format = res.hidden_by_format;
                return res;
            },
        },
    }
</script>

<style lang="scss">
    .td-custom {
        //border: 1px solid #d3e0e9;
        height: 1px;
        padding: 0;
        position: relative;
        color: #222;

        .td-wrapper {
            height: 100%;
            box-sizing: border-box;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: inherit;

            .wrapper-inner {
                max-height: 100%;
                width: 100%;
                overflow: hidden;
                text-decoration: inherit;

                .inner-content {
                    text-decoration: inherit;
                    max-height: inherit;
                    /*padding: 3px 5px;*/

                    /*Horizontal scrollbar*/
                    ::-webkit-scrollbar {
                        height: 5px;
                    }
                    ::-webkit-scrollbar-track {
                        background: #f1f1f1;
                    }
                    ::-webkit-scrollbar-thumb {
                        background: #888;
                    }
                    ::-webkit-scrollbar-thumb:hover {
                        background: #555;
                    }
                }
            }
        }
    }
</style>