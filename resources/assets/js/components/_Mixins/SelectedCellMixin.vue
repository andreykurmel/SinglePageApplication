<script>
    import {SelectedCells} from "../../classes/SelectedCells";

    /**
     * Must be present:
     * this.addingRow: Object
     * this.tableMeta: Object
     * this.allRows: Object
     * this.addRow(): Function
     * this.canEditCell(): Function
     * this.rowIndexClicked(): Function
     * $refs.scroll_wrapper: HTML component
     *
     * Can be:
     * this.behavior: String
     */
    export default {
        data: function () {
            return {
                selectedCell: new SelectedCells(),
            }
        },
        computed: {
        },
        methods: {
            //global key handler
            globalKeyHandler(e) {
                let cmdOrCtrl = e.metaKey || e.ctrlKey;
                if (this.behavior === 'list_view' && cmdOrCtrl && e.keyCode === 13 && this.addingRow.active) {//ctrl + 'enter' + 'active top adding row'
                    this.addRow();
                }

                if (['INPUT', 'TEXTAREA'].indexOf(e.target.nodeName) > -1) {
                    return;
                }

                if (this.selectedCell.has_row() && this.selectedCell.has_col()) {
                    //scroll table on changing columns/rows
                    if (e.keyCode === 37) {//left arrow
                        let sel_fld = _.find(this.tableMeta._fields, {field: this.selectedCell.get_col()}) || {};
                        $(this.$refs.scroll_wrapper).scrollLeft(this.$refs.scroll_wrapper.scrollLeft - Number(sel_fld.width));
                    }
                    if (e.keyCode === 38 && this.selectedCell.get_row() > 0) {//up arrow
                        $(this.$refs.scroll_wrapper).scrollTop(this.$refs.scroll_wrapper.scrollTop - Number(this.tdCellHGT));
                    }
                    if (e.keyCode === 39) {//right arrow
                        let sel_fld = _.find(this.tableMeta._fields, {field: this.selectedCell.get_col()}) || {};
                        $(this.$refs.scroll_wrapper).scrollLeft(this.$refs.scroll_wrapper.scrollLeft + Number(sel_fld.width));
                    }
                    if (e.keyCode === 40 && this.selectedCell.get_row() < (this.allRows.length - 1)) {//down arrow
                        $(this.$refs.scroll_wrapper).scrollTop(this.$refs.scroll_wrapper.scrollTop + Number(this.tdCellHGT));
                    }
                    //---

                    if (e.shiftKey && e.keyCode === 191) {//shift + '?'
                        let idxx = this.selectedCell.get_row();
                        this.rowIndexClicked(idxx, this.allRows[idxx]);
                    }

                    if (this.behavior === 'list_view' || this.behavior === 'settings_display') {
                        if (cmdOrCtrl && e.keyCode === 67) {//ctrl + 'c'
                            this.selectedCell.start_copy(this.tableMeta, this.allRows);
                        }
                        if (cmdOrCtrl && e.keyCode === 86 && this.canEditSelected()) {//ctrl + 'v'
                            let sel_fld = _.find(this.tableMeta._fields, {field: this.selectedCell.get_col()}) || {};
                            if (sel_fld.f_type === 'Attachment') {
                                return;
                            } else {
                                this.fillPasteData(!e.shiftKey);
                            }
                        }
                        if (cmdOrCtrl && e.keyCode === 90 && this.canEditSelected()) {//ctrl + 'z'
                            let $rev_rows = this.$root.data_reverser.do_reverse(this.tableMeta.id);
                            this.$emit('mass-updated-rows', $rev_rows);
                        }
                    }
                    if (e.keyCode === 46 && this.canEditSelected()) {//delete
                        let $changed_rows = this.selectedCell.delete_in_selected(this.tableMeta, this.allRows);
                        this.$emit('mass-updated-rows', $changed_rows);
                    }
                    if (e.keyCode === 27) {//esc
                        this.selectedCell.clear();
                    }
                }
            },
            canEditSelected() {
                let can = true;
                let idxs = this.selectedCell.idxs(this.tableMeta);
                for (let r = idxs.row_start; r <= idxs.row_end; r++) {
                    for (let c = idxs.col_start; c <= idxs.col_end; c++) {
                        let fld = this.tableMeta._fields[c];
                        can = can && this.canEditCell(fld, this.allRows[r]);
                    }
                }
                return can;
            },
            pasteData() {
                let envs = this.selectedCell.idxs(this.tableMeta, '');
                let tocopy = this.selectedCell.idxs(this.tableMeta, 'copy_');
                let len = Math.abs(envs.row_end - envs.row_start) || Math.abs(tocopy.row_end - tocopy.row_start);
                this.$root.data_reverser.pre_change(this.allRows, envs.row_start, len);
                if (this.selectedCell.fill_want_confirmation(this.allRows)) {
                    Swal({
                        title: 'Info',
                        text: 'Confirm overwriting of existing data?',
                        confirmButtonText: 'Yes',
                        cancelButtonText: 'No',
                        showCancelButton: true,
                    }).then(response => {
                        this.fillPasteData(!!response.value);
                    });
                } else {
                    this.fillPasteData();
                }
            },
            fillPasteData(overwrite) {
                this.selectedCell.fill_copy(this.tableMeta, this.allRows, overwrite).then((result) => {
                    this.$root.data_reverser.after_change(this.tableMeta.id, this.allRows);
                    this.$emit('mass-updated-rows', result.updated_rows);
                    let nrows = _.reverse(result.new_rows || []);
                    for (let i = 0; i < nrows.length; i++) {
                        this.$emit('added-row', nrows[i]);
                        window.sleep(300);
                    }
                    //this.selectedCell.clear(); //commented to copy cell multiple times
                });
            },
            unselectCell() {
                if (this.selectedCell.get_col()) {
                    this.selectedCell.clear();
                }
            },
            unselectCellOutside(e) {
                let container = $(this.$refs.scroll_wrapper);
                if (container && container.has(e.target).length === 0 && this.selectedCell.get_col()) {
                    this.selectedCell.clear();
                }
            },
        },
    }
</script>