<script>
    /**
     *  can be present:
     *
     *  this.headersChanger: Object,
     *  */
    export default {
        data: function () {
            return {
                sub_header_remover: '',
                maxRowsInHeader: 0,
                multiSpan: {
                    row: {},
                    col: {},
                },
            }
        },
        computed: {
        },
        methods: {
            getMultiRowspan(hdrIndex, curHeaderRow) {
                return this.multiSpan.row[curHeaderRow+'_'+hdrIndex];
            },
            getMultiColspan(hdrIndex, curHeaderRow) {
                return this.multiSpan.col[curHeaderRow+'_'+hdrIndex];
            },
            fillHeaderRowColSpanCache(showMetaFields) {
                this.maxRowsInHeader = this.getMaxRows(showMetaFields);
                for (let curHeaderRow = 1; curHeaderRow <= this.maxRowsInHeader; curHeaderRow++) {
                    _.each(showMetaFields, (header, index) => {
                        this.multiSpan.row[curHeaderRow+'_'+index] = this.getMultiValueRowspan(header, curHeaderRow, showMetaFields);
                        this.multiSpan.col[curHeaderRow+'_'+index] = this.getMultiValueColspan(index, curHeaderRow, showMetaFields);
                    });
                }
            },
            //multiheaders functions
            getMaxRows(showMetaFields) {
                let max = 0;
                _.each(showMetaFields, (el) => {
                    if (el.name) {
                        max = Math.max(max, el.name.split(',').length);
                    }
                });
                this.headerHasRows = max;
                this.metaFieldsCount = showMetaFields.length;
                return max;
            },
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
            getMultiValueRowspan(tableHeader, curHeaderRow, showMetaFields) {
                if (this.maxRowsInHeader === 1) {
                    return (tableHeader.unit || tableHeader.unit_display) ? 1 : 2;
                }

                let rowspan = 0;
                let names = this.splitName(tableHeader.name);
                let i = curHeaderRow - 1;

                if (curHeaderRow <= names.length && curHeaderRow > 1 && _.trim(names[i]) === _.trim(names[i-1])) {
                    //if name is the same as previous name -> then it name is spanned and don`t print it again
                    rowspan = 0;
                } else {
                    //span header rows with the same names
                    if (curHeaderRow < names.length) {
                        while (curHeaderRow < names.length && _.trim(names[i]) === _.trim(names[curHeaderRow])) {
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
            getMultiValueColspan(index, curHeaderRow, showMetaFields) {
                if (this.maxRowsInHeader === 1) {
                    return 1;
                }

                let colspan = 0;
                let prev_names = (index > 0 ? this.splitName(showMetaFields[index-1].name) : []);
                let names = this.splitName(showMetaFields[index].name);
                let next_names = (index < (showMetaFields.length-1) ? this.splitName(showMetaFields[index+1].name) : []);
                let i = curHeaderRow - 1;

                if (curHeaderRow <= names.length) {
                    if (
                        index > 0
                        &&
                        _.trim(names[i]) === _.trim(prev_names[i])
                        &&
                        this.getMultiValueRowspan(showMetaFields[index], curHeaderRow, showMetaFields) === this.getMultiValueRowspan(showMetaFields[index-1], curHeaderRow, showMetaFields)
                    ) {
                        //if column name is the same as previous column name and is visible -> then it column name is spanned and don`t print it again
                        colspan = 0;
                    } else {
                        //span visible header columns with the same names and equal rowspans
                        while (
                            index < (showMetaFields.length-1)
                            &&
                            _.trim(names[i]) === _.trim(next_names[i])
                            &&
                            this.getMultiValueRowspan(showMetaFields[index], curHeaderRow, showMetaFields) === this.getMultiValueRowspan(showMetaFields[index+1], curHeaderRow, showMetaFields)
                        ) {
                            colspan += 1;
                            index++;
                            names = next_names;
                            next_names = (index < (showMetaFields.length-1) ? this.splitName(showMetaFields[index+1].name) : []);
                        }

                        //min colspan for printing = 1
                        colspan += 1;
                    }
                }
                return colspan;
            },
            //is last row
            lastRow(tableHeader, curHeaderRow, showMetaFields) {
                return (
                        this.getMultiValueRowspan(tableHeader, curHeaderRow, showMetaFields)
                        +
                        curHeaderRow - ((tableHeader.unit || tableHeader.unit_display) ? 1 : 2)
                    ) === (this.maxRowsInHeader);
            },
        },
    }
</script>