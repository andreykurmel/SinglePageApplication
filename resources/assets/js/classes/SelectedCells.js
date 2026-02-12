
import {SpecialFuncs} from './SpecialFuncs';
import {UnitConversion} from './UnitConversion';
import {VerticalTableFldObject} from "../components/CustomTable/VerticalTableFldObject";

export class SelectedCells {

    /**
     *
     */
    constructor(disabled) {
        this.uid = window.uuidv4();
        this.disabled = !!disabled;
        this.in_editing = false;
        this.clear();

        if (!window._all_sel_cells) window._all_sel_cells = [];
        window._all_sel_cells.push(this);
    }

    // FIELDS
    /**
     *
     */
    clear() {
        this.row = -1; //Number
        this.col = null; //String
        this.clear_copy();
        this.clear_square();
    }

    /**
     *
     */
    clear_copy() {
        this.copy_row = -1; //Number
        this.copy_col = null; //String
        this.copy_square_row = -1; //Number
        this.copy_square_col = null; //String
        this.copy_mode = false; //Boolean
    }

    /**
     *
     */
    clear_square() {
        this.square_row = -1; //Number
        this.square_col = null; //String
    }

    /**
     *
     */
    clear_the_others() {
        _.each(window._all_sel_cells, (cell) => {
            if (cell.uid !== this.uid) {
                cell.clear();
            }
        });
    }
    // FIELDS


    // SELECTS
    /**
     *
     * @param fld
     * @param row_idx
     */
    single_select(fld, row_idx) {
        this.row = row_idx;
        this.col = fld.field;
        this.clear_square();
        this.clear_the_others();
    }

    /**
     *
     * @param fld
     * @param row_idx
     */
    square_select(fld, row_idx) {
        if ((this.col !== fld.field) || (this.row !== row_idx)) {
            this.square_row = row_idx;
            this.square_col = fld.field;
        } else {
            this.clear_square();
        }
        this.clear_the_others();
    }

    /**
     *
     * @param tableMeta
     * @param fld
     * @param row_idx
     * @returns {*|boolean}
     */
    is_selected(tableMeta, fld, row_idx) {
        return this.disabled ? false : this.check_sel('', tableMeta, fld, row_idx);
    }

    /**
     *
     * @param tableMeta
     * @param fld
     * @param row_idx
     * @returns {*}
     */
    is_sel_copy(tableMeta, fld, row_idx) {
        if (!this.copy_mode) {
            return false;
        }
        return this.check_sel('copy_', tableMeta, fld, row_idx);
    }

    /**
     *
     * @param $pref
     * @param tableMeta
     * @param fld
     * @param row_idx
     * @returns {*|boolean}
     */
    check_sel($pref = '', tableMeta, fld, row_idx) {
        if (this[$pref+'square_row'] > -1 && this[$pref+'square_col']) {
            let fld_idx = _.findIndex(tableMeta._fields, {field: fld.field});
            let idxs = this.idxs(tableMeta, $pref);

            return fld
                && (idxs.col_start <= fld_idx && fld_idx <= idxs.col_end)
                && (row_idx === undefined || (idxs.row_start <= row_idx && row_idx <= idxs.row_end));
        } else {
            return fld
                && this[$pref+'col'] === fld.field
                && (row_idx === undefined || this[$pref+'row'] === row_idx);
        }
    }
    // SELECTS


    // SELECTED CHANGE
    /**
     *
     * @param tableMeta
     * @param $to_next
     * @param $except_attach
     */
    next_col(tableMeta, $to_next = true, $except_attach = false) {
        if (!tableMeta || !this.col) {
            return;
        }

        let init_field = this.col;
        let avail_fields = _.filter(tableMeta._fields, (fld) => {
            return !fld._permis_hidden
                && ($except_attach ? VerticalTableFldObject.fieldSetting('fld_popup_shown', fld) : fld.is_showed)
                && (!$except_attach || fld.f_type !== 'Attachment');
        });
        let idx = _.findIndex(avail_fields, {'field': init_field});
        //find next col
        if ($to_next) {
            for (let i = 0; i < avail_fields.length; i++) {
                if (
                    i > idx
                    && avail_fields[idx]
                    && avail_fields[i].is_floating == avail_fields[idx].is_floating
                ) {
                    this.col = avail_fields[i].field;
                    break;
                }
            }
            //if last col of 'floating table' -> go to fist col of 'table'
            if (
                avail_fields[idx]
                && avail_fields[idx].is_floating
                && init_field === this.col
            ) {
                for (let i = 0; i < avail_fields.length; i++) {
                    if (! avail_fields[i].is_floating) {
                        this.col = avail_fields[i].field;
                        break;
                    }
                }
            }
        }
        //find prev col
        else {
            for (let i = avail_fields.length-1; i >= 0; i--) {
                if (
                    i < idx
                    && avail_fields[idx]
                    && avail_fields[i].is_floating == avail_fields[idx].is_floating
                ) {
                    this.col = avail_fields[i].field;
                    break;
                }
            }
            //if first col of 'table' -> go to last col of 'floating table'
            if (
                avail_fields[idx]
                && !avail_fields[idx].is_floating
                && init_field === this.col
            ) {
                for (let i = avail_fields.length-1; i >= 0; i--) {
                    if (avail_fields[i].is_floating) {
                        this.col = avail_fields[i].field;
                        break;
                    }
                }
            }
        }
        //this.clear_copy();
        this.clear_square();
    }

