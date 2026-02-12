<script>
    /**
     *  should be present:
     *
     *  this.tableMeta: Object
     *  this.allRows: Object|Array
     *  this.selectedCell: selectedCells
     *  this.editing: Bool
     *  this.isVertTable: Bool
     *  this.isSelectedExt: Bool
     *  this.no_key_handler: Bool
     *  */
    import {eventBus} from "../../app";

    export default {
        data: function () {
            return {
            }
        },
        methods: {
            globalClick(e) {
                let excluded = this.inArray(this.tableHeader.f_type, ['Address']);
                let container = $(this.$refs.cell_tb_data);
                if (!excluded && this.editing && container && container.has(e.target).length === 0) {
                    setTimeout(() => {//timeout - fix: when formula is closed by clicking in another place -> last change will not be saved
                        this.hideEdit();
                    }, 100);
                }
            },
            keyHandler(e) {
                //ignore not used cases
                if (e.shiftKey || [9,13,27,33,34,37,38,39,40].indexOf(e.keyCode) === -1) {
                    return;
                }

                if (this.editing) {
                    //if press 'Enter'/'Esc' on selected edited cell -> close edit mode
                    if ([13, 27].indexOf(e.keyCode) > -1) {
                        e.preventDefault();
                        this.cmk_closeEdit();
                    }
                    //arrows left/right
                    let cmdOrCtrl = e.metaKey || e.ctrlKey;
                    if (cmdOrCtrl && [37, 39].indexOf(e.keyCode) > -1) {
                        e.preventDefault();
                        this.cmk_changeCol(e.keyCode === 39);
                    }
                    //arrows up/down
                    if (cmdOrCtrl && [38, 40].indexOf(e.keyCode) > -1) {
                        e.preventDefault();
                        this.cmk_rowChange(e.keyCode === 40);
                    }
                    //if press 'Tab' -> select next column
                    if (e.keyCode === 9) {
                        e.preventDefault();
                        this.cmk_changeCol(true, true);
                    }
                } else {
                    e.preventDefault();
                    //if press 'Enter' on selected not edited cell -> open edit mode
                    if (e.keyCode === 13 && !this.editing) {
                        this.showEdit(true);
                    }
                    //arrow left/right
                    if ([37, 39].indexOf(e.keyCode) > -1) {
                        this.cmk_changeCol(e.keyCode === 39);
                    }
                    //arrows up/down
                    if ([38, 40].indexOf(e.keyCode) > -1) {
                        this.cmk_rowChange(e.keyCode === 40);
                    }
                    //if press 'Tab' -> select next column
                    if (e.keyCode === 9) {
                        this.cmk_changeCol(true);
                    }
                    //clicked 'PgUp'/'PgDn'
                    if ([33, 34].indexOf(e.keyCode) > -1) {
                        this.cmk_changeCol(e.keyCode === 34);
                    }
                }
            },
            globalKeydownHandler(e) {
                if (this.isSelectedExt && !this.no_key_handler) {
                    this.keyHandler(e);
                }
                if (this.no_key_handler) {
                    this.$nextTick(() => { this.no_key_handler = false; });
                }
            },
            cmk_changeCol(is_next, and_edit) {
                this.cmk_closeEdit();
                this.$nextTick(() => {
                    if (this.selectedCell) {
                        this.selectedCell.next_col(this.tableMeta, is_next, this.isVertTable);
                        if (and_edit) {
                            this.$nextTick(() => {
                                eventBus.$emit('table-data-open-edit', this.tableMeta.id);
                            });
                        }
                    }
                });
            },
            cmk_rowChange(is_next) {
                this.cmk_closeEdit();
                this.$nextTick(() => {
                    if (this.selectedCell) {
                        //Change col instead of row for VertTable
                        if (this.isVertTable) {
                            this.selectedCell.next_col(this.tableMeta, is_next, this.isVertTable);
                        } else {
                            this.selectedCell.next_row(this.allRows, is_next);
                        }
                    }
                });
            },
            cmk_closeEdit() {
                if (this.editing) {
                    this.hideEdit();
                    this.updateValue();
                    if (this.$refs.inline_input && $(this.$refs.inline_input).hasClass('select2-hidden-accessible')) {
                        $(this.$refs.inline_input).select2('destroy');
                    }
                }
            },
        },
    }
</script>