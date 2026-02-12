import {SpecialFuncs} from "./SpecialFuncs";

export class VirtualScrollFns {

    /**
     *
     * @param tableMeta
     * @param tableRows
     * @param widths
     * @param behavior
     * @param availableColumns
     * @param forbiddenColumns
     */
    constructor(tableMeta, tableRows, widths, behavior, availableColumns, forbiddenColumns) {
        this.tableMeta = tableMeta;
        this.showMetaFields = this.fillShowFields(availableColumns, forbiddenColumns);

        this.hasFloating = !!_.find(this.showMetaFields, {is_floating: 1});
        this.lastFloating = _.findLastIndex(this.showMetaFields, {is_floating: 1});

        this.metaFieldsLefts = this.fillMetaWidths(widths);
        this.virtualRowsHtml = this.fillVirtualRowsHtml(tableRows, behavior);

        this.widthsStyle = {
            '-2': {
                width: (widths.index_col + 1) + 'px',
                position: this.hasFloating ? 'sticky' : 'relative',
                zIndex: 100,
                left: this.hasFloating ? this.metaFieldsLefts[-2]+'px' : null,
                borderRight: '1px solid #d3e0e9',
                backgroundColor: '#fff',
            },
            '-1': {
                width: widths.favorite_col + 'px',
                position: this.hasFloating ? 'sticky' : 'relative',
                zIndex: 100,
                left: this.hasFloating ? this.metaFieldsLefts[-1]+'px' : null,
                borderRight: '1px solid #d3e0e9',
                backgroundColor: '#fff',
            },
        };
    }

    /**
     *
     */
    fillShowFields(availableColumns, forbiddenColumns) {
        let fields = _.filter(this.tableMeta._fields, (hdr) => {
            return !hdr._permis_hidden
                && hdr.is_showed
                && (!forbiddenColumns || forbiddenColumns.indexOf(hdr.field) === -1)
                && (!availableColumns || availableColumns.indexOf(hdr.field) > -1);
        });
        return _.orderBy(fields, ['is_floating'], ['desc']);
    }

    /**
     *
     * @param widths
     * @returns {{"-2": number, "-1": (number|*), "0": *}}
     */
    fillMetaWidths(widths) {
        let idxW = widths.index_col + 1;
        let lefts = {
            '-2': 0,
            '-1': idxW,
            '0': idxW + widths.favorite_col,
        };

        _.each(lefts, (fld, i) => {
            lefts[i+1] = vueRootApp.getFloat(fld.width) + (lefts[i] || 0);
        });

        return lefts;
    }

    /**
     *
     * @param tableRows
     * @param behavior
     * @returns {*[]}
     */
    fillVirtualRowsHtml(tableRows, behavior) {
        let result = [];
        _.each(tableRows || [], (row) => {
            let rr = {
                _id: row.id,
                _tbRow: row,
                _presentStyle: this._presentRowFormat(row, behavior),
            };
            _.each(this.showMetaFields, (header) => {
                rr[header.field] = {
                    value: SpecialFuncs.showFullHtml(header, row, this.tableMeta, true),
                    style: this._cellFormats(row, header),
                };
            });
            result.push(rr);
        });
        return result;
    }

    /**
     *
     * @param tableRow
     * @param tableHeader
     * @returns {{}|{width: string, height: string, backgroundColor: string, textAlign: (string), freezed_by_format: boolean, hidden_by_format: boolean, color: null, fontStyle: null, fontWeight: null, textDecoration: null}}
     */
    _cellFormats(tableRow, tableHeader) {
        if (!tableHeader) {
            return {};
        }

        let res = {
            width: (parseFloat(tableHeader.width) || 100) + 'px',
            height: '100%',
            backgroundColor: 'inherit',
            borderRight: '1px solid #d3e0e9',
            textAlign: (tableHeader.f_type === 'Boolean' ? 'center' : ''),
            freezed_by_format: false,
            hidden_by_format: false,
            color: vueRootApp.themeTextFontColor,
            lineHeight: vueRootApp.themeTextLineHeigh+'px',
            fontSize: vueRootApp.themeTextFontSize + 'px',
            fontFamily: vueRootApp.themeTextFontFamily,
            fontStyle: null,
            fontWeight: null,
            textDecoration: null,
            justifyContent: tableHeader.col_align || 'center',
        };

        //just for adding watcher by Vue and update style after RowUpdate.
        if (tableRow.row_hash) {
            res.color = null;
        }

        if (this.tableMeta) {
            let condFormats = this.tableMeta._cond_formats || [];
            for (let i = condFormats.length-1; i >= 0; i--) {
                let format = condFormats[i] || {};

                //check that Format is applied to this Cell
                if (
                    format.status == 1 //check that Format is Active
                    &&
                    this._testRow(tableRow, format.id) //check saved result that current row is active for format
                    &&
                    (!format.table_column_group_id || this._testColumn(tableHeader, format.table_column_group_id)) //check column
                ) {
                    res = this._applyFormatToRes(format, res);
                }
            }
        }

        res.backgroundColor = res.hidden_by_format ? '#CCC' : res.backgroundColor;
        res.color = res.color || vueRootApp.themeTextFontColor;

        if (tableHeader.f_type === 'Color' && tableRow[tableHeader.field]) {
            res.padding = '0';
        }

        if (tableHeader.input_type === 'Mirror') {
            res.lineHeight = (parseInt(res.fontSize) + 4) + 'px';
        }

        let hdrIndex = _.findIndex(this.showMetaFields, {id: Number(tableHeader.id)});
        let is_flo = tableHeader ? tableHeader.is_floating : this.hasFloating;
        if (is_flo) {
            res.position = 'sticky';
            res.zIndex = 100;
            res.left = this.metaFieldsLefts[hdrIndex]+'px';
            res.borderRight = hdrIndex === this.lastFloating ? '1px solid #222' : '1px solid #d3e0e9';
            res.backgroundColor = res.backgroundColor === 'inherit' ? 'white' : res.backgroundColor;
        }

        return res;
    }