    /**
     *
     * @param tableMeta
     * @param old_header
     * @returns {boolean}
     */
    notFloatingOrNotFirst(tableMeta, old_header) {
        this.next_col(tableMeta, true);
        let next_col = _.find(tableMeta._fields, {
                field: this.get_col()
            }) || {};

        return !old_header.is_floating
            && next_col.field !== old_header.field;
    }

    /**
     *
     * @param $tableRows
     * @param $to_next
     */
    next_row($tableRows, $to_next = true) {
        if (!$tableRows || this.row === -1) {
            return;
        }

        if ($to_next) {
            if (this.row < ($tableRows.length-1)) {
                this.row++;
            }
        } else {
            if (this.row > 0) {
                this.row--;
            }
        }
        //this.clear_copy();
        this.clear_square();
    }
    // SELECTED CHANGE


    /**
     *
     * @param tableMeta
     * @param tableRows
     * @returns {Array}
     */
    delete_in_selected(tableMeta, tableRows) {
        let changed_rows = [];
        let idxs = this.idxs(tableMeta);
        for (let r = idxs.row_start; r <= idxs.row_end; r++) {
            for (let c = idxs.col_start; c <= idxs.col_end; c++) {
                let fld = tableMeta._fields[c];
                tableRows[r]._old_val = tableRows[r][fld.field];
                tableRows[r][fld.field] = null;
                tableRows[r]._changed_field = fld.field;
                if (fld.input_type === 'Formula') {
                    tableRows[r][fld.field+'_formula'] = null;
                }
                if (fld.input_type === 'Mirror') {
                    tableRows[r][fld.field+'_mirror'] = '';
                }
            }
            changed_rows.push(tableRows[r]);
        }
        return changed_rows;
    }


    // COPY FUNCTIONS
    /**
     *
     */
    start_copy(tableMeta, tableRows) {
        this.copy_row = this.row;
        this.copy_col = this.col;
        this.copy_square_row = this.square_row;
        this.copy_square_col = this.square_col;
        this.copy_mode = true;

        let clip = '';
        if (this.copy_square_row > -1 && this.copy_square_col) {
            let idxs = this.idxs(tableMeta, 'copy_');
            let r_i = idxs.row_start;
            while (r_i <= idxs.row_end) {
                if (tableRows[r_i]) {
                    let c_i = idxs.col_start;
                    while (c_i <= idxs.col_end) {
                        if (tableMeta._fields[c_i]) {
                            let val = tableRows[r_i][tableMeta._fields[c_i].field];
                            val = UnitConversion.doConv(tableMeta._fields[c_i], val);
                            val = SpecialFuncs.format(tableMeta._fields[c_i], val);
                            val = String(val).replace(/<[^>]*>/gi, ''); //remove tags
                            clip += String(val || '').replace(/\t\n/gi, '') + '\t';
                        }
                        c_i++;
                    }
                    clip += '\n';
                }
                r_i++;
            }
        } else {
            let fld_idx = _.findIndex(tableMeta._fields, {field: this.copy_col});
            let val = tableRows[this.copy_row] ? tableRows[this.copy_row][this.copy_col] : '';
            val = UnitConversion.doConv(tableMeta._fields[fld_idx], val);
            val = SpecialFuncs.format(tableMeta._fields[fld_idx], val);
            val = String(val).replace(/<[^>]*>/gi, ''); //remove tags
            clip = String(val || '').replace(/\t\n/gi, '');
        }
        SpecialFuncs.strToClipboard(clip);
    }

