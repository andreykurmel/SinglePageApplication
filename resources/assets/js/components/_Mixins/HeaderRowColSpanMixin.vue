<script>
    /**
     *  should be present:
     *
     *  this.tableMeta: Object,
     *  this.maxRowsInHeader: Number,
     *  this.isShowFieldElem(header): Function,
     *
     *  can be present:
     *
     *  this.headersChanger: Object,
     *  */
    export default {
        data: function () {
            return {
                sub_header_remover: '',
            }
        },
        computed: {
        },
        methods: {
            //multiheaders functions
            splitName(name) {
                name = String(name).replace(this.sub_header_remover, '');
                return name.split(',');
            },
            getMultiName(header, index) {
                let name = header.name;

                if (this.headersChanger && this.headersChanger[header.field] !== undefined) {
                    return this.headersChanger[header.field];
                }

                index--;
                let res = '';
                if (name) {
                    let names = this.splitName(name);
                    if (names.length > index) {
                        res = names[index];
                    }
                }
                return res;
            },
            //span table header rows
            getMultiRowspan(tableHeader, curHeaderRow) {
                if (this.maxRowsInHeader === 1) {
                    return (tableHeader.unit || tableHeader.unit_display) ? 1 : 2;
                }

                let rowspan = 0;
                let names = this.splitName(tableHeader.name);
                let i = curHeaderRow - 1;

                if (curHeaderRow <= names.length && curHeaderRow > 1 && names[i] === names[i-1]) {
                    //if name is the same as previous name -> then it name is spanned and don`t print it again
                    rowspan = 0;
                } else {
                    //span header rows with the same names
                    if (curHeaderRow < names.length) {
                        while (curHeaderRow < names.length && names[i] === names[curHeaderRow]) {
                            rowspan += 1;
                            curHeaderRow++;
                        }
                    }

                    //min rowspan for printing = 1
                    if (curHeaderRow <= names.length) {
                        rowspan += 1;
                    }
                    //if it is last header row OR we have no names more -> span all bottom rows
                    if (curHeaderRow === names.length) {
                        rowspan += this.maxRowsInHeader - curHeaderRow;
                        rowspan += (tableHeader.unit || tableHeader.unit_display) ? 0 : 1;
                    }
                }
                return rowspan;
            },
            //span table header columns
            getMultiColspan(index, curHeaderRow, meta_fields) {
                if (this.maxRowsInHeader === 1) {
                    return 1;
                }

                let colspan = 0;
                let prev_names = (index > 0 ? this.splitName(meta_fields[index-1].name) : []);
                let names = this.splitName(meta_fields[index].name);
                let next_names = (index < (meta_fields.length-1) ? this.splitName(meta_fields[index+1].name) : []);
                let i = curHeaderRow - 1;

                if (curHeaderRow <= names.length) {
                    if (
                        index > 0
                        &&
                        names[i] === prev_names[i]
                        &&
                        this.isShowFieldElem(meta_fields[index-1])
                        &&
                        this.getMultiRowspan(meta_fields[index], curHeaderRow) === this.getMultiRowspan(meta_fields[index-1], curHeaderRow)
                    ) {
                        //if column name is the same as previous column name and is visible -> then it column name is spanned and don`t print it again
                        colspan = 0;
                    } else {
                        //span visible header columns with the same names and equal rowspans
                        while (
                            index < (meta_fields.length-1)
                            &&
                            names[i] === next_names[i]
                            &&
                            this.isShowFieldElem(meta_fields[index+1])
                            &&
                            this.getMultiRowspan(meta_fields[index], curHeaderRow) === this.getMultiRowspan(meta_fields[index+1], curHeaderRow)
                        ) {
                            colspan += 1;
                            index++;
                            names = next_names;
                            next_names = (index < (meta_fields.length-1) ? this.splitName(meta_fields[index+1].name) : []);
                        }

                        //min colspan for printing = 1
                        colspan += 1;
                    }
                }
                return colspan;
            },
            //is last row
            lastRow(tableHeader, curHeaderRow) {
                return (
                        this.getMultiRowspan(tableHeader, curHeaderRow)
                        +
                        curHeaderRow - ((tableHeader.unit || tableHeader.unit_display) ? 1 : 2)
                    ) === (this.maxRowsInHeader);
            },
        },
    }
</script>