    /**
     *
     * @param tableRow
     * @param behavior
     * @returns {{}}
     * @private
     */
    _presentRowFormat(tableRow, behavior) {
        let res = {
            backgroundColor: '#FFF',
        };

        //stim preselect
        let stim = _.find(vueRootApp.tablda_highlights, (el) => {
            return this.tableMeta.id == el.table_id && tableRow.id == el.row_id;
        });
        if (stim) {
            res.border = '2px solid #44F';
        }

        //ListView is new
        if (tableRow._is_new && $.inArray(behavior, ['list_view', 'request_view']) > -1) {
            res.backgroundColor = '#DFD';
        }
        else //CondFormats
        if (this.tableMeta._cond_formats && this.tableMeta._cond_formats.length) {

            _.each(this.tableMeta._cond_formats, (format) => {
                if (
                    format.status == 1 //check that Format is Active
                    &&
                    this._testRow(tableRow, format.id) //check saved result that current row is active for format
                    &&
                    !format.table_column_group_id
                ) {
                    res = this._applyFormatToRes(format, res);
                }
            });
            res.backgroundColor = (res.hidden_by_format ? '#CCC' : res.backgroundColor);

        }
        return res;
    }

    /**
     *
     * @param format
     * @param res
     * @returns {*}
     * @private
     */
    _applyFormatToRes(format, res) {
        res.backgroundColor = format.bkgd_color || res.backgroundColor || null;
        res.color = format.color || res.color || null;
        if (format.font_size) {
            res.fontSize = format.font_size + 'px';
            res.lineHeight = (Number(format.font_size) + 2) + 'px';
        }
        let fonts = SpecialFuncs.parseMsel(format.font);
        _.each(fonts, (f) => {
            (f === 'Italic' ? res.fontStyle = 'italic' : res.fontStyle = res.fontStyle || null);
            (f === 'Bold' ? res.fontWeight = 'bold' : res.fontWeight = res.fontWeight || null);
            (f === 'Strikethrough' ? res.textDecoration = 'line-through' : res.textDecoration = res.textDecoration || null);
            (f === 'Overline' ? res.textDecoration = 'overline' : res.textDecoration = res.textDecoration || null);
            (f === 'Underline' ? res.textDecoration = 'underline' : res.textDecoration = res.textDecoration || null);
        });
        res.freezed_by_format = (res.freezed_by_format || format.activity === 'Freezed');
        res.hidden_by_format = !format.show_table_data;
        return res;
    }

    /**
     *
     * @param tableRow
     * @param format_id
     * @returns {boolean}
     * @private
     */
    _testRow(tableRow, format_id) {
        return tableRow._applied_cond_formats
            && tableRow._applied_cond_formats.indexOf(format_id) > -1;
    }

    /**
     *
     * @param tableHeader
     * @param table_column_group_id
     * @returns {boolean}
     * @private
     */
    _testColumn(tableHeader, table_column_group_id) {
        let bool = true;
        let meta = this.tableMeta;
        let column_group = _.find(meta._column_groups, {id: Number(table_column_group_id)})
            || _.find(meta._gen_col_groups, {id: Number(table_column_group_id)});

        if (column_group) {
            bool = _.findIndex(column_group._fields, {id: Number(tableHeader.id)}) > -1;
        }
        return bool;
    }
}