    /**
     *
     * @param tableRows
     * @returns {boolean}
     */
    fill_want_confirmation(tableRows) {
        if (this.copy_square_row > -1 && this.copy_square_col) { // copied a few cells
            return false;
        } else {
            return !tableRows[this.copy_row];
        }
    }

    /**
     *
     * @param tableMeta
     * @param tableRows
     * @param overwrite
     * @returns {Promise<unknown>}
     */
    fill_copy(tableMeta, tableRows, overwrite) {
        let updated_rows = [];
        let promis = null;

        if (this.copy_square_row > -1 && this.copy_square_col) { // copied a few cells
            let idxs = this.idxs(tableMeta, 'copy_');
            let r_i = idxs.row_start;
            let step = 0;
            
            while (r_i <= idxs.row_end) {

                let targrow = tableRows[this.row+step];
                let copyrow = tableRows[r_i];
                if (targrow && copyrow) {

                    let fld_idx = _.findIndex(tableMeta._fields, {field: this.col});
                    let c_i = idxs.col_start;
                    let ccl = 0;

                    while (c_i <= idxs.col_end) {
                        let t_header = tableMeta._fields[fld_idx+ccl];
                        let c_header = tableMeta._fields[c_i];
                        if (t_header && c_header) {
                            let val = UnitConversion.doConv(c_header, copyrow[c_header.field]);
                            this._copy_value(targrow, t_header, val, copyrow);
                        }
                        c_i++;
                        ccl++;
                    }

                    updated_rows.push(targrow);
                }
                
                r_i++;
                step++;
            }
            
        } else { // copied one cell

            let copyrow = tableRows[this.copy_row];
            if (copyrow) {
                if (this.square_row > -1 && this.square_col) {
                    let idxs = this.idxs(tableMeta, '');
                    let r_i = idxs.row_start;
                    while (r_i <= idxs.row_end) {
                        if (tableRows[r_i]) {
                            let c_i = idxs.col_start;
                            while (c_i <= idxs.col_end) {
                                if (tableMeta._fields[c_i]) {
                                    let t_header = tableMeta._fields[c_i];
                                    let c_header = _.find(tableMeta._fields, {field: this.copy_col});
                                    if (tableRows[r_i] && t_header && c_header) {
                                        let val = UnitConversion.doConv(c_header, copyrow[c_header.field]);
                                        this._copy_value(tableRows[r_i], t_header, val, copyrow);
                                    }
                                }
                                c_i++;
                            }
                            updated_rows.push(tableRows[r_i]);
                        }
                        r_i++;
                    }
                } else {
                    let targrow = tableRows[this.row];
                    let t_header = _.find(tableMeta._fields, {field: this.col});
                    let c_header = _.find(tableMeta._fields, {field: this.copy_col});
                    if (targrow && t_header && c_header) {
                        let val = UnitConversion.doConv(c_header, copyrow[c_header.field]);
                        this._copy_value(targrow, t_header, val, copyrow);
                        updated_rows.push(targrow);
                    }
                }
            } else {
                //Copied from Clipboard
                if (navigator.clipboard) {
                    promis = navigator.clipboard.readText().then((clipboard) => {
                        return this._fill_copy_from_clipboard(tableMeta, tableRows, overwrite, clipboard);
                    });
                } else {
                    SpecialFuncs.clipFillPaste();
                    promis = window.sleep(500, (resolve) => {
                        return this._fill_copy_from_clipboard(tableMeta, tableRows, overwrite, SpecialFuncs.clipboardGetStr());
                    });
                }
            }
            
        }
        
        this.clear_square();
        return promis || new Promise((resolve) => {
            resolve({
                updated_rows: updated_rows,
                new_rows: []
            })
        });
    }

