<script>
    /**
     *  should be present:
     *
     *  this.editing: Bool
     *  this.isVertTable: Bool
     *  this.isSelected: Bool
     *  this.no_key_handler: Bool
     *  */
    export default {
        data: function () {
            return {
                avail_key_handler_codes: [9,13,27,33,34,37,38,39,40],
            }
        },
        methods: {
            keyHandler(e) {
                if (['INPUT', 'TEXTAREA'].indexOf(e.target.nodeName) > -1) {
                    //if press 'Enter' on selected edited cell -> close edit mode
                    if (e.keyCode == 13 && this.editing) {
                        e.preventDefault();
                        this.hideEdit();
                        this.updateValue();
                        //this.changeCol(true);
                    }
                    return;
                }

                if ((!this.$root.data_is_editing && !e.shiftKey) || (this.$root.data_is_editing && e.ctrlKey)) {
                    //if press 'Enter' on selected not edited cell -> open edit mode
                    if (e.keyCode == 13 && !this.editing) {
                        e.preventDefault();
                        this.showEdit(true);
                    } else
                    //if press 'Tab','PgDn','ArrowDn' on selected not edited cell -> select next column
                    if (e.keyCode == 9 || e.keyCode == 34 || (this.isVertTable && (e.keyCode == 39 || e.keyCode == 40))) {
                        e.preventDefault();
                        this.changeCol(true);
                    } else
                    //if press 'PgUp','ArrowUp' on selected not edited cell -> select prev column
                    if (e.keyCode == 33 || (this.isVertTable && (e.keyCode == 38 || e.keyCode == 37))) {
                        e.preventDefault();
                        this.changeCol(false);
                    } else
                    //if press 'Esc' on selected and edited cell -> close editing
                    if ([27, 37, 38, 39, 40, 191].indexOf(e.keyCode) > -1 && this.editing) {// e.keyCode == 27
                        e.preventDefault();
                        this.hideEdit();
                        this.updateValue();
                        if (this.$refs.inline_input && $(this.$refs.inline_input).hasClass('select2-hidden-accessible')) {
                            $(this.$refs.inline_input).select2('destroy');
                        }
                    }
                }
            },
            globalKeydownHandler(e) {
                if (this.inArray(e.keyCode, this.avail_key_handler_codes) && this.isSelected && !this.no_key_handler) {
                    this.keyHandler(e);
                }
                if (this.no_key_handler) {
                    this.$nextTick(() => { this.no_key_handler = false; });
                }
            },
        },
    }
</script>