    /**
     *
     * @param tableMeta
     * @param tableRows
     * @param overwrite
     * @param clipboard
     * @returns {{updated_rows: *[], new_rows: *[]}}
     * @private
     */
    _fill_copy_from_clipboard(tableMeta, tableRows, overwrite, clipboard) {
        let idxs = this.idxs(tableMeta, '');
        let new_rows = [];
        let u_rows = [];
        clipboard = clipboard.replace(/<tr\/?>/gi, '\n');
        clipboard = clipboard.replace(/<td\/?>/gi, '\t');
        clipboard = clipboard.replace(/\n+/gi, '\n');
        clipboard = clipboard.replace(/\t+/gi, '\t');
        let clip_rows = clipboard.split('\n') || [];
        clip_rows = _.map(clip_rows, (row) => _.trim(row));
        clip_rows = _.filter(clip_rows);

        let clip_one_value = null;
        if (clip_rows.length === 1 && clip_rows[0].split(/[\t]/i).length === 1) {
            clip_one_value = clip_rows[0];
        }

        if (idxs.row_start == idxs.row_end && idxs.col_start == idxs.col_end) {
            _.each(clip_rows, (row, i) => {
                let tablRow = tableRows[idxs.row_start + i];
                let usedRow = tablRow || SpecialFuncs.emptyRow(tableMeta);
                if (usedRow) {
                    _.each(row.split(/[\t]/i), (col, j) => {
                        let tablField = tableMeta._fields[idxs.col_start + j];
                        if (tablField && (overwrite || !usedRow[tablField.field])) {
                            this._copy_value(usedRow, tablField, col);
                        }
                    });
                    tablRow ? u_rows.push(usedRow) : new_rows.push(usedRow);
                }
            });
        } else {
            let r_i = idxs.row_start;
            while (r_i <= idxs.row_end) {
                let clipr = clip_one_value || clip_rows[r_i - idxs.row_start];
                let tablRow = tableRows[r_i];
                let usedRow = tablRow || SpecialFuncs.emptyRow(tableMeta);
                if (usedRow && clipr) {
                    clipr = clipr.split(/[\t]/i);
                    let clipr_i = 0;
                    let c_i = idxs.col_start;
                    while (c_i <= idxs.col_end) {
                        if (tableMeta._fields[c_i] && (clip_one_value || clipr[clipr_i])) {
                            let tfld = tableMeta._fields[c_i];
                            if (overwrite || !usedRow[tfld.field]) {
                                this._copy_value(usedRow, tfld, clip_one_value || clipr[clipr_i]);
                            }
                            clipr_i++;
                        }
                        c_i++;
                    }
                    tablRow ? u_rows.push(usedRow) : new_rows.push(usedRow);
                }
                r_i++;
            }
        }

        return {
            updated_rows: u_rows,
            new_rows: new_rows
        };
    }

    /**
     *
     * @param tableRow
     * @param tableHeader
     * @param val
     * @param fromRow
     * @private
     */
    _copy_value(tableRow, tableHeader, val, fromRow) {
        tableRow._old_val = tableRow[tableHeader.field];
        tableRow[tableHeader.field] = UnitConversion.doConv(tableHeader, val, true);
        tableRow._changed_field = tableHeader.field;
        if (tableHeader.input_type === 'Formula') {
            tableRow[tableHeader.field+'_formula'] = tableRow[tableHeader.field];
        }
        if (fromRow && fromRow['_u_'+tableHeader.field]) {
            tableRow['_u_'+tableHeader.field] = fromRow['_u_'+tableHeader.field];
        }
    }
    // COPY FUNCTIONS


    //CHECKERS
    /**
     *
     * @returns {boolean}
     */
    has_row() {
        return this.row > -1;
    }

    /**
     *
     * @returns {boolean}
     */
    has_col() {
        return !!this.col;
    }

    /**
     *
     * @param tableMeta
     * @param pref
     * @returns {{col_start: number, col_end: number, row_start: (number|*), row_end: (number|*)}}
     */
    idxs(tableMeta, pref = '') {
        let start_idx = _.findIndex(tableMeta._fields, {field: this[pref+'col']});
        let end_idx = _.findIndex(tableMeta._fields, {field: this[pref+'square_col']});
        let col_start = (start_idx > end_idx ? end_idx : start_idx);
        let col_end = (start_idx > end_idx ? start_idx : end_idx);

        let row_start = (this[pref+'row'] > this[pref+'square_row'] ? this[pref+'square_row'] : this[pref+'row']);
        let row_end = (this[pref+'row'] > this[pref+'square_row'] ? this[pref+'row'] : this[pref+'square_row']);

        row_start = row_start === -1 ? row_end : row_start;
        row_end = row_end === -1 ? row_start : row_end;
        col_start = col_start === -1 ? col_end : col_start;
        col_end = col_end === -1 ? col_start : col_end;

        return {
            col_start: col_start,
            col_end: col_end,
            row_start: row_start,
            row_end: row_end,
        };
    }
    //CHECKERS


    // GETTERS
    /**
     *
     * @returns {*|number}
     */
    get_row() {
        return this.row;
    }

    /**
     *
     * @returns {*|string|null}
     */
    get_col() {
        return this.col;
    }
    // GETTERS
}